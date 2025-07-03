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
 * Process the selected user for attendance report generation.
 *
 * @package    report_userattend
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// PARAMS.
$userid     = required_param('userid', PARAM_INT);
$programid  = optional_param('programid', 0, PARAM_INT);

// CONSTANTS.
define('REPORT_USERATTEND_BATCH', get_config('report_userattend', 'batch'));

// Check if a program is selected.
if ($programid === 0) {
    // If no program is selected, redirect to the select a program page.
    include_once('pages/selectprogram.php');
} else {
    // If a program is selected, display the attendance report for that program & for that user.
    include_once('pages/attendancereport.php');
}