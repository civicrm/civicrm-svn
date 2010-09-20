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

        // get enabled extensions from the database
        $extensions['enabled'] = $this->_discoverEnabled();

        // get installed extensions
        $extensions['local'] = $this->_discoverInstalled();

        // get uploaded extensions
        $extensions['uploaded'] = $this->_discoverUploaded();

        CRM_Core_Error::debug( $extensions );

        return $extensions;
    }


    private function _discoverUploaded() {

        $uploaded = array();

        $config = CRM_Core_Config::singleton( );
        $d = $config->extensionsDir . DIRECTORY_SEPARATOR . 'temp';
        $e = scandir( $d );
        foreach( $e as $dc => $name ) {
            $dir = $d . DIRECTORY_SEPARATOR . $name;
            $infoFile = $dir . DIRECTORY_SEPARATOR . self::EXT_INFO_FILENAME;
            if( is_dir( $dir ) && file_exists( $infoFile ) ) {
                $t = $this->_buildExtensionRecord( $dir );
                $attr = $t['info']->attributes();
                CRM_Core_Error::debug('s', $attr);
                $uploaded[(string) $attr->type][$name] = $t;
            }
//            if( function_exists( 'zip_open' ) {
//                if( is_file( $p ) && zip_open( $p ) ) {
//                    do {
//                        $f = zip_read($p);
//                    } while ($f && zip_entry_name($f) != self::EXT_INFO_FILENAME);
//                    zip_entry_open($p, $f, "r");
//                    $nfoFile = zip_entry_read($f, zip_entry_filesize($f));
//                    $nfo = $this->_parseInfoFile( $nfoFile );
//                    $uploaded[$name] = $nfo;
//                }
//            }
        }

        return $uploaded;
        
    }


    private function _discoverEnabled() {
        require_once 'CRM/Core/OptionGroup.php';
        $ov =  CRM_Core_OptionGroup::values( self::OPTION_GROUP_NAME, false, false, false, null, 'grouping' );
        $enabled = array();

        $config = CRM_Core_Config::singleton( );
        $d = $config->extensionsDir;
        foreach( $ov as $name => $type ) {
            $dir = $d . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $name;
            $infoFile = $dir . DIRECTORY_SEPARATOR . self::EXT_INFO_FILENAME;
            if( is_dir( $dir ) && file_exists( $infoFile ) && in_array( $type, $this->allowedExtTypes ) ) {
                $enabled[$type][$name] = $this->_buildExtensionRecord( $dir );
            }
        }
        return $enabled;
    }

    private function _discoverInstalled() {
        $local = array();

        $config = CRM_Core_Config::singleton( );
        $d = $config->extensionsDir;

        // we expect extension type directories on the top level
        foreach( $this->allowedExtTypes as $dc => $extType ) {
            $extTypePath = $d . DIRECTORY_SEPARATOR . $extType;
            if( file_exists( $extTypePath ) ) {
                $e = scandir( $extTypePath );
                foreach( $e as $dc => $name ) {
                    $dir = $extTypePath . DIRECTORY_SEPARATOR . $name;
                    $infoFile = $dir . DIRECTORY_SEPARATOR . self::EXT_INFO_FILENAME;
                    if( is_dir( $dir ) && file_exists( $infoFile ) ) {
                        $local[$extType][$name] = $this->_buildExtensionRecord( $dir );
                    }
                }
            }
        }
        return $local;
        
    }


    private function _buildExtensionRecord( $dir ) {
        $infoFile = $dir . DIRECTORY_SEPARATOR . self::EXT_INFO_FILENAME;
        if( file_exists( $infoFile ) && $this->_validateInfoFile()) {
            $rec['path'] =  $dir;
            $rec['valid'] = $this->_validateExtension();
            $rec['info'] = $this->_parseInfoFile( $infoFile );
            return $rec;                
        }
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

