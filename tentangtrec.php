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

 $context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tpdashboard/tentangtrec.php'));
$PAGE->set_pagelayout('dashboard');
// $PAGE->set_title(get_string('pluginname','local_tpdashboard'));

$PAGE->set_title('TREC');

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_tpdashboard/tentangtrec',[
  'teks'=> 'Tentang TREC'
]);

echo $OUTPUT->footer();