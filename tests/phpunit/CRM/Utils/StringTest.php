<?php

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CRM/Utils/String.php';

class CRM_Utils_StringTest extends CiviUnitTestCase 
{
    
    function get_info( ) 
    {
        return array(
                     'name'        => 'String Test',
                     'description' => 'Test String Functions',
                     'group'       => 'CiviCRM BAO Tests',
                     );
    }
    
    function setUp( ) 
    {
        parent::setUp();
    }

    function testStripPathChars( ) {
        $testSet = array( '' => '',
                          null => null,
                          'civicrm' => 'civicrm',
                          'civicrm/dashboard' => 'civicrm/dashboard',
                          'civicrm/contribute/transact' => 'civicrm/contribute/transact',
                          'civicrm/<hack>attempt</hack>' => 'civicrm/_hack_attempt_/hack_',
                          'civicrm dashboard & force = 1,;' => 'civicrm_dashboard___force___1__'
                          );
        


        foreach ( $testSet as $in => $expected ) {
            $out = CRM_Utils_String::stripPathChars( $in );
            $this->assertEquals( $out, $expected, "Output does not match" );
        }
    }

}