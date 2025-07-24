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

namespace report_userattend\form;

/**
 * Class selectbatch
 *
 * @package    report_userattend
 * @copyright  2025 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class selectbatch extends \moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;
        $userid = $this->_customdata['userid'];
        
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        require_once(__DIR__ . '/../../lib.php');
        $batches = report_userattend_get_batches($userid);

        if (empty($batches)) {
            // If no batches are found, display a message.
            notice(get_string('form_nobatches', 'report_userattend'), new \moodle_url('/report/userattend/'));
        }

        $mform->addElement('select', 'batch', get_string('form_batch', 'report_userattend'), $batches);
        $mform->setType('batch', PARAM_INT);
        $mform->addRule('batch', null, 'required', null, 'client');

        // Add a submit button.
        $this->add_action_buttons(false, get_string('form_program', 'report_userattend'));
    }
}
