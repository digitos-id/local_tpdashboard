<?php

namespace local_tpdashboard\form;
use moodleform;

require_once("$CFG->libdir/formslib.php");

class profile extends moodleform {
    public function definition()
    {
        global $CFG;
        $mform = $this->_form;
        $mform->addElement('text', 'firstname', get_string('firstname')); // Add elements to your form
        $mform->setType('messagetext', PARAM_NOTAGS);
        $this->add_action_buttons();
    }

    function validaiton()
    {
        return array();
    }
}
