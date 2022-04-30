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

<link href="<?php echo $this->config->item('base_url1'); ?>/design_assets/accept_plan/styles.css" rel="stylesheet" type="text/css" />

<script src="<?php echo $this->config->item('base_url1'); ?>fronthand/ui/js/modernizr.custom.min.js"></script>
<script src="<?php echo $this->config->item('base_url1'); ?>fronthand/ui/js/jquery.signaturepad.min.js"></script><?php 
	$type_arr =   $plan_steps_keys; // 
	$name_arr=$title_app_arr;
	['scorecards','bussiness','account_planning','action_plan', 'marketing_action_plan'];

	$months = array (1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',
		11=>'Nov',12=>'Dec');
	
	
 
	$green_button = '<img id="ninty_dy_img_top" class="clickable_cls ninty_dy_img" src='.$this->config->item(base_url1).'/fronthand/images/green-circle.png'.' onclick="'.$click.'">'; 
	$red_button = '<img id="ninty_dy_img_top" class="clickable_cls ninty_dy_img" src='.$this->config->item(base_url1).'/fronthand/images/red-circle.png'.' onclick="'.$click.'">'; 
	$yellow_button = '<img id="ninty_dy_img_top" class="clickable_cls ninty_dy_img" src='.$this->config->item(base_url1).'/fronthand/images/yl_btm.png'.'>'; 
	$bl_button = '<img id="ninty_dy_img_top" class="clickable_cls ninty_dy_img" src='.$this->config->item(base_url1).'/fronthand/images/blue-circle.png'.'>'; 
	$white_btn = '<img id="ninty_dy_img_top" class="clickable_cls ninty_dy_img" src='.$this->config->item(base_url1).'/fronthand/images/white-circle.png'.'>';

	if($get_started){
		$url = base_url().'home/default';
	}else{
		$url = "";
	} ?>
	
	
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-header align-items-center d-flex">
				<h4 class="card-title mb-0 flex-grow-1 font_theme_color1"><?php echo $this->language_data['planning_plan_accept'] ? $this->language_data['planning_plan_accept'] : "Plan Acceptance and Approval Report"; ?></h4>
				<div class="filter_text"><?php 
					$this->load->view('fronthand/fiscal_dropdown',array('selecterYearDropDown' =>$selecterYearDropDown, 'id' => 'plan_drpdwn', 'class' =>'acceptPlanDrpdwn', 'func_name' => 'change_fiscal();', 'selecterYear' =>$current_yr,'module_name' =>'accept_plan'));?>
				  <form method="post" id="fiscal_year_form" style="display:none;">
				  <input type="text" name="select_yr" value="<?php echo $current_yr; ?>" class="hidden_fiscal_year">
				  </form>
				
				</div>
			</div>
			<div class="show_status_hint task_show_status_hint">
				<?php echo $red_button; ?>
				<div class="hint_text span2">	<?php echo $this->language_data['planning_plan_not_accept'] ? $this->language_data['planning_plan_not_accept'] : "Not Accepted / Not Approved"; ?></div>
			
				<?php echo $green_button; ?>
				<div class="hint_text span2"><?php echo $this->language_data['planning_plan_approved'] ? $this->language_data['planning_plan_approved'] : "Approved / Accepted"; ?></div>
			
				<?php echo $bl_button; ?>
				<div class="hint_text span2"><?php echo $this->language_data['planning_plan_current_pending'] ? $this->language_data['planning_plan_current_pending'] : "Pending in Current Period"; ?></div>
			
				<?php echo $white_btn; ?>
				<div class="hint_text span2"><?php echo $this->language_data['planning_plan_future_pending'] ? $this->language_data['planning_plan_future_pending'] : "Pending in Future Period"; ?></div>
			</div>
			<div class="card-body">
				<div class="ajax_res_s"><?php $get_url = $this->uri->segment(3);?>
		
		<div>
		  <div>
		
		<div class="row">
		<div class="col-12">
		
			<div class="overlay overlay_new" style="display: none;"><?php
				$loder_content = custom_loader('camdashboard');
				$this->load->view('loader',array('loader_data'=> (array)$loder_content,'outer_class'=>'overlay','inner_class'=>'loader')); ?>
			</div>
			<div class="table-responsive">
			
			
			
			
			
				<table class="plan_tbl rk_plan_tbl plan-approve-table">
					
					<!--tr>
						<th colspan="6" class="thFirst bg_colork">Partner Plan Acceptance Status</th>
						<th colspan="4" class="thFirst bg_colork">Partner Plan Approval Status</th>
					</tr-->
					<tr>
						<th class="bg_theme_color2"><?php echo $this->language_data['planning_application'] ? $this->language_data['planning_application'] : "Application"; ?></th>
						
						<th class="accept_period bg_theme_color2 plan-approve-period" style="width:20%;"><?php echo $this->language_data['period'] ? $this->language_data['period'] : "Period"; ?></th>
						
						<th class="bg_theme_color2 plan-approve-status"><?php echo $this->language_data['planning_application_status'] ? $this->language_data['planning_application_status'] : "Acceptance Status"; ?></th>
						<th class="bg_theme_color2"><?php echo $this->language_data['planning_acceptor_name'] ? $this->language_data['planning_acceptor_name'] : "Acceptor Name"; ?></th>
						
						
						<th class="bg_theme_color2 remove_td" style="width:20%;"><?php echo $this->language_data['planning_acceptor_signature'] ? $this->language_data['planning_acceptor_signature'] : "Acceptor Signature"; ?></th>
						<th class="bg_theme_color2" style="display:none;"><?php echo $this->language_data['planning_partner_signature'] ? $this->language_data['planning_partner_signature'] : "Signed By Partner"; ?></th>
						<th class="bg_theme_color2"><?php echo $this->language_data['planning_acceptance_date'] ? $this->language_data['planning_acceptance_date'] : "Acceptance Date"; ?></th>
						
						<th class="plan_disableth bg_theme_color2"><?php echo $this->language_data['planning_approval_status'] ? $this->language_data['planning_approval_status'] : "Approval Status"; ?></th>
						
						<th class="plan_disableth bg_theme_color2"><?php echo ucfirst($this->session->userdata('client_name'));?> <?php echo $this->language_data['planning_approval_name'] ? $this->language_data['planning_approval_name'] : "Approval Name"; ?></th>
						
						<th class="plan_disableth bg_theme_color2 remove_td" style="width:20%;"><?php echo ucfirst($this->session->userdata('client_name'));?> <?php echo $this->language_data['planning_approval_signature'] ? $this->language_data['planning_approval_signature'] : "Approval Signature"; ?></th>
						
						<th class="bg_theme_color2" style="display:none;"><?php echo $this->language_data['planning_signature_by_cam'] ? $this->language_data['planning_signature_by_cam'] : "Signed By CAM"; ?></th>
						
						<th class="plan_disableth bg_theme_color2"><?php echo ucfirst($this->session->userdata('client_name'));?> <?php echo $this->language_data['planning_approval_date'] ? $this->language_data['planning_approval_date'] : "Approval Date"; ?></th>
						
						
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
									$quarter = 'Q'.($j+1).'('.$monthArray['Qtrs'][$j]['start_month'].' '.$monthArray['Qtrs'][$j]['start_year'].' - '.$monthArray['Qtrs'][$j]['end_month'].' '.$monthArray['Qtrs'][$j]['end_year'].')';
			
									$actual_qtr = $name_arr[$val].'   FY'.substr($end_yr,2,2).' Q'.($j+1).'('.$monthArray['Qtrs'][$j]['start_month'].' '.$monthArray['Qtrs'][$j]['start_year'].' - '.$monthArray['Qtrs'][$j]['end_month'].' '.$monthArray['Qtrs'][$j]['end_year'].') ';
			
									$quarter_name = 'Q'.($j+1);
								}
								elseif($frequency_data[$val]== 1){
									$quarter = 'FY('.$months[$forecast_data['start_month']].' '.$strt_yr.' - '.$months[$forecast_data['end_month']].' '.$end_yr.')';
			
									$actual_qtr = $name_arr[$val].'    FY'.substr($end_yr,2,2).' Q'.($j+1).'('.$months[$forecast_data['start_month']].' '.$strt_yr.' - '.$months[$forecast_data['end_month']].' '.$end_yr.') ';
			
									$quarter_name = 'Q1';
								}
								else{
									$quarter = 'Q'.($j+1).'('.$monthArray['Qtrs'][$j*2]['start_month'].' '.$monthArray['Qtrs'][$j*2]['start_year'].' - '.$monthArray['Qtrs'][($j*2)+1]['end_month'].' '.$monthArray['Qtrs'][($j*2)+1]['end_year'].')';
			
									$actual_qtr = $name_arr[$val].'    FY'.substr($end_yr,2,2).' Q'.($j+1).'('.$monthArray['Qtrs'][$j*2]['start_month'].' '.$monthArray['Qtrs'][$j*2]['start_year'].' - '.$monthArray['Qtrs'][($j*2)+1]['end_month'].' '.$monthArray['Qtrs'][($j*2)+1]['end_year'].') ';
			
									$quarter_name = 'Q'.($j+1);
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
								if($signatures[$current_yr]['Q'.($j+1)][$val]['signature']){
									echo '<td class="green_clsk"><img class="img_remove" src='.$this->config->item(base_url1).'/fronthand/images/green-circle.png'.'></td>';
								}else{
									$this->load->view('fronthand/ui/accept_plan/frontend_balls',array('atmpt_balls' => $balls_data['balls_attempt'], 'score_board' => $balls_data['score_board'], 'frequency_data' => $frequency_data, 'semi_annual_balls' => $balls_data['semi_annual_balls'],'val' => $val,'quarter' => 'q'.($j+1),'complete_quarter' => $quarter,'current_yr' => $current_yr,'disable_val' => "" ));
								} ?>
								<td><?php 
									if($signatures[$current_yr]['Q'.($j+1)][$val]['signature']){
										echo $signatures[$current_yr]['Q'.($j+1)][$val]['plan_accepted_by']; 
									}else{
										
									}?>
								</td>
								
								<td class="remove_td"><?php 
									if($signatures[$current_yr]['Q'.($j+1)][$val]['signature']){
										$explode_signature = explode(".",$signatures[$current_yr]['Q'.($j+1)][$val]['signature']);
										if(in_array("png",$explode_signature)){ ?>
											<img disabled class="signature_cls img_remove" src="<?php echo $this->config->item('base_url1'); ?>uploads/signatures/<?php echo $signatures[$current_yr]['Q'.($j+1)][$val]['signature']; ?>"><?php 
										}else{
											echo '<span class="'.$signatures[$current_yr]['Q'.($j+1)][$val]['signature'].'">'.$signatures[$current_yr]['Q'.($j+1)][$val]['plan_accepted_by'].'</span>';
										}
									} ?>
								</td>
								<td><?php 
									echo $signatures[$current_yr]['Q'.($j+1)][$val]['plan_accept_date']; ?>
								</td><?php 
								if($signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature']){
									echo '<td class="plan_disabletd non-click green_clsk"><img class="img_remove" src='.$this->config->item(base_url1).'/fronthand/images/green-circle.png'.'></td>';
								}else{
									$this->load->view('fronthand/ui/accept_plan/frontend_balls',array('atmpt_balls' => $balls_data['balls_attempt'],'score_board' => $balls_data['score_board'],'frequency_data' => $frequency_data,'semi_annual_balls' => $balls_data['semi_annual_balls'],'val' => $val,'quarter' => 'q'.($j+1),'complete_quarter' => $quarter,'current_yr' => $current_yr,'disable_val' => "yes" ));
								}?>
								<td class="plan_disabletd"><?php 
									if($signatures[$current_yr]['Q'.($j+1)][$val]['plan_approved_by']){
										echo $signatures[$current_yr]['Q'.($j+1)][$val]['plan_approved_by']; 
									}?>
								</td>
								<td class="plan_disabletd remove_td"><?php 
									if($signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature']){
										$explode_cam_signature = explode(".",$signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature']);
										if(in_array("png",$explode_cam_signature)){ ?>
											<img disabled class="signature_cls img_remove" src="<?php echo $this->config->item('base_url1'); ?>uploads/signatures/<?php echo $signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature']; ?>"><?php 
										} else {
											echo '<span class="'.$signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature'].'">'.$signatures[$current_yr]['Q'.($j+1)][$val]['plan_approved_by'].'</span>';
										}

									} ?>
								</td>
								<td class="plan_disabletd"><?php 
									echo $signatures[$current_yr]['Q'.($j+1)][$val]['plan_approved_date']; ?>
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
			
		</div>	
		</div>	
		</div>	
		</div>	
		
		

		
		
		
		
		</div>	
		</div>	
		</div>	
		</div>	
		</div>	
		</div>	
	</div>
</div>


<!--  popup -->
<div id="pop_wrapper" style="display:none;" >
	<div class="innter_pop acceptPlanPop" >
		
		<div class="row">
			<div class="col-md-12 rk_accpt_popupop">
				<h1 class="pop_head header_color"></h1>
				<span class="close_x">x</span> 
				<div class="error_div"></div>
				
				<form method="post" action="<?php echo base_url(); ?>plan_accept_approve/accept_plan" class="sigPad" enctype="multipart/form-data">
					<div class="accept_quarters">
						<table class="plan_tbl rk_plan_tbl table-bordered" style="width:100%">
						<thead>
						<tr>
						<th class="bg_theme_color2"><?php echo $this->language_data['planning_application'] ? $this->language_data['planning_application'] : "Application"; ?></th>
						<th class="bg_theme_color2"><?php echo $this->language_data['period'] ? $this->language_data['period'] : "Period"; ?></th>
						</tr>
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
							<tr class="accept_tr"><?php 
								if($frequency_data[$val] == 3){
									$quarter = 'Q'.($j+1).'('.$monthArray['Qtrs'][$j]['start_month'].' '.$monthArray['Qtrs'][$j]['start_year'].' - '.$monthArray['Qtrs'][$j]['end_month'].' '.$monthArray['Qtrs'][$j]['end_year'].')';
			
									$actual_qtr = $name_arr[$val].'   FY'.substr($end_yr,2,2).' Q'.($j+1).'('.$monthArray['Qtrs'][$j]['start_month'].' '.$monthArray['Qtrs'][$j]['start_year'].' - '.$monthArray['Qtrs'][$j]['end_month'].' '.$monthArray['Qtrs'][$j]['end_year'].') ';
			
									$quarter_name = 'Q'.($j+1);
								}
								elseif($frequency_data[$val]== 1){
									$quarter = 'FY('.$months[$forecast_data['start_month']].' '.$strt_yr.' - '.$months[$forecast_data['end_month']].' '.$end_yr.')';
			
									$actual_qtr = $name_arr[$val].'    FY'.substr($end_yr,2,2).' Q'.($j+1).'('.$months[$forecast_data['start_month']].' '.$strt_yr.' - '.$months[$forecast_data['end_month']].' '.$end_yr.') ';
			
									$quarter_name = 'Q1';
								}
								else{
									$quarter = 'Q'.($j+1).'('.$monthArray['Qtrs'][$j*2]['start_month'].' '.$monthArray['Qtrs'][$j*2]['start_year'].' - '.$monthArray['Qtrs'][($j*2)+1]['end_month'].' '.$monthArray['Qtrs'][($j*2)+1]['end_year'].')';
			
									$actual_qtr = $name_arr[$val].'    FY'.substr($end_yr,2,2).' Q'.($j+1).'('.$monthArray['Qtrs'][$j*2]['start_month'].' '.$monthArray['Qtrs'][$j*2]['start_year'].' - '.$monthArray['Qtrs'][($j*2)+1]['end_month'].' '.$monthArray['Qtrs'][($j*2)+1]['end_year'].') ';
			
									$quarter_name = 'Q'.($j+1);
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
								
								</div>
 
						<!--ul class="accpt_ul"></ul><br-->
						<div class="border-accept-div">
						<div class="rk_accept_div">
						
						
						
						
						
						

						
							<ul>
								<li id="select_signature" class="rk_list active" onclick="display_accept_plan_tab(this,'style');" title="Select Signature" alt="1"><?php echo  $this->language_data['planning_select_signature'] ? $this->language_data['planning_select_signature'] : "Select Signature"; ?></li>

								<li id="draw_signature" class="rk_list" onclick="display_accept_plan_tab(this,'draw');"  title="Draw"><?php echo  $this->language_data['planning_create_signature'] ? $this->language_data['planning_create_signature'] : "Create your own Signature"; ?></li>

								<li id="upload_signature" class="rk_list" onclick="display_accept_plan_tab(this,'upload');" title="Upload Signature"><?php echo  $this->language_data['planning_upload_signature'] ? $this->language_data['planning_upload_signature'] : "Upload Signature"; ?></li>
							</ul>
							<!--<span class="rk_fname_t"><strong>Logged in User: </strong></span>
							<span class="rk_fname"><?php 
								/* if($this->session->userdata('is_cam_login')) {
									echo $this->session->userdata('crm_acc_name'); 
								} else {
									echo $this->session->userdata('username');	
								} */ ?>
							</span>-->
						</div>
						<!--<div class="sig_title">Select Sign to Accept Your Plan:</div>-->
					
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
					</div>
					
					
					<div class="accept_btns">
						<input type="button" class="accept_plan save_document" onclick="vaidate_data();" value="<?php echo $this->language_data['save'] ? $this->language_data['save'] : "Save"; ?>"/>
						<button class=clearButton><?php echo $this->language_data['planning_reset'] ? $this->language_data['planning_reset'] : "Reset"; ?></button>
					</div>
				</form>
				
				
				
				<form method="post" action="<?php echo base_url(); ?>plan_accept_approve/accept_plan" class="upload_signature_form" enctype="multipart/form-data">
				
				  <input type="hidden" name="select_yr" value="<?php echo $current_yr; ?>" class="hidden_fiscal_year">
				  
				</form>
			</div>
		</div>
		<div class="pop_clr"></div>
	
	
	
	
	
	
	
	
	
	</div>      
</div>
<!-- /popup end  -->

<!--Export Plan Aproval frontend---><?php
if(!empty($_POST['type'])){ ?>
			<div class="table-responsive">
				<table class="export_plan_tbl rk_plan_tbl" style="display:none;">
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
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork"><?php echo $this->language_data['planning_application'] ? $this->language_data['planning_application'] : "Application"; ?></th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="accept_period bg_colork">Period</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork ">Plan Acceptance Status </th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork">Partner Plan Acceptance Name</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;">Partner Acceptance Signature</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork" style="display:none;">Signed By Partner</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork">Partner Acceptance Date</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class=" bg_colork">Plan Approval Status</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork"><?php echo ucfirst($this->session->userdata('client_name'));?> Approval Name</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork remove_td"><?php echo ucfirst($this->session->userdata('client_name'));?> Approval Signature</th>
						<th style="background-color:#014891;color:#fff; border: 1px solid #ddd;" class="bg_colork" style="display:none;">Signed By CAM</th>
						<th style="background-color:#014891;color:#fff;" class="remove_td bg_colork"><?php echo ucfirst($this->session->userdata('client_name'));?> Approval Date</th>
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
									$quarter = 'Q'.($j+1).'('.$monthArray['Qtrs'][$j]['start_month'].' '.$monthArray['Qtrs'][$j]['start_year'].' - '.$monthArray['Qtrs'][$j]['end_month'].' '.$monthArray['Qtrs'][$j]['end_year'].')';
			
									$actual_qtr = $name_arr[$val].'   FY'.substr($end_yr,2,2).' Q'.($j+1).'('.$monthArray['Qtrs'][$j]['start_month'].' '.$monthArray['Qtrs'][$j]['start_year'].' - '.$monthArray['Qtrs'][$j]['end_month'].' '.$monthArray['Qtrs'][$j]['end_year'].') ';
			
									$quarter_name = 'Q'.($j+1);
								}
								elseif($frequency_data[$val]== 1){
									$quarter = 'FY('.$months[$forecast_data['start_month']].' '.$strt_yr.' - '.$months[$forecast_data['end_month']].' '.$end_yr.')';
			
									$actual_qtr = $name_arr[$val].'    FY'.substr($end_yr,2,2).' Q'.($j+1).'('.$months[$forecast_data['start_month']].' '.$strt_yr.' - '.$months[$forecast_data['end_month']].' '.$end_yr.') ';
			
									$quarter_name = 'Q1';
								}
								else{
									$quarter = 'Q'.($j+1).'('.$monthArray['Qtrs'][$j*2]['start_month'].' '.$monthArray['Qtrs'][$j*2]['start_year'].' - '.$monthArray['Qtrs'][($j*2)+1]['end_month'].' '.$monthArray['Qtrs'][($j*2)+1]['end_year'].')';
			
									$actual_qtr = $name_arr[$val].'    FY'.substr($end_yr,2,2).' Q'.($j+1).'('.$monthArray['Qtrs'][$j*2]['start_month'].' '.$monthArray['Qtrs'][$j*2]['start_year'].' - '.$monthArray['Qtrs'][($j*2)+1]['end_month'].' '.$monthArray['Qtrs'][($j*2)+1]['end_year'].') ';
			
									$quarter_name = 'Q'.($j+1);
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
								if($signatures[$current_yr]['Q'.($j+1)][$val]['signature']){
									echo '<td class="green_clsk" style="background-color:green !important;border:1px solid #ddd;"><img class="img_remove" src='.$this->config->item(base_url1).'/fronthand/images/green-circle.png'.'></td>';
								}else{
									$this->load->view('fronthand/ui/accept_plan/frontend_balls',array('atmpt_balls' => $balls_data['balls_attempt'], 'score_board' => $balls_data['score_board'], 'frequency_data' => $frequency_data, 'semi_annual_balls' => $balls_data['semi_annual_balls'],'val' => $val,'quarter' => 'q'.($j+1),'complete_quarter' => $quarter,'current_yr' => $current_yr,'disable_val' => "" ));
								} ?>
								<td><?php 
									if($signatures[$current_yr]['Q'.($j+1)][$val]['signature']){
										echo $signatures[$current_yr]['Q'.($j+1)][$val]['plan_accepted_by']; 
									}else{
										
									}?>
								</td>
								<td><?php 
									if($signatures[$current_yr]['Q'.($j+1)][$val]['signature']){
										echo 'signed';
									}else{
										
									}?>
								</td>
								<td class="remove_td"><?php 
									if($signatures[$current_yr]['Q'.($j+1)][$val]['signature']){
										$explode_signature = explode(".",$signatures[$current_yr]['Q'.($j+1)][$val]['signature']);
										if(in_array("png",$explode_signature)){ ?>
											<img disabled class="signature_cls img_remove" src="<?php echo $this->config->item('base_url1'); ?>uploads/signatures/<?php echo $signatures[$current_yr]['Q'.($j+1)][$val]['signature']; ?>"><?php 
										}else{
											echo '<span class="'.$signatures[$current_yr]['Q'.($j+1)][$val]['signature'].'">'.$signatures[$current_yr]['Q'.($j+1)][$val]['plan_accepted_by'].'</span>';
										}
									} ?>
								</td>
								<td><?php 
									echo $signatures[$current_yr]['Q'.($j+1)][$val]['plan_accept_date']; ?>
								</td><?php 
								if($signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature']){
									echo '<td class="plan_disabletd non-click green_clsk"><img class="img_remove" style="background-color:green !important;border:1px solid #ddd;" src='.$this->config->item(base_url1).'/fronthand/images/green-circle.png'.'></td>';
								}else{
									$this->load->view('fronthand/ui/accept_plan/frontend_balls',array('atmpt_balls' => $balls_data['balls_attempt'],'score_board' => $balls_data['score_board'],'frequency_data' => $frequency_data,'semi_annual_balls' => $balls_data['semi_annual_balls'],'val' => $val,'quarter' => 'q'.($j+1),'complete_quarter' => $quarter,'current_yr' => $current_yr,'disable_val' => "yes" ));
								}?>
								<td class="plan_disabletd"><?php 
									if($signatures[$current_yr]['Q'.($j+1)][$val]['plan_approved_by']){
										echo $signatures[$current_yr]['Q'.($j+1)][$val]['plan_approved_by']; 
									}?>
								</td>
								<td class="plan_disabletd" style="display:none;"><?php 
									if($signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature']){	
										echo 'signed';
									}else{
										
									}?>
								</td>
								<td class="plan_disabletd remove_td"><?php 
									if($signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature']){
										$explode_cam_signature = explode(".",$signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature']);
										if(in_array("png",$explode_cam_signature)){ ?>
											<img disabled class="signature_cls img_remove" src="<?php echo $this->config->item('base_url1'); ?>uploads/signatures/<?php echo $signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature']; ?>"><?php 
										} else {
											echo '<span class="'.$signatures[$current_yr]['Q'.($j+1)][$val]['cam_signature'].'">'.$signatures[$current_yr]['Q'.($j+1)][$val]['plan_approved_by'].'</span>';
										}

									} ?>
								</td>
								<td class="plan_disabletd"><?php 
									echo $signatures[$current_yr]['Q'.($j+1)][$val]['plan_approved_date']; ?>
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
<?php } ?>

<div class="response_data"></div>

<input type="hidden" class="hidden_partner_name" value="<?php echo  $this->session->userdata('partnername'); ?>">
<input type="hidden" class="hidden_url" value="<?php echo  $url; ?>">


<?php 
$planning_popup_heading  =  $this->language_data['planning_popup_heading'] ? $this->language_data['planning_popup_heading'] : "Plan Acceptance and Signature for FY"; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.js"></script>
<script src="<?php echo $this->config->item('base_url1'); ?>design_assets/accept_plan/accept_plan.js?v=1.73342"></script>



<script type="text/javascript">





	function show_popup(type,quarter,year,application_id){
		$(".publish_error").remove();
		$("body").addClass('model-open');
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
		$(".pop_head").text(partner_name+"  <?php echo $planning_popup_heading; ?>"+fy_yr);
		var qtrs_arr = [];
		var app_arr = [];
		var type_arr = [];
		var quarter_name_arr = [];
		
		
	}






	

</script>