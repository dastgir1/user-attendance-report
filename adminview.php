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
 * Display admin Attendance report.
 *
 * @package    report_userattend
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_admin();

// Set the userid.
$userid = optional_param('userid', 0, PARAM_INT);
// Set the batchid.
$batchid = optional_param('batch', 0, PARAM_INT);
// Set the programid.
$programid = optional_param('programid', 0, PARAM_INT);

if ($userid === 0) {
    // Search filter where admin can search for a student.
    require_once('pages/searchuser.php');
} else if ($batchid === 0) {
    // Set $returnurl to be used in selectprogram.php file.
    $returnurl = new \moodle_url('/report/userattend/', ['userid' => $userid]);

    require_once('pages/selectbatch.php');
}else if($programid === 0) {
    // If a batch is selected, display the SelectProgram form.
    $returnurl = new \moodle_url('/report/userattend/', ['userid' => $userid, 'batch' => $batchid]);
    require_once('pages/selectprogram.php');

}else {
    // If a program is selected, display the attendance report.
    $returnurl = new \moodle_url('/report/userattend/', ['userid' => $userid, 'programid' => $programid]);
    require_once('pages/attendancereport.php');
}
