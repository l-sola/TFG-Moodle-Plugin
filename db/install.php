<?php

/// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block tfg is defined here.
 *
 * @package     block_tfg
 * @copyright   2020 Your Name <you@example.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Post installation procedure
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_block_tfg_install() {
    global $DB,$CFG;
    
     
    $json_competences   = file_get_contents($CFG->dirroot."/blocks/tfg/db/competences.json");
    $json_scenes        = file_get_contents($CFG->dirroot."/blocks/tfg/db/scenes.json");
    $json_activities    = file_get_contents($CFG->dirroot."/blocks/tfg/db/activities.json");
    
   
    
    
    $competences = json_decode($json_competences, true);
    
    $DB_competence = new stdClass();
    for($i = 0; $i < count($competences['competences']); $i++){
        
        $DB_competence->id              =   $competences['competences'][$i]['code'];
        $DB_competence->name            =   $competences['competences'][$i]['name'];
        $DB_competence->description     =   $competences['competences'][$i]['description'];
        
               
        $DB->insert_record_raw('tfg_competence', $DB_competence,true,false,true);
               
    }
    
    
    $activities = json_decode($json_activities, true);
    
    
    for($i = 0; $i < count($activities['activities']); $i++){
        $DB_myactivity_type = new stdClass();
        $DB_subquestions    = new stdClass();
        $DB_myactivity      = new stdClass();
        $DB_questions       = new stdClass(); 
        
        $DB_myactivity->id          =   $activities['activities'][$i]['code'];
        $DB_myactivity->name        =   $activities['activities'][$i]['name'];
        $DB_myactivity->image       =   $activities['activities'][$i]['image'];
        $DB_myactivity->titlename   =   $activities['activities'][$i]['titlename'];
        $description                =   $activities['activities'][$i]['description'];
        
        $DB->insert_record_raw('tfg_myactivity', $DB_myactivity,true,false,true);
        
        $DB_myactivity_type->myactivityid   =   $DB_myactivity->id;
        $DB_myactivity_type->description    =   $description;
        
        $DB_questions -> id                 =   $DB_myactivity->id;
        $DB_questions -> description        =   $description;
        $DB_questions -> titlename          =   $activities['activities'][$i]['titlename'];
        
        $DB_subquestions -> id                 =   $DB_myactivity->id;
        $DB_subquestions -> description        =   $description;
        
        switch ($activities['activities'][$i]['code']){

            case "A101":
            case "A102":
            case "A103":
            case "A104":
                $DB_myactivity_type->type = $activities['activities'][$i]['type'];
                               
                $DB->insert_record('tfg_myactivity_type_forum', $DB_myactivity_type);
                break;
            //WorkShop
            case "A200":
             
                $DB->insert_record('tfg_myactivity_type_workshop', $DB_myactivity_type);
                break;
            //Assign
            case "A300":

                $DB->insert_record('tfg_myactivity_type_assign', $DB_myactivity_type);
                break;
            //Data
            case "A400":
                
                $DB->insert_record('tfg_myactivity_type_data', $DB_myactivity_type);
                break;
            //Glossary
            case "A500":

                $DB->insert_record('tfg_myactivity_type_glossary', $DB_myactivity_type);
                break;
            //Wiki
            case "A600":
                
                $DB->insert_record('tfg_myactivity_type_wiki', $DB_myactivity_type);
                break;
            //Feedback
            case "A700":
               
                $DB->insert_record('tfg_myactivity_type_feedback', $DB_myactivity_type);
                break;
            //multianswer
            case "A001":
                               
                $DB_myactivity_type->myquestionid  = $DB_myactivity->id ;
                
                $DB->insert_record_raw('tfg_myquestion', $DB_questions,true,false,true);
                $DB->insert_record_raw('tfg_mq_type_multianswer', $DB_subquestions,true,false,true);
                $DB->insert_record('tfg_myactivity_type_quiz', $DB_myactivity_type);
                
                break;
            //True false
            case "A002":
                
                $DB_myactivity_type->myquestionid  = $DB_myactivity->id ;
                
                $DB->insert_record_raw('tfg_myquestion', $DB_questions,true,false,true);
                $DB->insert_record_raw('tfg_mq_type_truefalse', $DB_subquestions,true,false,true);
                $DB->insert_record('tfg_myactivity_type_quiz', $DB_myactivity_type);
                
                break;
            //Shortanswer
            case "A003":
                
                $DB_myactivity_type->myquestionid  = $DB_myactivity->id ;
                
                $DB->insert_record_raw('tfg_myquestion', $DB_questions,true,false,true);
                $DB->insert_record_raw('tfg_mq_type_shortanswer', $DB_subquestions,true,false,true);
                $DB->insert_record('tfg_myactivity_type_quiz', $DB_myactivity_type);
                
                break;
            //Numerical
            case "A004":
                
                $DB_myactivity_type->myquestionid  = $DB_myactivity->id ;
                
                $DB->insert_record_raw('tfg_myquestion', $DB_questions,true,false,true);
                $DB->insert_record_raw('tfg_mq_type_numerical', $DB_subquestions,true,false,true);
                $DB->insert_record('tfg_myactivity_type_quiz', $DB_myactivity_type);
                
                break;
            //essay
            case "A005":
                
                $DB_myactivity_type->myquestionid  = $DB_myactivity->id ;
                
                $DB->insert_record_raw('tfg_myquestion', $DB_questions,true,false,true);
                $DB->insert_record_raw('tfg_mq_type_essay', $DB_subquestions,true,false,true);
                $DB->insert_record('tfg_myactivity_type_quiz', $DB_myactivity_type);
                               
                break;
            //match
            case "A006":
                
                $DB_myactivity_type->myquestionid  = $DB_myactivity->id ;
                
                $DB->insert_record_raw('tfg_myquestion', $DB_questions,true,false,true);
                $DB->insert_record_raw('tfg_mq_type_match', $DB_subquestions,true,false,true);
                $DB->insert_record('tfg_myactivity_type_quiz', $DB_myactivity_type);
                
                break;
            //drag markers
            case "A007":
                
                $DB_myactivity_type->myquestionid  = $DB_myactivity->id ;
                
                $DB->insert_record_raw('tfg_myquestion', $DB_questions,true,false,true);
                $DB->insert_record_raw('tfg_mq_type_drags_markers', $DB_subquestions,true,false,true);
                $DB->insert_record('tfg_myactivity_type_quiz', $DB_myactivity_type);
                
                break;
            //calculated
            case "A008":
                $DB_myactivity_type->myquestionid  = $DB_myactivity->id ;
                
                $DB->insert_record_raw('tfg_myquestion', $DB_questions,true,false,true);
                $DB->insert_record_raw('tfg_mq_type_calculated_simpl', $DB_subquestions,true,false,true);
                $DB->insert_record('tfg_myactivity_type_quiz', $DB_myactivity_type);
                break;
            
            case "A009":
                $DB_myactivity_type->questionid  = $DB_myactivity->id ;
                
                $DB->insert_record_raw('tfg_myquestion', $DB_questions,true,false,true);
                $DB->insert_record_raw('tfg_mq_type_gapselect', $DB_subquestions,true,false,true);
                $DB->insert_record('tfg_myactivity_type_quiz', $DB_myactivity_type);
                break;
            
            case "A010":
                $DB_myactivity_type->myquestionid  = $DB_myactivity->id ;
                
                $DB->insert_record_raw('tfg_myquestion', $DB_questions,true,false,true);
                $DB->insert_record_raw('tfg_mq_type_drags_textimages', $DB_subquestions,true,false,true);
                $DB->insert_record('tfg_myactivity_type_quiz', $DB_myactivity_type);
                break;
                                
        }
        
               
    }
    /*$DB_questions = new stdClass();
    $DB_myquiz_type = new stdClass();
    for($i = 0; $i < count($activities['questions']); $i++){
        
        $DB_questions->myactivityid    =   $activities['questions'][$i]['code'];
        $DB_questions->name            =   $activities['questions'][$i]['name'];
        
        
        $DB_questions->description     =   $activities['questions'][$i]['description'];
        
        $DB->insert_record('tfg_myactivity', $DB_questions);
        
        $DB_myquiz_type->myactivityid  =   $activities['questions'][$i]['code'];
        $DB->insert_record('tfg_myactivity_type_quiz', $DB_myquiz_type);   
        
        //$DB_questions->myactivityid    =   $activities['questions'][$i]['code'];
    }*/
    
    
    $scenes = json_decode($json_scenes, true);
    
    $DB_scene = new stdClass();
    $DB_scene_competence = new stdClass();
    for($i = 0; $i < count($scenes['scenes']); $i++){
        $DB_scene->id              =   $scenes['scenes'][$i]['id'];
        $DB_scene->name            =   $scenes['scenes'][$i]['scene'];
        $DB_scene->description     =   $scenes['scenes'][$i]['description'];
        $DB_scene->myactivityid    =   $scenes['scenes'][$i]['activity'];
        $DB_scene->howto           =   $scenes['scenes'][$i]['howto'];
        
        $DB->insert_record_raw('tfg_scene', $DB_scene,true,false,true);
        
        for($j=0;$j<count($scenes['scenes'][$i]['competences']);$j++){
            $DB_scene_competence->id            =   '0';
            $DB_scene_competence->competenceid  =   $scenes['scenes'][$i]['competences'][$j]['competenceid'];
            $DB_scene_competence->sceneid       =   $DB_scene->id;
            
            
            $DB->insert_record_raw('tfg_appears', $DB_scene_competence,true,false,true);
           
        }
              
    }
    
}
?>

