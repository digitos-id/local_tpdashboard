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

// use local_tpdashboard\form\profile;

require_once('../../config.php');
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once('classes/form/profile.php');
require_once('classes/form/password.php');

// $userid = optional_param('id', $USER->id, PARAM_INT);    // User id.
$userid = $USER->id;

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tpdashboard/myprofile_new.php'));
$PAGE->set_pagelayout('dashboard');
$PAGE->set_title(get_string('myprofiletitle','local_tpdashboard'));

// Guest can not edit.
if (isguestuser()) {
    print_error('guestnoeditprofile');
}

// The user profile we are editing.
if (!$user = $DB->get_record('user', array('id' => $userid))) {
    print_error('invaliduserid');
}

// Guest can not be edited.
if (isguestuser($user)) {
    print_error('guestnoeditprofile');
}

// Load the appropriate auth plugin.
$userauth = get_auth_plugin($user->auth);

if (!$userauth->can_edit_profile()) {
    print_error('noprofileedit', 'auth');
}

if ($editurl = $userauth->edit_profile_url()) {
    // This internal script not used.
    redirect($editurl);
}

$systemcontext   = context_system::instance();
$personalcontext = context_user::instance($user->id);

// Check access control.
if ($user->id == $USER->id) {
    // Editing own profile - require_login() MUST NOT be used here, it would result in infinite loop!
    if (!has_capability('moodle/user:editownprofile', $systemcontext)) {
        print_error('cannotedityourprofile');
    }

} else {
    // Teachers, parents, etc.
    require_capability('moodle/user:editprofile', $personalcontext);
    // No editing of guest user account.
    if (isguestuser($user->id)) {
        print_error('guestnoeditprofileother');
    }
    // No editing of primary admin!
    if (is_siteadmin($user) and !is_siteadmin($USER)) {  // Only admins may edit other admins.
        print_error('useradmineditadmin');
    }
}

$PAGE->set_pagelayout('admin');
$PAGE->add_body_class('limitedwidth');
// $PAGE->set_context($personalcontext);
if ($USER->id != $user->id) {
    $PAGE->navigation->extend_for_user($user);
} else {
    if ($node = $PAGE->navigation->find('myprofile', navigation_node::TYPE_ROOTNODE)) {
        $node->force_open();
    }
}

$userid = $USER->id;
$user_object = core_user::get_user($userid);
// $user = $DB->get_record('user', array('id' => $userid));
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

// Load user preferences.
useredit_load_preferences($user);

// Load custom profile fields data.
profile_load_data($user);

// Prepare the editor and create form.
$editoroptions = array(
    'maxfiles'   => EDITOR_UNLIMITED_FILES,
    'maxbytes'   => $CFG->maxbytes,
    'trusttext'  => false,
    'forcehttps' => false,
    'context'    => $personalcontext
);

$user = file_prepare_standard_editor($user, 'description', $editoroptions, $personalcontext, 'user', 'profile', 0);
// Prepare filemanager draft area.
$draftitemid = 0;
$filemanagercontext = $editoroptions['context'];
$filemanageroptions = array('maxbytes'       => $CFG->maxbytes,
                             'subdirs'        => 0,
                             'maxfiles'       => 1,
                             'accepted_types' => 'optimised_image');
file_prepare_draft_area($draftitemid, $filemanagercontext->id, 'user', 'newicon', 0, $filemanageroptions);
$user->imagefile = $draftitemid;

$mformprofile = new profile('', array(
    'editoroptions' => $editoroptions,
    'filemanageroptions' => $filemanageroptions, 
    'user' => $user));

// Deciding where to send the user back in most cases.
if ($returnto === 'profile') {
    if ($course->id != SITEID) {
        $returnurl = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id));
    } else {
        $returnurl = new moodle_url('/user/profile.php', array('id' => $user->id));
    }
} else {
    $returnurl = new moodle_url('/user/preferences.php', array('userid' => $user->id));
}

$returnurl = new moodle_url('/local/tpdashboard/myprofile_new.php');

if ($mformprofile->is_cancelled()) {
    redirect($returnurl);
} else if ($usernew = $mformprofile->get_data()) {

    $emailchangedhtml = '';

    if ($CFG->emailchangeconfirmation) {
        // Users with 'moodle/user:update' can change their email address immediately.
        // Other users require a confirmation email.
        if (isset($usernew->email) and $user->email != $usernew->email && !has_capability('moodle/user:update', $systemcontext)) {
            $a = new stdClass();
            $emailchangedkey = random_string(20);
            set_user_preference('newemail', $usernew->email, $user->id);
            set_user_preference('newemailkey', $emailchangedkey, $user->id);
            set_user_preference('newemailattemptsleft', 3, $user->id);

            $a->newemail = $emailchanged = $usernew->email;
            $a->oldemail = $usernew->email = $user->email;

            $emailchangedhtml = $OUTPUT->box(get_string('auth_changingemailaddress', 'auth', $a), 'generalbox', 'notice');
            $emailchangedhtml .= $OUTPUT->continue_button($returnurl);
        }
    }

    $authplugin = get_auth_plugin($user->auth);

    $usernew->timemodified = time();

    // Description editor element may not exist!
    if (isset($usernew->description_editor) && isset($usernew->description_editor['format'])) {
        $usernew = file_postupdate_standard_editor($usernew, 'description', $editoroptions, $personalcontext, 'user', 'profile', 0);
    }

    // Pass a true old $user here.
    if (!$authplugin->user_update($user, $usernew)) {
        // Auth update failed.
        print_error('cannotupdateprofile');
    }

    // Update user with new profile data.
    user_update_user($usernew, false, false);

    // Update preferences.
    useredit_update_user_preference($usernew);

    // Update interests.
    if (isset($usernew->interests)) {
        useredit_update_interests($usernew, $usernew->interests);
    }

    // Update user picture.
    if (empty($CFG->disableuserimages)) {
        core_user::update_picture($usernew, $filemanageroptions);
    }

    // Update mail bounces.
    useredit_update_bounces($user, $usernew);

    // Update forum track preference.
    useredit_update_trackforums($user, $usernew);

    // Save custom profile fields data.
    profile_save_data($usernew);

    // Trigger event.
    \core\event\user_updated::create_from_userid($user->id)->trigger();

    // If email was changed and confirmation is required, send confirmation email now to the new address.
    if ($emailchanged !== false && $CFG->emailchangeconfirmation) {
        $tempuser = $DB->get_record('user', array('id' => $user->id), '*', MUST_EXIST);
        $tempuser->email = $emailchanged;

        $supportuser = core_user::get_support_user();

        $a = new stdClass();
        $a->url = $CFG->wwwroot . '/user/emailupdate.php?key=' . $emailchangedkey . '&id=' . $user->id;
        $a->site = format_string($SITE->fullname, true, array('context' => context_course::instance(SITEID)));
        $a->fullname = fullname($tempuser, true);
        $a->supportemail = $supportuser->email;

        $emailupdatemessage = get_string('emailupdatemessage', 'auth', $a);
        $emailupdatetitle = get_string('emailupdatetitle', 'auth', $a);

        // Email confirmation directly rather than using messaging so they will definitely get an email.
        $noreplyuser = core_user::get_noreply_user();
        // if (!$mailresults = email_to_user($tempuser, $noreplyuser, $emailupdatetitle, $emailupdatemessage)) {
        //     die("could not send email!");
        // }
    }

    // Reload from db, we need new full name on this page if we do not redirect.
    $user = $DB->get_record('user', array('id' => $user->id), '*', MUST_EXIST);

    if ($USER->id == $user->id) {
        // Override old $USER session variable if needed.
        foreach ((array)$user as $variable => $value) {
            if ($variable === 'description' or $variable === 'password') {
                // These are not set for security nad perf reasons.
                continue;
            }
            $USER->$variable = $value;
        }
        // Preload custom fields.
        profile_load_custom_fields($USER);
    }

    if (is_siteadmin() and empty($SITE->shortname)) {
        // Fresh cli install - we need to finish site settings.
        redirect(new moodle_url('/admin/index.php'));
    }

    if (!$emailchanged || !$CFG->emailchangeconfirmation) {
        redirect($returnurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

$mformpassword = new login_change_password_form();

if ($mformpassword->is_cancelled()) {
    redirect($CFG->wwwroot.'/user/preferences.php?userid='.$USER->id.'&amp;course='.$course->id);
} else if ($data = $mformpassword->get_data()) {

    if (!$userauth->user_update_password($USER, $data->newpassword1)) {
        print_error('errorpasswordupdate', 'auth');
    }

    user_add_password_history($USER->id, $data->newpassword1);

    if (!empty($CFG->passwordchangelogout)) {
        \core\session\manager::kill_user_sessions($USER->id, session_id());
    }

    if (!empty($data->signoutofotherservices)) {
        webservice::delete_user_ws_tokens($USER->id);
    }

    // Reset login lockout - we want to prevent any accidental confusion here.
    login_unlock_account($USER);

    // register success changing password
    unset_user_preference('auth_forcepasswordchange', $USER);
    unset_user_preference('create_password', $USER);

    $strpasswordchanged = get_string('passwordchanged');

    // Plugins can perform post password change actions once data has been validated.
    core_login_post_change_password_requests($data);

    $fullname = fullname($USER, true);

    $PAGE->set_title($strpasswordchanged);
    $PAGE->set_heading(fullname($USER));
    echo $OUTPUT->header();

    notice($strpasswordchanged, new moodle_url($PAGE->url . "#tab2", array('return'=>1)));

    echo $OUTPUT->footer();
    exit;
}

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
     'profileform' => $mformprofile->render(),
     'passwordform' => $mformpassword->render()
    // 'subholding' => "PT A A"
 ];

echo $OUTPUT->render_from_template('local_tpdashboard/myprofile_template',$data);
// $mformprofile->display();

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
