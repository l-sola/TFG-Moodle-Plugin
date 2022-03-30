<?php
require_once('../../config.php');

defined('MOODLE_INTERNAL') || die();






if($_POST['operation'] == 'bt'){
    
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

             //BORRAR 
             //echo $sql_compentence;
             echo 
             '<form onsubmit="read_form(event,this,\''.$_POST['form'].'\')" method="POST" name="competences">
                 <div style="float:left">';
                     foreach($competences as $competence){
                         echo'

                         <div class="b_activities">
                             <div class="chk_comp '.$competence->id.'"><input title = "'.$competence->name.'" type="checkbox" name="'.$competence->id.'" value="'.$competence->id.'" /></div>
                             <div class="activity_text"><strong>'.$competence->name.':</strong><br>'.$competence->description.'</div>
                         </div>';

                     }


             echo'</div>

                 <input name="suggest_activity" value = "'.$_POST['buttom'].'" type="submit"></input> 

             </form>     
';
}else if($_POST['operation'] == 'rq'){
    $sql_compentence = 
    "SELECT
        c.name,
        c.description,
        o.competenceid as id,
        COUNT(o.competenceid) as number
    FROM
        `tfg_order` o
    INNER JOIN `tfg_student_comp_request` s ON
        o.requestid = s.id
    INNER JOIN tfg_competence c ON
        o.competenceid = c.id
    WHERE
        s.courseid = ".$_SESSION['course_id']."
    GROUP BY
        competenceid
    ORDER BY number desc";
    
   // echo $sql_compentence;
    $competences = $DB->get_records_sql($sql_compentence);

            $contador = 0;
             echo 
             '<form onsubmit="read_form(event,this,\''.$_POST['form'].'\')" method="POST" name="competences">
                 <div style="float:left">';
                     foreach($competences as $competence){
                         $contador++;
                     
                         echo'

                         <div class="b_activities">
                             <div class="chk_comp '.$competence->id.'"><input title = "'.$competence->name.'" type="checkbox" name="'.$competence->id.'" value="'.$competence->id.'" /></div>
                             <div class="activity_text"><strong>'.$competence->name.':</strong><br>'.$competence->description.'</div>';
                             if($contador == 1){
                                echo '<div class="informative_text" ><strong>1ª más solicitada</strong></div>';
                             }elseif($contador == 2){
                                echo '<div class="informative_text"><strong>2ª más solicitada</strong></div>';
                             }elseif($contador == 3){
                                echo '<div class="informative_text"><strong>3ª más solicitada</strong></div>';
                             }else{
                                 echo '<div class="informative_text">'.$contador.'ª más solicitada</div>';
                             }
                         echo '</div>';

                     }


             echo'</div>

                 <input name="suggest_activity" value = "'.$_POST['buttom'].'" type="submit"></input> 

             </form>';
        
        
    
}
else if($_POST['operation'] == 'cc'){
    
    $sql_compentence="
        SELECT
        c.name,
        c.description,
        c.id,
        COUNT(A.competenceid) AS NUMBER
        FROM
            `tfg_myactivity_item` mi
        INNER JOIN `tfg_has` h ON
            mi.id = h.myactivity_itemid
        INNER JOIN `tfg_scene` s ON
            h.sceneid = s.id
        INNER JOIN `tfg_appears` A ON
            A.sceneid = s.id
        INNER JOIN tfg_competence c ON
        A.competenceid = c.id
        
        WHERE
            course = ".$_SESSION['course_id']." 
        GROUP BY
            A.competenceid
        ORDER BY NUMBER
        DESC";
    
            $competences = $DB->get_records_sql($sql_compentence);

            $contador = 0;
             echo 
             '<form onsubmit="read_form(event,this,\''.$_POST['form'].'\')" method="POST" name="competences">
                 <div style="float:left">';
                     foreach($competences as $competence){
                         $contador++;
                     
                         echo'

                         <div class="b_activities">
                             <div class="chk_comp '.$competence->id.'"><input title = "'.$competence->name.'" type="checkbox" name="'.$competence->id.'" value="'.$competence->id.'" /></div>
                             <div class="activity_text"><strong>'.$competence->name.':</strong><br>'.$competence->description.'</div>';
                             if($contador == 1){
                                echo '<div class="informative_text"><strong>1ª menos cubierta</strong></div>';
                             }elseif($contador == 2){
                                echo '<div class="informative_text"><strong>2ª menos cubierta</strong></div>';
                             }elseif($contador == 3){
                                echo '<div class="informative_text" ><strong>3ª menos cubierta</strong></div>';
                             }else{
                                 echo '<div class="informative_text">'.$contador.'ª menos cubierta</div>';
                             }
                         echo '</div>';

                     }


             echo'</div>

                 <input name="suggest_activity" value = "'.$_POST['buttom'].'" type="submit"></input> 

             </form>';
    
}
else if($_POST['operation'] == 'lc'){
    
    $sql_compentence =
    "SELECT
        c.id,
        c.name,
        c.description,
        COUNT(scr.id) AS n_requested
    FROM
        tfg_student_comp_request scr
    INNER JOIN tfg_order o ON
        scr.id = o.requestid
    INNER JOIN tfg_competence c ON
        o.competenceid = c.id
    WHERE scr.courseid = ".$_SESSION['course_id']." "
            . "AND scr.usermodified = ".$USER->id."
    GROUP BY
        c.id
    ORDER BY
        n_requested";
            
            
            $competences = $DB->get_records_sql($sql_compentence);

            $contador = 0;
             echo 
             '<form onsubmit="read_form(event,this,\''.$_POST['form'].'\')" method="POST" name="competences">
                 <div style="float:left">';
                     foreach($competences as $competence){
                         $contador++;
                     
                         echo'

                         <div class="b_activities">
                             <div class="chk_comp '.$competence->id.'"><input title = "'.$competence->name.'" type="checkbox" name="'.$competence->id.'" value="'.$competence->id.'" /></div>
                             <div class="activity_text"><strong>'.$competence->name.':</strong><br>'.$competence->description.'</div>';
                             if($contador == 1){
                                echo '<div class="informative_text"><strong>1ª menos solicitada</strong></div>';
                             }elseif($contador == 2){
                                echo '<div class="informative_text"><strong>2ª menos solicitada</strong></div>';
                             }elseif($contador == 3){
                                echo '<div class="informative_text" ><strong>3ª menos solicitada</strong></div>';
                             }else{
                                 echo '<div class="informative_text">'.$contador.'ª menos solicitada</div>';
                             }
                         echo '</div>';

                     }


             echo'</div>

                 <input name="suggest_activity" value = "'.$_POST['buttom'].'" type="submit"></input> 

             </form>';
}

















?>