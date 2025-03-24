@enrol @enrol_xp @javascript
Feature: Testing that enrolment can happen based on level

  Background:
    Given the following "courses" exist:
      | fullname  | shortname |
      | Course 1  | c1        |
      | Course 2  | c2        |
    And the following "users" exist:
      | username | firstname | lastname |
      | t1       | Teacher   | One      |
      | s1       | Student   | One      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | t1       | c1     | editingteacher |
      | t1       | c2     | editingteacher |
      | s1       | c1     | student        |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | Course       | c1        |
    And the following "block_xp > config" exist:
      | worldcontext | name               | value |
      | c1           | enablelevelupnotif | 0     |
    And the following "activities" exist:
      | activity | course | name                       |
      | page     | c2     | Mystery page for enrollees |

  Scenario: Students are enrolled when they attain a certain level
    Given I am on the "c2" "course" page logged in as "t1"
    And I add "Level Up XP Enrolment" enrolment method in "c2" with:
      | Level to attain           | 5         |
      | Course to attain level in | Course 1  |
      | A welcome message         | You rock! |

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And the following should exist in the "table" table:
      | First name  | Level |
      | Student One | -     |
    And I follow "Edit" for "Student One" in the XP report
    And I set the field "Total" in the "Edit Student One" "dialogue" to "120"
    And I click on "Save changes" "button" in the "Edit Student One" "dialogue"
    And the following should exist in the "table" table:
      | First name  | Level |
      | Student One | 2     |

    And I am on the "c2" "course" page logged in as "s1"
    And I should see "You cannot enrol yourself"

    And I am on the "c1" "block_xp > report" page logged in as "t1"
    And I follow "Edit" for "Student One" in the XP report
    And I set the field "Total" in the "Edit Student One" "dialogue" to "1000"
    And I click on "Save changes" "button" in the "Edit Student One" "dialogue"
    And the following should exist in the "table" table:
      | First name  | Level |
      | Student One | 5     |

    When I am on the "c2" "course" page logged in as "s1"
    Then I should see "Mystery page for enrollees"
    And I open the notification popover
    And I follow "View full notification"
    And I should see "You rock!"
