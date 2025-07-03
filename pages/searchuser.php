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
 * This file handles the search functionality for users in the report_userattend plugin.
 *
 * @package    report_userattend
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Search the user.
$searchuserform = new \report_userattend\form\searchuser();

// If the form is submitted.
if ($fromform = $searchuserform->get_data()) {
    $searchuser = $fromform->searchuser;

    // Search the users in the database.
    $users = $DB->get_records_select('user', 'email LIKE :email OR firstname LIKE :firstname OR lastname LIKE :lastname',
        ['email' => '%' . $searchuser . '%', 'firstname' => '%' . $searchuser . '%', 'lastname' => '%' . $searchuser . '%'], 'email', 'id, email, firstname, lastname');

    // Initiate the search user table.
    $searchusertable          = new html_table();
    $searchusertable->head    = ['Search Result', ''];
    $searchusertable->align   = ['left', 'left'];
    $searchusertable->data    = [];

    if (empty($users)) {
        $searchusertable->data[] = [get_string('report_norecord', 'local_autocert'), ''];
    } else {
        foreach ($users as $user) {
            $searchusertable->data[] = [
                html_writer::link(new moodle_url('/user/profile.php', ['id' => $user->id]), $user->email),
                $OUTPUT->single_button(new moodle_url('/report/userattend/index.php', ['userid' => $user->id]), 'Select to proceed'),
            ];
        }
    }

    // Add the table to the content.
    $searchcontent = html_writer::table($searchusertable);
}

echo $searchuserform->render();     // Display the search form.
echo $searchcontent ?? '';            // Display the search content.
