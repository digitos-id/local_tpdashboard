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
// https://www.nelayankode.com/2022/03/cara-membuat-popup-image-lightbox.html

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/customcert/classes/certificate.php');
require_once($CFG->dirroot . '/mod/customcert/classes/template.php');
require_once($CFG->libdir . '/pdflib.php');

require_login();

$userid = optional_param('userid', $USER->id, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', \mod_customcert\certificate::CUSTOMCERT_PER_PAGE, PARAM_INT);
$pageurl = $url = new moodle_url('/mod/customcert/my_certificates.php', array(
    'userid' => $userid,
    'page' => $page, 'perpage' => $perpage
));

// Requires a login.
if ($courseid) {
    require_login($courseid);
} else {
    require_login();
}

// Check that we have a valid user.
$user = \core_user::get_user($userid, '*', MUST_EXIST);

// If we are viewing certificates that are not for the currently logged in user then do a capability check.
if (($userid != $USER->id) && !has_capability('mod/customcert:viewallcertificates', context_system::instance())) {
    throw new moodle_exception('You are not allowed to view these certificates');
}

$PAGE->set_url($pageurl);
// $PAGE->set_context(context_user::instance($userid));
$context = context_system::instance(); 
$PAGE->set_context($context);
$PAGE->set_title(get_string('mycertificates', 'customcert'));
$PAGE->set_pagelayout('standard');
$PAGE->requires->css('/local/tpdashboard/styles.css');

$total = \mod_customcert\certificate::get_number_of_certificates_for_user($userid);
// print_r($total . " page = " . $page . " perpage = " . $perpage);
$rawdata = \mod_customcert\certificate::get_certificates_for_user($userid, $page, $perpage);
// print_r("Yudhi cek = " . $rawdata[1]->name);

foreach ($rawdata as $id => $record) {
    // echo $record->id;
    $customcert = $DB->get_record('customcert', array('id' => 1), '*', MUST_EXIST);
    $template_temp = $DB->get_record('customcert_templates', array('id' => $customcert->templateid), '*', MUST_EXIST);
    $template = new \mod_customcert\template($template_temp);
    // var_dump($template_temp->name);
    $name = $template_temp->name;
    $filename = rtrim(format_string($name, true, ['context' => $template->get_context()]), '.');
    // $output = $template->generate_pdf(false, $userid, true);
    // exit();
    
    // Create the pdf object.
    $pdf = new \pdf();

    if ($pages = $DB->get_records('customcert_pages', array('templateid' => $customcert->templateid, 'sequence' => 1), 'sequence ASC')) {

        $customcert = $DB->get_record('customcert', ['templateid' => $template_temp->id]);

        // If the template belongs to a certificate then we need to check what permissions we set for it.
        if (!empty($customcert->protection)) {
            $protection = explode(', ', $customcert->protection);
            $pdf->SetProtection($protection);
        }

        if (empty($customcert->deliveryoption)) {
            $deliveryoption = \mod_customcert\certificate::DELIVERY_OPTION_INLINE;
        } else {
            $deliveryoption = $customcert->deliveryoption;
        }

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetTitle($filename);
        $pdf->SetAutoPageBreak(true, 0);

        // This is the logic the TCPDF library uses when processing the name. This makes names
        // such as 'الشهادة' become empty, so set a default name in these cases.
        $filename = preg_replace('/[\s]+/', '_', $filename);
        $filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $filename);

        if (empty($filename)) {
            $filename = get_string('certificate', 'customcert');
        }

        $filename = clean_filename($filename);

        // Loop through the pages and display their content.
        foreach ($pages as $page) {
            // Add the page to the PDF.
            if ($page->width > $page->height) {
                $orientation = 'L';
            } else {
                $orientation = 'P';
            }
            $pdf->AddPage($orientation, array($page->width, $page->height));
            $pdf->SetMargins($page->leftmargin, 0, $page->rightmargin);
            // Get the elements for the page.
            if ($elements = $DB->get_records('customcert_elements', array('pageid' => $page->id), 'sequence ASC')) {
                // Loop through and display.
                foreach ($elements as $element) {
                    // Get an instance of the element class.
                    if ($e = \mod_customcert\element_factory::get_element_instance($element)) {
                        $e->render($pdf, $preview, $user);
                    }
                }
            }
        }
    }

    $output = $pdf->Output('', 'S');

    $im = new Imagick();
    $im->setResolution(300, 300);     //set the resolution of the resulting jpg
    $im->readImageBlob($output);    //[0] for the first page
    $im->setImageFormat('jpg');
    $certificate_file_base = $filename . '_' . $userid; 
    $certificate_file_path = 'pix/certificate/' . $certificate_file_base . '.jpg'; 
    $im->writeImage($certificate_file_path);
    // header('Content-Type: image/jpeg');
    // echo $im;
    // echo '<img src="data:image/jpg;base64,'.base64_encode($im->getImageBlob()).'" alt="" />';]
    $file_data = [
        "filename" => $certificate_file_path,
        "filename_base" => $certificate_file_base,
        "name" => $name
    ];
    $filename_list[] = $file_data;
    // print_r($filename_list);

}

echo $OUTPUT->header();

$data = [
    'certificate_list' => $filename_list,
    'title' => "Ladder Dashboard"
];

echo $OUTPUT->render_from_template('local_tpdashboard/mycertificate', $data);

echo $OUTPUT->footer();
