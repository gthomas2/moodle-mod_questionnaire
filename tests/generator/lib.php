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
 * mod_questionnaire data generator
 *
 * @package    mod_questionnaire
 * @copyright  2015 Mike Churchward (mike@churchward.ca)
 * @author     Mike Churchward
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


class mod_questionnaire_generator extends testing_module_generator {

    /**
     * Create a questionnaire activity.
     * @param array $record
     * @param array $options
     * @return int
     */
    public function create_instance($record = array(), array $options = null) {
        $record = (object)$record;

        $defaultquestionnairesettings = array(
            'qtype'                 => 0,
            'respondenttype'        => 'fullname',
            'resp_eligible'         => 'all',
            'resp_view'             => 0,
            'opendate'              => 0,
            'closedate'             => 0,
            'resume'                => 0,
            'navigate'              => 0,
            'grade'                 => 0,
            'sid'                   => 0,
            'timemodified'          => time(),
            'completionsubmit'      => 0,
            'autonum'               => 3,
            'create'                => 'new-0',        // Used in form only to indicate a new, empty instance.
        );

        foreach ($defaultquestionnairesettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }

    /**
     * Create a survey instance with data from an existing questionnaire object.
     * @param object $questionnaire
     * @param array $options
     * @return int
     */
    public function create_content($questionnaire, $record = array()) {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/locallib.php');

        $survey = $DB->get_record('questionnaire_survey', array('id' => $questionnaire->sid), '*', MUST_EXIST);
        foreach ($record as $name => $value) {
            $survey->{$name} = $value;
        }
        return $questionnaire->survey_update($survey);
    }

    /**
     * Create an default question as a generic object.
     * @param integer $qtype The question type to create.
     * @param array $questiondata Any data to load into the question.
     * @param array $choicedata Any choice data for the question.
     * @return object
     */
    public function create_question($qtype, $questiondata = array(), $choicedata = array()) {
        global $DB;

        // Construct a new question object.
        $question = questionnaire_question_base::question_builder($qtype);
        $questiondata= (object)$questiondata;
        $question->add($questiondata, $choicedata);

        return $question;
    }

    /**
     * Create a check box question type as a question object.
     * @param integer $surveyid
     * @param array $questiondata
     * @param array $choicedata
     * @return object
     */
    public function create_question_checkbox($surveyid, $questiondata = array(), $choicedata = array()) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

        $question = $this->create_question(QUESCHECK, array('survey_id' => $surveyid) + $questiondata, $choicedata);
        return new questionnaire_question_check($question->qid);
    }

    /**
     * Create a date question type as a question object.
     * @param integer $surveyid
     * @param array $questiondata
     * @return object
     */
    public function create_question_date($surveyid, $questiondata = array()) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

        $question = $this->create_question(QUESDATE, array('survey_id' => $surveyid) + $questiondata);
        return new questionnaire_question_date($question->qid);
    }

    /**
     * Create a dropdown question type as a question object.
     * @param integer $surveyid
     * @param array $questiondata
     * @param array $choicedata
     * @return object
     */
    public function create_question_dropdown($surveyid, $questiondata = array(), $choicedata = array()) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

        $question = $this->create_question(QUESDROP, array('survey_id' => $surveyid) + $questiondata, $choicedata);
        return new questionnaire_question_drop($question->qid);
    }

    /**
     * Create an essay question type as a question object.
     * @param integer $surveyid
     * @param array $questiondata
     * @return object
     */
    public function create_question_essay($surveyid, $questiondata = array()) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

        $questiondata['survey_id'] = $surveyid;
        $questiondata['length'] = 0;
        $questiondata['precise'] = 5;
        $question = $this->create_question(QUESESSAY, $questiondata);
        return new questionnaire_question_essay($question->qid);
    }

    /**
     * Create a sectiontext question type as a question object.
     * @param integer $surveyid
     * @param array $questiondata
     * @return object
     */
    public function create_question_sectiontext($surveyid, $questiondata = array()) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

        $question = $this->create_question(QUESSECTIONTEXT, array('survey_id' => $surveyid) + $questiondata);
        return new questionnaire_question_sectiontext($question->qid);
    }

    /**
     * Create a numeric question type as a question object.
     * @param integer $surveyid
     * @param array $questiondata
     * @return object
     */
    public function create_question_numeric($surveyid, $questiondata = array()) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

        $questiondata['survey_id'] = $surveyid;
        $questiondata['length'] = 10;
        $questiondata['precise'] = 0;
        $question = $this->create_question(QUESNUMERIC, $questiondata);
        return new questionnaire_question_numeric($question->qid);
    }

    /**
     * Create a radio button question type as a question object.
     * @param integer $surveyid
     * @param array $questiondata
     * @param array $choicedata
     * @return object
     */
    public function create_question_radiobuttons($surveyid, $questiondata = array(), $choicedata = array()) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

        $question = $this->create_question(QUESRADIO, array('survey_id' => $surveyid) + $questiondata, $choicedata);
        return new questionnaire_question_radio($question->qid);
    }

    /**
     * Create a ratescale question type as a question object.
     * @param integer $surveyid
     * @param array $questiondata
     * @param array $choicedata
     * @return object
     */
    public function create_question_ratescale($surveyid, $questiondata = array(), $choicedata = array()) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

        $question = $this->create_question(QUESRATE, array('survey_id' => $surveyid) + $questiondata, $choicedata);
        return new questionnaire_question_rate($question->qid);
    }

    /**
     * Create a textbox question type as a question object.
     * @param integer $surveyid
     * @param array $questiondata
     * @return object
     */
    public function create_question_textbox($surveyid, $questiondata = array()) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

        $questiondata['survey_id'] = $surveyid;
        $questiondata['length'] = 20;
        $questiondata['precise'] = 25;
        $question = $this->create_question(QUESTEXT, $questiondata);
        return new questionnaire_question_text($question->qid);
    }

    /**
     * Create a yes/no question type as a question object.
     * @param integer $surveyid
     * @param array $questiondata
     * @return object
     */
    public function create_question_yesno($surveyid, $questiondata = array()) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/questiontypes.class.php');

        $question = $this->create_question(QUESYESNO, array('survey_id' => $surveyid) + $questiondata);
        return new questionnaire_question_yesno($question->qid);
    }
}