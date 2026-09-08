@core @core_admin @core_webservice
Feature: Web service user settings
  In order to configure authorised users for a web service
  As an admin
  I need to use the page that lets you do that

  Background:
    # Include a custom profile field so we can check it gets displayed
    Given the following "custom profile fields" exist:
      | datatype | shortname | name           | param2 |
      | text     | frog      | Favourite frog | 100    |
    And the following config values are set as admin:
      | showuseridentity | email,profile_field_frog |
    And the following "users" exist:
      | username | firstname | lastname | email         | profile_field_frog |
      | user1    | User      | One      | 1@example.org | Kermit             |
    And the following "core_webservice > Service" exists:
      | name            | Silly service |
      | shortname       | silly         |
      | restrictedusers | 1             |
      | enabled         | 1             |

  Scenario: Add a user to a web service
    When I log in as "admin"
    And I navigate to "Server > Web services > External services" in site administration
    And I click on "Authorised users" "link" in the "Silly service" "table_row"
    And I set the field "Not authorised users" to "User One"
    And I press "Add"
    Then I should see "User One" in the ".alloweduserlist" "css_element"
    And I should see "1@example.org" in the ".alloweduserlist" "css_element"
    And I should see "Kermit" in the ".alloweduserlist" "css_element"
    # An IP restriction which could never match any address is rejected.
    And I click on "User One" "link" in the ".alloweduserlist" "css_element"
    And I set the field "IP restriction" to "services.example.com"
    And I press "Update"
    And I should see "These IP restrictions are invalid: services.example.com" in the "IP restriction" "form_row"
    # A valid restriction is accepted.
    And I set the field "IP restriction" to "127.0.0.1,192.168.0.0/16"
    And I press "Update"
    And I should not see "These IP restrictions are invalid"

  @javascript
  Scenario: Add a function to a web service
    When I log in as "admin"
    And I navigate to "Server > Web services > External services" in site administration
    And I click on "Functions" "link" in the "Silly service" "table_row"
    And I follow "Add functions"
    And I set the field "Name" to "core_blog_get_entries"
    And I click on "Add functions" "button" in the "#fgroup_id_buttonar" "css_element"
    Then I should see "core_blog_get_entries" in the "generaltable" "table"
