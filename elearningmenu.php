<?php
//...

/**
 * Custom Dashboard local version details
 *
 * @package    local_tpdashboard/elearningmenu
 * @copyright  2023 Prihantoosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

 global $USER;

 require_once('../../config.php');
 require_once($CFG->dirroot.'/user/profile/lib.php');
 
 $context = context_system::instance();
 $PAGE->set_context($context);
 $PAGE->set_url(new moodle_url('/local/tpdashboard/elearningmenu.php'));
 $PAGE->set_pagelayout('dashboard');
 $PAGE->set_title(get_string('myprofiletitle','local_tpdashboard'));
 
 $userid = $USER->id;
 $user_object = core_user::get_user($userid);
 
 $firstname = $USER->firstname;
 $subholding = $USER->profile[subholding];
 $subcompany = $USER->profile[subcompany];
 $email = $USER->email;
 
 // echo $USER->profile[subholding];
 //    var_dump($USER);
 //    die();
 
 $description = "<b>description</b>";
  $data = [
         'title1' => "",
         'title2' => "",
         'firstname' => $firstname,
         'email' => $email,
         'subholding' => $subholding,
         'subcompany' => $subcompany,
         'description' => format_text($description, FORMAT_HTML),
         'url_tentangtrec' => "https://enlight-dev.digitos.id/local/tpdashboard/tentangtrec.php"
  ];
 // var_dump($data);
  // die();
  //
 
 echo $OUTPUT->header();
 echo $OUTPUT->render_from_template('local_tpdashboard/elearningmenu_template',$data);
 // echo '<h1>Personal Dashboard</h1>';
 
 echo $OUTPUT->footer();

