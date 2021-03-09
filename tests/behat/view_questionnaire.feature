@mod @mod_smartquest
Feature: Smsrtquests can be public, private or template
  In order to view a smartquest
  As a user
  The type of the smartquest affects how it is displayed.

@javascript
  Scenario: Add a template smartquest
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | manager1 | Manager | 1 | manager1@example.com |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | manager1 | C1 | manager |
    And the following "activities" exist:
      | activity | name | description | course | idnumber |
      | smartquest | Test smartquest | Test smartquest description | C1 | smartquest0 |
    And I log in as "manager1"
    And I am on site homepage
    And I am on "Course 1" course homepage
    And I follow "Test smartquest"
    And I navigate to "Advanced settings" in current page administration
    And I should see "Content options"
    And I set the field "id_realm" to "template"
    And I press "Save and display"
    Then I should see "Template smartquests are not viewable"

@javascript
  Scenario: Add a smartquest from a public smartquest
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | manager1 | Manager | 1 | manager1@example.com |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
      | Course 2 | C2 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | manager1 | C1 | manager |
      | manager1 | C2 | manager |
      | student1 | C2 | student |
    And the following "activities" exist:
      | activity | name | description | course | idnumber |
      | smartquest | Test smartquest | Test smartquest description | C1 | smartquest0 |
    And the following config values are set as admin:
      | coursebinenable | 0 | tool_recyclebin |
    And I log in as "manager1"
    And I am on site homepage
    And I am on "Course 1" course homepage
    And I follow "Test smartquest"
    And I follow "Test smartquest"
    And I navigate to "Questions" in current page administration
    And I add a "Check Boxes" question and I fill the form with:
      | Question Name | Q1 |
      | Yes | y |
      | Min. forced responses | 1 |
      | Max. forced responses | 2 |
      | Question Text | Select one or two choices only |
      | Possible answers | One,Two,Three,Four |
# Neither of the following steps work in 3.2, since the admin options are not available on any page but "view".
    And I follow "Advanced settings"
    And I should see "Content options"
    And I set the field "id_realm" to "public"
    And I press "Save and return to course"
# Verify that a public smartquest cannot be used in the same course.
    And I turn editing mode on
    And I add a "Smsrtquest" to section "1"
    And I expand all fieldsets
    Then I should see "(No public smartquests.)"
    And I press "Cancel"
# Verify that a public smartquest can be used in a different course.
    And I am on site homepage
    And I am on "Course 2" course homepage
    And I add a "Smsrtquest" to section "1"
    And I expand all fieldsets
    And I set the field "name" to "Smsrtquest from public"
    And I click on "Test smartquest [Course 1]" "radio"
    And I press "Save and return to course"
    And I log out
    And I log in as "student1"
    And I am on "Course 2" course homepage
    And I follow "Smsrtquest from public"
    Then I should see "Answer the questions..."
# Verify message for public smartquest that has been deleted.
    And I log out
    And I log in as "manager1"
    And I am on site homepage
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I delete "Test smartquest" activity
    And I am on site homepage
    And I am on "Course 2" course homepage
    And I follow "Smsrtquest from public"
    Then I should see "This smartquest used to depend on a Public smartquest which has been deleted."
    And I should see "It can no longer be used and should be deleted."
    And I log out
    And I log in as "student1"
    And I am on "Course 2" course homepage
    And I follow "Smsrtquest from public"
    Then I should see "This smartquest is no longer available. Ask your teacher to delete it."