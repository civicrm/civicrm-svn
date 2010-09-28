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
require_once( 'CRM/Core/Extensions/ExtensionType.php' );

class CRM_Core_Extensions
{


    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     * @var object
     * @static
     */
    private static $_singleton = null;

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
        if( is_null($this->extDir) || empty( $this->extDir ) ) {
            return;
        }
        
        $this->extensions = $this->discover();
    }

    static function &singleton()
    {
        if ( self::$_singleton === null ) {
            self::$_singleton = new CRM_Core_Extensions();
        }

        return self::$_singleton;
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

        foreach( $extensions['uploaded'] as $type => $extList ) {
            foreach( $extList as $name => $nfo ) {
                $extensions['per_id'][$nfo['id']]['label'] = (string) $nfo['info']->name;
                $extensions['per_id'][$nfo['id']]['key'] = $name;
                $extensions['per_id'][$nfo['id']]['type'] = $type;
                $extensions['per_id'][$nfo['id']]['status'] = 'uploaded';
                $extensions['per_id'][$nfo['id']]['path'] = $nfo['path'];
            }
        }


        // get enabled extensions from the database
        $extensions['enabled'] = $this->_discoverEnabled();

        foreach( $extensions['enabled'] as $type => $extList ) {
            foreach( $extList as $name => $nfo ) {
                $extensions['per_id'][$nfo['id']]['label'] = $nfo['label'];
                $extensions['per_id'][$nfo['id']]['key'] = $name;
                $extensions['per_id'][$nfo['id']]['type'] = $type;
                $extensions['per_id'][$nfo['id']]['status'] = 'enabled';
                $extensions['per_id'][$nfo['id']]['path'] = $nfo['path'];
            }
        }

        // if local contains enabled extensions, make sure we know
        foreach( $extensions['local'] as $type => $extList ) {
            foreach( $extList as $name => $nfo ) {
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

        // FIXME: temporary hack for lack of other ideas
        $y = 1;

        // let's number uploaded extensions, it'll be useful later on
        // This is used for 

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
                $uploaded[(string) $attr->type][$name]['id'] = $y++;
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
            } else {
                foreach( $r as $key => $val ) {
                    $enabled[$type][$name][$key] = $val;                
                }
                $enabled[$type][$name]['is_corrupt'] = TRUE;
            }
        }
        
//        CRM_Core_Error::debug( $enabled );
        
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

    public function getExtensionsPerId( $id ) {
        return $this->extensions['per_id'][$id];
    }

    public function setIsActive( $id, $is_active ) {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_OptionValue', $id, 'is_active', $is_active );
    }

    public function getHandlerClass( $type ) {
        $object = null;
        switch( $type ) {
            case 'search':
                require_once 'CRM/Core/Extensions/ExtensionType/Search.php';
                $object = new CRM_Core_Extensions_ExtensionType_Search();
                break;
            case 'payment':
                require_once 'CRM/Core/Extensions/ExtensionType/Payment.php';
                $object = new CRM_Core_Extensions_ExtensionType_Payment();
                break;
            case 'report':
                require_once 'CRM/Core/Extensions/ExtensionType/Report.php';
                $object = new CRM_Core_Extensions_ExtensionType_Report();
                break;
        }
        return $object;
    }

    public function install( $id, $key ) {
        $handler = $this->getHandlerClass( $this->extensions['per_id'][$id]['type'] );
        $handler->install( $id, $key );
    }

    public function delete( $id, $key ) {
        $handler = $this->getHandlerClass( $this->extensions['per_id'][$id]['type'] );
        $handler->deinstall( $id, $key );
//        CRM_Core_Error::debug( $this->extensions );
//        CRM_Core_Error::debug( $this->extensions['per_id'] );
//        CRM_Core_Error::debug($id, $this->extensions['per_id'][$id]['key'] );

//        var_dump( $id );
//        var_dump( $this->extensions['per_id'][$id]['path']);
        
//        CRM_Core_Error::debug( 'u', $this->extensions['per_id'] );

//        if( $this->extensions['per_id'][$id]['key'] !== $key ) {
//            CRM_Core_Error::fatal( ts("Extension key doesn't match extension id - please verify integrity of extensions registry. Skipping uninstall.") );
//        }


//        CRM_Core_Error::debug( $this->extensions['per_id'][$id] );

//        if( $this->extensions['per_id'][$id]['status'] === 'uploaded' ) {
//            CRM_Utils_File::cleanDir( $this->extensions['per_id'][$id]['path'] );
//        } elseif( $this->extensions['per_id'][$id]['status'] === 'enabled' ) {
//            CRM_Utils_File::cleanDir( $this->extensions['per_id'][$id]['path'] );
//            // and delete appropriate records
//        } else {
//            CRM_Core_Error::fatal( ts("Extension status unknown - please verify integrity of extensions registry. Skipping uninstall.") );
//        }


    }


    public function extensionsEnabled() {
        if( is_null($this->extDir) || empty( $this->extDir ) ) {
            return FALSE;
        }
        return TRUE;
    }
    
    
}

