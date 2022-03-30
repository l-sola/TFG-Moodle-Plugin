<?php



require_once('../../config.php');

defined('MOODLE_INTERNAL') || die();

//Workshop(?)
require_once "{$CFG->dirroot}/lib/filelib.php";
require_once "{$CFG->dirroot}/lib/editorlib.php";

require_once "{$CFG->dirroot}/course/lib.php";
require_once "{$CFG->dirroot}/course/modlib.php";

 //Quizz question
require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->dirroot . '/question/import_form.php');
require_once($CFG->dirroot . '/question/format.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');




function question_category_select_menu_block($contexts,$activityid){
    global $DB;
    switch($activityid){
        case 'A001':
            $qtype = 'multichoice';
            break;
        case 'A002':
            $qtype = 'truefalse';
            break;
        case 'A003':
            $qtype = 'shortanswer';
            break;
        case 'A004':
            $qtype = 'numerical';
            break;
        case 'A005':
            $qtype = 'essay';
            break;
        case 'A006':
            $qtype = 'match';
            break;
        case 'A007':
            $qtype = 'ddmarker';
            break;
        case 'A008':
            $qtype = 'calculatedsimple';
            break;
        case 'A009':
            $qtype = 'gapselect';
            break;
        case 'A010':
            $qtype = 'ddimageortext';
            break;
    }
    if($qtype<>''){
        $categoriesarray = question_category_options($contexts, false, 0,false, -1);

        $choose = '';
        $selected = '';
        $options = array();
        $options2 = array();
        $n_max=0;
                
        foreach ($categoriesarray as $group => $opts) {
            
            $options2[] = array($group => $opts);
            
            
            foreach (array_keys($opts) as $category){
                
                $category_context = explode(',',$category);
               
                $contextid = $category_context[1];
                
                
                
                $sql_question= "SELECT count(qc.parent)
                                         FROM {question} q
                                         JOIN {question_categories} qc ON qc.id = q.category
                                        WHERE qc.contextid = '$contextid' AND q.parent = 0 AND q.qtype='".$qtype."' AND q.category=".$category_context[0];
                
                
                if(($n=($DB->count_records_sql($sql_question)))>0){
                    
                    //Elimino la ultima posicion el caracter ')'
                    $opts[$category] = rtrim($opts[$category], ')');
                    
                    //Elimino los números
                    while(is_numeric($opts[$category][strlen($opts[$category])-1])){
                        $opts[$category] = rtrim($opts[$category], $opts[$category][strlen($opts[$category])-1]);
                    }
                    //Elimino la ultima posicion el caracter '(' tras haber eliminado todos los numeros
                    $opts[$category] = rtrim($opts[$category], '(');
                    
                    //Añado cuantas actividades hay de ese tipo de verdad
                    $n_max += $n;
                    $opts[$category] .= '('.$n.')';
                }else{
                   
                    unset($opts[$category]);
                }
            } 
            
            //Si no tenemos subcategoria disponible no guardamos el grupo principal
            if(count($opts)!=0){
                
                $options[] = array($group => $opts);
            }

        }
        
        if(count($options)>0){
            
            echo  '<br><br>Se ha detectado que tienes actividades de este tipo en las siguientes categorias:<br>';
            echo html_writer::label(get_string('questioncategory', 'core_question'), 'id_movetocategory', false, array('class' => 'accesshide'));
            $attrs = array(
                'id' => 'id_movetocategory',
                'class' => 'custom-select',
                'data-action' => 'toggle',
                'data-togglegroup' => 'qbank',
                'data-toggle' => 'action',
                'disabled' => false,
                'multiple' => 'multiple',
                'required' => 'required'
            );
            echo html_writer::select($options, 'category_name', $selected, $choose, $attrs);
           
            echo'<br><br>Introduce cuantas preguntas selecciono del banco de preguntas (como máximo <strong>'.$n_max.' preguntas</strong>): <input style="width:60px" required="required" type="number" name="n_question" min="1" max="'.$n_max.'" value="1"/><br>';
        }
        else{
           
            echo  '<br><br>No se ha detectado que disponga de actividades de este tipo en su banco de preguntas, si desea continuar le crearemos'
            . 'una actividad de este tipo por defecto en una de la siguientes categorias que seleccione:<br>';
            
            echo html_writer::label(get_string('questioncategory', 'core_question'), 'id_movetocategory', false, array('class' => 'accesshide'));
            $attrs = array(
                'id' => 'id_movetocategory',
                'class' => 'custom-select',
                'data-action' => 'toggle',
                'data-togglegroup' => 'qbank',
                'data-toggle' => 'action',
                'disabled' => false,
                'required' => 'required'
            );
            echo html_writer::select($options2, 'category_name', $selected, $choose, $attrs).'<br><br>';
        }
    }
}

function forum($section){
    
    //Forum
    $myForum = new stdClass();
    $myForum->modulename='forum';
    $myForum->course = $_SESSION['course_id'];
    $myForum->section = $section;   
    $myForum->visible = 0;
    $myForum->forcesubscribe = 1;
    $myForum->format = 1;
    $myForum->cmidnumber = null;
    $myForum->grade_forum = 0;
    
    return $myForum;
}

function create_activity($idactivity,$section,$create_activity,$categories,$n_question){
    global $DB;
    $quiz =false;
    $myactivity= new stdClass();
    $data_base = new stdClass();
    
    $sql = "Select titlename from `tfg_myactivity` where id='".$idactivity."'";  
    $name = $DB->get_records_sql($sql);
    $activity_title = $name[key($name)]->titlename;
   
    $sql = "Select howto from `tfg_scene` where id='".$_SESSION['scene_id']."'";  
    $howto_sql = $DB->get_records_sql($sql);
    $howto = $howto_sql[key($howto_sql)]->howto;
    
    switch ($idactivity){
        
        case 'A001':
        case 'A002':
        case 'A003':
        case 'A004':
        case 'A005':
        case 'A006':
        case 'A007':
        case 'A008':
        case 'A009':
        case 'A010':
                   
            //create an object with all of the neccesary information to build a quiz
            $myactivity->modulename='quiz';
            $myactivity->name = 'Cuestionario creado a través del bloque';
            $myactivity->introformat = 0;
            $myactivity->quizpassword = '';
            $myactivity->course = $_SESSION['course_id'];
            $myactivity->section = $section;
            $myactivity->timeopen = 0;
            $myactivity->timeclose = 0;
            $myactivity->timelimit = 0;
            $myactivity->grade = 0;
            $myactivity->sumgrades = 0;
            $myactivity->gradeperiod = 0;
            $myactivity->attempts = 1;
            $myactivity->preferredbehaviour = 'deferredfeedback';
            $myactivity->attemptonlast = 0;
            $myactivity->shufflequestions = 0;
            $myactivity->grademethod = 1;
            $myactivity->questiondecimalpoints = 2;
            $myactivity->visible = 0;
            $myactivity->questionsperpage = 1;
            $myactivity->introeditor = array('text' => '********************************************<br>La actividad que está usted viendo ahora mismo se ha creado de forma automática gracias al bloque añadido en el curso de forma no visible para el estudiante, es conveniente que lea con detalle la siguiente información respecto a cómo configurar la actividad y por último active la opción visible de la actividad para poder ser usada por el alumnado y borre el texto para configurar la pregunta de la actividad. Al ser una actividad cuestionario se a abierto otra pestaña con las preguntas asociadas a este cuestionario <br>********************************************<br><br> El siguiente cuestionario se ha creado automáticamente para albergar un conjunto de preguntas de un tipo en concreto, por favor diríjase a la ventana siguiente para configurar las preguntas insertadas en el mismo. <br><br> A continuación edite los parámetros más importantes para el cuestionario como puede ser la temporalización, calificación y retroalimentación y subscripción.','format' => 1);

            //all of the review options
            $myactivity->attemptduring=1;
            $myactivity->correctnessduring=1;
            $myactivity->marksduring=1;
            $myactivity->specificfeedbackduring=1;
            $myactivity->generalfeedbackduring=1;
            $myactivity->rightanswerduring=1;
            $myactivity->overallfeedbackduring=1;

            $myactivity->attemptimmediately=1;
            $myactivity->correctnessimmediately=1;
            $myactivity->marksimmediately=1;
            $myactivity->specificfeedbackimmediately=1;
            $myactivity->generalfeedbackimmediately=1;
            $myactivity->rightanswerimmediately=1;
            $myactivity->overallfeedbackimmediately=1;

            $myactivity->marksopen=1;

            $myactivity->attemptclosed=1;
            $myactivity->correctnessclosed=1;
            $myactivity->marksclosed=1;
            $myactivity->specificfeedbackclosed=1;
            $myactivity->generalfeedbackclosed=1;
            $myactivity->rightanswerclosed=1;
            $myactivity->overallfeedbackclosed=1;
            
            $tabla='tfg_myactivity_item_quiz';
            
            $quiz = true;
            
            $_SESSION['quiz_activity'] = $quiz;
            break;
        //tipos: eachuser(cada persona plantea un tema),general,single(debate sencillo),blog, qanda(Foro pregunta y respuesta)
        case 'A101':
            $myactivity = forum($section);
            $myactivity->type = 'single';
            $myactivity->name = $activity_title;
            $myForum->introeditor = array('text' => $howto,'format' => 1);
            $tabla='tfg_myactivity_item_forum';  
            break;
        case 'A102':
            $myactivity = forum($section);
            $myactivity->type = 'general';
            $myactivity->name = $activity_title;
            $myForum->introeditor = array('text' => $howto,'format' => 1);
            $tabla='tfg_myactivity_item_forum';
            break;
        case 'A103':
            $myactivity = forum($section);
            $myactivity->type = 'eachuser';
            $myactivity->name = $activity_title;
            $myForum->introeditor = array('text' => $howto,'format' => 1);
            $tabla='tfg_myactivity_item_forum';
            break;
        case 'A104':
            $myactivity = forum($section);
            $myactivity->type = 'qanda';
            $myactivity->name = $activity_title;
            $myForum->introeditor = array('text' => $howto,'format' => 1);
            $tabla='tfg_myactivity_item_forum';
            break;
        case 'A200':
            //Workshop             
            $myactivity->modulename='workshop';
            $myactivity->name = $activity_title;
            $myactivity->course = $_SESSION['course_id'];
            $myactivity->visible = 0;
            $myactivity->introeditor = array('text' => $howto,'format' => 1);
            $myactivity->section = $section; 
            $myactivity->grade =  80.00000 ; 
            $myactivity->gradinggrade =20.00000;
            $myactivity->gradecategory = 0;
            $myactivity->gradinggradecategory = 0;
            $myactivity->submissionstart = 0;
            $myactivity->submissionend = 0;
            $myactivity->assessmentstart = 0;
            $myactivity->assessmentend = 0;
            $myactivity->cmidnumber = null;
            $myactivity->instructauthorsformat = 1;
            $myactivity->instructreviewersformat=1;
            $myactivity->conclusion='';
            $myactivity->overallfeedbackfiletypes='';
            $myactivity->strategy = 'accumulative';
            $draftitemid = file_get_submitted_draft_itemid('instructauthors');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'instructauthors', 0);    // no context yet, itemid not used
            $myactivity->instructauthorseditor=array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);

            $draftitemid = file_get_submitted_draft_itemid('instructreviewers');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'instructreviewers', 0);    // no context yet, itemid not used
            $myactivity->instructreviewerseditor=  array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);

            $draftitemid = file_get_submitted_draft_itemid('conclusion');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'conclusion', 0);    // no context yet, itemid not used
            $myactivity->conclusioneditor= array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);
            
            $tabla='tfg_myactivity_item_workshop';
            break;
        case 'A300':
            //Assign
            $myactivity->modulename='assign';
            $myactivity->name = $activity_title;
            $myactivity->course = $_SESSION['course_id'];
            $myactivity->visible = 0;
            $myactivity->introeditor = array('text' => $howto,'format' => 1);
            $myactivity->section = $section;   
            $myactivity->submissiondrafts = 0;
            $myactivity->requiresubmissionstatement = 0;
            $myactivity->requireallteammemberssubmit = 0;
            $myactivity->sendnotifications = 0;
            $myactivity->sendlatenotifications = 0;
            $myactivity->duedate = 0;
            $myactivity->cutoffdate = 0;
            $myactivity->gradingduedate = 0;
            $myactivity->allowsubmissionsfromdate = 0;
            $myactivity->grade = 100;
            $myactivity->teamsubmission = 0;
            $myactivity->blindmarking = 0;
            $myactivity->markingworkflow = 0;
            $myactivity->markingallocation = 0;
            $myactivity->cmidnumber = null;
            
            $tabla='tfg_myactivity_item_assign';
            break;
        case 'A400':
            //Data           
            $myactivity->modulename='data';
            $myactivity->name = $activity_title;
            $myactivity->course = $_SESSION['course_id'];
            $myactivity->visible = 0;
            $myactivity->introeditor = array('text' => $howto,'format' => 1);
            $myactivity->section = $section;   
            $myactivity->cmidnumber = null;
            
            $tabla='tfg_myactivity_item_data';
            break;
        case 'A500':
            //Glossary            
            $myactivity->modulename='glossary';
            $myactivity->name = $activity_title;
            $myactivity->course = $_SESSION['course_id'];
            $myactivity->section = $section;   
            $myactivity->visible = 0;
            $myactivity->introeditor = array('text' => $howto,'format' => 1);
            $myactivity->displayformat = 'dictionary';
            $myactivity->cmidnumber = null;
            $myactivity->assessed = 0;
            
            $tabla='tfg_myactivity_item_glossary';           
            break;
        case 'A600':
            //Wiki            
            $myactivity->modulename='wiki';
            $myactivity->name = $activity_title;
            $myactivity->course = $_SESSION['course_id'];
            $myactivity->visible = 0;
            $myactivity->introeditor = array('text' => $howto,'format' => 1);
            $myactivity->section = $section; 
            $myactivity->cmidnumber = null;
            $tabla='tfg_myactivity_item_wiki';
            break;
        case 'A700':
            //FeedBack            
            $myactivity->modulename='feedback';
            $myactivity->name = $activity_title;
            $myactivity->course = $_SESSION['course_id'];
            $myactivity->visible = 0;
            $myactivity->introeditor = array('text' => $howto,'format' => 1);
            $myactivity->section = $section; 
            $myactivity->site_after_submit = '';
            $myactivity->page_after_submit = '';

            $draftitemid = file_get_submitted_draft_itemid('page_after_submit_editor');
            // no context yet, itemid not used
            file_prepare_draft_area($draftitemid, null, 'mod_feedback', 'page_after_submit', false);
            $myactivity->page_after_submit_editor['text'] = '';
            $myactivity->page_after_submit_editor['format'] = editors_get_preferred_format();
            $myactivity->page_after_submit_editor['itemid'] = $draftitemid;
            
            $tabla='tfg_myactivity_item_feedback';
            break;
    }
    $created_activity = create_module($myactivity);
        
    $_SESSION['module_id'] = $created_activity->module;
    
    $id_activity_moodle = $created_activity -> instance;
    
    //Insertion in tfg_myactivity_item
    
    $data_base-> course = $_SESSION['course_id'];
    $data_base-> type   = $myactivity->modulename;
    $data_base-> timecreated = time();
    $id_activity = $DB->insert_record('tfg_myactivity_item', $data_base);
    
       
     //Insertion in subtable of each activity
    $data_table2 = new stdClass();
    $data_table2-> id = $id_activity;
    $data_table2-> moodle_activityid = $id_activity_moodle;
    
    $DB->insert_record_raw($tabla, $data_table2,true,false,true);
    
    //Insertion in the relation between scene and tfg_myactivity_item
    $data_table3                        = new stdClass();
    $data_table3-> id                   = 0;
    $data_table3-> myactivity_itemid    = $id_activity;
    $data_table3-> sceneid              = $_SESSION['scene_id'];
    $DB->insert_record_raw('tfg_has', $data_table3,true,false,true);  
    
    //Si es de tipo quiz y hace faltar crear una por defecto
    if($quiz && $create_activity){
        create_question($idactivity,$categories['0'],$myactivity,$created_activity->instance); 
    }
    //Aqui ira el paso de preguntas del banco al cuestionario
    else if($quiz){
        
        $condition='';
        $i=1;
        foreach($categories as $category){
           $condition .= 'category = '.$category;
           
           if($i != count($categories)){
                
                $condition .= ' OR ';
            }
            $i++;
        }
        
        $sql = "Select * from {question} where ".$condition." ORDER BY RAND() LIMIT ".$n_question;
        //echo '<br>'.$sql.'<br><br>';
         
        $questions = $DB->get_records_sql($sql);
        
        //Añadimos las preguntas ya existentes al nuevo cuestionario creado
        foreach ($questions as $q){
           
            quiz_add_quiz_question($q->id, $myactivity, $page = 0, $maxmark = null);
        
        }
        //Como hemos añadido varias preguntas al nuevo cuestionario tenemos que añadir los slots en nuestra tabla 
        //de ahi que haya que ver cuantas preguntas han sido añadidas.
        $sql_slots = "Select * from {quiz_slots} where quizid = ".$created_activity -> instance;
        
        $slots = $DB->get_records_sql($sql_slots);
        foreach ($slots as $slot){
        
            $usage = new stdClass();
            $usage -> id = $slot->id;
            $usage -> myactivity_item_quizid = $created_activity -> instance;

            $DB->insert_record_raw('tfg_myactivity_usage', $usage,true,false,true);
       
        }
        

    }
    
    //como se ha creado la actividad cambiamos el estado de la peticion por parte del profesorado
    $request = new stdClass();
    $request->id = $_SESSION['request_teacher_id'];
    $request->myactivity_itemid = $id_activity;
    
    //Actualizar la request del profesorado
    $DB->update_record('tfg_teacher_comp_request',$request);
    return  $created_activity->instance;
}

function create_question($idactivity,$category,$myquiz,$quizid){
    global $DB,$USER,$CFG;
        
    $STANDARD_OVERALL_CORRECT_FEEDBACK = 'Bien hecho!';
    $STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK =   'Partes pero solo partes son correctas';
    $STANDARD_OVERALL_INCORRECT_FEEDBACK = 'Incorrecto!.';
            
    $myactivity= new stdClass();
    
    $question = new stdClass();
    $question->category = $category;
    $question->createdby = $USER->id;

    $sql = "Select titlename from `tfg_myactivity` where id='".$idactivity."'";  
    $name = $DB->get_records_sql($sql);
    $activity_title = $name[key($name)]->titlename;
   
    $sql = "Select howto from `tfg_scene` where id='".$_SESSION['scene_id']."'";  
    $howto_sql = $DB->get_records_sql($sql);
    $howto = $howto_sql[key($howto_sql)]->howto;
    
    switch ($idactivity){
        
        case 'A001':
            
            $myactivity->name = $activity_title;
            $myactivity->questiontext = array('text' => $howto, 'format' => FORMAT_HTML);
            $myactivity->generalfeedback = array('text' => 'Los números impares son 1 y 3.', 'format' => FORMAT_HTML);
            $myactivity->defaultmark = 1;
            $myactivity->noanswers = 5;
            $myactivity->numhints = 2;
            $myactivity->penalty = 0;
            $myactivity->category = $category;

            $myactivity->shuffleanswers = 1;
            $myactivity->answernumbering = '123';
            $myactivity->showstandardinstruction = 0;
            $myactivity->single = '0';
            $myactivity->correctfeedback = array('text' => '',
                                                     'format' => FORMAT_HTML);
            $myactivity->partiallycorrectfeedback = array('text' => '',
                                                              'format' => FORMAT_HTML);
            $myactivity->shownumcorrect = 1;
            $myactivity->incorrectfeedback = array('text' => '',
                                                       'format' => FORMAT_HTML);
            $myactivity->fraction = array('0.5', '0.0', '0.5', '0.0', '0.0');
            $myactivity->answer = array(
                0 => array(
                    'text' => 'One',
                    'format' => FORMAT_PLAIN
                ),
                1 => array(
                    'text' => 'Two',
                    'format' => FORMAT_PLAIN
                ),
                2 => array(
                    'text' => 'Three',
                    'format' => FORMAT_PLAIN
                ),
                3 => array(
                    'text' => 'Four',
                    'format' => FORMAT_PLAIN
                ),
                4 => array(
                    'text' => '',
                    'format' => FORMAT_PLAIN
                )
            );

            $myactivity->feedback = array(
                0 => array(
                    'text' => 'Uno es impar.',
                    'format' => FORMAT_HTML
                ),
                1 => array(
                    'text' => 'Dos es par.',
                    'format' => FORMAT_HTML
                ),
                2 => array(
                    'text' => 'Tres es impar.',
                    'format' => FORMAT_HTML
                ),
                3 => array(
                    'text' => 'Cuatro es par.',
                    'format' => FORMAT_HTML
                ),
                4 => array(
                    'text' => '',
                    'format' => FORMAT_HTML
                )
            );

            $myactivity->hint = array(
                0 => array(
                    'text' => 'Hint 1.',
                    'format' => FORMAT_HTML
                ),
                1 => array(
                    'text' => 'Hint 2.',
                    'format' => FORMAT_HTML
                )
            );
            $myactivity->hintclearwrong = array(0, 1);
            $myactivity->hintshownumcorrect = array(1, 1);
            
            $question->qtype    = 'multichoice';            
            break;
        //True False
        case 'A002':
            $myactivity->name = $activity_title;
            $myactivity->questiontext = array('text' => $howto, 'format' => FORMAT_HTML);
            $myactivity->questiontext['format'] = '1';
            $myactivity->questiontext['text'] = 'La respuesta es verdad.';

            $myactivity->defaultmark = 1;
            $myactivity->generalfeedback = array();
            $myactivity->generalfeedback['format'] = '1';
            $myactivity->generalfeedback['text'] = 'Deberías haber seleccionado la opción verdadera.';

            $myactivity->correctanswer = '1';
            $myactivity->feedbacktrue = array();
            $myactivity->feedbacktrue['format'] = '1';
            $myactivity->feedbacktrue['text'] = 'Correcto!';

            $myactivity->feedbackfalse = array();
            $myactivity->feedbackfalse['format'] = '1';
            $myactivity->feedbackfalse['text'] = 'Incorrecto!';
            $myactivity->category = $category;
            $myactivity->penalty = 1;
            
            $question->qtype    = 'truefalse';
            break;
        //Shortanswer
        case 'A003':
            $myactivity->name = $activity_title;
            $myactivity->questiontext = array('text' => $howto, 'format' => FORMAT_HTML);
            $myactivity->defaultmark = 1.0;
            $myactivity->generalfeedback = array('text' =>'Rana o sapo son opciones acceptables', 'format' => FORMAT_HTML);
            $myactivity->usecase = false;
            $myactivity->answer = array('Rana', 'Sapo', '*');
            $myactivity->fraction = array('1.0', '0.8', '0.0');
            $myactivity->feedback = array(
                array('text' => 'Rana es muy buena respuesta.', 'format' => FORMAT_HTML),
                array('text' => 'Sapo es casi perfecta respuesta, rana mejor', 'format' => FORMAT_HTML),
                array('text' => 'Incorrecto', 'format' => FORMAT_HTML),
                );
            $myactivity->category = $category;
            
            $question->qtype    = 'shortanswer';
            break;
        //Numerical
        case 'A004':
            
            $myactivity->name = $activity_title;
            $myactivity->questiontext = array();
            $myactivity->questiontext['format'] = '1';
            $myactivity->questiontext['text'] = $howto;

            $myactivity->defaultmark = 1;
            $myactivity->generalfeedback = array();
            $myactivity->generalfeedback['format'] = '1';
            $myactivity->generalfeedback['text'] = '3.14 es la respuesta correcta.';

            $myactivity->noanswers = 6;
            $myactivity->answer = array();
            $myactivity->answer[0] = '3.14';
            $myactivity->answer[1] = '3.142';
            $myactivity->answer[2] = '3.1';
            $myactivity->answer[3] = '3';
            $myactivity->answer[4] = '*';
            $myactivity->answer[5] = '';

            $myactivity->tolerance = array();
            $myactivity->tolerance[0] = 0;
            $myactivity->tolerance[1] = 0;
            $myactivity->tolerance[2] = 0;
            $myactivity->tolerance[3] = 0;
            $myactivity->tolerance[4] = 0;
            $myactivity->tolerance[5] = 0;

            $myactivity->fraction = array();
            $myactivity->fraction[0] = '1.0';
            $myactivity->fraction[1] = '0.0';
            $myactivity->fraction[2] = '0.0';
            $myactivity->fraction[3] = '0.0';
            $myactivity->fraction[4] = '0.0';
            $myactivity->fraction[5] = '0.0';

            $myactivity->feedback = array();
            $myactivity->feedback[0] = array();
            $myactivity->feedback[0]['format'] = '1';
            $myactivity->feedback[0]['text'] = 'Muy bien.';

            $myactivity->feedback[1] = array();
            $myactivity->feedback[1]['format'] = '1';
            $myactivity->feedback[1]['text'] = 'Muy preciso';

            $myactivity->feedback[2] = array();
            $myactivity->feedback[2]['format'] = '1';
            $myactivity->feedback[2]['text'] = 'No suficiente preciso';

            $myactivity->feedback[3] = array();
            $myactivity->feedback[3]['format'] = '1';
            $myactivity->feedback[3]['text'] = 'No suficiente preciso';

            $myactivity->feedback[4] = array();
            $myactivity->feedback[4]['format'] = '1';
            $myactivity->feedback[4]['text'] = 'Incorrecto!';

            $myactivity->feedback[5] = array();
            $myactivity->feedback[5]['format'] = '1';
            $myactivity->feedback[5]['text'] = '';

            $myactivity->unitrole = '3';
            $myactivity->unitpenalty = 0.1;
            $myactivity->unitgradingtypes = '1';
            $myactivity->unitsleft = '0';
            $myactivity->nounits = 1;
            $myactivity->multiplier = array();
            $myactivity->multiplier[0] = '1.0';

            $myactivity->penalty = '0';
            $myactivity->numhints = 2;
            $myactivity->hint = array();
            $myactivity->hint[0] = array();
            $myactivity->hint[0]['format'] = '1';
            $myactivity->hint[0]['text'] = '';

            $myactivity->hint[1] = array();
            $myactivity->hint[1]['format'] = '1';
            $myactivity->hint[1]['text'] = '';

            $myactivity->qtype = 'numerical';
            $myactivity->category = $category;
            
            $question->qtype    = 'numerical';
            break;
        //Essay
        case 'A005':
            $myactivity->name = $activity_title;
            $myactivity->questiontext = array('text' => $howto, 'format' => FORMAT_HTML);
            $myactivity->defaultmark = 1.0;
            $myactivity->generalfeedback = array('text' => 'Espero que haya incluido todos los conceptos dados en clase.', 'format' => FORMAT_HTML);
            $myactivity->responseformat = 'editor';
            $myactivity->responserequired = 1;
            $myactivity->responsefieldlines = 10;
            $myactivity->attachments = 0;
            $myactivity->attachmentsrequired = 0;
            $myactivity->filetypeslist = '';
            $myactivity->graderinfo = array('text' => '', 'format' => FORMAT_HTML);
            $myactivity->responsetemplate = array('text' => '', 'format' => FORMAT_HTML);
            $myactivity->category = $category;
            
            $question->qtype    = 'essay';
            break;
        //match
        case 'A006':
            
            $myactivity->name = $activity_title;
            $myactivity->questiontext = array('text' => $howto, 'format' => FORMAT_HTML);
            $myactivity->generalfeedback = array('text' => 'Retroalimentación general: ', 'format' => FORMAT_HTML);
            $myactivity->defaultmark = 1;
            $myactivity->penalty = 0;
            $myactivity->category = $category;

            $myactivity->shuffleanswers = 1;
            $myactivity->correctfeedback = array('text' => $STANDARD_OVERALL_CORRECT_FEEDBACK,
                                            'format' => FORMAT_HTML);
            $myactivity->partiallycorrectfeedback = array('text' => $STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK,
                                                 'format' => FORMAT_HTML);
            $myactivity->shownumcorrect = true;
            $myactivity->incorrectfeedback = array('text' => $STANDARD_OVERALL_INCORRECT_FEEDBACK,
                                        'format' => FORMAT_HTML);   

            $myactivity->subquestions = array(
                0 => array('text' => 'Rana', 'format' => FORMAT_HTML),
                1 => array('text' => 'Gato', 'format' => FORMAT_HTML),
                2 => array('text' => 'Tritón', 'format' => FORMAT_HTML),
                3 => array('text' => '', 'format' => FORMAT_HTML));

            $myactivity->subanswers = array(
                0 => 'Anfibio',
                1 => 'Mamífero',
                2 => 'Anfibio',
                3 => 'Insecto'
            );

            $myactivity->noanswers = 4;
            
            $question->qtype    = 'match';
            
            break;
        //Drag marker
        case 'A007':
            $bgdraftitemid = 0;
            file_prepare_draft_area($bgdraftitemid, null, null, null, null);
            $fs = get_file_storage();
            $filerecord = new stdClass();
            $filerecord->contextid = context_user::instance($USER->id)->id;
            $filerecord->component = 'user';
            $filerecord->filearea = 'draft';
            $filerecord->itemid = $bgdraftitemid;
            $filerecord->filepath = '/';
            $filerecord->filename = 'mkmap.png';
            $fs->create_file_from_pathname($filerecord, $CFG->dirroot .
                '/question/type/ddmarker/tests/fixtures/mkmap.png');

            $myactivity->name = $activity_title;
            $myactivity->questiontext = array(
            'text' => $howto,
            'format' => FORMAT_HTML,
            );
            $myactivity->defaultmark = 2;
            $myactivity->generalfeedback = array(
            'text' => '',
            'format' => FORMAT_HTML,
            );
            $myactivity->bgimage = $bgdraftitemid;
            $myactivity->shuffleanswers = 0;

            $myactivity->drags = array(
            array('label' => '0', 'noofdrags' => 1),
            array('label' => '1', 'noofdrags' => 1),
            );

            $myactivity->drops = array(
            array('shape' => 'Rectangle', 'coords' => '0,0;272,389', 'choice' => 1),
            array('shape' => 'Rectangle', 'coords' => '272,0;272,389', 'choice' => 2),
            );

            $myactivity->correctfeedback = array('text' => $STANDARD_OVERALL_CORRECT_FEEDBACK,
                                            'format' => FORMAT_HTML);
            $myactivity->partiallycorrectfeedback = array('text' => $STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK,
                                                 'format' => FORMAT_HTML);
            $myactivity->shownumcorrect = true;
            $myactivity->incorrectfeedback = array('text' => $STANDARD_OVERALL_INCORRECT_FEEDBACK,
                                        'format' => FORMAT_HTML);

            $myactivity->penalty = '0';
            $myactivity->hint = array(
            array(
                'text' => 'Hint 1.',
                'format' => FORMAT_HTML,
            ),
            array(
                'text' => 'Hint 2.',
                'format' => FORMAT_HTML,
            ),
            );
            $myactivity->hintshownumcorrect = array(1, 1);
            $myactivity->hintclearwrong = array(0, 1);
            $myactivity->hintoptions = array(0, 1);
            $myactivity->category = $category;
            
            $question->qtype    = 'ddmarker';
            break;
        //Calculated simple
        case 'A008':
            
            $myactivity->name = $activity_title;

            $myactivity->qtype = 'calculatedsimple';

            $myactivity->questiontext = array();
            $myactivity->questiontext['text'] = $howto;
            $myactivity->questiontext['format'] = '1';

            $myactivity->defaultmark = 1;
            $myactivity->generalfeedback = array();
            $myactivity->generalfeedback['text'] = '<p>The answer is  {a} + {b}</p>';
            $myactivity->generalfeedback['format'] = '1';

            $myactivity->synchronize = 0;
            $myactivity->initialcategory = 1;
            $myactivity->reload = 1;
            $myactivity->mform_isexpanded_id_answerhdr = 1;
            $myactivity->noanswers = 1;
            $myactivity->answer = array('{a} + {b}');

            $myactivity->fraction = array('1.0');

            $myactivity->tolerance = array(0.01);
            $myactivity->tolerancetype = array('1');

            $myactivity->correctanswerlength = array('2');
            $myactivity->correctanswerformat = array('1');

            $myactivity->feedback = array();
            $myactivity->feedback[0] = array();
            $myactivity->feedback[0]['text'] = '';
            $myactivity->feedback[0]['format'] = '1';

            $myactivity->unitrole = '3';
            $myactivity->unitpenalty = 0.1;
            $myactivity->unitgradingtypes = '1';
            $myactivity->unitsleft = '0';
            $myactivity->nounits = 1;
            $myactivity->multiplier = array('1.0');

            $myactivity->penalty = '0';
            $myactivity->numhints = 2;
            $myactivity->hint = array();
            $myactivity->hint[0] = array();
            $myactivity->hint[0]['text'] = '';
            $myactivity->hint[0]['format'] = '1';

            $myactivity->hint[1] = array();
            $myactivity->hint[1]['text'] = '';
            $myactivity->hint[1]['format'] = '1';

            $myactivity->calcmin = array();
            $myactivity->calcmin[1] = 1;
            $myactivity->calcmin[2] = 1;

            $myactivity->calcmax = array();
            $myactivity->calcmax[1] = 10;
            $myactivity->calcmax[2] = 10;

            $myactivity->calclength = array();
            $myactivity->calclength[1] = '1';
            $myactivity->calclength[2] = '1';

            $myactivity->calcdistribution = array();
            $myactivity->calcdistribution[1] = 0;
            $myactivity->calcdistribution[2] = 0;

            $myactivity->datasetdef = array();
            $myactivity->datasetdef[1] = '1-0-a';
            $myactivity->datasetdef[2] = '1-0-b';

            $myactivity->defoptions = array();
            $myactivity->defoptions[1] = '';
            $myactivity->defoptions[2] = '';

            $myactivity->selectadd = '10';
            $myactivity->selectshow = '10';
            $myactivity->number = array();
            $myactivity->number[1] = '2.3';
            $myactivity->number[2] = '7.6';
            $myactivity->number[3] = '2.1';
            $myactivity->number[4] = '6.4';
            $myactivity->number[5] = '1.4';
            $myactivity->number[6] = '1.9';
            $myactivity->number[7] = '9.9';
            $myactivity->number[8] = '9.5';
            $myactivity->number[9] = '9.0';
            $myactivity->number[10] = '5.2';
            $myactivity->number[11] = '2.1';
            $myactivity->number[12] = '7.3';
            $myactivity->number[13] = '7.9';
            $myactivity->number[14] = '1.2';
            $myactivity->number[15] = '2.3';
            $myactivity->number[16] = '3.4';
            $myactivity->number[17] = '1.9';
            $myactivity->number[18] = '5.2';
            $myactivity->number[19] = '3.4';
            $myactivity->number[20] = '3.4';

            $myactivity->itemid = array_fill(1, 20, 0);

            $myactivity->definition = array();
            $myactivity->definition[1] = '1-0-b';
            $myactivity->definition[2] = '1-0-a';
            $myactivity->definition[3] = '1-0-b';
            $myactivity->definition[4] = '1-0-a';
            $myactivity->definition[5] = '1-0-b';
            $myactivity->definition[6] = '1-0-a';
            $myactivity->definition[7] = '1-0-b';
            $myactivity->definition[8] = '1-0-a';
            $myactivity->definition[9] = '1-0-b';
            $myactivity->definition[10] = '1-0-a';
            $myactivity->definition[11] = '1-0-b';
            $myactivity->definition[12] = '1-0-a';
            $myactivity->definition[13] = '1-0-b';
            $myactivity->definition[14] = '1-0-a';
            $myactivity->definition[15] = '1-0-b';
            $myactivity->definition[16] = '1-0-a';
            $myactivity->definition[17] = '1-0-b';
            $myactivity->definition[18] = '1-0-a';
            $myactivity->definition[19] = '1-0-b';
            $myactivity->definition[20] = '1-0-a';

            $myactivity->category = $category;
            
            $question->qtype    = 'calculatedsimple';
            break;
        //GapSelect
        case 'A009':
            $myactivity->name = $activity_title;
            $myactivity->questiontext = ['text' => $howto, 'format' => FORMAT_HTML];
            $myactivity->defaultmark = 1.0;
            $myactivity->generalfeedback = ['text' => 'La respuesta correcta es: "El gato se sentó en la alfombra"', 'format' => FORMAT_HTML];
            $myactivity->choices = [
                ['answer' => 'gato', 'choicegroup' => '1'],
                ['answer' => 'mesa',    'choicegroup' => '2'],
                ['answer' => 'alfombra', 'choicegroup' => '1']
            ];

            

            $myactivity->correctfeedback = array('text' => $STANDARD_OVERALL_CORRECT_FEEDBACK,
                                            'format' => FORMAT_HTML);
            $myactivity->partiallycorrectfeedback = array('text' => $STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK,
                                                 'format' => FORMAT_HTML);
            $myactivity->shownumcorrect = true;
            $myactivity->incorrectfeedback = array('text' => $STANDARD_OVERALL_INCORRECT_FEEDBACK,
                                        'format' => FORMAT_HTML);

            $myactivity->category = $category;
            $myactivity->shownumcorrect = 0;
            $myactivity->shuffleanswers=0;
            $myactivity->penalty = 0;
            
            $question->qtype    = 'gapselect';
            break;
        //drag text/image
        case 'A010':
            $bgdraftitemid = 0;
            file_prepare_draft_area($bgdraftitemid, null, null, null, null);
            $fs = get_file_storage();
            $filerecord = new stdClass();
            $filerecord->contextid = context_user::instance($USER->id)->id;
            $filerecord->component = 'user';
            $filerecord->filearea = 'draft';
            $filerecord->itemid = $bgdraftitemid;
            $filerecord->filepath = '/';
            $filerecord->filename = 'oceanfloorbase.jpg';
            $fs->create_file_from_pathname($filerecord, $CFG->dirroot .
                    '/question/type/ddimageortext/tests/fixtures/oceanfloorbase.jpg');

            $myactivity->name = $activity_title;
            $myactivity->questiontext = array(
                'text' => $howto,
                'format' => FORMAT_HTML,
            );
            $myactivity->defaultmark = 1;
            $myactivity->generalfeedback = array(
                'text' => '<p>Más información puede ser encontrada en el tema 4, bloque 4.2</p>',
                'format' => FORMAT_HTML,
            );
            $myactivity->bgimage = $bgdraftitemid;
            $myactivity->shuffleanswers = 0;
            $myactivity->drags = array(
                array('dragitemtype' => 'word', 'draggroup' => '1', 'infinite' => '0'),
                array('dragitemtype' => 'word', 'draggroup' => '1', 'infinite' => '0'),
                array('dragitemtype' => 'word', 'draggroup' => '1', 'infinite' => '0'),
                array('dragitemtype' => 'word', 'draggroup' => '1', 'infinite' => '0'),
                array('dragitemtype' => 'word', 'draggroup' => '1', 'infinite' => '0'),
                array('dragitemtype' => 'word', 'draggroup' => '1', 'infinite' => '0'),
                array('dragitemtype' => 'word', 'draggroup' => '1', 'infinite' => '0'),
                array('dragitemtype' => 'word', 'draggroup' => '1', 'infinite' => '0'),
            );
            $myactivity->dragitem = array(0, 0, 0, 0, 0, 0, 0, 0);
            $myactivity->draglabel =
            array(
                'Isla<br/>arc',
                'Dorsal<br/>medio oceánica',
                'Planicia<br/>abisal',
                'Talud<br/>continental',
                'Fosa<br/>oceánica',
                'Cuesta<br/>continental',
                'Cadena<br/>montañosa',
                'Plataforma<br/>continental',
            );
            $myactivity->drops = array(
                array('xleft' => '53', 'ytop' => '17', 'choice' => '7', 'droplabel' => ''),
                array('xleft' => '172', 'ytop' => '2', 'choice' => '8', 'droplabel' => ''),
                array('xleft' => '363', 'ytop' => '31', 'choice' => '5', 'droplabel' => ''),
                array('xleft' => '440', 'ytop' => '13', 'choice' => '3', 'droplabel' => ''),
                array('xleft' => '115', 'ytop' => '74', 'choice' => '6', 'droplabel' => ''),
                array('xleft' => '210', 'ytop' => '94', 'choice' => '4', 'droplabel' => ''),
                array('xleft' => '310', 'ytop' => '87', 'choice' => '1', 'droplabel' => ''),
                array('xleft' => '479', 'ytop' => '84', 'choice' => '2', 'droplabel' => ''),
            );

            $myactivity->correctfeedback = array('text' => $STANDARD_OVERALL_CORRECT_FEEDBACK,
                                            'format' => FORMAT_HTML);
            $myactivity->partiallycorrectfeedback = array('text' => $STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK,
                                                 'format' => FORMAT_HTML);
            $myactivity->shownumcorrect = true;
            $myactivity->incorrectfeedback = array('text' => $STANDARD_OVERALL_INCORRECT_FEEDBACK,
                                        'format' => FORMAT_HTML);

            $myactivity->penalty = '0';
            $myactivity->hint = array(
                array(
                    'text' => '<p>Seran eliminados los incorrectos.</p>',
                    'format' => FORMAT_HTML,
                ),
                array(
                    'text' => '<ul>
                               <li> La llanura abisal es una extensión plana casi sin rasgos distintivos del fondo del océano de 4 a 6 km por debajo del nivel del mar. </li>
                               <li> La elevación continental es la parte del fondo del océano que se inclina suavemente más allá del talud continental. </li>
                               <li> La plataforma continental es el fondo oceánico de suave pendiente que se encuentra cerca de la costa. </li>
                               <li> El talud continental es la parte relativamente empinada del fondo del océano más allá de la plataforma continental. </li>
                               <li> Una cordillera en medio del océano es una amplia cordillera submarina de varios kilómetros de altura. </li>
                               <li> Un cinturón montañoso es una larga cadena de montañas. </li>
                               <li> Un arco de islas es una cadena de islas volcánicas. </li>
                               <li> Una trinchera oceánica es una depresión profunda en el fondo del océano. </li>
                               </ul>',
                    'format' => FORMAT_HTML,
                ),
                array(
                    'text' => '<p>Seran eliminados los incorrectos.</p>',
                    'format' => FORMAT_HTML,
                ),
                array(
                    'text' => '<ul>
                               <li> La llanura abisal es una extensión plana casi sin rasgos distintivos del fondo del océano de 4 a 6 km por debajo del nivel del mar. </li>
                               <li> La elevación continental es la parte del fondo del océano que se inclina suavemente más allá del talud continental. </li>
                               <li> La plataforma continental es el fondo oceánico de suave pendiente que se encuentra cerca de la costa. </li>
                               <li> El talud continental es la parte relativamente empinada del fondo del océano más allá de la plataforma continental. </li>
                               <li> Una cordillera en medio del océano es una amplia cordillera submarina de varios kilómetros de altura. </li>
                               <li> Un cinturón montañoso es una larga cadena de montañas. </li>
                               <li> Un arco de islas es una cadena de islas volcánicas. </li>
                               <li> Una trinchera oceánica es una depresión profunda en el fondo del océano. </li>
                               </ul>',
                    'format' => FORMAT_HTML,
                ),
            );
            $myactivity->hintclearwrong = array(1, 0, 1, 0);
            $myactivity->hintshownumcorrect = array(1, 1, 1, 1);
            $myactivity->category = $category;
            
            $question->qtype = 'ddimageortext';
            break;
    }
    
    $question_created = question_bank::get_qtype($question->qtype)->save_question($question,$myactivity);
    
    //add the quiz question
    quiz_add_quiz_question($question_created->id, $myquiz, $page = 0, $maxmark = null);
    
    $sql = "Select * from {quiz_slots} where quizid = ".$quizid;
    $slots = $DB->get_records_sql($sql);
    
    $usage = new stdClass();
    $usage -> id = key($slots);
    $usage -> myactivity_item_quizid = $quizid;
    
    $DB->insert_record_raw('tfg_myactivity_usage', $usage,true,false,true);
  
    
}

function create_request($user,$courseid,$table){
    global $DB;
    
    $request  = new stdClass();
    $request -> usermodified    = $user;
    $request -> timecreated     = time();
    $request -> courseid        = $courseid;
    $requestid = $DB->insert_record($table, $request);
    
    return $requestid;
}


if($_POST['operation']  == 'competences_student'){
    $operation = $_POST['operation'];
    unset($_POST['operation']);
    
    $param = $_POST;
    if(count($param) >= 1){
        
        $requestid = create_request($USER->id,$_SESSION['course_id'],'tfg_student_comp_request');
        
        $obj_order = new stdClass();
        $obj_order->id = 0 ;
        $obj_order->requestid = $requestid;
        
        foreach($param as $p){
            $obj_order->competenceid = $p ;
            $DB->insert_record_raw('tfg_order', $obj_order,true,false,true);
        }
        
        echo html_writer::div('Petición guardada correctamente.<br>','');
        echo html_writer::tag('a', 'Pedir otra competencia', array('href' => '../tfg/block_data.php?courseid='.$_SESSION['course_id'])).'<br>';
    }
}
else if($_POST['operation'] == 'competences_teacher' || $_POST['operation'] == 'config_activity_backup' ){
   
    $operation = $_POST['operation'];
    unset($_POST['operation']);
    
    $param = $_POST;
    
    if(count($param) >= 1){
        
        if($operation == 'competences_teacher'){
            //Guardamos la petición 
            
            $requestid = create_request($USER->id,$_SESSION['course_id'],'tfg_teacher_comp_request');
            
            $table_satisfy = new stdClass();
            $table_satisfy->id = 0 ;
            $table_satisfy->requestid = $requestid;
            
            $_SESSION['request_teacher_id']=$requestid;
        }else{
            
            //Recuperación de la sesión anterior
            echo html_writer::div('Se detectó una sesión anterior no finalizada, por favor seleccione la actividad a crear o cancele el proceso.<br>','message');
            $sql_request = 'SELECT s.competenceid, c.name, c.description FROM `tfg_satisfy` s INNER JOIN tfg_competence c ON s.competenceid = c.id WHERE requestid='.$param['requestid'];
            
            $_SESSION['request_teacher_id']=$param['requestid'];
            
            
            $obj = $DB->get_records_sql($sql_request);
            $param = (array_keys($obj));
           
            echo html_writer::div('<br>Las competencias seleccionadas en la ultima sección fueron:.<br>');
            echo '<div class="lane" style="float:left">';
            foreach($obj as $p){
                echo'

                    <div class="b_activities">
                        <div class="chk_comp '.$p->competenceid.'"><input title = "'.$p->name.'" type="checkbox" name="'.$p->competenceid.'" value="'.$p->competenceid.'" checked disabled/></div>
                        <div class="activity_text"><strong>'.$p->name.':</strong><br>'.$p->description.'</div>
                    </div>';
            }
            
            echo '</div>';
        }

        
        
        $where = '';
        $innerjoins = '';
        $i=1;
        foreach($param as $p){
            
            if($i!=1){
                $innerjoins .= "
                INNER JOIN `tfg_appears` ap".$i." ON
                ap1.sceneid = ap".$i.".sceneid ";
                
            }
           
            $where .= "ap".$i.".competenceid ='".$p."'";
            
            if($i != count($param)){
                
                $where .= ' AND ';
            }
           
            
            $i++;
            if($operation == 'competences_teacher'){
                //Relation bt competence and request 
                $table_satisfy->competenceid = $p ;
                $DB->insert_record_raw('tfg_satisfy', $table_satisfy,true,false,true);
            }
        }
        //Consulta para obtener las actividades que cumplen las competencias seleccionadas
        $sql = "
            SELECT
                ap1.sceneid,
                a.name,
                a.id,
                s.description,
                a.image
            FROM
               `tfg_appears` ap1
           ".$innerjoins."
            INNER JOIN `tfg_scene` s ON
                s.id = ap1.sceneid
            INNER JOIN `tfg_myactivity` a ON
                a.id = s.myactivityid
            WHERE
                ".$where.";";
        
        //Borrar!
        //echo '<br>'.$sql.'<br><br>';
        
        echo '<div class="activities_block">';
        
        $activities =  $DB->get_records_sql($sql);
        //AQUI COMPROBAR SI LA CONSULTA DEVUELVUE ALGO PARA MOSTRAR MENSAJE O QUE 
        if(count($activities)<1){
            echo '<br>'.html_writer::div('No se ha encontrado actividad que pueda ser cubierta por las competencias seleccionadas.<br>','message');
        }
        else{
            echo html_writer::div('En base a las competencias previamente seleccionadas, las actividades que cubren estas competencias son las siguientes:<br>');
            foreach($activities as $activity){

                echo'
                <div class="b_activities">
                    <button onclick="config_activity(\''.$activity->id.'\',\''.$activity->sceneid.'\')"><img width="25px" src="./resource/moodle_icons/'.$activity->image.'"></button>
                    <div class="activity_text"><strong>'.$activity->name.':</strong><br>'.$activity->description.'</div>
                </div>';
            }
        }
        echo '</div>';
        echo '<div style="display:inline-block">';
        echo '<br>'.html_writer::tag('a', 'Cancelar creación de la actividad', array('href' => 'block_data.php?courseid='.$_SESSION['course_id'].'&request='.$_SESSION['request_teacher_id']));
        echo '</div>';
        
      
    }
    else{
        $a = html_writer::tag('a', 'Crear otra actividad', array('href' => '../tfg/block_data.php?courseid='.$_SESSION['course_id'].'&request='.$_SESSION['request_teacher_id'])).'<br>';
        echo html_writer::div('No se ha seleccionado ningún tipo de competencia, vuelva al menú principal.<br>'.$a,'message');
    }
}
else if($_POST['operation'] == 'config_activity' ){
    
    $_SESSION['scene_id']=$_POST['scene'];
    
    //Question categories
    $object_context = context_course::instance($_SESSION['course_id']);
    
    $contexts = array();
    foreach (explode('/',$object_context->path) as $context_id){
        $object = new stdClass();
        if($context_id<>''){
            $object->id=$context_id;
            array_push($contexts,$object);  
        }
        
    }
    
       
    //Sections to add the activity
    $sql_categories = "SELECT section,name FROM {course_sections} WHERE course =".$_SESSION['course_id'];
    $categories = $DB->get_records_sql($sql_categories);
    
    echo 
    '<form onsubmit="read_form(event,this,\'create_activity\')" method="POST">';
     echo  'Selecciona en que sección la pregunta será incluida: '
    . '<input type="hidden" name="id" value="'.$_POST['id'].'"></input>
        
            <select name="section" required="required">';
            foreach($categories as $category ){
                if($category->section!= 0){
                    if($category->name==''){

                        echo '<option  value="'.$category->section.'">Tema '.$category->section.'</option><br>';
                    }
                    else{
                        echo '<option  value="'.$category->section.'">'.$category->name.'</option><br>';
                    }
                }
            }
            echo  
        '</select>';
        
        //Es una pregunta?
        $sql_categories = "SELECT * FROM `tfg_myquestion` WHERE id = '".$_POST['id']."' ";
        $categories = $DB->get_records_sql($sql_categories);
        if(count($categories) > 0){
            question_category_select_menu_block($contexts,$_POST['id']);
        }
        
        
        echo '<br><input value = "Crear actividad"  type="submit"></input>
            
    </form><br>';
    
    echo html_writer::tag('a', 'Cancelar creación de la actividad ', array('href' => '../tfg/block_data.php?courseid='.$_SESSION['course_id'].'&request='.$_SESSION['request_teacher_id'])).'<br>';
               
          
}
//Proceso de creación de actividades
else if ($_POST['operation'] == 'create_activity' ){
        
   echo 'La actividad ha sido creada correctamente.<br><br>';
   
   echo html_writer::tag('a', 'Crear otra actividad', array('href' => '../tfg/block_data.php?courseid='.$_SESSION['course_id'])).'<br>';
   //No question   
   $create_activity=true;
   $categories='';
   $n_question=0;
   $array_categories = array();
    if(isset($_POST['category_name'])){
        $create_activity=false;
        $categories = $_POST['category_name'];
        
        $array_ex = explode('&',$categories);
        if($array_ex['0'] =='undefined'){
            unset($array_ex['0']);
        }
        
        foreach($array_ex as $ass){
            $array_1 = explode(',',$ass);
            $array_categories[] = $array_1['0'];
        }
        if(count($array_categories)>=1){
            $n_question = $_POST['n_question'];
        }
        else{
            $create_activity=true;
        }
        
    }
    $id = create_activity($_POST['id'],$_POST['section'],$create_activity,$array_categories,$n_question);
        
    $sql_edit = "SELECT * FROM {course_modules} WHERE module =".$_SESSION['module_id']." AND instance = '".$id."' AND course =".$_SESSION['course_id'];

    $edit = $DB->get_records_sql($sql_edit);

    foreach ($edit as $e){

        $id_edit = $e->id;
    }
    
    if($_SESSION['quiz_activity']){
        echo'<input id="quizid" type="hidden" value = "'.$id_edit.'"/>';
    }
    
    echo'<input id="activityid" type="hidden" value = "'.$id_edit.'"/>';
    echo html_writer::tag('a', 'Edita esta actividad creada', array('value'=> $id_edit,'id'=>'edit_activity_tag','href' => '/moodle/course/modedit.php?update='.$id_edit.'&return=1'));
    
    
    
}

?>

