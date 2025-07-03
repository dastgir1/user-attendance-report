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
 * Settings for User Attendance report
 *
 * @package    report_userattend
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Report page.
$ADMIN->add('reports',
    new admin_externalpage('userattend_report',
        get_string('settings_reportpage', 'report_userattend'),
        new moodle_url("/report/userattend/index.php"),
    )
);

// Configuration page.
$page = new admin_settingpage('userattend_config', get_string('settings_configpage', 'report_userattend'));

// Batch selection setting.
$page->add(
    new admin_setting_configselect(
        'report_userattend/batch',
        get_string('settings_batch', 'report_userattend'),
        get_string('settings_batch_desc', 'report_userattend'),
        null,
        $DB->get_records_menu('course_categories', ['parent' => 0], 'name', 'id, name'),
    )
);

$ADMIN->add('reports', $page);
