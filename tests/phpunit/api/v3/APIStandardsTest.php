<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/**
 *  File to v3 APIs for Standards compliance
 *
 *  (PHP 5)
 *
 *   @copyright Copyright CiviCRM LLC (C) 2009
 *   @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 *   @version   $Id:
 *   @package   CiviCRM
 *
 *   This file is part of CiviCRM
 *
 *   CiviCRM is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Affero General Public License
 *   as published by the Free Software Foundation; either version 3 of
 *   the License, or (at your option) any later version.
 *
 *   CiviCRM is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You should have received a copy of the GNU Affero General Public
 *   License along with this program.  If not, see
 *   <http://www.gnu.org/licenses/>.
 */

/**
 *  Include class definitions
 */
require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'api/api.php';

/**
 *  Test APIv3 civicrm_activity_* functions
 *
 *  @package   CiviCRM
 *  @todo determine where help functions should sit (here or 'up the tree'), & best way to define API dir
 */
class api_v3_APIStandardsTest extends CiviUnitTestCase
{

  protected $_apiversion;
  protected $_apiDir;
  protected $_functionFiles;
  protected $_regexForGettingAPIStdFunctions;
  
  /**
   *  Constructor
   *
   *  Initialize configuration
   */
  function __construct( ) {
    parent::__construct( );
  }

  /**
   *  Test setup for every test
   *
   *  Connect to the database, truncate the tables that will be used
   *  and redirect stdin to a temporary file
   */
  public function setUp()
  {
    //  Connect to the database
    parent::setUp();
    $this->_apiversion = 3;
    $this->_apiDir = "../api/v3/";
    $this->_functionFiles = array('Entity.php', 'utils.php');
    //should possibly insert variable rather than '3' in below
    $this->_regexForGettingAPIStdFunctions = '/^civicrm_api3.*_*$/';
   }
  
     /*
     * test checks that all v3 API return a standardised error message when 
     * the $params passed in is not an array.
     */
   
    function testParamsNotArray() {
        $files = $this->getAllFilesinAPIDir();
        $this->assertGreaterThan(1, count($files),"something has gone wrong listing the files in line " . __LINE__);
        $this->requireOnceFilesArray($files);
        $apiStdFunctions = $this->getAllAPIStdFunctions();
        $this->assertGreaterThan(1, count($apiStdFunctions),"something has gone wrong getting the std functions in line " . __LINE__);
        $params = 'string';
        foreach($apiStdFunctions as $key => $function){
          $result = $function($params);
          $this->assertEquals(1, $result['is_error'], $function . "does not return error in line " . __LINE__); 
          $this->assertEquals('Input variable `params` is not an array', $result['error_message'],"$function does not return correct error when a non-array is submitted in line " . __LINE__ );      
        }
    }
    
    /*
     * Get all the files in the API directory for the relevant version which contain API functions
     * @return array $files array of php files in the directory excluding helper files
     */
    function getAllFilesinAPIDir(){
      $files =array();
      $handle=opendir($this->_apiDir);

      while (($file = readdir($handle))!==false) {
         if (strstr($file,".php") && $file != 'Entity.php' && $file !='utils.php'){
           $files[]=$file;
         } 
       }
   
      closedir($handle);
      return $files;
    }
    
    /*
     * Require once  Files
     * @files array list of files to load
     */
    
    function requireOnceFilesArray($files){
        foreach ($files as $key=>$file){
          require_once $this->_apiDir.$file;  
        }
    }
    
    /*
     * Get all api exposed functions that are expected to conform to standards
     * @return array $functionlist
     */
    function getAllAPIStdFunctions(){
      $functionlist = get_defined_functions();
      $apiStFunctions = preg_grep($this->_regexForGettingAPIStdFunctions, $functionlist['user']);
      return $apiStFunctions; 
    }
    
}