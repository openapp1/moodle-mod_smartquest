@mod @mod_smartquest
Feature: In smartquest, dropdown questions can be defined with scores attributed to specific answers, in order
  to provide score dependent feedback.
  In order to define a feedback question
  As a teacher
  I must add a required dropdown question type with choice/value combinations.

  @javascript
  Scenario: Create a smartquest with a dropdown question type and verify that feedback options exist.
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
      | activity | name | description | course | idnumber | resume | navigate |
      | smartquest | Test smartquest | Test smartquest description | C1 | smartquest0 | 1 | 1 |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test smartquest"
    And I navigate to "Advanced settings" in current page administration
    Then I should not see "Feedback options"
    And I follow "Questions"
    Then I should see "Add questions"
    And I add a "Dropdown Box" question and I fill the form with:
      | Question Name | Q3 |
      | Yes | y |
      | Question Text | Select one choice |
      | Possible answers | 1=One,2=Two,3=Three,4=Four |
    Then I should see "[Dropdown Box] (Q3)"
    And I follow "Advanced settings"
    And I should see "Feedback options"
    And I log out