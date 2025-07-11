<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Display student Attendance report for current user.
 *
 * @package    report_userattend
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_brickfield\local\areas\mod_choice\option;

// FILES.
require_once('../../config.php');
require_once('lib.php');
require_login();
$url = new moodle_url('/report/userattend/studentview.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

echo $OUTPUT->header();
$userid = $USER->id;
echo $OUTPUT->heading(get_string('pluginname', 'report_userattend'));
$returnurl = new \moodle_url('/report/userattend/studentview.php', ['userid' => $userid]);

// Select program from.
$selectprogramform = new \report_userattend\form\selectprogram($returnurl, [
    'userid' => $userid,
]);

echo $selectprogramform->render();
// If the form is submitted.
if ($fromform = $selectprogramform->get_data()) {
    $programid = $fromform->program;    
    // Generate attendance reports for the user across all courses in a program.
    $context = report_userattend_get_context_of_user_attendance_report_in_program($userid, $programid);

    echo $OUTPUT->render_from_template('report_userattend/attendance_report', $context);
}

echo $OUTPUT->footer();
