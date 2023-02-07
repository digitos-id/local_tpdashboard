<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>;.

/**
 * @package     local_pintar_analytics
 * @copyright   2022 Prihantoosa <toosa@digitos.id> 
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tpdashboard/index.php'));
$PAGE->set_pagelayout('dashboard');
# $PAGE->set_title($SITE->fullname);
$PAGE->set_title(get_string('pluginname','local_tpdashboard'));
# $string['pluginname']='Greetings';
# $PAGE->set_heading(get_string('pluginname','local_tpdashboard'));

echo $OUTPUT->header();
# 
# if (isloggedin()) {
#     echo '<h2>PIC: ' . fullname($USER) . '</h2>';
# } else {
#     echo '<h2>Anda belum login</h2>';
# }

# echo '<h2>Greetings, user</h2>';
echo $OUTPUT->render_from_template('local_tpdashboard/index_template',['teks'=> 'Profile']);
# ('block_pintar_analytic/chart1i_template', ['id' => $courseid, 'data_group' => $arr_group]);

// 
// Membaca data yang dikirim melalui URL berupa array yang dikirim menggunakan 
// $url + http_build_query($dataid);
//
$idArray = explode('&',$_SERVER["QUERY_STRING"]);
foreach ($idArray as $index => $avPair) {
 list($ignore, $value) = explode('=',$avPair);
 $id[$index] = $value;
}

echo $OUTPUT->footer();