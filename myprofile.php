<?php
//...

/**
 * Custom Dashboard local version details
 *
 * @package    local_tpdashboard/myprofile
 * @copyright  2023 Prihantoosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
$PAGE->set_url(new moodle_url(url: '/local/tpdashboard/myprofile.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(title: 'My Profile');


echo $OUTPUT->header();

echo '<h1>Personal Dashboard</h1>';

echo $OUTPUT->footer();