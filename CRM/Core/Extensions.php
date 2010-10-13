<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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

require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/Extensions/ExtensionType.php';

/**
 * This class stores logic for managing CiviCRM extensions.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */
class CRM_Core_Extensions
{

    /**
     * The option group name
     */
    const OPTION_GROUP_NAME = 'system_extensions';

    /**
     * Extension info file name
     */
    const EXT_INFO_FILENAME = 'info.xml';

    /**
     * Allows quickly verifying if extensions are enabled
     * 
     * @access private
     * @var boolean
     */
    public $enabled = FALSE;

    /**
     * Full path to extensions directory
     * 
     * @access private
     * @var null|string
     */
    private $_extDir = null;

    /**
     * List of active (installed) extensions ordered by id
     * 
     * @access private
     * @var null|array
     */
    private $_extById = null;

    /**
     * List of active (installed) extensions ordered by id
     * 
     * @access private
     * @var null|array
     */
    private $_extByKey = null;


    /**
     * Constructor - we're not initializing information here
     * since we don't want any database hits upon object
     * initialization.
     * 
     * @access public
     * @return null
     */
    public function __construct( ) {
        $config =& CRM_Core_Config::singleton( );
        $this->_extDir = $config->extensionsDir;
        
        if( ! empty( $this->_extDir ) ) {
            $this->enabled = TRUE;
        }        
    }

    /**
     * Populates variables containing information about extension.
	 * This method is not supposed to call on object initialisation.
     * 
     * @access public
     * @return null
     */
    public function populate( $fullInfo = FALSE ) {
        if( is_null($this->_extDir) || empty( $this->_extDir ) ) {
            return;
        }
        
        $installed = $this->getInstalled( $fullInfo );
        $uploaded = $this->getNotInstalled( );
        $this->_extById = array_merge( $installed, $uploaded );
        $this->_extByKey = array();
        foreach( $this->_extById as $id => $ext ) {
            $this->_extByKey[$ext->key] = $ext;
        }
    }

    /**
     * Returns the list of extensions ordered by extension key.
     * 
     * @access public
     * @return array the list of installed extensions
     */
    public function getExtensionsByKey( $fullInfo = FALSE ) {
        $this->populate( $fullInfo );
        return $this->_extByKey;
    }

    /**
     * @todo DEPRECATE
     * 
     * @access public
     * @return array the list of installed extensions
     */
    public function getInstalled( $fullInfo = FALSE ) {
        return $this->_discoverInstalled( $fullInfo );
    }

    /**
    * @todo DEPRECATE
     * 
     * @access public
     * @return
     */
    public function getAvailable( ) {
        return $this->_discoverAvailable();
    }

    /**
     * 
     * 
     * @access public
     * @return
     */
    public function getNotInstalled( ) {
        $installed = $this->_discoverInstalled();
        $result = $this->_discoverAvailable();
        $instKeys = array();
        foreach( $installed as $id => $ext ) {
            $instKeys[] = $ext->key;
        }
        foreach( $result as $id => $ext ) {
            if( array_key_exists( $ext->key, array_flip( $instKeys ) ) ) {
                unset( $result[$id] );
            }
        }
        return $result;                
    }    


    /**
     * 
     * 
     * @access private
     * @return
     */
    private function _discoverInstalled( $fullInfo = FALSE ) {
        require_once 'CRM/Core/OptionValue.php';
        require_once 'CRM/Core/Extensions/Extension.php';
        $result = array();        
        $groupParams = array( 'name' => self::OPTION_GROUP_NAME );
        $links = array();
        $ov = CRM_Core_OptionValue::getRows( $groupParams, $links );
        foreach( $ov as $id => $entry ) {
            $ext = new CRM_Core_Extensions_Extension( $entry['value'], $entry['grouping'], $entry['name'], 
                                                      $entry['label'], $entry['description'], $entry['is_active'] );
            $ext->setId($id);
            if( $fullInfo ) {
                $ext->readXMLInfo();            
            }
            $result[$id] = $ext;
        }
        return $result;
    }

    /**
     * 
     * 
     * @access private
     * @return
     */
    private function _discoverAvailable() {
        require_once 'CRM/Core/Extensions/Extension.php';
        $result = array();
        $e = scandir( $this->_extDir );
        foreach( $e as $dc => $name ) {
            $dir = $this->_extDir . DIRECTORY_SEPARATOR . $name;
            $infoFile = $dir . DIRECTORY_SEPARATOR . self::EXT_INFO_FILENAME;
            if( is_dir( $dir ) && file_exists( $infoFile ) ) {
                $ext = new CRM_Core_Extensions_Extension( $name );
                $ext->readXMLInfo();
                $result[] = $ext;
            }
        }
        return $result;
    }

    /**
     * 
     * 
     * @access public
     * @return
     */
    public function keyToPath( $key ) {
        $this->populate();
        $e = $this->_extByKey;
        
        $file = (string) $e[$key]->file;

        return
            $this->_extDir . 
            DIRECTORY_SEPARATOR .
            $key . 
            DIRECTORY_SEPARATOR . 
            $file . 
            '.php';
    }

    /**
     * 
     * 
     * @access private
     * @return
     */
    public function keyToClass( $key ) {
        return str_replace( '.', '_', $key );
    }

    /**
     * 
     * 
     * @access public
     * @return
     */
    public function classToKey( $clazz ) {
        return str_replace( '_', '.', $clazz );
    }

    /**
     * 
     * 
     * @access public
     * @return
     */
    public function classToPath( $clazz ) {
        $elements = explode( '_', $clazz );
        $key = implode( '.', $elements );
        return $this->keyToPath( $key );
    }

    /**
     * 
     * 
     * @access public
     * @return
     */
    public function getTemplatePath( $clazz ) {
        $path = $this->classToPath( $clazz );
        $pathElm = explode( DIRECTORY_SEPARATOR, $path );
        array_pop( $pathElm );
        return implode( DIRECTORY_SEPARATOR, $pathElm ) . DIRECTORY_SEPARATOR . 'templates';
    }

    /**
     * 
     * 
     * @access public
     * @return
     */    
    public function getTemplateName( $clazz ) {
        $this->populate();
        $e = $this->_extByKey;
        $file = (string) $e[$key]->file;
        $key = $this->classToKey( $clazz );
        return (string) $e[$key]->file . '.tpl' ;
    }    

    /**
     * 
     * 
     * @access public
     * @return
     */
    public function isExtensionKey( $string ) {
        // check if the string is an extension name or the class
        return ( strpos($string, '.') !== FALSE ) ? TRUE : FALSE;
    }

    /**
     * 
     * 
     * @access public
     * @return
     */    
    public function isExtensionClass( $string ) {
        
        if ( substr( $string, 0, 4 ) != 'CRM_' ) {
            require_once 'CRM/Core/PseudoConstant.php';
            $extensions = CRM_Core_PseudoConstant::getExtensions( $string );
            if ( in_array( $string, $extensions ) ) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * 
     * 
     * @access public
     * @return
     */
    public function setIsActive( $id, $is_active ) {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_OptionValue', $id, 'is_active', $is_active );
    }

    /**
     * 
     * 
     * @access public
     * @return
     */
    public function install( $id, $key ) {
        $e = $this->getNotInstalled();
        $ext = $e[$id];
        $ext->install();
    }

    /**
     * 
     * 
     * @access public
     * @return
     */
    public function uninstall( $id, $key ) {
        $this->populate();
        $e = $this->getExtensionsByKey( );
        $ext = $e[$key];
        $ext->uninstall();
    }

}

