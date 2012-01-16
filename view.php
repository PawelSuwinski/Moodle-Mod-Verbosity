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
 * This page prints a particular instance of verbosity
 *
 * @package    mod_verbosity
 * @copyright  2012 Paweł Suwiński
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir . '/grade/constants.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // verbosity instance ID

if ($id) {
    if (! $cm = get_coursemodule_from_id('verbosity', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = get_record('course', 'id', $cm->course)) {
        error('Course is misconfigured');
    }

    if (! $verbosity = get_record('verbosity', 'id', $cm->instance)) {
        error('Course module is incorrect');
    }

} else if ($a) {
    if (! $verbosity = get_record('verbosity', 'id', $a)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $verbosity->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('verbosity', $verbosity->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

add_to_log($course->id, "verbosity", "view", "view.php?id=$cm->id", "$verbosity->id");

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', $context)) {
    notice(get_string("activityiscurrentlyhidden"),$CFG->wwwroot . '/course/view.php?id=' . $course->id);
}

/// Print the page header
$strverbositys = get_string('modulenameplural', 'verbosity');
$strverbosity  = get_string('modulename', 'verbosity');

$navlinks = array();
$navlinks[] = array('name' => $strverbositys, 'link' => "index.php?id=$course->id", 'type' => 'activity');
$navlinks[] = array('name' => format_string($verbosity->name), 'link' => '', 'type' => 'activityinstance');

$navigation = build_navigation($navlinks);

print_header_simple(format_string($verbosity->name), '', $navigation, '', '', true,
              update_module_button($cm->id, $course->id, $strverbosity), navmenu($course, $cm));

$grade_view_allowed = (
    has_capability('moodle/grade:view', $context) && 
    ($course->showgrades || has_capability('moodle/grade:viewall', $context))
) ? true : false ;

print_heading(format_string($verbosity->name));

if(!verbosity_grades_needs_update($verbosity)) {
    if($grade_view_allowed) {
        notify(get_string('gradesuptodate','verbosity'),'green');
    }
} elseif (verbosity_grades_update($verbosity) == GRADE_UPDATE_OK) {
    if($grade_view_allowed) {
        notify(get_string('gradesupdatesuccess','verbosity'),'green');
    }
} else {
    if($grade_view_allowed) {
        notify(get_string('gradesupdatefailure','verbosity'));
    }
}

print_box_start('generalbox', 'intro');
if (trim(strip_tags($verbosity->intro))) {
    $formatoptions->noclean = true;
    $formatoptions->para    = false;
    echo format_text($verbosity->intro, $verbosity->introformat, $formatoptions); 
}

if($grade_view_allowed) {
    echo '<br \><a href="'.$CFG->wwwroot . '/grade/report/index.php?id='.$course->id.'">'.
        get_string('grader:view','gradereport_grader').'</a>';
}
print_box_end();

/// Finish the page
print_footer($course);

?>
