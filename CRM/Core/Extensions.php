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

        $config = CRM_Core_Config::singleton( );
        $this->extDir = $config->extensionsDir;
        if( is_null($this->extDir) ) {
            CRM_Core_Error::fatal( "If you want to use extensions, please configure CiviCRM Extensions directory in Administer -> Configure -> Global Settings -> Directories." );
        }
        
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

        // get uploaded extensions
        $extensions['uploaded'] = $this->_discoverUploaded();

        // get installed extensions
        $extensions['local'] = $this->_discoverInstalled();

//        // if uploaded contains locally installed extensions (temp not cleaned up), ignore them
//        foreach( $extensions['uploaded'] as $type => $extList ) {
//            foreach( $extList as $name => $dc ) {
//                if( array_key_exists( $name, $extensions['local'][$type] ) ) {
//                    unset($extensions['uploaded'][$type][$name]);
//                } 
//            }
//        }

        // get enabled extensions from the database
        $extensions['enabled'] = $this->_discoverEnabled();

        // if local contains enabled extensions, make sure we know
        foreach( $extensions['local'] as $type => $extList ) {
            foreach( $extList as $name => $dc ) {
                if( $extensions['enabled'][$type] && array_key_exists( $name, $extensions['enabled'][$type] ) ) {
                    $extensions['local'][$type][$name]['files_exist'] = TRUE;
                    $extensions['local'][$type][$name]['id'] = $extensions['enabled'][$type][$name]['id'];
                } else {
                    $extensions['local'][$type][$name]['files_exist'] = FALSE;
                }
            }
        }

//        CRM_Core_Error::debug( $extensions );

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
                $uploaded[(string) $attr->type][$name] = $t;
                // uploaded extensions don't have db ids, so we're using the key here
                $uploaded[(string) $attr->type][$name]['id'] = $name;
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
        require_once 'CRM/Core/OptionValue.php';
        $groupParams = array( 'name' => self::OPTION_GROUP_NAME );
        $links = array();
        $ov = CRM_Core_OptionValue::getRows( $groupParams, $links );

        $enabled = array();

        $config = CRM_Core_Config::singleton( );
        $d = $config->extensionsDir;
        foreach( $ov as $id => $r ) {
            $name = $r['value'];
            $type = $r['grouping'];
            $dir = $d . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $name;
            $infoFile = $dir . DIRECTORY_SEPARATOR . self::EXT_INFO_FILENAME;
            if( is_dir( $dir ) && file_exists( $infoFile ) && in_array( $type, $this->allowedExtTypes ) ) {
                $enabled[$type][$name] = $this->_buildExtensionRecord( $dir, $id );
                foreach( $r as $key => $val ) {
                    $enabled[$type][$name][$key] = $val;
                }                
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


    private function _buildExtensionRecord( $dir, $id = null ) {
        $infoFile = $dir . DIRECTORY_SEPARATOR . self::EXT_INFO_FILENAME;
        if( file_exists( $infoFile ) && $this->_validateInfoFile()) {
            $rec['path'] =  $dir;
            $rec['valid'] = $this->_validateExtension();
            $rec['info'] = $this->_parseInfoFile( $infoFile );
            $rec['id'] = $id;
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
    
    public function key2path( $key, $type ) {

        $e = $this->extensions;
        $config = CRM_Core_Config::singleton( );

        $callback = (string) $e['enabled'][$type][$key]['info']->callback;
        $path = $config->extensionsDir . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $key . 
                                         DIRECTORY_SEPARATOR . $callback . '.php';
        return $path;

    }

    public function key2class( $key, $type ) {
        $e = $this->extensions;
        $config = CRM_Core_Config::singleton( );
        $callback = (string) $e['enabled'][$type][$key]['info']->callback;
        $clazz = 'Extension_' . ucwords( $type ) . '_' . str_replace( '.', '_', $key );
        return $clazz;
    }

    public function class2path( $clazz ) {
        $elements = explode( '_', $clazz );
        $type = strtolower( $elements[1]);
        $keyElm = array_slice( $elements, 2);
        $key = implode( '.', $keyElm );
        return $this->key2path( $key, $type );
    }

    public function getTemplatePath( $clazz ) {
        $path = $this->class2path( $clazz );
        $pathElm = explode( DIRECTORY_SEPARATOR, $path );
        array_pop( $pathElm );
        return implode( DIRECTORY_SEPARATOR, $pathElm ) . DIRECTORY_SEPARATOR . 'templates';
    }

    public function isExtensionKey( $string ) {
        // check if the string is an extension name or the class
        $dots = strpos($string, '.');
        if( $dots !== FALSE ) {
            return TRUE;
        }
        return FALSE;        
    }
    
    public function isExtensionClass( $string ) {
        if( substr( $string, 0, 10 ) == 'Extension_' ) {
            return TRUE;
        }
        return FALSE;
    }

    public function setIsActive( $id, $is_active ) {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_OptionValue', $id, 'is_active', $is_active );
    }
    
}

