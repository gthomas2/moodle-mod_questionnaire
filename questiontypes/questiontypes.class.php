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
 * This file contains the parent class for questionnaire question types.
 *
 * @author Mike Churchward
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questiontypes
 */

/**
 * Class for describing a question
 *
 * @author Mike Churchward
 * @package questiontypes
 */

 // Constants.
define('QUESCHOOSE', 0);
define('QUESYESNO', 1);
define('QUESTEXT', 2);
define('QUESESSAY', 3);
define('QUESRADIO', 4);
define('QUESCHECK', 5);
define('QUESDROP', 6);
define('QUESRATE', 8);
define('QUESDATE', 9);
define('QUESNUMERIC', 10);
define('QUESPAGEBREAK', 99);
define('QUESSECTIONTEXT', 100);

GLOBAL $qtypenames;
$qtypenames = array(
        QUESYESNO => 'yesno',
        QUESTEXT => 'text',
        QUESESSAY => 'essay',
        QUESRADIO => 'radio',
        QUESCHECK => 'check',
        QUESDROP => 'drop',
        QUESRATE => 'rate',
        QUESDATE => 'date',
        QUESNUMERIC => 'numeric',
        QUESPAGEBREAK => 'pagebreak',
        QUESSECTIONTEXT => 'sectiontext'
        );
GLOBAL $idcounter, $CFG;
$idcounter = 0;

require_once($CFG->dirroot.'/mod/questionnaire/locallib.php');
require_once($CFG->dirroot.'/mod/questionnaire/questiontypes/responsetypes.class.php');

abstract class questionnaire_question_base {

    // Class Properties.
    /** @var int $id The database id of this question. */
    public $id          = 0;

    /** @var int $survey_id The database id of the survey this question belongs to. */
    public $survey_id   = 0;

    /** @var string $name The name of this question. */
    public $name        = '';

    /** @var string $type The name of the question type. */
    public $type        = '';

    /** @var array $choices Array holding any choices for this question. */
    public $choices     = array();

    /** @var string $response_table The table name for responses. */
    public $responsetable = '';

    /** @var int $length The length field. */
    public $length      = 0;

    /** @var int $precise The precision field. */
    public $precise     = 0;

    /** @var int $position Position in the questionnaire */
    public $position    = 0;

    /** @var string $content The question's content. */
    public $content     = '';

    /** @var string $allchoices The list of all question's choices. */
    public $allchoices  = '';

    /** @var boolean $required The required flag. */
    public $required    = 'n';

    /** @var boolean $deleted The deleted flag. */
    public $deleted     = 'n';

    // Class Methods.

    /**
     * The class constructor
     *
     */
    public function __construct($id = 0, $question = null, $context = null, $params = array()) {
        global $DB;
        static $qtypes = null;

        if (is_null($qtypes)) {
            $qtypes = $DB->get_records('questionnaire_question_type', array(), 'typeid',
                                       'typeid, type, has_choices, response_table');
        }

        if ($id) {
            $question = $DB->get_record('questionnaire_question', array('id' => $id));
        }

        if (is_object($question)) {
            $this->id = $question->id;
            $this->survey_id = $question->survey_id;
            $this->name = $question->name;
            // Added for skip feature.
            $this->dependquestion = $question->dependquestion;
            $this->dependchoice = $question->dependchoice;
            $this->length = $question->length;
            $this->precise = $question->precise;
            $this->position = $question->position;
            $this->content = $question->content;
            $this->required = $question->required;
            $this->deleted = $question->deleted;

            $this->type_id = $question->type_id;
            $this->type = $qtypes[$this->type_id]->type;
            $this->response_table = $qtypes[$this->type_id]->response_table;
            if ($qtypes[$this->type_id]->has_choices == 'y') {
                $this->get_choices();
            }
        }
        $this->context = $context;

        foreach ($params as $property => $value) {
            $this->$property = $value;
        }

        if ($respclass = $this->responseclass()) {
            $this->response = new $respclass($this);
        }
    }

    static function question_builder($qtype, $params = null) {
        global $CFG, $qtypenames;

        $qclassfile = $CFG->dirroot.'/mod/questionnaire/questiontypes/question' . $qtypenames[$qtype] . '.class.php';
        $qclassname = 'questionnaire_question_' . $qtypenames[$qtype];
        require_once($qclassfile);
        if (!empty($params) && is_array($params)) {
            $params = (object)$params;
        }
        return new $qclassname(0, $params);
    }

    private function get_choices() {
        global $DB;

        if ($choices = $DB->get_records('questionnaire_quest_choice', array('question_id' => $this->id), 'id ASC')) {
            foreach ($choices as $choice) {
                $this->choices[$choice->id] = new stdClass();
                $this->choices[$choice->id]->content = $choice->content;
                $this->choices[$choice->id]->value = $choice->value;
            }
        } else {
            $this->choices = array();
        }
    }

    /**
     * Insert response data method.
     */
    public function insert_response($rid, $val) {
        if (isset ($this->response) && is_object($this->response) && is_subclass_of($this->response, 'questionnaire_response_base')) {
            return $this->response->insert_response($rid, $val);
        } else {
            return false;
        }
    }

    /**
     * Get results data method.
     */
    public function get_results($rids = false) {
        if (isset ($this->response) && is_object($this->response) && is_subclass_of($this->response, 'questionnaire_response_base')) {
            return $this->response->get_results($rids);
        } else {
            return false;
        }
    }

    /**
     * Display results method.
     */
    public function display_results($rids=false, $sort='') {
        if (isset ($this->response) && is_object($this->response) && is_subclass_of($this->response, 'questionnaire_response_base')) {
            return $this->response->display_results($rids, $sort);
        } else {
            return false;
        }
    }

    /**
     * Each question type must define its response class.
     *
     * @return object The response object based off of questionnaire_response_base.
     *
     */
    abstract protected function responseclass();

    /**
     * Question specific display method.
     *
     * @param object $formdata
     * @param string $descendantdata
     * @param integer $qnum
     * @param boolean $blankquestionnaire
     *
     */
    abstract protected function question_survey_display($formdata, $descendantsdata, $blankquestionnaire);

    /**
     * Question specific response display method.
     *
     * @param object $data
     * @param integer $qnum
     *
     */
    abstract protected function response_survey_display($data);

    /**
     * Main function for displaying a question.
     *
     * @param object $formdata
     * @param string $descendantdata
     * @param integer $qnum
     * @param boolean $blankquestionnaire
     *
     */
    private function question_display($formdata, $descendantsdata, $qnum='', $blankquestionnaire) {
        $this->questionstart_survey_display($qnum, $formdata, $descendantsdata);
        $this->question_survey_display($formdata, $descendantsdata, $blankquestionnaire);
        $this->questionend_survey_display($qnum);
    }

    public function survey_display($formdata, $descendantsdata, $qnum='', $blankquestionnaire=false) {
        $this->question_display($formdata, $descendantsdata, $qnum, $blankquestionnaire);
    }

    public function response_display($data, $qnum='') {
        $this->questionstart_survey_display($qnum, $data);
        $this->response_survey_display($data);
        $this->questionend_survey_display($qnum);
    }

    public function questionstart_survey_display($qnum, $formdata='') {
        global $OUTPUT, $SESSION, $questionnaire, $PAGE;
        $currenttab = $SESSION->questionnaire->current_tab;
        $pagetype = $PAGE->pagetype;
        $skippedquestion = false;
        $skippedclass = '';
        $autonum = $questionnaire->autonum;
        // If no questions autonumbering.
        $nonumbering = false;
        if ($autonum != 1 && $autonum != 3) {
            $qnum = '';
            $nonumbering = true;
        }
        // If we are on report page and this questionnaire has dependquestions and this question was skipped.
        if ( ($pagetype == 'mod-questionnaire-myreport' || $pagetype == 'mod-questionnaire-report')
                        && $nonumbering == false
                        && $formdata
                        && $this->dependquestion != 0 && !array_key_exists('q'.$this->id, $formdata)) {
            $skippedquestion = true;
            $skippedclass = ' unselected';
            $qnum = '<span class="'.$skippedclass.'">('.$qnum.')</span>';
        }
        // In preview mode, hide children questions that have not been answered.
        // In report mode, If questionnaire is set to no numbering,
        // also hide answers to questions that have not been answered.
        $displayclass = 'qn-container';
        if ($pagetype == 'mod-questionnaire-preview' || ($nonumbering
                        && ($currenttab == 'mybyresponse' || $currenttab == 'individualresp'))) {
            $parent = questionnaire_get_parent ($this);
            if ($parent) {
                $dependquestion = $parent[$this->id]['qdependquestion'];
                $dependchoice = $parent[$this->id]['qdependchoice'];
                $parenttype = $parent[$this->id]['parenttype'];
                $displayclass = 'hidedependquestion';
                if (isset($formdata->{'q'.$this->id}) && $formdata->{'q'.$this->id}) {
                    $displayclass = 'qn-container';
                }

                if ($this->type_id == QUESRATE) {
                    foreach ($this->choices as $key => $choice) {
                        if (isset($formdata->{'q'.$this->id.'_'.$key})) {
                            $displayclass = 'qn-container';
                            break;
                        }
                    }
                }

                if (isset($formdata->$dependquestion) && $formdata->$dependquestion == $dependchoice) {
                    $displayclass = 'qn-container';
                }

                if ($parenttype == QUESDROP) {
                    $qnid = preg_quote('qn-'.$this->id, '/');
                    if (isset($formdata->$dependquestion) && preg_match("/$qnid/", $formdata->$dependquestion)) {
                        $displayclass = 'qn-container';
                    }
                }
            }
        }

        echo html_writer::start_tag('fieldset', array('class' => $displayclass, 'id' => 'qn-'.$this->id));
        echo html_writer::start_tag('legend', array('class' => 'qn-legend'));

        // Do not display the info box for the label question type.
        if ($this->type_id != QUESSECTIONTEXT) {
            if (!$nonumbering) {
                echo html_writer::start_tag('div', array('class' => 'qn-info'));
                echo html_writer::start_tag('div', array('class' => 'accesshide'));
                echo get_string('questionnum', 'questionnaire');
                echo html_writer::end_tag('div');
                echo html_writer::tag('h2', $qnum, array('class' => 'qn-number'));
                echo html_writer::end_tag('div');
            }
            $required = '';
            if ($this->required == 'y') {
                $required = html_writer::start_tag('div', array('class' => 'accesshide'));
                $required .= get_string('required', 'questionnaire');
                $required .= html_writer::end_tag('div');
                $required .= html_writer::empty_tag('img',
                        array('class' => 'req',
                                'title' => get_string('required', 'questionnaire'),
                                'alt' => get_string('required', 'questionnaire'),
                                'src' => $OUTPUT->pix_url('req')));
            }
            echo $required;
        }
        // If question text is "empty", i.e. 2 non-breaking spaces were inserted, empty it.
        if ($this->content == '<p>  </p>') {
            $this->content = '';
        }
        echo html_writer::end_tag('legend');
        echo html_writer::start_tag('div', array('class' => 'qn-content'));
        echo html_writer::start_tag('div', array('class' => 'qn-question '.$skippedclass));
        if ($this->type_id == QUESNUMERIC || $this->type_id == QUESTEXT ||
            $this->type_id == QUESDROP) {
            echo html_writer::start_tag('label', array('for' => $this->type . $this->id));
        }
        if ($this->type_id == QUESESSAY) {
            echo html_writer::start_tag('label', array('for' => 'edit-q' . $this->id));
        }
        $options = array('noclean' => true, 'para' => false, 'filter' => true, 'context' => $this->context, 'overflowdiv' => true);
        echo format_text(file_rewrite_pluginfile_urls($this->content, 'pluginfile.php',
            $this->context->id, 'mod_questionnaire', 'question', $this->id), FORMAT_HTML, $options);
        if ($this->type_id == QUESNUMERIC || $this->type_id == QUESTEXT ||
            $this->type_id == QUESESSAY || $this->type_id == QUESDROP) {
            echo html_writer::end_tag('label');
        }
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'qn-answer'));
    }

    public function questionend_survey_display() {
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('fieldset');
    }

    private function response_check_required ($data) {
        // JR check all question types
        if ($this->type_id == QUESRATE) { // Rate is a special case.
            foreach ($this->choices as $cid => $choice) {
                $str = 'q'."{$this->id}_$cid";
                if (isset($data->$str)) {
                    return ('&nbsp;');
                }
            }
        }
        if ( ($this->required == 'y') &&  empty($data->{'q'.$this->id}) ) {
            return ('*');
        } else {
            return ('&nbsp;');
        }
    }

    /**
     * Override this to provide specific form data for editing the question type.
     */
    public function edit_form(MoodleQuickForm $qform, $modcontext) {
        return false;
    }
}