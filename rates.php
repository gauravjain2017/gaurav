<?php

class Rates_Route_Hours extends WP_REST_Controller {
    private $companies_table;
    private $energy_types_table;
    private $providers_table;
    private $rates_table;
    
    private $file_ext;
    private $mime_types;
    
    function __construct() {
        global $wpdb;
        
        $this->companies_table = $wpdb->prefix . 'gcp_parks_companies';
        $this->energy_types_table = $wpdb->prefix . 'gcp_energy_types';
        $this->providers_table = $wpdb->prefix . 'gcp_parks_providers';
        $this->rates_table = $wpdb->prefix . 'gcp_hours';
        
        $this->file_ext = 'xlsx';
        $this->mime_types = ["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"];
    }
    
    public function register_routes() {
        $version = '1';
        $namespace = 'gcp/v' . $version;
        $base = 'hours';
        
        register_rest_route( $namespace, '/' . $base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'                => array(),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_item' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'                => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ) );
        register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'                => array(
                    'context' => array(
                        'default' => 'view',
                    ),
                ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'                => $this->get_endpoint_args_for_item_schema( false ),
            ),
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_item' ),
                'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                'args'                => array(
                    'force' => array(
                        'default' => false,
                    ),
                ),
            ),
        ) );
        register_rest_route( $namespace, '/' . $base . '/schema', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array( $this, 'get_public_item_schema' ),
        ) );
    }
    
    public function get_items( $request ) {
        global $wpdb;
        $companies = $wpdb->get_results( "SELECT * FROM {$this->companies_table} ORDER BY name" );
        $energy_types = $wpdb->get_results( "SELECT * FROM {$this->energy_types_table} ORDER BY name" );
        $providers = $wpdb->get_results( "SELECT * FROM {$this->providers_table} ORDER BY name" );
        
        $data['status'] = "success";
        $data['data']['companies'] = $companies;
        $data['data']['energy_types'] = $energy_types;
        $data['data']['providers'] = $providers;
        
        return new WP_REST_Response( $data, 200 );
    }
    
    public function get_item( $request ) {
        $id = $request->get_param('id');
        
        if ( $id ) {
            return new WP_REST_Response( "", 200 );
        } else {
            return new WP_Error( 'code', __( 'Rate with ID specified does not exist.', 'gcp' ) );
        }
    }
    
    protected function update_rates($company_id, $energy_type_id, $provider_id, $rates) {
	
        global $wpdb;
        $sql = $wpdb->prepare("DELETE FROM {$this->rates_table} WHERE company_id={$company_id}");
        $wpdb->query($sql);
        
        $problematic_rows = [];
		
        for ($i = 0; $i < count($rates); $i += 1) {
			
			$date1      = explode(' ',$rates[$i][0]);
			$parkopen   = explode(' ',$rates[$i][1]);
			$parkclose  = explode(' ',$rates[$i][2]);
			$provider  = trim($rates[$i][3]);
			if($provider == 'Heavy'){
				$provider_id = 3;
				}else if($provider == 'Medium'){
				$provider_id = 2;
				}else{ 
				$provider_id = 1;
				}
            $date2      = $date1[0];
            $park_open  = $parkopen[1];
            $park_close = $parkclose[1];
            
            if (isset($date2) && isset($park_open) &&  isset($park_close)  )  {
					$date  = date('m/d/Y',strtotime($date2));				
		
                   $sql_rate = $wpdb->prepare("INSERT INTO {$this->rates_table} (date, company_id, provider_id, park_open, park_close) VALUES('{$date}', {$company_id}, {$provider_id}, '{$park_open}', '{$park_close}')");
                    $sql_response = $wpdb->query($sql_rate);					
                    
                    if (!$sql_response || $sql_response !== 1) {
                        array_push($problematic_rows, [
                            'id' => ($i + 2),
                            'message' => 'Row could not be inserted.',
                            'row' => $rates[$i]
                        ]);
                    }
                } else {
                    array_push($problematic_rows, [
                        'id' => ($i + 2),
                        'message' => 'Row data is malformed.',
                        'row' => $rates[$i]
                    ]);
                }
        }
        
        return $problematic_rows;
    }
  
    public function create_item( $request ) {
        $body = $request->get_body_params();
        $files = $request->get_file_params();
        
        $is_valid = true;
        $message = null;
        $errors = null;
        
        if ( !empty( $files ) && !empty( $files['file'] ) ) {
            $file = $files['file'];
        } else {
            $is_valid = false;
            $message = "File is not present. Please upload a file.";
        }
        
        /* File Validation */
        if ( !$file ) {
            $is_valid = false;
            $message = "File is not present. Please upload a file.";
        }
        
        if ( !is_uploaded_file( $file['tmp_name'] ) ) {
            $is_valid = false;
            $message = "File was not uploaded. Please upload a file.";
        }
        
        if (! $file['error'] === UPLOAD_ERR_OK ) {
            $is_valid = false;
            $message = "Upload error: " . $file['error'];
        }
        
        $ext = pathinfo( $file['name'], PATHINFO_EXTENSION );
        if ( $ext !== $this->file_ext ) {
            $is_valid = false;
            $message = "File must be of type: " . $this->file_ext;
        }
        
        $mimeType = mime_content_type($file['tmp_name']);
        if ( !in_array( $file['type'], $this->mime_types ) || !in_array( $mimeType, $this->mime_types ) ) {
            $is_valid = false;
            $message = "File must have one of the following mime types: " . implode(", ", $this->mime_types);
        }
        /* File Validation */
        
        if ($is_valid &&
            array_key_exists('company_id', $body) && !empty($body['company_id'])) {
            global $wpdb;
            
            $simpleXlsx = new SimpleXLSX();
            $rows = null;
            if ( $xlsx = SimpleXLSX::parse( $file['tmp_name'] ) ) {
                $rows = $xlsx->rows();
				// $special_offer = sanitize_text_field($rows[1][2]);
			
               
                if ($rows) {
                    if (count($rows) > 1) {
                        if (count($rows[0]) === 4)
                            
                        {
                            $data_rows = array_slice($rows, 1);
                            $errors = $this->update_rates($body['company_id'], $body['energy_type_id'], $body['provider_id'], $data_rows);
                            
                            if (count($errors) > 0) {
                                $is_valid = false;
                                $message = "The spreadsheet has been imported, but some of the data was malformed.";
                            }
                        } else {
                            $is_valid = false;
                            $message = "Spreadsheet column headers are malformed.";
                        }
                    } else {
                        $is_valid = false;
                        $message = "Spreadsheet only contains headers.";
                    }
                } else {
                    $is_valid = false;
                    $message = "Spreadsheet does not contain any data.";
                }
            } else {
                $is_valid = false;
                $message = SimpleXLSX::parseError();
            }
            
            if ($is_valid) {
                $data["status"] = "success";
                return new WP_REST_Response( $data, 200 );
            }
        }
        
        $data["status"] = "error";
        $data["message"] = $message;
        
        if (!empty($errors) && count($errors) > 0) {
            $data["data"] = $errors;
        }
        
        return new WP_REST_Response( $data, 200 );
    }
    
    public function update_item( $request ) {
        $id = $request->get_param('id');
        
        if ($id) {
            return new WP_REST_Response( "", 200 );
        }
        
        return new WP_Error( 'cant-update', __( 'Could not update Rate.', 'gcp' ), array( 'status' => 500 ) );
    }
    
    public function delete_item( $request ) {
        $id = $request->get_param('id');
        
        if ($id) {
            return new WP_REST_Response( "", 200 );
        }
        
        return new WP_Error( 'cant-delete', __( 'Could not delete Rate.', 'gcp' ), array( 'status' => 500 ) );
    }
    
    public function get_items_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }
    
    public function get_item_permissions_check( $request ) {
        return $this->get_items_permissions_check( $request );
    }
    
    public function create_item_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }
    
    public function update_item_permissions_check( $request ) {
        return $this->create_item_permissions_check( $request );
    }
    
    public function delete_item_permissions_check( $request ) {
        return $this->create_item_permissions_check( $request );
    }
    
    protected function prepare_item_for_database( $request ) {
        return array();
    }
    
    protected function get_collection() {
        global $wpdb;
        $rates = $wpdb->get_results( "SELECT * FROM {$this->rates_table} ORDER BY zip" );
        
        $obj['status'] = "success";
        $obj['data']['rates'] = $rates;
        
        return $obj;
    }
    
    public function prepare_item_for_response( $item, $request ) {
        return array();
    }
    
    public function get_collection_params() {
        return array(
            'page'     => array(
                'description'       => 'Current page of the collection.',
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'description'       => 'Maximum number of items to be returned in result set.',
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
            ),
            'search'   => array(
                'description'       => 'Limit results to those matching a string.',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        );
    }
}
