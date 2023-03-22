<?php
//...

/**
 * Custom Dashboard local version details
 *
 * @package    local_tpdashboard/myprofile
 * @copyright  2023 Prihantoosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

require_once('../../config.php');
require_once($CFG->libdir . '/enrollib.php');
require_once('../../course/renderer.php');
require_once($CFG->dirroot . '/course/renderer.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');

redirect_if_major_upgrade_required();

require_login();

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}

$context = context_system::instance();

// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page(null, MY_PAGE_PUBLIC, MY_PAGE_COURSES)) {
    throw new Exception('mymoodlesetup');
}

$url = new moodle_url('/local/tpdashboard/mycourse.php');

// Start setting up the page.
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->add_body_classes(['limitedwidth', 'page-mycourses']);
// $PAGE->set_pagelayout('mycourses');

// $PAGE->set_pagetype('my-index');
// $PAGE->blocks->add_region('content');
// $PAGE->set_subpage($currentpage->id);
$PAGE->set_title(get_string('mycourses'));
$PAGE->requires->css('/local/tpdashboard/styles.css');
// $PAGE->set_heading(get_string('mycourses'));

$courses = enrol_get_all_users_courses($USER->id);

echo $OUTPUT->header();

$totalcourse = count($courses);

// if ($totalcourse) {
//     # code...
// }

$table = new html_table();
$table->head = array();
$table->colclasses = array();
$table->attributes['class'] = 'admintable generaltable table-sm';
$table->head[] = "Bulan / Tahun";
$table->colclasses[] = 'centeralign';
$table->head[] = "Histori Pembelajaran";
$table->colclasses[] = 'centeralign';
$table->head[] = "Status";
$table->colclasses[] = 'centeralign';
$table->head[] = "Nilai";
$table->colclasses[] = 'centeralign';

$table->id = "courses";

foreach ($courses as $id => $course) {
    $courseimage = \core_course\external\course_summary_exporter::get_course_image($course);
    if (!$courseimage) {
        $courseimage = $OUTPUT->get_generated_image_for_id($id);
    }
    $courseurl = new moodle_url('/course/view.php', array('id' => $id));
    $grade = grade_get_course_grade($USER->id, $id);
    $progress = \core_completion\progress::get_course_progress_percentage($course, $USER->id);

    // print_r($progress);

    // $row = array();
    // $row[] = $id;
    // $row[] = $course->fullname;
    // $cell = new html_table_cell('TEXT');
    // $row->cells[] = $cell;
    // if ($progress < 100) {
    //     $row[] = "Progres";
    //     # code...
    // }
    // $row[] = $grade->grade;
    $row = new html_table_row();
    $datecell = new html_table_cell(userdate($course->startdate, '%B %Y'));
    $row->cells[] = $datecell;
    $namecell = new html_table_cell($course->fullname);
    $row->cells[] = $namecell;
    if ($progress == 100) {
        $statuscell = new html_table_cell("Sukses");
        $statuscell->style = "background-color: green;color: white;";
        $gradecell = new html_table_cell(number_format($grade->grade,2));
    } else {
        $statuscell = new html_table_cell("Progress");
        $statuscell->style = "background-color: red;color: white;";
        $gradecell = new html_table_cell("");
    }
    $row->cells[] = $statuscell;
    $row->cells[] = $gradecell;
    
    $table->data[] = $row;

    $course_data = [
        "image_url" => $courseimage,
        "name" => $course->fullname,
        "course_url" => $courseurl
    ];

    $course_list[] = $course_data;
}
$output = '';
if (!empty($table)) {
    $output .= html_writer::start_tag('div', array('class' => 'no-overflow'));
    $output .= html_writer::table($table);
    $output .= html_writer::end_tag('div');
    // echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
}

$data = [
    'courses_list' => $output,
    'title2' => "Ladder Dashboard"
];


echo $OUTPUT->render_from_template('local_tpdashboard/myhistory', $data);

echo $OUTPUT->footer();


function get_course_image()
{
    global $COURSE;
    $url = '';
    require_once($CFG->libdir . '/filelib.php');
    $context = context_course::instance($COURSE->id);
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0);
    foreach ($files as $f) {
        if ($f->is_valid_image()) {
            $url = moodle_url::make_pluginfile_url($f->get_contextid(), $f->get_component(), $f->get_filearea(), null, $f->get_filepath(), $f->get_filename(), false);
        }
    }
    return $url;
}
