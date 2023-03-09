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

use local_tpdashboard\form\profile;

require_once('../../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once('classes/form/profile.php');

require_login();

 $context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tpdashboard/myprofile.php'));
$PAGE->set_pagelayout('dashboard');
$PAGE->set_title(get_string('myprofiletitle','local_tpdashboard'));

$userid = $USER->id;
$user_object = core_user::get_user($userid);
$user = $DB->get_record('user', array('id' => $userid));
// die (fullname($user));
// print_r ($USER);
// print_r($USER->profile['subholding']);
// $PAGE->set_title("Test");
// $PAGE->set_heading(fullname($user));
// $tab = optional_param('t', 1, PARAM_INT);
// $tabs = [];
// $tabs[] = new tabobject(1, new moodle_url($url, ['t'=>1]), $tab1_title);
// $tabs[] = new tabobject(2, new moodle_url($url, ['t'=>2]), $tab2_title);
// echo $OUTPUT->tabtree($tabs, $tab);

$mformprofile = new profile();
echo $OUTPUT->header();
echo $OUTPUT->heading($userfullname);

# Untuk Echo langsung image berikut html
# $conditions = array('size' => '100', 'link' => false, 'class' => '');
# $person_profile_pic = $OUTPUT->user_picture($user_object, $conditions);
# echo $person_profile_pic;

 $data = [
     'profileimgurl' => getprofilepictureurl($user_object),
     'description' => format_text($description, FORMAT_HTML),
     'userfullname' => fullname($user),
     'email' => $USER->email,
     'phone1' => $USER->phone1,
     'subholding' => $USER->profile['subholding'],
     'profileform' => $mformprofile->render()
    // 'subholding' => "PT A A"
 ];

echo $OUTPUT->render_from_template('local_tpdashboard/myprofile_template',$data);

echo $OUTPUT->footer();


 /**
     * Retrieves the URL for the user's profile picture, if one is available.
     * 
     * @param object $user The Moodle user object for which we want a photo.
     * @return string URL to the photo image file but with $1 for the size.
     */
function getprofilepictureurl($user) { 
        if (isloggedin() && !isguestuser() && $user->picture > 0) {
            $usercontext = context_user::instance($user->id, IGNORE_MISSING);
            $url = moodle_url::make_pluginfile_url($usercontext->id, 'user', 'icon', null, '/', "f$1")
                    . '?rev=' . $user->picture;
        } else {
            // If the user does not have a profile picture, use the default faceless picture.
            global $PAGE, $CFG;
            $renderer = $PAGE->get_renderer('core');
            if ($CFG->branch >= 33) {
                $url = $renderer->image_url('u/f$1');
            } else {
                $url = $renderer->pix_url('u/f$1'); // Deprecated as of Moodle 3.3.
            }
        }
        return str_replace('/f%24', '/f$', $url);
    }
