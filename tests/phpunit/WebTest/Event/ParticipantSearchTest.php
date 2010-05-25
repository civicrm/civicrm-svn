<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviSeleniumTestCase.php';


 
class WebTest_Contact_ParticipantSearchTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }


  function testParticipantSearchForm( )
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );
      
      // Logging in. Remember to wait for page to load. In most cases,
      // you can rely on 30000 as the value that allows your test to pass, however,
      // sometimes your test might fail because of this. In such cases, it's better to pick one element
      // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
      // page contents loaded and you can continue your test execution.
      $this->webtestLogin( );

      // visit event search page
      $this->open($this->sboxPath . "civicrm/event/search&reset=1");
      $this->waitForPageToLoad("30000");

      $stringsToCheck = 
          array( 'Participant Name',
                 'Event Name',
                 'Event Dates',
                 'Participant Status',
                 'Participant Role',
                 'Find Test Participants',
                 'Find Pay Later Participants',
                 'Fee Level',
                 'Fee Amount' );

      // search for elements
      foreach ( $stringsToCheck as $string ) {
          $this->assertTrue($this->isTextPresent($string), "Could not find $string in search Form!");
      }

  }

  function testParticipantSearchForce( )
  {
      $this->open( $this->sboxPath );
      
      $this->webtestLogin( );

      // visit event search page
      $this->open($this->sboxPath . "civicrm/event/search&reset=1&force=1");
      $this->waitForPageToLoad("30000");

      // assume generated DB
      // there are participants
      $this->assertTrue($this->isTextPresent("Select Records"), "A forced event search did not return any results");

  }

  function testParticipantSearchEmpty( ) {
      $this->open( $this->sboxPath );
      
      $this->webtestLogin( );

      // visit event search page
      $this->open($this->sboxPath . "civicrm/event/search&reset=1");
      $this->waitForPageToLoad("30000");

      $crypticName = "foobardoogoo_" . md5( time( ) );
      $this->type( "sort_name", $crypticName );

      $this->click( "_qf_Search_refresh" );
      $this->waitForPageToLoad("30000");

      $stringsToCheck = 
          array( 'No matches found for',
                 'Name or Email LIKE',
                 $crypticName );

      // search for elements
      foreach ( $stringsToCheck as $string) {
          $this->assertTrue($this->isTextPresent($string), "Could not find '$string' in search results!");
      }
  }

  function testParticipantSearchEventName( ) {
      $this->open( $this->sboxPath );
      
      $this->webtestLogin( );

      // visit event search page
      $this->open($this->sboxPath . "civicrm/event/search&reset=1");
      $this->waitForPageToLoad("30000");

      $eventName = "Fall Fundraiser Dinner";
      $this->type( "event_name", $eventName );
      $this->type( "event_id", 1 );

      $this->click( "_qf_Search_refresh" );
      $this->waitForPageToLoad("30000");

      $stringsToCheck = 
          array( "Event = $eventName",
                 'Select Records:',
                 'Edit Search Criteria' );

      // search for elements
      foreach ( $stringsToCheck as $string) {
          $this->assertTrue($this->isTextPresent($string), "Could not find '$string' in search results!");
      }
  }


}

?>
