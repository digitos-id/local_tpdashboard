<?php
/**
 * Custom Dashboard local version details
 *
 * @package    local_tpdashboard
 * @copyright  2023 Prihantoosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$capabilities = array (

    'local/tpdashboard:viewpages' => array (
        'captype'       => 'read',
        'contextlevel'  => CONTEXT_SYSTEM,
        'legacy'    => array (
            'guest'         => CAP_PREVENT,
            'student'       => CAP_ALLOW,
            'teacher'           => CAP_ALLOW,
            'editingteacher'    => CAP_ALLOW,
            'manager'           => CAP_ALLOW,
            'coursecreator'     => CAP_ALLOW
          )
    ),
    

    'local/tpdashboard:managepages' => array (
        'captype'       => 'read',
        'contextlevel'  => CONTEXT_SYSTEM,
        'legacy'    => array (
            'guest'         => CAP_PREVENT,
            'student'       => CAP_ALLOW,
            'teacher'           => CAP_ALLOW,
            'editingteacher'    => CAP_ALLOW,
            'manager'           => CAP_ALLOW,
            'coursecreator'     => CAP_ALLOW
        )
    )
);
