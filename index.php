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
 * Display User Attendance report
 *
 * @package    report_userattend
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// FILES.
global $CFG,$PAGE;
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
// $PAGE->requires->css('/report/userattend/assets/css/styles.min.css');
// $PAGE->requires->js('/report/userattend/assets/js/script.min.js');
require_once('lib.php');

// Admin settings.
admin_externalpage_setup('userattend_report');

// PARAMS.
$userid = optional_param('userid', 0, PARAM_INT);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_userattend'));

if ($userid === 0) {
    // Search filter where admin can search for a student.
    include_once('pages/searchuser.php');
} else {
    // A search filter having all the programs in which the selected user is enrolled.
    include_once('pages/processuser.php');
}

echo $OUTPUT->footer();
