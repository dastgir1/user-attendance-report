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
 * Form to select a program for user attendance report.
 *
 * @package    report_userattend
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$returnurl = new \moodle_url('/report/userattend/', ['userid' => $userid]);

// Select program from.
$selectprogramform = new \report_userattend\form\selectprogram($returnurl, [
    'userid' => $userid,
]);

// If the form is submitted.
if ($fromform = $selectprogramform->get_data()) {
    // TODO: Redirect is showing warning.
    redirect(new \moodle_url('/report/userattend/', [
        'userid'    => $userid,
        'programid' => $fromform->program,
    ]));
}

echo $selectprogramform->render(); // Display the search form.
