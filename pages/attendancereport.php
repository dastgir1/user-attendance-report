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
 * Report will be generated as per the calculation is finalized in the document ”Student Attendance Reporting System Design Document”.
 *
 * @package    report_userattend
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// PARAMS.

$userid     = required_param('userid', PARAM_INT);
$programid  = required_param('programid', PARAM_INT);

$attendanceRepot=get_user_attendance_report($userid, $programid);



// echo $OUTPUT->render_from_template('report_userattend/attendancereport',$attendanceRepot);
echo $OUTPUT->render_from_template('report_userattend/attendance_report',$attendanceRepot);
