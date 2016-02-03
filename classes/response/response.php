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

defined('MOODLE_INTERNAL') || die();

/**
 * Questionnaire response
 * @author    gthomas2
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class response {
    /**
     * @var int
     */
    public $questionid;

    /**
     * @var int
     */
    public $questiontype;

    /**
     * @var int
     */
    public $responseid;

    /**
     * @var int
     */
    public $submitted;

    /**
     * @var stdClass
     */
    public $user;

    /**
     * @var string
     */
    public $grade;

    function __construct($questionid, $questiontype, $responseid, $submitted, $user, $grade = null) {
        $this->questionid = $questionid;
        $this->questiontype = $questiontype;
        $this->responseid = $responseid;
        $this->submitted = $submitted;
        $this->user = $user;
        $this->grade = $grade;
    }
    function get_response() {
        return $this->response;
    }
}