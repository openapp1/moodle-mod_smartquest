@mod @mod_smartquest
Feature: Smsrtquests can be anonymous
  When anonymous smartquests are viewed
  The user name is dispplayed as "anonymous".

  Background: Add an anonymous smartquest
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
      | smartquest | Anonymous smartquest | Anonymous smartquest description | C1 | smartquest0 |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Anonymous smartquest"
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I should see "Response options"
    And I set the field "id_respondenttype" to "anonymous"
    And I press "Save and display"
    Then I should see "Anonymous smartquest"
    And I navigate to "Questions" in current page administration
    And I add a "Yes/No" question and I fill the form with:
      | Question Name | Q1 |
      | Yes | y |
      | Question Text | Do you like this course |
    And I log out

@javascript
  Scenario: Student completes an anonymous smartquest
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Anonymous smartquest"
    And I navigate to "Answer the questions..." in current page administration
    Then I should see "Anonymous smartquest"
    And I click on "Yes" "radio"
    And I press "Submit smartquest"
    Then I should see "Thank you for completing this Smsrtquest."
    And I follow "Continue"
    Then I should see "Your response"
    And I should see "Anonymous smartquest"
    And I should see "Respondent: - Anonymous -"