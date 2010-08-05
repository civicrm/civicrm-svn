<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * This class stores logic for managing CiviCRM extensions.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

 require_once 'CRM/Core/Config.php';

class CRM_Core_Extensions
{


    /**
     * The option group name
     */
    const OPTION_GROUP_NAME = 'system_extensions';

    const EXT_DIR = 'extensions';

    const EXT_INFO_FILENAME = 'info.xml';
    
    private $allowedExtTypes = array( 'payment', 'search', 'report' );
    
    public $extensions = null;

    function __construct( ) {
        
        if( is_null( $this->extensions )) {
            $this->extensions = $this->discover();
            if( is_null($this->extensions) ) {
                CRM_Core_Error::fatal( 'Cannot retrieve option group for extensions (' . self::OPTION_GROUP_NAME . '). Make sure the upgrade process was correct.' );
            }                        
        }
    }

    public function getExtensions() {
        return $this->extensions;
    }


    /*
     * Function that performs full scan for extensions from all possible
     * sources and returns full listing.
     * 
     * @param array $tables  array of tables
     *
     * @return null
     * @access public
     * @static
     */                   
    public function discover() {
        $extensions = array();
        $local = $this->_discoverLocal();
        $this->_getRegisteredExtensions();
        CRM_Core_Error::debug( $local );
        return $extensions;
    }



    private function _getRegisteredExtensions() {
        $groupParams = array( 'id' => CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', self::OPTION_GROUP_NAME) );

        CRM_Core_Error::debug( 'registered', $groupParams);
        $extensions = array();
        require_once 'CRM/Core/OptionValue.php';
        CRM_Core_OptionValue::getValues( $groupParams, $extensions );

//        CRM_Core_Error::debug( 'registered', $extensions);
        
        $groupParams = array( 'name' => self::OPTION_GROUP_NAME );
        $optionValue = CRM_Core_OptionValue::getRows($groupParams, null, 'component_id,weight');        
    }

    private function _discoverLocal() {
        $local = array();

        // we expect extension type directories on the top level
        foreach( $this->allowedExtTypes as $dc => $extType ) {
            $extTypePath = self::EXT_DIR . DIRECTORY_SEPARATOR . $extType;
            if( file_exists( $extTypePath ) ) {
                $e = scandir( $extTypePath );
                foreach( $e as $dc => $name ) {
                    $extPath = $extTypePath . DIRECTORY_SEPARATOR . $name;
                    $infoFile = $extPath . DIRECTORY_SEPARATOR . self::EXT_INFO_FILENAME;
                    if( is_dir( $extPath ) && file_exists( $infoFile ) && $this->_validateInfoFile()) {
                        $local[$extType][$name]['path'] =  $extPath;
                        $local[$extType][$name]['valid'] = $this->_validateExtension();
                        $local[$extType][$name]['info'] = $this->_parseInfoFile( $infoFile );
                    }
                }
            }
        }
        return $local;
    }


    private function _parseInfoFile( $file ) {
        $dom = DomDocument::load( $file );
        $dom->xinclude( );
        return simplexml_import_dom( $dom );
    }

    private function _validateExtension() {
        return true;
    }

    private function _validateInfoFile() {
        return true;
    }

}


$i = new CRM_Core_Extensions();

$i->discover();

