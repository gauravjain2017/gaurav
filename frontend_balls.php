<?php 
// $name_arr = array("scorecard"=>"Scorecard","business_plan"=>"Sales & Profit Forecast","marketing_plan"=>"Marketing Plan","action_plan"=>"Action Plan","qbr"=>"QBR");
$name_arr=$title_app_arr;

$click = "show_popup('".$name_arr[$val]."','".$complete_quarter."','".$current_yr."','".$val."')";
// debug($disable_val);

if($disable_val=="yes"){
	$cls_disable="plan_disabletd non-click";
	
}
else{
	$cls_disable="";
}
$green_button = '<td class="green_clsk '.$cls_disable.'"><img class="clickable_cls img_remove" src='.$this->config->item(base_url1).'/fronthand/images/green-circle.png'.' onclick="'.$click.'"></td>'; 
$red_button = '<td class="red_clsk '.$cls_disable.'"><img class="clickable_cls click_btn_class img_remove " src='.$this->config->item(base_url1).'/fronthand/images/red-circle.png'.' onclick="'.$click.'"></td>'; 

$bl_button = '<td class="blue_clsk '.$cls_disable.'"><img class="clickable_cls click_btn_class img_remove" src='.$this->config->item(base_url1).'/fronthand/images/blue-circle.png'.' onclick="'.$click.'"></td>'; 
$white_btn = '<td class="white_clsk '.$cls_disable.'"><img class="clickable_cls click_btn_class img_remove" src='.$this->config->item(base_url1).'/fronthand/images/white-circle.png'.' onclick="'.$click.'"></td>';
$empty_btn = '<td class="white_clsk '.$cls_disable.'"><img class="clickable_cls click_btn_class empty_btn img_remove" src='.$this->config->item(base_url1).'/fronthand/images/empty-circle.png'.' onclick="'.$click.'"></td>';

// if($val == "scorecards" || $val == "marketing_action_plan" || $val == "action_plan") {


	if ($frequency_data[$val]==1){ // Annual - show one column 			
		if($score_board[$val]['q1']>0 || $score_board[$val]['q2']>0 || $score_board[$val]['q3']>0 ||$score_board[$val]['q4']>0 ){
			echo $red_button;
		}
		else{ 
			if($atmpt_balls[$val]['q1'] == 'blue' || $atmpt_balls[$val]['q2'] == 'blue' || $atmpt_balls[$val]['q3'] == 'blue'  || $atmpt_balls[$val]['q4'] == 'blue' ){echo $bl_button;}
			
			elseif($atmpt_balls[$val]['q1'] == 'red' || $atmpt_balls[$val]['q2'] == 'red' || $atmpt_balls[$val]['q3'] == 'red'  || $atmpt_balls[$val]['q4'] == 'red' ){echo $red_button;}
			
			elseif($atmpt_balls[$val]['q1'] == 'white' || $atmpt_balls[$val]['q2'] == 'white' || $atmpt_balls[$val]['q3'] == 'white'  || $atmpt_balls[$val]['q4'] == 'white' ){echo $white_btn;}
			elseif($atmpt_balls[$val]['q1'] == 'empty' || $atmpt_balls[$val]['q2'] == 'empty' || $atmpt_balls[$val]['q3'] == 'empty'  || $atmpt_balls[$val]['q4'] == 'empty' ){echo $red_button;}
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
			echo $red_button;
		}
		else { 
			if($semi_annual_balls[$val]['semi_'.$quarter] == 'blue'){echo $bl_button;}
			
			elseif($semi_annual_balls[$val]['semi_'.$quarter] == 'red'){echo $red_button;}
			
			elseif($semi_annual_balls[$val]['semi_'.$quarter] == 'white'){echo $white_btn;}
			
			elseif($semi_annual_balls[$val]['semi_'.$quarter] == 'empty'){echo $red_button;} 
		} 
	} 
	elseif($frequency_data[$val]==3){ // Quarterly - show all quarters 

		if($score_board[$val][$quarter]>0){
			echo $red_button;
		}
		else{ 
			if($atmpt_balls[$val][$quarter] == 'blue'){echo $bl_button;}elseif($atmpt_balls[$val][$quarter] == 'white'){echo $white_btn;}elseif($atmpt_balls[$val][$quarter] == 'red'){echo $red_button;}elseif($atmpt_balls[$val][$quarter] == 'empty'){echo $red_button;} else{echo $red_button;}
		}
	}

?>