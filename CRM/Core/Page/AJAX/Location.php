<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/BAO/UFGroup.php';
require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Profile/Form.php';

/**
 * This class contains all the function that are called using AJAX
 */
class CRM_Core_Page_AJAX_Location
{

    /**
     * FIXME: we should make this method like getLocBlock() OR use the same method and 
     * remove this one. 
     *
     * Function to obtain the location of given contact-id. 
     * This method is used by on-behalf-of form to dynamically generate poulate the 
     * location field values for selected permissioned contact. 
     */
    function getPermissionedLocation( ) 
    {
        $cid           = CRM_Utils_Type::escape( $_GET['cid'], 'Integer' );
        $ufId          = CRM_Utils_Type::escape( $_GET['ufId'], 'Integer' );
        $profileFields = CRM_Core_BAO_UFGroup::getFields( $ufId, false, CRM_Core_Action::VIEW, null, null, false,
                                                          null, false, null, CRM_Core_Permission::CREATE, null );

        require_once 'CRM/Core/BAO/Location.php';
        $entityBlock = array( 'contact_id' => $cid );
        $location    =& CRM_Core_BAO_Location::getValues( $entityBlock );

        $config = CRM_Core_Config::singleton();
        $addressSequence = array_flip($config->addressSequence());

        foreach ( $location as $fld => $values ) {
            $locType = $values[1]['location_type_id'];
            if ( $fld == 'email' ) {
                $elements["onbehalf_{$fld}-{$locType}"] = array( 'type'  => 'Text',
                                                                 'value' => $location[$fld][1][$fld] );
                unset( $profileFields["{$fld}-{$locType}"] );
            } else if ( $fld == 'phone' ) {
                $phoneTypeId = $values[1]['phone_type_id'];
                $elements["onbehalf_{$fld}-{$locType}-{$phoneTypeId}"] = array( 'type'  => 'Text',
                                                                                'value' => $location[$fld][1][$fld] );
                unset( $profileFields["{$fld}-{$locType}"] );
            }
        }
        
        $locTypeId = $location['address'][1]['location_type_id'];
        $addressFields = array( 'street_address', 
                                'supplemental_address_1',
                                'supplemental_address_2',
                                'city',
                                'postal_code',  
                                'country',
                                'state_province' );

        foreach ( $addressFields as $field ) {
            if ( array_key_exists( $field, $addressSequence ) ) {
                $addField = $field;
                if ( in_array( $field, array( 'state_province', 'country' ) ) ) {
                    $addField = "{$field}_id";
                }
                $elements["onbehalf_{$field}-{$locTypeId}"] = array( 'type'  => 'Text',
                                                                     'value' => $location['address'][1][$addField] );
                unset( $profileFields["{$field}-{$locTypeId}"] );
            }
        }
        
        //set custom field defaults
        $defaults = array( );
        CRM_Core_BAO_UFGroup::setProfileDefaults( $cid, $profileFields, $defaults );
        
        if ( !empty( $defaults ) ) {
            foreach ( $profileFields as $key => $val ) {
                
                if ( array_key_exists( $key, $defaults ) ) {
                    if ( $val['html_type'] == 'Radio' ) {
                        $elements["onbehalf[{$key}]"]['type'] = $val['html_type'];
                        $elements["onbehalf[{$key}]"]['value'] = $defaults[$key];
                    } else if ( $val['html_type'] == 'CheckBox' ) {
                        foreach ( $defaults[$key] as $k => $v ) {
                            $elements["onbehalf[{$key}][{$k}]"]['type'] = $val['html_type'];
                            $elements["onbehalf[{$key}][{$k}]"]['value'] = $v;
                        }
                    } else {
                        $elements["onbehalf_{$key}"]['type'] = $val['html_type'];
                        $elements["onbehalf_{$key}"]['value'] = $defaults[$key];
                    }
                } else {
                    $elements["onbehalf_{$key}"]['value'] = '';
                }
            }
        }
        
        echo json_encode( $elements );
        CRM_Utils_System::civiExit( );
    }

    function jqState( $config ) {
        if ( ! isset( $_GET['_value'] ) ||
        empty( $_GET['_value'] ) ) {
            CRM_Utils_System::civiExit( );
        }

        require_once 'CRM/Core/PseudoConstant.php';
        $result =& CRM_Core_PseudoConstant::stateProvinceForCountry( $_GET['_value'] );

        $elements = array( array( 'name'  => ts('- select a state-'),
            'value' => '' ) );
        foreach ( $result as $id => $name ) {
            $elements[] = array( 'name'  => $name,
                'value' => $id );
        }

        require_once "CRM/Utils/JSON.php";
        echo json_encode( $elements );
        CRM_Utils_System::civiExit( );
    }
    

    function jqCounty( $config ) {
        if ( ! isset( $_GET['_value'] ) ||
        empty( $_GET['_value'] ) ) {
            $elements = array( array( 'name'  => ts('- select state -'),
                'value' => '' ) );
        } else {

            require_once 'CRM/Core/PseudoConstant.php';
            $result =& CRM_Core_PseudoConstant::countyForState( $_GET['_value'] );

            $elements = array( array( 'name'  => ts('- select -'),
                'value' => '' ) );
            foreach ( $result as $id => $name ) {
                $elements[] = array( 'name'  => $name,
                    'value' => $id );
            }
            
            if ( $elements == array( array( 'name'  => ts('- select -'), 'value' => '' ) )) {
                $elements = array( array( 'name'  => ts('- no counties -'),
                    'value' => '' ) );
            }

        }
        
        require_once "CRM/Utils/JSON.php";
        echo json_encode( $elements );
        CRM_Utils_System::civiExit( ); 
    }

    function getLocBlock( ) {
        // i wish i could retrieve loc block info based on loc_block_id, 
        // Anyway, lets retrieve an event which has loc_block_id set to 'lbid'.  
        if ( $_POST['lbid'] ) {
            $params  = array('1' => array($_POST['lbid'], 'Integer')); 
            $eventId = CRM_Core_DAO::singleValueQuery('SELECT id FROM civicrm_event WHERE loc_block_id=%1 LIMIT 1', $params);
        }
        // now lets use the event-id obtained above, to retrieve loc block information.  
        if ( $eventId ) {
            $params = array( 'entity_id' => $eventId ,'entity_table' => 'civicrm_event');
            require_once 'CRM/Core/BAO/Location.php';
            // second parameter is of no use, but since required, lets use the same variable.
            $location = CRM_Core_BAO_Location::getValues($params, $params);
        }

        $result = array( );
		require_once 'CRM/Core/BAO/Preferences.php';
        $addressOptions  = CRM_Core_BAO_Preferences::valueOptions( 'address_options', true, null, true );
        // lets output only required fields.
        foreach ( $addressOptions as $element => $isSet ) {
            if ( $isSet && (! in_array($element, array('im', 'openid'))) ) {
                if ( in_array($element, array('country', 'state_province', 'county')) ) {
                    $element .= '_id' ;
                } else if ( $element == 'address_name' ) {
                    $element = 'name' ;
                }
                $fld = "address[1][{$element}]";
                $value = CRM_Utils_Array::value( $element, $location['address'][1] );
                $value = $value ? $value : "";
                $result[str_replace( array('][', '[', "]"), array('_', '_', ''), $fld)] = $value;
            }
        }

        foreach (array('email', 'phone_type_id', 'phone') as $element) {
            $block = ($element == 'phone_type_id') ? 'phone' : $element;
            for ( $i = 1; $i < 3; $i++ ) {
                $fld   = "{$block}[{$i}][{$element}]";
                $value = CRM_Utils_Array::value( $element, $location[$block][$i] );
                $value = $value ? $value : "";
                $result[str_replace( array('][', '[', "]"), array('_', '_', ''), $fld)] = $value;
            }
        }

        // set the message if loc block is being used by more than one event.
        require_once 'CRM/Event/BAO/Event.php';
        $result['count_loc_used'] = CRM_Event_BAO_Event::countEventsUsingLocBlockId( $_POST['lbid'] );

        echo json_encode( $result );
        CRM_Utils_System::civiExit( ); 
    }
}
