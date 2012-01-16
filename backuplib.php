<?php 
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
 * This php script contains all the stuff to backup/restore
 * verbosity mods
 *
 * @package    mod_verbosity
 * @copyright  2012 Paweł Suwiński
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//This is the "graphical" structure of the verbosity mod:
//
//                       verbosity
//                     (CL,pk->id)
//
// Meaning: pk->primary key field of the table
//          fk->foreign key to link with parent
//          nt->nested field (recursive data)
//          CL->course level info
//          UL->user level info
//          files->table may have files)
//
//-----------------------------------------------------------

//This function executes all the backup procedure about this mod
function verbosity_backup_mods($bf,$preferences) {
    global $CFG;

    $status = true; 

    ////Iterate over verbosity table
    if ($verbositys = get_records ("verbosity","course", $preferences->backup_course,"id")) {
        foreach ($verbositys as $verbosity) {
            if (backup_mod_selected($preferences,'verbosity',$verbosity->id)) {
                $status = verbosity_backup_one_mod($bf,$preferences,$verbosity);
            }
        }
    }
    return $status;
}

function verbosity_backup_one_mod($bf,$preferences,$verbosity) {

    global $CFG;

    if (is_numeric($verbosity)) {
        $verbosity = get_record('verbosity','id',$verbosity);
    }

    $status = true;

    //Start mod
    fwrite ($bf,start_tag("MOD",3,true));
    //Print assignment data
    fwrite ($bf,full_tag("ID",4,false,$verbosity->id));
    fwrite ($bf,full_tag("MODTYPE",4,false,"verbosity"));
    fwrite ($bf,full_tag("NAME",4,false,$verbosity->name));
    fwrite ($bf,full_tag("INTRO",4,false,$verbosity->intro));
    fwrite ($bf,full_tag("INTROFORMAT",4,false,$verbosity->introformat));
    fwrite ($bf,full_tag("TIMECREATED",4,false,$verbosity->timecreated));
    fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$verbosity->timemodified));
    fwrite ($bf,full_tag("COUNTER",4,false,$verbosity->counter));
    fwrite ($bf,full_tag("ALLGRADED",4,false,$verbosity->allgraded));
    fwrite ($bf,full_tag("FORUMID",4,false,$verbosity->forumid));
    //End mod
    $status = fwrite ($bf,end_tag("MOD",3,true));

    return $status;
}

////Return an array of info (name,value)
function verbosity_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {
    if (!empty($instances) && is_array($instances) && count($instances)) {
        $info = array();
        foreach ($instances as $id => $instance) {
            $info += verbosity_check_backup_mods_instances($instance,$backup_unique_code);
        }
        return $info;
    }
    
     //First the course data
     $info[0][0] = get_string("modulenameplural","verbosity");
     $info[0][1] = count_records("verbosity", "course", "$course");
     return $info;
} 

////Return an array of info (name,value)
function verbosity_check_backup_mods_instances($instance,$backup_unique_code) {
     //First the course data
    $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
    $info[$instance->id.'0'][1] = '';
    return $info;
}

?>
