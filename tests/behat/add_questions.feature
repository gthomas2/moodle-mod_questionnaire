@mod @mod_questionnaire
Feature: Add questions to a questionnaire activity
  In order to conduct surveys of the users in a course
  As a teacher
  I need to add a questionnaire activity with questions to a moodle course

@javascript
  Scenario: Add a questionnaire to a course
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log in as "teacher1"
    And I add a questionnaire "Test questionnaire" to the course "Course 1" and start to enter questions
    And I add a "Yes/No" question and I fill the form with:
      | Question Name | Q1 |
      | Yes | y |
      | Question Text | Choose yes or no |
    And I add a "Check Boxes" question and I fill the form with:
      | Question Name | Q2 |
      | Yes | y |
      | Min. forced responses | 1 |
      | Max. forced responses | 2 |
      | Question Text | Select one or two choices only |
      | Possible answers | One,Two,Three,Four |
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Test questionnaire"
    And I follow "Answer the questions..."
    Then I should see "Choose yes or no"
    And I press "Submit questionnaire"
    Then I should see "Please answer Required questions: #1. #2."
    And I set the field "Yes" to "y"
    And I set the field "One" to "checked"
    And I set the field "Two" to "checked"
    And I set the field "Three" to "checked"
    And I press "Submit questionnaire"
#    Then I should see "There is something wrong with your answer to question: #2."
    Then I should see "For this question you must tick a maximum of 2 box(es)."