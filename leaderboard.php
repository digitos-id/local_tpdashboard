<?php
//...

/**
 * Custom Dashboard local version details
 *
 * @package    local_tpdashboard/leaderboard
 * @copyright  2023 Prihantoosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

 require_once('../../config.php');
 require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tpdashboard/leaderboard.php'));
$PAGE->set_pagelayout('dashboard');
$PAGE->set_title(get_string('leaderboard','local_tpdashboard'));

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_tpdashboard/leaderboard',['teks'=> 'My Leaderboard']);

echo $OUTPUT->footer();