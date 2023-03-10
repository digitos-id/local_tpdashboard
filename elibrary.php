<?php
//...

/**
 * Custom Dashboard local version details
 *
 * @package    local_tpdashboard/elibrary
 * @copyright  2023 Prihantoosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once('../../config.php');
 require_login();
 
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tpdashboard/elibrary.php'));
$PAGE->set_pagelayout('dashboard');
// $PAGE->set_title(get_string('pluginname','local_tpdashboard'));

$PAGE->set_title('Elibrary');

echo $OUTPUT->header();

$data = [
  'title'=> 'Elibrary',
  'description' => format_text($description, FORMAT_HTML),
];

echo $OUTPUT->render_from_template('local_tpdashboard/elibrary',[
  'title'=> 'Elibrary'
]);

echo $OUTPUT->footer();
