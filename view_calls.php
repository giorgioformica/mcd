<?php


function mcdPlanningViews_callback()
{  
	if(!empty($_POST['view'])){
$view = $_POST['view'];

		echo FrmViewsDisplaysController::get_shortcode( array( 'id' => $view, 'filter' => 1) );

   
	}
	else{
		echo  0;
	}
	 die();
}
add_action( 'wp_ajax_mcdPlanningViews', 'mcdPlanningViews_callback' );



function render_view_update_callback()
{  
$id = $_POST['id'];
$view = $_POST['view'];
echo FrmViewsDisplaysController::get_shortcode( array( 'id' => $view, 'filter' => 1, 'itemNum'=> $id) );
die();
}
add_action( 'wp_ajax_callViews', 'render_view_update_callback' );

function render_module_callback()
{  
$id = $_POST['id'];
$elem = $_POST['elem'];
echo FrmFormsController::get_form_shortcode( array( 'id' => $id ) );
die();
}
add_action( 'wp_ajax_renderModule', 'render_module_callback' );








function mcdPlanningStandard_callback( )
{  
	global $wpdb;
	$day_names=["lunedì","martedì","mercoledì","giovedì","venerdì","sabato","domenica"];
		$pdvs =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT A.meta_value AS hl, C.meta_value AS region
FROM `rvk_frm_item_metas`AS A
LEFT JOIN `rvk_frm_item_metas`AS C ON A.item_id = C.item_id AND C.field_id = 2111
WHERE A.field_id = 2089
")
		);
	$pdvs_reg =[];
foreach ($pdvs as $pdv){
	$pdvs_reg[$pdv->hl] = $pdv->region;
}
	$programs =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT * FROM `rvk_mcd_base_plan` ORDER BY hour ASC
")
		);
	$operators =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT * FROM `rvk_mcd_base_operators`
")
		);
	$day_reg =[];
foreach($programs as $program){
	if(!in_array($pdvs_reg[$program->hl],$day_reg[$program->day]))
		$day_reg[$program->day][]=$pdvs_reg[$program->hl];	 
			 }
	 ob_start();
?>
<div id="controls-box"></div>
<div id="standard_plan_container">
    <ul class="content-box">
        <? for($i = 0; $i <= 6; $i++) {
		$local_pdvs =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT B.meta_value AS hl,A.meta_value AS name
FROM `rvk_frm_item_metas`AS A
RIGHT JOIN `rvk_frm_item_metas`AS B ON A.item_id = B.item_id AND B.field_id = 2089 AND B.meta_value NOT IN(
SELECT hl
FROM `rvk_mcd_base_plan`
WHERE day = $i
)
WHERE A.field_id = 2102
")
			);
		 ?>

            <li class="head-box">
                <p id="<? echo $i ?>" class="week_day">
                    <? echo $day_names[$i] ?>
                </p>
                <div class="modify-box">
                    <a class="open_lock" href="javascript:void(0)" style="margin-right:5px;"
                        onclick="openClose(this)"><img src="https://my-genius.it/includes/img/modifica_bk.png"
                            width="20" height="20" /></a>
                    <a class="open_lock" href="javascript:void(0)" style="margin-right:5px; display:none;"
                        onclick="openClose(this)"><img src="https://my-genius.it/includes/img/lock_icon.png"
                            width="20" /></a>
                </div>
            </li>
            <ul class="detail_box" id="details_<? echo $i ?>">
                <?
			 foreach($day_reg[$i] as $region){ 
	?>
                <li class="region-box" id="region_<? echo $region ?>_<? echo $i ?>">
                    <h5>
                        <? echo $region ?>
                    </h5>
                </li>
                <?
			 foreach($programs as $program){ 
				 if($pdvs_reg[$program->hl] == $region && $program->day == $i){
				$time_arr =	explode(":",  $program->hour);
				$time = $time_arr[0].':'.$time_arr[1];
	?>
                <li class="pdv-box" id="<? echo $program->id ?>">
                    <img src="https://my-genius.it/includes/img/mcd_logo.png" width="40" height="auto" class=""
                        style="margin: 0 10px" />
                    <p>
                        <? echo $program->pdv ?>
                    </p>
                    <p style="margin: 0 10px" id="time_<? echo $program->id ?>">
                        <? echo $time ?> <span class="pdv_week_name">
                            <? echo substr($day_names[$i],0,3) ?>
                        </span>
                    </p>
                    <form id="modify_<? echo $program->id ?>" style="display:none" class="modify_pdv" method="post">
                        <input type="hidden" name="action" value="base_plan_modify_item" />
                        <input type="hidden" name="type" value="base" />
                        <input type="hidden" name="id" value="<? echo $program->id ?>" />
                        <input type="hidden" name="day" value="<? echo $i ?>" />
                        <input type="time" id="new_baseProgram_time_<? $program->id ?>" name="time" class="programTime"
                            value="<? echo $time ?>" />
                        <div class="form_btn_container">
                            <a onclick="modify_pdv_base_program(this)" class="modify_btn">SALVA</a>
                            <a class="cancel_btn" onclick="cancelModify('<? echo $program->id  ?>','base')">ANNULLA</a>
                        </div>
                    </form>
                    <div class="btn_container" style="display:none">
                        <button class="change_btn"
                            onclick="openModify(event,'<? echo $program->id  ?>','base')">MODIFICA</button>
                        <button class="change_btn"
                            onclick="deleteElem(this,'<? echo $program->id  ?>',<? echo $i  ?>,'base')">ELIMINA</button>
                    </div>
                </li>


                <div class="operator-box">

                    <?
			 foreach($operators as $operator){ 
				 if($operator->plan_id == $program->id){
				$time_op_arr =	explode(":",  $operator->hour);
				$time_op = $time_op_arr[0].':'.$time_op_arr[1];
	?>
                    <li class="op-box" id="<? echo $operator->id ?>">
                        <p class="op_name">
                            <? echo $operator->name ?>
                        </p>
                        <p style="margin: 0 10px" id="time_<? echo $operator->id ?>">
                            <? echo $time_op ?>
                        </p>
                        <form id="modify_<? echo $operator->id ?>" style="display:none" class="modify_op" method="post">
                            <input type="hidden" name="action" value="base_operator_modify_item" />
                            <input type="hidden" name="type" value="base" />
                            <input type="hidden" name="day" value="<? echo $i ?>" />
                            <input type="hidden" name="id" value="<? echo $operator->id ?>" />
                            <input type="time" id="new_baseProgram_op_time_<? $operator->id ?>" name="time"
                                class="programTime" value="<? echo $time_op ?>" />

                            <div class="form_btn_container">
                                <a onclick="modify_pdv_base_program(this)" class="modify_btn">SALVA</a>
                                <a class="cancel_btn"
                                    onclick="cancelModify('<? echo $operator->id  ?>','op_base')">ANNULLA</a>
                            </div>

                        </form>
                        <div class="btn_container" style="display:none">
                            <button class="change_btn"
                                onclick="openModify(event,'<? echo $operator->id  ?>','base')">MODIFICA</button>
                            <button class="change_btn"
                                onclick="deleteElem(this,'<? echo $operator->id  ?>',<? echo $i  ?>,'op_base')">ELIMINA</button>
                        </div>
                    </li>
                    <?			 
				 }
			 }
	?>
                    <li class="op_<? echo  $program->id ?> new-op" id="new_op<? echo  $program->id ?>"
                        style="display:none">
                        <input type="hidden" id="new_baseProgram_store_id_<? echo $program->id ?>"
                            name="new_baseProgram_store_id" value="<? echo $program->id ?>" />
                        <input type="hidden" id="new_baseProgram_op_day_<? echo $program->id ?>"
                            name="new_baseProgram_op_day" value="<? echo $i ?>" />
                        <select id="op_new_<? echo $program->id ?>" class="chosen op"
                            data-placeholder="Seleziona l'Operatore...">
                            <option></option>
                            <?
$local_op =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT B.meta_value AS matr,A.meta_value AS name
FROM `rvk_frm_item_metas`AS A
RIGHT JOIN `rvk_frm_item_metas`AS B ON A.item_id = B.item_id AND B.field_id = 2076 AND B.meta_value NOT IN(
SELECT matr FROM `rvk_mcd_base_operators` WHERE plan_id = '$program->id' OR ( day = $i AND hour BETWEEN SUBTIME('$program->hour', '2:00:00') AND ADDTIME('$program->hour', '2:00:00') 
)
    )
WHERE A.field_id = 2084
")
			);
	foreach($local_op as $op)	{	
		echo	'<option value="'.$op->matr.'">'.$op->name.'</option>';
				
	}
?>
                        </select>
                        <input type="time" id="new_baseProgram_op_time_<? echo $program->id ?>"
                            name="new_baseProgram_op_time" step="900" class="programTime"
                            value="<? echo $program->hour ?>" />
                        <div class="btn_container" style="display:none">
                            <button class="add_btn" id="new_baseProgram_op_btn_<? echo $program->id ?>"
                                value="<? echo $program->id ?>"
                                onclick="saveToBaseProgram(this.id,'op_base')">SALVA</button>
                            <a class="cancel_btn" onclick="toogleAddBox('<? echo $program->id ?>','op')">ANNULLA</a>
                        </div>
                    </li>
                    <li class="add-operator" style="display:none" id="add_op<? echo $program->id ?>"
                        onclick="toogleAddBox('<? echo $program->id ?>','op')"></li>
                </div>
                <?			 
				 }
			 }
	
	
			 }
	?>

                <li class="details_<? echo $i ?> new-box" id="new_<? echo $i ?>" style="display:none;">
                    <input type="hidden" id="new_baseProgram_day_<? echo $i ?>" name="new_baseProgram_day"
                        value="<? echo $i ?>">
                    <select id="pdv_new_<? echo $i ?>" class="chosen pdv" data-placeholder="Scegli il Pdv...">
                        <option></option>
                        <?
	foreach($local_pdvs as $pdv)	{	
		echo	'<option value="'.$pdv->hl.'">'.$pdv->name.'</option>';
				
	}
?>
                    </select>
                    <input type="time" id="new_baseProgram_time_<? echo $i ?>" name="new_baseProgram_time" step="900"
                        class="programTime">
                    <div class="btn_container" style="display:none">
                        <button class="add_btn" id="new_baseProgram_btn_<? echo $i ?>"
                            onclick="saveToBaseProgram(this.id,'base')">SALVA</button><a class="cancel_btn"
                            onclick="toogleAddBox(<? echo $i ?>,'pdv')">ANNULLA</a>
                    </div>
                </li>

                <li class="add-box" style="display:none" id="add_<? echo $i ?>"
                    onclick="toogleAddBox(<? echo $i ?>,'pdv')"></li>

            </ul>

        <?
	 }
	?>
    </ul>
    <div style="clear:both;"></div>
</div>

<?php

	echo ob_get_clean();
	 die();
}
add_action( 'wp_ajax_mcdPlanningStandard', 'mcdPlanningStandard_callback' );




















function mcdPlanningWeek_callback(){
	global $wpdb;
	$current_date = date("Y-m-d");
	$dates = $_POST['dates'];
	$monday = $_POST['start'];
	$sunday = $_POST['end'];
	if(!$dates){
	$monday = date('Y-m-d', strtotime('monday this week'));
	$sunday = date('Y-m-d', strtotime('sunday this week +1 day'));
	$dates = [];
	$datesIso = [];
	$period = new DatePeriod(
     new DateTime($monday),
     new DateInterval('P1D'),
     new DateTime($sunday)
);
	foreach ($period as $key => $value) {
    $dates[]=$value->format('d/m/Y') ; 
	$datesIso[]=$value->format('Y-m-d') ; 
}
$sunday = date('Y-m-d', strtotime('sunday this week'));
	} else {
		$datesIso = $dates;
		$dates = [];
		foreach ($datesIso as $key => $date) {
    		$dates[] = \DateTime::createFromFormat('Y-m-d',$date)->format('d/m/Y') ; 
		}
	}
$weekNum = intval(date('W',strtotime($monday))) > 9 ? intval(date('W',strtotime($monday))) : '0' . intval(date('W',strtotime($monday)));
$pdvs =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT A.meta_value AS hl, C.meta_value AS region
FROM `rvk_frm_item_metas`AS A
LEFT JOIN `rvk_frm_item_metas`AS C ON A.item_id = C.item_id AND C.field_id = 2111
WHERE A.field_id = 2089
")
		);
	$pdvs_reg =[];
foreach ($pdvs as $pdv){
	$pdvs_reg[$pdv->hl] = $pdv->region;
}
	$programs =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT * FROM `rvk_mcd_plan` WHERE date BETWEEN '$monday' AND '$sunday' ORDER BY hour ASC
")
		);
	$uniqueDates =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT DISTINCT date FROM `rvk_mcd_plan` WHERE date BETWEEN '$monday' AND '$sunday'
")
		);
	$uniqueHl =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT hl FROM `rvk_mcd_plan` WHERE date BETWEEN '$monday' AND '$sunday'
")
		);
	$uniqueStDates =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT DISTINCT day FROM `rvk_mcd_base_plan`
")
		);
	$uniqueStHl =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT hl FROM `rvk_mcd_base_plan`
")
		);
	$operators =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT * FROM `rvk_mcd_plan_operators` WHERE date BETWEEN '$monday' AND '$sunday'
")
		);
	$day_reg =[];
foreach($programs as $program){
	if(!in_array($pdvs_reg[$program->hl],$day_reg[$program->day]))
		$day_reg[$program->day][]=$pdvs_reg[$program->hl];	 
			 }
	$create_btn_txt = count($uniqueDates) == 0 ? 'GENERA' : 'COMPLETA';
	ob_start();
	?>
<div id="controls-box">
    <div class="generator_btns">
        <? if (count($uniqueDates) != count($uniqueStDates) || count($uniqueHl) != count($uniqueStHl) )
	{ ?>
        <button class="create_week_btn" onclick="createWeek()">
            <? echo $create_btn_txt ?> SETTIMANA
        </button>
        <? if(!empty($uniqueDates)){ ?> <button class="delete_week_btn" onclick="deleteWeek()">CANCELLA
            SETTIMANA</button>
        <? } ?>
        <? } else { ?>
        <button class="delete_week_btn" onclick="deleteWeek()">CANCELLA SETTIMANA</button>
        <? } ?>
    </div>
    <input type='week' id='weeklyDatePicker' value="<? echo intval(date('Y',strtotime($monday))) .'-W'. $weekNum ?>"
        min="2022-W50" />

</div>

<div id="plan_container">


    <? if(!$programs) {?>
    <div class="norecord" style="margin:20px"></div>
    <? } else { ?>

    <div id="timer-bar"></div>
    <ul class="content-box">
        <? for($i = 0; $i <= 6; $i++) {
		$local_pdvs =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT B.meta_value AS hl,A.meta_value AS name
FROM `rvk_frm_item_metas`AS A
RIGHT JOIN `rvk_frm_item_metas`AS B ON A.item_id = B.item_id AND B.field_id = 2089 AND B.meta_value NOT IN(
SELECT hl
FROM `rvk_mcd_base_plan`
WHERE day = $i
)
WHERE A.field_id = 2102
")
			);
		 ?>
        <ul style="margin-top:10px">
            <li class="head-box" id="<? echo $datesIso[$i] ?>">
                <p id="<? echo $i ?>" class="week_day">
                    <? echo $dates[$i] ?>
                </p>
                <div class="modify-box">
                    <a class="open_lock" href="javascript:void(0)" style="margin-right:5px;"
                        onclick="openClose(this)"><img src="https://my-genius.it/includes/img/modifica_bk.png"
                            width="20" height="20" /></a>
                    <a class="open_lock" href="javascript:void(0)" style="margin-right:5px; display:none;"
                        onclick="openClose(this)"><img src="https://my-genius.it/includes/img/lock_icon.png"
                            width="20" /></a>
                </div>
            </li>
            <ul class="detail_box" id="details_<? echo $i ?>">
                <?
			 foreach($day_reg[$i] as $region){ 
	?>
                <li class="region-box" id="region_<? echo $region ?>_<? echo $i ?>">
                    <h5>
                        <? echo $region ?>
                    </h5>
                </li>
                <?
			 foreach($programs as $program){ 
				 if($pdvs_reg[$program->hl] == $region && $program->day == $i){
				$time_arr =	explode(":",  $program->hour);
				$time = $time_arr[0].':'.$time_arr[1];
				$program_date = \DateTime::createFromFormat('Y-m-d',$program->date)->format('Y-m-d');
				$pdvConfirmed ='';
				$archived_pdv ='';
				if($program_date >= $current_date){
					$pdvConfirmed = $program->status? 'confirmed':'';
				} else {
					$archived_pdv = $program->status > 1 ? 'archived-pdv' : '';
				}
	?>
                <li class="pdv-box week <? echo $pdvConfirmed ?> <? echo $archived_pdv ?>" id="<? echo $program->id ?>">
                    <img src="https://my-genius.it/includes/img/mcd_logo.png" width="40" height="auto" class=""
                        style="margin: 0 10px" />
                    <p>
                        <? echo $program->pdv ?>
                    </p>
                    <p style="margin: 0 10px" id="time_<? echo $program->id ?>">
                        <? echo $time ?>
                    </p>
                    <form id="modify_<? echo $program->id ?>" style="display:none" class="modify_pdv" method="post">
                        <input type="hidden" name="action" value="base_plan_modify_item" />
                        <input type="hidden" name="type" value="plan" />
                        <input type="hidden" name="id" value="<? echo $program->id ?>" />
                        <input type="hidden" name="day" value="<? echo $i ?>" />
                        <input type="time" id="new_baseProgram_time_<? $program->id ?>" name="time" class="programTime"
                            value="<? echo $time ?>" />
                        <div class="form_btn_container">
                            <a onclick="modify_pdv_base_program(this)" class="modify_btn">SALVA</a>
                            <a class="cancel_btn" onclick="cancelModify('<? echo $program->id  ?>','plan')">ANNULLA</a>
                        </div>
                    </form>
                    <? 
		$archived_icon = $program->status > 1 ? 'archived-icon' : 'waiting-icon';
		if($program_date < $current_date){ ?>
                    <span class="report-icon <? echo $archived_icon ?>" title="Archivia Report">
                        <ion-icon name="albums-outline"></ion-icon>
                    </span>
                    <? } ?>
                    <div class="btn_container" style="display:none">
                        <button class="change_btn"
                            onclick="openModify(event,'<? echo $program->id  ?>','plan')">MODIFICA</button>
                        <button class="change_btn"
                            onclick="deleteElem(this,'<? echo $program->id  ?>',<? echo $i  ?>,'plan')">ELIMINA</button>
                    </div>
                </li>


                <div class="operator-box">

                    <?
			 foreach($operators as $operator){ 
				 if($operator->plan_id == $program->id){
				$time_op_arr =	explode(":",  $operator->hour);
				$time_op = $time_op_arr[0].':'.$time_op_arr[1];
				$display =  $operator->status ? 'display:none' : 'display:block';
				$noDisplay = $operator->status ? 'display:block' : 'display:none';
				$archived_op ='';
				if($program_date >= $current_date){
				$opConfirmed = $operator->status ? 'confirmed' : '';
				}else{
					$archived_op = $operator->status > 3 ? '' : 'waiting-op';
				}
	?>
                    <li class="op-box <? echo $opConfirmed ?> <? echo $archived_op ?>" id="<? echo $operator->id ?>">
                        <p class="op_name">
                            <? echo $operator->name ?>
                        </p>
                        <p style="margin: 0 10px" id="time_<? echo $operator->id ?>">
                            <? echo $time_op ?>
                        </p>
                        <form id="modify_<? echo $operator->id ?>" style="display:none" class="modify_op" method="post">
                            <input type="hidden" name="action" value="base_operator_modify_item" />
                            <input type="hidden" name="type" value="plan" />
                            <input type="hidden" name="day" value="<? echo $i ?>" />
                            <input type="hidden" name="id" value="<? echo $operator->id ?>" />
                            <input type="time" id="new_baseProgram_op_time_<? $operator->id ?>" name="time"
                                class="programTime" value="<? echo $time_op ?>" />

                            <div class="form_btn_container">
                                <a onclick="modify_pdv_base_program(this)" class="modify_btn">SALVA</a>
                                <a class="cancel_btn"
                                    onclick="cancelModify('<? echo $operator->id  ?>','op_plan')">ANNULLA</a>
                            </div>

                        </form>
                        <? if($program_date >= $current_date){ 
			?>
                        <div class="confirmation_box">
                            <img src="https://my-genius.it/includes/img/confirmed.png" width="30"
                                id="confirmed_<? echo $operator->id ?>" style="<? echo $noDisplay ?>"
                                onclick="confirmOperator(this,'<? echo $operator->id ?>','<? echo $program->id ?>',0)" />
                            <img src="https://my-genius.it/includes/img/not_confirmed.png" width="30"
                                id="not_confirmed_<? echo $operator->id ?>" style="<? echo $display ?>"
                                onclick="confirmOperator(this,'<? echo $operator->id ?>','<? echo $program->id ?>',1)" />
                        </div>
                        <? 
												 } 
			?>
                        <div class="btn_container" style="display:none">
                            <button class="change_btn"
                                onclick="openModify(event,'<? echo $operator->id  ?>','plan')">MODIFICA</button>
                            <button class="change_btn"
                                onclick="deleteElem(this,'<? echo $operator->id  ?>',<? echo $i  ?>,'op_plan')">ELIMINA</button>
                        </div>
                    </li>
                    <?			 
				 }
			 }
	?>
                    <li class="op_<? echo  $program->id ?> new-op" id="new_op<? echo  $program->id ?>"
                        style="display:none">
                        <input type="hidden" id="new_baseProgram_store_id_<? echo $program->id ?>"
                            name="new_baseProgram_store_id" value="<? echo $program->id ?>" />
                        <input type="hidden" id="new_baseProgram_op_day_<? echo $program->id ?>"
                            name="new_baseProgram_op_day" value="<? echo $i ?>" />
                        <select id="op_new_<? echo $program->id ?>" class="chosen op"
                            data-placeholder="Seleziona l'Operatore...">
                            <option></option>
                            <?
$local_op =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT B.meta_value AS matr,A.meta_value AS name
FROM `rvk_frm_item_metas`AS A
RIGHT JOIN `rvk_frm_item_metas`AS B ON A.item_id = B.item_id AND B.field_id = 2076 AND B.meta_value NOT IN(
SELECT matr FROM `rvk_mcd_plan_operators` WHERE plan_id = '$program->id' OR ( day = $i AND hour BETWEEN SUBTIME('$program->hour', '2:00:00') AND ADDTIME('$program->hour', '2:00:00') 
)
    )
WHERE A.field_id = 2084
")
			);
	foreach($local_op as $op)	{	
		echo	'<option value="'.$op->matr.'">'.$op->name.'</option>';
				
	}
?>
                        </select>
                        <input type="time" id="new_baseProgram_op_time_<? echo $program->id ?>"
                            name="new_baseProgram_op_time" step="900" class="programTime"
                            value="<? echo $program->hour ?>" />
                        <div class="btn_container" style="display:none">
                            <button class="add_btn" id="new_baseProgram_op_btn_<? echo $program->id ?>"
                                value="<? echo $program->id ?>"
                                onclick="saveToBaseProgram(this.id,'op_plan')">SALVA</button>
                            <a class="cancel_btn" onclick="toogleAddBox('<? echo $program->id ?>','op')">ANNULLA</a>
                        </div>
                    </li>
                    <li class="add-operator" style="display:none" id="add_op<? echo $program->id ?>"
                        onclick="toogleAddBox('<? echo $program->id ?>','op')"></li>
                </div>
                <?			 
				 }
			 }
	
	
			 }
	?>

                <li class="details_<? echo $i ?> new-box" id="new_<? echo $i ?>" style="display:none;">
                    <input type="hidden" id="new_baseProgram_day_<? echo $i ?>" name="new_baseProgram_day"
                        value="<? echo $i ?>">
                    <select id="pdv_new_<? echo $i ?>" class="chosen pdv" data-placeholder="Scegli il Pdv...">
                        <option></option>
                        <?
	foreach($local_pdvs as $pdv)	{	
		echo	'<option value="'.$pdv->hl.'">'.$pdv->name.'</option>';
				
	}
?>
                    </select>
                    <input type="time" id="new_baseProgram_time_<? echo $i ?>" name="new_baseProgram_time" step="900"
                        class="programTime">
                    <div class="btn_container" style="display:none">
                        <button class="add_btn" id="new_baseProgram_btn_<? echo $i ?>"
                            onclick="saveToBaseProgram(this.id,'plan')">SALVA</button><a class="cancel_btn"
                            onclick="toogleAddBox(<? echo $i ?>,'pdv')">ANNULLA</a>
                    </div>
                </li>

                <li class="add-box" style="display:none" id="add_<? echo $i ?>"
                    onclick="toogleAddBox(<? echo $i ?>,'pdv')"></li>

            </ul>
        </ul>
        <?
	 }
	?>
    </ul>
    <div style="clear:both;"></div>





    <? } ?>


</div>
<?php
	echo ob_get_clean();
	die();
}
add_action( 'wp_ajax_mcdPlanningWeek', 'mcdPlanningWeek_callback' );



function mcdPlanningDay_callback(){
	ob_start();
	
	?>
<div id="controls-box">
    <button class="create_week_btn">GENERA GIORNO</button>
    <input type='date' id='dailyDatePicker' value="" />

</div>
<div id="plan_container">
    <?
	for($i = 0; $i <= 24; $i++){
		$h= $i<10 ? '0'. $i : $i;
?>

    <div class="hour_container">
        <div class="day_hour_tag" style="">
            <? echo $h ?>:00
        </div>
    </div>
    <?
	}
?>
</div>
<?php
	
	echo ob_get_clean();
	die();
}
add_action( 'wp_ajax_mcdPlanningDay', 'mcdPlanningDay_callback' );




























function reloadProgram($i){
		global $wpdb;
	$day_names=["lunedì","martedì","mercoledì","giovedì","venerdì","sabato","domenica"];
		$pdvs =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT A.meta_value AS hl, C.meta_value AS region
FROM `rvk_frm_item_metas`AS A
LEFT JOIN `rvk_frm_item_metas`AS C ON A.item_id = C.item_id AND C.field_id = 2111
WHERE A.field_id = 2089
")
		);
	$pdvs_reg =[];
foreach ($pdvs as $pdv){
	$pdvs_reg[$pdv->hl] = $pdv->region;
}
	$programs =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT * FROM `rvk_mcd_base_plan` ORDER BY created_at ASC
")
		);
	$operators =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT * FROM `rvk_mcd_base_operators`
")
		);
	$day_reg =[];
foreach($programs as $program){
	if(!in_array($pdvs_reg[$program->hl],$day_reg[$program->day]))
		$day_reg[$program->day][]=$pdvs_reg[$program->hl];	 
			 }
			$local_pdvs =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT B.meta_value AS hl,A.meta_value AS name
FROM `rvk_frm_item_metas`AS A
RIGHT JOIN `rvk_frm_item_metas`AS B ON A.item_id = B.item_id AND B.field_id = 2089 AND B.meta_value NOT IN(
SELECT hl
FROM `rvk_mcd_base_plan`
WHERE day = $i
)
WHERE A.field_id = 2102
")
			);
		 ob_start();
	foreach($day_reg[$i] as $region){ 
	?>
<li class="region-box" id="region_<? echo $region ?>_<? echo $i ?>">
    <h5>
        <? echo $region ?>
    </h5>
</li>
<?
			 foreach($programs as $program){ 
				 if($pdvs_reg[$program->hl] == $region && $program->day == $i){
				$time_arr =	explode(":",  $program->hour);
				$time = $time_arr[0].':'.$time_arr[1];
	?>
<li class="pdv-box" id="<? echo $program->id ?>">
    <img src="https://my-genius.it/includes/img/mcd_logo.png" width="40" height="auto" class=""
        style="margin: 0 10px" />
    <p>
        <? echo $program->pdv ?>
    </p>
    <p style="margin: 0 10px" id="time_<? echo $program->id ?>">
        <? echo $time ?> <span class="pdv_week_name">
            <? echo substr($day_names[$i],0,3) ?>
        </span>
    </p>
    <form id="modify_<? echo $program->id ?>" style="display:none" class="modify_pdv" method="post">
        <input type="hidden" name="action" value="base_plan_modify_item" />
        <input type="hidden" name="type" value="base" />
        <input type="hidden" name="day" value="<? echo $i ?>" />
        <input type="hidden" name="id" value="<? echo $program->id ?>" />
        <input type="time" id="new_baseProgram_time_<? $program->id ?>" name="time" class="programTime"
            value="<? echo $time ?>" />
        <div class="form_btn_container">
            <a onclick="modify_pdv_base_program(this)" class="modify_btn">SALVA</a>
            <a class="cancel_btn" onclick="cancelModify('<? echo $program->id  ?>','base')">ANNULLA</a>
        </div>
    </form>
    <div class="btn_container">
        <button class="change_btn" onclick="openModify(event,'<? echo $program->id  ?>','base')">MODIFICA</button>
        <button class="change_btn"
            onclick="deleteElem(this,'<? echo $program->id  ?>',<? echo $i  ?>,'base')">ELIMINA</button>
    </div>
</li>


<div class="operator-box">

    <?
			 foreach($operators as $operator){ 
				 if($operator->plan_id == $program->id){
				$time_op_arr =	explode(":",  $operator->hour);
				$time_op = $time_op_arr[0].':'.$time_op_arr[1];
	?>
    <li class="op-box" id="<? echo $operator->id ?>">
        <p class="op_name">
            <? echo $operator->name ?>
        </p>
        <p style="margin: 0 10px" id="time_<? echo $operator->id ?>">
            <? echo $time_op ?>
        </p>
        <form id="modify_<? echo $operator->id ?>" style="display:none" class="modify_op" method="post">
            <input type="hidden" name="action" value="base_operator_modify_item" />
            <input type="hidden" name="type" value="base" />
            <input type="hidden" name="day" value="<? echo $i ?>" />
            <input type="hidden" name="id" value="<? echo $operator->id ?>" />
            <input type="time" id="new_baseProgram_op_time_<? $operator->id ?>" name="time" class="programTime"
                value="<? echo $time_op ?>" />

            <div class="form_btn_container">
                <a onclick="modify_pdv_base_program(this)" class="modify_btn">SALVA</a>
                <a class="cancel_btn" onclick="cancelModify('<? echo $operator->id  ?>','op_base')">ANNULLA</a>
            </div>

        </form>
        <div class="btn_container">
            <button class="change_btn" onclick="openModify(event,'<? echo $operator->id  ?>','base')">MODIFICA</button>
            <button class="change_btn"
                onclick="deleteElem(this,'<? echo $operator->id  ?>',<? echo $i  ?>,'op_base')">ELIMINA</button>
        </div>
    </li>
    <?			 
				 }
			 }
	?>
    <li class="op_<? echo  $program->id ?> new-op" id="new_op<? echo  $program->id ?>" style="display:none">
        <input type="hidden" id="new_baseProgram_store_id_<? echo $program->id ?>" name="new_baseProgram_store_id"
            value="<? echo $program->id ?>" />
        <input type="hidden" id="new_baseProgram_op_day_<? echo $program->id ?>" name="new_baseProgram_op_day"
            value="<? echo $i ?>" />
        <select id="op_new_<? echo $program->id ?>" class="chosen op" data-placeholder="Seleziona l'Operatore...">
            <option></option>
            <?
$local_op =  
		$wpdb->get_results( 
			$wpdb->prepare("
SELECT B.meta_value AS matr,A.meta_value AS name
FROM `rvk_frm_item_metas`AS A
RIGHT JOIN `rvk_frm_item_metas`AS B ON A.item_id = B.item_id AND B.field_id = 2076 AND B.meta_value NOT IN(
SELECT matr FROM `rvk_mcd_base_operators` WHERE plan_id = '$program->id' OR ( day = $i AND hour BETWEEN SUBTIME('$program->hour', '2:00:00') AND ADDTIME('$program->hour', '2:00:00') 
)
    )
WHERE A.field_id = 2084
")
			);
	foreach($local_op as $op)	{	
		echo	'<option value="'.$op->matr.'">'.$op->name.'</option>';
				
	}
?>
        </select>
        <input type="time" id="new_baseProgram_op_time_<? echo $program->id ?>" name="new_baseProgram_op_time"
            step="900" class="programTime" value="<? echo $program->hour ?>" />
        <div class="btn_container">
            <button class="add_btn" id="new_baseProgram_op_btn_<? echo $program->id ?>" value="<? echo $program->id ?>"
                onclick="saveToBaseProgram(this.id,'op_base')">SALVA</button>
            <a class="cancel_btn" onclick="toogleAddBox('<? echo $program->id ?>','op')">ANNULLA</a>
        </div>
    </li>
    <li class="add-operator" id="add_op<? echo $program->id ?>" onclick="toogleAddBox('<? echo $program->id ?>','op')">
    </li>
</div>
<?			 
				 }
			 }
		}
	?>

<li class="details_<? echo $i ?> new-box" id="new_<? echo $i ?>" style="display:none;">
    <input type="hidden" id="new_baseProgram_day_<? echo $i ?>" name="new_baseProgram_day" value="<? echo $i ?>">
    <select id="pdv_new_<? echo $i ?>" class="chosen pdv" data-placeholder="Scegli il Pdv...">
        <option></option>
        <?
	foreach($local_pdvs as $pdv)	{	
		echo	'<option value="'.$pdv->hl.'">'.$pdv->name.'</option>';
				
	}
?>
    </select>
    <input type="time" id="new_baseProgram_time_<? echo $i ?>" name="new_baseProgram_time" step="900"
        class="programTime">
    <div class="btn_container">
        <button class="add_btn" id="new_baseProgram_btn_<? echo $i ?>"
            onclick="saveToBaseProgram(this.id,'base')">SALVA</button><a class="cancel_btn"
            onclick="toogleAddBox(<? echo $i ?>,'pdv')">ANNULLA</a>
    </div>
</li>

<li class="add-box" id="add_<? echo $i ?>" onclick="toogleAddBox(<? echo $i ?>,'pdv')"></li>
<?
		echo ob_get_clean();
}