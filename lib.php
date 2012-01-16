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
 * Library of functions and constants for module verbosity
 *
 * @package    mod_verbosity
 * @copyright  2012 Paweł Suwiński
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $verbosity An object from the form in mod_form.php
 * @return int The id of the newly inserted verbosity record
 */
function verbosity_add_instance($verbosity) {

    $verbosity->timecreated = time();
    $verbosity->id = insert_record('verbosity', $verbosity);
    verbosity_grades_update($verbosity);
    return $verbosity->id;
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $verbosity An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function verbosity_update_instance($verbosity) {

    $verbosity->timemodified = time();
    $verbosity->id = $verbosity->instance;

    if(!$oldverbosity = get_record('verbosity', 'id', $verbosity->id)) {
        return false;
    }

    $retval=update_record('verbosity', $verbosity);

    if(
        $oldverbosity->allgraded != $verbosity->allgraded || 
        $oldverbosity->forumid != $verbosity->forumid
    ) {
        verbosity_grades_update($verbosity);
    }

    return $retval;
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function verbosity_delete_instance($id) {
    global $CFG;

    if (! $verbosity = get_record('verbosity', 'id', $id)) {
        return false;
    }

    $result = true;

    if (! delete_records('verbosity', 'id', $verbosity->id)) {
        $result = false;
    }

    require_once($CFG->libdir.'/gradelib.php');
    grade_update('mod/verbosity', $verbosity->course, 'mod', 'verbosity', $verbosity->id, 0, NULL, array('deleted'=>1));

    return $result;
}


/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function verbosity_user_outline($course, $user, $mod, $verbosity) {
    return null;
}


/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function verbosity_user_complete($course, $user, $mod, $verbosity) {
    return true;
}


/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in verbosity activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function verbosity_print_recent_activity($course, $isteacher, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}


/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function verbosity_cron () {
    global $CFG;
    require_once($CFG->libdir . '/grade/constants.php');

    $rs = get_recordset_sql(verbosity_get_sql(
        'v.id,v.course,v.name,v.counter,v.allgraded,v.forumid,COUNT(p.id) as postscounter',
        null,
        'v.id,v.course,v.name,v.counter,v.allgraded,v.forumid'
    ));


    while ($rec = rs_fetch_record($rs)) {
        if($rs->fields['postscounter'] != $rs->fields['counter'])
        {
            if (verbosity_grades_update((object) $rs->fields) == GRADE_UPDATE_OK) {
                mtrace('Grades update for verbosity id '.$rs->fields['id'].': succeed.');
            } else {
                mtrace('Grades update for verbosity id '.$rs->fields['id'].': failed.');
            }
        }
        rs_next_record($rs);
    }
    rs_close($rs);

    return true;
}


/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of verbosity. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $verbosityid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function verbosity_get_participants($verbosityid) {
    return false;
}


/**
 * This function returns if a scale is being used by one verbosity
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $verbosityid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function verbosity_scale_used($verbosityid, $scaleid) {
    return false;
}


/**
 * Checks if scale is being used by any instance of verbosity.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any verbosity
 */
function verbosity_scale_used_anywhere($scaleid) {
    return false;
}


/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function verbosity_install() {
    return true;
}


/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function verbosity_uninstall() {
    return true;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the data.
 * @param $mform form passed by reference
 */
function verbosity_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'verbosityheader', get_string('modulenameplural', 'verbosity'));
    $mform->addElement('checkbox', 'reset_verbosity_grades', get_string('resetgrades','verbosity'));
}


/**
 * Course reset form defaults.
 */
function verbosity_reset_course_form_defaults($course) {
    return array('reset_verbosity_grades' => 1);
}

/**
 * Actual implementation of the rest coures functionality, delete all the
 * data responses for course $data->courseid.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function verbosity_reset_userdata($data) {
    global $CFG;
    
    if(
        !isset($data->reset_verbosity_grades) || 
        $data->reset_verbosity_grades != 1 ||
        !$verbosities = get_records('verbosity','course',(int)$data->courseid)
    ) {
        return array();
    }

    require_once($CFG->libdir.'/gradelib.php');
    require_once($CFG->libdir . '/grade/constants.php');

    $error = false;

    foreach ($verbosities as $verbosity) {

        if(grade_update('mod/verbosity', $verbosity->course, 'mod', 'verbosity', 
            $verbosity->id, 0, NULL, array('reset' => true))  != GRADE_UPDATE_OK) {
            $error = true;
            continue;
        }

        set_field('verbosity','counter',0,'id',$verbosity->id);
    }

    return array(array(
        'component' => get_string('modulenameplural', 'verbosity'), 
        'item'      => get_string('resetgrades','verbosity'), 
        'error'     => $error,
    ));
}
//////////////////////////////////////////////////////////////////////////////////////
/// Any other verbosity functions go here.  Each of them must have a name that
/// starts with verbosity_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.

/**
 * Does grades need to be updated 
 *
 * @access public
 * @param object $verbosity 
 * @return bool
 */
function verbosity_grades_needs_update($verbosity) {
    return get_field_sql(verbosity_get_sql(
        'COUNT(p.id) != '.$verbosity->counter,
        $verbosity->id
    ))  == 0 ? false : true;
}

/**
 * Create or update grade item and grades for given verbosity
 *
 * @access public
 * @param object $verbosity 
 * @return int 0 if ok, error code otherwise
 */
function verbosity_grades_update($verbosity) {
    global $CFG;

    if (!function_exists('grade_update')) { //workaround for buggy PHP versions
        require_once($CFG->libdir.'/gradelib.php');
    }

    $params = array(
        'itemname'  => $verbosity->name, 
        'gradetype' => GRADE_TYPE_VALUE,
        'grademin'  => 0,
        'grademax'  => 0,
    );

    $grades = null;
    $postscounter = 0;

    if ($grades = get_records_sql(verbosity_get_sql(
        'p.userid, p.userid as userid, COUNT(p.id) as rawgrade',
        $verbosity->id,
        'p.userid'
    ))) {

        foreach($grades as $grade) {
            $postscounter += $grade->rawgrade;
            if($grade->rawgrade > $params['grademax']) { 
                $params['grademax'] = $grade->rawgrade;
            }
        }
    }

    set_field('verbosity','counter',$postscounter,'id',$verbosity->id);

    if($grade_item = grade_item::fetch(array(
        'courseid'  => $verbosity->course,
        'itemtype'  => 'mod',
        'itemmodule'  => 'verbosity',
        'iteminstance'  => $verbosity->id,
        'itemnumber'  => 0,
    ))) {

      // remove old grades for non members users
      $select = 'itemid = '.$grade_item->id;
      if(!empty($grades)) {
          $select.= ' AND userid NOT IN ('.implode(',',array_keys($grades)).')';
      }
      if(count_records_select('grade_grades',$select) > 0) {
          delete_records_select('grade_grades',$select);
      }

      // set new rawgrademax if it is nessesary  
      $select = 'itemid = '.$grade_item->id.' AND rawgrademax != '.$params['grademax'];
      if(count_records_select('grade_grades',$select,'COUNT(id)') > 0) {
          set_field_select('grade_grades','rawgrademax',$params['grademax'],$select);
      }

    }

    return grade_update('mod/verbosity', $verbosity->course, 'mod', 'verbosity',
        $verbosity->id, 0, $grades, $params);
}

/**
 * Return sql to fetch proper disscussions.
 * @access public
 * @return string 
 */ 
function verbosity_get_sql($select, $verbosityid = null, $groupby = null) {
    global $CFG;

    $sql = '
        SELECT '.
            $select.' 
        FROM '. 
            $CFG->prefix.'forum_posts AS p, '.
            $CFG->prefix.'forum_discussions AS d, '.
            $CFG->prefix.'forum AS f, '.
            $CFG->prefix.'verbosity v 
        WHERE 
            p.discussion=d.id AND
            d.forum=f.id AND 
            d.course=v.course AND
            (
                (v.allgraded = 1 AND f.assessed > 0 AND f.scale != 0) OR
                (v.allgraded = 0 AND (v.forumid = f.id OR v.forumid = 0)) 
            ) 
    ';

    if(!is_null($verbosityid)) {
        $sql .= ' AND v.id = '.$verbosityid;
    }

    if(!is_null($groupby)) {
        $sql .= ' GROUP BY '.$groupby;
    }

    return $sql;
}


?>
