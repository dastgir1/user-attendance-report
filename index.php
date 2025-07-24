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
require_once('../../config.php');
require_once('lib.php');

// PAGE.
$pluginname = get_string('pluginname', 'report_userattend');
$PAGE->set_url(new moodle_url('/report/userattend/'));
$PAGE->set_context(\core\context\system::instance());
$PAGE->set_title($pluginname);
$PAGE->set_heading($pluginname);

echo $OUTPUT->header();

if (is_siteadmin()) {
    require_once('adminview.php');
   
} else {
    require_once('studentview.php');
}

echo $OUTPUT->footer();
