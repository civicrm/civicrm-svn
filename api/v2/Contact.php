<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 * new version of civicrm apis. See blog post at
 * http://civicrm.org/node/131
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'api/v2/utils.php';

/**
 * Add or update a contact. If a dupe is found, check for
 * ignoreDupe flag to ignore or return error
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return array (reference )        contact_id of created or updated contact
 * @static void
 * @access public
 */
function &civicrm_contact_add( &$params ) {
    _civicrm_initialize( );
    //    CRM_Core_Error::debug('p', 'satan');
    $contactID = CRM_Utils_Array::value( 'contact_id', $params );
    
    if ( ! $contactID ) {
        $dupeCheck = CRM_Utils_Array::value( 'dupe_check', $params, false );
        $values    = civicrm_contact_check_params( $params, $dupeCheck );
        if ( $values ) {
            return $values;
        }
        
    }
    
    if( isset( $params['email'] ) ) {
        $location['1']['location_type_id'] = 1;
        $location['1']['is_primary'] = 1;
        $location['1']['email']['1']['email'] = $params['email'];
        $location['1']['email']['1']['is_primary'] = 1;
        if( is_array( $location ) ) {
            $params['location'] =  $location;
            unset($params['email']);
        }
    }
    
    $change = array( 'individual_prefix' => 'prefix',
                     'prefix'            => 'prefix_id',
                     'individual_suffix' => 'suffix',
                     'suffix'            => 'suffix_id',
                     'gender'            => 'gender_id' );
    
    foreach ( $change as $field => $changeAs ) {
        if ( array_key_exists( $field, $params ) ) {
            $params[$changeAs] = $params[$field];
            unset( $params[$field] );
        }
    }
    
    if ( !( is_numeric( $params['suffix_id'] ) ) 
         && isset( $params['suffix_id'] ) ) {
        $params['suffix_id'] = array_search( $params['suffix_id'] , CRM_Core_PseudoConstant::individualSuffix() );
    }
    
    if ( !( is_numeric( $params['prefix_id'] ) ) 
         && isset( $params['prefix_id'] ) ) {
        $params['prefix_id'] = array_search( $params['prefix_id'] , CRM_Core_PseudoConstant::individualPrefix() );
    } 
    
    if ( !  ( is_numeric( $params['gender_id'] ) ) 
         && isset( $params['gender_id'] ) ) {
        $params['gender_id'] = array_search( $params['gender_id'] , CRM_Core_PseudoConstant::gender() );
    }
    
    $contact =& _civicrm_contact_add( $params, $contactID );
    if ( is_a( $contact, 'CRM_Core_Error' ) ) {
        return civicrm_create_error( $contact->_errors[0]['message'] );
    } else {
        $values = array( );
        $values['contact_id'] = $contact->id;
        $values['is_error']   = 0;
    }
    return $values;
}

/**
 * Retrieve a specific contact, given a set of input params
 * If more than one contact exists, return an error, unless
 * the client has requested to return the first found contact
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return array (reference )        array of properties, if error an array with an error id and error message
 * @static void
 * @access public
 */
function &civicrm_contact_get( &$params ) {
    _civicrm_initialize( );

    $values = array( );
    if ( empty( $params ) ) {
        return civicrm_create_error( ts( 'No input parameters present' ) );
    }

    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Input parameters is not an array' ) );
    }

    $contacts =& civicrm_contact_search( $params );
    if ( civicrm_error( $contacts ) ) {
        return $contacts;
    }

    if ( count( $contacts ) != 1 &&
         ! $params['returnFirst'] ) {
        return civicrm_create_error( ts( '%1 contacts matching input params', array( 1 => count( $contacts ) ) ) );
    }

    $contacts = array_values( $contacts );
    return $contacts[0];
}

/**
 * Delete a contact
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 */
function civicrm_contact_delete( &$params ) {
    require_once 'CRM/Contact/BAO/Contact.php';

    $contactID = CRM_Utils_Array::value( 'contact_id', $params );
    if ( ! $contactID ) {
        return civicrm_create_error( ts( 'Could not find contact_id in input parameters' ) );
    }

    if ( CRM_Contact_BAO_Contact::deleteContact( $contactID ) ) {
        return civicrm_create_success( );
    } else {
        return civicrm_create_error( ts( 'Could not delete contact' ) );
    }
}

/**
 * Retrieve a set of contacts, given a set of input params
 *
 * @param  array   $params           (reference ) input parameters
 * @param array    $returnProperties Which properties should be included in the
 *                                   returned Contact object. If NULL, the default
 *                                   set of properties will be included.
 *
 * @return array (reference )        array of contacts, if error an array with an error id and error message
 * @static void
 * @access public
 */
function &civicrm_contact_search( &$params ) {
    _civicrm_initialize( );

    $inputParams      = array( );
    $returnProperties = array( );
    $otherVars = array( 'sort', 'offset', 'rowCount' );
    
    $sort     = null;
    $offset   = 0;
    $rowCount = 25;
    foreach ( $params as $n => $v ) {
        if ( substr( $n, 0, 7 ) == 'return.' ) {
            $returnProperties[ substr( $n, 7 ) ] = $v;
        } elseif ( array_key_exists( $n, $otherVars ) ) {
            $$n = $v;
        } else {
            $inputParams[$n] = $v;
        }
    }

    if ( empty( $returnProperties ) ) {
        $returnProperties = null;
    }

    require_once 'CRM/Contact/BAO/Query.php';
    $newParams =& CRM_Contact_BAO_Query::convertFormValues( $inputParams );
    list( $contacts, $options ) = CRM_Contact_BAO_Query::apiQuery( $newParams,
                                                                   $returnProperties,
                                                                   null,
                                                                   $sort,
                                                                   $offset,
                                                                   $rowCount );
    return $contacts;
}

/**
 * This function ensures that we have the right input parameters
 *
 * We also need to make sure we run all the form rules on the params list
 * to ensure that the params are valid
 *
 * @param array   $params          Associative array of property name/value
 *                                 pairs to insert in new contact.
 * @param boolean $dupeCheck       Should we check for duplicate contacts
 * @param boolean $dupeErrorArray  Should we return values of error
 *                                 object in array foramt
 * @param boolean $requiredCHeck   Should we check if required params
 *                                 are present in params array
 *
 * @return null on success, error message otherwise
 * @access public
 */
function civicrm_contact_check_params( &$params, $dupeCheck = true, $dupeErrorArray = false, $requiredCheck = true ) {
    if ( $requiredCheck ) {
        $required = array(
                          'Individual'   => array(
                                                  array( 'first_name', 'last_name' ),
                                                  'email',
                                                  ),
                          'Household'    => array(
                                                  'household_name',
                                                  ),
                          'Organization' => array(
                                                  'organization_name',
                                                  ),
                          );
        
        // cannot create a contact with empty params
        if ( empty( $params ) ) {
            return civicrm_create_error( 'Input Parameters empty' );
        }
        
        if ( ! array_key_exists( 'contact_type', $params ) ) {
            return civicrm_create_error( 'Contact Type not specified' );
        }
        
        // contact_type has a limited number of valid values
        $fields = CRM_Utils_Array::value( $params['contact_type'], $required );
        if ( $fields == null ) {
            return civicrm_create_error( "Invalid Contact Type: {$params['contact_type']}" );
        }
        
        $valid = false;
        $error = '';
        foreach ( $fields as $field ) {
            if ( is_array( $field ) ) {
                $valid = true;
                foreach ( $field as $element ) {
                    if ( ! CRM_Utils_Array::value( $element, $params ) ) {
                        $valid = false;
                        $error .= $element; 
                        break;
                    }
                }
            } else {
                if ( CRM_Utils_Array::value( $field, $params ) ) {
                    $valid = true;
                }
            }
            if ( $valid ) {
                break;
            }
        }
        
        if ( ! $valid ) {
            return civicrm_create_error( "Required fields not found for {$params['contact_type']} : $error" );
        }
    }
    
    if ( $dupeCheck ) {
        // check for record already existing
        if ( $params['contact_type'] == 'Organization' || $params['contact_type'] == 'Household' ) {
            $ids = array();
            require_once "CRM/Contact/DAO/Contact.php";
            $contact = & new CRM_Contact_DAO_Contact();
            if ( $params['contact_type'] == 'Organization' ) {
                $contact->organization_name = $params['organization_name'];
            } else {
                $contact->household_name = $params['household_name'];
            }
            $contact->find();
            while ($contact->fetch(true)) {
                if ( $contact->id != $options) {
                    $ids[] = $contact->id;
                    $ids = implode( ', ',  $ids );
                }
            }
        } else {
            require_once 'CRM/Core/BAO/UFGroup.php';
            $ids = CRM_Core_BAO_UFGroup::findContact( $params ) ;
        }
        
        if ( $ids != null ) {
            if ( $dupeErrorArray ) {
                $error = CRM_Core_Error::createError( "Found matching contacts: $ids",
                                                      CRM_Core_Error::DUPLICATE_CONTACT, 
                                                      'Fatal', $ids );
                return civicrm_create_error( $error->pop( ) );
            }
            
            return civicrm_create_error( "Found matching contacts: $ids", 8000, 'Fatal',
                                         $ids );
        }
    }
    
    return null;
}

function &civicrm_replace_contact_formatted($contactId, &$params, &$fields) {
    //$contact = civcrm_get_contact(array('contact_id' => $contactId));
    
    $delContact = array( 'contact_id' => $contactId );
    
    civicrm_contact_delete($delContact);
    
    $cid = CRM_Contact_BAO_Contact::createProfileContact( $params, $fields, 
                                                          null, null, null, 
                                                          $params['conatct_type'] );
    return civicrm_create_success( $cid );
}


/** 
 * takes an associative array and creates a contact object and all the associated 
 * derived objects (i.e. individual, location, email, phone etc) 
 * 
 * @param array $params (reference ) an assoc array of name/value pairs 
 * @param  int     $contactID        if present the contact with that ID is updated
 * 
 * @return object CRM_Contact_BAO_Contact object  
 * @access public 
 * @static 
 */ 
function &_civicrm_contact_add( &$params, $contactID = null ) 
{
    require_once 'CRM/Utils/Hook.php';
    //    CRM_Core_Error::debug('p', 'oh');
    if ( $contactID ) {
        CRM_Utils_Hook::pre( 'edit', 'Individual', $contactID, $params );
    } else {
        CRM_Utils_Hook::pre( 'create', 'Individual', null, $params ); 
    }

    require_once 'CRM/Core/Transaction.php';
    $transaction = new CRM_Core_Transaction( );

    if ( $contactID ) {
        $params['contact_id'] = $contactID;
    }
    require_once 'CRM/Contact/BAO/Contact.php';
    $contact = CRM_Contact_BAO_Contact::create( $params );

    $transaction->commit( );

    if ( $contactID ) {
        CRM_Utils_Hook::post( 'edit', 'Individual', $contact->id, $contact );
    } else {
        CRM_Utils_Hook::post( 'create', 'Individual', $contact->id, $contact );
    }

    return $contact;
}

?>
