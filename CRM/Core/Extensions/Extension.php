<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */


class CRM_Core_Extensions_Extension
{

    /**
     * 
     */
    const OPTION_GROUP_NAME = 'system_extensions';

    const STATUS_INSTALLED = 'installed';
    
    const STATUS_LOCAL = 'local';
    
    const STATUS_REMOTE = 'remote';

    public $type = null;
    
    public $path = null;
    
    public $upgradable = false;
    
    public $upgradeVersion = null;    
    
    function __construct( $key, $type = null, $name = null, $label = null, $file = null, $is_active = 1 ) {
        $this->key = $key;
        $this->type = $type;
        $this->name = $name;
        $this->label = $label;
        $this->file = $file;
        $this->is_active = $is_active;
        
        $config = CRM_Core_Config::singleton( );
        $this->path = $config->extensionsDir . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR;
    }

    public function setId( $id ) {
        $this->id = $id;
    }

    public function setUpgradable( ) {
        $this->upgradable = true;
    }
    
    public function setUpgradeVersion( $version ) {
        $this->upgradeVersion = $version;
    }    

    public function setInstalled( ) {
        $this->setStatus( self::STATUS_INSTALLED );
    }
    
    public function setLocal( ) {
        $this->setStatus( self::STATUS_LOCAL );
    }
    
    public function setRemote( ) {
        $this->setStatus( self::STATUS_REMOTE );
    }

    public function setStatus( $status ) {
        $labels = array( self::STATUS_INSTALLED => ts('Installed'),
                         self::STATUS_LOCAL     => ts('Local only'),
                         self::STATUS_REMOTE	=> ts('Available') );
        $this->status = $status;
        $this->statusLabel = $labels[$status];
    }

    public function xmlObjToArray($obj)
    {
        $arr = array();
        if( is_object( $obj ) ) {
            $obj = get_object_vars( $obj );
        }
        if( is_array( $obj ) ) {
            foreach( $obj as $i => $v ) {
                if ( is_object( $v ) || is_array( $v ) ) {
                    $v = $this->xmlObjToArray( $v );
                }
                if ( empty( $v ) ) {
                    $arr[$i] = null;
                } else {
                    $arr[$i] = $v;
                }
            }
        }
        return $arr;
    }

    public function readXMLInfo( $xml = false ) {
        if( $xml === false ) {
            $info = $this->_parseXMLFile( $this->path . 'info.xml' );
        } else {
            $info = $this->_parseXMLString( $xml );
        }
        
        if ( $info == false ) {
            $this->name = 'Invalid extension';
        } else {
        
        $this->type = (string) $info->attributes()->type;
        $this->file = (string) $info->file;
        $this->label = (string) $info->name;

        // Convert first level variables to CRM_Core_Extension properties
        // and deeper into arrays. An exception for URLS section, since
        // we want them in special format.
        foreach( $info as $attr => $val ) {
            if( count($val->children()) == 0 ) {
                $this->$attr = (string) $val;
            } elseif( $attr === 'urls' ) {
                $this->urls = array();
                foreach( $val->url as $url) {
                    $urlAttr = (string) $url->attributes()->desc;
                    $this->urls[$urlAttr] = (string) $url;
                }
                ksort( $this->urls );
            } else {
                $this->$attr = $this->xmlObjToArray( $val );
            }
        }
        }
    }

    private function _parseXMLString( $string ) {
        return simplexml_load_string( $string, 'SimpleXMLElement');
    }

    private function _parseXMLFile( $file ) {
        if( file_exists( $file ) ) {
            return simplexml_load_file( $file,
            'SimpleXMLElement', LIBXML_NOCDATA);
        } else {
            CRM_Core_Error::fatal( 'Extension file ' . $file . ' does not exist.' );
        }
        return array();
    }
    
    public function install( ) {
        if( $this->status != self::STATUS_LOCAL ) {
            $this->download();
            $this->installFiles();
        }
        $this->_registerExtensionByType();
        $this->_createExtensionEntry();
        if ( $this->type == 'payment' )
            $this->_runPaymentHook( 'install' );
    }
    
    public function uninstall( ) {
        if ( $this->type == 'payment' )
            $this->_runPaymentHook( 'uninstall' );
        $this->removeFiles();    
        $this->_removeExtensionByType();
        $this->_removeExtensionEntry();
    }

    public function removeFiles() {
        $config = CRM_Core_Config::singleton( );
        CRM_Utils_File::cleanDir( $config->extensionsDir . DIRECTORY_SEPARATOR . $this->key, true );
    }
    
    public function installFiles() {
        $config = CRM_Core_Config::singleton( );

        $zip = new ZipArchive;
        $res = $zip->open( $this->tmpFile );
        if ($res === TRUE) {
            $path = $config->extensionsDir . DIRECTORY_SEPARATOR . 'tmp';        
            $zip->extractTo( $path );
            $zip->close();
        } else {
            CRM_Core_Error::fatal( 'Unable to extract the extension.' );
        }

        $filename = $path . DIRECTORY_SEPARATOR . $this->key . DIRECTORY_SEPARATOR . 'info.xml';
        $newxml = file_get_contents( $filename );
        
        if (empty($newxml))
            CRM_Core_Error::fatal( ts( 'Failed reading data from %1 during installation', array( 1 => $filename ) ) );
            
        $check = new CRM_Core_Extensions_Extension( $this->key . ".newversion" );
        $check->readXMLInfo( $newxml );
        if( $check->version != $this->version ) {
            CRM_Core_Error::fatal( 'Cannot install - there are differences between extdir XML file and archive XML file!' );
        }
        
        CRM_Utils_File::copyDir( $path . DIRECTORY_SEPARATOR . $this->key,
                                 $config->extensionsDir . DIRECTORY_SEPARATOR . $this->key );
        
        
    }
    
    public function download( ) {
    
        $config = CRM_Core_Config::singleton( );
        
        $path = $config->extensionsDir . DIRECTORY_SEPARATOR . 'tmp';
        $filename = $path . DIRECTORY_SEPARATOR . $this->key . '.zip';

        if( !$this->downloadUrl ) {
            CRM_Core_Error::fatal( 'Cannot install this extension - downloadUrl is not set!' );
        }
        
        // Download extension zip file ...
        
        @ini_set( 'allow_url_fopen', 1 );
        @ini_set( 'user_agent', 'CiviCRM v' . CRM_Utils_System::version( ) );
        
        // Check response code on downloadUrl
        $headers       = get_headers( $this->downloadUrl );
        $response_code = substr( $headers[0], 9, 3 );
     
        if ( $response_code == 200 ) {
        
            $context = stream_context_create(array(
                'http' => array(
                    'timeout' => 20      // Timeout if no reply after 20 seconds
                )
            ));
            
            // Attempt to download file
            if ( !$zipfile = file_get_contents( $this->downloadUrl, false, $context ) ) 
                CRM_Core_Error::fatal( ts( 'Unable to download extension from %1', array(1 => $this->downloadUrl) ) );
            
            // Attempt to save file
            if ( @file_put_contents( $filename, $zipfile ) === false )
                CRM_Core_Error::fatal( ts ( 'Unable to write to %1.<br />Is the location writable?', array(1 => $filename) ) );
            
        } else {
            // Response code != 200?
            // Bail and inform user ...
            $error = 'Unable to download extension from %1.';
            if ( $response_code >= 100 && $response_code < 300 )
                CRM_Core_Error::fatal( ts ( "$error<br />Server returned an HTTP %2 response code.", array(1 => $this->downloadUrl, 2 => $response_code) ) );
            elseif ( $response_code >= 300 && $response_code < 400 )
                CRM_Core_Error::fatal( ts ( "$error<br />URL is redirecting.", array(1 => $this->downloadUrl) ) );
            else
                CRM_Core_Error::fatal( ts( "$error<br />Server returned an HTTP %2 error.", array(1 => $this->downloadUrl, 2 => $response_code) ) );
        }
        
        @ini_restore( 'user_agent' );
        @ini_restore( 'allow_url_fopen' );
        
        $this->tmpFile = $filename;
    }    

    public function enable( ) {
        $this->_setActiveByType( 1 );
        CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_OptionValue', $this->id, 'is_active', 1 );
        if ( $this->type == 'payment' )
            $this->_runPaymentHook( 'enable' );
    }
    
    public function disable( ) {
        if ( $this->type == 'payment' )
            $this->_runPaymentHook( 'disable' );
        $this->_setActiveByType( 0 );
        CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_OptionValue', $this->id, 'is_active', 0 );
    }


    private function _setActiveByType( $state ) {
        $hcName = "CRM_Core_Extensions_" . ucwords($this->type);
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $hcName) . '.php');
        $ext = new $hcName( $this );
        $state ? $ext->enable() : $ext->disable();
    }

    private function _registerExtensionByType() {
        $hcName = "CRM_Core_Extensions_" . ucwords($this->type);
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $hcName) . '.php');
        $ext = new $hcName( $this );
        $ext->install();
    }
    
    private function _removeExtensionByType() {
        $hcName = "CRM_Core_Extensions_" . ucwords($this->type);
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $hcName) . '.php');
        $ext = new $hcName( $this );
        $ext->uninstall();
    }    

    private function _removeExtensionEntry() {
        CRM_Core_BAO_OptionValue::del($this->id);
        CRM_Core_Session::setStatus( ts('Selected option value has been deleted.') );
    }
    
    /**
     * Function to run hooks in the payment processor class
     * Load requested payment processor and call the method specified.
     *
     * @param string $method - the method to call in the payment processor class 
     * @private
     */
    private function _runPaymentHook( $method ) {
        
        // Not concerned about performance at this stage, as these are seldomly performed tasks
        // (payment processor enable/disable/install/uninstall). May wish to implement some
        // kind of registry/caching system if more hooks are added.
        
        
        if ( ! isset( $this->id ) || empty( $this->id ) )
            $this->id = 0;
        
        $ext = new CRM_Core_Extensions( );
        
        $paymentClass = $ext->keyToClass( $this->key, 'payment' );
        require_once $ext->classToPath( $paymentClass );
        
        // See if we have any instances of this PP defined ..
        if ($this->id && $processor_id = CRM_Core_DAO::singleValueQuery("
                SELECT pp.id
                  FROM civicrm_option_group og
            INNER JOIN civicrm_option_value ov
                    ON ov.option_group_id = og.id
            INNER JOIN civicrm_payment_processor pp 
                    ON pp.payment_processor_type = ov.name
                 WHERE og.name = 'system_extensions'
                   AND ov.grouping = 'payment'
                   AND ov.id = %1
              
        ",
            array(
                1 => array( $this->id, 'Integer' )  
            )
        )) {
            // If so, load params in the usual way ..
            $paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment( $processor_id, null );
        
        } else {
            // Otherwise, do the best we can to construct some ..
            $dao = CRM_Core_DAO::executeQuery("
                    SELECT ppt.* FROM civicrm_option_value ov
                INNER JOIN civicrm_payment_processor_type ppt
                        ON ppt.name = ov.name
                     WHERE ov.name = %1
                       AND ov.grouping = 'payment'
            ",
                array(
                    1 => array( $this->name, 'String' )
                )
            );
            if ( $dao->fetch( ) ) 
                $paymentProcessor = array(
                    'id'                     => -1,
                    'name'                   => $dao->title,
                    'payment_processor_type' => $dao->name,
                    'user_name'              => 'nothing',
                    'password'               => 'nothing', 
                    'signature'              => '',           
                    'url_site'               => $dao->url_site_default,
                    'url_api'                => $dao->url_api_default, 
                    'url_recur'              => $dao->url_recur_default, 
                    'url_button'             => $dao->url_button_default, 
                    'subject'                => '', 
                    'class_name'             => $dao->class_name,
                    'is_recur'               => $dao->is_recur,
                    'billing_mode'           => $dao->billing_mode,
                    'payment_type'           => $dao->payment_type
                );
            else
                CRM_Core_Error::fatal( "Unable to find payment processor in " . __CLASS__ . '::' . __METHOD__ );
            
        }
        
        // In the case of uninstall, check for instances of PP first.
        // Don't run hook if any are found.
        if ( $method == 'uninstall' && $paymentProcessor['id'] > 0 )
            return;
            
        switch ($method) {
            
            case 'install':
            case 'uninstall':
            case 'enable':
            case 'disable':
                
                // Instantiate PP
                eval( '$processorInstance = ' . $paymentClass . '::singleton( null, $paymentProcessor );' );
                
                // Does PP implement this method, and can we call it?
                if ( method_exists( $processorInstance, $method ) && is_callable( array( $processorInstance, $method ) ) ) {
                   // If so, call it ...
                   $processorInstance->$method( );
                }
                break;
            default:
                CRM_Core_Session::setStatus( "Unrecognized payment hook ($method) in " . __CLASS__ . '::' . __METHOD__ );
        }
    
    }
    
    private function _createExtensionEntry() {
        $groupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', self::OPTION_GROUP_NAME, 'id', 'name' );
        $weight = CRM_Utils_Weight::getDefaultWeight( 'CRM_Core_DAO_OptionValue', array( 'option_group_id' => $groupId) );
            
        $params = array( 'option_group_id' => $groupId,
                         'weight' => $weight,
                         'label' => $this->label,
                         'name'  => $this->name,
                         'value' => $this->key,
                         'grouping' => $this->type,
                         'description' => $this->file,
                         'is_active' => 1
                      );

        $ids = array();
        $optionValue = CRM_Core_BAO_OptionValue::add($params, $ids);    
    }
    

}