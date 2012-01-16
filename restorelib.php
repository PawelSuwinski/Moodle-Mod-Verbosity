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
//                    (CL,pk->id)
//
// Meaning: pk->primary key field of the table
//          fk->foreign key to link with parent
//          nt->nested field (recursive data)
//          CL->course level info
//          UL->user level info
//          files->table may have files)
//
//-----------------------------------------------------------

//This function executes all the restore procedure about this mod
function verbosity_restore_mods($mod,$restore) {

    global $CFG;

    $status = true;

    //Get record from backup_ids
    $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

    if ($data) {
        //Now get completed xmlized object
        $info = $data->info;
        //traverse_xmlize($info); //Debug
        //print_object ($GLOBALS['traverse_array']); //Debug
        //$GLOBALS['traverse_array']=""; //Debug
      
        //Now, build the verbosity record structure
        $verbosity->course = $restore->course_id;
        $verbosity->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
        $verbosity->intro = backup_todb($info['MOD']['#']['INTRO']['0']['#']);
        $verbosity->introformat = backup_todb($info['MOD']['#']['INTROFORMAT']['0']['#']);
        $verbosity->timecreated = $info['MOD']['#']['TIMECREATED']['0']['#'];
        $verbosity->timemodified = $info['MOD']['#']['TIMEMODIFIED']['0']['#'];
        $verbosity->counter = backup_todb($info['MOD']['#']['COUNTER']['0']['#']);
        $verbosity->allgraded = backup_todb($info['MOD']['#']['ALLGRADED']['0']['#']);
        $verbosity->forumid = backup_todb($info['MOD']['#']['FORUMID']['0']['#']);


        //The structure is equal to the db, so insert the verbosity
        $newid = insert_record ("verbosity",$verbosity);

        //Do some output     
        if (!defined('RESTORE_SILENTLY')) {
            echo "<li>".get_string("modulename","verbosity")." \"".format_string(stripslashes($verbosity->name),true)."\"</li>";
        }
        backup_flush(300);

        if ($newid) {
            //We have the newid, update backup_ids
            backup_putid($restore->backup_unique_code,$mod->modtype,
                         $mod->id, $newid);

        } else {
            $status = false;
        }
    } else {
        $status = false;
    }

    return $status;
}

function verbosity_decode_content_links_caller($restore) {
    global $CFG;
    $status = true;

    if ($verbositys = get_records_sql ("SELECT v.id, v.intro
                               FROM {$CFG->prefix}verbosity v
                               WHERE v.course = $restore->course_id")) {
        $i = 0;   //Counter to send some output to the browser to avoid timeouts
        foreach ($verbositys as $verbosity) {
            //Increment counter
            $i++;
            $content = $verbosity->intro;
            $result = restore_decode_content_links_worker($content,$restore);

            if ($result != $content) {
                //Update record
                $verbosity->intro = addslashes($result);
                $status = update_record("verbosity", $verbosity);
                if (debugging()) {
                    if (!defined('RESTORE_SILENTLY')) {
                        echo '<br /><hr />'.s($content).'<br />changed to<br />'.s($result).'<hr /><br />';
                    }
                }
            }
            //Do some output
            if (($i+1) % 5 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 100 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }
        }
    }
    return $status;
}

//This function returns a log record with all the necessay transformations
//done. It's used by restore_log_module() to restore modules log.
function verbosity_restore_logs($restore,$log) {
                
    $status = false;
                
    //Depending of the action, we recode different things
    switch ($log->action) {
    case "add":
        if ($log->cmid) {
            //Get the new_id of the module (to recode the info field)
            $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
            if ($mod) {
                $log->url = "view.php?id=".$log->cmid;
                $log->info = $mod->new_id;
                $status = true;
            }
        }
        break;
    case "update":
        if ($log->cmid) {
            //Get the new_id of the module (to recode the info field)
            $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
            if ($mod) {
                $log->url = "view.php?id=".$log->cmid;
                $log->info = $mod->new_id;
                $status = true;
            }
        }
        break;
    case "view":
        if ($log->cmid) {
            //Get the new_id of the module (to recode the info field)
            $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
            if ($mod) {
                $log->url = "view.php?id=".$log->cmid;
                $log->info = $mod->new_id;
                $status = true;
            }
        }
        break;
    default:
        if (!defined('RESTORE_SILENTLY')) {
            echo "action (".$log->module."-".$log->action.") unknown. Not restored<br />";  //Debug
        }
        break;
    }

    if ($status) {
        $status = $log;
    }
    return $status;
}
?>
