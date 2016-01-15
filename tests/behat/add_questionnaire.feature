@mod @mod_questionnaire
Feature: Add a questionnaire activity
  In order to conduct surveys of the users in a course
  As a teacher
  I need to add a questionnaire activity to a moodle course

  Scenario: Add a forum and a discussion attaching files
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
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Questionnaire" to section "1" and I fill the form with:
      | Name | Test questionnaire |
      | Description | Test questionnaire description |
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I follow "Test questionnaire"
    Then I should see "This questionnaire does not contain any questions."