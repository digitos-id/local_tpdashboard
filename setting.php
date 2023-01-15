<?php

/**
 * Custom Dashboard local version details
 *
 * @package    local_tpdashboard
 * @copyright  2023 Prihantoosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $ADMIN->add('root', new admin_category('tpdashboard', get_string('pluginname',local_tpdashboard)));
    
    $ADMIN->add('tpdashboard', new admin_externalpage('userdata', get_string('userdata',local_tpdashboard),
                                new moodle_url('/local/tpdashboard/userdata.php')));

    $ADMIN->add('tpdashboard', new admin_externalpage('usermetadata', get_string('usermetadata',local_tpdashboard),
                                new moodle_url('/local/tpdashboard/metadata.php')));
}