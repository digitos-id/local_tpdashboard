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
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/course/classes/category.php');

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

$PAGE->set_pagetype('my-index');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title(get_string('mycourses'));
$PAGE->requires->css('/local/tpdashboard/styles.css');
// $PAGE->set_heading(get_string('mycourses'));

$courses = enrol_get_all_users_courses($USER->id);
// var_dump($courses);

// $courses_inc_not_enroll = enrol_get_my_courses();
// var_dump($courses_inc_not_enroll);

$allcourse = get_courses();
// var_dump($allcourse);

$cohorts = cohort_get_user_cohorts($USER->id);
// var_dump($cohorts);

// $courses_cohorts = [];

foreach ($cohorts as $id => $cohort) {
    // echo "idcohort = " . $cohort->idnumber;
    $tags = core_tag_tag::guess_by_name($cohort->idnumber, '*');
    // var_dump($tags);
    foreach ($tags as $id => $tag) {
        $search_param['tagid'] = $id;
        $course_search = \core_course_category::search_courses($search_param);
        $courses_cohorts[] = $course_search;
    }
    // $courses_cohort = course_get_tagged_courses($cohort->idnumber);
    // $search_param = array("tagid" => array("SB"));
}
// echo "cohorts courses";
// var_dump($courses_cohorts);
$courses_rec = array();

foreach ($courses_cohorts as $courses_tag) {
    foreach ($courses_tag as $id => $course) {
        // echo "name " . $course->fullname . "\n";
        if (isset($courses[$id]) || isset($courses_rec[$id])) continue;
        // $coursemetadata = get_course_metadata($course);
        // var_dump($coursemetadata);
        // echo "course blm enrol " . $course->fullname . "<br>";
        $courses_rec[$id] = $course;
        
    }
}

// var_dump($courses_rec);

foreach ($courses_rec as $id => $course) {
    $courseimage = \core_course\external\course_summary_exporter::get_course_image($course);
    if (!$courseimage) {
        $courseimage = $OUTPUT->get_generated_image_for_id($id);
    }
    $courseurl = new moodle_url('/course/view.php', array('id' => $id));

    $course_data = [
        "image_url" => $courseimage,
        "name" => $course->fullname,
        "course_url" => $courseurl
    ];

    $course_list[] = $course_data;
}

$data = [
    'courses_list' => $course_list,
    'title2' => "Ladder Dashboard"
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_tpdashboard/mycourse', $data);

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

function get_course_metadata($course)
{
    if ($course instanceof stdClass) {
        $course = new core_course_list_element($course);
    }
    $handler = \core_customfield\handler::get_handler('core_course', 'course');
    // This is equivalent to the line above.
    //$handler = \core_course\customfield\course_handler::create();
    $customdata = (array)$handler->export_instance_data_object($course->id);
    $final = [];
    foreach ($course->get_custom_fields() as $field) {

        if ($handler->can_view($field->get_field(), $course->id)) {
            if (array_key_exists($field->get_field()->get('shortname'), $customdata)) {
                $final[$field->get_field()->get('shortname')] = array(
                    'name' => $field->get_field()->get('name'),
                    'shortname' => $field->get_field()->get('shortname'),
                    'description' => $field->get_field()->get('description'),
                    'type' => $field->get_field()->get('type'),
                    'data' => $customdata[$field->get_field()->get('shortname')]
                );
            }
        }
    }

    return $final;
}
