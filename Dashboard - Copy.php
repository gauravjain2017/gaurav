<?php


function user_avg_calculator($value)
{
    $total = count($value);
    $value = array_sum($value);

    return round($value / $total);
}
class Dashboard extends CI_Controller
{
    /**
     * Check if the user is logged in, if he's not,
     * send him to the login page.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('../controllers/salesforce/models/Salesforce_api_model');
        $get_fiscal_year = $this->Common_model->get_data_row('*', 'tbl_forecast_year', array('client_id' => $this->session->userdata('client_id')));
        $this->start_month = $get_fiscal_year['start_month'];
        if ($this->start_month != 1) {
            $this->start_year = $get_fiscal_year['start_fiscal_year'] + 1;
        } else {
            $this->start_year = $get_fiscal_year['start_fiscal_year'];
        }
        $this->start_year_val = $get_fiscal_year['start_fiscal_year'];
        $this->end_year = $get_fiscal_year['end_fiscal_year'];
        $this->end_month = $get_fiscal_year['end_month'];

        $this->load->model('Cam_model');
        $this->month = array('1' => 'Jan', '2' => 'Feb', '3' => 'Mar', '4' => 'Apr', '5' => 'May', '6' => 'Jun', '7' => 'Jul', '8' => 'Aug', '9' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');

        $this->session->keep_flashdata('member_login');
		$this->get_steps = $this->Common_model->get_scorecard_steps($this->session->userdata('client_id'),"frontend");
		$this->parent_steps      = $this->get_steps['parent_step'];
    }

   public function index()
    {
        $this->load->model('Manageusers_model');
        $this->load->model('Sfowner_model');
        if ($this->session->userdata('dealer_login')) 
		{
			
			// Count Listing
			
            $data['allreports'] = $this->Manageusers_model->get_all_reports($this->session->userdata('client_id'),'members_details');
			
			 $data['partners_count'] = $this->Manageusers_model->get_all_reports($this->session->userdata('client_id'),'partners_accounts');
			 
			  $data['cam_count'] = $this->Manageusers_model->get_all_reports($this->session->userdata('client_id'),'account_channel_manager');
			  
			  
            $data['topreports'] = $this->Manageusers_model->get_top_reports($this->session->userdata('client_id'),'members_details');
			
		$data['allpartners'] = $this->Manageusers_model->get_top_reports($this->session->userdata('client_id'),'partners_accounts');
			
			$data['allcam'] = $this->Manageusers_model->get_top_reports($this->session->userdata('client_id'),'account_channel_manager');
			
            $data['allusers'] = $this->Manageusers_model->get_all_users($this->session->userdata('client_id'));
			
		
			
            $data['topusers'] = $this->Manageusers_model->get_top_users($this->session->userdata('client_id'));
            $get_roles = $this->Sfowner_model->getall_roles();
            $data2 = array('getall_roles_in_session' => $get_roles);
            $this->session->set_userdata($data2);
            $data['main_content'] = 'fronthand/twitter/dashboard';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
		}
    // add additional year in forecasr year
    public function additional_years()
    {
        $this->load->model('Manageproducts_model');
        $clientid = $this->session->userdata('client_id');
        $previous_year = $this->Common_model->get_data_row('additional_year,end_fiscal_year', 'tbl_forecast_year', array('client_id' => $clientid));
        if ($previous_year['additional_year'] >= 0) {
            $count_year = $previous_year['additional_year'] + 1;
        }
        $update['additional_year'] = $count_year;
        $this->Common_model->update_data('tbl_forecast_year', $update, array('client_id' => $clientid));
        //$this->Manageproducts_model->set_forecast_all_products($clientid,($previous_year['end_fiscal_year']+$count_year));
        echo $count_year + $previous_year['end_fiscal_year'];
    }
	
	
    /**
     * Dashboard for Channel Acount Partners.
     */
    public function partner_dashboard()
    {
        $this->load->model('Sfowner_model');
        $this->load->model('Sfpartners_model');
        if ($this->session->userdata('partner_login')) {
            $data['get_contacts_of_partner'] = $this->Sfpartners_model->get_partner_contacts($this->session->userdata('partner_acc_id'));
            $getall_roles = $this->Sfowner_model->getall_roles();
            $data_all = array('getall_roles' => $getall_roles);
            $this->session->set_userdata($data_all);
            $data['main_content'] = 'fronthand/twitter/dashboard_partner';
            $this->load->view('/includes/template_partner_dashboard', $data);
        } else {
            redirect('manageaudit');
        }
    }

    public function report_order()
    {
        if ($this->session->userdata('dealer_login')) {
		    $client_id = $this->session->userdata('client_id');
            $this->load->model('Sfpartners_model');
            $session_uerdata = $this->session->userdata;
            $query = $this->Sfpartners_model->get_report_order_data($session_uerdata['client_id']);
            $data['report_order'] = $query;
			$this->db->where('client_id ', $client_id);
			$query = $this->db->get('consolidate_steps');
			$data['steps'] = $query->result_array();
            $data['main_content'] = 'fronthand/twitter/manageusers/report_order';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
    }

    public function drag_drop_report_positions()
    {
        $this->load->model('Sfpartners_model');
        $dragable = $this->input->post('dragable');
        $dropable = $this->input->post('dropable');
        $this->Sfpartners_model->update_drag_drop_report($dragable, $dropable);
    }

    /**
     * Edit the Partner profile.
     */
      public function edit_partner()
    {
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Sfpartners_model');
        $this->load->model('Sfowner_model');
        $this->load->model('Dashboard_model');
		
		$encode = $this->Common_model->encode_decode_id($this->input->get('partner_id'));
		$partnerid = $encode['decrypted_id'];
		//debug($encode);die;
        //$partnerid = base64_decode($this->input->get('partner_id'));
       
	   $data['custom_field'] = $this->Common_model->get_data_array('*', 'tbl_custom_field', array("custom_field_type"=>"2"),'field_order','field_order');
	   $partner_custom_data = $this->Common_model->get_data_array('custom_field_data_id,custom_field_id,value', 'tbl_custom_field_data', array('edit_id' =>$partnerid));
	   $data['partner_custom_data'] = $this->Common_model->keyValuepair($partner_custom_data,'value','custom_field_id');
	   $data['custom_field_ids'] = $this->Common_model->keyValuepair($partner_custom_data,'custom_field_data_id','custom_field_id');
	   $data['all_dropdown'] = $this->Dashboard_model->get_custom_option();
	   $data['get_field'] = $this->Common_model->get_field_type();
	   
	   
	 
        $data['allsuper_partners'] = $this->Sfpartners_model->get_superpartner($this->session->userdata('client_id'));
        $data['all_cam'] = $this->Sfowner_model->get_cam_without_director($this->session->userdata('client_id'));
        $data['edit_partner'] = $this->Sfpartners_model->edit_partner_view($partnerid);
        //added by ajay to fetch the data for secondary cam
        $selected_cams = $this->Sfpartners_model->edit_secondary_partner_view($partnerid);
        //$data["selected_cams"] = $selected_cams;
        foreach ($selected_cams as $value) {
            $data['selected_cams'][] = $value['secondary_cam_id'];
        }
        foreach ($data['all_cam'] as $cam_name) {
            if (in_array($cam_name['id'], $data['selected_cams'])) {
                $selected_cam_arr[$cam_name['id']] = $cam_name['crm_acc_name'];
            }
        }
        $data['sec_cams'] = $selected_cam_arr;
		$client_id = $this->session->userdata('client_id');
        //ended by ajay to fetch the data for secondary cam
		$sql="select * from tbl_parent_partner WHERE client_id=$client_id";
			
		$child_partners_ids=$this->Common_model->get_conditional_array($sql);
		$partner_id = array();
		foreach($child_partners_ids as $kay => $ChildPartner){
			$partner_id[] = $ChildPartner['partner_id'];
		}
		$data['child_parnter_ids'] = $partner_id;
		
		$parent_partner_id = array();
			foreach($child_partners_ids as $kay => $ChildPartner){
				$parent_partner_id[] = $ChildPartner['parent_partner_id'];
			}
			$data['parent_partner_id'] = $parent_partner_id;
			
        $data['main_content'] = 'custom_field/dashboard/partners/edit_partner';
        $this->load->view('/includes/template_new', $data);
    }

    /**
     * Edit the Partner's contact profile.
     */
    public function edit_contact()
    {  
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Reseseller_model');
        $this->load->model('Sfcontactsdetails_model');
        $this->load->model('Sfowner_model');
        $this->load->model('Sfpartners_model');
		$encode = $this->Common_model->encode_decode_id($this->input->get('contact_id'));
		$contactid = $encode['decrypted_id'];
		$data['contactid'] = $encode['decrypted_id'];
        
        //$contactid =  base64_decode($this->input->get('contact_id'));
        $data['allpartners'] = $this->Sfpartners_model->get_groupby_partner_name_($this->session->userdata('client_id'));
        $client_id = $this->session->userdata('client_id');

        $data['edit_contact'] = $this->Sfcontactsdetails_model->edit_contact_view($contactid);

        //$sql = "SELECT md.*,GROUP_CONCAT(md.partner_id SEPARATOR '<>') as partner_ids FROM members_details as md  WHERE clientid='".$client_id."' and email='".$contactid."' group by md.email";
        // $data['edit_contact'] = $this->Common_model->get_conditional_array($sql);
        //echo "<pre>";print_r($data['edit_contact']);echo "</pre>";

        $data['is_checked'] = $this->Common_model->get_data_row('is_checked', 'client_pl_steps', array('clientid' => $this->session->userdata('client_id')));
        $data['sfpartners'] = $this->Sfpartners_model->get_groupby_partner_name_($this->session->userdata('client_id'));
        $data['sfowner'] = $this->Sfowner_model->get_sfowner($search='',$cam_roles='');
        $data['main_content'] = 'fronthand/twitter/edit_contact_view';
        $this->load->view('/includes/template_new', $data);
    }

    /**
     * Edit the Channel Account Manager profile.
     */
    public function edit_cam()
    {   		
        $this->Common_model->check_dashboard_login();
        $clientid = $this->session->userdata('client_id');
        $this->load->model('Sfowner_model');
        $this->load->model('Region_model');
		
		$encode =  $this->Common_model->encode_decode_id($this->input->get('cam_id'));
		//debug($encode['decrypted_id']);die;
        $crm_id = $encode['decrypted_id'];
		
        //$crm_id = end($this->uri->segment_array());
        $data['edit_cam'] = $this->Sfowner_model->edit_cam_view_module($crm_id);
        foreach ($data['edit_cam'] as $edit) {
            $cam_id = $edit['id'];
        }

        // get selected regions by this cam
        $data['cam_slctd_level1_regions'] = $this->Region_model->format_array_as_value($this->Region_model->cam_level1_regions($cam_id), 'region_level1_id');
        $data['cam_slctd_level2_regions'] = $this->Region_model->format_array_as_value($this->Region_model->cam_level2_regions($cam_id), 'region_level2_id');
        $data['cam_slctd_level3_regions'] = $this->Region_model->format_array_as_value($this->Region_model->cam_level3_regions($cam_id), 'region_level3_id');

        // get all the regions
        $data['all_level1_regions'] = $this->Region_model->get_region_level1();
        $data['all_level2_regions'] = $this->Region_model->get_region_level2();
        $data['all_level3_regions'] = $this->Region_model->get_region_level3();
        $data['region'] = $this->Common_model->get_data_row('active_region', 'region_settings', array('client_id' => $clientid));
        $data['main_content'] = 'fronthand/twitter/edit_cam_view';
        $this->load->view('/includes/template_new', $data);
    }

    /**
     * Update the Partners profile.
     */
   public function updatepartner()
    {    
	    $this->Common_model->check_dashboard_login();
        $this->load->model('../controllers/salesforce/models/Salesforce_api_model');
        $this->load->model('Sfpartners_model');
        $this->load->model('Dashboard_model');
        $partnerid = $this->input->post('partner_id');
        $client_id = $this->session->userdata('client_id');
        $crm_id = $this->input->post('crm_id');
        $superpartner_id = $this->input->post('superpartner_id');
        //added by ajay for secondary cam to fetch the secondary data

        if ($this->input->post('crm_id_secondary') != '') {
            $crm_id_secondary = $this->input->post('crm_id_secondary');
        } else {
            $crm_id_secondary = '0';
        }

        $cam = explode('##', $crm_id);
        $crm_id = $cam[0];
        $cam_id = $cam[1];
        //ended by ajay for secondary cam to fetch the secondary data
        $this->db->select('crm_acc_name,');
        $this->db->where('crm_id', $crm_id);
        $query = $this->db->get('account_channel_manager');
        $crm_acc_name = $query->row_array();

        $data = array(
            'partner_acc_name' => htmlspecialchars($this->input->post('partner_acc_name')),
            'crm_name' => htmlspecialchars($crm_acc_name['crm_acc_name']),
            'crm_id' => htmlspecialchars($crm_id),
            'address1' => htmlspecialchars($this->input->post('address1')),
            'address2' => htmlspecialchars($this->input->post('address2')),
            'city' => htmlspecialchars($this->input->post('city')),
            'partner_division' => htmlspecialchars($this->input->post('partner_division')),
            'super_partner_id' => htmlspecialchars($this->input->post('superpartner_id')),
            'state_province' => htmlspecialchars($this->input->post('state_province')),
            'postal_code' => htmlspecialchars($this->input->post('postal_code')),
            'country' => htmlspecialchars($this->input->post('country')),
            'phone1' => htmlspecialchars($this->input->post('phone1')),
            'phone2' => htmlspecialchars($this->input->post('phone2')),
            'url' => htmlspecialchars($this->input->post('url')),
            'primary_partner_id' => $this->input->post('primary_partner_id') != '' ? htmlspecialchars($this->input->post('primary_partner_id')) : null,
            'secondary_partner_id' => htmlspecialchars($this->input->post('secondary_partner_id')),
            // 'source' => $this->input->post('source'),
            'email' => htmlspecialchars($this->input->post('email')),
        );

        $secondary_partner_id = $this->input->post('secondary_partner_id');
        $primary_partner_id = $this->input->post('primary_partner_id');
		
		// update custom fields
		$this->Dashboard_model->update_custom_fields($partnerid,$_POST,'2');
		
		
        if ($this->input->post('primary_partner_id') != '') {
            $client_id = $this->session->userdata('client_id');
            $data['id'] = $partnerid;
            $this->Salesforce_api_model->insert_update_account($data, $client_id);
            //exit;
        }

        $check_salesforce_id = $this->Sfpartners_model->check_if_exist_edit('partners_accounts', 'primary_partner_id', $primary_partner_id, $partnerid);

        $check_external_id = $this->Sfpartners_model->check_if_exist_edit('partners_accounts', 'secondary_partner_id', $secondary_partner_id, $partnerid);

        if ($check_external_id != '' or $check_salesforce_id != '') {
            $data['all_cam'] = $this->Sfpartners_model->get_cam_name_with_id();
            $data['edit_partner'] = $this->Sfpartners_model->edit_partner_view($partnerid);
            $selected_cams = $this->Sfpartners_model->edit_secondary_partner_view($partnerid);
            foreach ($selected_cams as $value) {
                $data['selected_cams'][] = $value['secondary_cam_id'];
            }
        }
        if ($check_salesforce_id) {
            $data['error_message'] = '<div class="alert alert-error fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Primary Partner ID# already exists!</strong> </div>';

            $data['main_content'] = 'fronthand/twitter/edit_partner_view';
            $this->load->view('/includes/template_new', $data);

            return false;
        }

        if ($check_external_id) {
            $data['error_message'] = '<div class="alert alert-error fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Secondary Partner ID# already exists!</strong> </div>';

            $data['main_content'] = 'fronthand/twitter/edit_partner_view';
            $this->load->view('/includes/template_new', $data);

            return false;
        }

        $old_ext_id = $this->input->post('old_secondary_id');
        $old_sf_id = $this->input->post('old_primary_id');
        //$ext_arr['partner_external_id'] = $secondary_partner_id;
        //$sf_id_arr['partner_saleforce_id'] = $primary_partner_id;

        $update = $this->Sfpartners_model->updatepartner($partnerid, $data);
        //added by ajay to update the secondary cam into database
        $previous_ids = $this->Common_model->get_data_array('id', 'partner_secondary_cam', array('partner_id' => $partnerid, 'client_id' => $client_id));
        $ids = $this->Common_model->ToSingleDimensional($previous_ids,$forkey="");
        $update_sec = array();
        $insert_sec = array();
        $not_to_delete = array(-1);
        for ($x = 0; $x < count($crm_id_secondary); ++$x) {
            $data_secondary = array();

            $data_secondary['client_id'] = $this->session->userdata('client_id');
            $data_secondary['cam_id'] = $cam_id;
            $data_secondary['secondary_cam_id'] = $crm_id_secondary[$x];
            $data_secondary['partner_id'] = $partnerid;

            if ($ids[$x] > 0) {
                $data_secondary['id'] = $ids[$x];
                $update_sec[] = $data_secondary;
                $not_to_delete[] = $ids[$x];
            } else {
                $insert_sec[] = $data_secondary;
            }
        }

        if (!empty($not_to_delete)) {
            $this->db->where_not_in('id', $not_to_delete);
            $this->db->where('partner_id', $partnerid);
            $this->db->delete('partner_secondary_cam');
        }
        if (!empty($update_sec)) {
            $this->db->update_batch('partner_secondary_cam', $update_sec, 'id');
            echo $this->db->last_query();
        }
        if (!empty($insert_sec)) {
            $this->db->insert_batch('partner_secondary_cam', $insert_sec);
        }
        //ended by ajay to update the secondary cam into database

        if ($update) {
            $this->session->set_flashdata('msg', "<div class='alert alert-success'>Partner Updated Successfully</div>");
            redirect('dashboard/manage_partners');
        } else {
            $this->session->set_flashdata('msg', "<div class='alert alert-error'> failed!!</div>");
            redirect('dashboard/manage_partners');
        }
    }

    /**
     * Update Channel Account Manager
     * edited by - Subhash.
     */
    public function updatecam($type)
    {
        // Load required models
        $this->load->model('Sfcontactsdetails_model');
        $this->load->model('Sfowner_model');
        $this->load->model('Region_model');
        $this->load->model('Sfpartners_model');

        //Get Authorization levels
        $regional_director = $this->input->post('is_region3_director');
        if ($regional_director == 1) {
            $is_region1_director = '1';
            $is_region2_director = '0';
            $is_region3_director = '0';
        } elseif ($regional_director == 2) {
            $is_region1_director = '0';
            $is_region2_director = '1';
            $is_region3_director = '0';
        } elseif ($regional_director == 3) {
            $is_region1_director = '0';
            $is_region2_director = '0';
            $is_region3_director = '1';
        } else {
            $is_region1_director = '0';
            $is_region2_director = '0';
            $is_region3_director = '0';
        }
        //$is_region1_director = $this->input->post('is_region1_director');
        //$is_region2_director = $this->input->post('is_region2_director');
        //$is_region3_director = $this->input->post('is_region3_director');

        //Get Selected Regions
        $level1_regions = $this->input->post('assign_level1_region');
        $level2_regions = $this->input->post('assign_level2_region');
        $level3_regions = $this->input->post('assign_level3_region');

        //Get Deselected Regions
        $non_slctd_level1 = $this->input->post('non_slctd_level1');
        $non_slctd_level2 = $this->input->post('non_slctd_level2');
        $non_slctd_level3 = $this->input->post('non_slctd_level3');

        $crm_id = $this->input->post('crm_id'); // get crm_id
        $data['edit_cam'] = $this->Sfowner_model->edit_cam_view($crm_id);
        foreach ($data['edit_cam'] as $edit) {
            $cam_id = $edit['id'];
        } // get cam_id

        $view_edit_record = $this->Sfowner_model->edit_cam_view($crm_id);
        $edit_email = $view_edit_record['0']['email'];
        //Edit by Kirti to set cam rights in admin to approve and allocate plans
        $cam_type = $this->input->post('cam_type');
        if (isset($_POST['approved_status']) && ($cam_type == 'director' || $cam_type == 'cam_director' || $cam_type == 'cam')) {
            $approve_status = 1;
        } else {
            $approve_status = 0;
        }

        if (isset($_POST['allocate_mdf']) && ($cam_type == 'director' || $cam_type == 'cam_director' || $cam_type == 'cam')) {
            $allocate_mdf = 1;
        } else {
            $allocate_mdf = 0;
        }
        if (isset($_POST['view_business_plans']) && ($cam_type == 'director' || $cam_type == 'cam_director' || $cam_type == 'cam')) {
            $business_plan_val = 1;
        } else {
            if ($cam_type == 'cam') {
                $business_plan_val = 1;
            } else {
                $business_plan_val = 0;
            }
        }

        // Get user input of cam details
        $crm_name = $this->input->post('first_name') . ' ' . $this->input->post('last_name');
        $data = array(
			'cam_type'            => htmlspecialchars($this->input->post('cam_type')),
			'crm_acc_name'        => htmlspecialchars($crm_name),
			'title'               => htmlspecialchars($this->input->post('title')),
			'company'             => htmlspecialchars($this->input->post('company')),
			'address1'            => htmlspecialchars($this->input->post('address1')),
			'address2'            => htmlspecialchars($this->input->post('address2')),
			'city'                => htmlspecialchars($this->input->post('city')),
			'state_province'      => htmlspecialchars($this->input->post('state_province')),
			'postal_code'         => htmlspecialchars($this->input->post('postal_code')),
			'country'             => htmlspecialchars($this->input->post('country')),
			'phone1'              => htmlspecialchars($this->input->post('phone1')),
			'phone2'              => htmlspecialchars($this->input->post('phone2')),
			'email'               => htmlspecialchars($this->input->post('email')),
			'crm_id'              => htmlspecialchars($this->input->post('primary_cam_id')),
			'approve_plans '      => htmlspecialchars($approve_status), //edit by kirti
			'allocate_mdf '       => htmlspecialchars($allocate_mdf),
			'edit_businessplan'   => htmlspecialchars($business_plan_val),
			'cam_sfdc_id'         => htmlspecialchars($this->input->post('secondary_cam_id')),
			'primary_region_id'   => htmlspecialchars($this->input->post('primary_region_id')),
			'is_region1_director' => $is_region1_director,
			'is_region2_director' => $is_region2_director,
			'is_region3_director' => $is_region3_director,
        );

        $region_arr = array('is_region1_director' => $is_region1_director, 'is_region2_director' => $is_region2_director, 'is_region3_director' => $is_region3_director);
        $check_email = $this->input->post('check_email');
        if (isset($_REQUEST['password']) && ($_REQUEST['password'] != '')) {
            $random_pwd = $this->input->post('password');
            $hash_password = md5($random_pwd);
            $data['password'] = $hash_password;
        }
        //Save CAM details

        $clientid = $this->session->userdata('client_id');

        $cam_email = $this->input->post('email');
        $primary_cam_id = $this->input->post('primary_cam_id');
        $secondary_cam_id = $this->input->post('secondary_cam_id');

        $check_email_exists = $this->Sfpartners_model->check_if_exist_edit('account_channel_manager', 'email', $cam_email, $cam_id);

        $check_external_id = $this->Sfpartners_model->check_if_exist_edit('account_channel_manager', 'crm_id', $primary_cam_id, $cam_id);

        $check_salesforce_id = $this->Sfpartners_model->check_if_exist_edit('account_channel_manager', 'cam_sfdc_id', $secondary_cam_id, $cam_id);

        if ($check_email_exists != '' or $check_external_id != '' or $check_salesforce_id != '') {
            $data['cam_slctd_level1_regions'] = $this->Region_model->format_array_as_value($this->Region_model->cam_level1_regions($cam_id), 'region_level1_id');
            $data['cam_slctd_level2_regions'] = $this->Region_model->format_array_as_value($this->Region_model->cam_level2_regions($cam_id), 'region_level2_id');
            $data['cam_slctd_level3_regions'] = $this->Region_model->format_array_as_value($this->Region_model->cam_level3_regions($cam_id), 'region_level3_id');
            $data['all_level1_regions'] = $this->Region_model->get_region_level1();
            $data['all_level2_regions'] = $this->Region_model->get_region_level2();
            $data['all_level3_regions'] = $this->Region_model->get_region_level3();
            $data['region'] = $this->Common_model->get_data_row('active_region', 'region_settings', array('client_id' => $clientid));
            $data['edit_cam'] = $this->Sfowner_model->edit_cam_view($crm_id);
        }

        if ($check_email_exists) {
            $data['error_message'] = '<div class="alert alert-error fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Email ID already exists!</strong> </div>';
            $data['main_content'] = 'fronthand/twitter/edit_cam_view';
            $this->load->view('/includes/template_new', $data);

            return false;
        }

        if ($check_external_id) {
            $data['error_message'] = '<div class="alert alert-error fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>External ID already exists!</strong> </div>';
            $data['main_content'] = 'fronthand/twitter/edit_cam_view';
            $this->load->view('/includes/template_new', $data);

            return false;
        }

        if ($check_salesforce_id) {
            $data['error_message'] = '<div class="alert alert-error fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Salesforce ID already exists!</strong> </div>';
            $data['main_content'] = 'fronthand/twitter/edit_cam_view';
            $this->load->view('/includes/template_new', $data);

            return false;
        }

        // echo '<pre>',print_r($data),'</pre>';exit;
        if ($this->input->post('cam_type') == 'director') {
            $partner_update = array(
                'crm_id' => null,
                'crm_name' => null,
            );
            $partner_where = array(
                'crm_id' => $crm_id,
            );
            $this->Common_model->update_data('partners_accounts', $partner_update, $partner_where);

            //Delete Secondry Cam From partner_secondary_cam
            $this->Common_model->delete_records('partner_secondary_cam', array('client_id' => $clientid, 'secondary_cam_id' => $cam_id));
        }

        $update_cam = $this->Sfowner_model->updatecam($crm_id, $data);
        //Save regions if selected
        $assign_regn = $this->Sfowner_model->assign_regions_to_cam($cam_id, $level1_regions, $level2_regions, $level3_regions, $region_arr);

        if ($update_cam) {
            if ($edit_email == $this->input->post('email') && $_REQUEST['password'] != '') {
                if ($check_email == 1) {
                    $data['send_emails_to_cam'] = $this->Sfowner_model->send_email_to_cam($_POST['email'], $random_pwd,"");
                }
            }
            if ($type == 'vcam') {
                $this->session->set_flashdata('region_update', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Updated Successfully!</strong> </div>');
                redirect('dashboard/regioncontrol');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Updated Successfully!</strong> </div>');
                redirect('dashboard/manage_account_channel_manager');
            }
        } else {
            $this->session->set_flashdata('msg', "<div style='color:red;'>failed!!</div>");
            redirect('dashboard/manage_account_channel_manager');
        }
    }

    /**
     * Update the contact profile.
     */
    public function updatecontact()
    { 
	    $this->Common_model->check_dashboard_login();
        $this->load->model('Sfcontactsdetails_model');
        $this->load->model('Sfpartners_model');
        $this->load->model('Reseseller_model');
        $contactid = $this->input->post('contactid');
        $contact_email = $this->input->post('contact_email');
        $clientid = $this->session->userdata('client_id');

        if ($this->input->post('partner_division')) {
            $partner_records = $this->Reseseller_model->get_parner_by_id($this->input->post('partner_division'));
            $partner_name = $partner_records->partner_acc_name;
            $partner_id = $partner_records->id;
        } else {
            $partner_arr = explode('##', $this->input->post('partner_name'));
            $partner_id = $partner_arr[2];
            $partner_name = $partner_arr[1];
        }
        $member_details = $this->Common_model->get_data_row('primary_contact_id,secondary_contact_id', 'members_details', array('id' => $contactid));
		
		$member_contact_detail = $this->Common_model->get_data_row('partner_contact_id,member_id', 'tbl_actionplan_partner_contact_detail', array('member_id' => $contactid));
	  
		
        if ($this->input->post('primary_contact_id') == '' || $this->input->post('primary_contact_id') == 0) {
            $p_contact_id = null;
            if ($member_details['primary_contact_id'] != '') {
                $p_contact_id = $member_details['primary_contact_id'];
            }
        } else {
            $p_contact_id = $this->input->post('primary_contact_id');
        }
        if ($this->input->post('secondary_contact_id') == '' || $this->input->post('secondary_contact_id') == 0) {
            $s_contact_id = null;
            if ($member_details['secondary_contact_id'] != '') {
                $s_contact_id = $member_details['secondary_contact_id'];
            }
        } else {
            $s_contact_id = $this->input->post('secondary_contact_id');
        }

        $cmid = $this->Sfpartners_model->edit_partner_view($partner_id); // get cam name and cam id by partner id
        $username = $this->input->post('firstname') . ' ' . $this->input->post('lastname');

        /******************  Update Assign Partners start ********************* */

        $data = array(
            'username' => $username,
            'first_name'          =>  htmlspecialchars($this->input->post('firstname')),
            'last_name'           =>  htmlspecialchars($this->input->post('lastname')),
            'address1'            =>  htmlspecialchars($this->input->post('address1')),
            'email'               =>  htmlspecialchars($this->input->post('email')),
            'address2'            =>  htmlspecialchars($this->input->post('address2')),
            'title'               =>  htmlspecialchars($this->input->post('title')),
            'city'                =>  htmlspecialchars($this->input->post('city')),
            'state'               =>  htmlspecialchars($this->input->post('state')),
            'zipcode'             =>  htmlspecialchars($this->input->post('zipcode')),
            'country'             =>  htmlspecialchars($this->input->post('country')),
            'phoneno'             =>  htmlspecialchars($this->input->post('phoneno')),
            'phoneno2'            =>  htmlspecialchars($this->input->post('phoneno2')),
            'primary_contact_id'  =>  htmlspecialchars($this->input->post('primary_contact_id')),
            'secondary_contact_id' =>  htmlspecialchars($this->input->post('secondary_contact_id')),
            'partner_name'        =>  htmlspecialchars($partner_name),
            'partner_id'          => $partner_id,
            'crm'                 =>  htmlspecialchars($cmid[0]['crm_name']),
            'crm_id' => $cmid[0]['crm_id'],
        );
        $update = $this->Sfcontactsdetails_model->updatecontact($contactid, $data);
		if(!empty($member_contact_detail)){
			$member_contactdata = array(
            'first_name'          =>  htmlspecialchars($this->input->post('firstname')),
            'last_name'           =>  htmlspecialchars($this->input->post('lastname')),
            'address1'            =>  htmlspecialchars($this->input->post('address1')),
            'email'               =>  htmlspecialchars($this->input->post('email')),
            'address2'            =>  htmlspecialchars($this->input->post('address2')),
            'title'               =>  htmlspecialchars($this->input->post('title')),
            'city'                =>  htmlspecialchars($this->input->post('city')),
            'state'               =>  htmlspecialchars($this->input->post('state')),
            'zipcode'             =>  htmlspecialchars($this->input->post('zipcode')),
            'country'             =>  htmlspecialchars($this->input->post('country')),
            'phoneno'             =>  htmlspecialchars($this->input->post('phoneno')),
            'phoneno2'            =>  htmlspecialchars($this->input->post('phoneno2')),
            'primary_contact_id'  =>  htmlspecialchars($this->input->post('primary_contact_id')),
        );
		$this->db->where('partner_contact_id', $member_contact_detail['partner_contact_id']);				
		$this->db->update('tbl_actionplan_partner_contact_detail', $member_contactdata);
		}
        if ($p_contact_id != '') {
            $datass = $this->Salesforce_api_model->insert_update_contact($data, $clientid);
            //echo "<pre>"; print_r($datass); exit;
        }

        $check_email = $this->input->post('check_email');
        if ($check_email > 0) {
            $this->sending_email_to_contact($contactid, $partner_name);

            $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Contact has been updated successfully. </strong> </div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Contact has been updated successfully.</strong> </div>');
        }

        /******************  Update Assign Partners end ********************* */

        redirect('dashboard/manage_partner_contact');
    }

    /**
     * Delete the partner.
     */
    public function delete_partner()
    {    
	   $this->Common_model->check_dashboard_login();
        $this->load->model('../controllers/salesforce/models/Salesforce_api_model');
        $partnerid = $this->input->get('partner_id');
        $this->load->model('Sfpartners_model');

        $clientid = $this->session->userdata('client_id');
        $datass = $this->Salesforce_api_model->delete_sf_account($partnerid, $clientid);
        $delete = $this->Sfpartners_model->delete_partner($partnerid);
        if ($delete) {
            $this->session->set_flashdata('msg', "<div style='color:#a94442; background-color: #f2dede; border-color: #ebccd1; padding: 10px; margin-bottom: 10px;font-weight: 600; border-radius: 4px;border: 1px solid transparent;width: 97%;'>Partner Deleted successfully</div>");
        } else {
            $this->session->set_flashdata('msg', "<div style='color:red;'>Partner Deleting failed!!</div>");
        }
        redirect('dashboard/manage_partners');
    }

    /**
     * Delete the Account Channel Manager.
     */
    public function delete_cam()
    {   
	    $this->Common_model->check_dashboard_login();
        $cam_id = $this->input->get('cam_id');
        $this->load->model('Sfowner_model');
        $id = $cam_id;
        $this->Common_model->delete_records('cam_level1_region', array('cam_id' => $id['id']));
        $this->Common_model->delete_records('cam_level2_region', array('cam_id' => $id['id']));
        $this->Common_model->delete_records('cam_level3_region', array('cam_id' => $id['id']));
        $delete = $this->Sfowner_model->delete_cam($cam_id);
        if ($delete) {
            $this->session->set_flashdata('msg', "<div style='color:red;'>CAM Deleted Successfully!!</div>");
        } else {
            $this->session->set_flashdata('msg', "<div style='color:red;'>CAM Deleting failed!!</div>");
        }
        redirect('dashboard/manage_account_channel_manager');
    }

    /**
     * Delete the Contact.
     */
    public function delete_contact()
    {  
	   $this->Common_model->check_dashboard_login();
        $contactid = $this->input->get('contact_id');
        $partnerid = $this->input->get('partner_id');
        $this->load->model('Sfcontactsdetails_model');
        $delete = $this->Sfcontactsdetails_model->delete_contact($contactid);
        if ($delete) {
            $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Contact has been deleted successfully.</strong> </div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Contact deleted failed.</strong> </div>');
        }
        if ($partnerid) {
            redirect('dashboard/view_partners_contacts/?partner_id=' . $partnerid);
        } else {
            redirect('dashboard/manage_partner_contact');
        }
    }

    /**
     * Get all the regions.
     */
    public function manage_regions()
    {
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Region_model');
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $data['regions'] = $this->Region_model->get_all_region();
            $data['main_content'] = 'fronthand/twitter/manageregion';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
    }

    /**
     * Add a new partner.
     */
    public function addregion()
    {
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $this->load->model('Region_model');
            $region = $this->input->post('region');
            $region_desc = $this->input->post('region_desc');
            $created_at = $this->input->post('created_at');
            $updated_at = $this->input->post('updated_at');
            $clientid = $this->session->userdata('client_id');
            $super_region_id = $this->input->post('super_region_id');
            $data = array(
                'region' => $region, 'super_region_id' => $super_region_id, 'region_desc' => $region_desc, 'created_at' => $created_at, 'updated_at' => $updated_at, 'clientid' => $clientid,
            );
            $add = $this->Region_model->add_region($data);
            if ($add) {
                $this->session->set_flashdata('msg', 'Added Successfully!!');
                redirect('dashboard/manage_regions');
            } else {
                $this->session->set_flashdata('msg', 'failed!!');
                redirect('dashboard/manage_regions');
            }
        } else {
            redirect('manageaudit');
        }
    }

    /**
     * Edit the region.
     */
    public function editregion()
    {
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $this->load->model('Region_model');
            $region_id = $this->input->get('region_id');
            $data['editregion'] = $this->Region_model->get_region_by_id($region_id);
            $data['super_regions'] = $this->Region_model->get_all_super_region();
            $data['main_content'] = 'fronthand/twitter/view_edit_region';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
    }

    /**
     * Update the region.
     */
    public function updateregion()
    {
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $this->load->model('Region_model');
            $region = $this->input->post('region');
            $super_region_id = $this->input->post('super_region_id');
            $region_desc = $this->input->post('region_desc');
            $created_at = $this->input->post('created_at');
            $updated_at = $this->input->post('updated_at');
            $region_id = $this->input->post('region_id');
            $data = array(
                'region' => $region, 'super_region_id' => $super_region_id, 'region_desc' => $region_desc, 'created_at' => $created_at, 'updated_at' => $updated_at,
            );
            $update = $this->Region_model->update_region($region_id, $data);
            if ($update) {
                $this->session->set_flashdata('msg', 'Updated Successfully!!');
                redirect('dashboard/manage_regions');
            } else {
                $this->session->set_flashdata('msg', 'failed!!');
                redirect('dashboard/manage_regions');
            }
        } else {
            redirect('manageaudit');
        }
    }

    /**
     * View page for Add a new partner.
     */
    public function view_addregion()
    {
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $this->load->model('Region_model');
            $data['super_regions'] = $this->Region_model->get_all_super_region();
            $data['main_content'] = 'fronthand/twitter/addregion';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
    }

    /**
     * Delete the region.
     */
    public function deleteregion()
    {
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $this->load->model('Region_model');
            $region_id = $this->input->get('region_id');
            $delete = $this->Region_model->delete_region($region_id);
            if ($delete) {
                $this->session->set_flashdata('msg', 'Region Deleted Successfully!!');
            } else {
                $this->session->set_flashdata('msg', 'Region Deleting failed!!');
            }
            redirect('dashboard/manage_regions');
        } else {
            redirect('manageaudit');
        }
    }

    /**
     * Comprehensive Forcast Repost for 3 years.
     */
    public function forecastreport()
    {
        $session_data = $this->session->all_userdata();
        $cam_session_data = $session_data['all_data'];
        $client_id = $cam_session_data['client_id'];
        $this->load->model('Forecastreport_model');
        $data['partner_with_reports'] = $this->Forecastreport_model->GetPartnerWithReports($client_id);
        $data['main_content'] = 'fronthand/twitter/forecastreport';
        $this->load->view('/includes/template_cam_dashboard', $data);
    }

    /**
     * Settings for the CAM.
     */
    public function cam_settings()
    {
        $this->load->model('Sfowner_model');
        $this->load->model('Sfpartners_model');
        if ($this->session->userdata('cam_login')) {
            $this->load->model('Role_model');
            $this->load->model('Region_model');
            $data['allregions'] = $this->Region_model->get_all_region_new();
            $data['getcambyid'] = $this->Sfowner_model->getcambyid($this->session->userdata('crm_id'));
            $data['main_content'] = 'fronthand/twitter/cam_settings';
            $this->load->view('/includes/template_cam_dashboard', $data);
        } else {
            redirect('manageaudit');
        }
    }

    /**
     * Sending email to the Contact with auto generated password and login url.
     *
     * @return partners names
     */
    public function sending_email_to_contact($insert, $partnerName = null)
    {
        $client_tbl_id = $this->session->userdata('client_id');
        $this->load->model('Sfpartners_model');
        $this->load->model('Email_model');
        $cemail = $this->input->post('email');
        //print_r($cemail);die();
        $caccess = 1;
        $cname = $this->input->post('firstname');
        $memberid = $_GET['id'];
        //    print_r($memberid);die();
        $random_password = uniqid();
        $data = array('password' => md5($random_password), 'is_active' => $caccess);
        $this->Common_model->update_data('members_details', array('password' => md5($random_password)), array('id' => $insert));
        $mailing_data = $this->Email_model->get_emails_by_mailtype($client_tbl_id, 2);

        $data['dynamic_mail_data'] = $mailing_data;
        $data['firstname'] = $cname;
        $data['random_password'] = $random_password;
        $data['emailuser'] = $cemail;
        $data['partner_name'] = $partnerName;
        //echo "Email is send to your email id with your password";
        $this->load->view('emailtemplate/account_created_user', $data, true); // this will return you HTML
    }

    /**
     * Restrict the access of the contact.
     *
     * @return partners names
     */
    public function sending_noaccess_to_contact()
    {
        $client_tbl_id = $this->session->userdata('client_id');
        $this->load->model('Sfpartners_model');
        $caccess = $this->input->get('caccess');
        $id = $this->input->get('id');
        $data = array('is_active' => $caccess);
        $update = $this->Sfpartners_model->sending_noaccess_to_contact($id, $data);
        if ($update != 0) {
            $update_plan = "update report_created_by set is_active='0' where clientid='" . $client_tbl_id . "'  and memberid ='" . $id . "' ";
           $this->db->query($update_plan);
			
            echo 'Plan Deactivate from cam dashboard';
        } else {
            echo 'Plan Activate from cam dashboard';
        }
    }

    public function uiadd_contact()
    {   
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $client_id = $this->session->userdata('client_id');
        $this->load->model('Sfowner_model');
        $this->load->model('Role_model');
        $this->load->model('Region_model');
        $this->load->model('Sfpartners_model');
        $data['allpartners'] = $this->Sfpartners_model->get_groupby_partner_name_($client_id);

        $data['is_checked'] = $this->Common_model->get_data_row('is_checked', 'client_pl_steps', array('clientid' => $client_id));

        $partner_id = $this->uri->segment(3);
        $data['partner_record'] = $this->Sfpartners_model->get_partner_with_id($partner_id);
        $data['allregions'] = $this->Region_model->get_all_region();
        $data['allcam'] = $this->Sfowner_model->get_sfowner($client_id,$cam_roles="");
        $data['main_content'] = 'fronthand/twitter/uiaddcontact';
        $this->load->view('/includes/template_new', $data);
    }

    /**
     * Insert New contact profile.
     */
    public function addnewcontact()
    {  
	    $this->Common_model->check_dashboard_login();
        $client_id = $this->session->userdata('client_id');
        $this->load->model('Sfcontactsdetails_model');
        $this->load->model('../controllers/salesforce/models/Salesforce_api_model');
        $this->load->model('Sfpartners_model');
        $this->load->model('Reseseller_model');
        /*$camdata = $this->input->post('assignpartner');
        $cam = explode("##",$camdata);
        $cam_name = $cam[1];
        $cam_id = $cam[0];
         */

        if ($this->input->post('partner_division')) {
            $partner_records = $this->Reseseller_model->get_parner_by_id($this->input->post('partner_division'));
            $partner_name = $partner_records->partner_acc_name;
            $partner_id = $partner_records->primary_partner_id;
            $partner_ids = $partner_records->id;
            $cmid = $partner_records->crm_id;
        } else {
            $partner = $this->input->post('partner_name');
            $partner_data = $this->Common_model->get_data_row('id,primary_partner_id,partner_acc_name', 'partners_accounts', array('id' => $partner));
            $partner_name = $partner_data['partner_acc_name'];
            $partner_id = $partner_data['primary_partner_id'];
            $partner_ids = $partner_data['id'];
        }
        if ($this->input->post('primary_contact_id') == '') {
            $p_contact_id = null;
        } else {
            $p_contact_id = $this->input->post('primary_contact_id');
        }
        if ($this->input->post('secondary_contact_id') == '') {
            $s_contact_id = null;
        } else {
            $s_contact_id = $this->input->post('secondary_contact_id');
        }
        $cmid = $this->Sfpartners_model->edit_partner_view($partner_ids); // get cam name and cam id by partner id
        $username = $this->input->post('firstname') . ' ' . $this->input->post('lastname');

        $data = array(
		'username'             => htmlspecialchars($username),
		'first_name'           => htmlspecialchars($this->input->post('firstname')),
		'last_name'            => htmlspecialchars($this->input->post('lastname')),
		'email'                => htmlspecialchars($this->input->post('email')),
		'address1'             => htmlspecialchars($this->input->post('address1')),
		'address2'             => htmlspecialchars($this->input->post('address2')),
		'title'                => htmlspecialchars($this->input->post('title')),
		'city'                 => htmlspecialchars($this->input->post('city')),
		'state'                => htmlspecialchars($this->input->post('state')),
		'zipcode'              => htmlspecialchars($this->input->post('zipcode')),
		'country'              => htmlspecialchars($this->input->post('country')),
		'clientid'             => $client_id,
		'phoneno'              => htmlspecialchars($this->input->post('phoneno')),
		'phoneno2'             => htmlspecialchars($this->input->post('phoneno2')),
		'partner_division'     => htmlspecialchars($this->input->post('partner_division')),
		'partner_name'         => htmlspecialchars($partner_name),
		'partner_id'           => htmlspecialchars($this->input->post('partner_name')),
		'partner_division'     => htmlspecialchars($this->input->post('partner_division')),
		'primary_contact_id'   => htmlspecialchars($p_contact_id),
		'secondary_contact_id' => htmlspecialchars($s_contact_id),
		'crm'                  => htmlspecialchars($cmid[0]['crm_name']),
		'crm_id'               => $cmid[0]['crm_id'],
		'is_active' => 1,
        );
        $insert = $this->Sfcontactsdetails_model->insertcontact($data);
        if ($p_contact_id != '') {
            $this->Salesforce_api_model->insert_update_contact($data, $client_id);
        }
        $check_email = $this->input->post('check_email');

        if ($check_email > 0) {
            $this->sending_email_to_contact($insert, $partner_name);
            //$update = $this->Sfpartners_model->sending_email_to_contact($memberid, $data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Contact has been added successfully. </strong> </div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Contact has been added successfully. </strong> </div>');
        }

        redirect('dashboard/manage_partner_contact');

        //redirect('dashboard/view_partners_contacts/?partner_id='.$partner_ids.'');

        /* else
    {
    $this->session->set_flashdata('msg', 'failed!!');
    redirect('dashboard');
    } */
    }

    /**
     * Select partner.
     */
    public function select_partner()
    {
        //echo "hello";
        $this->load->model('Sfpartners_model');
        $this->load->model('Sfowner_model');
        $data['main_content'] = 'fronthand/ui/select_partner';
        $data['sfpartners'] = $this->Sfpartners_model->get_all_partners_detail();
        $data['sfowner'] = $this->Sfowner_model->get_sfowner();
        $this->load->view('/includes/template_ui', $data);
        //redirect("member/viewpartners?cache_cleared=12");
    }

   
    /**
     * Check the partner and email for the user if already exists or not.
     */
    public function check_partner_org()
    {
        $this->load->model('Sfcontactsdetails_model');
        $partnerid = $this->input->get('partnerid');
        $emailuser = $this->input->get('email');
        $partner = $this->input->get('partner');
        $domainname = $this->input->get('domainname');
        if (!preg_match('@^[hf]tt?ps?://@', $domainname)) {
            $domainname = 'http://' . $domainname;
        }
        if (($emailuser != '') && ($partner != '')) {
            $this->load->model('Sfpartners_model');
            $partner_status = $this->Sfpartners_model->check_partner_approve($partnerid); // check the partner is approved or not
            if (empty($partner_status)) {
                $get_partner_cam = $this->Sfpartners_model->get_partner_cam($partnerid, $partner);
                $select_cam_email = $this->Sfpartners_model->get_cam_email($get_partner_cam);
                if ($select_cam_email) {
                    $cam = explode('##', $select_cam_email);
                    $camemail = $cam[0];
                    $camname = $cam[1];
                    $data['camname'] = $camname;
                    $data['partner'] = $partner;
                    $data['camemail'] = $camemail;
                    $this->load->view('emailtemplate/request_to_approve_partner', $data, true); // this will return you html data as message
                }
            }
            $query = $this->Sfcontactsdetails_model->match_partner_email($partnerid, $emailuser, $partner);
            $check_login_active = $query[0]->is_active;
            $member_name = $query['0']->username;
            if ($member_name != '') {
                $member_name = $query['0']->username;
            } else {
                $member_name = 'User';
            }
            if ($check_login_active == 1) { //match_partner_email
                $random_password = uniqid();
                $data = array('password' => md5($random_password));
                $update = $this->Sfcontactsdetails_model->change_contact_password($emailuser, $data);
                if ($update) {
                    $data['name'] = 'User';
                    $data['emailuser'] = $emailuser;
                    $data['random_password'] = $random_password;
                    $data['username'] = $emailuser;
                    $data['emailuser'] = $emailuser;
                    $data['firstname'] = $member_name;
                    $data['random_password'] = $random_password;
                    $message = $this->load->view('emailtemplate/account_created_user', $data, true); // this will return
                }
                echo '<span style="color:green">Congratulations there is a match !  Look in your email box for a password and link to log in and get started</span>';
            } else { // if not matched the partner name and email
                $data['name'] = 'User';
                $data['partnerid'] = $this->input->get('partnerid');
                $data['emailuser'] = $emailuser;
                $data['firstname'] = $member_name;
                $data['partner'] = $this->input->get('partner');
                $data['domainname'] = $this->input->get('domainname');
                $datapartner = array('partnerid' => $partnerid, 'emailuser' => $emailuser, 'partner' => $partner, 'domainname' => $domainname);
                $this->session->set_userdata($datapartner);
                $message = $this->load->view('emailtemplate/inactive-account', $data, true); // this will return you html data as message
                echo 'Not matched';
            }
        } elseif (($emailuser != '') && ($partner == '')) {
            $query = $this->Sfcontactsdetails_model->match_partner_onlyemail($emailuser);
            if (empty($query)) { //match_partner_email
                echo '<span style="color:red">Your Email is not matched in our records please select your Partner Organisation and create new account!</span>';
            }
        } else { //if partner and email is not selected!
            echo '<span style="color:red">Please enter your Email and select your Partner Organisation!</span>';
        }
    }

    /**
     * find partner with only email id.
     */
    public function findpartnerwithemail()
    {
        $this->load->model('Sfpartners_model');
        $email = $this->input->get('email');
        $findpartner = $this->Sfpartners_model->findpartnerwithemail($email);
        echo $findpartner;
    }

    // super region code by ravi rana
    public function manage_super_regions()
    {
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Region_model');
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $data['super_regions'] = $this->Region_model->get_all_super_region();
            $data['main_content'] = 'fronthand/twitter/super_region/manageregion';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
    }

    public function view_superregion()
    {
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $this->load->model('Region_model');
            $data['main_content'] = 'fronthand/twitter/super_region/addregion';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
    }

    public function add_superregion()
    {
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $this->load->model('Region_model');
            $region = $this->input->post('region');
            $region_desc = $this->input->post('region_desc');
            $created_at = $this->input->post('created_at');
            $updated_at = $this->input->post('updated_at');
            $clientid = $this->session->userdata('client_id');
            $data = array(
                'region' => $region, 'region_desc' => $region_desc, 'created_at' => $created_at, 'updated_at' => $updated_at, 'clientid' => $clientid,
            );
            $add = $this->Region_model->add_super_region($data);
            if ($add) {
                $this->session->set_flashdata('msg', 'Added Successfully!!');
                redirect('dashboard/manage_super_regions');
            } else {
                $this->session->set_flashdata('msg', 'failed!!');
                redirect('dashboard/manage_super_regions');
            }
        } else {
            redirect('manageaudit');
        }
    }

    public function edit_superregion()
    {
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $this->load->model('Region_model');
            $region_id = $this->input->get('region_id');
            $data['editregion'] = $this->Region_model->get_super_region_by_id($region_id);
            $data['main_content'] = 'fronthand/twitter/super_region/view_edit_region';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
    }

    public function update_superregion()
    {
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $this->load->model('Region_model');
            $region = $this->input->post('region');
            $region_desc = $this->input->post('region_desc');
            $created_at = $this->input->post('created_at');
            $updated_at = $this->input->post('updated_at');
            $region_id = $this->input->post('region_id');
            $data = array(
                'region' => $region, 'region_desc' => $region_desc, 'created_at' => $created_at, 'updated_at' => $updated_at,
            );
            $update = $this->Region_model->update_super_region($region_id, $data);
            if ($update) {
                $this->session->set_flashdata('msg', 'Updated Successfully!!');
                redirect('dashboard/manage_super_regions');
            } else {
                $this->session->set_flashdata('msg', 'failed!!');
                redirect('dashboard/manage_super_regions');
            }
        } else {
            redirect('manageaudit');
        }
    }

    public function delete_superregion()
    {
        if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
            $this->load->model('Region_model');
            $region_id = $this->input->get('region_id');
            $delete = $this->Region_model->delete_super_region($region_id);
            if ($delete) {
                $this->session->set_flashdata('msg', 'Region Deleted Successfully!!');
            } else {
                $this->session->set_flashdata('msg', 'Region Deleting failed!!');
            }
            redirect('dashboard/manage_super_regions');
        } else {
            redirect('manageaudit');
        }
    }

    public function get_region_value()
    {
        $super_region_id = $_POST['super_region_id'];
        $this->load->model('Region_model');
        $data_region = $this->Region_model->get_all_region_by_id($super_region_id);
        if (is_array($data_region) and count($data_region) > 0) {
            echo "<option value=''>Select Region</option>";
            foreach ($data_region as $data_region_val) {
                echo "<option value='" . $data_region_val['id'] . "'>" . $data_region_val['region'] . '</option>';
            }
        }
    }

    // function for display performance records
    public function product_performacne()
    {
        if ($this->session->userdata('dealer_login')) {
            $clientid = $this->session->userdata('client_id');
            $this->load->model('Sfpartners_model');
            $this->load->model('Forecast_year_model');
            $query = $this->Forecast_year_model->GetForecastYear($clientid);
            $data['forecast_year'] = $query;
            $data['partners'] = $this->Sfpartners_model->get_all_partners_having_access($clientid); // get partner
            $data['main_content'] = 'fronthand/twitter/cam_forecast/performance/product-performance';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
    }

    // function for  performance csv
    public function product_performance_upload(){
        if($this->session->userdata('dealer_login')){
            $clientid = $this->session->userdata('client_id');
            $this->load->model('Sfpartners_model');
            $this->load->model('Forecast_year_model');
            $this->load->model('Cam_model');
            $firstyear = $this->start_year_val;
            $start_mth = $this->start_month;
            $start_one = $this->start_year;
            $end_mth = $this->end_month;
            $months1 = $this->Common_model->months(0, $start_mth);
            $months2 = $this->Common_model->months(0, $end_mth);
            for($x = 0; $x < 3; ++$x){
                $firstyear2 = $firstyear + 1 + $x;
                if($start_mth == 1){
                    $firstyear2 = $firstyear + $x;
                }
                $data['range'][] = '<li> Year ' . ($x + 1) . ' : ' . $months1 . ' - ' . ($firstyear + $x) . ' to ' . $months2 . ' - ' . ($firstyear2) . '</li>';
            }
            $sql = "SELECT id, sheet_name, insert_date, CONCAT('" . $this->config->item('base_url1') . "uploads/performance/', sheet_name) as link FROM actual_complete_report WHERE  datatype = 'forcast' AND client_id= $clientid ";
            $data['get_uploaded_xls'] = $this->Common_model->get_conditional_array($sql);
            $transaction_id_status = $this->input->post('transaction_id_status');
            if($transaction_id_status != ''){
                $this->Common_model->update_data('admin_clients', array('transaction_id_status' => $transaction_id_status), array('client_id' => $clientid));
            }

            $data['status'] = $this->Common_model->get_data_row('transaction_id_status', 'admin_clients', array('client_id' => $clientid));
            $data['main_content'] = 'upload_center/sales/product-performance-upload';
            $this->load->view('/includes/template_new', $data);
        }else{
            redirect('manageaudit');
        }
    }

    // function for upload performance reports:
    public function upload_performance_xls(){
        $this->load->model('Cam_model');
        $url_segment = explode('.', $_SERVER['HTTP_HOST']);
        if($url_segment[0] != 'www'){
            $client_uniquename = $url_segment[0];
        }else{
            $client_uniquename = $url_segment[1];
        }
        $clientid = $this->session->userdata('client_id');
        $this->load->model('Sfpartners_model');
        $config['upload_path'] = upload_performance_xls;
        $config['allowed_types'] = '*';
        $config['max_size'] = '100000';
        $explode_filename = explode(' (', $_POST['fiscal_year_start']);
        $explode_filename = explode('FY', $_POST['fiscal_year_start']);
        $file_name_year = $explode_filename[1];
        $explode_filename = explode(' (', $file_name_year);
        $explode_filenames = $explode_filename[0];
        $config['file_name'] = $client_uniquename . '_salesupload_' . $explode_filenames;
        $this->load->library('upload', $config);
		
        if(!$this->upload->do_upload('logo')){
            $error = array('error' => $this->upload->display_errors());
            print_r(json_encode($error));
            die();
            $image_url = '';
        }else{
            $allowed_ext = array('.xlsx', '.xls');
            $data = array('upload_data' => $this->upload->data());
            $extension = $data['upload_data']['file_ext'];
            $image_path = $data['upload_data']['file_name'];
            $image_url = $this->config->item('base_url1') . 'uploads/performance/' . $image_path;
            if (!in_array($extension, $allowed_ext)) {
                unlink(performance_file . $image_path);
                $error = array('error' => 'Invalid file extension ! ');
                print_r(json_encode($error));
                die();
            }

            /* check if transaction ID chaekbox checked */
           $sheet_detail = $this->Common_model->sheet_detail(performance_file . $image_path);
            $count_row = $sheet_detail['row'][0] - 1;
            $actual_data = $this->Common_model->readexcelsheet(performance_file . $image_path, $count_row);
			
			// Sheet Heading for compare column index
            $valid = array('partnername', 'primarypartnerid#*', 'transactionid#*', 'transactiondate*', 'salesstages*', 'secondarypartnerid#', 'dealname', 'productname', 'productid#', 'unit', 'revenueperunit', 
			'totaltransaction$value', 'manualtransactiondateoverride', 'dealtype', 'opportunitytype', 'partnertype',
			);			
		
		
            $actaul = array_map(function ($value) {return strtolower(str_replace(' ', '', $value));}, array_values($actual_data['csv_data'][9]));
			$diff = $this->Common_model->validateHead($valid, array_filter($actaul));
            if (!empty($diff)) {
                echo json_encode($diff);
                exit;
            }
			
			// Arrange uploaded file data
			$arrange_data = $this->Cam_model->arrange_actual_data($actual_data['csv_data'], $clientid, 0,'');
			
			// Insert Partner Actuals Data
            $this->Cam_model->insert_actual_data($arrange_data['valid'], $image_path, $clientid, $arrange_data,'');
        }
		return $image_url;
    }

     // delete actuall report records
    public function del_actuall_report()
    {
        $id = $this->input->post('id');
        $file_name = $this->Common_model->get_data_row('sheet_name', 'actual_complete_report', array('id' => $id, 'datatype' => 'forcast'));
        $sheet_name = $file_name['sheet_name'];
        $file_path = BASE_ABS_PATH . '/uploads/performance/' . $sheet_name;
        unlink($file_path);
        $this->Common_model->delete_records('actual_complete_report', array('id' => $id));
        echo $this->db->last_query();
    }

    // End super region code by ravi rana
    //<!-- Author : alok jha, To add Channel Executive Link , Starts-->
    public function ce_report()
    {
        if ($this->session->userdata('cam_login')) {
            $data = $this->get_all_patners_data();
            $data['main_content'] = 'fronthand/twitter/cam_forecast/partner/ce_report';
            $this->load->view('/includes/template_cam_dashboard', $data);
        } else {
            redirect('manageaudit');
        }
    }

    public function get_all_patners_data()
    {
        $client_id = $this->session->userdata('cam_clientid');
        $this->load->model('Sfpartners_model');
        if ($this->session->userdata('cam_login')) {
            $data = array();
            $this->load->model('Assessmentmodel_model');
            $this->load->model('manage_topics_model');
            $allPatnerId = $this->Sfpartners_model->get_patners_client($client_id);
            for ($x = 0; $x < count($allPatnerId); ++$x) {
                $memberid = $allPatnerId[$x]['memberid'];
                $data['memberid'][$x] = $memberid;
                $data['partnerid'][$x] = $allPatnerId[$x]['partner_id'];
                $data['patnername'][$x] = $this->Sfpartners_model->partner_record_with_id($allPatnerId[$x]['partner_id'], $client_id)->partner_acc_name;
                $data['report'][$x] = $this->get_assessment_data($client_id, $memberid);
            }

            return $data;
        } else {
            redirect('manageaudit');
        }
    }

    public function get_assessment_data($client_id, $memberid)
    {
        $assessment_report = $this->Assessmentmodel_model->get_assessment_report($client_id);
        $assessment = $this->Assessmentmodel_model->get_topic('assessmentAns', $client_id, $memberid);
        $assessment1 = json_decode($assessment[0]['alldata'], true);
        $topic = $assessment1['topic'];
        $category = $assessment1['category'];
        $assessment = $assessment[0]['selected_ans'];
        $assessment = json_decode($assessment, true);
        $total_question = count($assessment[2]);
        $assessment_per = array();
        $graduate_arr = array();
        $topic_assessment = array();
        $category_assessment = array();
        foreach ($assessment[2] as $k => $m_assess) {
            $assessment_per[] = (count($m_assess) * 100) / $m_assess['total'];
            $topic_assessment[$topic[$k][0]][] = (count($m_assess) * 100) / $m_assess['total'];
            $category_assessment[$category[$k][0]][] = (count($m_assess) * 100) / $m_assess['total'];
        }
        foreach ($assessment[3] as $k => $p_assessment) {
            ///formula is selected user index of ans / total ans
            $graduate_arr[] = ($p_assessment[0] * 100) / $p_assessment['total'];
            $topic_assessment[$topic[$k][0]][] = ($p_assessment[0] * 100) / $p_assessment['total'];
            $category_assessment[$category[$k][0]][] = ($p_assessment[0] * 100) / $p_assessment['total'];
        }
        foreach ($assessment[0] as $k => $o_assessment) {
            $graduate_arr[] = ($o_assessment[0] * 100) / $o_assessment['total'];
            $topic_assessment[$topic[$k][0]][] = ($o_assessment[0] * 100) / $o_assessment['total'];
            $category_assessment[$category[$k][0]][] = ($o_assessment[0] * 100) / $o_assessment['total'];
        }
        foreach ($assessment[1] as $k => $ml_assessment) {
            ///formula is selected user index of ans / total ans
            $graduate_arr[] = ($ml_assessment[0] * 100) / $ml_assessment['total'];
            $topic_assessment[$topic[$k][0]][] = ($ml_assessment[0] * 100) / $ml_assessment['total'];
            $category_assessment[$category[$k][0]][] = ($ml_assessment[0] * 100) / $ml_assessment['total'];
        }
        $topic_assessment_avg = array_map('user_avg_calculator', $topic_assessment);
        $topic_assessment_avg = array_map('user_avg_calculator', $topic_assessment);
        $category_avg = array_map('user_avg_calculator', $category_assessment);
        $current_section = 'assessment';
        $table = $this->config->item($current_section);
        $this->load->model('manage_topics_model');
        $this->load->model('Manage_question_model');
        $this->load->model('assessmentModel_model');
        $table = $table['topic'];
        $data['topics'] = $this->manage_topics_model->manage_topics($client_id, $table);
        $data['categories'] = $this->manage_topics_model->manage_topics($client_id, 'assessmentCategory');
        $data['user_topics'] = $topic_assessment_avg;
        $data['user_category'] = $category_avg;
        $data['step_name'] = '';
        $data['assessment_report'] = $assessment_report;
        $avg_topic_stats = $this->assessmentModel_model->get_avgTopicStats($client_id);
        $avg_category_stats = $this->assessmentModel_model->get_avgCategoryStats($client_id);
        $all_avg_cat = array();
        $data_string = '';
        foreach ($avg_category_stats as $cat_stats) {
            $all_avg_cat[$cat_stats['categoryid']] = round($cat_stats['total_score'] / $cat_stats['total_category']);
        }
        $data_string = rtrim($data_string, ',');
        $data['category_string'] = $data_string;
        $data['all_category'] = $all_avg_cat;
        $data_string = '';
        $all_avg_topic = array();
        foreach ($avg_topic_stats as $cat_stats) {
            $all_avg_topic[$cat_stats['topicid']] = round($cat_stats['total_score'] / $cat_stats['total_category']);
        }
        $data_string = rtrim($data_string, ',');
        $data['all_topic'] = $all_avg_topic;

        return $data;
    }

    public function patner_report()
    {
        $client_id = $this->session->userdata('cam_clientid');
        $memberid = $this->uri->segment(4);
        $data = array();
        $this->load->model('Sfpartners_model');
        $this->load->model('Resseller_steps_model');
        $this->load->model('Assessmentmodel_model');
        $p_id = $this->Resseller_steps_model->get_partner_main_id($memberid);
        $data['patnername'] = $this->Sfpartners_model->partner_record_with_id($p_id, $client_id)->partner_acc_name;
        $data = $this->get_assessment_data($client_id, $memberid);
        $data['main_content'] = 'fronthand/twitter/cam_forecast/partner/member_report';
        $this->load->view('/includes/template_cam_dashboard', $data);
    }

    public function ce_category()
    {
        if ($this->session->userdata('cam_login')) {
            $data = $this->get_all_patners_data();
            $data['main_content'] = 'fronthand/twitter/cam_forecast/partner/ce_category';
            $this->load->view('/includes/template_cam_dashboard', $data);
        } else {
            redirect('manageaudit');
        }
    }

    public function ce_topic()
    {
        if ($this->session->userdata('cam_login')) {
            $data = $this->get_all_patners_data();
            $data['main_content'] = 'fronthand/twitter/cam_forecast/partner/ce_topic';
            $this->load->view('/includes/template_cam_dashboard', $data);
        } else {
            redirect('manageaudit');
        }
    }

    //to find the availability if member is already active with partner
    public function member_availability()
    {
        $member = $this->input->post('member');
        $contact_id = $this->input->post('contact_id');
        $primary_contact_id = $this->input->post('primary_contact_id');
        $secondary_contact_id = $this->input->post('secondary_contact_id');
        $prtner = $this->input->post('partner_name');
        $partner_arr = explode('##', $prtner);
        $partners_id = $this->input->post('partner_name');
        $partner_id = $partners_id;
        $this->load->model('Sfcontactsdetails_model');
        $member_id = $_POST['member_id'];

        $arr = $this->Sfcontactsdetails_model->member_availability($member, $member_id, $primary_contact_id, $secondary_contact_id, $partner_id);

        if (is_array($arr) && count($arr) > 0) {
            $data['msg'] = 'Member is already active';
            echo json_encode($data);
            exit;
        } else {
            // $res = $this->Sfcontactsdetails_model->inactive_member($member,$partner_id);
            // if(count($res)>0){
            // $data['msg'] ='Request already sent';
            // echo json_encode($data);
            // exit;
            // }else{
            $data['msg'] = '0';
            //}
        }

        $unique_contact = $this->Sfcontactsdetails_model->unique_member_contact($contact_id, $member_id, $primary_contact_id, $secondary_contact_id);
        //debug($unique_contact);die;
        if (is_array($unique_contact) and count($unique_contact) > 0) {
            $data['msg'] = '0';
            echo json_encode($data);
            exit;
        } else {
            $data['msg'] = 2;
        }
        echo json_encode($data);
    }

    public function check_cam_login()
    {
        echo $this->session->userdata('login_with_cam');
        $this->session->set_userdata('login_with_cam', 'yes');
        $actual_cam_email = $this->input->post('actual_cam_email');
        $this->session->set_userdata('actual_cam_email', $actual_cam_email);
    }

    public function check_login_cam()
    {
        $cam_login_check = $this->session->userdata('cam_login');

        if ($cam_login_check == 1) {
            $this->session->userdata('login_with_cam');
            $this->load->model('Users_model');
            $this->load->model('Tracking_model');
            $members_info = $this->Users_model->get_members_info($_GET['member_id']);
            $this->load->model('Reseseller_model');
            $this->load->model('Resseller_steps_model');
            $members_info_detail = $members_info['0'];
            $members_info_detail['member_id'] = $members_info_detail['id'];
            $members_info_detail['client_id'] = $members_info_detail['clientid'];

            $members_info_detail['crm_id'] = $this->session->userdata('crm_id');
            $members_info_detail['reseller_login'] = true;
            $members_info_detail['api_logged_in'] = 0;

            $this->session->set_userdata($members_info_detail);
            $this->session->set_userdata('login_with_cam', 'yes');
            $partnername = $this->Reseseller_model->get_partner_name($members_info_detail['partner_id']);
            $is_sidebar = $this->Reseseller_model->get_partner_sidebar_openorclosed($members_info_detail['partner_id']);
            $this->session->set_userdata('partnername', $partnername);
            $this->session->set_userdata('is_sidebar', $is_sidebar);
			 $this->session->set_userdata('cam_task_manager',0);
            $this->session->set_userdata('url_type', '');
			$this->session->unset_userdata('prepublish_id');   
			if($this->session->userdata('is_saml') !=1){
			$this->session->unset_userdata('is_saml');
			}
			$this->config->set_item('cookie_path', '/');
			$this->input->set_cookie('gdpr_value','1', time() + (86400 * 30)); //Cookie set For 1 day
			$post_data['is_enable']  ='1';
	        $post_data['page_id']    = 3;
	        $this->Common_model->insert_gdpr_data('contact_login',$members_info_detail['member_id'],$post_data,'', $members_info_detail['client_id']);
			
            $forecast_year_data = $this->Resseller_steps_model->GetForecastYearRecords($members_info_detail['clientid']);
            $fiscal_year = $forecast_year_data['fiscal_year'];
            $this->session->set_userdata('new_forcast_year', $fiscal_year);

            //$selected_report_data = $this->Reseseller_model->get_member_selected_product($members_info_detail['clientid'],$members_info_detail['partner_id']);
            //$this->session->set_userdata('selectedproduct',$selected_report_data['selectedproduct']);

            $is_cam_login = true;
            $this->session->set_userdata('is_cam_login', $is_cam_login);
            $this->session->unset_userdata('is_bussiness_login');

            $this->session->userdata('new_forcast_year');
            $client_steps = $this->Common_model->get_data_row('steps', 'client_pl_steps', array('clientid' => $members_info_detail['client_id']));
            $partner_app_data = $this->Tracking_model->generate_partner_nav_steps($client_steps);
            $steps = json_decode($client_steps['steps'], true);
			
		$this->get_steps = $this->Common_model->get_scorecard_steps($this->session->userdata('clientid'),"frontend");
		$this->parent_steps  = $this->get_steps['parent_step']; 
		$this->get_started   = $this->get_steps['get_started']; 		
    
        $this->Users_model->ridirect_home_page($this->parent_steps,$this->get_started);
			
        } else {
            redirect('camdashboard/camdashboard');
        }
    }

    /**
     * Insert New Super Partner.
     */
    public function add_superpartner()
    {
        $this->Common_model->check_dashboard_login();
        $this->load->model('Sfpartners_model');
        if ($this->input->post()) {
            $data = array(
                'client_id' => $this->session->userdata('client_id'),
                'name' => $this->input->post('super_partner_name'),
                'super_partnersid' => $this->input->post('super_partnersid'),
            );
            $super_ptnr_chk_uid = $this->input->post('super_partnersid');
            //echo '<pre>'; print_r($super_ptnr_chk_uid); echo '</pre>'; die();

            $check_super_id = $this->Sfpartners_model->check_if_exist_super_partner_id('super_partners', 'super_partnersid', $super_ptnr_chk_uid);

            if ($check_super_id != '') {
                $this->session->set_flashdata('msgidregistered', 'This ID is already registered !!!');
                redirect('dashboard/add_superpartner');
            } else {
                $insert = $this->Sfpartners_model->insertsuperpartner($data);
                if ($insert) {
                    $this->session->set_flashdata('msg', 'Super Partner added successfully');
                    redirect('dashboard/manage_superpartner');
                } else {
                    $this->session->set_flashdata('msg', 'failed!!');
                    redirect('dashboard/manage_superpartner');
                }
            }
        }
        $data['main_content'] = 'fronthand/twitter/uiadd_super_partner';
        $this->load->view('/includes/template_new', $data);
    }

    public function manage_superpartner()
    {
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Sfpartners_model');
        $data['super_partner_data'] = $this->Sfpartners_model->get_data_array_super_partnersonly('*', 'super_partners', array('client_id' => $this->session->userdata('clientid')));
        //echo '<pre>'; print_r($data); echo '</pre>';

        $data['main_content'] = 'fronthand/twitter/uimanage_superpartner';
        $this->load->view('/includes/template_new', $data);
    }

    /**
     * View all Super Partner.
     */
    public function view_superpartners()
    {
        $this->load->model('Sfpartners_model');
        $data['get_Spartner_data'] = $this->Sfpartners_model->get_spartner_by_id($_GET['Spartner_id'], $this->session->userdata('client_id'));
        $data['all_partner_data'] = $this->Sfpartners_model->get_all_sfpartners_having_access();
        $data['main_content'] = 'fronthand/twitter/view_superpartner_partners';
        $this->load->view('/includes/template_new', $data);
    }

    /**
     * Save Partners by Super partners.
     */
    public function save_superpartner()
    {   
	    $this->Common_model->check_dashboard_login();
        $this->load->model('Sfpartners_model');
        $update = $this->Sfpartners_model->save_superpartner($this->session->userdata('client_id'));
        if ($update) {
            $this->session->set_flashdata('msg', 'Partners Added Successfully!!');
            redirect('dashboard/manage_superpartner');
        } else {
            $this->session->set_flashdata('msg', 'failed!!');
            redirect('dashboard');
        }
    }

    /**
     * Edit Super partners.
     */
    public function edit_super_partner($id)
    {
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        //echo $id;
        //$id=$this->uri->segment(2);
        $this->load->model('Sfpartners_model');
        $data['editdata'] = $this->Sfpartners_model->get_sprpartner_by_id($id, $this->session->userdata('client_id'));
        $data['main_content'] = 'fronthand/twitter/edit_super_partner';
        $this->load->view('/includes/template_new', $data);
    }

    /**
     * Update Super partners.
     */
    public function update_superpartner()
    {   
	   $this->Common_model->check_dashboard_login();
        $client_id = $this->session->userdata('client_id');

        $this->load->model('Sfpartners_model');
        if ($this->input->post('update_id')) {
            $id = $this->input->post('update_id');
            //echo '<pre>'; print_r($id);  echo '</pre>';
            $data = array(
                'name' => $this->input->post('super_partner_name'),
                'super_partnersid' => $this->input->post('super_partnersid'),
            );

            $super_ptnr_chk_uid = $this->input->post('super_partnersid');
            $check_super_id = $this->Sfpartners_model->check_if_exist_edit_spartner('super_partners', 'super_partnersid', $super_ptnr_chk_uid, $id);

            if ($check_super_id) {
                $data['editdata'] = $this->Sfpartners_model->get_spartner_by_id($id, $this->session->userdata('client_id'));
                $data['error_message'] = '<div class="alert alert-error fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Super Partner ID already exists!</strong> </div>';
                $data['main_content'] = 'fronthand/twitter/edit_super_partner';
                $this->load->view('/includes/template_new', $data);

                return false;
            } else {
                $update = $this->Sfpartners_model->update_superpartner($id, $data);

                if ($update) {
                    $this->session->set_flashdata('updatemsg', 'Super Partner updated successfully');
                    redirect('dashboard/manage_superpartner');
                }
            }
        }
    }

    /**
     * Delete Super partners.
     */
    public function delete_super_partner($deleteid)
    {    
	   $this->Common_model->check_dashboard_login();
        $this->load->model('Sfpartners_model');
        $delete = $this->Sfpartners_model->delete_super_partner($deleteid);
        if ($delete) {
            $this->session->set_flashdata('msgspdelete', 'Super Partner deleted successfully');
            redirect('dashboard/manage_superpartner');
        } else {
            $this->session->set_flashdata('msg', 'failed!!');
            redirect('dashboard');
        }
    }

    /**
     * Partners Records by partner id.
     */
    public function get_databy_partner_id()
    {
        $this->load->model('Sfpartners_model');
        $partner_id = $this->input->post('partner_id');
        $all_data = $this->Sfpartners_model->edit_partner_view($partner_id);
        if ($all_data[0]['partner_division'] == '') {
            echo $all_data[0]['city'];
        } else {
            echo $all_data[0]['partner_division'];
        }
    }

    /**
     * Assign partner New Super Partner by select box.
     */
    public function updateassign_Spartner()
    {
        $this->load->model('Sfpartners_model');
        $update = $this->Sfpartners_model->updateassign_Spartner($this->input->post('Spertnerid'), $this->input->post('pertnerid'));
        if ($update) {
            echo 'Super partner Updated';
        } else {
            echo 'Super partner Not Updated';
        }
    }

    public function check_ptnrname_ptnr_division()
    {
        $this->load->model('Sfpartners_model');
        $partner_name = $this->input->post('partner_name');
        $partner_division = $this->input->post('partner_division');
        $data = $this->Sfpartners_model->check_ptnrname_ptnr_division($partner_name, $partner_division, $this->session->userdata('client_id'));
        if ($data) {
            echo 'Partner Name and Partner Division already exist';
        }
    }

    public function get_divisionby_partner_name()
    {
        $this->load->model('Sfcontactsdetails_model');
        $this->load->model('Sfpartners_model');
        $partner_name = $this->input->post('partner_name');
        $all_data = $this->Sfpartners_model->get_divisionby_partner_name($partner_name, $this->session->userdata('client_id'));
        $edit_contact = $this->Sfcontactsdetails_model->edit_contact_view($_REQUEST['contact_id']);
        //echo"<pre>"; print_r($edit_contact); echo"</pre>";
        foreach ($all_data as $division) {
            if ($division['partner_division'] == '') {
                $division_name = $division['city'];
            } else {
                $division_name = $division['partner_division'];
            }
            if ($division_name == '') {
                echo '';
            } else {
                if ($division['id'] == $edit_contact[0]['partner_division']) {
                    $selected = "selected='selected'";
                } else {
                    $selected = '';
                }
                echo '<option value="' . $division['id'] . '" ' . $selected . '>' . $division_name . ' </option>';
            }
        }
    }

   
    public function marketing_actuals_upload()
    {
        if ($this->session->userdata('dealer_login')) {
            $clientid = $this->session->userdata('client_id');
            $this->load->model('Sfpartners_model');
            $this->load->model('Forecast_year_model');
            $query = $this->Forecast_year_model->GetForecastYear($clientid);
            $data['forecast_year'] = $query;
            $data['partners'] = $this->Sfpartners_model->get_all_partners_having_access($clientid); // get partner
            $data['get_uploaded_xls'] = $this->Sfpartners_model->get_marketing_xls($clientid); //get partner
            $session_uerdata = $this->session->userdata;
            $data['main_content'] = 'fronthand/twitter/mcal/manage_import_export/marketing_actuals_upload';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
    }

    // function to get marketing_actuals listings
    public function marketing_actuals_listings()
    {
        $clientid = $this->session->userdata('client_id');
        $this->load->model('Sfpartners_model');
        $data['get_uploaded_xls'] = $this->Sfpartners_model->get_marketing_xls($clientid);
        echo $this->load->view('fronthand/twitter/mcal/manage_import_export/actuals_listing', $data, true);
    }

    // function to upload marketing_actuals report
    public function marketing_xls_upload()
    {
        $this->load->model('Cam_model');
        $clientid = $this->session->userdata('client_id');
        // Upload file
        $config['upload_path'] = BASE_ABS_PATH . 'uploads/marketing';
        $config['allowed_types'] = '*';
        $config['max_size'] = '100000';
        $config['file_name'] = 'marketing_actual_forcast_data';
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('logo')) {
            $error = array('error' => $this->upload->display_errors());
            print_r(json_encode($error));
            die();
            $image_url = '';
        } else {
            $allowed_ext = array('.xlsx');
            $data = array('upload_data' => $this->upload->data());
            $extension = $data['upload_data']['file_ext'];
            $image_path = $data['upload_data']['file_name'];
            $image_url = $this->config->item('base_url1') . '/uploads/' . $image_path;
            if (!in_array($extension, $allowed_ext)) {
                unlink(performance_file . $image_path);
                $error = array('error' => 'Invalid file extension ! ');
                print_r(json_encode($error));
                die();
            }
            // Read File
            $get_upload = $this->upload->data();
            $file_name = BASE_ABS_PATH . 'uploads/marketing/' . $get_upload['file_name'];
            $sheet_detail = $this->Common_model->sheet_detail($file_name);
            $sheet_no = $sheet_detail['row'][0] - 1;
            $csv_data = $this->Common_model->readexcelsheet($file_name, $sheet_no); // Read excel file
            // Validate sheet header
            $valid = array('cam', 'partner', 'division', 'domain', 'year', 'quarter', 'tactic', 'tacticid');
            $actual = array_map(function ($value) {return strtolower(str_replace(' ', '', $value));}, array_values($csv_data['csv_data'][0]));
            $level_count = count($actual) - 7;
            $valid_actual = array($actual[0], $actual[1], $actual[2], $actual[3], $actual[4], $actual[5], $actual[6], $actual[7]);
            $diff = $this->Common_model->validateHead($valid, array_filter($valid_actual));
            if (!empty($diff)) {
                echo json_encode($diff);
                exit;
            }
            // Arrange excel data for insertion
            $arrange_data = $this->Cam_model->arrange_marketing_array($csv_data['csv_data'], $level_count, $clientid);
            //echo "<pre>";print_r($arrange_data); echo "</pre>";
            $insert_data = $this->Cam_model->insert_marketing_array($arrange_data, $clientid);
            // store sheet info in db
            $file_info['clientid'] = $clientid;
            $file_info['reportname'] = $get_upload['file_name'];
            $file_info['type'] = 'marketing';
            $file_info['fiscal_year'] = '0000';
            $file_info['year'] = '0000';
            $this->Cam_model->insert_sheet_info($file_info);
            $response['success'] = 'Data imported successfully';
            echo json_encode($response);
            die;
        }
        //return $image_url;
    }

    // Delete marketing uploaded actauls
    public function del_marketing_actuals()
    {
        $arr['id'] = $this->uri->segment(3);
        $this->load->model('Sfpartners_model');
        $this->Sfpartners_model->del_actuall_reports($arr);
        echo "<script>alert('Successfully deleted');location='" . base_url('dashboard/marketing_actuals_upload') . "'</script>";
        exit();
    }

    // call help page
    public function help()
    {
        if ($this->session->userdata('client_id')) {
            $data['main_content'] = 'fronthand/twitter/bulkimport/help';
            $this->load->view('/includes/template_new', $data);
        } else {
            redirect('manageaudit');
        }
    }



    // Upload Partner Pipeline Data 
    public function pipeline_import(){   
	    $this->Common_model->check_dashboard_login();
        $data['main_content'] = 'upload_center/pipeline/pipeline_upload';
        $this->load->view('/includes/template_new', $data);
    }
	
	// Upload Data In dashboard for Pipeline
    public function pipeline_sheet_upload(){
        $this->load->model('Cam_model');
        $this->load->model('Sfpartners_model');
        $clientid = $this->session->userdata('client_id');
		$client_fiscal_year = $this->Common_model->get_fiscal_year($clientid);
        $config['upload_path'] = BASE_ABS_PATH . '/uploads/pipeline';
        $config['allowed_types'] = '*';
        $config['max_size'] = '100000';
        $client_uniquename = $this->config->item('subdomain_client');
        $config['file_name'] = $client_uniquename . '_pipeline_index';
        $this->load->library('upload', $config);
        if(!$this->upload->do_upload('logo')){
            $error = array('error' => $this->upload->display_errors());
            print_r(json_encode($error));
            die();
            $image_url = '';
        }else{
            $allowed_ext = array('.xlsx');
            $data = array('upload_data' => $this->upload->data());
            $extension = $data['upload_data']['file_ext'];
            $image_path = $data['upload_data']['file_name'];
            $image_url = $this->config->item('base_url1') . '/uploads/' . $image_path;
            if(!in_array($extension, $allowed_ext)){
                unlink(performance_file . $image_path);
                $error = array('error' => 'Invalid file extension ! ');
                print_r(json_encode($error));
                die();
            }
			
            // Read File
            $get_upload = $this->upload->data();
            $file_name = BASE_ABS_PATH . '/uploads/pipeline/' . $get_upload['file_name'];
            $sheet_detail = $this->Common_model->sheet_detail($file_name);
            $sheet_no = $sheet_detail['row'][0] - 1;
            $csv_data = $this->Common_model->readexcelsheet($file_name, $sheet_no); // Read excel file
			
			// Valid Header array 
            $valid = array('partnername', 'primarypartnerid#*', 'dealid#*', 'dateofdeal*', 'estimatedclosedate*', 'salesstages*', 'secondarypartnerid#', 'dealname', '$amountperdeal', 'productname', 'productid', 'enduseraccountname', 'enduseraccountid#', 'leadsource', 'pipelinedealtype', 'salesrep', 'opportunitytype', 'partnertype');
			
			$actual = array_filter(array_map(function ($value) {
				return strtolower(str_replace(' ', '', $value));}, array_values($csv_data['csv_data'][10])));
			
			
		
			$valid_actual = array($actual[0], $actual[1], $actual[2], $actual[3], $actual[4], $actual[5], $actual[6], $actual[7], $actual[8], $actual[9], $actual[10], $actual[11], $actual[12], $actual[13], $actual[14], $actual[15], $actual[16], $actual[17]);
			
			// Check uploaded sheet heading match Or Not
			$diff = $this->Common_model->validateHead($valid, array_filter($valid_actual));
			
			
			
            if(!empty($diff)){
                echo json_encode($diff);
                exit;
            }
            if(count($actual) != count($valid_actual)){
                $response['error'] = 'Column Count Could Not Match';
                echo json_encode($response);
                exit;
            }
			
			// Arrange valid Or Invalid uploaded data
			$arrange_data = $this->Cam_model->arrange_pipeline_array($csv_data['csv_data'], $clientid);
			if(!empty($arrange_data['invalid'])){
                $response['invalid_report'] = $this->Cam_model->create_invalid_report($arrange_data['invalid'], $valid);
            } else {
                foreach ($arrange_data['valid'] as $sf_import) {
                    $id_partner = $sf_import['partner_id'];
                    $partner_ids[$id_partner] = $id_partner;
                }
                $partner_id = implode(',', $partner_ids);
                $arr['data'] = $partner_id;
                $this->Common_model->insert_data('partners_sf_json', $arr);
				$insert_data = $this->Cam_model->insert_pipeline_array($arrange_data['valid'], $clientid);
				
            }
            
			$file_info['client_id'] = $clientid;
            $file_info['sheet_name'] = $get_upload['file_name'];
            $file_info['datatype'] = 'Pipeline Index';
            $this->Cam_model->pipeline_insert_sheet_info($file_info);
            if (empty($arrange_data['valid'])) {
                $response['error'] = 'No data was uploaded. Please correct invalid records & try again.';
				$response['fail_count'] = 1 ;
				$create_name = "invalid_report".date("Y-m-d-h-m-s");
				$response['link'] = $create_name.'.csv' ;
                  

		 } else {
                if (empty($arrange_data['invalid'])) {
                    $response['success'] = 'Records uploded successfully !!';
                } else {
                    $response['error'] = 'Import Failed, please click the link to see the invalid data';
						
                }
            }
			
			//$download_link = $this->config->item('base_url1')."uploads/pipeline/invalid/". $get_upload['file_name'];
			// $img_link      =  $this->config->item('base_url1')."/fronthand/images/exl-icon.png";
			// $html  = '<tr class="remove_download_'.$last_id.'">
			// <td class="cellHeader bold_txt">
			// <img src="'.$img_link.'"><a href="'.$download_link.'">'.$get_upload['file_name'].'</a></td>
			// <td class="cellHeader bold_txt">'.date('Y m d h:m:s').'</td>
			// <td class="cellHeader bold_txt"><a href="javascript:void(0)"  class="btn btn-danger" onclick="delete_record('.$last_id.');"><i class="icon-remove icon-white"></i>Delete</a></td>
			// </tr>' ;
			// $response['newfile'] = $html ;	

			
            echo json_encode($response);
        }
    }

    public function pipeline_actuals_listings(){
        $clientid = $this->session->userdata('client_id');
        $this->load->model('Sfpartners_model');
        $data['get_uploaded_xls'] = $this->Sfpartners_model->get_pipeline_xls($clientid);
        echo $this->load->view('upload_center/pipeline/pipeline_listing', $data, true);
    }

    public function del_pipeline_actuals(){
        $arr['id'] = $this->uri->segment(3);
        $this->load->model('Sfpartners_model');
        $this->Sfpartners_model->del_pipeline_reports($arr);
        echo "<script>alert('Successfully deleted');location='" . base_url('dashboard/pipeline_import') . "'</script>";
        exit();
    }

    /*end Partner pipeline created by Ajay Raturi */

    /** Begin:Admin Region Levels - Subhash, Ravi(new), Dhirendra */

    // Common Function for all three regions- manage regions
     public function region($type)
    {
        $clientid = $this->session->userdata('client_id');

        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Region_model');
        $type = $this->uri->segment(4);
        $filterLevel = $this->uri->segment(5);
        switch ($type) {
            case '1':

                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    $data['manage_region_level1'] = $this->Common_model->get_data_array('region_level1_id,name,level2_parent_id', 'region_level1', array('client_id' => $clientid));

                    $get_region_two = $this->Common_model->get_data_array('name,region_level2_id', 'region_level2', array('client_id' => $clientid));
                    $data['get_region_2'] = $this->Common_model->keyValuepair($get_region_two, 'name', 'region_level2_id');

                    $data['main_content'] = 'fronthand/twitter/manage_regions/level1_regions/manage_region_level1';
                    $this->load->view('/includes/template_new', $data);
                } else {
                    redirect('manageaudit');
                }
                break;

            case '2':

                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    if ($filterLevel == 2) {
                        $this->load->model('Sfpartners_model');
                        $data['manage_region_level2'] = $this->Sfpartners_model->assigned_level1($clientid, $filterLevel);
                    } else {
                        $data['manage_region_level2'] = $this->Common_model->get_data_array('region_level2_id,name,level3_parent_id', 'region_level2', array('client_id' => $clientid));
                    }

                    $get_region_three = $this->Common_model->get_data_array('name,region_level3_id', 'region_level3', array('client_id' => $clientid));
                    $data['get_region_3'] = $this->Common_model->keyValuepair($get_region_three, 'name', 'region_level3_id');

                    $data['main_content'] = 'fronthand/twitter/manage_regions/level2_regions/manage_region_level2';
                    $this->load->view('/includes/template_new', $data);
                } else {
                    redirect('manageaudit');
                }
                break;

            case '3':
                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    if ($filterLevel == 2) {
                        $this->load->model('Sfpartners_model');
                        $data['manage_region_level3'] = $this->Sfpartners_model->assigned_level2($clientid, $filterLevel);
                    } else {
                        $data['manage_region_level3'] = $this->Common_model->get_data_array('name,region_level3_id', 'region_level3', array('client_id' => $clientid));
                    }

                    $data['main_content'] = 'fronthand/twitter/manage_regions/level3_regions/manage_region_level3';
                    $this->load->view('/includes/template_new', $data);
                } else {
                    redirect('manageaudit');
                }
                break;
				
            case '4':
			
                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    if ($filterLevel == 2) {
                        $this->load->model('Sfpartners_model');
                        $data['manage_region_level4'] = $this->Sfpartners_model->assigned_level2($clientid, $filterLevel);
                    } else {
                        $data['manage_region_level4'] = $this->Common_model->get_data_array('name,region_level4_id', 'region_level4', array('client_id' => $clientid));
                    }

                    $data['main_content'] = 'fronthand/twitter/manage_regions/level4_regions/manage_region_level4';
                    $this->load->view('/includes/template_new', $data);
                } else {
                    redirect('manageaudit');
                }
                break;				

            default:
                redirect('manageaudit');
        }
    }

    // Common function to add regions on all levels
      public function add($type)
    {
        $client_id = $this->session->userdata('client_id');
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Region_model');
        $type = $this->uri->segment(4);

        switch ($type) {
            case '1':
                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    $data['main_content'] = 'fronthand/twitter/manage_regions/level1_regions/add_region_level1';
                    $this->load->view('/includes/template_new', $data);
                } else {
                    redirect('manageaudit');
                }
                break;

            case '2':

                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    $data['not_asignd_regn_level1'] = $this->Region_model->get_notassigned_level1($client_id);
                    $data['asign_regn_level1'] = $this->Region_model->get_assigned_level1($client_id);
                    $data['all_region_level1'] = $all_region_level1;
                    $data['main_content'] = 'fronthand/twitter/manage_regions/level2_regions/add_region_level2';
                    $this->load->view('/includes/template_new', $data);
                } else {
                    redirect('manageaudit');
                }
                break;

            case '3':

                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    $data['not_asignd_regn_level2'] = $this->Region_model->get_notassigned_level2($client_id);
                    $data['asignd_regn_level2'] = $this->Region_model->get_assigned_level2($client_id);
                    $data['all_region_level2'] = $all_region_level2;
                    $data['main_content'] = 'fronthand/twitter/manage_regions/level3_regions/add_region_level3';
                    $this->load->view('/includes/template_new', $data);
                } else {
                    redirect('manageaudit');
                }
                break;
			
            case '4':

                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    $data['not_asignd_regn_level3'] = $this->Region_model->get_notassigned_level3($client_id);
                    $data['asignd_regn_level3'] = $this->Region_model->get_assigned_level3($client_id);
                    $data['all_region_level3'] = $all_region_level3;
                    $data['main_content'] = 'fronthand/twitter/manage_regions/level4_regions/add_region_level4';
                    $this->load->view('/includes/template_new', $data);
                } else {
                    redirect('manageaudit');
                }
                break;			
				
        }
    }

    // Add Region level 1
    public function add_region_level1()
    {    
	
	   $this->Common_model->check_dashboard_login();
        $data = array();

      
            $this->load->model('Region_model');
            $region = $this->input->post('region');
            $region_desc = $this->input->post('region_desc');
            $clientid = $this->session->userdata('client_id');
            $data = array('client_id' => $clientid, 'name' => $region);

            $check_unique = $this->Region_model->checkunique_field($id = '', $region, 'region_level1',$edit="");
            if ($check_unique == 0) {
                $add = $this->Common_model->insert_data('region_level1', $data);
            } else {
                $data['main_content'] = 'fronthand/twitter/manage_regions/level1_regions/add_region_level1';
                $this->load->view('/includes/template_new', $data);
            }

            if ($add) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>  Region Level 1 successfully added</strong> </div>');
                redirect('dashboard/region/level/1');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>  Region name already exist</strong> </div>');
                redirect('dashboard/add/level/1');
            }
       
    }

    // Add Region level 2  checkunique_field
    public function add_region_level2()
    {  
	     $this->Common_model->check_dashboard_login();
       
            $this->load->model('Region_model');
            $clientid = $this->session->userdata('client_id');
            $region_level2 = $this->input->post('region_level2');
            $region_level2_desc = $this->input->post('region_level2_desc');
            $assigned_region_level1 = $this->input->post('assigned_region_level1');
         
		 
            $data = array('client_id' => $clientid, 'name' => $region_level2);

            $check_unique = $this->Region_model->checkunique_field($id = '', $region_level2, 'region_level2',$edit="");
            if ($check_unique == 0) {
                $level2_parent_id = $this->Common_model->insert_data('region_level2', $data);
                $implode_assigned_ids = implode(',', $assigned_region_level1);

                $add = $this->Region_model->assign_level1_id($level2_parent_id, $implode_assigned_ids);
            }
            if ($add) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>  Region Level 2 successfully added</strong> </div>');
                redirect('dashboard/region/level/2');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>  Region name already exist</strong> </div>');
                redirect('dashboard/add/level/2');
            }
      
    }

    // Add Region level 3
    public function add_region_level3()
    {   
	    $this->Common_model->check_dashboard_login();
       
            $this->load->model('Region_model');
            $clientid = $this->session->userdata('client_id');
            $region_level3 = $this->input->post('region_level3');
            $region_level3_desc = $this->input->post('region_level3_desc');
            $assigned_region_level2 = $this->input->post('assigned_region_level2');

            $data = array('client_id' => $clientid, 'name' => $region_level3);
            $check_unique = $this->Region_model->checkunique_field($id = '', $region_level3, 'region_level3',$edit="");
            if ($check_unique == 0) {
                $level3_parent_id = $this->Common_model->insert_data('region_level3', $data);
                $implode_assigned_ids = implode(',', $assigned_region_level2);

                $add = $this->Region_model->assign_level2_id($level3_parent_id, $implode_assigned_ids);
            }
            if ($add) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong> Region Level 3 successfully added</strong> </div>');
                redirect('dashboard/region/level/3');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong> Region name already exist</strong> </div>');
                redirect('dashboard/add/level/3');
            }
        
    }

    /* Common function to edit all three regions */

   public function edit($type)
    {
        $client_id = $this->session->userdata('client_id');
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Region_model');
        $type = $this->uri->segment(3);

        switch ($type) {
            case 'vlevel1':
            case 'level1':
                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    $region_type = $this->uri->segment(4);
                    $data['editregion'] = $this->Region_model->get_level1_by_id($region_type);
                    $level1_id = $this->Region_model->get_data_array('region_level1_id', 'region_level1',array());
                    $region_id_arr = $this->Region_model->keyValuepair($level1_id, 'region_level1_id', 'region_level1_id');
                    if (!in_array($region_type, $region_id_arr)) {
                        redirect('manageaudit');
                    } else {
                        $data['main_content'] = 'fronthand/twitter/manage_regions/level1_regions/edit_region_level1';
                        $this->load->view('/includes/template_new', $data);
                    }
                } else {
                    redirect('manageaudit');
                }
                break;

            case 'level2':
            case 'vlevel2':

                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    $last_id = $this->uri->segment(4);
                    $data['editregion'] = $this->Region_model->get_level2_by_id($last_id);
                    $regions = $this->Region_model->get_assigned_level1_region($last_id);
                    $data['asign_regn_level1'] = $this->Region_model->get_assigned_level1($client_id);
                    $level2_id = $this->Region_model->get_data_array('region_level2_id', 'region_level2',array());
                    $region_id_arr = $this->Region_model->keyValuepair($level2_id, 'region_level2_id', 'region_level2_id');
                    $selected_level1_regions = array();
                    foreach ($regions as $key => $val) {
                        $selected_level1_regions[$val['region_level1_id']] = $val['name'];
                    }
                    $data['selected_level1_regions'] = $selected_level1_regions;

                    $not_asignd_regn_level1 = $this->Region_model->get_level1_assigned();
                    $non_asignd_regn_level1 = array();
                    foreach ($not_asignd_regn_level1 as $key => $val) {
                        $non_asignd_regn_level1[$val['region_level1_id']] = $val['name'];
                    }
                    $data['not_asignd_regn_level1'] = $non_asignd_regn_level1;
                    if (!in_array($last_id, $region_id_arr)) {
                        redirect('manageaudit');
                    } else {
                        $data['main_content'] = 'fronthand/twitter/manage_regions/level2_regions/edit_region_level2';
                        $this->load->view('/includes/template_new', $data);
                    }
                } else {
                    redirect('manageaudit');
                }
                break;

            case 'level3':
            case 'vlevel3':
                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    $last_id = $this->uri->segment(4);
                    $data['editregion'] = $this->Region_model->get_level3_by_id($last_id);
                    $region = $this->Region_model->get_assigned_level2_region($last_id);
                    $data['asignd_regn_level2'] = $this->Region_model->get_assigned_level2($client_id);
                    $level3_id = $this->Region_model->get_data_array('region_level3_id', 'region_level3',array());
                    $region_id_arr = $this->Region_model->keyValuepair($level3_id, 'region_level3_id', 'region_level3_id');

                    $selected_level2_regions = array();
                    foreach ($region as $key => $val) {
                        $selected_level2_regions[$val['region_level2_id']] = $val['name'];
                    }

                    $data['selected_level2_regions'] = $selected_level2_regions;

                    $not_asignd_regn_level2 = $this->Region_model->get_level2_assigned();

                    $non_asignd_regn_level2 = array();
                    foreach ($not_asignd_regn_level2 as $key => $val) {
                        $non_asignd_regn_level2[$val['region_level2_id']] = $val['name'];
                    }

                    $data['not_asignd_regn_level2'] = $non_asignd_regn_level2;
                    if (!in_array($last_id, $region_id_arr)) {
                        redirect('manageaudit');
                    } else {
                        $data['main_content'] = 'fronthand/twitter/manage_regions/level3_regions/edit_region_level3';
                        $this->load->view('/includes/template_new', $data);
                    }
                } else {
                    redirect('manageaudit');
                }
                break;
				
            case 'level4':
            case 'vlevel4':
                if (($this->session->userdata('dealer_login')) || ($this->session->userdata('crm_id'))) {
                    $last_id = $this->uri->segment(4);
                    $data['editregion'] = $this->Region_model->get_level4_by_id($last_id);
                    $region = $this->Region_model->get_assigned_level3_region($last_id);
                    $data['asignd_regn_level3'] = $this->Region_model->get_assigned_level3($client_id);
                    $level4_id = $this->Region_model->get_data_array('region_level4_id', 'region_level4',array());
                    $region_id_arr = $this->Region_model->keyValuepair($level4_id, 'region_level4_id', 'region_level4_id');
						//debug($region);die;
                    $selected_level3_regions = array();
                    foreach ($region as $key => $val) {
                        $selected_level3_regions[$val['region_level3_id']] = $val['name'];
                    }
					//debug($selected_level3_regions);die;	
                    $data['selected_level3_regions'] = $selected_level3_regions;

                    $not_asignd_regn_level3 = $this->Region_model->get_level3_assigned();

                    $non_asignd_regn_level3 = array();
                    foreach ($not_asignd_regn_level3 as $key => $val) {
                        $non_asignd_regn_level3[$val['region_level3_id']] = $val['name'];
                    }

                    $data['not_asignd_regn_level3'] = $non_asignd_regn_level3;
                    if (!in_array($last_id, $region_id_arr)) {
                        redirect('manageaudit');
                    } else {
                        $data['main_content'] = 'fronthand/twitter/manage_regions/level4_regions/edit_region_level4';
                        $this->load->view('/includes/template_new', $data);
                    }
                } else {
                    redirect('manageaudit');
                }
                break;				
				
            default:

                redirect('manageaudit');
        }
    }

    public function update_region_level1($type)
    {   
	 $this->Common_model->check_dashboard_login();
       
            $this->load->model('Region_model');
            $region_id = $this->input->post('region_id');
            $name = $this->input->post('name');
            $description = $this->input->post('description');
            $data = array('region_level1_id' => $region_id, 'name' => $name);

            $check_unique = $this->Region_model->checkunique_field('region_level1_id', $name, 'region_level1', $region_id);

            if ($check_unique == 0) {
                $add = $this->Region_model->update_regionlevel1($data);
            }

            if ($add) {
                if ($type == 'vlevel1') {
                    $this->session->set_flashdata('region_update', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Level updated successfully</strong> </div>');
                    redirect('dashboard/regioncontrol');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Level updated successfully</strong> </div>');
                    redirect('dashboard/region/level/1');
                }
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong> Region name already exist</strong> </div>');
                $data['main_content'] = 'fronthand/twitter/manage_regions/level1_regions/edit_region_level1';
                $this->load->view('/includes/template_new', $data);
                if ($check_unique != 0) {
                    redirect('dashboard/edit/level1/' . $region_id);
                } else {
                    redirect('dashboard/region/level/1');
                }
            }
        
    }

    public function update_regionlevel2($type)
    {   
	    $this->Common_model->check_dashboard_login();
       
            $this->load->model('Region_model');
            $clientid = $this->session->userdata('client_id');
            $region_id = $this->input->post('region_id');
            $name = $this->input->post('name');
            $description = $this->input->post('description');
            $assigned_region_level1 = $this->input->post('assigned_region_level1');
            $data = array('region_level2_id' => $region_id, 'name' => $name);
            $implode_assigned_ids = implode(',', $assigned_region_level1);

            $check_unique = $this->Region_model->checkunique_field('region_level2_id', $name, 'region_level2', $region_id);
            if ($check_unique == 0) {
                $this->Region_model->update_region_level2($data);
                $this->Common_model->update_data('region_level1', array('level2_parent_id' => null), array('client_id' => $clientid, 'level2_parent_id' => $region_id));
                $add = $this->Region_model->assign_level1_id($region_id, $implode_assigned_ids);
            }

            if ($add) {
                if ($type == 'vlevel2') {
                    $this->session->set_flashdata('region_update', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Level updated successfully</strong> </div>');
                    redirect('dashboard/regioncontrol');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Level updated successfully</strong> </div>');
                    redirect('dashboard/region/level/2');
                }
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong> Region name already exist</strong> </div>');

                if ($check_unique != 0) {
                    redirect('dashboard/edit/level2/' . $region_id);
                } else {
                    redirect('dashboard/region/level/2');
                }
            }
       
    }

    public function update_regionlevel3($type)
    {   
	     $this->Common_model->check_dashboard_login();
       
            $this->load->model('Region_model');
            $clientid = $this->session->userdata('client_id');
            $region_id = $this->input->post('region_id');
            $name = $this->input->post('name');
            $description = $this->input->post('description');
            $assigned_region_level2 = $this->input->post('assigned_region_level2');
            $data = array('region_level3_id' => $region_id, 'name' => $name);
            $implode_assigned_ids = implode(',', $assigned_region_level2);

            $check_unique = $this->Region_model->checkunique_field('region_level3_id', $name, 'region_level3', $region_id);

            if ($check_unique == 0) {
                $this->Region_model->update_region_level3($data);
                $this->Common_model->update_data('region_level2', array('level3_parent_id' => null), array('client_id' => $clientid, 'level3_parent_id' => $region_id));
                $add = $this->Region_model->assign_level2_id($region_id, $implode_assigned_ids);
            }

            if ($add) {
                if ($type == 'vlevel3') {
                    $this->session->set_flashdata('region_update', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Level updated successfully</strong> </div>');
                    redirect('dashboard/regioncontrol');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Level updated successfully</strong> </div>');
                    redirect('dashboard/region/level/3');
                }
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong> Region name already exist</strong> </div>');

                if ($check_unique != 0) {
                    redirect('dashboard/edit/level3/' . $region_id);
                } else {
                    redirect('dashboard/region/level/3');
                }
            }
       
    }

    // To delete region level 1
    public function deleteregionlevel1()
    {    
	     $this->Common_model->check_dashboard_login();
        $clientid = $this->session->userdata('client_id');
        $region_id = $this->uri->segment(3);
        $cam_records = $this->Common_model->get_data_row('cam_id', 'cam_level1_region', array('region_level1_id' => $region_id));
        if ($cam_records) {
            $this->session->set_flashdata('msg', '<div class="alert alert-info fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region is already assigned to some CAM. You cannot delete this Region.</strong></div>');
        } else {
            $delete = $this->Common_model->delete_records('region_level1', array('region_level1_id' => $region_id, 'client_id' => $clientid));
            $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Deleted Successfully!</strong></div>');
        }
        redirect('dashboard/region/level/1');
    }

    // TO delete region level 2
    public function deleteregionlevel2()
    { 
	     $this->Common_model->check_dashboard_login();
        $region_id = $this->uri->segment(3);
        $cam_records = $this->Common_model->get_data_row('cam_id', 'cam_level2_region', array('region_level2_id' => $region_id));
        if ($cam_records) {
            $this->session->set_flashdata('msg', '<div class="alert alert-info fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region is already assigned to some CAM. You cannot delete this Region.</strong></div>');
        } else {
            $this->Common_model->update_data('region_level1', array('level2_parent_id' => null), array('level2_parent_id' => $region_id));
            $delete = $this->Common_model->delete_records('region_level2', array('region_level2_id' => $region_id));
            $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Deleted Successfully!</strong></div>');
        }
        redirect('dashboard/region/level/2');
    }

    // Function to delete region 3
    public function deleteregionlevel3()
    {  
	    $this->Common_model->check_dashboard_login();
        $region_id = $this->uri->segment(3);
        $cam_records = $this->Common_model->get_data_row('cam_id', 'cam_level3_region', array('region_level3_id' => $region_id));
        if ($cam_records) {
            $this->session->set_flashdata('msg', '<div class="alert alert-info fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region is already assigned to some CAM. You cannot delete this Region.</strong></div>');
        } else {
            $this->Common_model->update_data('region_level2', array('level3_parent_id' => null), array('level3_parent_id' => $region_id));
            $delete = $this->Common_model->delete_records('region_level3', array('region_level3_id' => $region_id));

            $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Deleted Successfully!</strong></div>');
        }
        redirect('dashboard/region/level/3');
    }

    /** Drag and Drop Update START by Ravi(New)**/
    public function update_region_level2()
    {
        $clientid = $this->session->userdata('client_id');
        $unique_id = $this->input->post('item');
        $drag_id = $this->input->post('new_level2');
        $new = $this->Common_model->update_data('region_level1', array('level2_parent_id' => $drag_id), array('region_level1_id' => $unique_id, 'client_id' => $clientid));
        if ($new) {
            return true;
        } else {
            return false;
        }
    }

    public function update_region_level3()
    {
        $clientid = $this->session->userdata('client_id');
        $unique_id = $this->input->post('item');
        $drag_id = $this->input->post('new_level3');
        $new = $this->Common_model->update_data('region_level2', array('level3_parent_id' => $drag_id), array('region_level2_id' => $unique_id, 'client_id' => $clientid));

        if ($new) {
            return true;
        } else {
            return false;
        }
    }

    /** Drag and Drop Update END by Ravi(New) **/

    // Region visualization Interface  
		public function regioncontrol(){    
	    $this->Common_model->check_dashboard_login();
		$segment_uri = $this->uri->segment(3);	
		$this->load->library('pagination');
		$clientid= $this->session->userdata('client_id');
		$this->load->model('Sfowner_model');
		$this->load->model('Role_model');
		$this->load->model('Region_model');
		$this->load->model('Sfpartners_model');
		$data['allsuper_partners'] = $this->Sfpartners_model->get_superpartner($this->session->userdata('client_id')); 
		 $search='';
		$page =($this->input->get('per_page')) ? $this->input->get('per_page') : 0;
		$config['page_query_string'] = true;
		$config['enable_query_strings'] = true;
		$config['reuse_query_string'] = TRUE;
		if($this->input->post('s')){
			 $search=$this->input->post('s'); 
		}if($this->input->get('order')){
			 $order=$this->input->get('order'); 
			 $order_by=$this->input->get('order_by'); 
		} 
		if($this->input->get('crm_id')){
			$crm_ids = $this->input->get('crm_id');
		}
		$limit = ($this->input->get('limit')) ? $this->input->get('limit') : '25';
		
		$config['base_url'] = base_url().'dashboard/regioncontrol/region_visualization/';
		$get_all=$this->Region_model->get_all_sfpartners($crm_ids,$search); 
		$config['total_rows'] = $get_all ;
		$config['per_page'] = $limit;
		
		$this->pagination->initialize($config);	
		$data['cam_data']  =  $this->Region_model->get_sfpartners_pgn( $config['per_page'],$page,$search,$crm_ids);
		
		
		$data['disallow_partner'] = $this->Common_model->get_data_array('cam_id,partner_id','tbl_reassign_partners',array('client_id'=>$clientid));
		$disallow_partner_arr = array();
	     if(is_array($data['disallow_partner']) and count($data['disallow_partner'])>0){
		   foreach($data['disallow_partner'] as $key=>$partner_val){
		   $disallow_partner_arr[$partner_val['cam_id']][$partner_val['partner_id']]  = $partner_val['partner_id'];
			   }	 
		 }
		
		 $data['disallow_partner_arr']  = $disallow_partner_arr;
	
		
			
		$data['links'] = $this->pagination->create_links();
		//end
		
		
		$sql_cam="select t1.id, t1.crm_id,t1.crm_acc_name as cam_name,t1.primary_region_id,t1.is_region1_director,t1.is_region2_director,t1.is_region3_director,t2.region_level3_id from account_channel_manager as t1 join cam_level3_region as t2 on t1.id = t2.cam_id where t1.cam_type IN('cam_director','cam')";

		$distinct_cam_data = $this->Common_model->get_conditional_array($sql_cam);
		
		foreach($distinct_cam_data as $cam_data){
			if($cam_data['is_region3_director'] == 1){
				$data['region_cam3_array1'][$cam_data['region_level3_id']][] =   $cam_data;
			}
		}
		
		 if($segment_uri=="region_visualization"){ // region visualization tab
			 
		$region_level3="select * from region_level3";
		$data['region_level3'] = $this->Common_model->get_conditional_array($region_level3);
		
		/* Region Level2*/ 
		$sql_cam2="select t1.id,t1.crm_id,t1.crm_acc_name as cam_name,t1.primary_region_id,t1.is_region1_director,t1.is_region2_director,t1.is_region2_director,t2.region_level2_id from account_channel_manager as t1 join cam_level2_region as t2 on t1.id = t2.cam_id where t1.cam_type IN('cam_director','cam')";

		$distinct_cam2_data = $this->Common_model->get_conditional_array($sql_cam2);
		foreach($distinct_cam2_data as $cam_data){
			if($cam_data['is_region2_director'] == 1){
				$data['region_cam2_array1'][$cam_data['region_level2_id']][] =   $cam_data;
				
			}
		}
		
		
		
			 
		$region_level2="select * from region_level2";
		$data['region_level2'] = $this->Common_model->get_conditional_array($region_level2);
		
		/* Region Level1*/ 
		$sql_cam1="select t1.id,t1.crm_id,t1.crm_acc_name as cam_name,t1.primary_region_id,t1.is_region1_director,t1.is_region2_director,t1.is_region2_director,t2.region_level1_id from account_channel_manager as t1 join cam_level1_region as t2 on t1.id = t2.cam_id where t1.cam_type IN('cam_director','cam')";

		$distinct_cam1_data = $this->Common_model->get_conditional_array($sql_cam1);
		foreach($distinct_cam1_data as $cam_data){
			if($cam_data['is_region1_director'] == 1){
				$data['region_cam1_array1'][$cam_data['region_level1_id']][] =   $cam_data;
				
			}
		}
		$region_level1="select * from region_level1";
		$data['region_level1'] = $this->Common_model->get_conditional_array($region_level1);
		
		$sql_onlycam1="select t1.id,t1.crm_id,t1.crm_acc_name as cam_name,t1.primary_region_id,t1.is_region1_director,t1.is_region2_director,t1.is_region2_director from account_channel_manager as t1 where t1.cam_type = 'cam'";

		$distinct_onlycam1_data = $this->Common_model->get_conditional_array($sql_onlycam1);
		foreach($distinct_onlycam1_data as $cam_data){
			$data['region_onlycam1_array1'][$cam_data['primary_region_id']][] =   $cam_data;
			
		}
		
		$partner_secondary_cam = "select * from partner_secondary_cam";
		$partnersecondary_cam = $this->Common_model->get_conditional_array($partner_secondary_cam);
		
		foreach($partnersecondary_cam as $values){
			$data['selected_secondary_cam'][$values['partner_id']][] = $values['cam_id'];
			$data['partner_secondary_cam'][$values['partner_id']][] = $values['secondary_cam_id'];
		}
	
		$r1_cam = $this->Region_model->assigned_resign_data($clientid, $region_type = 'r1');
		$r2_cam = $this->Region_model->assigned_resign_data($clientid, $region_type = 'r2');
		$r3_cam = $this->Region_model->assigned_resign_data($clientid, $region_type = 'r3');
	     
     
			$data['r1_cam'] = $r1_cam;
			$data['r2_cam'] = $r2_cam;
			$data['r3_cam'] = $r3_cam;
		
		
		 $data['region1_parent'] = $this->Common_model->keyValuepair($r1_cam['cam_data'], 'level2_parent_id', 'region_level1_id');
		 
		 $data['region2_parent'] = $this->Common_model->keyValuepair($r2_cam['cam_data'], 'level3_parent_id', 'region_level2_id');
		
		$data['reqionlevel2_name'] = $this->Common_model->keyValuepair($r2_cam['cam_data'], 'name', 'region_level2_id');
		 
		 $data['reqionlevel3_name'] = $this->Common_model->keyValuepair($r3_cam['cam_data'], 'name', 'region_level3_id');
		 
		 
        $data['regionlevel2_id'] = $this->Common_model->keyValuepair($data['region_level1'], 'level2_parent_id', 'region_level1_id');
        $data['regionlevel2'] = $this->Common_model->keyValuepair($data['region_level2'], 'name', 'region_level2_id');
		$data['regionlevel3_id'] = $this->Common_model->keyValuepair($data['region_level2'], 'level3_parent_id', 'region_level2_id');
        $data['regionlevel3'] = $this->Common_model->keyValuepair($data['region_level3'], 'name', 'region_level3_id');
}
	

        $get_cam = $this->Common_model->get_data_array('is_access,crm_id,id,crm_acc_name,cam_type', 'account_channel_manager', array('clientid' => $clientid));
		
		 $cam_all_val  = array();
		 if(is_array($get_cam) and count($get_cam)>0){
		 foreach($get_cam as $key=> $get_cam_val){
			 if($get_cam_val['cam_type']=="cam" or $get_cam_val['cam_type']=="cam_director"){
			$cam_all_val[$get_cam_val['id']]  = $get_cam_val['crm_acc_name'];	 
			}
			 }	 
		 }
		 $data['cam_all_val']  = $cam_all_val;
		 
		$data['cam_status'] = $this->Common_model->keyValuepair($get_cam, 'is_access', 'id');
		
		
		
	
		
		$data['region'] = $this->Common_model->get_data_row('active_region', 'region_settings', array('client_id' => $clientid));

	
		// reassign partner tab data
		$cam_id      = $this->input->post('cam_id');
		$cam_crm_id  = $this->input->post('crm_id');
		if($segment_uri=="partner_exclusion"){
		$data['cam_data'] = $this->Common_model->get_conditional_array( "Select id,crm_id,crm_acc_name from account_channel_manager where is_access='1' and clientid='".$clientid."' and (is_region1_director='1' or is_region2_director='1' or is_region3_director='1')");	
		
		if($cam_id !="" and $segment_uri=="partner_exclusion"){
		 $cam_partner_arr = 	$this->Common_model->cam_partners_with_region($cam_id,$cam_crm_id,$clientid);
        $data['cam_partner_data'] = 	$cam_partner_arr['all_region_partner'];
	
		$get_partner = $this->Common_model->get_data_array('reassign_partners_id,cam_id,partner_id', 'tbl_reassign_partners', array('client_id' => $clientid,'cam_id' => $cam_id));
		
		
		
		$data['db_partner_arr']  = $this->Common_model->keyValuepair($get_partner, 'partner_id', 'reassign_partners_id');
		$data['partner_name']  = $this->Common_model->keyValuepair($data['cam_partner_data'], 'partner_acc_name', 'id');
         
       
		 if($this->input->post('save_partner')==1){
		  $reassign_partner = $this->input->post('reassign_partner');
		$this->save_reassign_partner($data['db_partner_arr'],$cam_id,$reassign_partner);
		}
		}
		}
	    $data['main_content'] = 'fronthand/twitter/manage_regions/region_visualization';
        $this->load->view('/includes/template_without_sidebar', $data);
    }
	
    /** End:Admin Region Levels - Subhash, Ravi(new), Dhirendra */

    /** Begin: Provision to set Yearly parameters (Annually, Quaterly, Semi- Annually) **/
   /** Begin: Provision to set Yearly parameters (Annually, Quaterly, Semi- Annually) **/
    public function set_frequency()
    {   

	    $this->Common_model->check_dashboard_login();
	
        $client_id = $this->session->userdata('client_id');
		$all_active_app = $this->get_steps = $this->Common_model->get_scorecard_steps($client_id,"");
		$data['all_active_app'] = $all_active_app['parent_step'];
		
        // fetch previoulsy saved data
        $data['frequency'] = $this->Common_model->get_data_row('pps,account_planning,bussiness,scorecards,action_plan,marketing_action_plan', 'report_frequency', array('client_id' => $client_id));
		
        $data['main_content'] = 'fronthand/twitter/frequency/set_frequency';
        $this->load->view('/includes/template_new', $data);
    }

    // Save frequency data
    public function save_frequency()
    {
        // Get user inputs
        if ($this->input->post('submit')) {
            $this->load->model('Region_model');
            $client_id = $this->session->userdata('client_id');

			
            $data['client_id'] = $client_id;
            $data['pps'] = trim($this->input->post('pps'));
            $data['account_planning'] = trim($this->input->post('account_planning'));
            $data['bussiness'] = trim($this->input->post('bussiness'));
            $data['scorecards'] = trim($this->input->post('scorecard'));
            $data['action_plan'] = trim($this->input->post('action_plan'));
            $data['marketing_action_plan'] = trim($this->input->post('marketing_action_plan'));

            $freq_id = $this->Common_model->get_data_row('report_frequency_id', 'report_frequency', array('client_id' => $client_id));
            $freq_id = $freq_id['report_frequency_id'];

            if ($freq_id) { // update if already exist
                $update = $this->Common_model->update_data('report_frequency', $data, array('report_frequency_id' => $freq_id));
                $this->success_msg('Data Updated Successfully');
            } else { // insert if client_id not exist
                $insert = $this->Common_model->insert_data('report_frequency', $data);
                $this->success_msg('Data Updated Successfully');
            }
            redirect('dashboard/manage_tasks');
        }
    }

    // Method to create Region
     public function save_regionsettings()
    {   
	    $this->Common_model->check_dashboard_login();
        $client_id = $this->session->userdata('client_id');
        $level = trim($this->input->post('level'));

        if ($level == 1) {
            $update = $this->Common_model->update_data('account_channel_manager', array('is_region2_director	' => null, 'is_region3_director' => null), array('clientid' => $client_id));
        }

        if ($level == 2) {
            $update = $this->Common_model->update_data('account_channel_manager', array('is_region3_director	' => null), array('clientid' => $client_id));
        }

        $update = $this->Common_model->update_data('region_settings', array('active_region' => $level), array('client_id' => $client_id));
        //        $this->success_msg( 'Region update successfully!');
        $this->session->set_flashdata('save_regions', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region update successfully!</strong></div>');
		$this->session->set_userdata('success_msg',1);
        redirect('dashboard/regioncontrol/region_level');
    }
	
	
	public function save_reassign_partner($db_partner_arr,$cam_id,$reassign_partner){
	
	 $this->Common_model->check_dashboard_login();
	  $client_id = $this->session->userdata('client_id');
	  $insert_arr  = array();
	  $post_partner  = array();
	 if(is_array($reassign_partner) and count($reassign_partner)>0){
	 foreach($reassign_partner as $key=>$partners){
	 $post_partner[$partners]           = $partners;   
	 if(! in_array($partners,$db_partner_arr)){
	 $insert_arr[$key]['partner_id']    = $partners;	 
	 $insert_arr[$key]['cam_id']        = $cam_id;	 
	 $insert_arr[$key]['client_id']     = $client_id;	 
	 }
	 
	 }	 
	}	
	 $partner_diff  = array_diff($db_partner_arr,$post_partner); 
 if (!empty($partner_diff)) {
	   $del_partner = implode("','", $partner_diff);
    $del_ans_ques = "delete from tbl_reassign_partners where client_id='".$client_id."' and cam_id='".$cam_id."'  and partner_id in('".$del_partner."') ";
		$this->db->query($del_ans_ques);
        }
		
	 if (!empty($insert_arr)) {
            $this->db->insert_batch(' tbl_reassign_partners', $insert_arr);
        }	
	}
	
	
	
	
	
    /** Common fuction to show success message
     * pass parameter as message on function call.
     */
    private function success_msg($msg)
    {
        $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>' . $msg . '</strong></div>');
    }

    /** End: Provision to set Yearly parameters (Annually, Quaterly, Semi- Annally) **/
    public function manage_consolidate_steps()
    {
        //echo "nmnmkn";
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $client_id = $this->session->userdata('client_id');

        //fetch database contents from database and to display against destined locations in form
        $this->db->where('client_id ', $client_id);
        $query = $this->db->get('consolidate_steps');
        $data['steps'] = $query->result_array();

        $data['main_content'] = 'fronthand/twitter/manage_consolidate_steps/consolidate_Steps.php';
        $this->load->view('/includes/template_new', $data);
    }

    public function insert_consolidate_steps()
    {
        //echo "vikky";

        $client_id = $this->session->userdata('client_id');
        $steps = array();
        $steps = $this->input->post('consolidatebox');
        $array_names = $this->input->post('consolidate_key');
        $array_names1 = array();

        foreach ($array_names as $key => $value) {
            $array_names1[trim($value)] = trim($steps[$key]);
        }
        $data = array(
            'client_id' => $client_id,
            'steps' => json_encode($array_names1),
        );
       
        $rowcount  = $this->db
		->where('client_id',$client_id)
		->count_all_results('consolidate_steps');

        if ($rowcount>0) {
            $this->db->where('client_id', $client_id);
            $this->db->update('consolidate_steps', $data);
            $this->session->set_flashdata('consolidate_step', 'Data Updated Sucessfully');
            redirect('dashboard/report_order');
        } else {
            $this->db->insert('consolidate_steps', $data);
            $this->session->set_flashdata('consolidate_step', 'Data Inserted Sucessfully');

            redirect('dashboard/report_order');
        }
    }


	/* Function for Export Data from pipeline table */
		function pipeline_export_data(){
		   $this->Common_model->check_dashboard_login(); 
		    $client_id  = $this->session->userdata('client_id');
			$client_name = ucwords($this->config->item('subdomain_client')); 
		    $file_name = "".$client_name." Partner Opportunities.xlsx";
			$header = array(
							'Partner Name',
							'Primary Partner ID#',
							'Deal ID#',
							'Date of Deal',
							'Estimated Close Date',
							'Sales Stages ',
							'Secondary Partner ID#',
							'Deal Name',
							'$ Amount Per Deal',
							'Product Name',
							'Product ID',
							'End User Account Name',
							'End User Account ID#',
							'Lead Source',
							'Pipeline Deal Type',
							'Sales Rep',
							'Opportunity Type',
							'Partner Type',
						);
						
		 $pipeline_levels = $this->Common_model->get_data_array('level,level_name', 'pipeline_levels', array());$pipeline_levels_name = $this->Common_model->keyValuepair($pipeline_levels, 'level_name', 'level');				
						
						
		 $opportunity = $this->Common_model->get_data_array('opportunity_type_title,opportunity_type_id', 'tbl_opportunity_type', array()); 	
         $opportunity_name = $this->Common_model->keyValuepair($opportunity, 'opportunity_type_title', 'opportunity_type_id');
		 
		 $dealtype = $this->Common_model->get_data_array('deal_type_title,deal_type_id', 'tbl_deal_type', array()); 	
         $deal_name = $this->Common_model->keyValuepair($dealtype, 'deal_type_title', 'deal_type_id');
		 
		 $product = $this->Common_model->get_data_array('id,productname', 'manage_products', array()); 	
         $product_name = $this->Common_model->keyValuepair($product, 'productname', 'id');
		 
        $sql = "select t1.partner_acc_name,t1.primary_partner_id,t1.secondary_partner_id,t2.* from partners_accounts as t1 inner join pipeline_actual_data as t2 on t1.id=t2.partner_id where t1.clientid='".$client_id."' ";
		$pipeline_arr = $this->Common_model->get_conditional_array($sql);		
        
	    foreach($pipeline_arr as $key_val=>$pipeline_val){
		 $get_deal_type  = 	json_decode($pipeline_val['deal_type']); 
		 foreach($get_deal_type as $deal_key=>$get_deal_val){
		 $deal_type_data[$key_val][]  = $deal_name[$get_deal_val];  
		 }
		 
		
			
		$sheet_array[$key_val]['partner_acc_name']  = $pipeline_val['partner_acc_name']; 	
		$sheet_array[$key_val]['primary_partner_id']  = $pipeline_val['primary_partner_id']; 	
		$sheet_array[$key_val]['deal_id']  = $pipeline_val['deal_id']; 	
		$sheet_array[$key_val]['deal_date']  = $pipeline_val['deal_date']; 	
		$sheet_array[$key_val]['close_date']  = $pipeline_val['close_date']; 	
		
		$sheet_array[$key_val]['pipeline_levels']  = $pipeline_levels_name[$pipeline_val['pipeline_levels']]; 	
		
		$sheet_array[$key_val]['secondary_partner_id']  = $pipeline_val['secondary_partner_id']; 	
		$sheet_array[$key_val]['deal_name']  = $pipeline_val['deal_name']; 	
		$sheet_array[$key_val]['amount_per_deal']  = $pipeline_val['amount_per_deal']; 	
		$sheet_array[$key_val]['product_name']  = $product_name[$pipeline_val['selected_products']]; 	
		
		$sheet_array[$key_val]['product_id']    =  $pipeline_val['selected_products']; 	
		$sheet_array[$key_val]['end_user_account_name']  = $pipeline_val['end_user_account_name']; 	
		$sheet_array[$key_val]['end_user_account_id']  = $pipeline_val['end_user_account_id']; 	
		$sheet_array[$key_val]['lead_source']  = $pipeline_val['lead_source']; 	
		
		$sheet_array[$key_val]['deal_type']  = implode(",",$deal_type_data[$key_val]);
		
		$sheet_array[$key_val]['sales_rep']         = $pipeline_val['sales_rep'];
		$sheet_array[$key_val]['opportunity_type']  = $opportunity_name[$pipeline_val['opportunity_type']]; 	
		$sheet_array[$key_val]['partner_type']      = $pipeline_val['partner_type']==1 ? "direct partner" : "indirect partner"; 	
			
		}
			if(count($sheet_array)==0){ $sheet_array = array();  }				
		 $this->Common_model->export_excel_sheet($header,$sheet_array,$file_name);
		}

     
	 	/* Function for Export Data from partner actual */
		function sales_export(){
		   $this->Common_model->check_dashboard_login(); 
		    $client_id  = $this->session->userdata('client_id');
			$client_name = ucwords($this->config->item('subdomain_client')); 
		    $file_name = "".$client_name." Partner Sales.xlsx";
			$header = array(
							'Partner Name',
							'Primary Partner ID#',
							'Transaction ID',
							'Transaction Date',
							'Sales Stages',
							'Secondary Partner ID#',
							'Deal Name',
						    'Product Name',
							'Product ID',
							'Unit',
							'Total Transaction $ Value',
							'Deal Type',
							'Opportunity Type',
							'Partner Type',
						);
						
			
         $pipeline_levels = $this->Common_model->get_data_array('level,level_name', 'pipeline_levels', array());$pipeline_levels_name = $this->Common_model->keyValuepair($pipeline_levels, 'level_name', 'level');
			
		 $opportunity = $this->Common_model->get_data_array('opportunity_type_title,opportunity_type_id', 'tbl_opportunity_type', array()); 	
         $opportunity_name = $this->Common_model->keyValuepair($opportunity, 'opportunity_type_title', 'opportunity_type_id');
		 
		 $dealtype = $this->Common_model->get_data_array('deal_type_title,deal_type_id', 'tbl_deal_type', array()); 	
         $deal_name = $this->Common_model->keyValuepair($dealtype, 'deal_type_title', 'deal_type_id');
		 
		 $product = $this->Common_model->get_data_array('id,productname', 'manage_products', array()); 	
         $product_name = $this->Common_model->keyValuepair($product, 'productname', 'id');
		 
        $sql = "select t1.partner_acc_name,t1.primary_partner_id,t1.secondary_partner_id,t2.* from partners_accounts as t1 inner join partners_actual_data as t2 on t1.id=t2.partner_id where t1.clientid='".$client_id."' ";
		$actual_arr = $this->Common_model->get_conditional_array($sql);		
        foreach($actual_arr as $key_val=>$actual_val){
		 $get_deal_type  = 	json_decode($actual_val['deal_type']); 
		 foreach($get_deal_type as $deal_key=>$get_deal_val){
		 $deal_type_data[$key_val][]  = $deal_name[$get_deal_val];  
		 }
		 $sheet_array[$key_val]['partner_acc_name']  = $actual_val['partner_acc_name']; 
		 $sheet_array[$key_val]['primary_partner_id']  = $actual_val['primary_partner_id'];
		 $sheet_array[$key_val]['transaction_id']  = $actual_val['transaction_id'];
		 $sheet_array[$key_val]['transaction_date']  = $actual_val['transaction_date'];
		 $sheet_array[$key_val]['pipeline_levels']  = $pipeline_levels_name[$actual_val['pipeline_levels']];$sheet_array[$key_val]['secondary_partner_id']  = $actual_val['secondary_partner_id']; 	 		
		 $sheet_array[$key_val]['deal_name']  = $actual_val['deal_name']; 	 
         $sheet_array[$key_val]['product_name']  = $product_name[$actual_val['products']]; 	
		 $sheet_array[$key_val]['product_id']    =  $actual_val['products']; 
		 $sheet_array[$key_val]['unit']    =  $actual_val['unit']; 
		
		 $sheet_array[$key_val]['dollar']    =  $actual_val['dollar']; 
	     $sheet_array[$key_val]['deal_type']  = implode(",",$deal_type_data[$key_val]);
		 $sheet_array[$key_val]['opportunity_type']  = $opportunity_name[$actual_val['opportunity_type']]; 	
		 $sheet_array[$key_val]['partner_type']      = $actual_val['partner_type']==1 ? "direct partner" : "indirect partner"; 
         }
		 
		
		 
			if(count($sheet_array)==0){ $sheet_array = array();  }				
		 $this->Common_model->export_excel_sheet($header,$sheet_array,$file_name);
		}
	 
	 
   
    
    /******************* DIAGNOSTIC REPORT START ********************/
    public function diagnostic_report()
    {   
	    $this->Common_model->check_dashboard_login();
        $data['main_content'] = 'fronthand/twitter/diagnostic_report/diagnostic_report';
        $this->load->view('/includes/template_new', $data);
    }

    public function get_diagnostic_report(){
        $client_id = $this->session->userdata('client_id');
        $startDate = date('Y-m-d H:i:s', strtotime($this->input->post('startDate')));
        $endDate = date('Y-m-d H:i:s', strtotime($this->input->post('endDate')));
       
		//Created
        $tablesError = array('actual_error' => 'tbl_sf_actuals_errors', 'member_error' => 'tbl_sf_members_errors', 'pipeline_error' => 'tbl_sf_pipeline_errors', 'acrrediation_error' => 'tbl_sf_accreditation_errors');
		
        foreach ($tablesError as $key => $tblName) {
            $data['created'][$key] = $this->Common_model->get_data_row('count(client_id) as count', $tblName,array('created_at >=' => $startDate,'created_at <=' => $endDate, 'client_id' => $client_id,))['count'];
		}
		
		$sqltasksummaryerror = "SELECT * FROM tbl_sf_tasksummary_data_errors WHERE insert_date >= '" . $startDate . "' AND insert_date <= '" . $endDate . "' group by task_id";
		//modified
		$sqlpartnerprofileerror = "SELECT * FROM tbl_sf_partner_profile_plan_errors WHERE created_on >= '" . $startDate . "' AND created_on <= '" . $endDate . "'";
		
		$sqlActInsert = "SELECT count(t1.client_id) as count FROM partners_actual_data as t1 inner join partners_accounts as t2 on t1.partner_id=t2.id WHERE t1.client_id = '" . $client_id . "' AND t2.clientid =  '" . $client_id . "' AND t1.insert_date >= '" . $startDate . "' AND t1.insert_date <= '" . $endDate . "' AND t1.salesforce_sales_id IS NOT NULL";

        $sqlPipeInsert = "SELECT count(t1.client_id) as count FROM pipeline_actual_data as t1 inner join partners_accounts as t2 on t1.partner_id=t2.id WHERE t1.client_id =  '" . $client_id . "' AND t2.clientid =  '" . $client_id . "' AND t1.insert_date >= '" . $startDate . "' AND t1.insert_date <= '" . $endDate . "' AND t1.salesforce_pipeline_id IS NOT NULL";
								
		$sqlAccreditationInsert = "SELECT count(t1.accreditation_id) as count FROM tbl_accreditation as t1 inner join members_details as t2 on t1.contact_id=t2.id WHERE t1.insert_date >= '" . $startDate . "' AND t1.insert_date <= '" . $endDate . "'";
								
		$sqltasksummaryInsert = "SELECT distinct task_id FROM  tbl_actionplan_tasksummary_data WHERE insert_date >= '" . $startDate . "' AND insert_date <= '" . $endDate . "' group by task_id";
		
		$sqlpartnerprofileInsert = "SELECT * FROM tbl_actionplan_partner_profile_salesforce_data WHERE created_on >= '" . $startDate . "' AND created_on <= '" . $endDate . "'";
	
		$sqlActMod = "SELECT count(t1.client_id) as count FROM partners_actual_data as t1 inner join partners_accounts as t2 on t1.partner_id=t2.id WHERE t1.client_id =  '" . $client_id . "' AND t2.clientid =  '" . $client_id . "' AND t1.modify_on >= '" . $startDate . "' AND t1.modify_on <= '" . $endDate . "' AND t1.modify_on != t1.insert_date AND t1.salesforce_sales_id IS NOT NULL";

        $sqlPipeMod = "SELECT count(t1.client_id) as count FROM pipeline_actual_data as t1 inner join partners_accounts as t2 on t1.partner_id=t2.id WHERE t1.client_id =  '" . $client_id . "' AND t2.clientid =  '" . $client_id . "' AND t1.modified_on >= '" . $startDate . "' AND t1.modified_on <= '" . $endDate . "' AND t1.modified_on != t1.insert_date AND t1.salesforce_pipeline_id IS NOT NULL";
							
		$sqlAccreditationMod = "SELECT count(t1.accreditation_id) as count FROM tbl_accreditation as t1 inner join members_details as t2 on t1.contact_id=t2.id WHERE t1.modify_on >= '" . $startDate . "' AND t1.modify_on <= '" . $endDate . "' AND t1.modify_on != t1.insert_date";
							
		$sqltasksummaryMod = "SELECT * FROM tbl_actionplan_tasksummary_data WHERE modify_on >= '" . $startDate . "' AND modify_on <= '" . $endDate . "' AND modify_on != insert_date group by task_id";
		
		$sqlpartnerprofileMod = "SELECT * FROM tbl_actionplan_partner_profile_salesforce_data WHERE updated_on >= '" . $startDate . "' AND updated_on <= '" . $endDate . "' AND updated_on != created_on";
								
				
		$data['created']['actual'] = $this->Common_model->get_conditional_array($sqlActInsert)[0]['count'];
        $data['modified']['actual'] = $this->Common_model->get_conditional_array($sqlActMod)[0]['count'];
        $data['created']['pipeline'] = $this->Common_model->get_conditional_array($sqlPipeInsert)[0]['count'];
        $data['modified']['pipeline'] = $this->Common_model->get_conditional_array($sqlPipeMod)[0]['count'];
		$data['created']['accreditation'] = $this->Common_model->get_conditional_array($sqlAccreditationInsert)[0]['count'];
		$data['modified']['accreditation'] = $this->Common_model->get_conditional_array($sqlAccreditationMod)[0]['count'];

		$tasksummary = $this->Common_model->get_conditional_array($sqltasksummaryInsert);
		$data['created']['tasksummary'] = count($tasksummary);
		
		$tasksummary_mod = $this->Common_model->get_conditional_array($sqltasksummaryMod);
		$data['modified']['tasksummary'] = count($tasksummary_mod);
		
		$partnerprofile = $this->Common_model->get_conditional_array($sqlpartnerprofileInsert);
		$data['created']['partnerprofile'] = count($partnerprofile);
		
		$partnerprofile_mod = $this->Common_model->get_conditional_array($sqlpartnerprofileMod);
		$data['modified']['partnerprofile'] = count($partnerprofile_mod);
		
		$tasksummary_error = $this->Common_model->get_conditional_array($sqltasksummaryerror);
		$data['error']['tasksummary'] = count($tasksummary_error);
		
		$partnerprofile_error = $this->Common_model->get_conditional_array($sqlpartnerprofileerror);
		$data['error']['partnerprofile'] = count($partnerprofile_error);

        $sqlInsertP = "select count(t1.clientid) as count from partners_accounts as t1 left join account_channel_manager t3 on t1.crm_id = t3.crm_id where t1.clientid = '" . $client_id . "' AND t1.insert_date >='" . $startDate . "' AND t1.insert_date <='" . $endDate . "' AND t1.salesforce_partner_id IS NOT NULL";

        $sqlModfP = "select count(t1.clientid) as count from partners_accounts as t1 left join account_channel_manager t3 on t1.crm_id = t3.crm_id where t1.clientid = '" . $client_id . "' AND t1.modified_on >='" . $startDate . "' AND t1.modified_on <='" . $endDate . "' AND t1.modified_on != t1.insert_date AND t1.salesforce_partner_id IS NOT NULL";

        $sqlInsertM = "select count(t2.clientid) as count from partners_accounts as t1 inner join members_details as t2 on t1.id = t2.partner_id where t1.clientid = '" . $client_id . "' AND t2.clientid = '" . $client_id . "' AND t2.insert_date >='" . $startDate . "' AND t2.insert_date <='" . $endDate . "' AND t2.salesforce_contact_id IS NOT NULL  ";

        $sqlModfM = "select count(t2.clientid) as count from partners_accounts as t1 inner join members_details as t2 on t1.id = t2.partner_id where t1.clientid = '" . $client_id . "' AND t2.clientid = '" . $client_id . "' AND t2.last_updated >='" . $startDate . "' AND t2.last_updated <='" . $endDate . "' AND t2.last_updated != t2.insert_date AND t2.salesforce_contact_id IS NOT NULL ";

        $data['created']['partner'] = $this->Common_model->get_conditional_array($sqlInsertP)[0]['count'];
        $data['modified']['partner'] = $this->Common_model->get_conditional_array($sqlModfP)[0]['count'];
        $data['created']['member'] = $this->Common_model->get_conditional_array($sqlInsertM)[0]['count'];
        $data['modified']['member'] = $this->Common_model->get_conditional_array($sqlModfM)[0]['count'];

        $data['tempUrl'] = '/' . strtotime($startDate) . '/' . strtotime($endDate) . '/1/';
        $this->load->view('fronthand/twitter/diagnostic_report/diagnostic_table', $data);
    }

    /******************* DIAGNOSTIC REPORT END ********************/

    /****** partner contact settings starts*********/
    public function partner_contact_settings()
    {   
	    $this->Common_model->check_dashboard_login();
        $clientid = $this->session->userdata('client_id');
        $data['is_checked'] = $this->Common_model->get_data_row('is_checked', 'client_pl_steps', array('clientid' => $clientid));

        $data['main_content'] = 'fronthand/twitter/manage_theme/partner_contact_settings';
        $this->load->view('/includes/template_new', $data);
    }

    public function save_settings()
    {
        $clientid = $this->session->userdata('client_id');
        //debug($this->input->post('is_checked'));die;
        if ($this->input->post('is_checked') != '') {
            $is_checked = $this->input->post('is_checked');
            $this->Common_model->update_data('client_pl_steps', array('is_checked' => $is_checked), array('clientid' => $clientid));

            $this->session->set_flashdata('msg_success', "<div style='text-align:center' class='alert alert-success'>Data Updated Successfully</div>");
            redirect('dashboard/partner_contact_settings');
        }
    }

    /****** partner contact settings ends*********/

    //function to check partner with memebers having same email
    public function check_member_partner()
    {
        $client_id = $this->session->userdata('clientid');
        $email = $this->input->post('member_email');
        $check_member = array();
        $check_member = $this->Common_model->get_data_array('id,partner_id,partner_name', 'members_details', array('email' => trim($email), 'clientid' => $client_id));
        if (count($check_member) > 1) {
            foreach ($check_member as $data) {
                echo '<option value="' . $data['partner_id'] . '">' . $data['partner_name'] . ' </option>';
            }
        }
    }

    /* ************** Parametric filter code start ********************   */
    public function getFilterType($val)
    {
        $arr = array('add_cam_filter' => 'cam', 'add_partner_filter' => 'partner', 'add_pipeline_filter' => 'pipeline', 'add_sales_filter' => 'actual_sales');

        return $arr[$val];
    }

    public function iframe_filters($get_filter)
    {   
	    $client_id = $this->session->userdata('clientid');
        $this->load->model('Parametric_model');
        $data = $this->display_search_clientID($client_id);
        if (isset($_POST['delete_all']) and count($this->input->post('filter_id_arr')) > 0) {
            $data['get_filter'] = $get_filter = $_POST['delete_all'];
            $this->db->where_in('manage_filter_id', $this->input->post('filter_id_arr'));
            $this->db->delete('tbl_manage_filter');
        }
        // $data['get_filter'] = $this->uri->segment(3);
        $search_id = $this->uri->segment(4);
        if ($this->input->post('get_filter')) {
            $data['get_filter'] = $this->input->post('get_filter');
        } elseif ($get_filter) {
            $data['get_filter'] = $get_filter;
        } elseif ($this->uri->segment(3)) {
            $data['get_filter'] = $this->uri->segment(3);
        } else {
            $data['get_filter'] = 'add_cam_filter';
        }
        $client_id = $data['client_id'];
        if (isset($_POST['save_filter']) and count($_POST) > 0) {
            $this->add_filters($_POST['filter_name'], 'iframe');
        }
        $type = $this->getFilterType($data['get_filter']);
        // if($search_id!=0){
        if (empty($search_id)) {
            $search_id = $this->activeFilterID($client_id, $type);
        }
        // }

        $sql = "SELECT filter.*,mapping.type FROM tbl_manage_filter AS filter
					INNER JOIN tbl_filter_mapping as mapping on mapping.manage_filter_id=filter.manage_filter_id
					WHERE mapping.client_id='" . $client_id . "' AND filter.client_id='" . $client_id . "' AND mapping.type='" . $type . "'
					GROUP BY filter.manage_filter_id order by filter.modify_on desc";

        $data['SF_Filters'] = $this->Common_model->get_conditional_array($sql);

        $data['all_flters'] = $this->Parametric_model->get_filtr_records($client_id, $search_id);
        $data['oprator_field'] = $this->Parametric_model->oprator_field();
        $data['partner_fields'] = $this->Parametric_model->partner_fields();
        $data['get_parametric_tables'] = $this->Parametric_model->get_parametric_tables();
        $data['search_id'] = $search_id;
        $this->display_search_view($data, 'iframe_filters',$redirectFromDelet="");
    }

    public function manage_filter($temp = '', $redirectFromDelet = '')
    {   
	    $this->Common_model->check_dashboard_login();
	    $client_id = $this->session->userdata('clientid');
        $filterRedirect = array('add_partner_filter', 'add_cam_filter', 'add_pipeline_filter', 'add_sales_filter');

        if (in_array($temp, $filterRedirect)) {
            $redirectFromDelet = '';
            $get_filter = $temp;
        } else {
            $get_filter = '';
        }
        if ($redirectFromDelet) {
            $data = $this->display_search_clientID($redirectFromDelet);
        } else {
            $data = $this->display_search_clientID($client_id);
        }
        $data['get_filter'] = $get_filter;
        if (isset($_POST['delete_all']) and count($this->input->post('filter_id_arr')) > 0) {
            $data['get_filter'] = $get_filter = $_POST['delete_all'];
            $this->db->where_in('manage_filter_id', $this->input->post('filter_id_arr'));
            $this->db->delete('tbl_manage_filter');
        }
        $client_id = $data['client_id'];
        $this->load->model('Parametric_model');
        $sql = "SELECT filter.*,mapping.type FROM tbl_manage_filter AS filter
					INNER JOIN tbl_filter_mapping as mapping on mapping.manage_filter_id=filter.manage_filter_id
					WHERE mapping.client_id='" . $client_id . "' AND filter.client_id='" . $client_id . "' GROUP BY filter.manage_filter_id order by filter.modify_on desc";

        $allFilters = $this->Common_model->get_conditional_array($sql);
        foreach ($allFilters as $tempdata) {
            if ($tempdata['status'] == 1) {
                $data['status'][$tempdata['type']] = $tempdata['manage_filter_id'];
                $sql = "SELECT * FROM tbl_filter_mapping as mapping
					WHERE client_id='" . $client_id . "' AND manage_filter_id = " . $tempdata['manage_filter_id'];

                $data['activeFilter'][$tempdata['type']] = $this->Common_model->get_conditional_array($sql);
            }
            if ($tempdata['type'] == 'cam') {
                $data['cam'][$tempdata['manage_filter_id']] = $tempdata;
            } elseif ($tempdata['type'] == 'partner') {
                $data['partner'][$tempdata['manage_filter_id']] = $tempdata;
            } elseif ($tempdata['type'] == 'pipeline') {
                $data['pipeline'][$tempdata['manage_filter_id']] = $tempdata;
            } elseif ($tempdata['type'] == 'actual_sales') {
                $data['actual_sales'][$tempdata['manage_filter_id']] = $tempdata;
            }
            // debug($tempdata,1);
        }
        // debug($data['activeFilter'],1);
        $data['oprator_field'] = $this->Parametric_model->oprator_field();
        $data['partner_fields'] = $this->Parametric_model->partner_fields();
        $data['get_parametric_tables'] = $this->Parametric_model->get_parametric_tables();
        if (empty($get_filter)) {
            $this->display_search_view($data, 'list-filter.php', $redirectFromDelet);
        } else {
            $this->manage_filters($get_filter);
        }
    }

    // manage filter section in dashboard redirectFromDelet
    public function manage_filters($get_filter = '', $search_id = '')
    {   
	
	    $this->Common_model->check_dashboard_login();
	    $client_id = $this->session->userdata('clientid');
        $data = $this->display_search_clientID($client_id);
        if (empty($search_id) && is_numeric($this->uri->segment(4))) {
            $search_id = $this->uri->segment(4);
        }

        // debug($search_id);
        if (empty($get_filter)) {
            $data['get_filter'] = $this->uri->segment(3);
        } else {
            $data['get_filter'] = $get_filter;
        }

        $this->load->model('Parametric_model');

        if ($data['get_filter']) {
            if (isset($_POST['delete_all']) and count($this->input->post('filter_id_arr')) > 0) {
                $data['get_filter'] = $get_filter = $_POST['delete_all'];
                $this->db->where_in('manage_filter_id', $this->input->post('filter_id_arr'));
                $this->db->delete('tbl_manage_filter');
            }

            if (isset($_POST['save_filter']) and count($_POST) > 0) {
                $this->add_filters($data['get_filter']);
            }

            $client_id = $data['client_id'];
            $type = $this->getFilterType($data['get_filter']);

            if (empty($search_id)) {
                $search_id = $this->activeFilterID($client_id, $type);
            }
            $sql = "SELECT filter.*,mapping.type FROM tbl_manage_filter AS filter
						INNER JOIN tbl_filter_mapping as mapping on mapping.manage_filter_id=filter.manage_filter_id
						WHERE mapping.client_id='" . $client_id . "' AND filter.client_id='" . $client_id . "' AND mapping.type='" . $type . "'
						GROUP BY filter.manage_filter_id order by filter.modify_on desc";

            $data['SF_Filters'] = $this->Common_model->get_conditional_array($sql);
        } else {
            $data['SF_Filters'] = array();
        }
        // debug($data['SF_Filters']);
        $data['all_flters'] = $this->Parametric_model->get_filtr_records($client_id, $search_id);
        $data['oprator_field'] = $this->Parametric_model->oprator_field();
        $data['partner_fields'] = $this->Parametric_model->partner_fields();
        $data['get_parametric_tables'] = $this->Parametric_model->get_parametric_tables();
        $data['search_id'] = $search_id;

        if ($search_id && !empty($data['all_flters'])) {
            $data['show_tab_on_edit'] = $data['all_flters'][0]['filter_table'];
        }
        $this->display_search_view($data,'manage_filters',$redirectFromDelet="");
    }

    public function activeFilterID($client_id, $type)
    {
        $sql = "SELECT filter.manage_filter_id,filter.status FROM tbl_manage_filter AS filter
						INNER JOIN tbl_filter_mapping as mapping on mapping.manage_filter_id=filter.manage_filter_id
						WHERE mapping.client_id='" . $client_id . "' AND filter.client_id='" . $client_id . "' AND mapping.type='" . $type . "' and filter.status='1'
						GROUP BY filter.manage_filter_id limit 1";
        $result = $this->Common_model->get_conditional_array($sql);
        if (!empty($result)) {
            return $result[0]['manage_filter_id'];
        } else {
            return 0;
        }
    }

    public function activateRecentAddedFilter($client_id, $type, $filterID)
    {
        $sql = "UPDATE tbl_manage_filter
					JOIN tbl_filter_mapping ON tbl_filter_mapping.manage_filter_id = tbl_manage_filter.manage_filter_id
					SET tbl_manage_filter.status='0'
					WHERE tbl_filter_mapping.type='" . $type . "'
					AND tbl_filter_mapping.client_id=$client_id
					AND tbl_manage_filter.client_id=$client_id ";
				$result =	$this->db->query($sql);
      
        $arr['status'] = '1';
        $this->Common_model->update_data('tbl_manage_filter', $arr, array('client_id' => $client_id, 'manage_filter_id' => $filterID));
    }

    public function add_filters($filter = '', $from = '')
    {
        $search_id = $_POST['search_id'];
       $client_id = $this->session->userdata('clientid');
        $this->load->model('Parametric_model');
        $data = $this->display_search_clientID($client_id);
        $client_id = $data['client_id'];
        if ($search_id != '') {
            $this->db->where('client_id', $client_id);
            $this->db->where('manage_filter_id', $search_id);
            $this->db->delete('tbl_manage_filter');
        }
        $lastID = $this->Parametric_model->insert_filters($_POST, $client_id);

        $type = $this->getFilterType($filter);
        $this->activateRecentAddedFilter($client_id, $type, $lastID);

        $activeFilterID = $this->activeFilterID($client_id, $type);
        if (!empty($activeFilterID)) {
            $urlString = '/' . $activeFilterID;
        } else {
            $urlString = '/0';
        }
        // debug($lastID);
        // debug($search_id);
        // debug($this->input->post(),1);
        if ($from == 'iframe') {
            redirect('dashboard/iframe_filters/' . $filter . $urlString . '/success?client_id=' . $data['iframeClient']);
        } elseif ($this->input->post('search_id')) {
            $ajaxDiv = $this->toggleAjaxDiv($filter);
            $class = $this->toggleClass($filter);
            echo $class . ',' . $ajaxDiv . ',' . $lastID . ',' . $filter . ',' . $type;
            exit;
        } else {
            redirect('dashboard/manage_filter/success');
        }
    }

    public function toggleClass($filter)
    {
        if ($filter == 'add_partner_filter') {
            return 'togglePartner';
        } elseif ($filter == 'add_cam_filter') {
            return 'toggleCam';
        } elseif ($filter == 'add_pipeline_filter') {
            return 'togglePipeline';
        } elseif ($filter == 'add_sales_filter') {
            return 'toggleSales';
        } else {
            return '';
        }
    }

    public function toggleAjaxDiv($filter)
    {
        if ($filter == 'add_partner_filter') {
            return 'ajax_partner';
        } elseif ($filter == 'add_cam_filter') {
            return 'ajax_cam';
        } elseif ($filter == 'add_pipeline_filter') {
            return 'ajax_pipeline';
        } elseif ($filter == 'add_sales_filter') {
            return 'ajax_sale';
        } else {
            return '';
        }
    }

    public function delete_filter()
    {  
	    $client_id = $this->session->userdata('clientid');
        $data = $this->display_search_clientID($client_id);
         $get_filter = $this->uri->segment(3);
        $search_id = $this->uri->segment(4);
        $this->db->where('client_id', $client_id);
        $this->db->where('manage_filter_id', $search_id);
        $this->db->delete('tbl_manage_filter');
        if ($data['iframeClient']) {
            redirect('dashboard/iframe_filters/' . $get_filter . '/delete?client_id=' . $data['iframeClient']);
        } else {
            redirect('dashboard/manage_filter/' . $get_filter . '/delete');
        }
    }

    // function for activate filter
    public function active_filter()
    {   
	    $client_id = $this->session->userdata('clientid');
        $data = $this->display_search_clientID($client_id);
       
        $filter = $this->uri->segment(3);

        $where1 = array('client_id' => $client_id);
        $arr['tbl_manage_filter.status'] = '0';

        $sql = "UPDATE tbl_manage_filter
					JOIN tbl_filter_mapping ON tbl_filter_mapping.manage_filter_id = tbl_manage_filter.manage_filter_id
					SET tbl_manage_filter.status='0'
					WHERE tbl_filter_mapping.type='" . $_POST['filter_type'] . "'
					AND tbl_filter_mapping.client_id=$client_id
					AND tbl_manage_filter.client_id=$client_id ";
        $this->db->query($sql);
        // $this->Common_model->update_data('tbl_manage_filter',$arr,array('client_id' =>$client_id));

        if ($_POST['filter_id'] != '' && $_POST['filter_type'] != '') {
            $arr['status'] = '1';
            $this->Common_model->update_data('tbl_manage_filter', $arr, array('client_id' => $client_id, 'manage_filter_id' => $_POST['filter_id']));
        }
        if ($this->input->post('type') == 'iframe') {
            $this->iframe_filters($filter);
        } elseif ($filter) {
            $this->manage_filters($filter);
        } else {
            $this->manage_filter();
        }
    }

    public function display_search_clientID($client_id)
    {
        if ($this->input->get('client_id')) { //iframe
            $result['client_id'] = $this->input->get('client_id');
            $result['iframeClient'] = $result['client_id'];
        } elseif ($client_id) { // redirect From delet
            $result['client_id'] = $client_id;
            $result['iframeClient'] = $client_id;
        } else { // web view
            if (!$this->session->userdata('dealer_login')) {
                redirect('manageaudit');
            }
            $result['client_id'] = $this->session->userdata('client_id');
            $result['iframeClient'] = '';
        }
        if ($result['iframeClient']) {
            $findClientInDb = $this->Common_model->get_data_row('client_id', 'admin_clients', array('client_id' => $result['client_id']));
            if ($findClientInDb) {
                return $result;
            } else {
                echo '<center><span style="color:red;">Client does not exist.</span></center>';
                exit;
            }
        } else {
            return $result;
        }
    }

    public function display_search_view($data, $view, $redirectFromDelet)
    {
        if ($this->input->get('client_id') || $redirectFromDelet) { //iframe || Redirect From Delet
            $data['main_content'] = "fronthand/twitter/manage_filters/$view";
            $this->load->view('/includes/template_nologin', $data);
        } else { // web view
            $data['main_content'] = "fronthand/twitter/manage_filters/$view";
            $this->load->view('/includes/template_new', $data);
        }
    }

    /* ************** Parametric filter code end ********************   */

    /* ************* partner profile  section start ************ */

    // function for change partner profile status and list data
    public function partner_profile()
    {
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $clientid = $this->session->userdata('client_id');
        $this->load->model('Managegoals_model');
        $this->load->model('Managestrategies_model');
        $this->load->model('Manageproducts_model');
        $data['get_status'] = $this->Common_model->get_data_row('status,partner_profile_status_id', 'tbl_partner_profile_status', array('client_id' => $clientid));

        $this->db->select('*');
        $this->db->from('tbl_partner_profile');
        $this->db->where(array('client_id' => $clientid));
        $this->db->order_by('step_order', 'ASC');
        $query = $this->db->get();
        $data['list_profile_data'] = $query->result_array();

        $all_titles = $this->Common_model->get_data_array('*', 'tbl_partner_profile_field_titles', array('client_id' => $clientid));
        foreach ($all_titles as $title_fields) {
            $data['all_titles'][$title_fields['partner_profile_id']][] = $title_fields['field_title'];
        }
        $data['group_goal_strategy'] = $this->Common_model->get_data_row('group_goal_strategy', 'admin_clients', array('client_id' => $clientid))['group_goal_strategy'];
        if (isset($_POST['status'])) {
            $profile_arr['status'] = $this->input->post('status');
            $profile_arr['client_id'] = $clientid;
            if (is_array($data['get_status']) and count($data['get_status']) > 0) {
                $this->Common_model->update_data('tbl_partner_profile_status', $profile_arr, array('client_id' => $clientid));
            } else {
                $this->Common_model->insert_data('tbl_partner_profile_status', $profile_arr);
            }
        }
        $query = $this->Manageproducts_model->getdataall($clientid);

        $pre_query = "select t1.partner_profile_id,t2.field_title from tbl_partner_profile t1 join tbl_partner_profile_field_titles t2 on t1.partner_profile_id = t2.partner_profile_id where t1.profile_type = '5' AND t1.client_id = $clientid";
        $data['prepublish_titles'] = $this->Common_model->get_conditional_array($pre_query);

        $data['child_data'] = array(); //$this->Common_model->get_data_array('partner_profile_child_id,partner_profile_id','tbl_partner_profile_child',array('client_id'=>$clientid));
        if ($this->input->post('module_type') != '') {
            //$id = $this->input->post('goal_id');
            $data['rowID'] = $this->input->post('rowID');
            $data['module_type'] = $this->input->post('module_type');
            $count = $this->input->post('count');
            if ($count == 'full') {
                $loop_count = 1;
            } elseif ($count == 'half') {
                $loop_count = 2;
            } else {
                $loop_count = 3;
            }
            $data['loop_count'] = $loop_count;
            $partner_profile_id = $this->input->post('partner_profile_id');
            $partner_profile_child_id = $this->input->post('child_id');
            $data['partner_profile_id'] = $partner_profile_id;
            $data['partner_profile_child_id'] = $partner_profile_child_id;
            $field_titles = $this->Common_model->get_data_array('field_title_id,type,field_title', 'tbl_partner_profile_field_titles', array('client_id' => $clientid, 'partner_profile_id' => $partner_profile_id));
            $profile_titles = $this->Common_model->keyValuepair($field_titles, 'field_title', 'type');
            $profile_title_ids = $this->Common_model->keyValuepair($field_titles, 'field_title_id', 'type');
            $data['profile_titles'] = $profile_titles;
            $data['profile_title_ids'] = $profile_title_ids;

            $prePublish_selected = $this->Common_model->get_data_array('prepublish_id', 'tbl_strategy_prepublish_selection', array('strategy_goal_id' => $partner_profile_id));
            foreach ($prePublish_selected as $pre) {
                $data['prePublish_selected'][] = $pre['prepublish_id'];
            }
            // debug($data['module_type']);exit;
            if ($data['module_type'] == '5' || $data['module_type'] == '8') {
                // $colmActivity ="";
                if ($data['module_type'] == 8) { //MDF
                    $colTable = 'tbl_strategy_goal_colums';
                    $primKey = 'strategy_goal_colum_id';
                    $cond = "AND column_type IN('30')";
                    // $colmActivity = ",is_activity_type";
                    if ($partner_profile_id) {
                        $sql = "SELECT $primKey,type,column_name,column_type,default_order,column_width, data_type,colum_order,tool_tip_act ,tool_tip_val,show_hide,desc_as_text,create_own FROM $colTable WHERE client_id=$clientid AND partner_profile_id=$partner_profile_id $cond ORDER BY colum_order ASC,$primKey ASC ";
                        $data['MDF_Col'] = $this->Common_model->get_conditional_array($sql);
                    }
                }
                if ($data['module_type'] == '8') {
                    $colTable = 'tbl_strategy_goal_colums';
                    $primKey = 'strategy_goal_colum_id';
                    $cond = "AND column_type IN('0','1','20')";
                    $data['parent_child'] = $this->input->post('parent_child');
                } else {
                    $colTable = 'tbl_pre_publish_columns';
                    $primKey = 'tbl_pre_publish_column_id';
                    $cond = "AND column_type IN('0','1','20')";
                }

                if (!empty($partner_profile_id)) {
                    $pre_publish_data = $this->Common_model->get_data_array('tbl_pre_publish_id,type,description,field_title,partner_id,create_own', 'tbl_pre_publish_profile', array('client_id' => $clientid, 'partner_profile_id' => $partner_profile_id, 'partner_id' => 0));
                    // debug($this->db->last_query());
                    // debug($pre_publish_data);
                    $sql = "SELECT $primKey,type,column_name,is_activity_type,is_activity_desc,column_type,column_width, data_type,tool_tip_act ,tool_tip_val,show_hide,desc_as_text,create_own,default_order FROM $colTable WHERE client_id=$clientid AND partner_profile_id=$partner_profile_id $cond ORDER BY column_type ASC,$primKey ASC ";

                    $pre_profile_columns = $this->Common_model->get_conditional_array($sql);

                    foreach ($pre_publish_data as $temp) {
                        $data['pre_publish_data'][$temp['type']][] = $temp['field_title'];
                        $data['pre_publish_type'][$temp['type']][] = $temp['partner_id'];
                        $data['pre_publish_profile_ids'][$temp['type']][] = $temp['tbl_pre_publish_id'];
                        $data['goal_desc'][$temp['type']][] = $temp['description'];
                        $data['create_own'][$temp['type']][] = $temp['create_own'];
                    }
                    // debug($data['pre_publish_data']);
                    $sql = "SELECT group_concat(id order by id asc ) as id, id as orderbykey,partner_profile_id,column_id,group_concat(name order by id asc SEPARATOR '<>,') as name from tbl_plan_dropdown WHERE client_id=$clientid AND partner_profile_id=$partner_profile_id and partner_id=0 group by column_id order by orderbykey asc";

                    $colm_dropdown = $this->Common_model->get_conditional_array($sql);
                    foreach ($colm_dropdown as $temp) {
                        $data['colm_dropdown'][1][$temp['column_id']] = $temp['name'];
                        $data['colm_dropdown_ids'][1][$temp['column_id']] = $temp['id'];
                    }

                    $sql = "SELECT group_concat(id order by id asc ) as id, id as orderbykey,partner_profile_id,goal_strategy_id,group_concat(name order by id asc  SEPARATOR '<>,') as name from tbl_plan_description WHERE client_id=$clientid AND partner_profile_id=$partner_profile_id and partner_id=0 group by goal_strategy_id order by orderbykey asc";

                    $strtgy_dropdown = $this->Common_model->get_conditional_array($sql);
                    foreach ($strtgy_dropdown as $temp) {
                        $data['strtgy_dropdown'][1][$temp['goal_strategy_id']] = $temp['name'];
                        $data['strtgy_dropdown_ids'][1][$temp['goal_strategy_id']] = $temp['id'];
                    }
                    foreach ($pre_profile_columns as $temp_col) {
                        $data['data_type'][$temp_col['type']][] = $temp_col['data_type'];
                        $data['tool_tip_act'][$temp_col['type']][] = $temp_col['tool_tip_act'];
                        $data['tool_tip_val'][$temp_col['type']][] = $temp_col['tool_tip_val'];
                        $data['show_hide'][$temp_col['type']][] = $temp_col['show_hide'];
                        $data['desc_as_text'][$temp_col['type']][] = $temp_col['desc_as_text'];
                        $data['create_own_col'][$temp_col['type']][] = $temp_col['create_own'];

                        $data['pre_publish_columns'][$temp_col['type']][] = $temp_col['column_name'];
                        $data['pre_publish_column_type'][$temp_col['type']][] = $temp_col['column_type'];
                        $data['pre_publish_column_ids'][$temp_col['type']][] = $temp_col[$primKey];
                        $data['pre_pub_tblcolumn_width'][$temp_col['type']][] = $temp_col['column_width'];
                        $data['default_order'][$temp_col['type']][] = $temp_col['default_order'];
                        // $data['pre_pub_tblcolumn_width'][$temp_col['type']][]=$temp_col['column_width'];

                        if ($data['module_type'] == '8') {
                            $data['is_activity_type'][$temp_col['type']][] = $temp_col['is_activity_type'];
                            $data['is_activity_desc'][$temp_col['type']][] = $temp_col['is_activity_desc'];
                        }
                    }
                    // debug($data['desc_as_text']);
                    $data['partner_profile'] = $this->Common_model->get_data_row('*', 'tbl_partner_profile', array('client_id' => $clientid, 'partner_profile_id' => $partner_profile_id));

                    //add by anjana starts for child columns
                    if ($data['module_type'] == 8 || $data['module_type'] == 5) {
                        if ($data['module_type'] == '8') {
                            $colTable = 'tbl_strategy_goal_colums';
                            $primKey = 'strategy_goal_colum_id';
                        } else {
                            $colTable = 'tbl_pre_publish_columns';
                            $primKey = 'tbl_pre_publish_column_id';
                        }
                        $section_sql = "SELECT $primKey,type,column_name,is_activity_type,is_activity_desc,column_type,data_type,tool_tip_val,tool_tip_act,column_width,section_number,create_own FROM $colTable WHERE client_id=$clientid AND partner_profile_id=$partner_profile_id AND column_type = '10' ORDER BY column_type ASC,$primKey ASC ";

                        $section_data = $this->Common_model->get_conditional_array($section_sql);

                        foreach ($section_data as $section_col) {
                            $data['data_type_section'][$section_col['type']][$section_col['section_number']][] = $section_col['data_type'];
                            $data['tool_tip_val_section'][$section_col['type']][$section_col['section_number']][] = $section_col['tool_tip_val'];
                            $data['tool_tip_act_section'][$section_col['type']][$section_col['section_number']][] = $section_col['tool_tip_act'];

                            $data['section_columns'][$section_col['type']][$section_col['section_number']][] = $section_col['column_name'];
                            $data['section_column_type'][$section_col['type']][$section_col['section_number']][] = $section_col['column_type'];
                            $data['section_column_ids'][$section_col['type']][$section_col['section_number']][] = $section_col[$primKey];
                            $data['section_tblcolumn_width'][$section_col['type']][$section_col['section_number']][] = $section_col['column_width'];
                            $data['create_own_sec'][$section_col['type']][$section_col['section_number']][] = $section_col['create_own'];
                            if ($data['module_type'] == 8) {
                                $data['is_activity_type_s'][$section_col['type']][$section_col['section_number']][] = $section_col['is_activity_type'];
                                $data['is_activity_desc_s'][$section_col['type']][$section_col['section_number']][] = $section_col['is_activity_desc'];
                            }
                        }
                    }
                    //add by anjana ends for child columns

                    //For Strategy Profile
                    //Code By Deepak
                    $strategie_profile_data = $this->Common_model->get_data_array('tbl_strategy_goals_profile_Id,type,create_own,field_title,strategy_description,partner_id', 'tbl_strategy_goals_titles', array('client_id' => $clientid, 'partner_profile_id' => $partner_profile_id, 'partner_id' => '0'));

                    foreach ($strategie_profile_data as $temp) {
                        $data['strategie_profile_data'][$temp['type']][] = $temp['field_title'];
                        $data['strategie_desc'][$temp['type']][] = $temp['strategy_description'];
                        $data['create_own'][$temp['type']][] = $temp['create_own'];
                        $data['strategy_profile_type'][$temp['type']][] = $temp['partner_id'];
                        $data['tbl_strategy_goals_profile_Id'][$temp['type']][] = $temp['tbl_strategy_goals_profile_Id'];
                    }
                }
                // debug($data,1);
            } elseif ($data['module_type'] == 1) {
                $data['scorecard'] = $this->show_scorecard($count, $partner_profile_id);
            } elseif ($data['module_type'] == 0) {
                if (!empty($partner_profile_id)) {
                    $sql = "SELECT tbl_user_editable_column_id,field_type,type,column_name,column_type,column_width FROM tbl_user_editable_columns WHERE client_id=$clientid AND partner_profile_id=$partner_profile_id ORDER BY column_type ASC,tbl_user_editable_column_id ASC";
                    $user_editable_data = $this->Common_model->get_conditional_array($sql);
                    foreach ($user_editable_data as $temp) {
                        $data['user_editable_columns'][$temp['type']][] = $temp['column_name'];
                        $data['user_editable_column_type'][$temp['type']][] = $temp['column_type'];
                        $data['user_editable_field_type'][$temp['type']][] = $temp['field_type'];
                        $data['user_editable_column_ids'][$temp['type']][] = $temp['tbl_user_editable_column_id'];
                        $data['user_editable_column_width'][$temp['type']][] = $temp['column_width'];
                    }
                }

                $data['display_editor'] = $this->input->post('display_editor');
                $data['display_date'] = $this->input->post('display_date');
                $data['display_bullets'] = $this->input->post('display_bullets');
            } elseif ($data['module_type'] == 2) {
                $goals_quer = $this->Managegoals_model->getdataall($clientid);
                $data['goals_s'] = $goals_quer;
                //debug($data['goals']);
                $query1 = $this->Managegoals_model->getdataall_assign_strategies($id);
                $goals = $this->Managegoals_model->getdata($clientid, $id);
                $data['goals'] = $goals;
                $data['column_width'] = $this->Common_model->get_data_array('column_name,column_width,column_type,', 'tbl_goals_column_width', array('client_id' => $clientid));
            } elseif ($data['module_type'] == 3) {
                $strategy_query = $this->Managestrategies_model->getdataall($clientid);
                $data['strategies'] = $strategy_query;
                $query1 = $this->Managestrategies_model->getdataall_assign_strategies($id);
                $strategiess = $this->Managestrategies_model->getdata($clientid, $id);
                $data['strategiess'] = $strategiess;
                $data['column_width'] = $this->Common_model->get_data_array('column_name,column_width,column_type,', 'tbl_strategy_column_width', array('client_id' => $clientid));
            }

            //Manage  Admin Custom Field
            elseif ($data['module_type'] == 7) {
                $data['mrktg_fields'] = $this->Common_model->get_data_array('*', 'tbl_mrktng_colums', array('client_id' => $clientid));

                $data['admin_field'] = $this->Common_model->get_data_array('*', 'tbl_mcal_admfields', array('client_id' => $clientid, 'column_type' => '1'));
                $data['admin_default'] = $this->Common_model->get_data_array('*', 'tbl_mcal_admfields', array('client_id' => $clientid, 'column_type' => '0', 'column_type' => '2'));
                $data['data_type_arr'] = array(
                    '1' => 'Free Text',
                    '2' => 'Currency',
                    '3' => 'Percentage',
                    '4' => 'Date',
                );
                foreach ($data['admin_field'] as $key => $temp_col) {
                    $data['admin_field_column'][$key] = $temp_col['field_title'];
                }
            }
        } else {
            //$data['product_selected'] = '';
        }
        $data['products'] = $query;
        $qbr_status = array(); //$this->Common_model->get_data_row('add_qbr_status','tbl_forecast_year',array('client_id'=>$clientid));
        $data['get_qbr_status'] = $qbr_status['add_qbr_status'];
        $data['qbr_status'] = $qbr_status['add_qbr_status'];
        $data['scorecard_column_title'] = array('0' => 'Category', '1' => 'Topic', '2' => 'Question Title', '3' => 'Current Activities', '4' => 'Planned Activities', '5' => 'Target Achievement Date', '6' => 'Detailed Action Plan');
        $data['scorecard_column'] = $this->Common_model->get_data_array('*', 'tbl_scorecard_column', array('client_id' => $clientid));

        //conditional module apps code
        $sql = "select t1.tbl_conditional_question_id,t1.title,t2.tbl_conditional_response_id,t2.response from tbl_conditional_question as t1 inner join tbl_conditional_response as t2 on t1.tbl_conditional_question_id=t2.conditional_question_id where t1.status='1' and t1.client_id = $clientid ";
        $condition_data = $this->Common_model->get_conditional_array($sql);
        $sql1 = 'select response_id,module_type,partner_profile_id,conditional_module_id from tbl_conditional_module';
        $module_type_data = $this->Common_model->get_conditional_array($sql1);
        foreach ($module_type_data as $k => $type) {
            $module_type[$type['response_id']][] = $type['partner_profile_id'];
            $data['condtionalmodule_id'][$type['response_id']][$type['partner_profile_id']] = $type['conditional_module_id'];
        }

        // $data['conditional_module_id']=implode(",",$conditional_module_id);
        foreach ($condition_data as $k => $val) {
            $temp['tbl_conditional_question_id'] = $val['tbl_conditional_question_id'];
            $temp['module_type'] = $module_type[$val['tbl_conditional_response_id']];
            $temp['title'] = $val['title'];
            $temp['conditional_module_id'] = $val['conditional_module_id'];
            $temp['tbl_conditional_response_id'] = $val['tbl_conditional_response_id'];
            $temp['response'] = $val['response'];
            $data['conditional_response_id'][] = $val['tbl_conditional_response_id'];

            $data['condtionals_data'][$val['tbl_conditional_question_id']][$val['tbl_conditional_response_id']] = $temp;
        }

        $condtional_apps = $this->Common_model->get_data_array('condtional_apps_id,conditional_question_id,conditional_response_id,status', 'tbl_condtional_apps', array('client_id' => $clientid, 'type' => 'action_plan'));
        foreach ($condtional_apps as $apps_status) {
            $data['condtional_apps_status'][$apps_status['conditional_response_id']] = $apps_status['status'];
            $data['condtional_apps_id'][$apps_status['conditional_response_id']] = $apps_status['condtional_apps_id'];
        }
        $sql = "select t1.field_title,t1.partner_profile_id from tbl_partner_profile_field_titles as t1 left join tbl_partner_profile as t2 on t1.partner_profile_id = t2.partner_profile_id where t1.client_id=$clientid and profile_type!='8'";
        $profile_title = $this->Common_model->get_conditional_array($sql);
        foreach ($profile_title as $title) {
            $data['profile_title'][$title['partner_profile_id']][] = $title['field_title'];
        }
        // debug($data['pre_publish_data']);

        $data['main_content'] = 'fronthand/twitter/partner_profile/partner_profile_new';
        $this->load->view('/includes/template_new', $data);
    }

    // function for check existing partner profile

    public function check_partner_profile($partner_profile_id)
    {
        $clientid = $this->session->userdata('client_id');
        $result = $this->Common_model->get_data_row('partner_profile_id', 'tbl_partner_profile', array('partner_profile_id' => $partner_profile_id));

        return $result;
    }

    // function for save partner profile data

    public function save_partner_profile()
    {
        $clientid = $this->session->userdata('client_id');
        $display_editor = $this->input->post('display_editor');
        $field_title = $this->input->post('field_title');
        $field_title_2 = $this->input->post('field_title_2');
        $field_title_3 = $this->input->post('field_title_3');
        $field_width = $this->input->post('field_width');
        $display_date = $this->input->post('display_date');
        $display_bullet = $this->input->post('display_bullet');
        $module_type = $this->input->post('display_type');
        $partner_profile_id = $this->input->post('partner_profile_id');
        $profile_array = $this->input->post('profile_arr');
        $scorecard_profile_ids = $this->input->post('scorecard_profile_id');
        $data_source = $this->input->post('data_source');
        if (is_array($display_editor) and count($display_editor) > 0) {
            foreach ($display_editor as $key => $arr) {
                $list_arr[$key]['partner_profile_id'] = $partner_profile_id[$key];
                $list_arr[$key]['display_editor'] = $display_editor[$key];
                $list_arr[$key]['field_title'] = $field_title[$key];
                $list_arr[$key]['field_title_2'] = $field_title_2[$key];
                $list_arr[$key]['field_title_3'] = $field_title_3[$key];
                $list_arr[$key]['field_width'] = $field_width[$key];
                $list_arr[$key]['display_date'] = $display_date[$key];
                $list_arr[$key]['display_bullet'] = $display_bullet[$key];
                $list_arr[$key]['client_id'] = $clientid;
                $list_arr[$key]['profile_type'] = $module_type[$key];
                $list_arr[$key]['data_source'] = $data_source[$key];
            }
            if (!empty($this->input->post('partner_profile_ids'))) {
                $partner_profile_id_to_del = $this->input->post('partner_profile_ids');
                $partner_profile_id_to_del_str = implode(',', $partner_profile_id_to_del);
                $sql = "DELETE FROM tbl_pre_publish_profile WHERE client_id =$clientid AND partner_profile_id in ($partner_profile_id_to_del_str)";
				$this->db->query($sql);
               
            }
            $scorecard_array = array();
            foreach ($list_arr as $profile_arr) {
                $list_array['partner_profile_id'] = $profile_arr['partner_profile_id'];
                $list_array['display_editor'] = $profile_arr['display_editor'];
                $list_array['field_title'] = $profile_arr['field_title'];
                $list_array['field_title_2'] = $profile_arr['field_title_2'];
                $list_array['field_title_3'] = $profile_arr['field_title_3'];
                $list_array['field_width'] = $profile_arr['field_width'];
                $list_array['display_date'] = $profile_arr['display_date'];
                $list_array['display_bullet'] = $profile_arr['display_bullet'];
                $list_array['client_id'] = $clientid;
                $list_array['profile_type'] = $profile_arr['profile_type'];
                $list_array['data_source'] = implode(',', $profile_arr['data_source']);
                $check_exists = $this->check_partner_profile($profile_arr['partner_profile_id']);
                if (is_array($check_exists) and count($check_exists) > 0) {
                    $this->Common_model->update_data('tbl_partner_profile', $list_array, array('client_id' => $clientid, 'partner_profile_id' => $profile_arr['partner_profile_id']));
                    $last_id[] = $profile_arr['partner_profile_id'];
                    if ($profile_arr['profile_type'] == 5) {
                        if (in_array($profile_arr['partner_profile_id'], $partner_profile_id_to_del)) {
                            $pre_publisArry[] = $profile_arr['partner_profile_id'];
                        }
                    }
                } else {
                    $last_id_num = $this->Common_model->insert_data('tbl_partner_profile', $list_array);
                    //$vineet5mayStart
                    $last_id[] = $last_id_num;
                    if ($profile_arr['profile_type'] == 5) {
                        $pre_publisArry[] = $last_id_num;
                    }
                    //$vineet5mayEND
                }
            }
            if (is_array($profile_array) && count($profile_array) > 0) {
                foreach ($profile_array as $keys => $profile_data) {
                    foreach ($profile_data as $k => $prof) {
                        $scorecard_array['partner_profile_id'] = $last_id[$keys];
                        $scorecard_array['scorecard_category_id'] = $prof[0];
                        $scorecard_array['scorecard_topic_id'] = $prof[1];
                        $scorecard_array['scorecard_question_id'] = $prof[2];
                        $scorecard_array['client_id'] = $clientid;

                        $check_scorecard_exists = $this->check_scorecard_profile($scorecard_profile_ids[$keys][$k][0]);
                        if (is_array($check_scorecard_exists) and count($check_scorecard_exists) > 0) {
                            $this->Common_model->update_data('tbl_scorecard_profile', $scorecard_array, array('client_id' => $clientid, 'tbl_scorecard_profile_id' => $scorecard_profile_ids[$keys][$k][0]));
                        } else {
                            $this->Common_model->insert_data('tbl_scorecard_profile', $scorecard_array);
                        }
                    }
                }
            }
            ////$vineet5mayStart
            if (!empty($this->input->post('pre_publish_title'))) {
                $publishSave = array();
                $titlesArry = $this->input->post('pre_publish_title');
                $i = 0;
                foreach ($titlesArry as $key => $titleDatatemp) {
                    foreach ($titleDatatemp as $row => $titletemp) {
                        foreach ($titletemp as $type => $title) {
                            $temp['partner_profile_id'] = $pre_publisArry[$i];
                            $temp['type'] = $row;
                            $temp['client_id'] = $clientid;
                            $temp['field_title'] = $title;
                            if (!empty($title)) {
                                $publishSave[] = $temp;
                            }
                        }
                    }
                    ++$i;
                }
                $this->db->insert_batch('tbl_pre_publish_profile', $publishSave);
            }
            ////$vineet5mayEND
            $this->session->set_flashdata('message_name', ' Module type updated successfully!');
        }
        redirect('dashboard/partner_profile');
    }

    public function deleteProfile()
    {
        $profileID = $this->input->post('profileID');
        $child_id = $this->input->post('child_id');
        $clientid = $this->session->userdata('client_id');

        $type = $this->Common_model->get_data_row('profile_type', 'tbl_partner_profile', array('client_id' => $clientid, 'partner_profile_id' => $profileID))['profile_type'];
        /* $this->Common_model->delete_records('tbl_partner_profile_full',array('partner_profile_id'=> $profileID,'client_id'=>$clientid));
        $this->Common_model->delete_records('tbl_partner_profile_half',array('partner_profile_id'=> $profileID,'client_id'=>$clientid));
        $this->Common_model->delete_records('tbl_partner_profile_third',array('partner_profile_id'=> $profileID,'client_id'=>$clientid));

        $this->Common_model->delete_records('tbl_partner_profile_field_titles',array('partner_profile_id'=> $profileID,'client_id'=>$clientid));
        $this->Common_model->delete_records('tbl_pre_publish_profile',array('partner_profile_id'=> $profileID,'client_id'=>$clientid));
        $this->Common_model->delete_records('tbl_pre_publish_columns',array('partner_profile_id'=> $profileID,'client_id'=>$clientid));

        $this->Common_model->delete_records('tbl_user_editable_columns',array('partner_profile_id'=> $profileID,'client_id'=>$clientid)); */

        if ($child_id == '') {
            $this->Common_model->delete_records('tbl_partner_profile', array('partner_profile_id' => $profileID, 'client_id' => $clientid));

            $this->Common_model->delete_records('tbl_scorecard_profile', array('partner_profile_id' => $profileID, 'client_id' => $clientid));

            $this->Common_model->delete_records('tbl_user_editable_comment', array('partner_profile_id' => $profileID, 'client_id' => $clientid));

            $this->Common_model->delete_records('tbl_prepublish_profile_comments', array('tbl_pre_publish_id' => $profileID, 'client_id' => $clientid));

            $this->Common_model->delete_records('tbl_strategy_goal_colums', array('partner_profile_id' => $profileID, 'client_id' => $clientid));
            $this->Common_model->delete_records('tbl_strategy_goal_comments', array('partner_profile_id' => $profileID, 'client_id' => $clientid));
        } else {
            // $this->Common_model->delete_records('tbl_strategy_goal_child_colums',array('partner_profile_child_id'=> $child_id,'client_id'=>$clientid));
            // $this->Common_model->delete_records('tbl_partner_profile_child',array('partner_profile_child_id'=> $child_id,'client_id'=>$clientid));
            // $this->Common_model->delete_records('tbl_strategy_goal_child_comments',array('partner_profile_child_id'=> $child_id,'client_id'=>$clientid));
        }

        $this->Common_model->delete_records('tbl_scorecard_column', array('partner_profile_id' => $profileID, 'client_id' => $clientid));
        $this->Common_model->delete_records('tbl_scorecard_comment', array('partner_profile_id' => $profileID, 'client_id' => $clientid));

        if ($type == '2' || $type == '3') {
            $sql = "SELECT GROUP_CONCAT(partner_plan_id) as IDS from  tbl_partner_plan where client_id=$clientid";
            $partner_paln_ids = $this->Common_model->get_conditional_array($sql);
            if (!empty($partner_paln_ids)) {
                if (isset($partner_paln_ids[0]['IDS'])) {
                    $partner_paln_ids = $partner_paln_ids[0]['IDS'];
                    if ($type == '2') {
                        $sql = "DELETE FROM tbl_goals_qbr_data WHERE partner_plan_id in ($partner_paln_ids);";
						$this->db->query($sql);
                        
                        $sql = "DELETE FROM tbl_partner_plan_goals WHERE partner_plan_id in ($partner_paln_ids);";
						$this->db->query($sql);
                        
                    }
                    if ($type == '3') {
                        $sql = "DELETE FROM tbl_strategy_qbr_data WHERE partner_plan_id in ($partner_paln_ids);";
                       $this->db->query($sql);
                        $sql = "DELETE FROM tbl_partner_plan_strategy WHERE partner_plan_id in ($partner_paln_ids);";
						$this->db->query($sql);
                       
                    }
                }
            }
        }
        //tbl_goals_qbr_data tbl_strategy_qbr_data tbl_partner_plan_goals tbl_partner_plan_strategy

        if ($type == '5' || $type == '8') {
            $this->Common_model->delete_records('tbl_plan_dropdown', array('partner_profile_id' => $profileID, 'client_id' => $clientid, 'module_type' => $type));
            if ($type == '5') {
                $this->Common_model->delete_records('tbl_strategy_prepublish_selection', array('prepublish_id' => $profileID, 'client_id' => $clientid));
            }
            if ($type == '8') {
                $this->Common_model->delete_records('tbl_strategy_prepublish_selection', array('strategy_goal_id' => $profileID, 'client_id' => $clientid));
            }
        }

        //$this->Common_model->delete_records('tbl_prepublish_profile_comments',array('partner_profile_id'=> $profileID,'client_id'=>$clientid));
        redirect('dashboard/partner_profile');
    }

    /* ************* partner profile section end ************ */

    //add by anjana
    public function set_average_deal()
    {
        $client_id = $this->session->userdata('client_id');
        $data['average_deal'] = $this->Common_model->get_data_row('average_deal', 'admin_clients', array('client_id' => $client_id));
        $data['main_content'] = 'camdashboard/reports/set_average_deal';
        $this->load->view('/includes/template_new', $data);
    }

    public function update_average_deal()
    {
        $client_id = $this->session->userdata('client_id');
        $average_deal = $this->input->post('average_deal');
        $update = $this->Common_model->update_data('admin_clients', array('average_deal' => $average_deal), array('client_id' => $client_id));
        if ($update) {
            $this->session->set_flashdata('avg_msg', "<div class='alert alert-success'>Updated Successfully</div>");
            redirect('dashboard/set_average_deal');
        }
    }

    //add by anjana

    // function for display dual target setting
    public function set_annual_target()
    {   
	    $this->Common_model->check_dashboard_login();
        $client_id = $this->session->userdata('client_id');
        $camPartnrs = $this->Cam_model->region_cam_partnrs($client_id);
        $patIds = $this->Common_model->keyValuepair($camPartnrs, 'partner_id', 'partner_id');
        $sql = 'SELECT id from tbl_set_target WHERE partner_id IN (' . implode(',', $patIds) . ') limit 0,1;';
        $data['plan_exist'] = $this->Common_model->get_conditional_array($sql)[0];
        $data['get_quaters'] = $this->Common_model->get_quarter($client_id);
        $data['main_content'] = 'admin/set_annual_target/set_target';
        // $this->load->view('/includes/template_cam_dashboard', $data) ;
        $this->load->view('/includes/template_new', $data);
    }

    public function arryProcessCamPartners($arr)
    {
        $result = array();
        foreach ($arr as $temp) {
            $resultTemp[$temp['primary_region_id']][$temp['cam_id']]['cam_id'] = $temp['cam_id'];
            $resultTemp[$temp['primary_region_id']][$temp['cam_id']]['crm_id'] = $temp['crm_id'];
            $resultTemp[$temp['primary_region_id']][$temp['cam_id']]['crm_acc_name'] = $temp['crm_acc_name'];
            $resultTemp[$temp['primary_region_id']][$temp['cam_id']]['primary_region_id'] = $temp['primary_region_id'];

            $resultTemp[$temp['primary_region_id']][$temp['cam_id']]['partners'][$temp['partner_id']]['partner_id'] = $temp['partner_id'];
            $resultTemp[$temp['primary_region_id']][$temp['cam_id']]['partners'][$temp['partner_id']]['partner_acc_name'] = $temp['partner_acc_name'];
            $resultTemp[$temp['primary_region_id']][$temp['cam_id']]['partners'][$temp['partner_id']]['primary_partner_id'] = $temp['primary_partner_id'];
            $resultTemp[$temp['primary_region_id']][$temp['cam_id']]['partners'][$temp['partner_id']]['secondary_partner_id'] = $temp['secondary_partner_id'];
            $resultTemp[$temp['primary_region_id']][$temp['cam_id']]['partners'][$temp['partner_id']]['cam_id'] = $temp['cam_id'];

            $resultCount[$temp['cam_id']][] = 0;
            $cam[$temp['primary_region_id']][] = 0;
        }
        $result['cam_partners'] = $resultTemp;
        $result['partners_count'] = $resultCount;
        $result['cam_count'] = $cam;
        // debug($result);
        return $result;
    }

    public function arrangeRegionArry($result)
    {
        foreach ($result as $temp) {
            $arrangeArr[$temp['region_3_id']]['id'] = $temp['region_3_id'];
            $arrangeArr[$temp['region_3_id']]['name'] = $temp['region_3_name'];
            $arrangeArr[$temp['region_3_id']]['region_2_Id'] = $temp['region_2_id'];
            $arrangeArr[$temp['region_3_id']]['region_1_Id'] = $temp['region_1_id'];

            $arrangeArr[$temp['region_3_id']]['region2'][$temp['region_2_id']]['id'] = $temp['region_2_id'];
            $arrangeArr[$temp['region_3_id']]['region2'][$temp['region_2_id']]['name'] = $temp['region_2_name'];
            $arrangeArr[$temp['region_3_id']]['region2'][$temp['region_2_id']]['parent_level_id'] = $temp['region_2_parent'];

            $arrangeArr[$temp['region_3_id']]['region2'][$temp['region_2_id']]['region1'][$temp['region_1_id']]['id'] = $temp['region_1_id'];
            $arrangeArr[$temp['region_3_id']]['region2'][$temp['region_2_id']]['region1'][$temp['region_1_id']]['name'] = $temp['region_1_name'];
            $arrangeArr[$temp['region_3_id']]['region2'][$temp['region_2_id']]['region1'][$temp['region_1_id']]['parent_level_id'] = $temp['region_3_id'];
            $arrangeArr[$temp['region_3_id']]['region2'][$temp['region_2_id']]['region1'][$temp['region_1_id']]['subparent_level_id'] = $temp['region_2_id'];

            $count_region_2[$temp['region_2_parent']][] = $temp['region_1_id'];
            $count_region_1[$temp['region_2_id']][] = $temp['region_1_id'];
        }

        $data['regions'] = $arrangeArr;
        $data['count_region_2'] = $count_region_2;
        $data['count_region_1'] = $count_region_1;
        // debug($data,1);
        return $data;
    }

    public function target_fy()
    {
        $cam_id = $this->session->userdata('cam_id');
        $client_id = $this->session->userdata('client_id');
        // debug($cam_id);
        // debug($client_id ,1);
        $camPartnrs = $this->Cam_model->region_cam_partnrs($client_id);

        $patIds = $this->Common_model->keyValuepair($camPartnrs, 'partner_id', 'partner_id');

        $sql = "SELECT year,partner_id from tbl_set_target where client_id=$client_id AND partner_id in (" . implode(',', $patIds) . ')  group by partner_id';

        $partnes_exist = $this->Common_model->get_conditional_array($sql);

        $data['partnes_plan_exist'] = $this->Common_model->keyValuepair($partnes_exist, 'partner_id', 'partner_id');

        // debug($data['partnes_plan_exist']);
        // debug($patIds,1);
        $this->load->model('Set_target_model');
        if (!empty($_POST['isSet'])) {
            $data['planned_years'] = $this->Set_target_model->getTargetedYear($cam_id, $client_id);
        } else {
            $data['planned_years'] = array();
        }

        // debug($data);
        $data['years_data'] = $this->Common_model->get_data_row('start_fiscal_year,end_fiscal_year,start_month,end_month', 'tbl_forecast_year', array('client_id' => $client_id));
        $data['months'] = $this->month;
        echo $this->load->view('/admin/set_annual_target/target_fy', $data);
    }

    public function target_method()
    {
        $cam_id = $this->session->userdata('cam_id');
        $client_id = $this->session->userdata('client_id');
        $this->load->model('Set_target_model');
        $yrs = explode(',', $_POST['years']);
        $setFrom = $_POST['setFrom'];
        $levelID = $_POST['levelID'];
        // debug($_POST,1);
        // $setFrom='';
        // $client_id   = $this->session->userdata('client_id');
        if (!empty($_POST['isSet'])) {
            if ($setFrom != 'partner') {
                $data['selected_method'] = $this->Common_model->get_data_row('type', 'tbl_set_target_logs', array('client_id' => $client_id, 'level' => $setFrom, 'set_for_id' => $levelID), 'created', 'DESC')['type'];
            } else {
                $data['selected_method'] = $this->Set_target_model->getSelectedMethodArray($client_id, $yrs, $levelID);
            }
        } else {
            $data['selected_method'] = '';
        }
        // debug($data,1);
        echo $this->load->view('/admin/set_annual_target/target_method', $data);
    }

    public function getViewBYMthod($method)
    {
        if ($method == 'prior_year') {
            return $view = 'sales_target_prior';
        } elseif ($method == 'fixed_annual') {
            return $view = 'sales_target_annual';
        } elseif ($method == 'by_qtr') {
            return $view = 'sales_target_qtrly';
        } elseif ($method == 'by_month') {
            return $view = 'sales_target_month';
        }
    }

    public function getTextStringByLevelText($levelText, $levelID)
    {
        $client_id = $this->session->userdata('client_id');
        if ($levelText == 'partner') {
            $view['levelText'] = 'Partner';
            $table = 'partners_accounts';
            $field = 'partner_acc_name as name';
            $id = 'id';
            $clientString = 'clientid';
        } elseif ($levelText == 'cam') {
            $view['levelText'] = 'CAM';
            $table = 'account_channel_manager';
            $field = 'crm_acc_name as name';
            $id = 'id';
            $clientString = 'clientid';
        } elseif ($levelText == 'region_1') {
            $view['levelText'] = 'Region Level 1';
            $table = 'region_level1';
            $field = 'name';
            $id = 'region_level1_id';
            $clientString = 'client_id';
        } elseif ($levelText == 'region_2') {
            $view['levelText'] = 'Region Level 2';
            $table = 'region_level2';
            $field = 'name';
            $id = 'region_level2_id';
            $clientString = 'client_id';
        } elseif ($levelText == 'region_3') {
            $view['levelText'] = 'Region Level 3';
            $table = 'region_level3';
            $field = 'name';
            $id = 'region_level3_id';
            $clientString = 'client_id';
        }
        $view['LevelName'] = $this->Common_model->get_data_row($field, $table, array($clientString => $client_id, $id => $levelID))['name'];
        // debug($this->db->last_query());
        // debug($view['LevelName'],1);
        return $view;
    }

    public function set_type()
    {
        $client_id = $this->session->userdata('client_id');
        $this->load->model('Set_target_model');
        $yrs = $_POST['years'];
        $method = $_POST['method'];
        $levelID = $_POST['levelID'];
        $levelText = $_POST['levelText'];

        $view = $this->getViewBYMthod($method);
        $data = $this->getTextStringByLevelText($levelText, $levelID);
        // debug($data);
        $data['years'] = explode(',', $yrs);
        if ($method == 'by_qtr') {
            $data['year_qtrs'] = $this->Common_model->year_month_arr(current($data['years']), $this->start_month); //TODO
            // $data['qtrly'] = $this->Set_target_model->getSavedData();
        } elseif ($method == 'by_month') {
            $data['months'] = $this->Common_model->year_month_arr(current($data['years']), $this->start_month);
            // $data['qtrly'] = $this->Set_target_model->getSavedData();
        }
        if ($_POST['method'] == $_POST['oldSelectedMethod']) {
            $camPartnrs = $this->Cam_model->region_cam_partnrs($client_id);
            $patIds = $this->Common_model->keyValuepair($camPartnrs, 'partner_id', 'partner_id');
            $data['prev_data'] = $this->Set_target_model->getSavedData($method, $patIds);
            // debug($test,1);
        } else {
            $data['prev_data'] = array();
        }
        // $client_id     = $this->session->userdata('client_id');
        $data['start_month'] = $this->start_month;
        echo $this->load->view('/admin/set_annual_target/' . $view, $data);
    }

    public function set_level($selectedMethod = '')
    {
        $this->load->model('Set_target_model');
        $cam_id = $this->session->userdata('cam_id');
        $crm_id = $this->session->userdata('crm_id');
        $client_id = $this->session->userdata('client_id');
        // $data['region'] = $this->Common_model->get_data_row('is_region1_director,is_region2_director,is_region3_director','account_channel_manager',array('clientid'=>$client_id,'id'=>$cam_id));

        // $regionIDs=0;
        // $level=0;
        // if($data['region']['is_region1_director'] == 1){
        // $level = 1;
        // }elseif($data['region']['is_region2_director'] == 1){
        // $level = 2;
        // }elseif($data['region']['is_region3_director'] == 1){
        // $level = 3;
        // }
        // $level = 0;
        $level = 3;
        // if(!empty($level)){
        // $regionIDs = $this->Cam_model->level_related_regions_ids($client_id ,$level);
        // }else{
        // $regionIDs = $this->Cam_model->getCamPRimaryRegion($client_id ,$cam_id);
        // }
        // if(!empty($regionIDs)){

        $region_settings = $this->Common_model->get_data_row('*', 'region_settings', array('client_id' => $client_id));
        $active_region = $region_settings['active_region'];

        if ($active_region == 3) {
            $region_where = "r1.client_id = '" . $client_id . "' and r2.client_id = '" . $client_id . "' and r3.client_id = '" . $client_id . "' ";

            $get_id = 'region_level3_id';
            $tbl_name = 'region_level3';
        } elseif ($active_region == 2) {
            $region_where = "r1.client_id = '" . $client_id . "' and r2.client_id = '" . $client_id . "' ";
            $get_id = 'region_level2_id';
            $tbl_name = 'region_level2';
        } elseif ($active_region == 1) {
            $region_where = "r1.client_id = '" . $client_id . "' ";
            $get_id = 'region_level1_id';
            $tbl_name = 'region_level1';
        } else {
            $region_where = "r1.client_id = '" . $client_id . "' ";
            $get_id = 'region_level1_id';
            $tbl_name = 'region_level1';
        }

        $region_data = $this->Common_model->get_data_array('*', $tbl_name, array('client_id' => $client_id));

        //echo "<pre>";print_r($data['region_data']);echo "</pre>";

        $regions = "select r3.region_level3_id as region_3_id,r3.name as region_3_name,r2.region_level2_id as region_2_id,r2.name as region_2_name,
					r2.level3_parent_id as region_2_parent,
					r1.region_level1_id as region_1_id,r1.name as region_1_name,r1.level2_parent_id as region_1_parent
					from region_level3 as r3
					right join
					region_level2 as r2 on r3.region_level3_id = r2.level3_parent_id
					right join
					region_level1 as r1 on r2.region_level2_id = r1.level2_parent_id
					where $region_where ";
        // AND r1.region_level1_id IN ($regionIDs)";
        // debug($regions);

        $result = $this->Common_model->get_conditional_array($regions);
        $data = $this->arrangeRegionArry($result);
        $data['selectedMethod'] = $selectedMethod;
        // }else{
        // echo 'No data exists';exit;
        // }
        // if(empty($level)){
        // $camPartnrs=$this->Cam_model->region_cam_partnrs($client_id,$cam_id);
        // }else{
        $camPartnrs = $this->Cam_model->region_cam_partnrs($client_id);
        $patIds = $this->Common_model->keyValuepair($camPartnrs, 'partner_id', 'partner_id');
        // $sql="SELECT id from tbl_set_target WHERE partner_id IN (".implode(',',$patIds).") limit 0,1;";
        // $data['plan_exist'] = $this->Common_model->get_conditional_array($sql)[0];
        // debug($data['plan_exist']);
        // }
        // }else{
        // echo 'No data exists';exit;
        // }
        $camPartnrs = $this->arryProcessCamPartners($camPartnrs);

        $data = array_merge($data, $camPartnrs);
        $years = '';
        // $years= explode(',',$_POST['years']);
        // $data['logs']=$this->Set_target_model->getLogsData();
        $data['logs'] = $this->Set_target_model->getLogsData();
        // debug($data['logs'],1);
        $data['level'] = $level;
        $data['active_region'] = $active_region;
        $data['level_selected'] = $this->Set_target_model->getSetTargetArray($client_id, $years, $patIds);
        // debug($data['level_selected']);
        $data['region_data'] = $this->Common_model->keyValuepair($region_data, 'name', $get_id);

        echo $this->load->view('/admin/set_annual_target/set_annual_target', $data);
    }

    public function saveTargetData()
    {
        $client_id = $this->session->userdata('client_id');
        // get prior year actual data
        $this->load->model('Set_target_model');
        $selectedYears = $_POST['selectedYears'];
        $selectedYears = explode(',', $_POST['selectedYears']);
        $selectedLevel = $_POST['selectedLevel'];
        $selectedMethod = $_POST['selectedMethod'];
        $PartnerIDs = $_POST['PartnerIDs'];
        $tosave = $_POST['tosave']; //frocess
        $oldSelectedMethod = $_POST['oldSelectedMethod']; //frocess
        // $oldSelectedMethod= $_POST['oldSelectedMethod'];//frocess
        $PartnerIDs = explode(',', $PartnerIDs);

        $partnrIDs = $this->Set_target_model->saveLogs();
        if ($selectedMethod == 'prior_year') {
            $get_priors_data = $this->Set_target_model->get_actual_sales_prior($client_id, $partnrIDs, ($selectedYears));
            // debug($get_priors_data,1);
        }
        // debug($data['get_priors_data'],1);
        $camPartnrs = $this->Cam_model->region_cam_partnrs($client_id);
        $patIds = $this->Common_model->keyValuepair($camPartnrs, 'partner_id', 'partner_id');
        // $this->Set_target_model->deletPrevPlanByMethod($selectedYears,$PartnerIDs,'');
        // $this->Set_target_model->deletPrevPlanByMethod($selectedYears,$PartnerIDs,'');

        $this->Set_target_model->deletePrevPlans($patIds);
        // $this->Set_target_model->deletePrevPlans();
        // debug($selectedYears);
        // debug($get_priors_data,1);

        foreach ($selectedYears as $yrCount => $year) {
            foreach ($PartnerIDs as $PrtnrID) {
                $PrtnrID = explode('_', $PrtnrID);
                if ($selectedMethod == 'prior_year') {
                    $priorVal = $get_priors_data[$PrtnrID[4]][$year];
                } else {
                    $priorVal = 0;
                }

                $targetData = $this->getProcessedArrayForTarget($selectedLevel, $year, $selectedMethod, $PrtnrID, $tosave);
				$get_partner_id[] = $targetData['partner_id'];
	            $RecentTargetID = $this->Common_model->insert_data('tbl_set_target', $targetData);
                // $RecentTargetID = 1;
                if ($selectedMethod != 'prior_year') {
                    $targetDetailData = $this->getProcessedArrayForTargetDetail($RecentTargetID, $selectedMethod, $tosave[$year], null, $year);
                } else {
                    $targetDetailData = $this->getProcessedArrayForTargetDetail($RecentTargetID, $selectedMethod, $tosave, $priorVal, $year);
                }

                $saveDetail = $this->db->insert_batch('tbl_set_targetdetail', $targetDetailData);
            }
        }
		if(is_array($get_partner_id) and count($get_partner_id)>0){
		$this->set_target_ppd_data($get_partner_id); // update ppd target value frontend
		}
		
        redirect('dashboard/set_level/' . $selectedMethod);
        // echo $selectedMethod;
    }
    
	// update ppd target value frontend
	function set_target_ppd_data($get_partner_id){
	$partner_id = "'".implode("','", $get_partner_id)."'";
	$sql = "select id,primary_partner_id from partners_accounts where id in($partner_id) ";
	$result = $this->Common_model->get_conditional_array($sql);
	$primary_id = $this->Common_model->keyValuepair($result, 'primary_partner_id', 'id');
	$partners_data  = array_values($primary_id);
	
	$tbl_pps_cron_data['uploaded_partner'] = json_encode($partners_data,true);
	$this->Common_model->update_data('tbl_pps_cron_data', $tbl_pps_cron_data, array('id' =>'1'));
	$partnerid  = 0;
    
	exec('php index.php cronjobs cam_ppd_metric_qtr "sheet_uploaded" "'.$partnerid.'" "'.$this->db->database.'" > /dev/null 2>&1 &');
	
	} 
	
	
    public function getProcessedArrayForTarget($selectedLevel, $year, $selectedMethod, $PrtnrID, $toSave)
    {
        // debug($PrtnrID,1);
        // if($selectedLevel=='region_3'){
        $selectedIDs['region_three_id'] = $PrtnrID[0];
        $selectedIDs['region_two_id'] = $PrtnrID[1];
        $selectedIDs['region_one_id'] = $PrtnrID[2];
        if ($selectedMethod == 'prior_year') {
            $selectedIDs['perctage_val'] = $toSave;
        }
        if ($selectedMethod == 'fixed_annual') {
            $tosaveVal = $_POST[$year];
            $selectedIDs['fixed_val'] = $toSave[$year];
            $selectedIDs['allocation'] = json_encode($tosaveVal);
        }
        $selectedIDs['client_id'] = $this->session->userdata('client_id');
        $selectedIDs['set_from'] = $selectedLevel;
        $selectedIDs['cam_id'] = $PrtnrID[3];
        $selectedIDs['partner_id'] = $PrtnrID[4];
        $selectedIDs['year'] = $year;
        $selectedIDs['type'] = $selectedMethod;
        $selectedIDs['created_by'] = $this->session->userdata('cam_id');

        return $selectedIDs;
    }

    public function getProcessedArrayForTargetDetail($RecentTargetID, $selectedMethod, $tosave, $priorVal, $year)
    {
        // debug($priorVal);
        $result = array();
        if ($selectedMethod == 'by_qtr') {
            $startMonth = $this->start_month;
            for ($i = 1; $i < 13; ++$i) {
                $temp['tbl_set_target_id'] = $RecentTargetID;
                $temp['month'] = $startMonth;
                if ($i > 0 && $i < 4) {
                    $value = ($tosave['Q1'] / 3);
                } elseif ($i > 3 && $i < 7) {
                    $value = ($tosave['Q2'] / 3);
                } elseif ($i > 6 && $i < 10) {
                    $value = ($tosave['Q3'] / 3);
                } elseif ($i < 13 && $i > 9) {
                    $value = ($tosave['Q4'] / 3);
                }
                $temp['value'] = $value;
                $result[] = $temp;

                ++$startMonth;
                if ($startMonth == 13) {
                    $startMonth = 1;
                }
            }
        } elseif ($selectedMethod == 'by_month') {
            foreach ($tosave as $mnth => $value) {
                $temp['tbl_set_target_id'] = $RecentTargetID;
                $temp['month'] = $mnth;
                $temp['value'] = $value;
                $result[] = $temp;
            }
        } elseif ($selectedMethod == 'fixed_annual') {
            $startMonth = $this->start_month;
            $tosaveVal = $_POST[$year];

            for ($i = 1; $i < 13; ++$i) {
                if ($i > 0 && $i < 4) {
                    $value = (($tosave * ($tosaveVal['q1'] / 100)) / 3);
                } elseif ($i > 3 && $i < 7) {
                    $value = (($tosave * ($tosaveVal['q2'] / 100)) / 3);
                } elseif ($i > 6 && $i < 10) {
                    $value = (($tosave * ($tosaveVal['q3'] / 100)) / 3);
                } elseif ($i < 13 && $i > 9) {
                    $value = (($tosave * ($tosaveVal['q4'] / 100)) / 3);
                }
                $temp['value'] = $value;

                $temp['tbl_set_target_id'] = $RecentTargetID;
                $temp['month'] = $startMonth;
                $result[] = $temp;
                ++$startMonth;
                if ($startMonth == 13) {
                    $startMonth = 1;
                }
            }
        } elseif ($selectedMethod == 'prior_year') {
            $startMonth = $this->start_month;
            for ($i = 1; $i < 13; ++$i) {
                $temp['tbl_set_target_id'] = $RecentTargetID;
                $temp['month'] = $startMonth;
                if (isset($priorVal[$startMonth])) {
                    $temp['value'] = ($priorVal[$startMonth] * ($tosave / 100));
                } else {
                    $temp['value'] = 0;
                }
                $result[] = $temp;
                ++$startMonth;
                if ($startMonth == 13) {
                    $startMonth = 1;
                }
            }
            // debug($result,1);
        }

        return $result;
    }

    public function deleteTarget()
    {
        $this->load->model('Set_target_model');
        $client_id = $this->session->userdata('client_id');
        $camPartnrs = $this->Cam_model->region_cam_partnrs($client_id);
        $patIds = $this->Common_model->keyValuepair($camPartnrs, 'partner_id', 'partner_id');
        $this->Set_target_model->deletByPartnerIDs($patIds);
    }

    public function CopyTargetByYear()
    {
        $this->load->model('Set_target_model');
        $year = $_POST['year'];
        $yearArr[] = $year;
        $client_id = $this->session->userdata('client_id');
        $camPartnrs = $this->Cam_model->region_cam_partnrs($client_id);
        $patIds = $this->Common_model->keyValuepair($camPartnrs, 'partner_id', 'partner_id');
        // $toSaveData = $this->Set_target_model->getPrevYearByPartnerIDs($patIds);
        $actual = $this->Set_target_model->get_actual_sales_prior($client_id, $patIds, $yearArr);
        $toSaveData = $this->Set_target_model->getPrevYearByPartnerIDs($patIds, $actual, $year);
        // debug($toSaveData);
        // debug($actual,1);
        $this->Set_target_model->savePrevYearData($toSaveData, $year);
    }

    //function to pass target value from admin to plan value
    public function set_target_post()
    {
        $this->load->model('Set_target_model');
        $client_id = $this->session->userdata('client_id');
        $years_data = $this->Common_model->get_data_row('start_fiscal_year,end_fiscal_year,start_month,end_month', 'tbl_forecast_year', array('client_id' => $client_id));

        $selectedLevelID = $_POST['selectedLevelID'];
        $selectedLevel = $_POST['selectedLevel'];
        // $p_ids = explode(',',$this->input->post('patIds'));
        // for($i=0;$i<count($p_ids);$i++){
        // $partner_ids[] = explode('_',$p_ids[$i])[4];
        // }
        // $patIDs = implode(',',$partner_ids);
        if ($selectedLevel == 'region_3') {
            $field = 'region_three_id';
        } elseif ($selectedLevel == 'region_2') {
            $field = 'region_two_id';
        } elseif ($selectedLevel == 'region_1') {
            $field = 'region_one_id';
        } elseif ($selectedLevel == 'cam') {
            $field = 'cam_id';
        } elseif ($selectedLevel == 'partner') {
            $field = 'partner_id';
        }
        $sql = "SELECT distinct(partner_id) from tbl_set_target WHERE set_from='" . $selectedLevel . "' AND $field = $selectedLevelID";
        $partnerIDs = $this->Common_model->get_conditional_array($sql);
        $patIds = $this->Common_model->keyValuepair($partnerIDs, 'partner_id', 'partner_id');
        $partner_ids = $patIds;
        $patIDs = implode(',', $patIds);

        $sql = "SELECT * FROM scorecard_metrics WHERE 	client_id=$client_id AND partner_id IN ($patIDs)";
        $scorecardDataTemp = $this->Common_model->get_conditional_array($sql);
        $scorecardData = array();
        foreach ($scorecardDataTemp as $score) {
            $scorecardData[$score['partner_id']][$score['scorecard_topic_id']][$score['year']][$score['quarter']]['actual_value'] = $score['actual_value'];
            $scorecardData[$score['partner_id']][$score['scorecard_topic_id']][$score['year']][$score['quarter']]['is_display_actual'] = $score['is_display_actual'];
        }
        // debug($patIds);

        // $camPartnrs = $this->Cam_model->region_cam_partnrs($client_id);
        // $patIds     = $this->Common_model->keyValuepair($camPartnrs,'partner_id','partner_id');

        $sql = "SELECT detail.value,detail.month,target.id,target.year FROM tbl_set_targetdetail as detail LEFT JOIN tbl_set_target as target on target.id=detail.tbl_set_target_id
			WHERE tbl_set_target_id IN (SELECT id FROM tbl_set_target WHERE partner_id =(SELECT partner_id FROM (tbl_set_target) WHERE client_id = $client_id AND partner_id IN ($patIDs) ORDER BY id DESC limit 0,1)) ORDER BY detail.id ASC";
        $totalVal = $this->Common_model->get_conditional_array($sql);
        // debug($totalVal);
        $qtr = 1;
        $count = 0;
        foreach ($totalVal as $temp) {
            $result[$temp['year']][$qtr] += $temp['value'];
            ++$count;
            if ($count == 3) {
                $count = 0;
                ++$qtr;
            }
            if ($qtr == 5) {
                $qtr = 1;
            }
        }
        // debug($result,1);
        $sql = "select scorecard_topic_id,topic_key from scorecard_topic where client_id = $client_id AND types = '4' AND topic_key != 'pipeline_deal'";
        $scorecard_topics = $this->Common_model->get_conditional_array($sql);
        $topic_ids = $this->Common_model->keyValuepair($scorecard_topics, 'scorecard_topic_id', 'topic_key');
        $pipeline = $this->Common_model->get_data_row('total_pipeline_index,pipeline_average_deal_amount', 'pipeline_index', array('client_id' => $client_id));
        $target_array = array();
        //$array = array();
        foreach ($partner_ids as $pID) {
            // exec('php index.php sflogin import_report_sf_edited "'.$pID.'" > /dev/null 2>&1 &');
            foreach ($result as $yr => $qtrs) {
                $quarters = $this->Common_model->year_month_arr($yr, $years_data['start_month']);
                $k = 0;
                foreach ($qtrs as $key => $qtr_val) {
                    foreach ($scorecard_topics as $val) {
                        $array['client_id'] = $client_id;
                        $array['partner_id'] = $pID;
                        $array['quarter'] = 'Q' . ($key);
                        $array['year'] = $quarters['quarters_year'][$k];
                        $array['scorecard_topic_id'] = $val['scorecard_topic_id'];

                        $actual_value = $scorecardData[$pID][$val['scorecard_topic_id']][$quarters['quarters_year'][$k]]['Q' . ($key)]['actual_value'];
                        $is_display_actual = $scorecardData[$pID][$val['scorecard_topic_id']][$quarters['quarters_year'][$k]]['Q' . ($key)]['is_display_actual'];
                        $array['actual_value'] = $actual_value != '' ? $actual_value : 0;
                        $array['is_display_actual'] = $is_display_actual != '' ? $is_display_actual : 0;

                        $p[] = $pID;
                        $q[] = "'Q" . ($key) . "'";
                        $t[] = $val['scorecard_topic_id'];
                        $y[] = $quarters['quarters_year'][$k];

                        if ($val['topic_key'] == 'plan_actual' || $val['topic_key'] == 'target_plan') {
                            $array['target_value'] = $qtr_val;
                        } elseif ($val['topic_key'] == 'pipeline_close_price' || $val['topic_key'] == 'pipeline_reg_price') {
                            $array['target_value'] = $qtr_val * $pipeline['total_pipeline_index'];
                        } elseif ($val['topic_key'] == 'pipeline_close_unit' || $val['topic_key'] == 'pipeline_reg_unit') {
                            $array['target_value'] = ($qtr_val * $pipeline['total_pipeline_index']) / $pipeline['pipeline_average_deal_amount'];
                        }
                        $array1[] = $array;
                    }
                    ++$k;
                }
            }
            $target_array['partner_id'] = $pID;
            $target_array['client_id'] = $client_id;
            $target_array['status'] = '0';
            $target_array['plan_status'] = '0';
            $target_array1[] = $target_array;
        }
        //debug($array1);exit;
        $ptner_ids = implode(',', $p);
        $quartrs = implode(',', $q);
        $tpcs = implode(',', $t);
        $years = implode(',', $y);
        $update_plan = "update tbl_plan_status set status = '0' where partner_id IN(" . $ptner_ids . ") AND client_id = $client_id";
		$this->db->query($update_plan);
        

        $delete = "delete from scorecard_metrics where client_id = $client_id AND partner_id IN(" . $ptner_ids . ') AND scorecard_topic_id IN(' . $tpcs . ') AND year IN(' . $years . ') AND quarter IN (' . $quartrs . ') ';
		$this->db->query($delete);
       

        $delete1 = "delete from tbl_target_status where client_id = $client_id AND partner_id IN(" . $ptner_ids . ')';
		$this->db->query($delete1);
       

        $this->db->insert_batch('scorecard_metrics', $array1);
        $this->db->insert_batch('tbl_target_status', $target_array1);
    }

    // function for display dual target setting END

    public function bind_scorecard_tbl()
    {
        $get_data = $this->Common_model->get_data_array('scorecard_category_id, 	scorecard_topic_id,scorecard_question_id', 'scorecard_question',array());

        $topic_arr = $this->Common_model->keyValuepair($get_data, 'scorecard_topic_id', 'scorecard_question_id');

        $cat_arr = $this->Common_model->keyValuepair($get_data, 'scorecard_category_id', 'scorecard_question_id');

        $get_calculation = $this->Common_model->get_data_array('scorecard_question_id', 'scorecard_answer',array());
        $questionare_arr = $this->Common_model->keyValuepair($get_calculation, 'scorecard_question_id', 'scorecard_question_id');

        foreach ($questionare_arr as $questionare_val) {
            echo $sql = "update scorecard_answer set
		scorecard_category_id='" . $cat_arr[$questionare_val] . "',
		scorecard_topic_id='" . $topic_arr[$questionare_val] . "' where scorecard_question_id='" . $questionare_val . "' ";
            $query = $this->db->query($sql);
            echo '<br>';
        }
    }

    //added by Deepak
    public function notification()
    {   
	   $this->Common_model->check_dashboard_login();
        $client_id = $this->session->userdata('client_id');
        $this->load->model('Sfpartners_model');
        $this->load->model('Sfowner_model');

        $data['release'] = array();
        $data['release_record'] = array();
		 //Not Assign Any Cam To partner
        $data['partner_null'] = $this->Sfpartners_model->assign_cam($client_id);
        //Not Active Member To Partner
        $data['member_active'] = $this->Sfpartners_model->partner_contact_active($client_id);
        //Cam Role Are Not Set
        $data['cam_set'] = $this->Sfpartners_model->cam_set($client_id);
        // active categories does not have any topic active/created for them
        $data['assign_topics'] = $this->Sfpartners_model->assign_topics($client_id);
        $data['assign_question'] = $this->Sfpartners_model->assign_question($client_id);
        $data['region_set'] = $this->Common_model->get_data_row('*', 'region_settings', array('client_id' => $client_id));
        //No level 2 Region Are Created
        $data['region_level_2'] = $this->Sfpartners_model->level_2_region($client_id);
        //No level 3 Region Are Created
        $data['region_level_3'] = $this->Sfpartners_model->level_3_region($client_id);
        //#Level 2 regions are not associate with any level 1 region
        $data['asign_regn1'] = $this->Sfpartners_model->assigned_level1($client_id);
        //#Level 3 regions are not associate with any level 2 region
        $data['asign_reg3'] = $this->Sfpartners_model->assigned_level2($client_id);

        $data['inactice_cam'] = $this->Sfpartners_model->inactive_cam($client_id);
        $data['inactive_partner'] = $this->Sfpartners_model->inactive_partner($client_id);
        // $data['asign_reg3'] = $this->Sfpartners_model->assigned_level2($client_id);

        $data['main_content'] = 'fronthand/twitter/notification/notification';
        $this->load->view('/includes/template_new', $data);
    }

    /* ****************** Progress bar code start ************ */

       public function progress_bar()
    {   
	
	    $this->Common_model->check_dashboard_login();
        $client_id = $this->session->userdata('client_id');
		
	
	
		$app_language = $this->input->post('app_language');
		if($app_language !=""){
		$this->session->set_userdata('admin_app_language',$app_language);
		}	
		$app_language  = $this->session->userdata('admin_app_language');
		
		
		
		$nav_titl="";
		if($app_language != "" and $app_language != "eng")
		{	
		  $nav_titl ='_'.$app_language;
		}
		
        $data['tracking_status'] = $this->Common_model->get_data_row('tracking_status', 'tracking_payments', array('client_id' => $client_id))['tracking_status'];
        $data['client_staps'] = $this->Common_model->get_client_staps($client_id);
        $data['parent_steps'] = $this->Common_model->get_data_array('client_steps_id, client_id, title, nav_title'.$nav_titl.' as nav_title, step_key, status, is_active, type, created_on, modify_on, step_order', 'tbl_client_steps', array('client_id' => $client_id, 'status' => '1'), 'step_order', 'step_order');
        $customize_data = $this->Common_model->get_conditional_array(
            "select t2.*,t1.step_key as parent_step_key from  tbl_client_sub_steps as t1 inner join tbl_customize_cost as t2 on t1.client_sub_step_id=t2.client_sub_step_id where t1.client_id='" . $client_id . "' and t2.client_id='" . $client_id . "' ");

        foreach ($customize_data as $customize_val) {
            $customize_arr[$customize_val['parent_step_key']][] = $customize_val;
        }
        $data['customize_arr'] = $customize_arr;
		$data['dropdowncntry'] = $this->Common_model->get_language(); 
		
		
        //echo "<pre>";print_r($customize_arr);echo "</pre>";
        $data['main_content'] = 'fronthand/twitter/progress_bar/progress_bar';
        $this->load->view('/includes/template_new', $data);
    }

    // update steps status
	
    public function update_step_status()
    {
        $step_key = $this->input->post('step_key');
        $update['is_active'] = $this->input->post('status');
        $sub_update['status'] = $this->input->post('status');
        $step_id = $this->input->post('step_id');
        $this->db->where('client_steps_id', $step_id);
        $this->db->update('tbl_client_steps', $update);

        if ($step_key != 'scorecard') {
            $this->db->where('client_steps_id', $step_id);
            $this->db->update('tbl_client_sub_steps', $sub_update);
        }
        //vineet mar26 stART
        $state = $this->input->post('state');
        $todo = $this->input->post('todo');
        $clientid = $this->session->userdata('client_id');
        if ($step_key == 'scorecards') {
            $step_key = 'scorecard';
        } elseif ($step_key == 'bussiness') {
            $step_key = 'business_plan';
        } elseif ($step_key == 'marketing_calculator') {
            $step_key = 'marketing_plan';
        } elseif ($step_key == 'salesforce') {
            $step_key = 'salesforce';
        } elseif ($step_key == 'action_plan') {
            $step_key = 'action_plan';
        }
        if ($state == 'deactivate' || $state == 'activate') {
            $this->insertEntriesInTrackingForAllPartners($clientid, $step_key);
        }
        if ($step_key != 'start' && $state == 'deactivate') {
            $sql = "UPDATE tracking_partner_level_app_activation SET isactive='" . $sub_update['status'] . "' WHERE client_id=$clientid AND app_type='" . $step_key . "'";
			$this->db->query($sql);
          
        } elseif ($step_key != 'start' && $state == 'activate') {
            if ($todo == 'doAll') { //do-all
                $this->activateAllPartnersTrackingbyMonth($clientid, $step_key);
                $sql = "UPDATE tracking_partner_level_app_activation SET isactive='" . $sub_update['status'] . "' WHERE client_id=$clientid   AND app_type='" . $step_key . "'";
				$this->db->query($sql);
              
            } else {
                $up = $this->Common_model->get_data_row('tracking_key,tracking_id', 'tracking_payments', array('client_id' => $clientid));
                if ($up['tracking_key']) {
                    $exist['tracking_key'] = json_decode($up['tracking_key']);
                    if (!in_array($step_key, $exist)) {
                        $exist['tracking_key'][] = $step_key;
                        $exist['tracking_key'] = json_encode($exist['tracking_key']);
                        $this->db->update('tracking_payments', $exist, array('tracking_id' => $up['tracking_id']));
                    }
                }
                $sql = "UPDATE tracking_partner_level_app_activation SET isactive='0' WHERE client_id=$clientid   AND app_type='" . $step_key . "'";
				$this->db->query($sql);
             
            }
        }
        //vineet mar26 end
    }

    public function insertEntriesInTrackingForAllPartners($clientid, $step_key)
    {
      
        $year = date('Y');
        $sql = "SELECT partner.partner_acc_name,partner.partner_acc_name as partner_acc_name,LTRIM(partner.partner_acc_name) as name,partner.super_partner_id,partner.secondary_partner_id, partner.id,partner.primary_partner_id,partner.id as partner_id, cam.id AS cam_id,cam.crm_acc_name,cam.crm_id,cam.primary_region_id,cam.primary_region_id as region_id FROM partners_accounts AS partner LEFT JOIN account_channel_manager AS cam on cam.crm_id=partner.crm_id where cam.clientid='" . $clientid . "' AND partner.clientid='" . $clientid . " ' AND partner.is_access='1' AND partner.id not in (SELECT partner_id FROM tracking_partner_level_app_activation WHERE client_id='" . $clientid . "' and year='" . $year . "' and app_type='" . $step_key . "') GROUP BY partner_id";
        $pats = $this->Common_model->get_conditional_array($sql);
        // debug($pats);
        $result = array();
        foreach ($pats as $temp) {
            $data['partner_id'] = $temp['id'];
            $data['cam_id'] = $temp['cam_id'];
            $data['client_id'] = $clientid;
            $data['year'] = $year;
            $data['app_type'] = $step_key;
            $data['isactive'] = '0';
            $data['creation_on'] = date('Y-m-d');
            $data['monthly_data'] = '{"jan":0,"feb":0,"mar":0,"apr":0,"may":0,"jun":0,"jul":0,"aug":0,"sep":0,"oct":0,"nov":0,"dec":0}';

            $result[] = $data;
        }
        if ($result) {
            $this->db->insert_batch('tracking_partner_level_app_activation', $result);
        }
    }

    public function activateAllPartnersTrackingbyMonth($clientid, $step_key)
    {
        $month = strtolower(date('M'));
        $year = date('Y');

        $sql = "SELECT * FROM tracking_partner_level_app_activation WHERE client_id=$clientid AND app_type='" . $step_key . "' AND year='" . $year . "'";
        $result = $this->Common_model->get_conditional_array($sql);

        $resultUpdate = array();
        foreach ($result as $temp) {
            $data = json_decode($temp['monthly_data'], true);
            $data[$month] = 1;
            $temp['monthly_data'] = json_encode($data);
            $resultUpdate[] = $temp;
        }
        if ($resultUpdate) {
            $this->db->update_batch('tracking_partner_level_app_activation', $resultUpdate, 'id');
        }
    }

    public function update_sub_step_status()
    {
        $update['status'] = $this->input->post('status');
        $step_ids = $this->input->post('step_id');
        $multi_step_id = $this->input->post('step_id');
        $explode_id = explode('-', $multi_step_id);
        $multi_step_ids = "'" . implode("','", $explode_id) . "'";
        if (is_array($explode_id) and count($explode_id) > 0) {
            $step_id = $explode_id;
        } else {
            $step_id = $step_ids;
        }
        $this->db->where_in('client_sub_step_id', $step_id);
        $this->db->update('tbl_client_sub_steps', $update);
    }

    // save client steps
    public function save_client_steps()
				{
					$client_step = $this->input->post('client_step');
					$client_step_id = $this->input->post('client_step_id');
					$sub_client_step = $this->input->post('sub_client_step');
					$sub_client_step_id = $this->input->post('sub_client_step_id');
					$inner_sub_steps = $this->input->post('inner_sub_steps');
					$inner_sub_steps_id = $this->input->post('inner_sub_steps_id');
					$app_language = $this->input->post('app_language');
					

					if (is_array($client_step_id) and count($client_step_id) > 0) {
						foreach ($client_step_id as $key => $step) {
							
							if($app_language=="" or $app_language=="eng")
							{
							$update_parent_step['nav_title'] = $client_step[$key];
							}
							else{
							$update_parent_step['nav_title_'.$app_language] = $client_step[$key];
							}
					
							
							
							
							$where = array('client_steps_id' => $step);
							$this->Common_model->update_data('tbl_client_steps', $update_parent_step, $where);
						}
					}

					if (is_array($sub_client_step_id) and count($sub_client_step_id) > 0) {
						foreach ($sub_client_step_id as $key => $sub_step) {
							
							if($app_language=="" or $app_language=="eng")
							{
							  $update_sub_step['nav_title'] = $sub_client_step[$key];
							}
							else{
							  $update_sub_step['nav_title_'.$app_language] = $sub_client_step[$key];
							}
							
							$where = array('client_sub_step_id' => $sub_step);
							$this->Common_model->update_data('tbl_client_sub_steps', $update_sub_step, $where);
						}
					}

					if (is_array($inner_sub_steps_id) and count($inner_sub_steps_id) > 0) {
						foreach ($inner_sub_steps_id as $inner_key => $inner_sub_step) {
							
							if($app_language=="" or $app_language=="eng")
							{
							 $inner_sub_arr['nav_title'] = $inner_sub_steps[$inner_key];
							}
							else{
							 $inner_sub_arr['nav_title_'.$app_language] = $inner_sub_steps[$inner_key];
							}
							
							
							$where = array('customize_cost_id' => $inner_sub_step);
							$this->Common_model->update_data('tbl_customize_cost', $inner_sub_arr, $where);
						}
					}
				}


    // change scorecard steps order
    public function update_scorecard_sub_steps()
    {
        $scorecard_ids = $this->input->post('scorecard_ids');
        $order_id = 1;
        foreach ($scorecard_ids as $key => $scorecard_id) {
            $this->db->query("update tbl_client_sub_steps set step_order='" . $order_id . "' where client_sub_step_id='" . $scorecard_id . "'  ");
            ++$order_id;
        }
    }

    // change scorecard steps order
    public function update_scorecard_steps()
    {
        // $positionid1 = $this->input->post('dragable');
        // $positionid2 = $this->input->post('dropable');
        // $step = $this->Common_model->get_conditional_array("SELECT client_steps_id,step_order FROM tbl_client_steps WHERE client_steps_id in($positionid1,$positionid2)");
        // foreach ($step as $key => $group_arr_val) {
            // $group_id_arr[$group_arr_val['client_steps_id']] = $group_arr_val['step_order'];
        // }
        // $group_type_positionid1 = $group_id_arr[$positionid1];
        // $group_type_positionid2 = $group_id_arr[$positionid2];
        // $this->db->query("update tbl_client_steps set step_order='" . $group_type_positionid2 . "' where client_steps_id='" . $positionid1 . "'  ");
        // $this->db->query("update tbl_client_steps set step_order='" . $group_type_positionid1 . "' where client_steps_id='" . $positionid2 . "'  ");
       
		$positionid1 = $this->input->post('theArry');
		$updates=array();
		foreach($positionid1 as $key =>$val){
			$updates[$key]['client_steps_id']=$val;
			$updates[$key]['step_order']=($key+1);
		}
		$this->db->update_batch('tbl_client_steps', $updates, 'client_steps_id');
		$this->progress_bar();
    }

    /* ****************** Progress bar code end ************ */

    /* code by vineet & anjana for scorecard profile starts */
    public function show_scorecard($type, $partner_profile_id = null)
    {
        $clientid = $this->session->userdata('client_id');
        //$type= $this->input->post('type');
        $partner_profile_id = $this->input->post('partner_profile_id');
        //$data['scorecard_categories'] = $this->Common_model->get_data_array('scorecard_category_id,title','scorecard_category',array('client_id'=>$clientid,'types'=>'1','is_draft'=>'0'));
        $sql1 = "select distinct(t1.scorecard_category_id),t1.title from scorecard_category t1 join scorecard_topic t2 on t1.scorecard_category_id = t2.scorecard_category_id where t1.client_id = $clientid and t1.types='1' and t1.is_draft = '0' ";
        $data['scorecard_categories'] = $this->Common_model->get_conditional_array($sql1);
        //echo $this->db->last_query();
        //debug($data['scorecard_categories']);exit;
        $data['scorecard_topics'] = $this->Common_model->get_data_array('scorecard_category_id,scorecard_topic_id,title', 'scorecard_topic', array('client_id' => $clientid, 'types' => '1', 'is_draft' => '0'));

        $sql = "select t2.scorecard_topic_id,t3.scorecard_category_id,t1.scorecard_question_id,t1.question_title from scorecard_question t1 inner join scorecard_topic t2 on t1.scorecard_topic_id = t2.scorecard_topic_id inner join scorecard_category t3 on t1.scorecard_category_id = t3.scorecard_category_id where t1.client_id = $clientid AND t1.is_draft ='0' AND t1.show_question ='1'";

        $data['scorecard_questions'] = $this->Common_model->get_conditional_array($sql);

        if ($partner_profile_id != null) {
            $data['scorecard_prof_data'] = $this->Common_model->get_data_array('*', 'tbl_scorecard_profile', array('client_id' => $clientid, 'partner_profile_id' => $partner_profile_id));
        }
        //debug($data['scorecard_prof_data']);exit;

        if ($type == 'full') {
            $loop_count = 1;
        } elseif ($type == 'half') {
            $loop_count = 2;
        } else {
            $loop_count = 3;
        }
        $data['loop_count'] = $loop_count;

        return $data;
        //echo $this->load->view('fronthand/twitter/partner_profile/partner_scorecard', $data) ;
    }

    /* goals & strategies starts */
    public function show_goals()
    {
        $clientid = $this->session->userdata('client_id');
        $this->load->model('Managegoals_model');
        $query = $this->Managegoals_model->getdataall($clientid);
        $data['goals'] = $query;
        $data['row_id'] = $this->input->post('row_id');
        echo $this->load->view('fronthand/twitter/partner_profile/profile_goals', $data);
    }

    public function show_strategies()
    {
        $clientid = $this->session->userdata('client_id');
        $this->load->model('Managestrategies_model');
        $query = $this->Managestrategies_model->getdataall($clientid);
        $data['strategiess'] = $query;
        $data['row_id'] = $this->input->post('row_id');
        echo $this->load->view('fronthand/twitter/partner_profile/profile_strategies', $data);
    }

    public function delete_goal()
    {
        $this->load->model('Managegoals_model');
        $session_uerdata = $this->session->userdata;
        $id = $this->input->post('goal_id');
        $this->Managegoals_model->delete_goal($session_uerdata['client_id'], $id);
    }

    public function delete_strategy()
    {
        $this->load->model('Managestrategies_model');
        $session_uerdata = $this->session->userdata;
        $id = $this->input->post('strategy_id');
        $this->Managestrategies_model->delete_strategies($session_uerdata['client_id'], $id);
    }

    /* goals & strategies ends */

    public function check_scorecard_profile($scorecard_profile_id)
    {
        $clientid = $this->session->userdata('client_id');
        $result = $this->Common_model->get_data_row('tbl_scorecard_profile_id', 'tbl_scorecard_profile', array('tbl_scorecard_profile_id' => $scorecard_profile_id, 'client_id' => $clientid));

        return $result;
    }

    public function prePublished()
    {
        $clientid = $this->session->userdata('client_id');
        $type = $this->input->post('type');
        $partner_profile_id = $this->input->post('partner_profile_id');

        if (!empty($partner_profile_id)) {
            $pre_publish_data = $this->Common_model->get_data_array('type,field_title', 'tbl_pre_publish_profile', array('client_id' => $clientid, 'partner_profile_id' => $partner_profile_id));
            foreach ($pre_publish_data as $temp) {
                $data['pre_publish_data'][$temp['type']][] = $temp['field_title'];
            }
            $data['partner_profile'] = $this->Common_model->get_data_row('*', 'tbl_partner_profile', array('client_id' => $clientid, 'partner_profile_id' => $partner_profile_id));
        }

        if ($type == 'full') {
            $loop_count = 1;
        } elseif ($type == 'half') {
            $loop_count = 2;
        } else {
            $loop_count = 3;
        }
        $data['row_id'] = $this->input->post('row_id');
        $data['loop_count'] = $loop_count;
        $data['partner_profile_id'] = $partner_profile_id;
        echo $this->load->view('fronthand/twitter/partner_profile/pre_publish', $data);
    }

    public function check_question()
    {
        $clientid = $this->session->userdata('client_id');
        $question_ids = $this->input->post('arr');
        $ids = implode(',', $question_ids);
        $sql = "select * from tbl_scorecard_profile where client_id = $clientid and scorecard_question_id IN($ids)";
        $result = $this->Common_model->get_conditional_array($sql);
        if (is_array($result) && count($result) > 0) {
            echo 'error';
        } else {
            echo '';
        }
    }

    public function savePrePublisComments()
    {
        parse_str($_POST['data'], $saveArry);
        $tepData = $saveArry['fields'];
        if (!empty($this->session->userdata('is_cam_login'))) { //camLOGIN
            $cam_id = $this->session->userdata('cam_id');
            $member_id = 0;
        } else { //MemberLogin
            $member_id = $this->session->userdata('member_id');
            $cam_id = 0;
        }
        $toDel = array();
        $save = array();
        foreach ($tepData as $profID => $fields) {
            foreach ($fields as $optionID => $field) {
                foreach ($field as $colmID => $commt) {
                    // $tbl_pre_publish_id = $prof_ids[$optionID][$colmID];
                    $temp['tbl_pre_publish_id'] = $profID;
                    $toDel[] = $profID;
                    $temp['cam_id'] = $cam_id;
                    $temp['member_id'] = $member_id;
                    $temp['partner_id'] = $this->session->userdata('partner_id');
                    $temp['option_id'] = $optionID;
                    $temp['colum_id'] = $colmID;
                    $temp['descr'] = $commt;
                    $save[] = $temp;
                }
            }
        }
        $sql = 'delete from tbl_prepublish_profile_comments where tbl_pre_publish_id IN (' . implode(',', array_unique($toDel)) . ')';
        $result = $this->db->query($sql);
        if (!empty($save)) {
            $result = $this->db->insert_batch('tbl_prepublish_profile_comments', $save);
        }
    }

    public function deletComment()
    {
        $ID = $this->input->post('id');
        $this->Common_model->delete_records('tbl_prepublish_profile_comments', array('tbl_comment_id' => $ID));
    }

    /* code by vineet & anjana for scorecard profile ends */

    public function array_swap(&$array, $swap_a, $swap_b)
    {
        list($array[$swap_a], $array[$swap_b]) = array($array[$swap_b], $array[$swap_a]);
    }

    public function save_partner_profile_update()
    {
        parse_str($_POST['profile_data'], $profilearray);
        reset($profilearray['section_column_title']);

        $first_key = key($profilearray['section_column_title']);
        $max_section_key = max(array_keys($profilearray['section_column_title'][$first_key][1]));
        $max_section_key = $max_section_key + 1;

        $desc_as_text = 0;
        if (!empty($profilearray['desc_as_text'])) {
            $desc_as_text = 1;
        }
        // debug($profilearray,1);
        $show_hide = $this->input->post('show_hide');
        // debug($show_hide,1);
        $col_width = $this->input->post('col_width');
        $section_width = $this->input->post('section_width');
        $count_section = count($section_width) - 1;
        $clientid = $this->session->userdata('client_id');
        $profile_array = $profilearray['profile_arr'];
        $scorecard_profile_ids = $profilearray['scorecard_profile_id'];
        $scorecard_column_title = $profilearray['scorecard_column_title'];
        // debug($_POST['is_activity_type']);
        // debug($_POST['is_activity_desc']);
        // debug($_POST['is_activity_type_s']);
        // debug($_POST['is_activity_desc_s']);exit;
        // $is_activity_type = $profilearray['is_activity_type'];
        // $is_activity_type_s = $profilearray['is_activity_type_s'];
        if (!empty($this->input->post('col_width'))) {
            $col_width = $this->input->post('col_width');
            foreach ($col_width as $key => $col_data) {
                $column_arr[$col_data['row_id']][$col_data['tbl_id']][] = $col_data['width'];
            }
        }
        if (!empty($this->input->post('section_width'))) {
            $section_width = $this->input->post('section_width');
            foreach ($section_width as $section_data) {
                $section_arr[$section_data['row_id']][$section_data['tbl_id']][$section_data['section_id']][] = $section_data['width'];
            }
        }
        if (!empty($this->input->post('dropdown_section'))) {
            $val = $this->input->post('dropdown_section');
            foreach ($val as $section_data) {
                $dropdown_section[$section_data['row_id']][$section_data['tbl_id']][$section_data['section_id']][] = $section_data['dropdown'];
            }
        }
        if (!empty($this->input->post('tool_tip_act_section'))) {
            $tool_tip = $this->input->post('tool_tip_act_section');
            foreach ($tool_tip as $section_data) {
                $tool_tip_act_section[$section_data['row_id']][$section_data['tbl_id']][$section_data['section_id']][] = $section_data['tool_tip'];
            }
        }
        if (!empty($this->input->post('is_activity_type_s'))) {
            $is_activity_type_s_temp = $this->input->post('is_activity_type_s');
            foreach ($is_activity_type_s_temp as $section_data) {
                $is_activity_type_s[$section_data['row_id']][$section_data['tbl_id']][$section_data['section_id']][] = $section_data['tool_tip'];
            }
        }

        if (!empty($this->input->post('is_activity_desc_s'))) {
            $is_activity_desc_s_temp = $this->input->post('is_activity_desc_s');
            foreach ($is_activity_desc_s_temp as $sec_data) {
                $is_activity_desc_s[$sec_data['row_id']][$sec_data['tbl_id']][$sec_data['section_id']][] = $sec_data['tool_tip'];
            }
        }
        $module_type = $profilearray['module_val'];
        /* save partner profile main data starts */
        if (!empty($profilearray['pre_publish_field_title'])) {
            $titles = $profilearray['pre_publish_field_title'];
            $insert_titles = array();
            $update_titles = array();
            foreach ($titles as $ky => $titles_val) {
                $title_count = count($titles_val);
                if ($title_count == 1) {
                    $field_width = 'full';
                } elseif ($title_count == 2) {
                    $field_width = 'half';
                } else {
                    $field_width = 'third';
                }
                $partner_profile_id = $profilearray['partner_profile_id'];
                $insert_arr = array(
                    'client_id' => $clientid,
                    'profile_type' => $profilearray['module_val'],
                    'field_width' => $field_width,
                );
                if ($partner_profile_id != '') {
                    $this->Common_model->update_data('tbl_partner_profile', $insert_arr, array('partner_profile_id' => $partner_profile_id));
                    $last_id = $partner_profile_id;
                } else {
                    $sql = "select IFNULL((max(step_order)+1),1) as step_order from tbl_partner_profile where client_id=$clientid";
                    $insert_arr['step_order'] = $this->Common_model->get_conditional_array($sql)[0]['step_order'];
                    $last_id = $this->Common_model->insert_data('tbl_partner_profile', $insert_arr);
                }

                //field titles update and insert starts by anjana
                foreach ($titles_val as $t_key => $titles_v) {
                    if (!empty($titles_v)) {
                        $field_titles['partner_profile_id'] = $last_id;
                        $field_titles['client_id'] = $clientid;
                        $field_titles['type'] = $t_key;
                        $field_titles['field_title'] = $titles_v;

                        if (isset($profilearray['titles_id'][$ky][$t_key]) && $profilearray['titles_id'][$ky][$t_key] != '') {
                            $field_titles['field_title_id'] = $profilearray['titles_id'][$ky][$t_key];
                            $update_titles[] = $field_titles;
                        } else {
                            unset($field_titles['field_title_id']);
                            $insert_titles[] = $field_titles;
                        }
                    }
                }
                if (count($update_titles) > 0) {
                    $this->db->update_batch('tbl_partner_profile_field_titles', $update_titles, 'field_title_id');
                }
                if (count($insert_titles) > 0) {
                    $this->db->insert_batch('tbl_partner_profile_field_titles', $insert_titles);
                }
                //field titles update and insert ends by anjana
            }
        }
        /* save partner profile main data ends */

        /* save pre publish data starts */
        if (!empty($profilearray['pre_publish_title'])) {
            // debug($profilearray,1);    //$this->Common_model->delete_records('tbl_pre_publish_profile',array('partner_profile_id'=>$last_id));
            $descDropDown = array();
            // $insert_profile_columns_ID[$last_id][$profilearray['column_ids'][$key][$row][$type]]= $_POST['dropdown'][$type];
            $insert_profile_options = array();
            $update_profile_options = array();
            $titlesArry = $profilearray['pre_publish_title'];
            $strategy_description = $profilearray['strategy_description'];
            $description = $profilearray['description'];
            $create_own = $profilearray['create_own_val'];

            $i = 0;
            foreach ($titlesArry as $key => $titleDatatemp) {
                foreach ($titleDatatemp as $row => $titletemp) {
                    foreach ($titletemp as $type => $title) {
                        if (!empty($title)) {
                            $temp['partner_profile_id'] = $last_id;
                            $temp['type'] = $row;
                            $temp['client_id'] = $clientid;
                            $temp['field_title'] = $title;
                            $temp['create_own'] = $create_own[$key][$row][$type];

                            // if(!empty($title)){
                            // $publishSave[]=$temp;
                            // }
                            // debug($profilearray);
                            // debug($temp,1);
                            //code by deepak

                            if ($module_type == 8) {
                                $temp['strategy_description'] = $strategy_description[$key][$row][$type];
                                if (isset($profilearray['strategy_profile_ids'][$key][$row][$type]) && !empty($profilearray['strategy_profile_ids'][$key][$row][$type])) {
                                    $stID = $profilearray['strategy_profile_ids'][$key][$row][$type];
                                    $temp['tbl_strategy_goals_profile_Id'] = $stID;
                                    $update_profile_options[] = $temp;
                                    $descDropDown[$last_id][$stID] = $strategy_description[$key][$row][$type];
                                } else {
                                    unset($temp['tbl_strategy_goals_profile_Id']);
                                    $stID = $this->Common_model->insert_data('tbl_strategy_goals_titles', $temp);
                                    $descDropDown[$last_id][$stID] = $strategy_description[$key][$row][$type];
                                }
                            } elseif ($module_type == 5) {
                                $temp['description'] = $description[$key][$row][$type];
                                if (isset($profilearray['pre_profile_ids'][$key][$row][$type]) && !empty($profilearray['pre_profile_ids'][$key][$row][$type])) {
                                    $stID = $profilearray['pre_profile_ids'][$key][$row][$type];
                                    $temp['tbl_pre_publish_id'] = $stID;
                                    $update_profile_options[] = $temp;
                                    $descDropDown[$last_id][$stID] = $description[$key][$row][$type];
                                } else {
                                    unset($temp['tbl_pre_publish_id']);
                                    $stID = $this->Common_model->insert_data('tbl_pre_publish_profile', $temp);
                                    $descDropDown[$last_id][$stID] = $description[$key][$row][$type];
                                }
                            }
                        }
                    }
                }
                ++$i;
            }

            //Code By Deepak
            if ($module_type == 8) {
                if (count($update_profile_options) > 0) {
                    $this->db->update_batch('tbl_strategy_goals_titles', $update_profile_options, 'tbl_strategy_goals_profile_Id');
                }
                // if(count($insert_profile_options) > 0){
                // $this->db->insert_batch('tbl_strategy_goals_titles',$insert_profile_options);
                // }
            } elseif ($module_type == 5) {
                if (count($update_profile_options) > 0) {
                    $this->db->update_batch('tbl_pre_publish_profile', $update_profile_options, 'tbl_pre_publish_id');
                }
                // if(count($insert_profile_options) > 0){
                // $this->db->insert_batch('tbl_pre_publish_profile',$insert_profile_options);
                // }
            }

            if (count($descDropDown) > 0) {
                $dropdownArrayUpdate = array();
                $dropdownArrayInsert = array();
                $sql = "SELECT group_concat(id order by id asc ) as id, id as orderbykey,partner_profile_id,goal_strategy_id,group_concat(name order by id asc  SEPARATOR '<>,') as name from tbl_plan_description WHERE client_id=$clientid AND partner_profile_id=$last_id and partner_id=0 group by goal_strategy_id order by orderbykey asc";
                $colm_dropdown = $this->Common_model->get_conditional_array($sql);
                foreach ($colm_dropdown as $temp) {
                    $colm_dropdown_ids[$temp['goal_strategy_id']] = $temp['id'];
                }
                foreach ($descDropDown as $proFID => $colmDrop) {
                    foreach ($colmDrop as $colID => $dropDown) {
                        if (!empty($dropDown)) {
                            $dropDownIDs = array();
                            $dropDown = explode('<>,', $dropDown);
                            if (isset($colm_dropdown_ids[$colID])) {
                                $dropDownIDs = explode(',', $colm_dropdown_ids[$colID]);
                            }
                            foreach ($dropDown as $keys => $tempVal) {
                                if (!empty($tempVal)) {
                                    $temp = array();

                                    $temp['client_id'] = $clientid;
                                    $temp['module_type'] = $module_type;
                                    $temp['partner_profile_id'] = $proFID;
                                    $temp['goal_strategy_id'] = $colID;
                                    $temp['name'] = $tempVal;
                                    if (isset($dropDownIDs[$keys]) && !empty($dropDownIDs[$keys])) {
                                        $temp['id'] = $dropDownIDs[$keys];
                                        $dropdownArrayUpdate[] = $temp;
                                    } else {
                                        $dropdownArrayInsert[] = $temp;
                                    }
                                }
                            }
                        }
                    }
                }
                // debug($dropdownArrayUpdate);
                // debug($dropdownArrayInsert);
                if (count($dropdownArrayUpdate) > 0) {
                    $this->db->update_batch('tbl_plan_description', $dropdownArrayUpdate, 'id');
                }
                if (count($dropdownArrayInsert) > 0) {
                    $this->db->insert_batch('tbl_plan_description', $dropdownArrayInsert);
                }
            }
        }

        if (!empty($profilearray['pre_publish_column_title'])) {
            // tbl_strategy_goal_colums
            $update_profile_columns = array();
            $insert_profile_columns = array();
            $ColumnsArry = $profilearray['pre_publish_column_title'];
            $Column_type = $profilearray['column_type'];
            $sectionArry = $profilearray['section_column_title'];
            $section_type = $profilearray['section_type'];
            $default_order = $profilearray['default_order'];

            $i = 0;
            // $toNotDelIDCol = array();
            $insert_profile_columns_ID = array();
            foreach ($ColumnsArry as $key => $ColsDatatemp) {
                foreach ($ColsDatatemp as $row => $Colstemp) {
                    $colum_order = 1;
                    $mdf_order = 1; //Edit by kirti (new column default_order)
                    // $colum_MDF=3;
                    // $colum_Counter=count($Colstemp);
                    foreach ($Colstemp as $type => $cols) {
                        if (!empty($cols)) {
                            if ($Column_type[$key][$row][$type] == '') {
                                $cam_type = '0';
                            } else {
                                $cam_type = $Column_type[$key][$row][$type];
                            }

                            if ($module_type == 8) {
                                $hideVal = 0;
                                if (isset($show_hide[$type])) {
                                    $hideVal = $show_hide[$type];
                                }
                                $temp1['show_hide'] = $hideVal;
                            } else {
                                $temp1['show_hide'] = 0;
                            }
                            if ($type == 3) {
                                $temp1['desc_as_text'] = $desc_as_text;
                            } else {
                                $temp1['desc_as_text'] = 0;
                            }
                            // debug($profilearray,1);
                            $temp1['partner_profile_id'] = $last_id;
                            if ($profilearray['parent_type'] == 'child') {
                                $temp1['partner_profile_child_id'] = $child_id;
                            }
                            // if(empty($profilearray['data_type'][$key][$row][$type])){
                            // $profilearray['data_type'][$key][$row][$type]=0;
                            // }
                            $temp1['type'] = $row;
                            $temp1['client_id'] = $clientid;
                            $temp1['column_name'] = $cols;
                            $temp1['data_type'] = $profilearray['data_type'][$key][$row][$type];
                            $temp1['tool_tip_act'] = ($_POST['tool_tip_act'][$type] ? $_POST['tool_tip_act'][$type] : 0); //$_POST['tool_tip_act'][$type];
                            $temp1['tool_tip_val'] = $profilearray['tool_tip_val'][$key][$row][$type];
                            // debug($temp1);

                            $temp1['create_own'] = $profilearray['create_own_col'][$key][$row][$type];

                            if ($column_arr[$key][$row][$type]) {
                                $temp1['column_width'] = $column_arr[$key][$row][$type];
                            } else {
                                $temp1['column_width'] = $section_width[$count_section]['width'];
                            }
                            $temp1['column_type'] = $cam_type;
                            $temp1['default_order'] = $default_order[$key][$row][$type];

                            if ($module_type == 8) {
                                if ($type == 4) {
                                    $temp1['section_number'] = ($max_section_key + 1);
                                } else {
                                    $temp1['section_number'] = '0';
                                }
                                if ($cam_type == '30') {
                                    $temp1['section_number'] = ($max_section_key);
                                }

                                if ($cam_type == '30') {
                                    $colum_order2 = $colum_order;
                                    ++$colum_order;
                                    $mdf_order2 = $mdf_order;
                                    ++$mdf_order;
                                } else {
                                    $colum_order2 = 0;
                                    $mdf_order2 = 0;
                                }
                                $temp1['colum_order'] = $colum_order2;
                                $temp1['is_activity_type'] = ($_POST['is_activity_type'][$type] ? $_POST['is_activity_type'][$type] : 0);
                                $temp1['is_activity_desc'] = ($_POST['is_activity_desc'][$type] ? $_POST['is_activity_desc'][$type] : 0);
                            }
                            if ($module_type == 8) {
                                $colTable = 'tbl_strategy_goal_colums';
                                $primKey = 'strategy_goal_colum_id';
                            } else {
                                $colTable = 'tbl_pre_publish_columns';
                                $primKey = 'tbl_pre_publish_column_id';
                            }
                            if (!isset($_POST['dropdown'][$type])) {
                                $_POST['dropdown'][$type] = array();
                            }
                            if (isset($profilearray['column_ids'][$key][$row][$type]) && $profilearray['column_ids'][$key][$row][$type] != '') {
                                $temp1[$primKey] = $profilearray['column_ids'][$key][$row][$type];
                                $update_profile_columns[] = $temp1;
                                // $toNotDelIDCol[] = $profilearray['column_ids'][$key][$row][$type];
                                $insert_profile_columns_ID[$last_id][$profilearray['column_ids'][$key][$row][$type]] = $_POST['dropdown'][$type];
                            } else {
                                unset($temp1[$primKey]);

                                // $insert_profile_columns[]=$temp1;
                                $colmLastId = $this->Common_model->insert_data($colTable, $temp1);

                                $insert_profile_columns_ID[$last_id][$colmLastId] = $_POST['dropdown'][$type];
                                /* if($profilearray['parent_type'] != "child"){
                            if($module_type==8 && $type==2){
                            $temp1['partner_profile_id']    =    $last_id;
                            $temp1['type']                =    $row;
                            $temp1['client_id']            =    $clientid;
                            $temp1['column_name']        =    'DESC';
                            $temp1['column_width']        =    $column_arr[$key][$row][$type];
                            $temp1['column_type']        =    $cam_type;
                            $insert_profile_columns[]=$temp1;
                            }
                            } */
                            }
                        }
                    }
                }
                ++$i;
            }

            // debug($update_profile_columns,1);exit;
            //add by anjana for sections starts

            // debug($profilearray['section_ids']);
            // debug($dropdown_section);exit;
            if (!empty($sectionArry) && count($sectionArry) > 0) {
                foreach ($sectionArry as $key => $sectionDatatemp) {
                    foreach ($sectionDatatemp as $row => $sectionTemp) {
                        foreach ($sectionTemp as $type => $sectionData) {
                            // $arrayTIP = array_values($_POST['tool_tip_act_section'][$key][$row][$type]);
                            foreach ($sectionData as $num => $sections) {
                                if (!empty($sections)) {
                                    $sectn_type = $section_type[$key][$row][$type][$num];
                                    $temp1['partner_profile_id'] = $last_id;
                                    $temp1['type'] = $row;
                                    $temp1['client_id'] = $clientid;
                                    $temp1['column_name'] = $sections;

                                    $temp1['column_width'] = $section_arr[$key][$row][$type][$num];
                                    $temp1['column_type'] = $sectn_type;
                                    $temp1['section_number'] = $type;
                                    $temp1['create_own'] = $profilearray['create_own_sec'][$key][$row][$type][$num];

                                    // if(empty($profilearray['data_type_section'][$key][$row][$type][$num])){
                                    // $profilearray['data_type_section'][$key][$row][$type][$num]=0;
                                    // }
                                    $temp1['data_type'] = $profilearray['data_type_section'][$key][$row][$type][$num];
                                    $temp1['tool_tip_act'] = $tool_tip_act_section[$key][$row][$type][$num]; //todo in files for checkbox//$profilearray['tool_tip_act_section'][$key][$row][$type][$num];
                                    $temp1['tool_tip_val'] = $profilearray['tool_tip_val_section'][$key][$row][$type][$num];
                                    // debug($temp1);

                                    if ($module_type == 8) {
                                        $temp1['is_activity_type'] = $is_activity_type_s[$key][$row][$type][$num];
                                        $temp1['is_activity_desc'] = $is_activity_desc_s[$key][$row][$type][$num];
                                        $colTable = 'tbl_strategy_goal_colums';
                                        $primKey = 'strategy_goal_colum_id';
                                    } else {
                                        $colTable = 'tbl_pre_publish_columns';
                                        $primKey = 'tbl_pre_publish_column_id';
                                    }
                                    if (isset($profilearray['section_ids'][$key][$row][$type][$num]) && $profilearray['section_ids'][$key][$row][$type][$num] != '') {
                                        $temp1[$primKey] = $profilearray['section_ids'][$key][$row][$type][$num];
                                        $update_profile_columns[] = $temp1;
                                        $insert_profile_columns_ID[$last_id][$temp1[$primKey]] = $dropdown_section[$key][$row][$type][$num];
                                    } else {
                                        unset($temp1[$primKey]);
                                        // $insert_profile_columns[]=$temp1;
                                        $colmLastId = $this->Common_model->insert_data($colTable, $temp1);
                                        $insert_profile_columns_ID[$last_id][$colmLastId] = $dropdown_section[$key][$row][$type][$num];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //add by anjana for sections ends

            //DELETE HERE
            /* if(!empty($toNotDelIDCol)){
            $SQL = "DELETE FROM $colTable WHERE partner_profile_id=$last_id AND $primKey NOT IN (".implode(',',$toNotDelIDCol).")";
            $this->Common_model->get_conditional_array($SQL);
            } */
            if (count($update_profile_columns) > 0) {
                $this->db->update_batch($colTable, $update_profile_columns, $primKey);
            }
            /* if(count($insert_profile_columns) > 0){
            $this->db->insert_batch($colTable,$insert_profile_columns);
            } */

            if (count($insert_profile_columns_ID) > 0) {
                $dropdownArrayUpdate = array();
                $dropdownArrayInsert = array();
                $sql = "SELECT group_concat(id order by id asc ) as id, id as orderbykey,partner_profile_id,column_id,group_concat(name order by id asc SEPARATOR '<>,') as name from tbl_plan_dropdown WHERE client_id=$clientid  and partner_id=0  AND partner_profile_id=$last_id group by column_id order by orderbykey asc";

                $colm_dropdown = $this->Common_model->get_conditional_array($sql);
                foreach ($colm_dropdown as $temp) {
                    $colm_dropdown_ids[$temp['column_id']] = $temp['id'];
                }
                foreach ($insert_profile_columns_ID as $proFID => $colmDrop) {
                    foreach ($colmDrop as $colID => $dropDown) {
                        if (!empty($dropDown)) {
                            $dropDownIDs = array();
                            $dropDown = explode('<>,', $dropDown);
                            if (isset($colm_dropdown_ids[$colID])) {
                                $dropDownIDs = explode(',', $colm_dropdown_ids[$colID]);
                            }
                            foreach ($dropDown as $keys => $tempVal) {
                                if (!empty($tempVal)) {
                                    $temp = array();

                                    $temp['client_id'] = $clientid;
                                    $temp['module_type'] = $module_type;
                                    $temp['partner_profile_id'] = $proFID;
                                    $temp['column_id'] = $colID;
                                    $temp['name'] = $tempVal;
                                    if (isset($dropDownIDs[$keys]) && !empty($dropDownIDs[$keys])) {
                                        $temp['id'] = $dropDownIDs[$keys];
                                        $dropdownArrayUpdate[] = $temp;
                                    } else {
                                        $dropdownArrayInsert[] = $temp;
                                    }
                                }
                            }
                        }
                    }
                }

                if (count($dropdownArrayUpdate) > 0) {
                    $this->db->update_batch('tbl_plan_dropdown', $dropdownArrayUpdate, 'id');
                }
                if (count($dropdownArrayInsert) > 0) {
                    $this->db->insert_batch('tbl_plan_dropdown', $dropdownArrayInsert);
                }

                // debug($insert_profile_columns_ID,1);
                // $insert_profile_columns_ID
            }
        }

        /* add by anjana for strategy prepublish selection starts */
        $preDataArray = array();
        if (!empty($profilearray['prepublish_level']) && count($profilearray['prepublish_level']) > 0) {
            foreach ($profilearray['prepublish_level'] as $pre_data) {
                $preData['client_id'] = $clientid;
                $preData['strategy_goal_id'] = $last_id;
                $preData['prepublish_id'] = $pre_data;
                $preDataArray[] = $preData;
            }
        }

        if (count($preDataArray) > 0) {
            $this->Common_model->delete_records('tbl_strategy_prepublish_selection', array('strategy_goal_id' => $last_id));
            $this->db->insert_batch('tbl_strategy_prepublish_selection', $preDataArray);
        }
        /* add by anjana for strategy prepublish selection ends */

        if (!empty($profilearray['user_editable_column_title'])) {
            //$this->Common_model->delete_records('tbl_user_editable_columns',array('partner_profile_id'=>$last_id));
            $insert_array = array();
            $update_array = array();
            $temp2 = array();
            $userColumnsArry = $profilearray['user_editable_column_title'];
            $usercolumn_type = $profilearray['user_editable_column_type'];
            $userColumnsIds = $profilearray['cloumn_id'];
            $field_type = $profilearray['field_type'];
            // debug($field_type,1);
            $i = 0;
            foreach ($userColumnsArry as $key => $userColsDatatemp) {
                foreach ($userColsDatatemp as $row => $userColstemp) {
                    foreach ($userColstemp as $type => $usercols) {
                        if ($usercolumn_type[$key][$row][$type] == '') {
                            $cam_types = '0';
                        } else {
                            $cam_types = $usercolumn_type[$key][$row][$type];
                        }

                        if (isset($field_type[$key][$row][$type])) {
                            $temp2['field_type'] = $field_type[$key][$row][$type];
                        } else {
                            $temp2['field_type'] = '0';
                        }

                        $temp2['partner_profile_id'] = $last_id;
                        $temp2['type'] = $row;
                        $temp2['client_id'] = $clientid;
                        $temp2['column_name'] = $usercols;
                        $temp2['column_width'] = $column_arr[$key][$row][$type];
                        $temp2['column_type'] = $cam_types;

                        if (isset($userColumnsIds[$key][$row][$type]) && $userColumnsIds[$key][$row][$type] != '') {
                            $temp2['tbl_user_editable_column_id'] = $userColumnsIds[$key][$row][$type];
                            $update_array[] = $temp2;
                        } else {
                            unset($temp2['tbl_user_editable_column_id']);
                            $insert_array[] = $temp2;
                        }
                    }
                }
                ++$i;
            }
            if (count($update_array) > 0) {
                $this->db->update_batch('tbl_user_editable_columns', $update_array, 'tbl_user_editable_column_id');
            }
            if (count($insert_array) > 0) {
                $this->db->insert_batch('tbl_user_editable_columns', $insert_array);
            }
        }
        /* save pre publish data ends */

        $scoreColumnsIds = $profilearray['column_id'];
        $scorecolumn_type = $profilearray['column_type'];
        if (is_array($scorecard_column_title) && count($scorecard_column_title) > 0) {
            $insert_array = array();
            $update_array = array();
            $socre_title = array();
            foreach ($scorecard_column_title as $keys => $scorecard_title) {
                foreach ($scorecard_title as $keyD => $scorecardtitle) {
                    foreach ($scorecardtitle as $k => $title) {
                        // debug($col_width[$k]['width']);
                        $socre_title['column_name'] = $title;
                        $socre_title['column_type'] = $scorecolumn_type[$keys][$keyD][$k];
                        $socre_title['partner_profile_id'] = $last_id;
                        $socre_title['column_width'] = $col_width[$k]['width'];
                        $socre_title['client_id'] = $clientid;

                        if ($scoreColumnsIds[$keys][$keyD][$k] != '') {
                            $socre_title['tbl_scorecard_column_id'] = $scoreColumnsIds[$keys][$keyD][$k];
                            $update_array[] = $socre_title;
                        } else {
                            unset($socre_title['tbl_scorecard_column_id']);
                            $insert_array[] = $socre_title;
                        }
                    }
                }
            }
            // debug($insert_array);
            // exit;
            if (count($update_array) > 0) {
                $this->db->update_batch('tbl_scorecard_column', $update_array, 'tbl_scorecard_column_id');
            }
            if (count($insert_array) > 0) {
                $this->db->insert_batch('tbl_scorecard_column', $insert_array);
            }
        }

        /* save scorecard questionnaire data starts */
        if (is_array($profile_array) && count($profile_array) > 0) {
            foreach ($profile_array as $keys => $profile_data) {
                foreach ($profile_data as $k => $prof) {
                    $scorecard_array['partner_profile_id'] = $last_id;
                    $scorecard_array['scorecard_category_id'] = $prof[0];
                    $scorecard_array['scorecard_topic_id'] = $prof[1];
                    $scorecard_array['scorecard_question_id'] = $prof[2];
                    $scorecard_array['client_id'] = $clientid;

                    $check_scorecard_exists = $this->check_scorecard_profile($scorecard_profile_ids[$keys][$k][0]);
                    if (is_array($check_scorecard_exists) and count($check_scorecard_exists) > 0) {
                        $this->Common_model->update_data('tbl_scorecard_profile', $scorecard_array, array('client_id' => $clientid, 'tbl_scorecard_profile_id' => $scorecard_profile_ids[$keys][$k][0]));
                    } else {
                        $this->Common_model->insert_data('tbl_scorecard_profile', $scorecard_array);
                    }
                }
            }
        }

        /* save scorecard questionnaire data ends */
        if (!empty($profilearray['field_title'])) {
            if ($this->input->post('module_type') == '7') {
                //Mkt fileds
                $mkt_width_arr = $this->input->post('mkt_width_arr');
                $mrktg_fields = $this->Common_model->get_data_array('*', 'tbl_mrktng_colums', array('client_id' => $clientid));
                $IDsArray = $this->Common_model->keyValuepair($mrktg_fields, 'id', 'id');
                foreach ($mkt_width_arr as $key => $fields) {
                    if (!empty($fields)) {
                        if (in_array($key, $IDsArray)) {
                            $mkt['id'] = $key;
                            $mkt['column_name'] = $fields['fields'];
                            $mkt['column_width'] = $fields['width'];
                            $mkt['client_id'] = $clientid;
                            $updateMktFileds[] = $mkt;
                        } else {
                            $mkt['column_name'] = $fields['fields'];
                            $mkt['column_width'] = $fields['width'];
                            $mkt['client_id'] = $clientid;
                            $insrtMktFileds[] = $mkt;
                        }
                    }
                }
                if (!empty($updateMktFileds)) {
                    $this->db->update_batch('tbl_mrktng_colums', $updateMktFileds, 'id');
                }
                if (!empty($insrtMktFileds)) {
                    $this->db->insert_batch('tbl_mrktng_colums', $insrtMktFileds);
                }
                //Mkt fileds
                $data = array();
                // $this->Common_model->delete_records('tbl_mcal_admfields',array('client_id'=>$clientid));
                $defautFields = array('Selected Goal', 'Marketing Activity', 'Tactic Type');
                $idsArry = $this->Common_model->get_data_array('mcal_admfield_id', 'tbl_mcal_admfields', array('client_id' => $clientid));

                $idsArry = $this->Common_model->keyValuepair($idsArry, 'mcal_admfield_id', 'mcal_admfield_id');
                foreach ($profilearray['field_title'] as $k => $val) {
                    $data = array();
                    $data['field_title'] = $val;
                    $data['data_type'] = $profilearray['data_type'][$k];
                    $data['client_id'] = $clientid;
                    $data['column_width'] = $col_width[$k]['width'];

                    if (in_array($val, $defautFields)) {
                        $data['column_type'] = '0';
                    } else {
                        $data['column_type'] = '1';
                    }
                    if ($val == 'QBR') {
                        $data['column_type'] = '2';
                    }
                    // debug($idsArry,1);
                    if (in_array($profilearray['id'][$k], $idsArry)) {
                        $data['mcal_admfield_id'] = $profilearray['id'][$k];
                        $update[] = $data;
                    } else {
                        $insert[] = $data;
                    }
                }
                // debug($insert);
                // debug($update,1);
                if (!empty($insert)) {
                    $this->db->insert_batch('tbl_mcal_admfields', $insert);
                }
                if (!empty($update)) {
                    $this->db->update_batch('tbl_mcal_admfields', $update, 'mcal_admfield_id');
                }
            }
        }
        if ($this->input->post('module_type') == '2' || $this->input->post('module_type') == '3') {
            if ($this->input->post('module_type') == '2') {
                $tavl = 'tbl_goals_column_width';
            } else {
                $tavl = 'tbl_strategy_column_width';
            }
            $this->Common_model->delete_records($tavl, array('client_id' => $clientid));
            //debug($col_width);exit;
            foreach ($col_width as $r_id => $row_arr) {
                $goals_arr[$r_id]['column_name'] = $row_arr['column_name'];
                $goals_arr[$r_id]['column_width'] = $row_arr['width'];
                $goals_arr[$r_id]['column_type'] = $row_arr['col_type'];
                $goals_arr[$r_id]['client_id'] = $clientid;
            }
            $this->db->insert_batch($tavl, $goals_arr);
        }
        redirect('dashboard/partner_profile');
    }

    public function deleteRows()
    {
        $id = $this->input->post('id');
        $type = $this->input->post('type');

        if ($type == 'columns' || $type == 'stGoal') {
            if ($type == 'stGoal') {
                $table = 'tbl_strategy_goal_colums';
                $primryID = 'strategy_goal_colum_id';
            } else {
                $table = 'tbl_pre_publish_columns';
                $primryID = 'tbl_pre_publish_column_id';
            }
            $oldData = $this->Common_model->get_data_row('partner_profile_id,client_id,section_number', $table, array($primryID => $id));
        }
        if ($type == 'options') {
            $this->Common_model->delete_records('tbl_pre_publish_profile', array('tbl_pre_publish_id' => $id));
            // $this->Common_model->delete_records('tbl_strategy_goals_titles',array('tbl_strategy_goals_profile_Id'=>$id));
            $this->Common_model->delete_records('tbl_plan_description', array('goal_strategy_id' => $id));
            $this->Common_model->delete_records('tbl_prepublish_profile_comments', array('option_id' => $id));
        }
        if ($type == 'columns') {
            $this->Common_model->delete_records('tbl_pre_publish_columns', array('tbl_pre_publish_column_id' => $id));
            $this->Common_model->delete_records('tbl_pre_publish_columns', array('source_id' => $id));
        }
        if ($type == 'user_columns') {
            $this->Common_model->delete_records('tbl_user_editable_columns', array('tbl_user_editable_column_id' => $id));
        }
        if ($type == 'stGoal') {
            $this->Common_model->delete_records('tbl_strategy_goal_colums', array('strategy_goal_colum_id' => $id));
            $this->Common_model->delete_records('tbl_strategy_goal_colums', array('source_id' => $id));
            $this->Common_model->delete_records('tbl_strategy_goal_comments', array('colum_id' => $id));
        }
        if ($type == 'child_columns') {
            // $this->Common_model->delete_records('tbl_strategy_goal_child_colums',array('strategy_goal_child_id'=>$id));
            //$this->Common_model->delete_records('tbl_strategy_goal_comments',array('colum_id'=>$id));
        }

        if ($type == 'stGoalTitle') {
            $this->Common_model->delete_records('tbl_strategy_goals_titles', array('tbl_strategy_goals_profile_Id' => $id));
            // $this->Common_model->delete_records('tbl_strategy_goals_titles',array('tbl_strategy_goals_profile_Id'=>$id));
        }

        if ($type == 'columns' || $type == 'stGoal') {
            if ($oldData['section_number'] > 0) {
                $oldDataTdel = $this->Common_model->get_data_row('section_number', $table, array('section_number' => $oldData['section_number'], 'partner_profile_id' => $oldData['partner_profile_id'], 'client_id' => $oldData['client_id']));
                if (!isset($oldDataTdel['section_number'])) {
                    $sql = "UPDATE $table set section_number=(section_number-1) WHERE section_number>2 AND partner_profile_id=" . $oldData['partner_profile_id'];
					$this->db->query($sql);
                    
                }
            }
        }
    }

    public function single_goal_data()
    {
        $this->load->model('Managegoals_model');
        $this->load->model('Manageproducts_model');
        $this->load->model('Managestrategies_model');
        $id = $this->input->post('goal_id');
        $type = $this->input->post('type');
        $clientid = $this->session->userdata('client_id');
        if ($type == 2) {
            $query1 = $this->Managegoals_model->getdataall_assign_strategies($id);
            $goals = $this->Managegoals_model->getdata($clientid, $id);
            $data['goals'] = $goals;
            $data['product_selected'] = $query1;
        } elseif ($type == 3) {
            $query2 = $this->Managestrategies_model->getdataall_assign_strategies($id);
            $strategiess = $this->Managestrategies_model->getdata($clientid, $id);
            $data['strategiess'] = $strategiess;
            $data['product_selected'] = $query2;
        }
        $query = $this->Manageproducts_model->getdataall($clientid);
        $data['goals_id'] = $id;
        $data['products'] = $query;
        $data['module_type'] = $type;
        $this->load->view('fronthand/twitter/partner_profile/goals_popup', $data);
    }

    public function update_qbr_status()
    {
        $clientID = $this->session->userdata('client_id');
        $status = $this->input->post('status');
        $data = array('add_qbr_status' => $status);
        // $sql="UPDATE tbl_forecast_year SET add_qbr_status='".$status."' WHERE client_id=$clientID";
        // $this->db->query($sql);
        // debug($this->db->last_query());
    }

    public function partner_profile_drag()
    {
        $clientid = $this->session->userdata('client_id');

        $business_id_array = $this->input->post('business_id_array');
        $business_order_array = $this->input->post('business_order_array');
        $increement = 0;
        foreach ($business_id_array as $business_id) {
            $this->db->query("update tbl_partner_profile set step_order=$business_order_array[$increement] where partner_profile_id = $business_id and client_id=$clientid ");
            ++$increement;
        }
        $this->partner_profile();
    }

    public function delete_scorecard_profile()
    {
        $delete_id = $this->input->post('scorecard_id');
        $this->db->where('tbl_scorecard_profile_id', $delete_id);
        $this->db->delete('tbl_scorecard_profile');
    }

    public function manage_label()
    {
        $clientid = $this->session->userdata('client_id');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('label_name', 'trim|required');
        $key[] = $content_key = $this->input->post('content_key');
        $product_column_title = $this->input->post('label_name');
        $res[$content_key] = $product_column_title;
        $data['product_column'] = $this->Common_model->get_data_row('*', 'tbl_manage_content', array('client_id' => $clientid, 'content_key' => 'revenue'));

        if ($this->form_validation->run() == false) {
            $data['main_content'] = 'fronthand/twitter/manage_label/manage_label';
            $this->load->view('/includes/template_new', $data);
        } else {
            $this->load->model('Manageproducts_model');
            if ($query = $this->Manageproducts_model->update_content($clientid, $res, $key)) {
                redirect('/dashboard/manage_revenue');
            } else {
                $data['main_content'] = 'fronthand/twitter/manage_label/manage_label';
                $this->load->view('/includes/template_new', $data);
            }
        }
    }

    public function update_group_goal_strategy()
    {
        $where = array('client_id' => $this->session->userdata('client_id'));
        $data = array('group_goal_strategy' => $this->input->post('keyVal'));

        return $this->Common_model->update_data('admin_clients', $data, $where);
    }

    public function removedropdown()
    {
        if (empty($this->input->post('type'))) {
            $table = 'tbl_plan_dropdown';
        } else {
            $table = 'tbl_plan_description';
        }
        $this->db->where('id', $this->input->post('id'));
        $this->db->where('client_id', $this->session->userdata('client_id'));

        return $this->db->delete($table);
    }

    /* Ticket ID: 1561 * - by Deepak */
    public function condtions_list()
    {    
	    $this->Common_model->check_dashboard_login(); 
        $clientid = $this->session->userdata('client_id');
        $status = $this->input->post('status');
        $question_id = $this->input->post('question_id');
        if ($status != '') {
            $this->Common_model->update_data('tbl_conditional_question', array('status' => $status), array('client_id' => $clientid, 'tbl_conditional_question_id' => $question_id));
        }
        $sql = "select t1.tbl_conditional_question_id,t1.title,t1.status ,t2.tbl_conditional_response_id,t2.response from tbl_conditional_question as t1 inner join tbl_conditional_response as t2 on t1.tbl_conditional_question_id=t2.conditional_question_id where t1.client_id = $clientid";
        $condition_data = $this->Common_model->get_conditional_array($sql);

        foreach ($condition_data as $val) {
            $temp['tbl_conditional_response_id'] = $val['tbl_conditional_response_id'];
            $temp['response'] = $val['response'];
            $data['response_data'][$val['tbl_conditional_question_id']][] = $temp;
            $data['question_data'][$val['tbl_conditional_question_id']] = $val;
        }
        $select_conditional_responses = $this->Common_model->get_conditional_array("select manage_content_id,content_key,status from tbl_manage_content where content_key in
		('application_header','cam_question_response') and client_id=$clientid");
        $data['select_conditional_responses'] = $this->Common_model->keyValuepair($select_conditional_responses, 'manage_content_id', 'content_key');
        $data['select_responses_status'] = $this->Common_model->keyValuepair($select_conditional_responses, 'status', 'content_key');
        if (!array_key_exists('application_header', $data['select_responses_status'])) {
            $application_header_data = array('client_id' => $clientid, 'title' => 'application header', 'content_key' => 'application_header', 'status' => '1', 'created_on' => date('Y/m/d'));
            $this->Common_model->insert_data('tbl_manage_content', $application_header_data);
        }

        $data['no_condition'] = $this->Common_model->get_data_row('manage_content_id,content_key,status', 'tbl_manage_content', array('client_id' => $clientid, 'content_key' => 'no_condition'));

        $data['main_content'] = 'fronthand/twitter/admin_conditions/condtions_admin_list';
        $this->load->view('/includes/template_new', $data);
    }

    public function no_condition()
    {
        $clientid = $this->session->userdata('client_id');
        $status = $this->input->post('status');
        $manage_content_id = $this->input->post('manage_content_id');

        $select_conditional_responses = $this->Common_model->get_conditional_array("select manage_content_id,content_key,status from tbl_manage_content where content_key in
			('no_condition') and client_id=$clientid");

        if (is_array($select_conditional_responses) and count($select_conditional_responses) > 0) {
            $this->Common_model->update_data('tbl_manage_content', array('status' => $status), array('client_id' => $clientid, 'content_key' => 'no_condition'));
        } else {
            $insert_data = array('title' => 'No Condition', 'status' => $status, 'content_key' => 'no_condition', 'client_id' => $clientid, 'created_on' => date('Y/m/d'));
            $this->Common_model->insert_data('tbl_manage_content', $insert_data, array('client_id' => $clientid));
        }
    }

    //Ticket ID #1561 Code By Deepak
    public function edit_conditions()
    {   
        $clientid = $this->session->userdata('client_id');
        $question_id = $this->uri->segment(4);
        $this->load->model('Sfowner_model');
		
        if ($question_id != '') {
            $sql = "select t1.tbl_conditional_question_id,t1.title,t1.status,t2.tbl_conditional_response_id,t2.response,t2.description from tbl_conditional_question as t1 inner join tbl_conditional_response as t2 on t1.tbl_conditional_question_id=t2.conditional_question_id where t1.client_id = $clientid and t1.tbl_conditional_question_id = $question_id";
            $condition_data = $this->Common_model->get_conditional_array($sql);
        }
        foreach ($condition_data as $k => $val) {
            $tmp['tbl_conditional_response_id'] = $val['tbl_conditional_response_id'];
            $tmp['response'] = $val['response'];
            $tmp['description'] = $val['description'];
            $data['response_details'][$val['tbl_conditional_question_id']][] = $tmp;
            $data['question_details'][$val['tbl_conditional_question_id']][] = $val;
        } 
        $question_text = $this->input->post('question');
        if (!empty($question_text)) {
            $this->Sfowner_model->update_response($clientid, $question_text);
        }
        $data['main_content'] = 'fronthand/twitter/admin_conditions/edit_admin_condtions';
        $this->load->view('/includes/template_new', $data);
    }

    //Ticket ID #1561 Code By Deepak
    public function add_conditions_questions()
    {
        $clientid = $this->session->userdata('client_id');
        $data['main_content'] = 'fronthand/twitter/admin_conditions/add_admin_condtions';
        $this->load->view('/includes/template_new', $data);
    }

    //Ticket ID #1561 Code By Deepak
    public function save_conditions_questions()
    {
        $clientid = $this->session->userdata('client_id');
        $response_id = $this->input->post('response_id');
        $condtion_q = $this->input->post('cond_q');
        $response = $this->input->post('response');
        $description = $this->input->post('description');
        $question = array('title' => $condtion_q, 'client_id' => $clientid, 'created_on' => date('Y/m/d'));
        $this->Common_model->insert_data('tbl_conditional_question', $question);
        $question_id = $this->db->insert_id();
        foreach ($response as $k => $val) {
            $temp['response'] = $val;
            $temp['description'] = $description[$k];
            $temp['conditional_question_id'] = $question_id;
            $temp['created_on'] = date('Y/m/d');
            $response_data[] = $temp;
        }
        if ($this->db->insert_batch('tbl_conditional_response', $response_data)) {
            $this->session->set_flashdata('edit_msg', "<div class='alert alert-success'>Records Updated Successfully</div>");
            redirect('dashboard/condtions_list');
        }
    }

    //Ticket ID #1561 Code By Deepak
    public function delete_conditions_question()
    {
        $clientid = $this->session->userdata('client_id');
        $question_id = $this->input->post('question_id');
        if ($this->db->delete('tbl_conditional_question', array('tbl_conditional_question_id' => $question_id), array('client_id' => $clientid))) {
            $this->session->set_flashdata('edit_msg', "<div class='alert alert-success'>Conditional Question is deleted successfully</div>");
        }
        redirect('dashboard/condtions_list');
    }

    //Ticket ID #1561 Code By Deepak
    public function delete_response()
    {
        $clientid = $this->session->userdata('client_id');
        $response_id = $this->input->post('response_id');
        $this->Common_model->delete_records('tbl_conditional_response', array('tbl_conditional_response_id' => $response_id), array('client_id' => $clientid));
    }

    //Ticket ID #1561 Code By Deepak
    public function condtional_module()
    {   $profile_id       = array();
        $clientid         = $this->session->userdata('client_id');
		$response_id      = $this->input->post('response_id');
		$question_id      = $this->input->post('question_id');
		$module_names     = $this->input->post('module_names');
		//debug($_POST); die;
		
        foreach ($module_names as $key => $module_val) {
		    $explode_module      = explode('<>', $module_val);    
		    $profile_id[]   =  $explode_module[0];
			$check_exists = $this->Common_model->get_data_row('*', 'tbl_conditional_module', array('client_id' => $clientid,'question_id' => $question_id,'response_id' => $response_id, 'partner_profile_id' => $explode_module[0]));
			if(count($check_exists)==0){
            $temp[$key]['response_id']        = $response_id;
            $temp[$key]['question_id']        = $question_id;
            $temp[$key]['client_id']          = $clientid;
            $temp[$key]['partner_profile_id'] = $explode_module[0];
            $temp[$key]['module_type']        = $explode_module[1];
            $temp[$key]['created_on']         = date('Y/m/d');
			}
			
			
			
        }
		if(!empty($profile_id)){
		$del_not_in = implode(',', $profile_id);
		$del_sql = "delete from tbl_conditional_module where question_id='".$question_id."' and response_id='".$response_id."' and  partner_profile_id not in($del_not_in) ";
		$this->db->query($del_sql);	
		}
		if(empty($profile_id)){
		$del_sqls = "delete from tbl_conditional_module where question_id='".$question_id."' and response_id='".$response_id."' ";
		$this->db->query($del_sqls);	
		}
		
        if (!empty($temp)) {
            $this->db->insert_batch('tbl_conditional_module', $temp);
            $this->partner_profile();
        }
		

		
		
    }

    //Ticket ID #1561 Code By Deepak
    public function update_apps()
    {
        $clientid = $this->session->userdata('client_id');
        $status = $this->input->post('status');
        $questionID = $this->input->post('questionID');
        $responseID = $this->input->post('responseID');
        $conditionappsID = $_POST['conditionappsID'];
        $id = $this->Common_model->get_data_array('condtional_apps_id', 'tbl_condtional_apps', array('client_id' => $clientid, 'type' => 'action_plan'));

        $indxarray = $this->Common_model->keyValuepair($id, 'condtional_apps_id', 'condtional_apps_id');
        $data = array('conditional_question_id' => $questionID, 'conditional_response_id' => $responseID, 'client_id' => $clientid, 'status' => $status, 'type' => 'action_plan', 'condtional_apps_id' => $conditionappsID);

        if (in_array($conditionappsID, $indxarray)) {
            $this->Common_model->update_data('tbl_condtional_apps', array('status' => $status), array('client_id' => $clientid, 'conditional_response_id' => $responseID, 'type' => 'action_plan'));
            echo $conditionappsID;
        } else {
            echo $this->Common_model->insert_data('tbl_condtional_apps', $data, array('client_id' => $clientid, 'conditional_question_id' => $question_id));
        }
    }

    //Ticket ID #1561 Code By Deepak
    public function select_conditional_responses()
    {
        $clientid = $this->session->userdata('client_id');
        $status = $this->input->post('status');
        $manage_content_id = $this->input->post('manage_content_id');

        $select_conditional_responses = $this->Common_model->get_conditional_array("select manage_content_id,content_key from tbl_manage_content where content_key in
		('application_header','cam_question_response') and client_id=$clientid");
        $select_responses = $this->Common_model->keyValuepair($select_conditional_responses, 'manage_content_id', 'content_key');
        $content_key = $this->input->post('content_key');
        if ($content_key == 'application_header') {
            $title = 'application header';
        } elseif ($content_key == 'cam_question_response') {
            $title = 'cam question response';
        }
        $insert_data = array('title' => $title, 'status' => $status, 'content_key' => $content_key, 'client_id' => $clientid, 'created_on' => date('Y/m/d'));
        if (in_array($manage_content_id, $select_responses)) {
            $this->Common_model->update_data('tbl_manage_content', array('status' => $status), array('client_id' => $clientid, 'manage_content_id' => $manage_content_id));
            // echo $manage_content_id;
            $this->session->set_flashdata('edit_msg', "<div class='alert alert-success'>Records Updated Successfully</div>");
        } else {
            $this->Common_model->insert_data('tbl_manage_content', $insert_data, array('client_id' => $clientid));
            $this->session->set_flashdata('edit_msg', "<div class='alert alert-success'>Records Updated Successfully</div>");
        }
    }

    public function delete_module()
    {
        $clientid = $this->session->userdata('client_id');
        $data = $this->input->post('data_sub');
        $condtional_module_id = $data[0]['conditional_module_id'];
        if ($this->db->delete('tbl_conditional_module', array('conditional_module_id' => $condtional_module_id), array('client_id' => $clientid))) {
            $this->session->set_flashdata('edit_msg', "<div class='alert alert-success'>Records Deleted Successfully</div>");
        }
    }

    // function set_transaction_status(){
    // $clientid = $this->session->userdata('client_id');
    // $data['transaction_id_status'] = $this->input->post('transaction_id_status');
    // $this->Common_model->update_data('admin_clients',$data,array('client_id'=>$clientid));
    // echo "success";
    // }

    /* Function for download excel for actuals */
    public function download_excel($type = 1, $startDate = null, $endDate = null, $SFonly = null, $searchType = null, $errorOnly = null)
    { //$type 1=>partner,2=>member
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }

        $client_id = $this->session->userdata('client_id');
        $transaction_id_status = $this->Common_model->get_data_row('transaction_id_status', 'admin_clients', array('client_id' => $client_id));

        if ($client_id == '') {
            redirect('manageaudit');
        }
        $is_checked = $this->Common_model->get_data_row('is_checked', 'client_pl_steps', array('clientid' => $client_id))['is_checked'];
        $data = array();

        $file_name = 'Actual_upload_sample.xlsx';

        $filename = logo_path . 'clientdata/' . $file_name;

        $this->load->library('PHPExcel/Classes/PHPExcel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $first_row = array(
            'Partner Name',
            'Primary Partner ID#  *',
            'Secondary Partner ID#',
            'Product Name  *',
            'Successful Channels Product ID',
            'Transaction Date  *',
            'Unit',
            'Revenue Per Unit',
            'Total Transaction $ Value',
            'Manual Transaction Date Override',
        );
        if ($transaction_id_status['transaction_id_status'] == '1') {
            array_splice($first_row, 9, 0, 'Transaction ID  *');
        } else {
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );

        $objPHPExcel->getActiveSheet()->getStyle('A0')->applyFromArray($style);
        $objPHPExcel->getActiveSheet()->getStyle('M0')->applyFromArray($style);

        $column = 'A';
        $rowCount = 1;

        for ($i = 0; $i < count($first_row); ++$i) {
            $objPHPExcel->getActiveSheet()->setCellValue($column . $rowCount, $first_row[$i]);
            $objPHPExcel->getActiveSheet()->getStyle($column . $rowCount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getStyle($column . $rowCount)->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFFF00'),
                    ),
                )
            );
            ++$column;
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($filename);
        $redirect_link = $this->config->item('base_url1') . '/uploads/clientdata/' . $file_name;
        redirect($redirect_link);
    }

    //added by kirti for partner_actual_data(ticket_id:#1617)
    public function actual_sales_data()
    {   
	    $this->Common_model->check_dashboard_login();
        $client_id = $this->session->userdata('client_id');
        $data['all_partners'] = $this->Common_model->get_data_array('id,partner_acc_name', 'partners_accounts', array('clientid' => $client_id), 'partner_acc_name', 'ASC');
        $data['main_content'] = 'fronthand/twitter/actual_sales/actual_sales';
        $this->load->view('/includes/template_new', $data);
    }

    public function get_actual_sales_data($limit = 50)
    {
        $client_id = $this->session->userdata('client_id');
        $startDate = date('Y-m-d', strtotime($this->input->post('startDate')));
        $endDate = date('Y-m-d', strtotime($this->input->post('endDate')));
        $partner_ids = $this->input->post('partner_ids');
        if ($partner_ids) {
            $ids = implode(',', $partner_ids);
        } else {
            $ids = -1;
        }
        $data['limit'] = $limit;
        // debug($limit);
        $sql = "select pa.id,ps.partner_acc_name,ps.primary_partner_id,mpa.productname,
			pa.transaction_date,pa.unit,pa.dollar,pa.transaction_id,pa.insert_date,
			pa.modify_on from partners_actual_data pa inner join partners_accounts
			ps on ps.id=pa.partner_id inner join manage_products mpa on mpa.id=pa.products
			where pa.client_id=$client_id and pa.transaction_date between '$startDate' and '$endDate' AND ps.id IN($ids) ";
        $data['sales_data'] = $this->Common_model->get_conditional_array($sql);
        $data['partner_data'] = $this->Common_model->keyValuepair($data['sales_data'], 'partner_acc_name', 'id');
        $this->load->view('fronthand/twitter/actual_sales/actual_sales_table', $data);
    }

    public function delete_actual_sales()
    {
        if ($_POST['limit']) {
            $limit = $_POST['limit'];
        } else {
            $limit = 50;
        }

        // debug($limit,1);
        $startDate = date('Y-m-d', strtotime($this->input->post('startDate')));

        $endDate = date('Y-m-d', strtotime($this->input->post('endDate')));
        $sales_id = $this->input->post('sales_id');
        if (!empty($sales_id)) {
            $this->db->where_in('id', $sales_id);
            $this->db->where('transaction_date >=', $startDate);
            $this->db->where('transaction_date <=', $endDate);
            $delete = $this->db->delete('partners_actual_data');
        }
        $this->get_actual_sales_data($limit);
    }

    //end by kirti (ticket_id:#1617)

    public function strategyOneTime()
    {
        $saveData = array();
        $sql = 'select distinct(partner_profile_id) from tbl_strategy_goal_colums';
        $all = $this->Common_model->get_conditional_array($sql);
        foreach ($all as $temp) {
            $sql = 'select * from tbl_strategy_goal_colums where partner_profile_id = ' . $temp['partner_profile_id'] . " AND column_type='30'";
            $exist = $this->Common_model->get_conditional_array($sql);
            if (empty($exist)) {
                $sql = 'select * from tbl_strategy_goal_colums where partner_profile_id = ' . $temp['partner_profile_id'] . '  order by section_number desc';
                $pervData = $this->Common_model->get_conditional_array($sql)[0];

                for ($i = 0; $i < 10; ++$i) {
                    if ($i == 0) {
                        $sectionNo = ($pervData['section_number'] + 2);
                        $datType = '1';
                        $colType = '0';
                        $name = '9 Columns Related to MDF Request';
                    } else {
                        $sectionNo = ($pervData['section_number'] + 1);
                        $colType = '30';
                        if ($i == 1) {
                            $name = 'Activity Type';
                            $datType = '5';
                        } elseif ($i == 2) {
                            $name = 'Activity Date';
                            $datType = '3';
                        } elseif ($i == 3) {
                            $name = 'Total Cost of Activity';
                            $datType = '2';
                        } elseif ($i == 4) {
                            $name = 'MDF Requested';
                            $datType = '2';
                        } elseif ($i == 5) {
                            $name = '% Devoted to Client';
                            $datType = '6';
                        } elseif ($i == 6) {
                            $name = '% Eligible for Reimbursement';
                            $datType = '6';
                        } elseif ($i == 7) {
                            $name = 'Expected ROI';
                            $datType = '2';
                        } elseif ($i == 8) {
                            $name = 'Approval Status';
                            $datType = '5';
                        } elseif ($i == 9) {
                            $name = 'MDF Allocated';
                            $datType = '2';
                        }
                    }

                    $data['strategy_goal_colum_id'] = null;
                    $data['partner_profile_id'] = $pervData['partner_profile_id'];
                    $data['client_id'] = $pervData['client_id'];
                    $data['type'] = 1;
                    $data['column_name'] = $name;
                    $data['column_width'] = '11.11%';
                    $data['column_type'] = $colType;
                    $data['data_type'] = $datType;
                    $data['tool_tip_act'] = 0;
                    $data['tool_tip_val'] = '';
                    $data['show_hide'] = 0;
                    $data['desc_as_text'] = 0;
                    $data['section_number'] = $sectionNo;
                    $data['colum_order'] = $i;

                    $saveData[] = $data;
                }
            }
        }
        if (!empty($saveData)) {
            $this->db->insert_batch('tbl_strategy_goal_colums', $saveData);
        }
    }

    //Code By Deepak Date 07/02/2019
    /*******************Call View Business Action Plan Report Export****************/
    public function action_plan_report()
    {   
	$this->Common_model->check_dashboard_login(); 
        $clientid = $this->session->userdata('client_id');
        $get_partnertype = $this->Common_model->get_data_array('tbl_admin_partnertype_export_id,partner_type', 'tbl_admin_partnertype_export', array('client_id' => $clientid));

        if ($_POST['responseID'] != '') {
            foreach ($_POST['responseID'] as $k => $val) {
                $tempup['partner_type'] = $val;
                $tempup['client_id'] = $clientid;
                $tempup['created_on'] = date('Y/m/d');
                $tempup['tbl_admin_partnertype_export_id'] = $_POST['export_id'][$k];
                $update[] = $tempup;
            }

            $this->Common_model->delete_records('tbl_admin_partnertype_export', array('client_id' => $clientid));
            $this->db->insert_batch('tbl_admin_partnertype_export', $update);
            // redirect('dashboard/action_plan_report');
        }
        //End Save Partner type
        $sql = "select t2.tbl_conditional_response_id,t1.tbl_conditional_question_id,t1.client_id,t2.response,t3.module_type,t3.partner_profile_id FROM tbl_conditional_question as t1 inner JOIN tbl_conditional_response as t2 on t1.tbl_conditional_question_id=t2.conditional_question_id inner JOIN tbl_conditional_module as t3 on t2.tbl_conditional_response_id=t3.response_id WHERE t1.client_id='" . $clientid . "' and t1.status='1' GROUP BY t2.tbl_conditional_response_id";
        $data['Partner_typename'] = $this->Common_model->get_conditional_array($sql); //Get Response Name

        //get Partner Type Data From tbl_admin_partnertype_export

        foreach ($get_partnertype as $temp) {
            $data['get_partnertype'][$temp['partner_type']] = $temp;
        }
        $data['main_content'] = 'fronthand/twitter/action_plan_report/export_action_plan_report';
        $this->load->view('/includes/template_new', $data);
    }

    /*******************Call View Business Action Plan Report Export****************/

    /************** Manage Mdf Balance For Business Action Plan***********/
    //Code By Deepak Date 18/02/2019
    public function manage_mdf_balance()
    {    
	    $this->Common_model->check_dashboard_login();
        $clientid = $this->session->userdata('client_id');
        $data['forecast_data'] = $this->Common_model->get_data_row('start_fiscal_year,end_fiscal_year,start_month,end_month', 'tbl_forecast_year', array('client_id' => $clientid));

        $result = array();
        for ($i = $data['forecast_data']['start_fiscal_year']; $i <= $data['forecast_data']['end_fiscal_year']; ++$i) {
            if ($data['forecast_data']['start_month'] == 1) {
                $result[$i] = $i;
            }
            if ($data['forecast_data']['start_month'] != 1 && $data['forecast_data']['end_fiscal_year'] >= ($i + 1)) {
                $result[$i] = ($i + 1);
            }
        }

        $data['selecterYearDropDown'] = $result;

        $data['primary_regions_data'] = $this->Common_model->get_data_array('region_level1_id,name', 'region_level1', array('client_id' => $clientid));

        if ($_POST['select_yr'] != '') {
            $select_yr = $_POST['select_yr'];
        } else {
            $select_yr = $data['forecast_data']['start_fiscal_year'];
        }
        $data['qtrs_array'] = $this->Common_model->year_month_arr($select_yr, $data['forecast_data']['start_month']);
        $data['selected_yr'] = $this->input->post('fiscal_year');

        $mdfbal_regiondata = $this->Common_model->get_data_array('mdf_balance_region_id,client_id,region_level1_id,year,quarter,value', 'tbl_mdf_balance_region', array('client_id' => $clientid, 'year' => $select_yr));
        foreach ($mdfbal_regiondata as $val) {
            $data['mdfbal_regiondata'][$val['region_level1_id']][$val['quarter']] = $val;
            $data['total_mdfbal_region'][$val['region_level1_id']][] = $val['value'];
            $data['grandtotal_regionwise'][] = $val['value'];
            $data['grandtotalquaterwise'][$val['quarter']][] = $val['value'];
        }
        $data['months'] = $this->month;

        // debug($data['grandtotalquaterwise']);
        $data['main_content'] = 'fronthand/twitter/partner_profile/manage_mdf_balance/dashboard/manage_mdf_balance_view';
        $this->load->view('/includes/template_new', $data);
    }

    //Save MDF Balance Data Region Wise
    public function save_mdf_bal_region()
    {
        $clientid = $this->session->userdata('client_id');
        $mdf_values = $this->input->post('mdf_bal');
        debug($this->input->post('select_yr'));
        $temp = array();
        foreach ($mdf_values as $key => $val) {
            foreach ($val as $k => $mdf_bal) {
                if ($mdf_bal != '') {
                    $temp['client_id'] = $clientid;
                    $temp['region_level1_id'] = $key;
                    $temp['year'] = $this->input->post('fiscal_year');
                    $temp['quarter'] = $k;
                    //edit by kirti
                    $temp['value'] = str_replace(',', '', $mdf_bal);
                    $temp['created_on'] = date('Y-m-d');
                    if ($this->input->post('mdf_balance_region_id')[$key][$k] != '') {
                        $temp['mdf_balance_region_id'] = $this->input->post('mdf_balance_region_id')[$key][$k];
                        $update[] = $temp;
                    } else {
                        unset($temp['mdf_balance_region_id']);
                        $insert[] = $temp;
                    }
                }
            }
        }
        if (!empty($insert)) {
            $this->db->insert_batch('tbl_mdf_balance_region', $insert);
        }
        if (!empty($update)) {
            $this->db->update_batch('tbl_mdf_balance_region', $update, 'mdf_balance_region_id');
        }

        $this->manage_mdf_balance();
    }

    /*********************** Manage Mdf Balance For Business Action Plan***********/

    public function manage_loader()
    {  
	    $this->Common_model->check_dashboard_login();
        $client_id = $this->session->userdata('client_id');

        $this->add_default_loaders($client_id);
        $data['client'] = $this->Common_model->get_data_row('client_uniquename', 'admin_clients', array('client_id' => $client_id));

        if ($_POST) {
            $loader_title = $this->input->post('loader_title');
            if ($_FILES['loader_image']['tmp_name'] != '') {
                $ext = array_pop(explode('.', $_FILES['loader_image']['name']));
                $filename = $data['client']['client_uniquename'] . '_' . time() . '.' . $ext;
            } elseif ($this->input->post('loader_radio')) {
                $loader_type = explode('<>', $this->input->post('loader_radio'));
                $id = $loader_type[0];
                $filename = $loader_type[1];
            } else {
                $filename = 'ajax-loader.gif';
            }

            $upload_path = BASE_ABS_PATH . 'uploads/loader_images/';
            move_uploaded_file($_FILES['loader_image']['tmp_name'], $upload_path . $filename);
            $save_data = array(
                'client_id' => $client_id,
                'loader_title' => $loader_title,
                'loader_image' => $filename,
                'is_active' => 1,
            );
            $this->Common_model->update_data('tbl_manage_loader', array('is_active' => 0), array('client_id' => $client_id));
            if ($id) {
                $this->Common_model->update_data('tbl_manage_loader', $save_data, array('id' => $id));
            } else {
                $this->Common_model->insert_data('tbl_manage_loader', $save_data);
            }
        }

        $data['s_loader_data'] = $this->Common_model->get_data_array('*', 'tbl_manage_loader', array('client_id' => $client_id, 'loader_type' => 0));
        $data['c_loader_data'] = $this->Common_model->get_data_array('*', 'tbl_manage_loader', array('client_id' => $client_id, 'loader_type' => 1));
        $data['selected_loader'] = $this->Common_model->get_data_row('*', 'tbl_manage_loader', array('client_id' => $client_id, 'is_active' => 1));

        $data['main_content'] = 'fronthand/twitter/manage_loader/manage_loader';
        $this->load->view('/includes/template_new', $data);
    }

    public function add_default_loaders($client_id)
    {

        $default_loader = array('ajax-loader.gif', 'loading1.gif', 'loading2.gif', 'loading3.gif', 'loading4.gif', 'loading5.gif');
        $is_data = $this->Common_model->get_data_array('*', 'tbl_manage_loader', array('client_id' => $client_id));
        $save_arr = array();
        for ($i = 0; $i < 6; $i++) {
            $save_data['client_id'] = $client_id;
            $save_data['loader_image'] = $default_loader[$i];
            $save_data['loader_type'] = 0;
            if ($i == 0) {
                $save_data['is_active'] = 1;
                $save_data['loader_title'] = "Please wait while your page is loading...";
            } else {
                $save_data['is_active'] = 0;
            }

            $save_arr[] = $save_data;
        }
        if (empty($is_data)) {
            $this->db->insert_batch('tbl_manage_loader', $save_arr);
        }

    }
    //**************NEW ACTION PLAN CODE START**************************//
    public function action_plan()
    { //startegy add/edit step 1 //colum add edit
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Action_plan_model');

        $clientid = $this->session->userdata('client_id');
        $partner_profile_id = $this->uri->segment(4);
        $defalutCmNames = $this->Action_plan_model->defalutNames();
        if ($partner_profile_id) { //edit
            $data = $this->Action_plan_model->getModule($clientid, $partner_profile_id);
            $field_titles = $this->Common_model->get_data_array('field_title_id,type,field_title', 'tbl_partner_profile_field_titles', array('client_id' => $clientid, 'partner_profile_id' => $partner_profile_id));
            $data['profile_titles'] = $this->Common_model->keyValuepair($field_titles, 'field_title', 'field_title_id');

            $prePublish_selected = $this->Common_model->get_data_array('prepublish_id', 'tbl_strategy_prepublish_selection', array('strategy_goal_id' => $partner_profile_id));
            foreach ($prePublish_selected as $pre) {
                $data['prePublish_selected'][] = $pre['prepublish_id'];
            }
            $dropDwn = $this->Action_plan_model->getDropdown($clientid, $partner_profile_id);
            $data = array_merge($data, $dropDwn);
            $data['colms'] = $this->Action_plan_model->getStrtgyColums($clientid, $partner_profile_id);
            foreach ($data['colms'] as $temp) {
                $data['section_colms'][$temp['section_number']][$temp['colum_order']] = $temp;
            }
			$sql= "SELECT count(type) as hideMDF FROM tbl_strategy_goal_colums WHERE column_type='30' and show_hide=0 AND partner_profile_id=$partner_profile_id and client_id=$clientid";
			$data['hideMDF'] = $this->Common_model->get_conditional_array($sql)[0]['hideMDF'];
        } else {
            $data = $this->Action_plan_model->getStratgyDefaultColmArry($defalutCmNames);
			$data['hideMDF'] = 1;
        }
        $data['defalutCmNames'] = $defalutCmNames;
        $pre_query = "select t1.partner_profile_id,t2.field_title from tbl_partner_profile t1 join tbl_partner_profile_field_titles t2 on t1.partner_profile_id = t2.partner_profile_id where t1.profile_type = '5' AND t1.client_id = $clientid";
        $data['prepublish_titles'] = $this->Common_model->get_conditional_array($pre_query);

		$selectedpre_query = "select prepublish_id from tbl_strategy_prepublish_selection where client_id = $clientid";
        $selected_prepublish = $this->Common_model->get_conditional_array($selectedpre_query);
		foreach($selected_prepublish as $val){
			$data['selected_prepublish'][] = $val['prepublish_id'];
		}
		
        $data['main_content'] = 'fronthand/twitter/partner_profile/action_plan/dashboard/action_plan';
        $this->load->view('/includes/template_new', $data);
    }
    public function action_plan_pre()
    { //prepubish step 1 add/edit column
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Action_plan_model');

        $clientid = $this->session->userdata('client_id');
        $partner_profile_id = $this->uri->segment(4);
        $defalutCmNames = $this->Action_plan_model->defalutNamesPre();

        if ($partner_profile_id) { //edit
            $data = $this->Action_plan_model->getModule($clientid, $partner_profile_id);
            $field_titles = $this->Common_model->get_data_array('field_title_id,type,field_title', 'tbl_partner_profile_field_titles', array('client_id' => $clientid, 'partner_profile_id' => $partner_profile_id));
            $data['profile_titles'] = $this->Common_model->keyValuepair($field_titles, 'field_title', 'field_title_id');

            $dropDwn = $this->Action_plan_model->getDropdown($clientid, $partner_profile_id);
            $data = array_merge($data, $dropDwn);
            $data['colms'] = $this->Action_plan_model->getPreColums($clientid, $partner_profile_id);

            foreach ($data['colms'] as $temp) {
                $data['section_colms'][$temp['section_number']][$temp['colum_order']] = $temp;
            }
        } else {
            $data = $this->Action_plan_model->getPrepublishDefaultColmArry($defalutCmNames);
        }
        $data['defalutCmNames'] = $defalutCmNames;

        $data['main_content'] = 'fronthand/twitter/partner_profile/action_plan/dashboard/action_plan_pre';
        $this->load->view('/includes/template_new', $data);
    }
    public function action_plan_drag()
    { //get column detail for drag drop
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        $partner_profile_id = $this->uri->segment(4);
        if ($partner_profile_id) { //edit
            $data = $this->Action_plan_model->getModuleNcolums($clientid, $partner_profile_id);
        }
        $data['main_content'] = 'fronthand/twitter/partner_profile/action_plan/dashboard/action_plan_drag';
        $this->load->view('/includes/template_new', $data);

    }
    public function action_plan_width()
    { // get column detail for width managment
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        // $module_type = $this->uri->segment(3);
        $partner_profile_id = $this->uri->segment(4);
        if ($partner_profile_id) { //edit
            $data = $this->Action_plan_model->getModuleNcolums($clientid, $partner_profile_id);
            foreach ($data['colms'] as $temp) {
                $data['colms_width'][$temp['section_number']][] = $temp;
            }
        }
        $data['main_content'] = 'fronthand/twitter/partner_profile/action_plan/dashboard/action_plan_width';
        $this->load->view('/includes/template_new', $data);

    }
    public function action_plan_source()
    { // get data for source destination relationship
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        $partner_profile_id = $this->uri->segment(4);
        if ($partner_profile_id) { //edit
            $data = $this->Action_plan_model->getModuleNcolums($clientid, $partner_profile_id);
        }
		// debug( $data );
        $data['main_content'] = 'fronthand/twitter/partner_profile/action_plan/dashboard/action_plan_source';
        $this->load->view('/includes/template_new', $data);

    }
	
	public function action_plan_conditionDisplay()
    { // get data for source destination relationship
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        $partner_profile_id = $this->uri->segment(4);
		 
        if ($partner_profile_id) { //edit
            $data = $this->Action_plan_model->getModuleNcolums($clientid, $partner_profile_id);
        }
		if(is_array($data['colms']) && !empty($data['colms'])){
		foreach($data['colms'] as $dataval){
			if($dataval['condition_display']!=0) { 
			$data['strategy_id'][] = $dataval['strategy_goal_colum_id'];
			$data['create_own'][$dataval['strategy_goal_colum_id']] = $dataval['create_own'];
			}
		}
		}
		 if(is_array($data['strategy_id']) && !empty($data['strategy_id'])){
		 $sql= "SELECT * from tbl_plan_dropdown WHERE column_id IN(".implode(',',$data['strategy_id']).") AND partner_profile_id  = ".$data['partner_profile']['partner_profile_id']."  AND client_id=$clientid AND partner_id=0 ";
		 $dropdownData = $this->Common_model->get_conditional_array($sql);
		 foreach($dropdownData as $key=>$valdrop){
			 $data['dropdownData'][$key]['id'] = $valdrop['column_id'];
			 $data['dropdownData'][$key]['name'] = $valdrop['name'];
			 $data['dropdownData'][$key]['option_id'] = $valdrop['id'];
			 $data['dropdownData'][$key]['dependent_column_id'] = $valdrop['dependent_column_id'];
		 }
		 }
		 /* debug( $data['dropdownData'] ); 
		EXIT;  */
        $data['main_content'] = 'fronthand/twitter/partner_profile/action_plan/dashboard/strategy_condition_display';
        $this->load->view('/includes/template_new', $data);

    }
	
    public function updateSource()
    { //update/add source destination relationship
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        $profile = $this->Action_plan_model->getModule($clientid, $_POST['profile_id']);
        if (!empty(implode(',', $this->input->post('source_col')))) {
            $this->Action_plan_model->updateSource($_POST, $profile);
        }
        redirect('dashboard/actionplan/cp_source/' . $_POST['profile_id']);
    }
	
	 public function updateSingletier()
    { //update/add source destination relationship
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        $profile = $this->Action_plan_model->getModule($clientid, $_POST['profile_id']);
        if (!empty(implode(',', $this->input->post('source_col')))) {
            $this->Action_plan_model->updateSingletier($_POST, $profile);
        }
        redirect('dashboard/actionplan/conditional_display/' . $_POST['profile_id']);
    }
	
	 public function update_SingleSectionOrder()
    { //update/add order section AND number
        $this->load->model('Action_plan_model');
        //$data = $this->input->post('data');
		$dataval['singletier_id'] = $this->input->post('singletier_id');
		$dataval['singletier'] = $this->input->post('singletier');
		$dataval['optiondata'] = $this->input->post('optiondata');
		$dataval['cond_id'] = $this->input->post('conditional_id');
		$dataval['create_your_own'] = $this->input->post('create_your_own');
		
        $profile_id = $this->input->post('profile_id');
        $clientid = $this->session->userdata('client_id');
        $profile = $this->Action_plan_model->getModule($clientid, $profile_id);
        $this->Action_plan_model->update_SingleSectionOrder($dataval, $profile);
    }

    public function updateSectionOrder()
    { //update/add order section AND number
        $this->load->model('Action_plan_model');
        $data = $this->input->post('data');
        $profile_id = $this->input->post('profile_id');
        $clientid = $this->session->userdata('client_id');
        $profile = $this->Action_plan_model->getModule($clientid, $profile_id);
        $this->Action_plan_model->updateSectionOrder($data, $profile);
    }
    public function updateWidth()
    { //update/add width for colums
        $this->load->model('Action_plan_model');
        $data = $this->input->post('data');
        $profile_id = $this->input->post('profile_id');
        $clientid = $this->session->userdata('client_id');
        $profile = $this->Action_plan_model->getModule($clientid, $profile_id);
        $this->Action_plan_model->updateWidth($data, $profile);
    }
    public function saveActionPlan($type = null)
    { // save/update prepub/strategy columns from step 1
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        if ($type == 8) {
            $tabl = 'tbl_strategy_goal_colums';
            $prim = 'strategy_goal_colum_id';
        } else {
            $tabl = 'tbl_pre_publish_columns';
            $prim = 'tbl_pre_publish_column_id';
        }
        if ($_POST) {
            $profile_id = $this->input->post('profile_id');
            $ids = $this->input->post('id');
            $colm_dropdown = $this->input->post('colm_dropdown');

            $oldids = array_filter($ids);
            $drops = array_filter($colm_dropdown);
            if (!empty($oldids) && !empty($drops)) {
                $oldids = implode(',', $oldids);
                $sql = "Select group_concat(id order by id asc SEPARATOR '<>,') as id, column_id FROM tbl_plan_dropdown WHERE client_id=$clientid AND partner_profile_id=$profile_id AND column_id IN ($oldids) group by column_id";
                $temp = $this->Common_model->get_conditional_array($sql);
                $oldDrop = $this->Common_model->keyValuepair($temp, 'id', 'column_id');
            }

            $column_width = $this->input->post('column_width');
            $column_type = $this->input->post('column_type');
            $default_order = $this->input->post('default_order');
            $desc_as_text = $this->input->post('desc_as_text');
            $section_number = $this->input->post('section_number');
            $source_id = $this->input->post('source_id');
            $column_name = $this->input->post('column_name');
            $data_type = $this->input->post('data_type');
            $create_own = $this->input->post('create_own');
            $tool_tip_act = $this->input->post('tool_tip_act');
            $tool_tip_val = $this->input->post('tool_tip_val');
            $show_hide = $this->input->post('show_hide');
            $module_title_id = $this->input->post('module_title_id');
            $module_title = $this->input->post('module_title');
            $prepublish_level = $this->input->post('prepublish_level');
            $multi_select = $this->input->post('multi_select');
            $condition_display = $this->input->post('condition_display');
            // $is_required = $this->input->post('is_required');

            if (!empty($profile_id)) {
                $data = $this->Action_plan_model->getModule($clientid, $profile_id);
            } else {
                $step_order = $this->Common_model->get_data_row('step_order', 'tbl_partner_profile', array('client_id' => $clientid), 'step_order', 'DESC')['step_order'];
                if (!empty($step_order)) {
                    $step_order = $step_order + 1;
                } else { $step_order = 1;}
                $arr = array();
                $arr['client_id'] = $clientid;
                $arr['step_order'] = $step_order;
                $arr['field_width'] = 'full';
                $arr['profile_type'] = $type;
                $profile_id = $this->Common_model->insert_data('tbl_partner_profile', $arr);
            }
            if ($type == 8) {
                $this->Action_plan_model->removeRelatedPrePub($clientid, $profile_id);
                if (!empty($prepublish_level)) {
                    $this->Action_plan_model->addPreStrRelation($clientid, $profile_id, $prepublish_level);
                }
            }
            $this->Action_plan_model->saveUpdateTitle($clientid, $module_title_id, $module_title, $profile_id);

            //save Colums
            $updateArry = array();
            $insertIDArry = array();
            for ($i = 0; $i < count($column_name); $i++) {
                if (!empty($column_name[$i])) {
                    $saveArry['source_id'] = $source_id[$i];
                    $saveArry[$prim] = $ids[$i];
                    $saveArry['column_name'] = $column_name[$i];
                    $saveArry['column_width'] = $column_width[$i];
                    $saveArry['column_type'] = $column_type[$i];
                    $saveArry['data_type'] = $data_type[$i];
                    $saveArry['default_order'] = $default_order[$i];
                    $saveArry['tool_tip_act'] = $tool_tip_act[$i];
                    $saveArry['multi_select'] = $multi_select[$i];

					if ($condition_display[$i] != '') {						
					if($condition_display[$i]==0){
							$saveArry['single_tier_id'] = 0;
					}
                    $saveArry['condition_display'] = $condition_display[$i];					
					}
                    $saveArry['tool_tip_val'] = $tool_tip_val[$i];
                    $saveArry['show_hide'] = $show_hide[$i];
                    $saveArry['desc_as_text'] = $desc_as_text[$i];
                    $saveArry['section_number'] = $section_number[$i];
                    $saveArry['colum_order'] = $i;
                    $saveArry['create_own'] = $create_own[$i];
                    // $saveArry['is_required'] = $is_required[$i] ?  $is_required[$i] :0;
					
                    if (!empty($ids[$i])) {
						if($condition_display[$i]==1){
							unset($saveArry['single_tier_id']);
					}
                        $updateArry[] = $saveArry;
                        $insertIDArry[] = $ids[$i];
                    } else {
                        $saveArry['partner_profile_id'] = $profile_id;
                        $saveArry['client_id'] = $clientid;
                        $saveArry['type'] = 1;
						if($condition_display[$i]==1){
							unset($saveArry['single_tier_id']);
					}
                        $lastID = $this->Common_model->insert_data($tabl, $saveArry);
                        $insertIDArry[] = $lastID;
                    }
                }
            }
			
            if (!empty($updateArry)) {
                $this->db->update_batch($tabl, $updateArry, $prim);
            }

            if (!empty($drops)) {
                $this->Action_plan_model->saveDropDownValues($clientid, $profile_id, $drops, $insertIDArry, $oldDrop, $type);
            }
			if ($type == 8) {
				 redirect('dashboard/actionplan/strategy/'. $profile_id);
			} else {
				 redirect('dashboard/actionplan/prepublished/'. $profile_id);
			}
			// redirect('dashboard/actionplan/creation_strategy/'. $profile_id);
        }
    }
    
	
	
public function action_plan_strategy()
    { //get prepub/strategy goals's and descrioption if any
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        $partner_profile_id = $this->uri->segment(4);
        $data = $this->commmonData($partner_profile_id);
		
		if($data['tbl_strategy_goals_profile_Id'] !='' && $data['strtgy_dropdown_ids'] !=''){
			$sql= "SELECT * from tbl_plan_description_options WHERE goal_strategy_id IN(".implode(',',$data['tbl_strategy_goals_profile_Id']).") AND partner_profile_id  = ".$data['partner_profile']['partner_profile_id']." AND tbl_plan_description_id IN(".implode(',',$data['strtgy_dropdown_ids']).") AND client_id=$clientid AND year=0 AND partner_id=0";
			$oldData = $this->Common_model->get_conditional_array($sql);
			$data['prev_desc_option'] = array();
			$data['prev_create_own'] = array();
			foreach($oldData as $tmp){
				$data['prev_desc_option'][$tmp['goal_strategy_id']][$tmp['tbl_plan_description_id']][$tmp['id']]=$tmp['name'];
				
				$data['prev_desc_shortname'][$tmp['goal_strategy_id']][$tmp['tbl_plan_description_id']][$tmp['id']]=$tmp['short_name'];
				
				$data['prev_desc_mbusiness_tactic_id'][$tmp['goal_strategy_id']][$tmp['tbl_plan_description_id']][$tmp['id']]=$tmp['mbusiness_tactic_id'];
				
				$data['prev_create_own'][$tmp['goal_strategy_id']][$tmp['tbl_plan_description_id']]=$tmp['create_own'];
			}
		}
		
		
		// Bussiness action plan updated view
		
		$tactic_type = "Select mbusiness_tactic_id,tactic_name from tbl_mbusiness_tactics where client_id = $clientid";
        $data['tactic_type'] = $this->Common_model->get_conditional_array($tactic_type);
		
	
		$data['main_content'] = 'fronthand/twitter/partner_profile/action_plan/dashboard/action_plan_strategy';
        $this->load->view('/includes/template_new', $data);
    }
	
   public function updateStrategyGoal() { 
		//save/update prepub/strategy goals's and descrioption if any
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        if ($_POST) {
            $profile_id = $this->input->post('profile_id');
            $data = $this->Action_plan_model->getModule($clientid, $profile_id);
            $pre_publish_title = $this->input->post('pre_publish_title');
            $goal_group = $this->input->post('goal_group');
            $goal_short_group = $this->input->post('goal_short_group');
            $strategy_description = $this->input->post('strategy_description');
            $recommended_goals = $this->input->post('recommended_goals');
			if(!empty($this->input->post('create_own_val'))){
				$create_own_val = $this->input->post('create_own_val');
			}else{
				$create_own_val = 0;
			}
            // if (!empty($pre_publish_title)) {
                if ($data['partner_profile']['profile_type'] == 8) {
                    $this->Action_plan_model->saveStrtgyTitle($clientid, $profile_id, $pre_publish_title, $create_own_val, $strategy_description);
					redirect('dashboard/actionplan/creation_strategy/' . $profile_id);
                } else {
					
					// $this->Action_plan_model->savePreTitle($clientid, $profile_id, $pre_publish_title, $create_own_val, $strategy_description);
                    $this->Action_plan_model->savePreTitle($clientid, $profile_id, $goal_group, $create_own_val, $recommended_goals,$goal_short_group);
					redirect('dashboard/actionplan/manage_goalgrouping/' . $profile_id);
					 
                }
            // }
          // redirect('dashboard/actionplan/manage_goalgrouping/' . $profile_id);
        }
		
    }

    public function removeClone()
    { //remove source destination relationship
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        if ($_POST) {
            $this->load->model('Action_plan_model');
            $data = $this->input->post('data');
            $profile_id = $this->input->post('profile_id');
            $this->Action_plan_model->removeClone($data, $profile_id);
        }
    }
	public function remove_clone_singletier()
    { //remove source destination relationship
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        if ($_POST) {
            $this->load->model('Action_plan_model');
            $data = $this->input->post('data');
            $profile_id = $this->input->post('profile_id');
            $this->Action_plan_model->remove_clone_singletier($data, $profile_id);
        }
    }
	function mdftab(){
		if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        $partner_profile_id = $this->uri->segment(4);
		if ($partner_profile_id) { //edit
            $data = $this->Action_plan_model->getModule($clientid, $partner_profile_id);
		}
		$data['main_content'] = 'fronthand/twitter/partner_profile/action_plan/dashboard/mdftab';
        $this->load->view('/includes/template_new', $data);
	}
	function updateMDF(){
		$this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        if ($_POST) {
            $data = $this->input->post('data');
			return  $this->Action_plan_model->updateMDFData($clientid, $data);
		}
	}
	
    //**************NEW ACTION PLAN CODE END**************************//
	public function goal_list(){
		if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
		// debug($clientid,1);
		 $this->load->model('Managegoals_model');
		 $this->load->model('Common_model');
		 $clientid = $this->session->userdata('client_id');	
		 
		 $goals_quer = $this->Managegoals_model->getdataall($clientid);
		 $goal_id=$this->input->post('id');
		 $data['goals'] = $goals_quer;
		 

		 // $data['goals_data'] = $this->Managegoals_model->getdata($clientid,$goal_id);
		 $data['goals_data'] = $this->Common_model->get_data_array('*','tbl_manage_goals' ,array('client_id' => $clientid, 'manage_goals_id'=>$goal_id));
		 $data['main_content'] = 'admin/marketing_calculator/marketing_goals/mrkt_goals';
		 $this->load->view('/includes/template_new', $data);
	
	}
	function addedit_mrkt_goal(){
		 $goal_id=$this->uri->segment(3);
		 $clientid = $this->session->userdata('client_id');	
		$data['goals_data'] = $this->Common_model->get_data_array('*','tbl_manage_goals' ,array('client_id' => $clientid, 'manage_goals_id'=>$goal_id));
	     $data['main_content'] = 'admin/marketing_calculator/marketing_goals/add_edit_popup';
		 $this->load->view('/includes/template_new', $data);
	}
	function iframes(){
	    $this->load->model('Dashboard_model');
		$data['iframes'] = $this->Dashboard_model->display_enabled_module();
		$data['main_content'] = 'iframe_module/iframeList';
        $this->load->view('/includes/template_new', $data);
	}
	
	
	function manage_app_style($from=null){
	;
		$this->Common_model->check_dashboard_login();
		$client_id = $this->session->userdata('client_id');
		$this->load->model('Dashboard_model');
		if(empty($from)){
			$partner_id = 0;
		}else{
			$partner_id = $this->session->userdata('partner_id');
		}
		//$this->Dashboard_model->add_default_theme($client_id,$partner_id);
		$data['theme'] = $this->Common_model->get_data_array('*','tbl_frontend_theme',array('client_id'=>$client_id));
		
		$data['frontendthemeid'] = $this->Common_model->get_data_row('frontend_theme_id', 'tbl_frontend_theme', array('client_id'=>$client_id, 'status' => '1'));
		
		$data['theme_id'] = $data['frontendthemeid']['frontend_theme_id'];
		$theme_id         = $data['frontendthemeid']['frontend_theme_id'];
		
		$data['get_posted_theme'] = $this->Common_model->get_data_array('*','tbl_frontend_theme',array('client_id'=>$client_id,'status'=>'1','frontend_theme_id'=>$theme_id));
		$data['theme_data'] = $data['get_posted_theme'][0];
		
		$theme_options = $this->Common_model->get_data_array('*','tbl_frontend_theme_option',array('frontend_theme_id'=>$theme_id));	
		$data['theme_options'] = $this->Common_model->keyValuepair($theme_options,'value','theme_key');
	
		$data['font_family'] = $this->Common_model->get_font_family();
		$data['viewFrom'] = $from;
		$data['main_content'] = 'theme_settings/admin/theme';
		$this->load->view('/includes/template_action_plan_dashboard', $data);
		
		
	
	}
	
	function save_theme_data(){
		if($_POST['theme_title']){
			$client_id = $this->session->userdata('client_id');
			$this->Common_model->update_data('tbl_frontend_theme',array('theme_title'=>$_POST['theme_title']),array('client_id'=>$client_id, 'frontend_theme_id'=>$_POST['theme_id']));
		}
		$this->load->model('Dashboard_model');
		$client_id = $this->session->userdata('client_id');
		$post_data = $_POST;
		$this->Dashboard_model->customize_theme($client_id,$post_data);
		$this->manage_app_style($from=null);
	}
	function reset_theme_data(){
		$this->load->model('Dashboard_model');
		$client_id = $this->session->userdata('client_id');
		$post_data = $_POST;
		$this->Dashboard_model->customize_theme($client_id,$post_data,'reset');
		$this->manage_app_style($from=null);
	}
	
	function activate_theme(){
		$this->load->model('Dashboard_model');
		$client_id = $this->session->userdata('client_id');
		$theme_id = $this->input->post('theme_id');
		$partner_id = $this->input->post('partner_id');
		$status = $this->input->post('stat');
		if($theme_id){
			$this->Common_model->update_data('tbl_frontend_theme',array('status'=>'0'),array('client_id'=>$client_id,'partner_id'=>$partner_id));
			$this->Common_model->update_data('tbl_frontend_theme',array('status'=>$status),array('frontend_theme_id'=>$theme_id));
			//$this->Dashboard_model->add_default_theme_options($client_id,$theme_id);
		}
		//$this->manage_app_style($partner_id);
	}

	public function commmonData($partner_profile_id){
		$data = array();
		$this->load->model('Action_plan_model');
        if ($partner_profile_id) { //edit
			$clientid = $this->session->userdata('client_id');
            $data = $this->Action_plan_model->getModule($clientid, $partner_profile_id);
            if ($data['partner_profile']['profile_type'] == 8) {
                $goalSt = $this->Action_plan_model->getStrategyGoal($clientid, $partner_profile_id);
                $goalDesc = $this->Action_plan_model->getStrategyGoalDesc($clientid, $partner_profile_id);
            } else {
                $goalSt = $this->Action_plan_model->getPreGoal($clientid, $partner_profile_id);
                $goalDesc = $this->Action_plan_model->getPreGoalDesc($clientid, $partner_profile_id);
            }
            if (empty($goalSt)) {$goalSt = array();}
            if (empty($goalDesc)) {$goalDesc = array();}
            $data = array_merge($data, $goalSt, $goalDesc);
            $data['data_type'] = $this->Common_model->get_data_row('data_type', $data['table'], array('client_id' => $clientid, 'partner_profile_id' => $partner_profile_id, 'default_order' => '2'))['data_type'];
        }
		return $data;
	}
	public function desc_option(){
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        $partner_profile_id = $this->uri->segment(4);
		$data = $this->commmonData($partner_profile_id);
		if(!empty($data['tbl_strategy_goals_profile_Id'])){
			$sql= "SELECT * from tbl_plan_description_options WHERE goal_strategy_id IN(".implode(',',$data['tbl_strategy_goals_profile_Id']).") AND partner_profile_id  = ".$data['partner_profile']['partner_profile_id']." AND tbl_plan_description_id IN(".implode(',',$data['strtgy_dropdown_ids']).") AND client_id=$clientid AND year=0 AND partner_id=0";
			$oldData = $this->Common_model->get_conditional_array($sql);
			$data['prev_desc_option'] = array();
			$data['prev_create_own'] = array();
			foreach($oldData as $tmp){
				$data['prev_desc_option'][$tmp['goal_strategy_id']][$tmp['tbl_plan_description_id']][$tmp['id']]=$tmp['name'];
				$data['prev_create_own'][$tmp['goal_strategy_id']][$tmp['tbl_plan_description_id']]=$tmp['create_own'];
			}
		}
        $data['main_content'] = 'fronthand/twitter/partner_profile/action_plan/dashboard/desc_option';
        $this->load->view('/includes/template_new', $data);
    }
	
	function updateDescOption(){
		$this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        // debug($_POST,1);
        if ($this->input->post()){
			$table = 'tbl_plan_description_options';
			$post = $this->input->post('data');
			$create_your_own = $this->input->post('create_your_own');
			$profile_id = $this->input->post('profile_id');
			$profile_Data = $this->Common_model->get_data_row('*', 'tbl_partner_profile', array('client_id' => $clientid, 'partner_profile_id' => $profile_id));
			$oldData = $this->Common_model->get_data_array('*', $table, array('client_id' => $clientid, 'partner_profile_id' => $profile_id,'year'=>0,'partner_id'=>0));
			$campare = array();
			foreach($oldData as $tmp){
				$campare[$tmp['goal_strategy_id']][$tmp['tbl_plan_description_id']][]=$tmp['id'];
			}
			$inserArry = array();
			$updateArry = array();
			//debug($create_your_own);
			
			foreach($post as $goal => $desArr){
				foreach($desArr as $desID => $descOpArr){
					
					foreach($descOpArr as $k => $descOption){
						if(empty($create_your_own[$goal][$desID][0])){
							$create_own = 0;
						}else{
							$create_own = $create_your_own[$goal][$desID][0];
						}
						$temp = array();
						$temp['client_id'] = $clientid;
						$temp['tbl_plan_description_id'] = $desID;
						$temp['module_type'] = $profile_Data['profile_type'];
						$temp['partner_profile_id'] = $profile_id;
						$temp['goal_strategy_id'] = $goal;
						$temp['name'] = $descOption;
						$temp['create_own'] = $create_own;
						$temp['partner_id'] = 0;
						$temp['year '] = 0;
						
						if(isset($campare[$temp['goal_strategy_id']][$temp['tbl_plan_description_id']][$k])){
							$id = $campare[$temp['goal_strategy_id']][$temp['tbl_plan_description_id']][$k];
							$temp['id'] = $id;
							$updateArry[] = $temp;
						}else{
							$inserArry[] = $temp;
						}
					}
				}
			}
			//debug($updateArry);
			//exit;
			if (!empty($updateArry)) {
				$this->db->update_batch($table, $updateArry, 'id');
			}
			if (!empty($inserArry)) {
				$this->db->insert_batch($table, $inserArry);
			}
			redirect('dashboard/actionplan/desc_option/'.$profile_id);
		}
	}
	function deleteDescOption(){
		
		if ($this->input->post()){
			$id = $this->input->post('id');
			$total_select_tactic = $this->input->post('total_select_tactic');
			$total_recomend_gaol = $this->input->post('total_recomend_gaol');
			
			$profile_Data = $this->Common_model->get_data_row('*', 'tbl_plan_description_options', array('id' => $id));
			
			$table = 'tbl_plan_description_options';
			$this->Common_model->delete_records($table , array('id' => $id));
			if($total_select_tactic==1){
			    $this->Common_model->delete_records('tbl_plan_description' , array('id' => $profile_Data['tbl_plan_description_id']));
			}
			if($total_select_tactic<='1' && $total_recomend_gaol<='1'){
			    $this->Common_model->delete_records('tbl_pre_publish_profile' , array('tbl_pre_publish_id' => $profile_Data['goal_strategy_id']));
			}
		}
	}
	
	function deleteDescOption_singletier(){
		if ($this->input->post()){
			$id = $this->input->post('id');
			$table = 'tbl_plan_dropdown';
			 $this->Common_model->delete_records($table , array('id' => $id));
			return $this->Common_model->delete_records($table , array('dependent_column_id' => $id));
		}
	}
	function plan_export(){
	
		$this->Common_model->check_dashboard_login();
		$clientid = $this->session->userdata('client_id');
		$this->load->model('Export_model');
		$this->load->model('Ppt_dynamic_template_model');
		$data['theme_arr'] = $this->Export_model->theme_name($clientid);
		$this->add_default_theme($clientid);
		$data['font_family'] = $this->Ppt_dynamic_template_model->get_font_family();	
		
		$data['selected_theme'] = $this->Common_model->get_data_row('template_setting_id,theme_id','tbl_template_setting',array());
	
		if($this->input->post('ppt_theme_id')){
			$ppt_theme_id = $this->input->post('ppt_theme_id');
			$data['theme_setting'] = $this->Export_model->get_client_theme_setting($clientid,$ppt_theme_id);
		}
		
		$clientname = $this->Common_model->get_data_row('client_uniquename','admin_clients',array('client_id'=>$clientid))['client_uniquename'];
		$data['navigation'] = $this->Ppt_dynamic_template_model->simple_nav_steps($clientname);
		$data['main_content'] = 'ppt_dynamic_template/simple_customize/exportdata_theme_setting';
        $this->load->view('/includes/template_new', $data);
	}
	function add_default_theme($client_id){
			
			$theme_arr = $this->Export_model->get_theme_setting();
			$presentation_theme_arr['client_id'] 	=  $client_id;
			$presentation_theme_arr['theme_name'] 	=  'Ash';
			$presentation_theme_arr['theme_image'] 	=  'ash.png';
			$presentation_theme_arr['theme_key'] 	=  '0';
			
			$theme_exists = $this->Common_model->get_data_row('*','tbl_presentation_theme',array('client_id'=>$client_id,'theme_key'=>'0'));
			if(!$theme_exists){
				$presentation_theme_id = $this->Common_model->insert_data('tbl_presentation_theme',$presentation_theme_arr);
				$counter = 0;
				foreach($theme_arr['header'] as $theme_key => $theme_val){
					$save_arr[$counter]['presentation_theme_id'] 	= $presentation_theme_id;
					$save_arr[$counter]['type'] 					= "header";
					$save_arr[$counter]['key'] 						= $theme_key;
					$save_arr[$counter]['value']					= $theme_val;
				$counter++;}
				
				foreach($theme_arr['body'] as $theme_key => $theme_val){
					$save_arr[$counter]['presentation_theme_id'] 	= $presentation_theme_id;
					$save_arr[$counter]['type'] 					= "body";
					$save_arr[$counter]['key'] 						= $theme_key;
					$save_arr[$counter]['value']					= $theme_val;
				$counter++;}
				
				foreach($theme_arr['footer'] as $theme_key => $theme_val){
					$save_arr[$counter]['presentation_theme_id'] 	= $presentation_theme_id;
					$save_arr[$counter]['type'] 					= "footer";
					$save_arr[$counter]['key'] 						= $theme_key;
					$save_arr[$counter]['value']					= $theme_val;
				$counter++;}
				
				$this->db->insert_batch('tbl_ppt_theme_option',$save_arr);
			}
		}

 public function set_loginpage()
    {   
	    $this->Common_model->check_dashboard_login();
        $client_id = $this->session->userdata('client_id');
        // fetch previoulsy saved data
        $data['login_page'] = $this->Common_model->get_data_row('*', 'manage_theme', array('clientid' => $client_id));
        $data['main_content'] = 'fronthand/twitter/login_page/set_loginpage';
        $this->load->view('/includes/template_new', $data);
    }
	 public function save_loginpage_setting()
    {
        // Get user inputs
        if ($this->input->post('submit')) {
            $client_id = $this->session->userdata('client_id');

            $data['clientid'] = $client_id;
            $data['client_logo'] = trim($this->input->post('client_logo'));
            $data['cam_text'] = trim($this->input->post('cam_text'));
            $data['page_intro'] = trim($this->input->post('page_intro'));

                $update = $this->Common_model->update_data('manage_theme', $data, array('clientid' => $client_id));
                $this->success_msg('Settings Updated Successfully!');
           
            redirect('dashboard/set_loginpage');
        }
    }
	public function cam_configuration_fields()
    {   
	   $this->Common_model->check_dashboard_login();
        $client_id = $this->session->userdata('client_id');
        // fetch previoulsy saved data
        $data['cam_conf_field'] = $this->Common_model->get_data_array('*', 'tbl_cam_configuration_fields', array());
		
        $data['main_content'] = 'fronthand/twitter/cam_configuration_fields/cam_configuration_fields';
        $this->load->view('/includes/template_new', $data);
    }
	 public function save_cam_configuration_fields()
    {
        // Get user inputs
        if ($this->input->post('submit')) {
            $client_id = $this->session->userdata('client_id');
			$cam_role   = trim($this->input->post('cam_role'));
			$default_name = $this->input->post('default_name');
			$field_name = $this->input->post('field_name');
			$is_access  = $this->input->post('is_access');
			
					foreach($default_name as $key => $fieldval){
						$temp = array();
						$temp['default_name'] = $fieldval;
						$temp['type'] = $cam_role;
						$temp['field_name'] = $field_name[$key];
						$temp['is_access'] = ($is_access[$key] ? $is_access[$key] : 0);
						
						//debug($temp); 
						$this->db->update('tbl_cam_configuration_fields', $temp, array('default_name' => $temp['default_name'],'type' => $temp['type']));
						//debug($this->db->last_query()); exit;
				}
			if($cam_role=='cam'){ $camrole = 'CAM Role'; }else if($cam_role=='director'){ $camrole = 'DIRECTOR Role'; }else{ $camrole = 'CAM DIRECTOR Role'; }
                $this->success_msg('Records are updated successfully for '.$camrole);
				$this->session->set_flashdata('role', $cam_role);
           
            redirect('dashboard/cam_configuration_fields');
        }
    }
	
	 public function application_setting() 
    {   
	    $this->Common_model->check_dashboard_login();
        $clientid = $this->session->userdata('client_id');
		$this->load->model('Manageproducts_model');
		$this->load->model('Managetheme_model');
	    $show_approveplans = $this->input->post('show_approveplan');
	    $show_approveplan  = $show_approveplans !="" ? '1':'0';
	  
	  if($this->input->post('submit') != ''){
		  $is_checked = $this->input->post('is_checked');
            $this->Common_model->update_data('client_pl_steps', array('is_checked' => $is_checked), array('clientid' => $clientid));
			
        $average_deal = $this->input->post('average_deal');
        $update = $this->Common_model->update_data('admin_clients', array('average_deal' => $average_deal,'show_approveplan' => $show_approveplan), array('client_id' => $clientid));		
		
		// 
		
        $product_column_title = $this->input->post('label_name');
		
		
		$data_revenue = $this->Common_model->get_data_row('*', 'tbl_manage_content', array('client_id' => $clientid,'content_key' => 'revenue'));
		
		if(!empty($data_revenue)){
		$update_lable = $this->Common_model->update_data('tbl_manage_content', array('title' => $product_column_title), array('client_id' => $clientid,'content_key' => 'revenue'));
		}else{
			$revenue_arr['client_id'] 	=  $clientid;
			$revenue_arr['content_key'] 	=  'revenue';
			$revenue_arr['title'] 	=  $product_column_title;
			$revenue_arr['status'] 	=  '1';
			
				$revenue_arr_id = $this->Common_model->insert_data('tbl_manage_content',$revenue_arr);
		}
		 // logo image 
			  if(!empty($_FILES['logo']['name']))
			  {
				$image_url = $this->do_upload();
			  }
			  else{
				$image_url = "";
			  }	
        $this->Managetheme_model->add_theme_settings($image_url,$clientid);
		
	  }
	  // is_checked data		 
		$data['is_checked'] = $this->Common_model->get_data_row('is_checked', 'client_pl_steps', array('clientid' => $clientid));		
		// average_deal data label_name		
		$data['average_deal'] = $this->Common_model->get_data_row('average_deal,show_approveplan', 'admin_clients', array('client_id' => $clientid));		
		// label data 		
        $data['product_column'] = $this->Common_model->get_data_row('*', 'tbl_manage_content', array('client_id' => $clientid, 'content_key' => 'revenue'));
	
		$theme_default = $this->Managetheme_model->get_theme_data($clientid);
		$data['theme_default'] = $theme_default[0];
		 
		 //debug($data); exit;
		 
        $data['main_content'] = 'fronthand/twitter/admin_conditions/application_setting';
        $this->load->view('/includes/template_new', $data);
    }



	function do_upload()
	{
		$config['upload_path'] = logo_path;
		$config['allowed_types'] = '*';
		$config['max_size']	= '10000';
		$config['max_width']  = '3024';
		$config['max_height']  = '2768';
		$config['file_name']  = uniqid().".png";

		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload('logo'))
		{
			$error = array('error' => $this->upload->display_errors());
            print_r($error);
            die();
           $image_url = "";
		}
		else
		{
			$data = array('upload_data' => $this->upload->data());
            $image_path =  $data['upload_data']['file_name'];
            $image_url = $this->config->item('base_url1').'/uploads/'.$image_path;
           // echo "<img src='".$image_url."' />";
		 //	$this->load->view('upload_success', $data);
		}
		
        return  $image_url;
	}
	
	
   
     public function accreditation_export_data($startDate = null, $endDate = null, $SFonly = null, $searchType = null, $errorOnly = null)
    {
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $client_id = $this->session->userdata('client_id');
        $client_name = $this->Common_model->get_data_row('client_uniquename', 'admin_clients', array('client_id' => $client_id));
        $this->load->model('Sfpartners_model');
        if ($client_id == '') {
            redirect('manageaudit');
        }
        $this->load->model('Cam_model');
        if ($errorOnly) {
            $fileString = '_Error';
            $data = $this->Cam_model->get_accreditation_error_details($client_id, $startDate, $endDate, $SFonly, $searchType);
        } else {
            $fileString = '';
            $data = $this->Cam_model->get_accreditation_details($client_id, $startDate, $endDate, $SFonly, $searchType);
        }
       
        $file_name = '' . $client_name['client_uniquename'] . '_Accreditation' . $fileString . '_data.xlsx';
        $filename = logo_path . 'clientdata/' . $file_name;
        $this->load->library('PHPExcel/Classes/PHPExcel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

		 $first_row = array(
            'Certificate name',
            'Certificate ID',
            'Contact ID',
            'Partner ID',
            'Accreditation Date',
            'Accreditation Expiration Date',
            'Insert Date',
            'Modify On',
        );
     
        $column1 = 'G';
        if ($errorOnly) {
            array_unshift($first_row, 'Reason');
			$column1 = 'H';
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        ); 
		$stylecolumn = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ),
        ); 
		
		
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray($style);
		
        $column = 'A';
        // $column1 = 'G';
        $rowCount = 1;
        for ($i = 0; $i < count($first_row); ++$i) {
            $objPHPExcel->getActiveSheet()->setCellValue($column . $rowCount, $first_row[$i]);
            $objPHPExcel->getActiveSheet()->getStyle($column . $rowCount)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getStyle($column . $rowCount)->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'FFFF00'),
                    ),
                )
            );  
			$objPHPExcel->getActiveSheet()->getStyle($column)->applyFromArray($stylecolumn);
            ++$column;
        }
        $rowCount = 2;
        for ($i = 0; $i < count($data); ++$i) {
            $column = 'A';
            foreach ($data[$i] as $key => $value) {
                $objPHPExcel->getActiveSheet()->setCellValue($column . $rowCount, $value);
                $objPHPExcel->getActiveSheet()->getStyle($column1 . $rowCount)->getNumberFormat()->setFormatCode('\$\ #,##0.00');
                if ($value == 'ERROR') {
                    $objPHPExcel->getActiveSheet()->getStyle($column . $rowCount)->applyFromArray(
                        array(
                            'font' => array(
                                'color' => array('rgb' => 'FF0000'),
                            ),
                        )
                    );
					$objPHPExcel->getActiveSheet()->getStyle($column)->applyFromArray($stylecolumn);
                }
                if ($errorOnly) {
                    if ($key == 'reason') {
                        $objPHPExcel->getActiveSheet()->getStyle($column . $rowCount)->applyFromArray(
                            array(
                                'font' => array(
                                    'color' => array('rgb' => 'FF0000'),
                                ),
                            )
                        );
						$objPHPExcel->getActiveSheet()->getStyle($column)->applyFromArray($stylecolumn);
                    }
                }
                ++$column;
            }
            ++$rowCount;
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($filename);
        $redirect_link = $this->config->item('base_url1') . '/uploads/clientdata/' . $file_name;
        redirect($redirect_link);
    }


   // manage all custom field
   function manage_custom_field(){
      $this->Common_model->check_dashboard_login();
       $this->load->model('Dashboard_model');
	   $custom_field_arr = array();
	   $data['segment'] = $this->uri->segment(3);
	   $data['tabbing'] = array("cam"=>"Cam","partner"=>"Partner","contact"=>"Contact");
	   if($data['segment'] =="cam"){ $field_data = 1; }
	   if($data['segment'] =="partner"){ $field_data = 2; }
	   if($data['segment'] =="contact"){ $field_data = 3; }
	   
	   
	   
	    // get edit fields popup
	    $get_update_id = $this->input->post('get_update_id'); 
	    if($get_update_id !=""){
		$data['popup_custom_field'] = $this->Common_model->get_data_row('*', 'tbl_custom_field', array('custom_field_id' => $get_update_id));
		$data['popup_drop_field'] = $this->Common_model->get_data_array('*', 'tbl_custom_field_dropdown', array('custom_field_id' => $get_update_id));
		
		$data['all_drop_ids'] = $this->Common_model->keyValuepair($data['popup_drop_field'],'custom_field_dropdown_id','custom_field_dropdown_id');
		}
		
		 // delete custom field 
	   $delete_id = $this->input->post('del_id');
	   if($delete_id !=""){
	   $this->db->where('custom_field_id', $delete_id);
       $this->db->delete('tbl_custom_field');
	}
	   // update required status  
	   $update_id = $this->input->post('update_id');
	   if($update_id !=""){
	   $update_data['is_required']  = $this->input->post('status');
	   $this->Common_model->update_data('tbl_custom_field', $update_data, array('custom_field_id' => $update_id));
	   }
	    // insert custom field
	    if($this->input->post('field_name') !=""){
		$this->Dashboard_model->insert_cutom_field($_POST,$field_data);
		}
		
		
	   $data['get_field'] = $this->Common_model->get_field_type()['full_name'];
	   $data['custom_field'] = $this->Common_model->get_data_array('*', 'tbl_custom_field', array("custom_field_type"=>$field_data),'field_order','field_order');
	   
	   $c_drop_data = $this->Dashboard_model->get_custom_option();
	   $data['all_dropdown'] = $c_drop_data['all_dropdown'];
	   $data['main_content'] = 'custom_field/dashboard/custom_field';
       $this->load->view('/includes/template_new', $data);
	   }
   
     function custom_drag_drop(){
		 $this->Common_model->check_dashboard_login();
	 $custom_id=$this->input->post('custom_id');
	 $custom_order_array=$this->input->post('custom_order_array');
	 	$increement = 0;
	 	foreach($custom_id as $custom_ids)
			{
			$this->db->query("update tbl_custom_field set field_order=$custom_order_array[$increement] where custom_field_id = $custom_ids");
				$increement++;
			}
		
	 }
   
   
	public function action_plan_prepublish()
    { //get prepub/strategy goals's and descrioption if any
	$this->Common_model->check_dashboard_login();
        if (!$this->session->userdata('dealer_login')) {
            redirect('manageaudit');
        }
        $this->load->model('Action_plan_model');
        $clientid = $this->session->userdata('client_id');
        $partner_profile_id = $this->uri->segment(4);
        $data = $this->commmonData($partner_profile_id);
		
		if($data['tbl_strategy_goals_profile_Id'] !='' && $data['strtgy_dropdown_ids'] !=''){
			$sql= "SELECT * from tbl_plan_description_options WHERE goal_strategy_id IN(".implode(',',$data['tbl_strategy_goals_profile_Id']).") AND partner_profile_id  = ".$data['partner_profile']['partner_profile_id']." AND tbl_plan_description_id IN(".implode(',',$data['strtgy_dropdown_ids']).") AND client_id=$clientid AND year=0 AND partner_id=0";
			$oldData = $this->Common_model->get_conditional_array($sql);
			$data['prev_desc_option'] = array();
			$data['prev_create_own'] = array();
			
			foreach($oldData as $tmp){
				$data['prev_desc_option'][$tmp['goal_strategy_id']][$tmp['tbl_plan_description_id']][$tmp['id']]=$tmp['name'];
				
				$data['prev_desc_shortname'][$tmp['goal_strategy_id']][$tmp['tbl_plan_description_id']][$tmp['id']]=$tmp['short_name'];
				
				$data['prev_desc_mbusiness_tactic_id'][$tmp['goal_strategy_id']][$tmp['tbl_plan_description_id']][$tmp['id']]=$tmp['mbusiness_tactic_id'];
				
				$data['prev_create_own']=$tmp['create_own'];
			}
			
		}
		
		
		// Bussiness action plan updated view
		
		$tactic_type = "Select mbusiness_tactic_id,tactic_name from tbl_mbusiness_tactics where client_id = $clientid AND is_default='1'";
        $data['tactic_type'] = $this->Common_model->get_conditional_array($tactic_type);
		
		$tactic_type_data = $this->Action_plan_model->get_admin_tactic_type($clientid);
			
			
		foreach($tactic_type_data as $val){
			$data['cat_name'][$val['mbusiness_category_id']] = $val['category_name'];
			$data['act_name'][$val['mbusiness_category_id']][$val['mbusiness_activity_id']] = $val['activity_name'];
			$data['tact_name'][$val['mbusiness_category_id']][$val['mbusiness_activity_id']][$val['mbusiness_tactic_id']] = $val['tactic_name'];
			$data['camp_name'][$val['mbusiness_category_id']][$val['mbusiness_activity_id']][$val['mbusiness_tactic_id']][$val['id']] = $val['campaign_name'];
		} 
	
		$data['main_content'] = 'fronthand/twitter/partner_profile/action_plan/dashboard/manage_goalgrouping';
        $this->load->view('/includes/template_new', $data);
    }
    
	
	// Start Activity Grouping, Activity and Tactic Type Admin Control
	
   function manage_activity_type($data=null){
	   $this->Common_model->check_dashboard_login();
       $this->load->model('Dashboard_model');
       $this->load->model('Mbusinessplan_model');
	   $client_id     = $this->session->userdata('client_id');
	   
	   $tactic_id   = $this->input->post('tactic_id'); 
	   $goal_id   = $this->input->post('goal_id'); 
	   if($tactic_id!=''){
		   $data['tactic_id']   = $tactic_id;
		 }
		 
		 if($goal_id!=''){
		   $data['goal_id']   = $goal_id;
		 }
		 
	   $data['app_status'] = $this->Mbusinessplan_model->get_mbusiness_application_status($client_id);
	   $data['goal'] = $this->Common_model->get_data_array('*', 'tbl_mbusiness_goals', array('client_id' => $client_id));
	   $data['activity_grouping'] = $this->Mbusinessplan_model->manage_activity($client_id);
	   $data['goaldata']          = $this->Mbusinessplan_model->get_goal_data($client_id);
			
	   $data['activities_title']  = $this->Mbusinessplan_model->manage_activity($client_id);
	   $tactics_data              = $this->Mbusinessplan_model->manage_tactics($client_id);
	  
	  foreach($tactics_data as $tactval){
		 $data['manage_activity_title_data'][$tactval['mbusiness_tactic_id']] = $tactval; 
	  }
	  
	  $plandata = $this->Common_model->get_data_array('*','tbl_mbusiness_profile_plandata',array());
	  $data['roi_method_activate'] = $this->Common_model->get_data_row('roi_method_activate','tbl_mbusiness_application_setting',array());


	  $activity_roi_completion_data = $this->Common_model->get_data_array('*','tbl_mbusiness_coefficient_manager',array());
	  
	  foreach($activity_roi_completion_data as $completion_data){
		  if($completion_data['required_forecast']=='1'){
		  $data['activity_roi_sttaus'][$completion_data['mbusiness_tactic_id']] = $completion_data['mbusiness_tactic_id'];
		  }
		  $data['lead_level_status'][$completion_data['mbusiness_lead_level_id']] = $completion_data['status'];
		  if($completion_data['status']=='1'){
			$data['completion_LLS'][] = $completion_data['mbusiness_lead_level_id']; 
		  }
	  }
	  
	  $data['planactivity_type']       = $this->Common_model->keyValuepair($plandata, 'tactics_id','sub_plan_id');
      $data['plangoal_activity_title'] = $this->Common_model->keyValuepair($plandata, 'campaign_id','sub_plan_id');

	   $data['main_content'] = 'dashboard/Manage_Activities_and_Tactics/manage_activities_and_tactics';
       $this->load->view('/includes/template_new', $data);
	}
	
	public function save_custom_tactic_type(){
		$this->Common_model->check_dashboard_login();
			$client_id                = $this->session->userdata('client_id');
			$activity_id              = $this->input->post('activity_id');
			$goal_id                  = $this->input->post('goal_id');
			$tactic_name              = $this->input->post('tactic_name');
			$tactic_key               = $this->input->post('tactic_key');
			$tactic_hide_forms        = $this->input->post('tactic_hide_forms');
			$percentageDevotedClient  = $this->input->post('percentageDevotedClient');				
 		
			if(!empty($goal_id)){
				if($activity_id==''){ $activity_id = '1'; }
				if($tactic_key != ''){
					$activity_type_id = $this->Common_model->get_data_row('tactic_key','tbl_mbusiness_tactics',array('tactic_key' => $tactic_key));
					
					if(in_array($tactic_key, $activity_type_id)){
						$data['tactic_key'] = 'Activity Type ID already exists';
					}else{ $data['tactic_key'] = ''; }
				}
					
				if($data['tactic_key'] != ''){
				}else{
					$save_arr[$key]['mbusiness_activity_id']   = $activity_id;
					$save_arr[$key]['goal_id'] 		           = $goal_id;
					$save_arr[$key]['client_id']	           = $client_id;
					$save_arr[$key]['tactic_key']	           = $tactic_key;
					$save_arr[$key]['tactic_code']	           = $tactic_key;
					$save_arr[$key]['tactic_name']	           = $tactic_name;
					$save_arr[$key]['custom_tactic_code']	   = $tactic_key;
					$save_arr[$key]['custom_tactic_name']	   = $tactic_name;
					$save_arr[$key]['is_default']	           = '0';
					$save_arr[$key]['status']	               = '0';
					$save_arr[$key]['tactic_hide_forms']	   = $tactic_hide_forms;
					$save_arr[$key]['percentageDevotedClient'] = $percentageDevotedClient;				
				    $this->db->insert_batch('tbl_mbusiness_tactics',$save_arr);					
				}				
			}
			$this->manage_activity_type($data);			
		}
		
		public function update_custom_tactic_type() {
			$this->Common_model->check_dashboard_login();
			$client_id          = $this->session->userdata('client_id');
			$tactic_update_id   = $this->input->post('tactic_type_id');			
			$activity_id        = $this->input->post('activity_id');
			$goal_id            = $this->input->post('goal_id');
			$tactic_name        = $this->input->post('tactic_name');
			$tactic_key         = $this->input->post('tactic_key');
			$tactic_code        = $this->input->post('tactic_code');
			$status             = $this->input->post('status');
			$tactic_hide_forms  = $this->input->post('tactic_hide_forms');
			$percentageDevotedClient  = $this->input->post('percentageDevotedClient');			
			
			if($tactic_key != ''){
					$activity_type_id = $this->Common_model->get_data_row('tactic_key','tbl_mbusiness_tactics',array('tactic_key' => $tactic_key , 'mbusiness_tactic_id !=' => $tactic_update_id));
					
					if(in_array($tactic_key, $activity_type_id)){
						$data['tactic_key'] = 'Activity Type ID already exists';
					}else{ $data['tactic_key'] = ''; }
				}
				
			if($data['tactic_key'] != ''){				
			}else{
				if($tactic_update_id!='' && $activity_id==''){					
					$this->Common_model->update_data('tbl_mbusiness_tactics', array('goal_id' => $goal_id,'tactic_code' => $tactic_key,'tactic_key' => $tactic_key,'tactic_name' => $tactic_name,'custom_tactic_code' => $tactic_key,'custom_tactic_name' => $tactic_name,'percentageDevotedClient' => $percentageDevotedClient,'status' => $status,'tactic_hide_forms' => $tactic_hide_forms), array('mbusiness_tactic_id' => $tactic_update_id));
				}
				if($tactic_update_id!='' && $activity_id!=''){					
					$this->Common_model->update_data('tbl_mbusiness_tactics', array('mbusiness_activity_id' => $activity_id,'goal_id' => $goal_id,'tactic_code' => $tactic_key,'tactic_key' => $tactic_key,'tactic_name' => $tactic_name,'custom_tactic_code' => $tactic_key,'custom_tactic_name' => $tactic_name,'percentageDevotedClient' => $percentageDevotedClient,'status' => $status,'tactic_hide_forms' => $tactic_hide_forms), array('mbusiness_tactic_id' => $tactic_update_id));					
				}
				
				if($tactic_update_id!=''){	
					$this->Common_model->update_data('tbl_mbusiness_goals_data', array('goal_id' => $goal_id), array('tactics_id' => $tactic_update_id));
				}
				
			}
			$this->manage_activity_type($data);
		}
	
	public function set_lead_waterfall_conversion_coef(){
		$this->Common_model->check_dashboard_login();
		$client_id = $this->session->userdata('client_id');
		$tactic_id = $this->input->post('tactic_id');
		$this->load->model('Mbusinessplan_model');
		
		if($tactic_id != ''){
		$data['lead_level'] =  $this->Common_model->get_conditional_array("select * from tbl_mbusiness_lead_level order by metric_level asc");	       
		   
	   // Get lead level and coefficient 
		$sql = "select t1.mbusiness_lead_level_id,t1.metric_level,t1.metric_name,t2.* from tbl_mbusiness_lead_level as t1 inner join tbl_mbusiness_coefficient_manager as t2 on t1.mbusiness_lead_level_id=t2.mbusiness_lead_level_id order by t1.metric_level,t1.mbusiness_lead_level_id ";
		$coefficientdata =  $this->Common_model->get_conditional_array($sql);
		foreach($coefficientdata as $val){
			if($val['status']=='1'){
			$data['selected_lead_level_tactic_data'][$val['metric_level']][$val['mbusiness_lead_level_id']][] = $val;
			$data['selectedtactic'][$val['mbusiness_tactic_id']][$val['mbusiness_lead_level_id']] = $val;
			}
			$data['selected_tactic_data'][$val['mbusiness_tactic_id']][$val['mbusiness_lead_level_id']] = $val;
		}
	}	
	
	$this->manage_activity_type($data);
		
	}
		  
		function save_lead_level(){
			  $this->Common_model->check_dashboard_login();
			  $mbusiness_lead_level_id  = $this->input->post('mbusiness_lead_level_id');
			  $metric_name              = $this->input->post('metric_name');
			  $metric_description       = $this->input->post('metric_description');
			  $metric_level             = $this->input->post('metric_level');
			  $tactic_status            = $this->input->post('tactic_status');
			  $tactic_id                = $this->input->post('tactic_id');
			  $client_id                = $this->session->userdata('client_id');
			  
		  $vals = array_count_values($metric_level);
		  		  
		  if(is_array($metric_name) and count($metric_name)>0){
			  
			foreach($metric_name as $key=>$metric_val) {
			if($metric_val !=""){
			$coefficient_update = array();
			$coefficient_insert = array();
			if($mbusiness_lead_level_id[$key] !=""){
			$update_arr['mbusiness_lead_level_id']   = $mbusiness_lead_level_id[$key];
			$update_arr['metric_name']         = $metric_val;
			$update_arr['metric_level']        = $metric_level[$key];
			$this->Common_model->update_data('tbl_mbusiness_lead_level',$update_arr, array('mbusiness_lead_level_id' =>$mbusiness_lead_level_id[$key]));
			$last_id =   $mbusiness_lead_level_id[$key];
			} else {
			$insert_arr['metric_name']         = $metric_val;
			$insert_arr['metric_level']        = $metric_level[$key];
			$this->Common_model->insert_data('tbl_mbusiness_lead_level', $insert_arr);
			$last_id =   $this->db->insert_id();
			}	
			
			$check_cofeicient =  $this->Common_model->get_data_row('mbusiness_coefficient_manager_id,mbusiness_tactic_id,mbusiness_lead_level_id','tbl_mbusiness_coefficient_manager', array('mbusiness_tactic_id' => $tactic_id, 'mbusiness_lead_level_id' => $last_id));
			
			if($tactic_status[$key]==''){ 
			if($vals[$metric_level[$key]]==1){ $status ='1'; }else{ $status ='0'; } }else{ 
			$status = $tactic_status[$key]; }
			
			if($check_cofeicient['mbusiness_coefficient_manager_id'] !=""){
			  $coefficient_update['status']              = $status;
			  $coefficient_update['metric_description']  = $metric_description[$key];
			  if($status =='0'){ $coefficient_update['required_forecast'] = '0'; }
			  
			  $this->Common_model->update_data('tbl_mbusiness_coefficient_manager',$coefficient_update, array('mbusiness_coefficient_manager_id' =>$check_cofeicient['mbusiness_coefficient_manager_id']));
			}else {			
				$coefficient_insert['mbusiness_tactic_id']      = $tactic_id;
				$coefficient_insert['mbusiness_lead_level_id']  = $last_id;
				$coefficient_insert['client_id']                = $client_id;
				$coefficient_insert['status']                   = $status;
				$coefficient_insert['required_forecast']        = '0';
				$coefficient_insert['lead_wtrfall_convr']       =  0;
				$coefficient_insert['roi_calc_level']           = '0';
				$coefficient_insert['metric_description']       = $metric_description[$key];
				$this->Common_model->insert_data('tbl_mbusiness_coefficient_manager', $coefficient_insert);		
			}
		      }
			
			$key++; } 
		  }
		  $this->set_lead_waterfall_conversion_coef();
		}
		  
			public function save_tactic_type_Coefficients(){
				$this->Common_model->check_dashboard_login();
			$client_id = $this->session->userdata('client_id');
			$mbusiness_tactic_id      = $this->input->post('mbusiness_tactic_id');
			$mbusiness_lead_level_id  = $this->input->post('mbusiness_lead_level_id');
			$lead_wtrfall_convr       = $this->input->post('lead_wtrfall_convr');
			$required_forecast        = $this->input->post('required_forecast');
			$roi_calc_level           = $this->input->post('roi_calc_level');
		
			if(is_array($mbusiness_lead_level_id) and count($mbusiness_lead_level_id)>0){
			foreach($mbusiness_lead_level_id as $key=> $lead_level_id){
			if($lead_wtrfall_convr[$lead_level_id] !=""){
			$update_coff['lead_wtrfall_convr']	    = $lead_wtrfall_convr[$lead_level_id];
			$update_coff['required_forecast']       = $required_forecast[$lead_level_id] !="" ? '1' : '0';
			if(!empty($roi_calc_level)){
			$update_coff['roi_calc_level']          = $roi_calc_level[$lead_level_id] !="" ? '1' : '0';
			}else{
				if($key==(count($mbusiness_lead_level_id)-1)){ $update_coff['roi_calc_level'] = '1'; }else{ $update_coff['roi_calc_level'] = '0'; }
			}
			
			$this->Common_model->update_data('tbl_mbusiness_coefficient_manager',$update_coff, array('mbusiness_lead_level_id' =>$lead_level_id,'mbusiness_tactic_id' =>$mbusiness_tactic_id));
			
			}
			 	
			}	
				
			}
			
			
		}
		
		public function change_tactic_staus()
		{
			$this->Common_model->check_dashboard_login();
			$client_id      = $this->session->userdata('client_id');
			$tactic_id      = $this->input->post('tactic_id');
			$tactic_status  = $this->input->post('tactic_status');
						
				
				if($tactic_id!=''){	
					$this->Common_model->update_data('tbl_mbusiness_tactics',array('is_default'=>$tactic_status), array('mbusiness_tactic_id' => $tactic_id));
				}
			
			redirect('dashboard/manage_activities_and_tactics');
		}
		
		public function save_activity_hierarchy() 
		{
			$this->Common_model->check_dashboard_login();
			$client_id           = $this->session->userdata('client_id');
			$cat_heading_title   = $this->input->post('custom_cat_heading_title');
			//$is_activate         = $this->input->post('is_activate');
			$act_heading_title   = $this->input->post('custom_act_heading_title');
			$tact_heading_title  = $this->input->post('custom_tact_heading_title');
			$tact_title_heading_title  = $this->input->post('custom_tact_title_heading_title');
			
			
					$this->Common_model->update_data('tbl_mbusiness_application_status',array('cat_heading_title'=>$cat_heading_title,'act_heading_title'=>$act_heading_title,'tactic_heading_title'=>$tact_heading_title,'tactic_title_heading_title'=>$tact_title_heading_title) , array('client_id' => $client_id));
				
			redirect('dashboard/manage_activities_and_tactics');
		}
	
		public function forecast_years(){ 
		$this->Common_model->check_dashboard_login();
			$client_id      = $this->session->userdata('client_id');
			if($this->input->post('start_year') !=""){
			$this->save_fiscal_year();
			}
			$sql_year = "SELECT * FROM tbl_fiscal_years WHERE client_id= $client_id ORDER BY start_year ASC";
			$data['forecast_year'] =  $this->Common_model->get_conditional_array($sql_year);
			
			$sql_year = "SELECT * FROM tbl_mbusiness_profile_plan WHERE client_id = $client_id";
			$fiscal_years_plan          =  $this->Common_model->get_conditional_array($sql_year);
			 $data['fiscal_years_plan'] = $this->Common_model->keyValuepair($fiscal_years_plan, 'plan_id','fiscal_years_id');
			$data['main_content'] = '/mbusiness_plan/forecast/all_forecast_year';
			$this->load->view('/includes/template_new', $data);
		}
		
		// add new forecast year 
		// function add_forecast_year(){
			// $clientid = $this->session->userdata('client_id');
			// $this->load->model('Forecast_year_model');
			// $query = $this->Forecast_year_model->GetForecastYear($clientid);
			// $data['forecast_year'] = $query ;
			// $session_uerdata = $this->session->userdata;
			// $data['main_content'] = '/mbusiness_plan/forecast/add_forecast_year';
			// $this->load->view('/includes/template_new', $data) ;
		// }
		
		
		// add new forecast year 
		function edit_forecast_year($message=''){
			$this->Common_model->check_dashboard_login();
			$client_id = $this->session->userdata('client_id');
			$yearid = $this->uri->segment(3);
			
			$sql_year = "SELECT * FROM tbl_fiscal_years WHERE client_id = $client_id and id = $yearid";
			$data['all_year'] =  $this->Common_model->get_conditional_array($sql_year);
			
			if(empty($data['all_year'])){
				$riderect = base_url('dashboard/forecast_years');
			    redirect($riderect);
			    return false;
			}
			$sql_year = "SELECT * FROM tbl_fiscal_semi_years WHERE client_id = $client_id and fiscal_years_id = $yearid";
			$data['semi_year'] =  $this->Common_model->get_conditional_array($sql_year);
			
			$sql_year = "SELECT * FROM tbl_fiscal_quaters WHERE client_id = $client_id and fiscal_years_id = $yearid";
			$data['quater_year'] =  $this->Common_model->get_conditional_array($sql_year);
			
			$sql_year = "SELECT * FROM tbl_mbusiness_profile_plan WHERE client_id = $client_id and fiscal_years_id = $yearid";
			$data['fiscal_years_plan'] =  $this->Common_model->get_conditional_array($sql_year);
			
			
			$data['main_content'] = '/mbusiness_plan/forecast/add_forecast_year';
			$this->load->view('/includes/template_new', $data) ;

			
		}
		
		function update_forecast_year(){
			$this->Common_model->check_dashboard_login();
			$clientid = $this->session->userdata('client_id');
			$yearid = $_POST['year_id'];
			$startdate = $_POST['startdate'];
			
			// check plan exist or not
			$sql_year = "SELECT * FROM tbl_mbusiness_profile_plan WHERE client_id = $clientid and year = $startdate and fiscal_years_id=$yearid";
			$plandata =  $this->Common_model->get_conditional_array($sql_year);
			
			if(!empty($plandata)){
				$riderect = base_url('dashboard/edit_forecast_year/'.$yearid.'?e=100');
			        redirect($riderect);
					return false;
			}
			
			if($_POST['year'] != ''){
				//Year data Update
				$year_start_date = date('Y-m-d',strtotime($_POST['year']['start_date']));
				$year_end_date   = date('Y-m-d',strtotime($_POST['year']['end_date']));
				
				if($year_start_date > $year_end_date){
					$riderect = base_url('dashboard/edit_forecast_year/'.$yearid.'?e=101');
			        redirect($riderect);
					return false;
				}else{ 
				$data = array( 'start_date' => $year_start_date, 'end_date' => $year_end_date ); 
				$this->db->where('client_id', $clientid); 
				$this->db->where('id', $yearid); 
				$this->db->update('tbl_fiscal_years', $data);
				}
				//Semi year data Update
				foreach($_POST['semi_year'] as $key=>$val){
					$semi_years['start_date'] = date('Y-m-d',strtotime($val['start_date']));
					$semi_years['end_date']   = date('Y-m-d',strtotime($val['end_date']));
					
					if($semi_years['start_date'] < $year_start_date){
					$riderect = base_url('dashboard/edit_forecast_year/'.$yearid.'?e=102');
			        redirect($riderect);
					return false;
					}else if($semi_years['end_date'] > $year_end_date){
					$riderect = base_url('dashboard/edit_forecast_year/'.$yearid.'?e=103');
			        redirect($riderect);
					return false;
					}else if($semi_years['start_date'] > $semi_years['end_date']){
					$riderect = base_url('dashboard/edit_forecast_year/'.$yearid.'?e=104');
			        redirect($riderect);
					return false;
					}else{ 
						$this->db->where('client_id', $clientid); 
						$this->db->where('fiscal_years_id', $yearid); 
						$this->db->where('semi_year_key', $key); 
						$this->db->update('tbl_fiscal_semi_years', $semi_years);
					}
				}
				
				//Quaterly data Update
				
				foreach($_POST['quarter'] as $quatr_key=>$values){
					$quater_years['start_date'] = date('Y-m-d',strtotime($values['start_date']));
					$quater_years['end_date'] = date('Y-m-d',strtotime($values['end_date']));
					if($quater_years['start_date'] < $year_start_date){
					$riderect = base_url('dashboard/edit_forecast_year/'.$yearid.'?e=105');
			        redirect($riderect);
					return false;
					}else if($quater_years['end_date'] > $year_end_date){
					$riderect = base_url('dashboard/edit_forecast_year/'.$yearid.'?e=106');
			        redirect($riderect);
					return false;
					}else if($quater_years['start_date'] > $quater_years['end_date']){
					$riderect = base_url('dashboard/edit_forecast_year/'.$yearid.'?e=107');
			        redirect($riderect);
					return false;
					}else{
					$this->db->where('client_id', $clientid); 
					$this->db->where('fiscal_years_id', $yearid); 
					$this->db->where('quater_key', $quatr_key); 
					$this->db->update('tbl_fiscal_quaters', $quater_years);
					}
				}
				 
				
			}
			
			
			$riderect = base_url('dashboard/forecast_years');
			redirect($riderect);
		}
		
		
		
function save_fiscal_year(){
 $this->Common_model->check_dashboard_login();	
$clientid = $this->session->userdata('client_id');	
$start_date  = date('Y-m-d',strtotime($this->input->post('start_year')));
$end_date    =  date('Y-m-d',strtotime($this->input->post('end_year')));

$expplode_start_yr  =   explode("-",$start_date);
$expplode_end_yr    =   explode("-",$end_date);

$yearly_data['start_date']   = $start_date;
$yearly_data['end_date']     = $end_date;
$yearly_data['start_year']   = $expplode_start_yr[0];
$yearly_data['end_year']     = $expplode_end_yr[0];
$yearly_data['client_id']    = $clientid;
$this->Common_model->insert_data('tbl_fiscal_years', $yearly_data);
$last_id  = $this->db->insert_id();



$start_date_format      = date_create($start_date);
$end_date_format        = date_create($end_date);
$difference             = date_diff($start_date_format,$end_date_format);
$days                   =  $difference->format("%a");


// semi year code 
$semi_yr            =   floor($days/2);
$first_semi         =   '+'.$semi_yr.' day';
$first_semi_end     =  date('Y-m-d',strtotime($first_semi, strtotime($start_date))); 
$second_semi_start  =  date('Y-m-d',strtotime('+1 day', strtotime($first_semi_end))); 
$semi_data[0]['start_date']        = $start_date;
$semi_data[0]['end_date']          = $first_semi_end;
$semi_data[0]['fiscal_years_id']   = $last_id;
$semi_data[0]['client_id']         = $clientid;
$semi_data[0]['semi_year_key']     = "H1";


$semi_data[1]['start_date']        = $second_semi_start;
$semi_data[1]['end_date']          = $end_date;
$semi_data[1]['fiscal_years_id']   = $last_id;
$semi_data[1]['client_id']         = $clientid;
$semi_data[1]['semi_year_key']     = "H2";

// quarters code 
$qtr_yr                =   floor($days/4);
$qtrs_day              =   '+'.$qtr_yr.' day';
$first_qtr_end         =  date('Y-m-d',strtotime($qtrs_day, strtotime($start_date))); 
$second_qtr_start      =  date('Y-m-d',strtotime('+1 day', strtotime($first_qtr_end))); 
$second_qtr_end        =  date('Y-m-d',strtotime($qtrs_day, strtotime($first_qtr_end))); 

$third_qtr_start       =  date('Y-m-d',strtotime('+1 day', strtotime($second_qtr_end))); 
$third_qtr_end         =  date('Y-m-d',strtotime($qtrs_day, strtotime($second_qtr_end))); 
$fouth_qtr_start       =  date('Y-m-d',strtotime('+1 day', strtotime($third_qtr_end))); 

$qtr_data[0]['start_date']   = $start_date;
$qtr_data[0]['end_date']     = $first_qtr_end;
$qtr_data[0]['fiscal_years_id']   = $last_id;
$qtr_data[0]['client_id']         = $clientid;
$qtr_data[0]['quater_key']        = "Q1";

$qtr_data[1]['start_date']   = $second_qtr_start;
$qtr_data[1]['end_date']     = $second_qtr_end;
$qtr_data[1]['fiscal_years_id']  = $last_id;
$qtr_data[1]['client_id']         = $clientid;
$qtr_data[1]['quater_key']        = "Q2";

$qtr_data[2]['start_date']   = $third_qtr_start;
$qtr_data[2]['end_date']     = $third_qtr_end;
$qtr_data[2]['fiscal_years_id']  = $last_id;
$qtr_data[2]['client_id']         = $clientid;
$qtr_data[2]['quater_key']        = "Q3";

$qtr_data[3]['start_date']   = $fouth_qtr_start;
$qtr_data[3]['end_date']     = $end_date;
$qtr_data[3]['fiscal_years_id']  = $last_id;
$qtr_data[3]['client_id']         = $clientid;
$qtr_data[3]['quater_key']        = "Q4";

//echo "<pre>";print_r($semi_data);echo "</pre>";
//echo "<pre>";print_r($qtr_data);echo "</pre>";

if(!empty($semi_data)){
$this->db->insert_batch('tbl_fiscal_semi_years', $semi_data);
}

if(!empty($qtr_data)){
$this->db->insert_batch('tbl_fiscal_quaters', $qtr_data);
}


}

		
		
	function insert_regioncontrol_data(){
	$this->Common_model->check_dashboard_login();
		$clientid = $this->session->userdata('client_id');	
		$update_partner_cam = array();
		$update_secondary_cam = array();
		$insert_secondary_cam = array();
		 
	      if(!empty($_POST['assign_cam'])){
			foreach($_POST['assign_cam'] as $key => $cam_id){
			    $explode_cam               = explode("<>",$cam_id['cam_id']);
				$update_partner_data['id'] = $key;
				$update_partner_data['crm_id'] = $explode_cam[0];
				$update_partner_cam[] = $update_partner_data;
			}
		}
		
		if(!empty($update_partner_cam)){
			$this->db->update_batch('partners_accounts',$update_partner_cam,'id');
		}
		
		if(!empty($_POST['assign_secondary_cam'])){
		   foreach($_POST['assign_secondary_cam'] as $key_val => $secondary_cam_id){
			foreach($secondary_cam_id as $k => $result){
				$del_cam_ids = "'".implode("','", $result)."'";
				$sql_delete = "delete from partner_secondary_cam where partner_id ='".$key_val."' and cam_id not IN($del_cam_ids)";
				$this->db->query($sql_delete);
				
			foreach($result as $key => $cam_id){
				$primary_cam_arr  =  explode("<>",$_POST['assign_cam'][$key_val]['cam_id']);
				$primary_cam_id   = $primary_cam_arr[1];
				$output_rsult = explode('_',$resultoutput);
				$exist_id = $this->Common_model->get_data_row('id', 'partner_secondary_cam',array('partner_id'=>$key_val,'secondary_cam_id' => $cam_id));
						if(empty($exist_id)){
							$insert_secondary_data['partner_id'] = $key_val;
							$insert_secondary_data['cam_id'] = $primary_cam_id;
							$insert_secondary_data['client_id'] = $clientid;
							$insert_secondary_data['secondary_cam_id'] = $cam_id;
							$insert_secondary_cam[] = $insert_secondary_data;
						}
					
					}
			
				}
				
				
			}
		}
		
		if(!empty($insert_secondary_cam)){
			$this->db->insert_batch('partner_secondary_cam',$insert_secondary_cam);
		}
		
		if($_POST['url'] !=''){
			redirect($_POST['url']);
		}else{
		   $this->session->set_userdata('success_msg',1);
			redirect("dashboard/regioncontrol/region_visualization");
		}
	}
		
		
		
	public function save_mbusiness_quantity_coefficient(){
		$client_id = $this->session->userdata('client_id');
		$mbusiness_tactic_id = $this->input->post('mbusiness_tactic_id');
		$quantity_coefficient = $this->input->post('quantity_coefficient');
		$insert_arr = array();
		$update_arr = array();
		
		foreach($_POST['quantity'] as $key => $tactic_dtails){ 
			$temp_data =array();
			$tactic_data =  $this->Common_model->get_data_row('*','tbl_mbusiness_quantity_coefficient', array('mbusiness_tactic_id' => $mbusiness_tactic_id, 'mbusiness_quantity_coefficient_id' => $key));
						
			$temp_data['client_id'] = $client_id;			
			$temp_data['quantity'] = $tactic_dtails;				
			$temp_data['quantity_coefficient'] = $quantity_coefficient[$key];			
			
			if(!empty($tactic_data)){
				$temp_data['mbusiness_quantity_coefficient_id'] = $key;	
				$update_arr[] = $temp_data;
			}else{
				$temp_data['mbusiness_tactic_id'] = $mbusiness_tactic_id;	
				$insert_arr[] = $temp_data;
			}
		}
		
		if(!empty($insert_arr)){
			$this->db->insert_batch('tbl_mbusiness_quantity_coefficient', $insert_arr);
		}
		
		if(!empty($update_arr)){
			$this->db->update_batch('tbl_mbusiness_quantity_coefficient', $update_arr,'mbusiness_quantity_coefficient_id');
		}
		redirect('dashboard/manage_activities_and_tactics');
	}	
		
	
	    public function update_region_level4()
    {
        $clientid = $this->session->userdata('client_id');
        $unique_id = $this->input->post('item');
        $drag_id = $this->input->post('new_level4');
        $new = $this->Common_model->update_data('region_level3', array('level4_parent_id' => $drag_id), array('region_level3_id' => $unique_id, 'client_id' => $clientid));

        if ($new) {
            return true;
        } else {
            return false;
        }
    }	
		
	public function update_regionlevel4($type)
    {   
	     $this->Common_model->check_dashboard_login();
       
            $this->load->model('Region_model');
            $clientid = $this->session->userdata('client_id');
            $region_id = $this->input->post('region_id');
            $name = $this->input->post('name');
            $description = $this->input->post('description');
            $assigned_region_level3 = $this->input->post('assigned_region_level3');
            $data = array('region_level4_id' => $region_id, 'name' => $name);
            $implode_assigned_ids = implode(',', $assigned_region_level2);

            $check_unique = $this->Region_model->checkunique_field('region_level4_id', $name, 'region_level4', $region_id);

            if ($check_unique == 0) { 
                $this->Region_model->update_region_level4($data);
                $this->Common_model->update_data('region_level3', array('level4_parent_id' => $region_id), array('client_id' => $clientid, 'level4_parent_id' => $region_id));
                $add = $this->Region_model->assign_level3_id($region_id, $implode_assigned_ids);
            }

            if ($add) {
                if ($type == 'vlevel4') {
                    $this->session->set_flashdata('region_update', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Level updated successfully</strong> </div>');
                    redirect('dashboard/regioncontrol');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Level updated successfully</strong> </div>');
                    redirect('dashboard/region/level/4');
                }
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong> Region name already exist</strong> </div>');

                if ($check_unique != 0) {
                    redirect('dashboard/edit/level4/' . $region_id);
                } else {
                    redirect('dashboard/region/level/4');
                }
            }
       
    }	
		
	    // Add Region level 4
    public function add_region_level4()
    {   
	    $this->Common_model->check_dashboard_login();
       
            $this->load->model('Region_model');
            $clientid = $this->session->userdata('client_id');
            $region_level4 = $this->input->post('region_level4');
            $region_level4_desc = $this->input->post('region_level4_desc');
            $assigned_region_level3 = $this->input->post('assigned_region_level3');

            $data = array('client_id' => $clientid, 'name' => $region_level4);
            $check_unique = $this->Region_model->checkunique_field($id = '', $region_level4, 'region_level4',$edit="");
            if ($check_unique == 0) {
                $level4_parent_id = $this->Common_model->insert_data('region_level4', $data);
                $implode_assigned_ids = implode(',', $assigned_region_level3);

                $add = $this->Region_model->assign_level3_id($level4_parent_id, $implode_assigned_ids);
            }
            if ($add) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong> Region Level 4 successfully added</strong> </div>');
                redirect('dashboard/region/level/4');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong> Region name already exist</strong> </div>');
                redirect('dashboard/add/level/4');
            }
        
    }	
		
	 // Function to delete region 4
    public function deleteregionlevel4()
    {  
	    $this->Common_model->check_dashboard_login();
        $region_id = $this->uri->segment(3);
		if ($cam_records) {
            $this->session->set_flashdata('msg', '<div class="alert alert-info fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region is already assigned to some CAM. You cannot delete this Region.</strong></div>');
        } else {
            $this->Common_model->update_data('region_level3', array('level4_parent_id' => null), array('level4_parent_id' => $region_id));
            $delete = $this->Common_model->delete_records('region_level4', array('region_level4_id' => $region_id));

            $this->session->set_flashdata('msg', '<div class="alert alert-success fade in"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Region Deleted Successfully!</strong></div>');
        }
        redirect('dashboard/region/level/4');
    }	
		
	// End Activity Grouping, Activity and Tactic Type Admin Control
    
	
	 
	  // function for upload and download center module
	  function upload_download_center(){
		  $this->Common_model->check_dashboard_login(); 
		  $clientid = $this->session->userdata('client_id');
		   $data['main_content'] = 'upload_center/upload_download_center';
          $this->load->view('/includes/template_new', $data);
		   }
	

	
}
