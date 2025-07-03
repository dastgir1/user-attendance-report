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

require_once("$CFG->libdir/formslib.php");

/**
 * Class searchuser
 *
 * @package    report_userattend
 * @copyright  2025 Syed Zonair <zonair@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class searchuser extends \moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        // Add a text field for search user.
        $mform->addElement('text', 'searchuser', get_string('form_searchuser', 'report_userattend'));
        $mform->setType('searchuser', PARAM_TEXT);
        $mform->addRule('searchuser', null, 'required', null, 'client');

        // Add a submit button.
        $this->add_action_buttons(false, get_string('form_searchbtn', 'report_userattend'));
    }
}
