<?php 
require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
?>
<script class="jsbin" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
<link rel="stylesheet" href="style/styles.css"  />
<script>
    function ajax_call(data,url,type,op,div){
                //console.log(url);
		$.ajax({
			data: data,
			url: url,
			type: type,
			contentType: false,
			processData: false,
			success: function (response){
				
                            document.getElementById(div).innerHTML = response;
                            if(op === 'create_activity'){
                                
                                edit_activity(document.getElementById('activityid').value);
                            }
			},
                        error: function(request,status,errorThrown) {
                            console.log(errorThrown);
                       }
		});	
		
	}
        
    function serialize(form) {
        
        var formData = new FormData();
		
		if (!form || form.nodeName !== "FORM") {
			return;
		}
		var i, j;
		for (i = form.elements.length - 1; i >= 0; i = i - 1) {
			if (form.elements[i].name === "") {
				continue;
			}
			switch (form.elements[i].nodeName) {
			case 'INPUT':
				switch (form.elements[i].type) {
				case 'text':
				case 'number':
				case 'email':
				case 'date':
				case 'datetime-local':
				case 'tel':
				case 'hidden':
				case 'password':
				case 'button':
				case 'reset':
				//case 'submit':
					formData.append(form.elements[i].name,form.elements[i].value);
					break;
				case 'checkbox':
				case 'radio':
					if (form.elements[i].checked) {
						formData.append(form.elements[i].name,encodeURIComponent(form.elements[i].value));
					}
					break;
				}
				break;
			
			case 'SELECT':
				switch (form.elements[i].type) {
				case 'select-one':
					formData.append(form.elements[i].name,form.elements[i].value);
					break;
				case 'select-multiple':
                                        var multiselect;
					for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
						if (form.elements[i].options[j].selected) {
							multiselect += '&'+form.elements[i].options[j].value;
						}
					}
                                        
                                        formData.append('category_name',multiselect);
					break;
				}
				break;
			/*case 'BUTTON':
				switch (form.elements[i].type) {
				case 'reset':
				case 'submit':
				case 'button':
					formData.append(form.elements[i].name,encodeURIComponent(form.elements[i].value));
					break;
				}
				break;*/
			}
		}
		return formData;
	}
	
	
	//read forms
	//Luis Sola
	//XX022020
	function read_form(e,form,operation){
		
		e.preventDefault();
	
		var formData = serialize(form);
		
		formData.append('operation',operation);
		
		ajax_call(formData,'data.php','POST',operation,"primer_contenedor");
		
	}
        
        
        function config_activity(idactivity,sceneid){
            
            var formData = new FormData();
            var operation='config_activity';
            formData.append('operation','config_activity');
            formData.append('scene',sceneid);
            formData.append('id',idactivity);
             
            ajax_call(formData,'data.php','POST',operation,"primer_contenedor");
        }
        
        function select_competence(requestid){
            //console.log(requestid);
            var operation='config_activity_backup';
            var formData = new FormData();
            formData.append('operation','config_activity_backup');
            formData.append('requestid',requestid);
            
            ajax_call(formData,'data.php','POST',operation,"primer_contenedor");
        }
        
        function select_query(select,buttom,form){
            
            var selectedOption = select.options[select.selectedIndex];
            var formData = new FormData();
            formData.append('operation',selectedOption.value);
            formData.append('form',form);
            formData.append('buttom',buttom);
            var operation = 'selectedOption.value';
            ajax_call(formData,'query.php','POST',operation,"consultas");
            
        }

    function edit_activity(idactivity){
        var URLdomain = window.location.host;
        window.open('http://'+URLdomain+'/moodle/course/modedit.php?update='+idactivity,'_self');
        if(document.getElementById('quizid')){
            window.open('http://'+URLdomain+'/moodle/mod/quiz/edit.php?cmid='+idactivity, '_blank');
        }
    }    
    
    
    
</script>
<?php

 
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


//Definición del URL de la página
$PAGE->set_url('/blocks/tfg/block_data.php', array());
//Recuperamos el parametro curso
$courseid = required_param('courseid', PARAM_INT);
//Definición del contexto en base al curso que venimos
$PAGE->set_context(context_course::instance($courseid));
//Definición del layout 
$PAGE->set_pagelayout('course');
//Definición del titulo
$PAGE->set_title("Your title");


$_SESSION['course_id']=$courseid;

if (has_capability('block/tfg:createactivity',  $PAGE->context)) {
    
    if(isset($_GET['request'])){
        
        //Delete request from 2 tables 
        $DB->delete_records("tfg_satisfy",array('requestid'=>$_GET['request']));
    
        $DB->delete_records("tfg_teacher_comp_request",array('id'=>$_GET['request']));
        
    }
    
    
    $PAGE->set_heading("Creación de una actividad en base a una serie de competencias");
    echo $OUTPUT->header();
    
    $form = 'competences_teacher';
    
    $buttom = 'Sugerir actividad';
    $text  = 'A continuación seleccione un número de competencias a cubrir en base al siguiente criterio::<br><br>';
    
    $options = array('bt'=>'Básico','rq'=>'En base a las peticiones del estudiante','cc'=>'En base a cuantas veces ha sido cubierta');
    

}else if(has_capability('block/tfg:askactivity',  $PAGE->context)){
     $PAGE->set_heading("Pedir competencia");
     echo $OUTPUT->header();
   
    $form = 'competences_student';
    
    $buttom = 'Pedir competencia';
    $text  = 'A continuación seleccione un número de competencias que quieres que sean cubiertas en base al siguiente criterio:<br><br>';
    
    $options = array('bt'=>'Básico','lc'=>'La competencias menos solicitada');
    
 }
    

    $sql = "Select * from tfg_teacher_comp_request where usermodified = ".$USER->id." and courseid = ".$courseid." and myactivity_itemid is null";
    
    echo '<span id="primer_contenedor">';
        
    if($DB->record_exists_sql($sql)){
       $request = $DB->get_records_sql($sql); 
       echo '<script type="text/javascript">select_competence('.$request[key($request)]->id.');</script>';
    }
    else{
       echo html_writer::div($text, "", null);
           
       
        $attrs = array(
        'onchange' => "select_query(this,'".$buttom."','".$form."')"
        );
      echo html_writer::select($options, 'order', '0', null, $attrs);
             
       echo '<span id="consultas">';
            $sql_compentence = "
                SELECT DISTINCT
                     (c.id),
                     c.name,
                     c.description
                 FROM
                     `tfg_appears` a
                 JOIN `tfg_competence` c ON
                     a.competenceid = c.id";
            $competences = $DB->get_records_sql($sql_compentence);

             
             echo 
             '<form onsubmit="read_form(event,this,\''.$form.'\')" method="POST" name="competences">
                 <div style="float:left">';
                     foreach($competences as $competence){
                         echo'

                         <div class="b_activities">
                             <div class="chk_comp '.$competence->id.'"><input title = "'.$competence->name.'" type="checkbox" name="'.$competence->id.'" value="'.$competence->id.'" /></div>
                             <div class="activity_text"><strong>'.$competence->name.':</strong><br>'.$competence->description.'</div>
                         </div>';

                     }


             echo'</div>

                 <input name="suggest_activity" value = "'.$buttom.'" type="submit"></input> 

             </form>
        </span>       
';
    }
   echo '</span>';

echo $OUTPUT->footer();

?>