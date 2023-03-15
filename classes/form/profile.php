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
 * Form to edit a users profile
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

// namespace local_tpdashboard\form;
use moodleform;

 if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}


require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot . '/user/lib.php');

class profile extends moodleform {
    
    public function definition()
    {
        global $CFG;
        $mform = $this->_form;
        $editoroptions = null;
        $filemanageroptions = null;
        $usernotfullysetup = user_not_fully_set_up($USER);

        if (!is_array($this->_customdata)) {
            throw new coding_exception('invalid custom data for user_edit_form');
        }
        $editoroptions = $this->_customdata['editoroptions'];
        $filemanageroptions = $this->_customdata['filemanageroptions'];
        $user = $this->_customdata['user'];
        $userid = $user->id;

        if ($user->id > 0) {
            useredit_load_preferences($user, false);
        }

        // Accessibility: "Required" is bad legend text.
        $strgeneral  = get_string('general');
        $strrequired = get_string('required');

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'course', $COURSE->id);
        $mform->setType('course', PARAM_INT);

        // Print the required moodle fields first.
        // $mform->addElement('header', 'moodle', $strgeneral);

        $strrequired = get_string('required');
        $stringman = get_string_manager();

        // Shared fields.
        // useredit_shared_definition($mform, $editoroptions, $filemanageroptions, $user);

        foreach (useredit_get_required_name_fields() as $fullname) {
            // print_r("Yudhi cek" . $fullname);
            $purpose = user_edit_map_field_purpose($user->id, $fullname);
            $mform->addElement('text', $fullname,  get_string($fullname),  'maxlength="100" size="30"' . $purpose);
            if ($stringman->string_exists('missing'.$fullname, 'core')) {
                $strmissingfield = get_string('missing'.$fullname, 'core');
            } else {
                $strmissingfield = $strrequired;
            }
            $mform->addRule($fullname, $strmissingfield, 'required', null, 'client');
            $mform->setType($fullname, PARAM_NOTAGS);
        }

        $enabledusernamefields = useredit_get_enabled_name_fields();
        // Add the enabled additional name fields.
        foreach ($enabledusernamefields as $addname) {
            $purpose = user_edit_map_field_purpose($user->id, $addname);
            $mform->addElement('text', $addname,  get_string($addname), 'maxlength="100" size="30"' . $purpose);
            $mform->setType($addname, PARAM_NOTAGS);
        }

        // Do not show email field if change confirmation is pending.
        if ($user->id > 0 and !empty($CFG->emailchangeconfirmation) and !empty($user->preference_newemail)) {
            $notice = get_string('emailchangepending', 'auth', $user);
            $notice .= '<br /><a href="edit.php?cancelemailchange=1&amp;id='.$user->id.'">'
                    . get_string('emailchangecancel', 'auth') . '</a>';
            $mform->addElement('static', 'emailpending', get_string('email'), $notice);
        } else {
            $purpose = user_edit_map_field_purpose($user->id, 'email');
            $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="30"' . $purpose);
            $mform->addRule('email', $strrequired, 'required', null, 'client');
            $mform->setType('email', PARAM_RAW_TRIMMED);
        }

        $mform->addElement('text', 'phone1', get_string('phone1'), 'maxlength="20" size="25"');
        $mform->setType('phone1', core_user::get_property_type('phone1'));
        $mform->setForceLtr('phone1');

        $options = array('language'=>'en', 'format'=>'dmY', 'optional' => true, 'addEmptyOption' => true, 'optionIncrement'=>array('i'=>'5'));
        $fieldtype = 'date_selector';

        $mform->addElement($fieldtype, 'tanggal', 'tanggal', $options);

        $dateg=array();
        $dateg[] =& $mform->createElement('text', 'datetext', get_string('datetext','module'));
        $dateg[] =& $mform->createElement('button', 'choosedate', get_string('choosedate','module'),array("onClick" =>"datePickerShow('id_datetext');"));
        $mform->addGroup($dateg, 'choosedate', get_string('choosedate','module'), array(' '), false);
        $mform->setDefault('datetext', 'xxxx');

        // $mform->addElement('text', 'firstname', get_string('firstname')); // Add elements to your form
        // $mform->setType('messagetext', PARAM_NOTAGS);
        // Next the customisable profile fields.
        profile_definition($mform, $userid);
        $this->add_action_buttons();
        $this->set_data($user);
    }

    function validaiton()
    {
        return array();
    }
}
