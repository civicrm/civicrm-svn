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
 | Version 3, 19 November 2007.                                       |
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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

class bin_migrate_import {

    function __construct( ) {
    }

    function run( $file ) {
        require_once 'CRM/Core/DAO/CustomGroup.php';
        require_once 'CRM/Core/DAO/CustomField.php';
        require_once 'CRM/Core/DAO/OptionValue.php';

        // read xml file
        $dom = DomDocument::load( $file );
        $dom->xinclude( );
        $xml = simplexml_import_dom( $dom );

        $idMap = array( 'custom_group' => array( ),
                        'option_group' => array( ) );

        // first create option groups and values if any
        $this->optionGroups( $xml, $idMap );
        $this->optionValues( $xml, $idMap );

        // now create custom groups
        $this->customGroups( $xml, $idMap );
        $this->customFields( $xml, $idMap );

        // now create profile groups
        $this->profileGroups( $xml, $idMap );
        $this->profileFields( $xml, $idMap );
        $this->profileJoins( $xml, $idMap );
    }

    function copyData( &$dao, &$xml, $save = false, $keyName = null ) {
        if ( $keyName ) {
            if ( isset( $xml->$keyName ) ) {
                $dao->$keyName = (string ) $xml->$keyName;
                if ( $dao->find( true ) ) {
                    echo "Found $keyName, {$dao->$keyName}, {$dao->__table}<p>";
                    return;
                }
            }
        }

        $fields =& $dao->fields( );
        foreach ( $fields as $name => $dontCare ) {
            if ( isset( $xml->$name ) ) {
                $value = (string ) $xml->$name;
                $value = str_replace( ":;:;:;",
                                      CRM_Core_DAO::VALUE_SEPARATOR,
                                      $value );
                $dao->$name = $value;
            }
        }
        if ( $save ) {
            $dao->save( );
        }
    }

    function optionGroups( &$xml, &$idMap ) {
        require_once 'CRM/Core/DAO/OptionGroup.php';
        foreach ( $xml->OptionGroups as $optionGroupsXML ) {
            foreach ( $optionGroupsXML->OptionGroup as $optionGroupXML ) {
                $optionGroup = new CRM_Core_DAO_OptionGroup( );
                $this->copyData( $optionGroup, $optionGroupXML, true, 'name' );
                $idMap['option_group'][$optionGroup->name] = $optionGroup->id;
            }
        }
    }

    function optionValues( &$xml, &$idMap ) {
        require_once 'CRM/Core/DAO/OptionValue.php';
        foreach ( $xml->OptionValues as $optionValuesXML ) {
            foreach ( $optionValuesXML->OptionValue as $optionValueXML ) {
                $optionValue = new CRM_Core_DAO_OptionValue( );
                $optionValue->option_group_id =
                    $idMap['option_group'][(string ) $optionValueXML->option_group_name];
                $this->copyData( $optionValue, $optionValueXML, false, 'label' );
                if ( ! isset( $optionValue->value ) ) {
                    $sql = "
SELECT     MAX(ROUND(v.value)) + 1
FROM       civicrm_option_value v
WHERE      v.option_group_id = %1
";
                    $params = array( 1 => array( $optionValue->option_group_id, 'Integer' ) );
                    $optionValue->value = CRM_Core_DAO::singleValueQuery( $sql, $params );
                }
                $optionValue->save( );
            }
        }
    }

    function customGroups( &$xml, &$idMap ) {
        require_once 'CRM/Core/BAO/CustomGroup.php';
        require_once 'CRM/Utils/String.php';
        foreach ( $xml->CustomGroups as $customGroupsXML ) {
            foreach ( $customGroupsXML->CustomGroup as $customGroupXML ) {
                $customGroup = new CRM_Core_DAO_CustomGroup( );
                $this->copyData( $customGroup, $customGroupXML, true, 'name' );

                $saveAgain = false;
                if ( ! isset( $customGroup->table_name ) ||
                     empty( $customGroup->table_name ) ) {
                    // fix table name
                    $customGroup->table_name = 
                        "civicrm_value_" .
                        strtolower( CRM_Utils_String::munge( $customGroup->title, '_', 32 ) ) .
                        "_{$customGroup->id}";

                    $saveAgain = true;
                }

                // fix extends stuff if it exists
                if ( isset( $customGroupXML->extends_entity_column_value_option_group ) &&
                     isset( $customGroupXML->extends_entity_column_value_option_value ) ) {
                    $sql = "
SELECT     v.value
FROM       civicrm_option_value v
INNER JOIN civicrm_option_group g ON g.id = v.option_group_id
WHERE      g.name = %1
AND        v.name = %2
";
                    $params = array( 1 => array( (string ) $customGroupXML->extends_entity_column_value_option_group, 'String' ),
                                     2 => array( (string ) $customGroupXML->extends_entity_column_value_option_value, 'String' ) );
                    $valueID = (int ) CRM_Core_DAO::singleValueQuery( $sql, $params );
                    if ( $valueID ) {
                        $customGroup->extends_entity_column_id = $customGroup->extends_entity_column_value = $valueID;
                        $saveAgain = true;
                    }
                }

                if ( $saveAgain ) {
                    $customGroup->save( );
                }

                CRM_Core_BAO_CustomGroup::createTable( $customGroup );
                $idMap['custom_group'][$customGroup->name] = $customGroup->id;
            }
        }
    }

    function customFields( &$xml, &$idMap ) {
        require_once 'CRM/Core/BAO/CustomField.php';
        foreach ( $xml->CustomFields as $customFieldsXML ) {
            foreach ( $customFieldsXML->CustomField as $customFieldXML ) {
                $customField = new CRM_Core_DAO_CustomField( );
                $customField->custom_group_id =
                    $idMap['custom_group'][(string ) $customFieldXML->custom_group_name];
                $this->copyData( $customField, $customFieldXML, false, 'name' );
                if ( empty( $customField->option_group_id ) &&
                     isset( $customFieldXML->option_group_name ) ) {
                    $customField->option_group_id =
                        $idMap['option_group'][(string ) $customFieldXML->option_group_name];
                }
                $customField->save( );

                CRM_Core_BAO_CustomField::createField( $customField, 'add' );
            }
        }
    }

    function profileGroups( &$xml, &$idMap ) {
        require_once 'CRM/Core/DAO/UFGroup.php';
        foreach ( $xml->ProfileGroups as $profileGroupsXML ) {
            foreach ( $profileGroupsXML->ProfileGroup as $profileGroupXML ) {
                $profileGroup = new CRM_Core_DAO_UFGroup( );
                $this->copyData( $profileGroup, $profileGroupXML, true, 'name' );
                $idMap['profile_group'][$profileGroup->name] = $profileGroup->id;
            }
        }
    }

    function profileFields( &$xml, &$idMap ) {
        require_once 'CRM/Core/DAO/UFField.php';
        foreach ( $xml->ProfileFields as $profileFieldsXML ) {
            foreach ( $profileFieldsXML->ProfileField as $profileFieldXML ) {
                $profileField = new CRM_Core_DAO_UFField( );
                $profileField->uf_group_id = $idMap['profile_group'][(string ) $profileFieldXML->profile_group_name];
                $this->copyData( $profileField, $profileFieldXML, false, 'name' );
                $profileField->save( );
            }
        }
    }

    function profileJoins( &$xml, &$idMap ) {
        require_once 'CRM/Core/DAO/UFJoin.php';
        foreach ( $xml->ProfileJoins as $profileJoinsXML ) {
            foreach ( $profileJoinsXML->ProfileJoin as $profileJoinXML ) {
                $profileJoin = new CRM_Core_DAO_UFJoin( );
                $profileJoin->uf_group_id = $idMap['profile_group'][(string ) $profileJoinXML->profile_group_name];
                $this->copyData( $profileJoin, $profileJoinXML, false, 'module' );
                $profileJoin->save( );
            }
        }
    }

}
    

function run( ) {
    session_start( );

    if ( ! array_key_exists( 'file', $_GET ) ||
         empty( $_GET['file'] ) ) {
        echo "Please send an input file to import<p>";
        exit( );
    }

    require_once '../../civicrm.config.php';
    require_once 'CRM/Core/Config.php'; 
    $config =& CRM_Core_Config::singleton( );

    // this does not return on failure
    CRM_Utils_System::authenticateScript( true );

    $import = new bin_migrate_import( );

    $import->run( $_GET['file'] );

    // clean up all caches etc
    CRM_Core_Config::clearDBCache( );

    echo "Import Done!";
}

run( );
