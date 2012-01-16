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
 * This page lists all the instances of verbosity in a particular course
 *
 * @package    mod_verbosity
 * @copyright  2012 Paweł Suwiński
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // course

if (! $course = get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

require_course_login($course);

add_to_log($course->id, 'verbosity', 'view all', "index.php?id=$course->id", '');


/// Get all required stringsverbosity

$strverbositys = get_string('modulenameplural', 'verbosity');
$strverbosity  = get_string('modulename', 'verbosity');


/// Print the header

$navlinks = array();
$navlinks[] = array('name' => $strverbositys, 'link' => '', 'type' => 'activity');
$navigation = build_navigation($navlinks);

print_header_simple($strverbositys, '', $navigation, '', '', true, '', navmenu($course));

/// Get all the appropriate data

if (! $verbositys = get_all_instances_in_course('verbosity', $course)) {
    notice('There are no instances of verbosity', "../../course/view.php?id=$course->id");
    die;
}

/// Print the list of instances (your module will probably extend this)

$timenow  = time();
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic = get_string('topic');

if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname);
    $table->align = array ('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname);
    $table->align = array ('center', 'left', 'left', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left', 'left', 'left');
}

foreach ($verbositys as $verbosity) {
    if (!$verbosity->visible) {
        //Show dimmed if the mod is hidden
        $link = '<a class="dimmed" href="view.php?id='.$verbosity->coursemodule.'">'.format_string($verbosity->name).'</a>';
    } else {
        //Show normal if the mod is visible
        $link = '<a href="view.php?id='.$verbosity->coursemodule.'">'.format_string($verbosity->name).'</a>';
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array ($verbosity->section, $link);
    } else {
        $table->data[] = array ($link);
    }
}

print_heading($strverbositys);
print_table($table);

/// Finish the page

print_footer($course);

?>
