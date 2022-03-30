<?php

//require_once('../../config.php');

require_login();
// This file is part of Moodle - http://moodle.org/
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

class block_tfg extends  block_list {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_tfg') ;
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {

        global $COURSE;
        
        if ($this->content !== null) {
            return $this->content;
        }
  
 	$this->content         =  new stdClass();            
        $this->content->items  = array();
        $this->content->icons  = array();
        
    	$this->content->text   = 'Bloque moodle TFG';
                
              
        if (has_capability('block/tfg:createactivity',  $this->context)) {
            $this->content->items[] = html_writer::tag('a', 'Proceso de creación de actividades basadas en competencias.', array('href' => '../blocks/tfg/block_data.php?courseid='.$COURSE->id));
                      
        }
        if(has_capability('block/tfg:askactivity',  $this->context)){
            
            $this->content->items[] = html_writer::tag('a', '¿Qué competencia quieres ejercitar?', array('href' => '../blocks/tfg/block_data.php?courseid='.$COURSE->id)); 
            
        }
   
      	
   
        
    }
}
