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
 * Defines the main verbosity configuration form
 *
 * @package    mod_verbosity
 * @copyright  2012 Paweł Suwiński
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once ('moodleform_mod.php');

class mod_verbosity_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE;
        $mform    =& $this->_form;

// General settings -------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('verbosityname', 'verbosity'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

    /// Adding the optional "intro" and "introformat" pair of fields
        $mform->addElement('htmleditor', 'intro', get_string('verbosityintro', 'verbosity'));
        $mform->setType('intro', PARAM_RAW);
        $mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');

    /// Adding "introformat" field
        $mform->addElement('format', 'introformat', get_string('format'));


// Configuration --------------------------------------------------------
        $mform->addElement('header', 'verbosityfieldset', get_string('forumchoice', 'verbosity'));
        
        $mform->addElement('checkbox', 'allgraded', get_string('allgradedforums', 'verbosity'));
        
        $forums = array();
        if ($forum_instances = get_all_instances_in_course('forum', $COURSE, null, true)) {
            foreach($forum_instances as $forum) {
                $forums[$forum->id] = $forum->name;
            }
        }

        asort($forums);
        $forums = array(0 => get_string('allforums','forum')) + $forums;
        
        $mform->addElement('select', 'forumid', get_string('forumchoice', 'verbosity'), $forums);
        $mform->disabledIf('forumid','allgraded','checked');

//-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }


    /**
     * If choosed forum was deleted it should be added to list as 'not existed one'
     * 
     */
    function definition_after_data() {
        parent::definition_after_data();

         $mform =& $this->_form;
         $forumid=array_shift($mform->getElement('forumid')->getSelected());

         if($forumid == 0) {
            return;
         }

         foreach($mform->getElement('forumid')->_options as $option) {
            if($option['attr']['value'] == $forumid) {
                return;
            }
         }

        $mform->getElement('forumid')->addOption(get_string('forumremoved','verbosity'),$forumid);
    }

    /**
     * Manage unchecked checkbox and disabled select fields cases 
     */
    function get_data($slashed=true) { 
    
        $verbosity = parent::get_data($slashed);

        if(!is_null($verbosity)) {
            if(!isset($verbosity->allgraded)) {
                $verbosity->allgraded = 0;
            }
            if(!isset($verbosity->forumid)) {
                $verbosity->forumid = 0;
            }
        }

        return $verbosity;
    }
}

?>
