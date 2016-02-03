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

namespace mod_questionnaire\response;

use mod_questionnaire\response\response;

defined('MOODLE_INTERNAL') || die();

/**
 * Container for response and single response item
 * @author    gthomas2
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class response_single extends response {
    public $response;
    function set_response(response_item $response) {
        $this->response = $response;
    }
}