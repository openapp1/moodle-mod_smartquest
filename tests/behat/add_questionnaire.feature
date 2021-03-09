@mod @mod_smartquest
Feature: Add a smartquest activity
  In order to conduct surveys of the users in a course
  As a teacher
  I need to add a smartquest activity to a moodle course

@javascript
  Scenario: Add a smartquest to a course without questions
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
    And the following "activities" exist:
      | activity | name | description | course | idnumber |
      | smartquest | Test smartquest | Test smartquest description | C1 | smartquest0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test smartquest"
    Then I should see "This smartquest does not contain any questions."