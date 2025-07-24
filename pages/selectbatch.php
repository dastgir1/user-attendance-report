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
 * TODO describe file selectbatch
 *
 * @package    report_userattend
 * @copyright  2025 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Select batch from.
$selectbatchform = new \report_userattend\form\selectbatch($returnurl, ['userid' => $userid]);
echo $selectbatchform->render(); // Display the Select Batch form.

// If the form is submitted.
if ($fromform = $selectbatchform->get_data()) {
    $batchid = $fromform->batch; // Set batchid. It is necessary.  
    // If a batch is selected, display the SelectProgram form.
    if ($batchid === 0) {
        
        require_once('pages/selectbatch.php');
    } else {
        
        $returnurl = new \moodle_url('/report/userattend/', ['userid' => $userid, 'batch' => $batchid]);

        require_once('pages/selectprogram.php');
    }
}
