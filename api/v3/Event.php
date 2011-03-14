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
 * File for the CiviCRM APIv3 event functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Event
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Event.php 30964 2010-11-29 09:41:54Z shot $
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';

/**
 * Create a Event
 *
 * This API is used for creating a Event
 *
 * @param  array   $params           (reference ) input parameters
 * Allowed @params array keys are:
 * {@schema Event/Event.xml}
 *
 * @return array of newly created event property values.
 * @access public
 * @todo v2 Event API didn't create custom fields - I can see this has been 'touched up' but should check event custom fields can now be created & then remove this comment
 */
function civicrm_api3_event_create( $params )
{
  _civicrm_api3_initialize( true );
  try {
    civicrm_api3_api_check_permission(__FUNCTION__, $params, true);
    civicrm_api3_verify_mandatory ($params,'CRM_Event_DAO_Event',array ('start_date','event_type_id','title'));

    $ids['eventTypeId'] = (int) $params['event_type_id'];
    $ids['startDate'  ] = $params['start_date'];
    $ids['event_id']    = CRM_Utils_Array::value( 'event_id', $params );

    //format custom fields so they can be added
    $value = array();
    _civicrm_api3_custom_format_params( $params, $values, 'Event' );
    $params = array_merge($values,$params);
    require_once 'CRM/Event/BAO/Event.php';
    $eventBAO = CRM_Event_BAO_Event::create($params, $ids);

    if ( is_a( $eventBAO, 'CRM_Core_Error' ) ) {
      return civicrm_api3_create_error( "Event is not created" );
    } else {
      $event = array();
      _civicrm_api3_object_to_array($eventBAO, $event[$eventBAO->id]);
    }

    return civicrm_api3_create_success($event,$params);
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}


/**
 * Get Event record.
 *
 *
 * @param  array  $params     an associative array of name/value property values of civicrm_event
 *
 * @return  Array of all found event property values.
 * @access public
 */

function civicrm_api3_event_get( $params )
{
  _civicrm_api3_initialize( true );
  try {
    civicrm_api3_verify_mandatory($params);

    $inputParams            = array( );
    $returnProperties       = array( );
    $returnCustomProperties = array( );
    $otherVars              = array( 'sort', 'offset', 'rowCount', 'isCurrent' );

    $sort     =  array_key_exists( 'return.sort', $params ) ? $params['return.sort'] : false;
    // don't check if empty, more meaningful error for API user instead of silent defaults
    $offset   = array_key_exists( 'return.offset', $params ) ? $params['return.offset'] : 0;
    $rowCount = array_key_exists( 'return.max_results', $params ) ? $params['return.max_results'] : 25;
    $isCurrent = array_key_exists( 'isCurrent', $params ) ? $params['isCurrent'] : 0;
    

    foreach ( $params as $n => $v ) {
      if ( substr( $n, 0, 7 ) == 'return.' ) {
        if ( substr( $n, 0, 14 ) == 'return.custom_') {
          //take custom return properties separate
          $returnCustomProperties[] = substr( $n, 7 );
        } elseif( !in_array( substr( $n, 7 ) ,array( 'sort', 'offset', 'max_results', 'isCurrent' ) ) ) {
          $returnProperties[] = substr( $n, 7 );
        }
      } elseif ( in_array( $n, $otherVars ) ) {
        $$n = $v;
      } else {
        $inputParams[$n] = $v;
      }
    }

    if ( !empty($returnProperties ) ) {
      $returnProperties[]='id';
      $returnProperties[]='event_type_id';
    }

    require_once 'CRM/Core/BAO/CustomGroup.php';
    require_once 'CRM/Event/BAO/Event.php';
    $eventDAO = new CRM_Event_BAO_Event( );
    $eventDAO->copyValues( $inputParams );
    $event = array();
    if ( !empty( $returnProperties ) ) {
      $eventDAO->selectAdd( );
      $eventDAO->selectAdd( implode( ',' , $returnProperties ) );
    }
    $eventDAO->whereAdd( '( is_template IS NULL ) OR ( is_template = 0 )' );
    
    if ( $isCurrent ) {
        $eventDAO->whereAdd( '(start_date >= CURDATE() || end_date >= CURDATE())' );
    }
    $eventDAO->orderBy( $sort );
    $eventDAO->limit( (int)$offset, (int)$rowCount );
    $eventDAO->find( );
    while ( $eventDAO->fetch( ) ) {
      $event[$eventDAO->id] = array( );
      CRM_Core_DAO::storeValues( $eventDAO, $event[$eventDAO->id] );
      $groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Event', CRM_Core_DAO::$_nullObject, $eventDAO->id, false, $eventDAO->event_type_id );
      $groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, CRM_Core_DAO::$_nullObject );
      $defaults  = array( );
      CRM_Core_BAO_CustomGroup::setDefaults( $groupTree, $defaults );

      if ( !empty( $defaults ) ) {
        foreach ( $defaults as $key => $val ) {
          if (! empty($returnCustomProperties ) ) {
            $customKey  = explode('_', $key );
            //show only return properties
            if ( in_array( 'custom_'.$customKey['1'], $returnCustomProperties ) ) {
              $event[$eventDAO->id][$key] = $val;
            }
          } else {
            $event[$eventDAO->id][$key] = $val;
          }
        }
      }
    }//end of the loop

    return civicrm_api3_create_success($event,$params,$eventDAO);

  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}

/**
 * Deletes an existing event
 *
 * This API is used for deleting a event
 *
 * @param  Array  $params    array containing event_id to be deleted
 *
 * @return boolean        true if success, error otherwise
 * @access public
 */
function civicrm_api3_event_delete( $params )
{
  _civicrm_api3_initialize( true );
  try {
    civicrm_api3_verify_mandatory($params);

    $eventID = null;

    $eventID = CRM_Utils_Array::value( 'event_id', $params );

    if ( ! isset( $eventID ) ) {
      return civicrm_api3_create_error(  'Invalid value for eventID'  );
    }

    require_once 'CRM/Event/BAO/Event.php';

    return CRM_Event_BAO_Event::del( $eventID ) ?  civicrm_api3_create_success( ) : civicrm_api3_create_error(  'Error while deleting event' );
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}

