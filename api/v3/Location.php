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

/**
 * File for the CiviCRM APIv3 location functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Location
 *
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Location.php 30174 2010-10-14 11:57:33Z kurund $
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';

/**
 * Create an additional location for an existing contact
 *
 * @param array $params  input properties
 *  
 * @return array  the created location's params
 *
 * @access public
 */
function civicrm_location_create( &$params ) {
    _civicrm_initialize(true );
    
    $error = _civicrm_location_check_params( $params );
    
    if ( civicrm_error( $error ) ) {
        return $error;
    }
    
    $locationTypeId = CRM_Utils_Array::value( 'location_type_id', $params );
    
    $location =& _civicrm_location_add( $params, $locationTypeId );
    return $location;
}

/**
 *  Update a specified location with the provided property values.
 * 
 *  @param  object  $contact        A valid Contact object (passed by reference).
 *  @param  string  $location_id    Valid (db-level) id for location to be updated. 
 *  @param  Array   $params         Associative array of property name/value pairs to be updated
 *
 *  @return Location object with updated property values
 * 
 *  @access public
 *
 */
function civicrm_location_update( $params ) {
    _civicrm_initialize( );

    if ( !is_array( $params ) ) {
        return civicrm_create_error( 'Params need to be of type array!' );
    }
    
    if( ! isset( $params['contact_id'] ) ) {
        return civicrm_create_error( '$contact is not valid contact datatype' );
    } 
    
    $unsetVersion = false;
    $locationTypes = array( );
    $hasLocBlockId = false;
    $allLocationTypes = CRM_Core_PseudoConstant::locationType( true );
      $locTypeIds = array( );
      foreach ( array( 'email', 'phone', 'im', 'address', 'openid' ) as $name ) {
          if ( isset( $params[$name] ) && is_array( $params[$name]) ) {
              foreach ( $params[$name] as $count => &$values ) {
                  $locName   = CRM_Utils_Array::value( 'location_type',    $values );
                  $LocTypeId = CRM_Utils_Array::value( 'location_type_id', $values );
                  if ( $locName && !in_array( $locName, $locationTypes ) ) $locationTypes[] = $locName;  
                  if ( $LocTypeId ) {
                      $locTypeIds[$LocTypeId] = $LocTypeId;
                  } else if ( in_array( $locName, $allLocationTypes ) ) {
                      $values['location_type_id'] = array_search( $locName, $allLocationTypes );
                  }
                  if ( !$hasLocBlockId && CRM_Utils_Array::value( 'id', $values ) ) $hasLocBlockId = true;
              }
          }
      }
      
      //get all location types.
      foreach ( $locTypeIds as $locId ) {
          $name = CRM_Utils_Array::value( $locId, $allLocationTypes );
          if ( !$name ) return civicrm_create_error( 'Invalid Location Type Id : %1', array( 1 => $locId ) ) ;
          if ( !in_array( $name, $locationTypes ) ) $locationTypes[] = $name;
      }
    
    
    $invalidTypes = array( );
    foreach ( $locationTypes as $name ) {
        if ( !in_array( $name, $allLocationTypes ) ) $invalidTypes[$name] = $name; 
    }
    if ( !empty( $invalidTypes ) ) {
        return civicrm_create_error(  "Invalid Location Type(s) : %1", array( 1 => implode( ', ', $invalidTypes ) )  );
    }
    
    //allow to swap locations.
    if ( $hasLocBlockId ) $locationTypes = $allLocationTypes; 
    
    if ( !empty( $locationTypes ) ) {
        $params['location_type'] = $locationTypes;
    } else {
        return civicrm_create_error( 'missing or invalid location_type_id' );
    }
    
    //get location filter by loc type.
    $locations =& civicrm_location_get( $params );
    
    if ( $unsetVersion ) {
        unset( $params['version'] );
    }
    
    if ( CRM_Utils_System::isNull( $locations ) ) {
        return civicrm_create_error(  "Invalid Location Type(s) : %1", 
                                         array( 1 => implode( ', ',CRM_Utils_Array::value( 'location_type',$params)))); 
    }
    
    $location =& _civicrm_location_update( $params, $locations );
    return $location ;
}


/**
 * Deletes a contact location.
 * 
 * @param object $contact        A valid Contact object (passed by reference).
 * @param string $location_id    A valid location ID.
 *
 * @return  null, if successful. CRM error object, if 'contact' or 'location_id' is invalid, permissions are insufficient, etc.
 *
 * @access public
 *
 */
function civicrm_location_delete( &$contact ) {     
    _civicrm_initialize( );

    if ( !is_array( $contact ) ) {
        return civicrm_create_error( 'Params need to be of type array!' );
    }
    
    if( ! isset( $contact['contact_id'] ) ) {
        return civicrm_create_error( '$contact is not valid contact datatype' );
    } 
    
    require_once 'CRM/Utils/Rule.php';
    $locationTypeID = CRM_Utils_Array::value( 'location_type', $contact );
    if ( ! $locationTypeID ||
         ! CRM_Utils_Rule::integer( $locationTypeID ) ) {
        return civicrm_create_error( 'missing or invalid location' );
    }
    
    $result =& _civicrm_location_delete( $contact );

    return $result;
}

/**
 * Returns array of location(s) for a contact
 * 
 * @param array $contact  a valid array of contact parameters
 *
 * @return array  an array of location parameters arrays
 *
 * @access public
 */
function civicrm_location_get( $contact ) {
    _civicrm_initialize( );

    if ( !is_array( $contact ) ) {
        return civicrm_create_error( 'Params need to be of type array!' );
    }
    
    if( ! isset( $contact['contact_id'] ) ) {
        return civicrm_create_error('$contact is not valid contact datatype');
    }
    
    $locationTypes = CRM_Utils_Array::value( 'location_type', $contact );
    
    if ( is_array($locationTypes) && !count($locationTypes) ) {
        return civicrm_create_error('Location type array can not be empty');
    }
    
    $location=& _civicrm_location_get( $contact, $locationTypes );
    
    return $location;
}

/**
 *
 * @param <type> $params
 * @param <type> $locationTypeId
 * @return <type>
 */
function _civicrm_location_add( &$params, $locationTypeId = null ) {

    
    // Get all existing location blocks.
    $blockParams = array( 'contact_id' => $params['contact_id'],
                          'entity_id'  => $params['contact_id'] );
    
    require_once 'CRM/Core/BAO/Location.php';
    $allBlocks = CRM_Core_BAO_Location::getValues( $blockParams );
    
    // get all blocks in contact array.
    $contact = array_merge( array( 'contact_id' => $params['contact_id'] ), $allBlocks );
    
    // copy params value in contact array.
    $primary = $billing = array( );
    foreach ( array( 'email', 'phone', 'im', 'openid' ) as $name ) {
        if ( CRM_Utils_Array::value( $name, $params ) ) {
            if ( ! isset( $contact[$name]) ||
                 ! is_array( $contact[$name])) {
                $contact[$name] = array( );
            }
            
            $blockCount = count( $contact[$name] );
            if ( is_array( $params[$name] ) ) {
                foreach ( $params[$name] as $val ) {
                    $contact[$name][++$blockCount] = $val;
                    // check for primary and billing.
                    if ( CRM_Utils_Array::value( 'is_primary', $val ) ) {
                        $primary[$name][$blockCount] = true; 
                    }
                    if ( CRM_Utils_Array::value( 'is_billing', $val ) ) {
                        $primary[$name][$blockCount] = true;  
                    }
                }
            }
        }
    }
    
    // get loc type id from params.
    if ( !$locationTypeId ) {
        $locationTypeId = CRM_Utils_Array::value( 'location_type_id', $params['address'][1] );
    }
    
    // address having 1-1 ( loc type - address ) mapping.
    $addressCount = 1;
    if ( array_key_exists( 'address', $contact ) && is_array( $contact['address'] )  ) {
        foreach ( $contact['address'] as $addCount => $values ) {
            if ( $locationTypeId == CRM_Utils_Array::value( 'location_type_id', $values ) ) {
                $addressCount = $addCount;
                break;
            }
            $addressCount++;
        }
    }
    
    if ( CRM_Utils_Array::value( '1', $params['address'] ) && !empty( $params['address'][1] ) ) {
        $contact['address'][$addressCount] = $params['address'][1];
        
        // check for primary and billing address.
        if ( CRM_Utils_Array::value('is_primary', $params['address'][1]) ) $primary['address'][$addressCount] = true;
        if ( CRM_Utils_Array::value('is_billing', $params['address'][1]) ) $billing['address'][$addressCount] = true;
        
        // format state and country.
        foreach ( array( 'state_province', 'country' ) as $field ) {
            $fName = ( $field == 'state_province' ) ? 'stateProvinceAbbreviation' : 'countryIsoCode';
            if ( CRM_Utils_Array::value( $field, $contact['address'][$addressCount] ) &&
                 is_numeric( $contact['address'][$addressCount][$field])) {
                $fValue =& $contact['address'][$addressCount][$field];
                eval( '$fValue = CRM_Core_PseudoConstant::' . $fName . '( $fValue );'  );
                
                //kill the reference.
                unset( $fValue );
            }
        }
    }
    
    //handle primary and billing reset.
    foreach ( array( 'email', 'phone', 'im', 'address', 'openid' ) as $name ) {
        if ( !array_key_exists($name, $contact) || CRM_Utils_System::isNull($contact[$name]) ) continue; 
        
        $errorMsg = null;
        $primaryBlockIndex = $billingBlockIndex = 0;
        if ( array_key_exists( $name, $primary ) ) {
            if ( count( $primary[$name] ) > 1 ) {
                $errorMsg .=  "Multiple primary $name."  ;
            } else {
                $primaryBlockIndex = key( $primary[$name] );
            }
        }
        
        if ( array_key_exists( $name, $billing ) ) {
            if ( count( $billing[$name] ) > 1 ) {
                $errorMsg .=  "Multiple billing $name.";
            } else {
                $billingBlockIndex = key( $billing[$name] ); 
            }
        }
        
        if ( $errorMsg ) {
            return civicrm_create_error( $errorMsg  );  
        }
        
        foreach ( $contact[$name] as $count => &$values ) {
            if ( $primaryBlockIndex && ($count != $primaryBlockIndex) ) $values['is_primary'] = false;
            if ( $billingBlockIndex && ($count != $billingBlockIndex) ) $values['is_billing'] = false;
            
            //kill the reference.
            unset( $values );
        }
    }
    
    // get all ids if not present.
    require_once 'CRM/Contact/BAO/Contact.php';
    CRM_Contact_BAO_Contact::resolveDefaults( $contact, true );
    
    require_once 'CRM/Core/BAO/Location.php';
    $result = CRM_Core_BAO_Location::create( $contact );
    
    if ( empty( $result ) ) {
        return civicrm_create_error( "Location not created"  );
    }
    
    $blocks = array( 'address', 'phone', 'email', 'im', 'openid' );
    foreach( $blocks as $block ) {
        for ( $i = 0; $i < count( $result[$block] ); $i++ ) {
            $locArray[$block][$i] = $result[$block][$i]->id;
        }
    }
    
    
    return civicrm_create_success( $locArray );
}

/**
 *
 * @param <type> $params
 * @param <type> $locationArray
 * @return <type>
 */
function _civicrm_location_update( $params, $locations ) {
    
    
    $contact = array( 'contact_id' => $params['contact_id'] ); 
    $primary = $billing = array( );
    
    // copy params value in contact array.
    foreach ( array( 'email', 'phone', 'im', 'openid' ) as $name ) {
        if ( CRM_Utils_Array::value( $name, $params ) && is_array( $params[$name] ) ) {
            $blockCount = 0;
            $contact[$name] = array( );
            foreach ( $params[$name] as $val ) {
                $contact[$name][++$blockCount] = $val;
                // check for primary and billing.
                if ( CRM_Utils_Array::value( 'is_primary', $val ) ) {
                    $primary[$name][$blockCount] = true; 
                }
                if ( CRM_Utils_Array::value( 'is_billing', $val ) ) {
                    $primary[$name][$blockCount] = true;  
                }
            }
        } else {
            // get values from db blocks so we dont lose them.
            if ( !CRM_Utils_Array::value( $name,  $locations ) || !is_array( $locations[$name]) ) continue;
            $contact[$name] = $locations[$name]; 
        }
    }
    
    $addressCount = 1;
    if ( CRM_Utils_Array::value( 1, $params['address'] ) && !empty( $params['address'][1] ) ) {
        $contact['address'][$addressCount] = $params['address'][1];
        
        // check for primary and billing address.
        if ( CRM_Utils_Array::value('is_primary', $params['address'][1]) ) $primary['address'][$addressCount] = true;
        if ( CRM_Utils_Array::value('is_billing', $params['address'][1]) ) $billing['address'][$addressCount] = true;
        
        // format state and country.
        foreach ( array( 'state_province', 'country' ) as $field ) {
            $fName = ( $field == 'state_province' ) ? 'stateProvinceAbbreviation' : 'countryIsoCode';
            if ( CRM_Utils_Array::value( $field, $contact['address'][$addressCount] ) &&
                 is_numeric( $contact['address'][$addressCount][$field])) {
                $fValue =& $contact['address'][$addressCount][$field];
                eval( '$fValue = CRM_Core_PseudoConstant::' . $fName . '( $fValue );'  );
                
                //kill the reference.
                unset( $fValue );
            }
        }
    }
    
    //handle primary and billing reset.
    foreach ( array( 'email', 'phone', 'im', 'address', 'openid' ) as $name ) {
        if ( !array_key_exists($name, $contact) || CRM_Utils_System::isNull($contact[$name]) ) continue; 
        $errorMsg = null;
        $primaryBlockIndex = $billingBlockIndex = 0;
        if ( array_key_exists( $name, $primary ) ) {
            if ( count( $primary[$name] ) > 1 ) {
                $errorMsg .=  "<br />Multiple Primary $name.";
            } else {
                $primaryBlockIndex = key( $primary[$name] );
            }
        }
        
        if ( array_key_exists( $name, $billing ) ) {
            if ( count( $billing[$name] ) > 1 ) {
                $errorMsg .=  "<br />Multiple Billing $name.";
            } else {
                $billingBlockIndex = key( $billing[$name] ); 
            }
        }
        
        if ( $errorMsg ) {
            return civicrm_create_error( $errorMsg  );  
        }
        
        foreach ( $contact[$name] as $count => &$values ) {
            if ( $primaryBlockIndex && ($count != $primaryBlockIndex) ) $values['is_primary'] = false;
            if ( $billingBlockIndex && ($count != $billingBlockIndex) ) $values['is_billing'] = false;
            // kill the reference.
            unset( $values );
        }
    }
    
    // get all ids if not present.
    require_once 'CRM/Contact/BAO/Contact.php';
    CRM_Contact_BAO_Contact::resolveDefaults( $contact, true );
    
    $location = CRM_Core_BAO_Location::create( $contact );
        
    if ( empty( $location ) ) {
        return civicrm_create_error( "Location not created"  );
    }
    
    $locArray = array( );
    
    $blocks = array( 'address', 'phone', 'email', 'im', 'openid' );
    $locationTypeId = null;
    foreach( $blocks as $block ) {
        for ( $i = 0; $i < count( $location[$block] ); $i++ ) {
            $locArray[$block][$i] = $location[$block][$i]->id;
            $locationTypeId = $location[$block][$i]->location_type_id;
        }
    }
    
    
    return civicrm_create_success( $locArray );
}

/**
 *
 * @param <type> $contact
 * @return <type>
 */
function _civicrm_location_delete( &$contact ) {
    require_once 'CRM/Core/DAO/LocationType.php';
    $locationTypeDAO     = new CRM_Core_DAO_LocationType( );
    $locationTypeDAO->id = $contact['location_type'];
        
    if ( ! $locationTypeDAO->find( ) ) {
        return civicrm_create_error( 'invalid location type' );
    }

    require_once 'CRM/Core/BAO/Location.php';
    CRM_Core_BAO_Location::deleteLocationBlocks( $contact['contact_id'], $contact['location_type'] );
    
    return null;
}

/**
 *
 * @param <type> $contact
 * @param <type> $locationTypes = array( 'Home', 'Work' ) else empty.
 * @return <type>
 */
function &_civicrm_location_get( $contact, $locationTypes = array( ) ) {
    $params = array( 'contact_id' => $contact['contact_id'],
                     'entity_id'  => $contact['contact_id'] ); 
    
    require_once 'CRM/Core/BAO/Location.php';    
    $locations = CRM_Core_BAO_Location::getValues( $params );
    
    $locValues = array( );
    
    // filter the blocks return only those from given loc type.
    if ( is_array( $locationTypes ) && !empty( $locationTypes ) ) {
        foreach ( $locationTypes  as $locName ) { 
            if ( !$locName ) continue;
            if ( $locTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_LocationType', $locName, 'id', 'name' ) ) {
                foreach ( array( 'email' , 'im', 'phone', 'address', 'openid'  ) as $name ) {
                    if ( !array_key_exists( $name, $locations ) || !is_array( $locations[$name] ) ) continue;
                    $blkCount = 0;
                    if ( array_key_exists( $name, $locValues ) ) $blkCount = count($locValues[$name]);
                    foreach ( $locations[$name] as $count => $values ) {
                        if ( $locTypeId == $values['location_type_id'] ) $locValues[$name][++$blkCount] = $values;
                    }
                }
            }
        }
    } else {
        $locValues = $locations;
    }
    
    
    
    return $locValues;
}

/**
 * This function ensures that we have the right input location parameters
 *
 * We also need to make sure we run all the form rules on the params list
 * to ensure that the params are valid
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new location.
 *
 * @return bool|CRM_Utils_Error
 * @access public
 */
function _civicrm_location_check_params( &$params ) {
    if ( !is_array( $params ) ) {
        return civicrm_create_error( 'Params need to be of type array!' );
    }

    // cannot create a location with empty params
    if ( empty( $params ) ) {
        return civicrm_create_error( 'Input Parameters empty' );
    }
    
    $errorField = null;
    if ( !CRM_Utils_Array::value( 'contact_id', $params ) ) {
        $errorField = 'contact_id';  
    }
    
    
    if ( $errorField ) {
        return civicrm_create_error( "Required fields not found for location $errorField" ); 
    }
    
    return array();
}