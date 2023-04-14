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
require_once($CFG->dirroot . '/cohort/lib.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tpdashboard/dashboard.php'));
$PAGE->set_pagelayout('dashboard');
// $PAGE->set_title(get_string('pluginname','local_tpdashboard'));

$cohorts = cohort_get_user_cohorts($USER->id);
$can_viewdashboard = false;

foreach ($cohorts as $id => $cohort) {
  if ($cohort->idnumber == 'viewdashboard') {
    $can_viewdashboard = true;
    continue;
  }
}

$PAGE->set_title('DASBOARD TREC');

echo $OUTPUT->header();

if ($can_viewdashboard) {
  echo $OUTPUT->render_from_template('local_tpdashboard/dashboard',[
    'teks'=> 'Dashboard TREC'
  ]);
} else {
  $back_url = new moodle_url('/local/tpdashboard/elearningmenu.php');
  echo $OUTPUT->render_from_template('local_tpdashboard/dashboard_unauth',[
    'teks'=> 'Dashboard TREC',
    'back_url' => $back_url
  ]);
}

echo $OUTPUT->footer();
