<style>
	.canvas_div .sign_text {
		margin-bottom: 20px !important; 
		padding-top: 6px; 
		width: 100%;
	}
	.accpt_ul input {
		margin-right: 10px;
	}
	.accpt_ul {
		margin-left: 20px;
		margin-top: 12px;
		line-height: 26px;
	}
	.col-12.form_div {
		margin-top: 0px !important;
	}
	.accept_quarters h5 {
		font-weight:bold;
		margin-bottom: 10px;  
		width: 100%; 
	}

	.canvas_div {
		margin-top: 10px;
	}
	.rk_acp_head{
		font-size:10px;
	}
	.plan_approvPg {
    margin-bottom: 10px;
    padding-bottom: 25px !important;
    padding-top: 10px;
    padding-right: 7px;
    padding-left: 10px;
}
	
.export_approve_pln {
    background: #014891;
    color: #fff;
    margin-bottom: 20px !important;
    padding-bottom: 11px !important;
    padding-right: 20px;
    padding-left: 20px;
    padding-top: 11px;
    border-radius: 4px;
	    cursor: pointer;
}

.application_selection{
	float:right;margin-top: 7px;
}

select#selected_application {
    padding: 3px;
}
.accept-home{padding-left:15px;
padding-right:15px;}

.accept_cont{margin-bottom:20px;
    padding-bottom: 15px;
    width: 100%;
    float: left;
    border: 1px solid #dee2e6;}
	
	span.accept-export-button {
    float: left;
    margin: 0 0 5px;
}

.accept-header-top{margin-top:0px;}

.plan-approve-heading{
    font-weight: normal;
    display: inline-block;
    font-size: 14px;
    text-align: right;}

.select-top.plan_approvPg label {padding-bottom:0px;
padding-top:0px;
    display:none;
}
table.plan-approve-table{margin:0 auto;width:98%;}

.plan-approve-nxt-sec {
    margin: 20px 0 0px 15px;
    float: left;
}

.plan_approvPg select.acceptPlanDrpdwn {
    float: right;
    height: 24px !important;
    margin-top: -5px;
}
table.plan-approve-table th.plan-approve-period {
    width: 200px !important;
}
table.plan-approve-table th.plan-approve-status {
    width: 9%;
}




</style>

<link href='https://fonts.googleapis.com/css?family=Oleo+Script' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Lobster+Two' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Berkshire+Swash' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Merienda+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Pacifico' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Just+Another+Hand' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Arizonia' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Cookie' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Leckerli+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Sofia' rel='stylesheet'>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="<?php echo $this->config->item('base_url1'); ?>fronthand/ui/js/modernizr.custom.min.js"></script>
<script src="<?php echo $this->config->item('base_url1'); ?>fronthand/ui/js/jquery.signaturepad.min.js"></script><?php 
	$type_arr =   $plan_steps_keys; // 
	$name_arr=$title_app_arr;
	['marketing_action_plan'];

	$green_button = '<img class="clickable_cls" src='.$this->config->item(base_url1).'/fronthand/images/green-circle.png'.' onclick="'.$click.'">'; 
	$red_button = '<img class="clickable_cls" src='.$this->config->item(base_url1).'/fronthand/images/red-circle.png'.' onclick="'.$click.'">'; 
	$yellow_button = '<img class="clickable_cls" src='.$this->config->item(base_url1).'/fronthand/images/yl_btm.png'.'>'; 
	$bl_button = '<img class="clickable_cls" src='.$this->config->item(base_url1).'/fronthand/images/blue-circle.png'.'>'; 
	$white_btn = '<img class="clickable_cls" src='.$this->config->item(base_url1).'/fronthand/images/white-circle.png'.'>';

	if($get_started){
		$url = base_url().'home/default';
	}else{
		$url = "";
	} ?>
<div class="main_accept accept-home">
	<div class="ajax_res_s">
		<div class="accept_bottom accept-header-top">
			<div class="export_plan_dv">
				<span class="exportScoreNav accept-export-button" style="color:#fff;" onclick="export_marketing_acceptplan('marketing_accept_plan');">Export</span>
		
				<?php if(array_key_exists('marketing_action_plan' , $parent_step)){ ?>
					<div class="application_selection">
						<label for="selected_application" style="font-weight:bold;">Accept/Approve Plan for:</label>
						<select id="selected_application">
							<option value="application">Applications</option>
							<option selected value="marketing_action_plan">Marketing Action Plan</option>
							
						</select>
					</div>	
				<?php } ?>	
			</div>
		
			<div class="accept_cont">
				<div class="select-top plan_approvPg">
					<div class="row">
						<div class="col-7 plan-approve-heading">
							<p>Plan Acceptance and Approval Report</p>
						</div>
						<div class="col-5"><?php 
							$this->load->view('mbusiness_plan/common_file/fiscal_year_dropdown.php',array(
								'fiscal_years' =>$fiscal_year_data['fiscal_years'],
								'id' => 'plan_drpdwn',
								'class' =>'acceptPlanDrpdwn',
								'func_name' => 'change_fiscal();',
								'selecteYear' =>$current_yr,
								'selecterYearID' =>$current_yr_id,
						
							));?>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-12">
						<div class="overlay overlay_new" style="display: none;"><?php
							$loder_content = custom_loader('camdashboard');
							$this->load->view('loader',array('loader_data'=> (array)$loder_content,'outer_class'=>'overlay','inner_class'=>'loader')); ?>
						</div>
					
						<div class="table-responsive">
							<table class="plan_tbl rk_plan_tbl main_tble_approve plan-approve-table">
								<tr>
									<th colspan="10" style="display:none;font-size:15px;font-weight:bold;">Accept Plan Report<br>Fiscal Year : <?php echo $current_yr;?> </th>
								</tr>
								<tr>
									<th colspan="10" style="display:none;font-size:15px;font-weight:bold;">Partner Name :<?php echo $this->session->userdata('partnername') ?> </th>
								</tr>
								<tr>
									<th colspan="6" class="thFirst bg_colork">Partner Plan Acceptance Status</th>
									<th colspan="4" class="thFirst bg_colork">Partner Plan Approval Status</th>
								</tr>
								<tr>
									<th class="bg_colork">Application</th>
									<th class="accept_period bg_colork plan-approve-period">Period</th>
									<th class="bg_colork plan-approve-status">Plan Acceptance Status 
										<span class="rk_acp_head">
											<div class="cal_help ml_help vineetTooltip" data-tooltip='Click on colored ball to apply signatures for accepting the plan'>?</div>
										</span>
									</th>
									<th class="bg_colork">Partner Plan Acceptance Name</th>
									<th class="bg_colork remove_td">Partner Acceptance Signature</th>
									<th class="bg_colork" style="display:none;">Signed By Partner</th>
									<th class="bg_colork">Partner Acceptance Date</th>
									<th class="plan_disableth bg_colork">Plan Approval Status</th>
									<th class="plan_disableth bg_colork"><?php echo ucfirst($this->session->userdata('client_name'));?> Approval Name</th>
									<th class="plan_disableth bg_colork remove_td"><?php echo ucfirst($this->session->userdata('client_name'));?> Approval Signature</th>
									<th class="bg_colork" style="display:none;">Signed By CAM</th>
									<th class="plan_disableth bg_colork"><?php echo ucfirst($this->session->userdata('client_name'));?> Approval Date</th>
								</tr><?php 
								
								
						foreach($type_arr as $k=> $val){
							if($frequency_data[$val] == 3){
								$loop_count = 4;
							}else{
								$loop_count = $frequency_data[$val];
							}
							for($j=0;$j<$loop_count;$j++){
							 ?>
							<tr><?php 
								if($frequency_data[$val] == 3){
									$quarter_val =  'Q'.($j+1);
									$quarter = 'Q'.($j+1).' ('.date('m/d/Y',strtotime($fiscal_year_data['qtrs_array'][$current_yr_id]['Q'.($j+1)]['quater_start_date'])).' - '.date('m/d/Y',strtotime($fiscal_year_data['qtrs_array'][$current_yr_id]['Q'.($j+1)]['quater_end_date'])).')';
								}
								elseif($frequency_data[$val]== 1){
									$quarter_val =  'FY'.($j+1);
									$quarter = 'FY'.$fiscal_year_data['fiscal_years'][$current_yr_id]['start_year'].' ('.date('m/d/Y',strtotime($fiscal_year_data['fiscal_years'][$current_yr_id]['start_date'])).' - '.date('m/d/Y',strtotime($fiscal_year_data['fiscal_years'][$current_yr_id]['end_date'])).')';
								}
								else{		
									$quarter_val =  'H'.($j+1);
									$quarter = 'H'.($j+1).' ('.date('m/d/Y',strtotime($fiscal_year_data['semi_array'][$current_yr_id]['H'.($j+1)]['semi_start_date'])).' - '.date('m/d/Y',strtotime($fiscal_year_data['semi_array'][$current_yr_id]['H'.($j+1)]['semi_end_date'])).')';
								} 	

								if($j == 0){ ?>
									<td rowspan="<?php echo $loop_count;?>"><span class="type_val"><?php echo $app_name = $name_arr[$val]; ?></span></td><?php 
								} ?>
								<td>
									<span><?php echo $quarter; ?></span>
									<input type="hidden" class="quarter_pop" value="<?php echo $actual_qtr; ?>"/>
									<input type="hidden" class="quarter_name" value="<?php echo $quarter_name; ?>"/>
									<input type="hidden" class="app_type" value="<?php echo $type_arr[$k]; ?>"/>
								</td><?php 
								
								
								$quarter_arr[] = $quarter;
								$app_arr[] = $app_name; 
								if($signatures[$current_yr][$quarter_val][$val]['signature']){
									echo '<td class="green_clsk"><img class="img_remove" src='.$this->config->item(base_url1).'/fronthand/images/green-circle.png'.'></td>';
								}else{
									$this->load->view('fronthand/ui/accept_plan/frontend_balls',array('atmpt_balls' => $balls_data['balls_attempt'], 'score_board' => $balls_data['score_board'], 'frequency_data' => $frequency_data, 'semi_annual_balls' => $balls_data['semi_annual_balls'],'val' => $val,'quarter' => 'q'.($j+1),'complete_quarter' => $quarter,'current_yr' => $current_yr,'disable_val' => "" ));
								} ?>
								<td><?php 
									if($signatures[$current_yr][$quarter_val][$val]['signature']){
										echo $signatures[$current_yr][$quarter_val][$val]['plan_accepted_by']; 
									}else{
										
									}?>
								</td>
								
								<td class="remove_td"><?php 
									if($signatures[$current_yr][$quarter_val][$val]['signature']){
										$explode_signature = explode(".",$signatures[$current_yr][$quarter_val][$val]['signature']);
										if(in_array("png",$explode_signature)){ ?>
											<img disabled class="signature_cls img_remove" src="<?php echo $this->config->item('base_url1'); ?>uploads/signatures/<?php echo $signatures[$current_yr][$quarter_val][$val]['signature']; ?>"><?php 
										}else{
											echo '<span class="'.$signatures[$current_yr][$quarter_val][$val]['signature'].'">'.$signatures[$current_yr][$quarter_val][$val]['plan_accepted_by'].'</span>';
										}
									} ?>
								</td>
								<td><?php 
									echo $signatures[$current_yr][$quarter_val][$val]['plan_accept_date']; ?>
								</td><?php 
								if($signatures[$current_yr][$quarter_val][$val]['cam_signature']){
									echo '<td class="plan_disabletd non-click green_clsk"><img class="img_remove" src='.$this->config->item(base_url1).'/fronthand/images/green-circle.png'.'></td>';
								}else{
									$this->load->view('fronthand/ui/accept_plan/frontend_balls',array('atmpt_balls' => $balls_data['balls_attempt'],'score_board' => $balls_data['score_board'],'frequency_data' => $frequency_data,'semi_annual_balls' => $balls_data['semi_annual_balls'],'val' => $val,'quarter' => 'q'.($j+1),'complete_quarter' => $quarter,'current_yr' => $current_yr,'disable_val' => "yes" ));
								}?>
								<td class="plan_disabletd"><?php 
									if($signatures[$current_yr][$quarter_val][$val]['plan_approved_by']){
										echo $signatures[$current_yr][$quarter_val][$val]['plan_approved_by']; 
									}?>
								</td>
								<td class="plan_disabletd remove_td"><?php 
									if($signatures[$current_yr][$quarter_val][$val]['cam_signature']){
										$explode_cam_signature = explode(".",$signatures[$current_yr][$quarter_val][$val]['cam_signature']);
										if(in_array("png",$explode_cam_signature)){ ?>
											<img disabled class="signature_cls img_remove" src="<?php echo $this->config->item('base_url1'); ?>uploads/signatures/<?php echo $signatures[$current_yr][$quarter_val][$val]['cam_signature']; ?>"><?php 
										} else {
											echo '<span class="'.$signatures[$current_yr][$quarter_val][$val]['cam_signature'].'">'.$signatures[$current_yr][$quarter_val][$val]['plan_approved_by'].'</span>';
										}

									} ?>
								</td>
								<td class="plan_disabletd"><?php 
									echo $signatures[$current_yr][$quarter_val][$val]['plan_approved_date']; ?>
								</td>
							</tr><?php 
						} 
					} ?>
					<tr style="display:none;"></tr>	
					<tr style="display:none;">
						<td style="display:none;background:#FFEBCD;color:#5c5cd6;font-size:16px;font-weight:bolder;height:40px;vertical-align:middle;text-align:center;border:1px solid #ddd;" colspan="10">What do these colored boxes means?</td>
					</tr>
					<tr>
						<td style="display:none;background:#388B18;border:1px #ddd;font-weight:normal;"></td><td colspan="9" style="display:none;height:30px;vertical-align:middle;border:1px solid #ddd;">Plan is accepted / approved for this time period</td>
					</tr>
					<tr>
						<td style="display:none;background:red;border:1px #ddd;font-weight:normal;"></td>
						<td colspan="10" style="display:none;height:30px;vertical-align:middle;border:1px solid #ddd;">Plan is not accepted / approved for this time period and past due</td>
					</tr>
					<tr>
						<td style="display:none;background:#014891;border:1px #ddd;font-weight:normal;"></td>
						<td colspan="9" style="display:none;height:30px;vertical-align:middle;border:1px solid #ddd;">Plan is pending for this period</td>
					</tr>
					<tr>
						<td style="display:none;background:#fff;border:1px #ddd;border:1px solid #ddd;color:#fff;font-weight:bolder;"></td>
						<td colspan="9" style="display:none;height:30px;vertical-align:middle;border:1px solid #ddd;">Future Period for accepting / approving plans</td>
					</tr>
				</table>
			</div>
			<table class="comment_table rk_approve_tbl plan-approve-nxt-sec">
				<thead>
					<tr>
						<td class="color_td rk_center"><?php echo $red_button; ?></td>
						<td>Plan is not accepted / approved for this time period and past due</td>
					</tr>
					<tr>
						<td class="color_td rk_center"><?php echo $green_button; ?></td>
						<td>Plan is accepted / approved for this time period</td>
					</tr>
					<tr>
						<td class="color_td rk_center"><?php echo $bl_button; ?></td>
						<td>Plan is pending for this period</td>
					</tr>
					<tr>
						<td class="color_td rk_center"><?php echo $white_btn; ?></td>
						<td>Future Period for accepting / approving plans</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="btm_td">Accepted Plans:</td>
						<td>Partner contacts are authorized to ???accept??? each component of their plan</td>
					</tr>
					<tr>
						<td class="btm_td">Approved Plans:</td>
						<td>Client channel executives can ???approve??? partner plans</td>
					</tr>
				</tbody>
			</table>
		</div>	
		</div>	
		</div>	
		</div>	
	</div>
	
</div>
<?php 

?>

<!--  popup -->
<div id="pop_wrapper" style="display:none;" >
	<div class="innter_pop acceptPlanPop" >
		<span class="close_x">x</span> 
		<div class="row">
			<div class="col-12 form_div rk_accpt_popupop">
				<h1 class="pop_head"></h1>
				<div class="error_div"></div>
				<form method="post" action="<?php echo base_url(); ?>plan_accept_approve/accept_approve_plan" class="sigPad" enctype="multipart/form-data">
					<div class="accept_quarters">
						<table class="plan_tbl rk_plan_tbl" style="width:100%">
						<thead>
						<tr>
						<th colspan="2">Check Applications/Periods for which you want to accept plans</th>
						<tr>
						<tr>
						<th>Application</th>
						<th>Period</th>
						<tr>
						</thead>
						<tbody>
						<?php 
					foreach($type_arr as $k=> $val){
						if($frequency_data[$val] == 3){
							$loop_count = 4;
						}else{
							$loop_count = $frequency_data[$val];
						}
						for($j=0;$j<$loop_count;$j++){
							if($forecast_data['start_month'] != 1){
								$strt_yr = $current_yr;
								$end_yr = $current_yr +1;
							}else{
								$strt_yr = $current_yr;
								$end_yr = $current_yr;
							} ?>
							<tr><?php 
								if($frequency_data[$val] == 3){
									
									$quarter = 'Q'.($j+1).' ('.date('m/d/Y',strtotime($fiscal_year_data['qtrs_array'][$current_yr_id]['Q'.($j+1)]['quater_start_date'])).' - '.date('m/d/Y',strtotime($fiscal_year_data['qtrs_array'][$current_yr_id]['Q'.($j+1)]['quater_end_date'])).')';
									$quarter_name = 'Q'.($j+1);
								}
								elseif($frequency_data[$val]== 1){
									$quarter = 'FY'.$fiscal_year_data['fiscal_years'][$current_yr_id]['start_year'].' ('.date('m/d/Y',strtotime($fiscal_year_data['fiscal_years'][$current_yr_id]['start_date'])).' - '.date('m/d/Y',strtotime($fiscal_year_data['fiscal_years'][$current_yr_id]['end_date'])).')';
									$quarter_name = 'FY'.($j+1);
								}
								else{		
									
									$quarter = 'H'.($j+1).' ('.date('m/d/Y',strtotime($fiscal_year_data['semi_array'][$current_yr_id]['H'.($j+1)]['semi_start_date'])).' - '.date('m/d/Y',strtotime($fiscal_year_data['semi_array'][$current_yr_id]['H'.($j+1)]['semi_end_date'])).')';
									$quarter_name = 'H'.($j+1);
								} 	


								if($j == 0){ ?>
									<td rowspan="<?php echo $loop_count;?>"><span class="type_val"><?php echo $app_name = $name_arr[$val]; ?></span></td><?php 
								} ?>
								<td>
									<input id="" class="chk_qtr" type="checkbox" name="qtrs[]" value="<?php echo $quarter_name . '<>' . $val;?>">
									<span><?php echo $quarter; ?></span>
									
								</td>
					</tr><?php }} ?>
					</tbody>
								</table>
						<!--ul class="accpt_ul"></ul><br-->
						<div class="rk_accept_div">
							<span class="rk_fname_t"><strong>Logged in User: </strong></span>
							<span class="rk_fname"><?php 
								if($this->session->userdata('is_cam_login')) {
									echo $this->session->userdata('crm_acc_name'); 
								} else {
									echo $this->session->userdata('username');	
								} ?>
							</span>
							<ul>
								<li id="select_signature" class="rk_list active" onclick="display_accept_plan_tab(this,'style');" title="Select Signature" alt="1">Select Signature</li>

								<li id="draw_signature" class="rk_list" onclick="display_accept_plan_tab(this,'draw');"  title="Draw">Create your own Signature</li>

								<li id="upload_signature" class="rk_list" onclick="display_accept_plan_tab(this,'upload');" title="Upload Signature">Upload Signature</li>
							</ul>
						</div>
						<h5 class="sig_title">Select Sign to Accept Your Plan:</h5>
					</div>
					<div class="custom_draw" style="display:none;">
						<div class="canvas_div">
							<canvas class=pad width=350 height=100 style="border:1px solid black;"></canvas>
							<div class="sign_text">To add signature - click inside the rectangular box and use your touch<br> pad or mouse to create a custom signature and then click Save.</div>
							<input type="hidden" name="output" class="output" />
							<input type="hidden" name="type" class="type" value=""/>
							<input type="hidden" name="quarter" class="quarter" value=""/>
							<input type="hidden" name="year" class="year" value=""/>
							<input type="hidden" name="application_id" class="application_id" value=""/>
						</div>
					</div>
					<div class="select_signature">
						<ul><?php 
							for($counter=1;$counter<=12;$counter++){ ?>
								<li class="signature_style">
									<label class="rk_sig_lbl">
										<span class="signature<?php echo $counter; ?>"><?php 
											if($this->session->userdata('is_cam_login')) {
												echo $this->session->userdata('crm_acc_name'); 
											}else{
												echo $this->session->userdata('username');	
											} ?> 
										</span>
										<input type="radio" class="rk_signature_input" name="signature" value="signature<?php echo $counter; ?>" <?php if($counter==1){ echo "checked='checked'";} ?>>
										<span class="checkmark"></span>
									</label>
								</li><?php 
							} ?>
						</ul>
					</div>
					<div class="rk_upload_sig" style="display:none;">
						<div class="col-md-4 text-center">
							<div class="upload-btn-wrapper">
								<button class="rk_sig_btn" type="button">Browse...</button>
								<input type="file" id="upload_signature_img">
							</div>
							<div class="rk_no_img_div">
								<img src="<?php echo $this->config->item(base_url1); ?>/fronthand/images/no-img.png" class="rk_no_img">
							</div>
							<div id="upload-demo" style="display:none;"></div>
						</div>
					</div>
					<br>
					<div class="accept_btns">
						<input type="button" class="accept_plan save_document" onclick="vaidate_data();" value="Save"/>
						<button class=clearButton>Reset</button>
					</div>
				</form>
				<form method="post" action="<?php echo base_url(); ?>plan_accept_approve/accept_plan" class="upload_signature_form" enctype="multipart/form-data"></form>
			</div>
		</div>
		<div class="pop_clr"></div>
	</div>      
</div>
<!-- /popup end  -->

<!--Export Plan Aproval frontend---><?php
// if(!empty($_POST['type'])){ ?>
			<div class="marketing_response_data table-responsive">
				<table class="marketing_export_plan_tbl rk_plan_tbl" style="display:none">
					<tr>
						<th colspan="10" style="font-size:15px;font-weight:bold;">Accept Plan Report<br>Fiscal Year : <?php echo $fiscal_year_val;?> </th>
					</tr>
					<tr>
						<th colspan="10" style="font-size:15px;font-weight:bold;">Partner Name :<?php echo $this->session->userdata('partnername') ?> </th>
					</tr>
					<tr>
						<th colspan="6" style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="thFirst bg_colork">Partner Plan Acceptance Status</th>
						<th colspan="4" class="thFirst bg_colork" style="background-color:#014891;color:#fff; border: 1px solid #ddd;" >Partner Plan Approval Status</th>
					</tr>
					<tr>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork">Application</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="accept_period bg_colork">Period</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork ">Plan Acceptance Status </th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork">Partner Plan Acceptance Name</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork">Partner Acceptance Signature</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork">Partner Acceptance Date</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;">Plan Approval Status</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;"><?php echo ucfirst($this->session->userdata('client_name'));?> Approval Name</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;"><?php echo ucfirst($this->session->userdata('client_name'));?> Approval Signature</th>
						<th style="background-color:#014891;color:#fff;" ><?php echo ucfirst($this->session->userdata('client_name'));?> Approval Date</th>
					</tr><?php 
					foreach($type_arr as $k=> $val){
						if($frequency_data[$val] == 3){
							$loop_count = 4;
						}else{
							$loop_count = $frequency_data[$val];
						}
						for($j=0;$j<$loop_count;$j++){
							if($forecast_data['start_month'] != 1){
								$strt_yr = $current_yr;
								$end_yr = $current_yr +1;
							}else{
								$strt_yr = $current_yr;
								$end_yr = $current_yr;
							} ?>
							<tr><?php 
								if($frequency_data[$val] == 3){
									$quarter_val =  'Q'.($j+1);
									$quarter = 'Q'.($j+1).' ('.date('m/d/Y',strtotime($fiscal_year_data['qtrs_array'][$current_yr_id]['Q'.($j+1)]['quater_start_date'])).' - '.date('m/d/Y',strtotime($fiscal_year_data['qtrs_array'][$current_yr_id]['Q'.($j+1)]['quater_end_date'])).')';
									$quarter_name = 'Q'.($j+1);
								}
								elseif($frequency_data[$val]== 1){
									$quarter_val =  'FY'.($j+1);
									$quarter = 'FY'.$fiscal_year_data['fiscal_years'][$current_yr_id]['start_year'].' ('.date('m/d/Y',strtotime($fiscal_year_data['fiscal_years'][$current_yr_id]['start_date'])).' - '.date('m/d/Y',strtotime($fiscal_year_data['fiscal_years'][$current_yr_id]['end_date'])).')';
									$quarter_name = 'FY'.($j+1);
								}
								else{	
									$quarter_val =  'H'.($j+1);
									$quarter = 'H'.($j+1).' ('.date('m/d/Y',strtotime($fiscal_year_data['semi_array'][$current_yr_id]['H'.($j+1)]['semi_start_date'])).' - '.date('m/d/Y',strtotime($fiscal_year_data['semi_array'][$current_yr_id]['H'.($j+1)]['semi_end_date'])).')';
									$quarter_name = 'H'.($j+1);
								} 	
	

								if($j == 0){ ?>
									<td style="vertical-align:middle;" rowspan="<?php echo $loop_count;?>"><span class="type_val"><?php echo $app_name = $name_arr[$val]; ?></span></td><?php 
								} ?>
								<td>
									<span><?php echo $quarter; ?></span>
									<input type="hidden" class="" value="<?php echo $actual_qtr; ?>"/>
									<input type="hidden" class="" value="<?php echo $quarter_name; ?>"/>
									<input type="hidden" class="" value="<?php echo $type_arr[$k]; ?>"/>
								</td><?php 
								$quarter_arr[] = $quarter;
								$app_arr[] = $app_name; 
								if($signatures[$current_yr][$quarter_val][$val]['signature']){
									echo '<td class="green_clsk" style="background-color:green !important;border:1px solid #ddd;"></td>';
								}else{
									
									
									
									$score_board = $balls_data['score_board'];
									$atmpt_balls = $balls_data['balls_attempt'];
									$semi_annual_balls = $balls_data['semi_annual_balls'];
									$quarter = 'q'.($j+1);
									
									
									$cls_disable = '';
									$red_button_bal = '<td style="background-color:red !important;border:1px solid #ddd;" class="red_clsk '.$cls_disable.'"></td>'; 
									$bl_button = '<td style="background-color:blue !important;border:1px solid #ddd;" class="blue_clsk '.$cls_disable.'"></td>'; 
									$white_btn = '<td style="background-color:#fff !important;border:1px solid #ddd;" class="white_clsk '.$cls_disable.'"></td>';
									$empty_btn = '<td style="background-color:#014891 !important !important;border:1px solid #ddd;color:#fff;" class="white_clsk '.$cls_disable.'"></td>';

									
										if ($frequency_data[$val]==1){ // Annual - show one column 			
											if($score_board[$val]['q1']>0 || $score_board[$val]['q2']>0 || $score_board[$val]['q3']>0 ||$score_board[$val]['q4']>0 ){
												echo $red_button_bal;
											}
											else{ 
												if($balls_data['balls_attempt']['q1'] == 'blue' || $balls_data['balls_attempt']['q2'] == 'blue' || $balls_data['balls_attempt']['q3'] == 'blue'  || $balls_data['balls_attempt']['q4'] == 'blue' ){echo $bl_button;
												
												
												}
												
												elseif($atmpt_balls[$val]['q1'] == 'red' || $atmpt_balls[$val]['q2'] == 'red' || $atmpt_balls[$val]['q3'] == 'red'  || $atmpt_balls[$val]['q4'] == 'red' ){echo $red_button_bal;}
												
												elseif($atmpt_balls[$val]['q1'] == 'white' || $atmpt_balls[$val]['q2'] == 'white' || $atmpt_balls[$val]['q3'] == 'white'  || $atmpt_balls[$val]['q4'] == 'white' ){echo $white_btn;}
												elseif($atmpt_balls[$val]['q1'] == 'empty' || $atmpt_balls[$val]['q2'] == 'empty' || $atmpt_balls[$val]['q3'] == 'empty'  || $atmpt_balls[$val]['q4'] == 'empty' ){echo $red_button_bal;}
											}				
										} 
										elseif($frequency_data[$val]==2){ // Semi-Annual - show two quarters 
										
											if($quarter == 'q1'){
												$fy_qtr = "q1";
												$nxt_qtr = 'q2';
											}else if($quarter == 'H1'){
												$fy_qtr = "q1";
												$nxt_qtr = 'q2';
												$quarter = 'q1';
											}
											else if($quarter == 'H2'){
												$fy_qtr = "q3";
												$nxt_qtr = 'q4';
												$quarter = 'q2';
											}
											else{
												$fy_qtr = "q3";
												$nxt_qtr = 'q4';
											}
											if($score_board[$val][$fy_qtr]>0 || $score_board[$val][$nxt_qtr]>0){
												echo $red_button_bal;
											}
											else { 
												if($semi_annual_balls[$val]['semi_'.$quarter] == 'blue'){echo $bl_button;}
												
												elseif($semi_annual_balls[$val]['semi_'.$quarter] == 'red'){echo $red_button_bal;}
												
												elseif($semi_annual_balls[$val]['semi_'.$quarter] == 'white'){echo $white_btn;}
												
												elseif($semi_annual_balls[$val]['semi_'.$quarter] == 'empty'){echo $red_button_bal;} 
											} 
										} 
										elseif($frequency_data[$val]==3){ // Quarterly - show all quarters 

											if($score_board[$val][$quarter]>0){
												echo $red_button_bal;
											}
											else{ 
												if($atmpt_balls[$val][$quarter] == 'blue'){echo $bl_button;}
												
												elseif($atmpt_balls[$val][$quarter] == 'white'){echo $white_btn;}
												
												elseif($atmpt_balls[$val][$quarter] == 'red'){
													echo $red_button_bal;}elseif($atmpt_balls[$val][$quarter] == 'empty'){echo $red_button_bal;} else{echo $red_button_bal;}
											}
										}
																		
									
									
									
									
									
								} ?>
								<td><?php 
									if($signatures[$current_yr][$quarter_val][$val]['signature']){
										echo $signatures[$current_yr][$quarter_val][$val]['plan_accepted_by']; 
									}else{
										
									}?>
								</td>
								<td><?php 
									if($signatures[$current_yr][$quarter_val][$val]['signature']){
										echo 'signed';
									}else{
										
									}?>
								</td>
								
								<td><?php 
									echo $signatures[$current_yr][$quarter_val][$val]['plan_accept_date']; ?>
								</td><?php 
								if($signatures[$current_yr][$quarter_val][$val]['cam_signature']){
									echo '<td class="plan_disabletd non-click green_clsk"><img class="img_remove" style="background-color:green !important;border:1px solid #ddd;" src='.$this->config->item(base_url1).'/fronthand/images/green-circle.png'.'></td>';
								}else{
									
									
									$score_board = $balls_data['score_board'];
									$atmpt_balls = $balls_data['balls_attempt'];
									$semi_annual_balls = $balls_data['semi_annual_balls'];
									$quarter = 'q'.($j+1);
									
									
									$cls_disable = '';
									$red_button_bal = '<td style="background-color:red !important;border:1px solid #ddd;" class="red_clsk '.$cls_disable.'"></td>'; 
									$bl_button = '<td style="background-color:blue !important;border:1px solid #ddd;" class="blue_clsk '.$cls_disable.'"></td>'; 
									$white_btn = '<td style="background-color:#fff !important;border:1px solid #ddd;" class="white_clsk '.$cls_disable.'"></td>';
									$empty_btn = '<td style="background-color:#014891 !important !important;border:1px solid #ddd;color:#fff;" class="white_clsk '.$cls_disable.'"></td>';

									
										if ($frequency_data[$val]==1){ // Annual - show one column 			
											if($score_board[$val]['q1']>0 || $score_board[$val]['q2']>0 || $score_board[$val]['q3']>0 ||$score_board[$val]['q4']>0 ){
												echo $red_button_bal;
											}
											else{ 
												if($balls_data['balls_attempt']['q1'] == 'blue' || $balls_data['balls_attempt']['q2'] == 'blue' || $balls_data['balls_attempt']['q3'] == 'blue'  || $balls_data['balls_attempt']['q4'] == 'blue' ){echo $bl_button;
												
												
												}
												
												elseif($atmpt_balls[$val]['q1'] == 'red' || $atmpt_balls[$val]['q2'] == 'red' || $atmpt_balls[$val]['q3'] == 'red'  || $atmpt_balls[$val]['q4'] == 'red' ){echo $red_button_bal;}
												
												elseif($atmpt_balls[$val]['q1'] == 'white' || $atmpt_balls[$val]['q2'] == 'white' || $atmpt_balls[$val]['q3'] == 'white'  || $atmpt_balls[$val]['q4'] == 'white' ){echo $white_btn;}
												elseif($atmpt_balls[$val]['q1'] == 'empty' || $atmpt_balls[$val]['q2'] == 'empty' || $atmpt_balls[$val]['q3'] == 'empty'  || $atmpt_balls[$val]['q4'] == 'empty' ){echo $red_button_bal;}
											}				
										} 
										elseif($frequency_data[$val]==2){ // Semi-Annual - show two quarters 
										
											if($quarter == 'q1'){
												$fy_qtr = "q1";
												$nxt_qtr = 'q2';
											}else if($quarter == 'H1'){
												$fy_qtr = "q1";
												$nxt_qtr = 'q2';
												$quarter = 'q1';
											}
											else if($quarter == 'H2'){
												$fy_qtr = "q3";
												$nxt_qtr = 'q4';
												$quarter = 'q2';
											}
											else{
												$fy_qtr = "q3";
												$nxt_qtr = 'q4';
											}
											if($score_board[$val][$fy_qtr]>0 || $score_board[$val][$nxt_qtr]>0){
												echo $red_button_bal;
											}
											else { 
												if($semi_annual_balls[$val]['semi_'.$quarter] == 'blue'){echo $bl_button;}
												
												elseif($semi_annual_balls[$val]['semi_'.$quarter] == 'red'){echo $red_button_bal;}
												
												elseif($semi_annual_balls[$val]['semi_'.$quarter] == 'white'){echo $white_btn;}
												
												elseif($semi_annual_balls[$val]['semi_'.$quarter] == 'empty'){echo $red_button_bal;} 
											} 
										} 
										elseif($frequency_data[$val]==3){ // Quarterly - show all quarters 

											if($score_board[$val][$quarter]>0){
												echo $red_button_bal;
											}
											else{ 
												if($atmpt_balls[$val][$quarter] == 'blue'){echo $bl_button;}
												
												elseif($atmpt_balls[$val][$quarter] == 'white'){echo $white_btn;}
												
												elseif($atmpt_balls[$val][$quarter] == 'red'){
													echo $red_button_bal;}elseif($atmpt_balls[$val][$quarter] == 'empty'){echo $red_button_bal;} else{echo $red_button_bal;}
											}
										}
									
								}?>
								<td class="plan_disabletd"><?php 
									if($signatures[$current_yr][$quarter_val][$val]['plan_approved_by']){
										echo $signatures[$current_yr]['Q'.($j+1)][$val]['plan_approved_by']; 
									}?>
								</td>
								<td class="plan_disabletd" style="display:none;"><?php 
									if($signatures[$current_yr][$quarter_val][$val]['cam_signature']){	
										echo 'signed';
									}else{
										
									}?>
								</td>
								
								<td class="plan_disabletd"><?php 
									echo $signatures[$current_yr][$quarter_val][$val]['plan_approved_date']; ?>
								</td>
							</tr><?php 
						} 
					} ?>
					<tr style="display:none;"></tr>	
					<tr style="display:none;">
						<td style="display:none;background:#FFEBCD;color:#5c5cd6;font-size:16px;font-weight:bolder;height:40px;vertical-align:middle;text-align:center;border:1px solid #ddd;" colspan="10">What do these colored boxes means?</td>
					</tr>
					<tr>
						<td style="display:none;background:#388B18;border:1px #ddd;font-weight:normal;"></td><td colspan="9" style="display:none;height:30px;vertical-align:middle;border:1px solid #ddd;">Plan is accepted / approved for this time period</td>
					</tr>
					<tr>
						<td style="display:none;background:red;border:1px #ddd;font-weight:normal;"></td>
						<td colspan="10" style="display:none;height:30px;vertical-align:middle;border:1px solid #ddd;">Plan is not accepted / approved for this time period and past due</td>
					</tr>
					<tr>
						<td style="display:none;background:#014891;border:1px #ddd;font-weight:normal;"></td>
						<td colspan="9" style="display:none;height:30px;vertical-align:middle;border:1px solid #ddd;">Plan is pending for this period</td>
					</tr>
					<tr>
						<td style="display:none;background:#fff;border:1px #ddd;border:1px solid #ddd;color:#fff;font-weight:bolder;"></td>
						<td colspan="9" style="display:none;height:30px;vertical-align:middle;border:1px solid #ddd;">Future Period for accepting / approving plans</td>
					</tr>
				</table>
<?php //} ?>





<div class="response_data"></div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.js"></script>
<script type="text/javascript">
	var resize = $('#upload-demo').croppie({
		enableExif: true,
		enableOrientation: true,    
		viewport: { // Default { width: 100, height: 100, type: 'square' } 
			width: 240,
			height: 100,
			type: 'square' //square
		},
		boundary: {
			width: 300,
			height: 300
		}
	});
	$('#upload_signature_img').on('change', function () { 
		$(".rk_no_img").hide();
		$("#upload-demo").show();
		var reader = new FileReader();
		reader.onload = function (e) {
			resize.croppie('bind',{
				url: e.target.result
			}).then(function(){
				console.log('jQuery bind complete');
			});
		}
		reader.readAsDataURL(this.files[0]);
	});

	function upload_signature(){
		$(".overlay_new").show();
		var year = $(".year").val();	
		var qtrs = [] ;
		$('.chk_qtr:checked').each(function() {
			var current_checkbox = $(this).val();
			qtrs.push(current_checkbox);
		});
		resize.croppie('result', {
			type: 'canvas',
			size: 'viewport'
		}).then(function (img) {
			$.ajax({
				url: "<?php echo base_url('plan_accept_approve/accept_plan'); ?>",
				type: "POST",
				data: {
					qtrs:qtrs,
					year:year,
					upload_signature_img:img,
				},
				success: function (data) {
			 
				}
			});
		});
	}
	
	function display_accept_plan_tab(val,type){
		$(".rk_list").removeClass("active");
		$(".rk_list").attr("alt","");
		$(val).addClass("active");
		$(val).attr("alt",1);

		if(type=="draw"){
			$(".custom_draw").show();	
			$(".select_signature").hide();	
			$(".rk_upload_sig").hide();	
			$(".rk_signature_input").prop('checked', false);
			$(".sig_title").text("Sign to Accept Your Plan:");
			$(".sig_title").attr("style","margin-left:0px;");
		}

		if(type=="style"){
			$(".custom_draw").hide();
			$(".rk_upload_sig").hide();		
			$(".select_signature").show();	
			$(".rk_signature_input:first").prop('checked', true);	
			$(".sig_title").text("Select Sign to Accept Your Plan:");
			$(".sig_title").attr("style","margin-left:0px;");
		}

		if(type=="upload"){
			$(".custom_draw").hide();	
			$(".select_signature").hide();	
			$(".rk_upload_sig").show();	
			$(".rk_signature_input").prop('checked', false);
			$(".sig_title").text("Upload Sign to Accept Your Plan:");
			$(".sig_title").attr("style","margin-left:45px;");
		}
	}
	$(document).ready(function () {
		$('.sigPad').signaturePad({drawOnly:true});
		$(".back_plan").show();
	}); 
	
	$(".close_x").click(function(e){    
		$("#pop_wrapper").hide();
		$('.accept_plan').hide();
		showonload();
	});	

	function show_popup(type,quarter,year,application_id){
		$(".publish_error").remove();
		$(".clearButton").trigger('click');
		var start_mth = '<?php echo $forecast_data['start_month'];?>';
		if(start_mth != 1){
			var fy_yr = parseInt(year)+1;
		}else{
			var fy_yr = year;
		}
		$("#pop_wrapper").show();
		$(".type").val(type);
		$(".quarter").val(quarter);
		$(".year").val(year);
		$(".application_id").val(application_id);
		$('.accept_plan').show();
		var partner_name = '<?php echo  $this->session->userdata('partnername') ?>';
		$(".pop_head").text(partner_name+"  Plan  Acceptance and Signature for FY"+fy_yr);
		var qtrs_arr = [];
		var app_arr = [];
		var type_arr = [];
		var quarter_name_arr = [];
		$(".click_btn_class").each(function(){
			var qtr = $(this).closest('td').prev('td').find('.quarter_pop').val();
			var quarter_name = $(this).closest('td').prev('td').find('.quarter_name').val();
			var app = $(this).closest('td').prev('td').prev('td').find('.type_val').text();
			var type_val = $(this).closest('td').prev('td').find('.app_type').val();
			qtrs_arr.push(qtr);
			app_arr.push(app);
			type_arr.push(type_val);
			quarter_name_arr.push(quarter_name);
		});
		$('.accpt_ul li').remove();
		for(var i=0;i<qtrs_arr.length;i++){
			
			var qtr_li = '<li><input id="'+type_arr[i]+'" class="chk_qtr" type="checkbox" name="qtrs[]" value="'+quarter_name_arr[i]+'<>'+type_arr[i]+'"/><label for="'+type_arr[i]+'">'+qtrs_arr[i]+'</label></li>';
			$(".accpt_ul").append(qtr_li);
		}
		
		
	}

	function change_fiscal(){
		var base_url = '<?php echo base_url(); ?>';
		var select_yr_id = $(".acceptPlanDrpdwn option:selected").val();
		var select_yr    = $(".acceptPlanDrpdwn option:selected").attr('data-year');
		
		$.ajax({
			type:'post',
			url:base_url+'plan_accept_approve/accept_approve_plan',
			dataType:'html',
			data:{
				select_yr_id : select_yr_id,
				select_yr : select_yr,
			},
			success:function(data){
				// var div_data = $(data).find(".ajax_res_s").html();
				// $(".ajax_res_s").html(div_data);
				
				var div_data = $(data).find(".main_tble_approve").html();
				$(".main_tble_approve").html(div_data);
				showonload();
			}
		});
	}
	
	function showonload(){
		var partner_name = '<?php echo $this->session->userdata('partnername'); ?>';
		$('.accept_plan').hide();
		$(".non-click").find('img').removeAttr("onclick");
		$(".non-click").find('img').removeClass("click_btn_class");
		$(".non-click").find('.empty_btn').remove();
	}
	showonload();

	function scrollHeightOnload(nameField){
		setTimeout(function(){
			$(nameField).each(function(textarea) {
				var height = $(this)[0].scrollHeight;
				$(this).css({ 'height': height+'px' });
			});
		}, 500);
	
	}
	scrollHeightOnload('textarea');

	function vaidate_data(){
		var upload_signature_val = $("#upload_signature_img").val();
		$('.rk_accpt_popupop').scrollTop(0);
		var error = 0;
		var sign = $(".canvas_div").find(".output").val();
		var chk = $(".chk_qtr:checked").length;
		var signature = $('input[name=signature]:checked').val();
		var draw_signature = $("#draw_signature").attr("alt");
		var upload_signatures = $("#upload_signature").attr("alt");
	
		if(chk == 0){
			$(".error_div").html("<div class='publish_error'>You must make a selection.</div>");
			return false;
		}
	
		if(draw_signature==1 && sign ==""){
			$(".error_div").html("<div class='publish_error'>Please sign the document.</div>");	
			return false;
		}   

		if(upload_signatures==1 && upload_signature_val ==""){
			$(".error_div").html("<div class='publish_error'>Please upload signature.</div>");	
			return false;
		}  	
	   
		if(upload_signatures==1 && upload_signature_val  !=""){
			$(".error_div").html("");   
			upload_signature(); 
			setTimeout(function(){
				$(".upload_signature_form").submit();
			}, 2000);
		}else{
			$(".error_div").html("");	
			$(".sigPad").submit();
		}
	}
	
	$("body").on("click",".logo,.home_link,.back_to_plan",function(){
		var link = $(".header_li:first").find("a").attr("href");
		var home_link = '<?php echo $url; ?>';
		if(home_link != ""){
			$(this).find("a").attr("href",home_link);
		}else{
			$(this).find("a").attr("href",link);
		}
	});
	
	$("body").on("change","#selected_application",function(){
		if($(this).val() == 'application'){
			window.location.href = '<?php echo base_url(); ?>plan_accept_approve/accept_plan';
		}
		
		if($(this).val() == 'marketing_action_plan'){
			window.location.href = '<?php echo base_url(); ?>plan_accept_approve/accept_approve_plan';
		}
		
	});
	
	
</script>