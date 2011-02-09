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
 * File for the CiviCRM APIv3 activity functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Activity
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Activity.php 30486 2010-11-02 16:12:09Z shot $
 *
 */

/**
 * Include common API util functions
 */
require_once 'api/v3/utils.php';

require_once 'CRM/Activity/BAO/Activity.php';
require_once 'CRM/Core/DAO/OptionGroup.php';

/**
 * Create a new Activity.
 *
 * Creates a new Activity record and returns the newly created
 * activity object (including the contact_id property). 
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new contact.
 * @param string $activity_type Which class of contact is being created.
 *            Valid values = 'SMS', 'Meeting', 'Event', 'PhoneCall'.
 * {@schema Activity/Activity.xml}
 *
 * @return CRM_Activity|CRM_Error Newly created Activity object
 *
 * @todo Erik Hommel 16 dec 2010 check if create function processes update correctly when activity_id is passed
 * @todo Erik Hommel 16 dec 2010 check for mandatory fields with utils function civicrm_verify_mandatory
 * @todo Erik Hommel 16 dec 2010 check permissions with utils function civicrm_api_permission_check
 * @todo Eileen 2 Feb - I am not sure custom data stuff is working - test doesn't seem to really test for it
 * 
 * @example ActivityCreate.php
 * {@example ActivityCreate.php 0} 
 *
 */
function civicrm_activity_create( &$params )
{
  _civicrm_initialize( true );
  try{
    $errors = array( );
    $addmode = True;
    if (!empty($params['id']) || !empty($params['activity_id'])){
      $addmode = False;
    }

    // check for various error and required conditions
    $errors = _civicrm_activity_check_params( $params, $addmode ) ;

    if ( !empty( $errors ) ) {
      return $errors;
    }

    // processing for custom data
    $values = array();
    _civicrm_custom_format_params( $params, $values, 'Activity' );

    if ( ! empty($values['custom']) ) {
      $params['custom'] = $values['custom'];
    }
    // create activity
    $activity = CRM_Activity_BAO_Activity::create( $params );

    if ( isset( $activity->id ) ) {
    _civicrm_object_to_array( $activity, $activityArray[$activity->id]);
    return civicrm_create_success($activityArray,$params);
    }


  } catch (PEAR_Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  }
}

/**
 *
 * @param array $params
 * @return array
 *
 * @todo Erik Hommel 16 dec 2010 check permissions with utils function civicrm_api_permission_check
 * @todo Erik Hommel 16 dec 2010 check if all DB fields are returned
 * @todo Erik Hommel 16 dec 2010 check if civicrm_create_success is handled correctly with REST (should be fixed in utils function civicrm_create_success)
 * @todo - this is a very limited GET not a search
 * 
 * {@example ActivityGet.php 0}
 */

function civicrm_activity_get( $params ) {
  _civicrm_initialize( true );
  try{

    civicrm_verify_one_mandatory($params,'',array('contact_id','activity_id'));
    if (!empty($params['contact_id'])){
     $activities = _civicrm_activities_get_by_contact($params['contact_id']);
     return civicrm_create_success($activities,$params);
    }
    $activityId = CRM_Utils_Array::value( 'activity_id', $params );
    $activity = _civicrm_activity_get( $activityId );

    if ( $activity ) {
      return civicrm_create_success( $activity ,$params);
    } else {
      return civicrm_create_error(  'Invalid Data'  );
    }
  } catch (PEAR_Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  }
}

/**
 * Delete a specified Activity.
 * @param array $params array holding 'id' of activity to be deleted
 *
 * @return void|CRM_Core_Error  An error if 'activityName or ID' is invalid,
 *                         permissions are insufficient, etc.
 *
 * @access public
 *
 * @todo what are required mandatory fields? id?
 * @todo Erik Hommel 16 dec 2010 check permissions with utils function civicrm_api_permission_check
 * @todo Erik Hommel 16 dec 2010 check if civicrm_create_success is handled correctly with REST (should be fixed in utils function civicrm_create_success)
 * {@example ActivityDelete.php 0}
 */
function civicrm_activity_delete( &$params )
{
  _civicrm_initialize(true );
  try{

    civicrm_verify_mandatory($params);
    $errors = array( );

    //check for various error and required conditions
    $errors = _civicrm_activity_check_params( $params ) ;

    if ( !empty( $errors ) ) {
      return $errors;
    }

    if ( CRM_Activity_BAO_Activity::deleteActivity( $params ) ) {
      return civicrm_create_success( );
    } else {
      return civicrm_create_error(  'Could not delete activity'  );
    }
  } catch (PEAR_Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  }
}

/**
 * Retrieve a specific Activity by Id.
 *
 * @param int $activityId
 * @todo this should probably be merged into main function
 * @return array (reference)  activity object
 * @access public
 *  */
function _civicrm_activity_get( $activityId, $returnCustom = true ) {
  $dao = new CRM_Activity_BAO_Activity();
  $dao->id = $activityId;
  if( $dao->find( true ) ) {
    $activity = array();
    _civicrm_object_to_array( $dao, $activity[$dao->id] );
    //also return custom data if needed.
    if ( $returnCustom && !empty( $activity ) ) {
      $customdata = _civicrm_activity_custom_get( array( 'activity_id'      => $activityId,
                                                              'activity_type_id' => $activity['activity_type_id']  )  );
     if ( is_array( $customData ) && !empty( $customData ) ) {
      $activity = array_merge( $activity, $customdata );
      }
    }
    return $activity;
  } else {
    return false;
  }
}

/**
 * Function to check for required params
 *
 * @param array   $params  associated array of fields
 * @param boolean $addMode true for add mode
 *
 * @return array $error array with errors
 */
function _civicrm_activity_check_params ( &$params, $addMode = false )
{


  $contactIds = array( 'source'   => CRM_Utils_Array::value( 'source_contact_id', $params ),
                         'assignee' => CRM_Utils_Array::value( 'assignee_contact_id', $params ),
                         'target'   => CRM_Utils_Array::value( 'target_contact_id', $params )
  );

  foreach ( $contactIds as $key => $value ) {
    if ( empty( $value ) ) {
      continue;
    }
    $valueIds = array( $value );
    if ( is_array( $value ) ) {
      $valueIds = array( );
      foreach ( $value as $id ) {
        if ( $id ) $valueIds[$id] = $id;
      }
    }
    if ( empty( $valueIds ) ) {
      continue;
    }

    $sql = '
SELECT  count(*) 
  FROM  civicrm_contact 
 WHERE  id IN (' . implode( ', ', $valueIds ) . ' )';
    if ( count( $valueIds ) !=  CRM_Core_DAO::singleValueQuery( $sql ) ) {
      return civicrm_create_error(  'Invalid %1 Contact Id', array( 1 => ucfirst( $key ) )  );
    }
  }

  $activityIds = array( 'activity' => CRM_Utils_Array::value( 'id', $params ),
                          'parent'   => CRM_Utils_Array::value( 'parent_id', $params ),
                          'original' => CRM_Utils_Array::value( 'original_id', $params )
  );

  foreach ( $activityIds as $id => $value ) {
    if (  $value &&
    !CRM_Core_DAO::getFieldValue( 'CRM_Activity_DAO_Activity', $value, 'id' ) ) {
      return civicrm_create_error(  'Invalid ' . ucfirst( $id ) . ' Id' );
    }
  }
  /*
   * @todo unique name for subject is activity subject - subject won't be supported in v4
   */
  // check for activity subject if add mode
  if ( $addMode && ! isset( $params['subject']) && ! isset( $params['activity_subject'] ) ) {
    return civicrm_create_error( 'Missing Subject'  );
  }
  /*
   * @todo unique name for id is activity id - id won't be supported in v4
   */

  if ( ! $addMode && ! isset( $params['id'] )&& ! isset( $params['activity_id'] )) {
    return civicrm_create_error(  'Required parameter "id" not found'  );
  }

  if ( ! $addMode && isset($params['id']) && (! is_numeric ( $params['id'] )) || (isset($params['id']) && ! is_numeric ( $params['id'] ) )) {
    return civicrm_create_error(  'Invalid activity "id"'  );
  }

  require_once 'CRM/Core/PseudoConstant.php';
  $activityTypes = CRM_Core_PseudoConstant::activityType( true, true, true, 'name' );

  // check if activity type_id is passed in
  if ( $addMode && !isset( $params['activity_name'] )  && !isset( $params['activity_type_id'] ) ) {
    //when name AND id are both absent
    return civicrm_create_error(  'Missing Activity Type'  );
  } else {
    $activityName   = CRM_Utils_Array::value( 'activity_name', $params );
    $activityTypeId = CRM_Utils_Array::value( 'activity_type_id', $params );

    if ( $activityName ) {
      $activityNameId = array_search( ucfirst( $activityName ), $activityTypes );

      if ( !$activityNameId ) {
        return civicrm_create_error(  'Invalid Activity Name'  );
      } else if ( $activityTypeId && ( $activityTypeId != $activityNameId ) ) {
        return civicrm_create_error(  'Mismatch in Activity'  );
      }
      $params['activity_type_id'] = $activityNameId;
    } else if ( $activityTypeId &&
    !array_key_exists( $activityTypeId, $activityTypes ) ) {
      return civicrm_create_error( 'Invalid Activity Type ID' );
    }
  }
  /*
   * @todo unique name for status_id is activity status id - status id won't be supported in v4
   */
  if (!empty($params['status_id'])){
    $params['activity_status_id'] = $params['status_id'];
  }
  // check for activity status is passed in
  if ( isset( $params['activity_status_id'] ) ) {
    require_once "CRM/Core/PseudoConstant.php";
    $activityStatus = CRM_Core_PseudoConstant::activityStatus( );

    if ( is_numeric( $params['activity_status_id'] ) && !array_key_exists( $params['activity_status_id'], $activityStatus ) ) {
      return civicrm_create_error( 'Invalid Activity Status' );
    } elseif ( !is_numeric( $params['activity_status_id'] ) ) {
      $statusId = array_search( $params['activity_status_id'], $activityStatus );

      if ( !is_numeric( $statusId ) ) {
        return civicrm_create_error( 'Invalid Activity Status' );
      }
    }
  }

  if ( isset( $params['priority_id'] ) && is_numeric( $params['priority_id'] ) ) {
    require_once "CRM/Core/PseudoConstant.php";
    $activityPriority = CRM_Core_PseudoConstant::priority( );

    if ( !array_key_exists( $params['priority_id'], $activityStatus ) ) {
      return civicrm_create_error( 'Invalid Priority' );
    }
  }

  // check for activity duration minutes
  if ( isset( $params['duration_minutes'] ) && !is_numeric( $params['duration_minutes'] ) ) {
    return civicrm_create_error('Invalid Activity Duration (in minutes)' );
  }

  // check for source contact id
  if ( $addMode && empty( $params['source_contact_id'] ) ) {
    return  civicrm_create_error( 'Missing Source Contact' );
  }

  if ( $addMode &&
  !CRM_Utils_Array::value( 'activity_date_time', $params ) ) {
    $params['activity_date_time'] = CRM_Utils_Date::processDate( date( 'Y-m-d H:i:s' ) );
  } else {
    if ( CRM_Utils_Array::value( 'activity_date_time', $params ) ) {
      $params['activity_date_time'] = CRM_Utils_Date::processDate( $params['activity_date_time'] );
    }
  }

  return null;
}

/**
 * Convert an email file to an activity
 * @todo EM  change inputs to an array
 * @todo - can we make this v3 happy - is this documented?
 */
function civicrm_activity_processemail( $file, $activityTypeID, $result = array( ) ) {

  // do not parse if result array already passed (towards EmailProcessor..)
  if ( empty($result) ) {
    // might want to check that email is ok here
    if ( ! file_exists( $file ) ||
    ! is_readable( $file ) ) {
      //TODO we don't like creating core errors!
      return CRM_Core_Error::createAPIError(  "File $file does not exist or is not readable");
    }
  }

  require_once 'CRM/Utils/Mail/Incoming.php';
  $result = CRM_Utils_Mail_Incoming::parse( $file );
  if ( $result['is_error'] ) {
    return $result;
  }

  $params = _civicrm_activity_buildmailparams( $result, $activityTypeID );
  $params['version'] =3;
  return civicrm_activity_create( $params );
}

/**
 *
 * @param <type> $result
 * @param <type> $activityTypeID
 * @return <type>
 */
function _civicrm_activity_buildmailparams( $result, $activityTypeID ) {
  // get ready for collecting data about activity to be created
  $params = array();

  $params['activity_type_id']   = $activityTypeID;
  $params['status_id']          = 2;
  $params['source_contact_id']  = $params['assignee_contact_id'] = $result['from']['id'];
  $params['target_contact_id']  = array( );
  $keys = array( 'to', 'cc', 'bcc' );
  foreach ( $keys as $key ) {
    if ( is_array( $result[$key] ) ) {
      foreach ( $result[$key] as $key => $keyValue ) {
        $params['target_contact_id'][]  = $keyValue['id'];
      }
    }
  }
  $params['subject']            = $result['subject'];
  $params['activity_date_time'] = $result['date'];
  $params['details']            = $result['body'];

  for ( $i = 1; $i <= 5; $i++ ) {
    if ( isset( $result["attachFile_$i"] ) ) {
      $params["attachFile_$i"] = $result["attachFile_$i"];
    }
  }

  return $params;
}


/**
 * Function retrieve activity custom data.
 * @param  array  $params key => value array.
 * @return array  $customData activity custom data
 * @todo is this an internal function? should be just returned / available by 'return' param?
 *
 * @access public
 */
function _civicrm_activity_custom_get( $params ) {

  $customData = array( );
  if ( !CRM_Utils_Array::value( 'activity_id', $params ) ) {
    return $customData;
  }

  require_once 'CRM/Core/BAO/CustomGroup.php';
  $groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Activity',
  CRM_Core_DAO::$_nullObject,
  $params['activity_id'],
  null,
  CRM_Utils_Array::value( 'activity_type_id', $params )
  );
  //get the group count.
  $groupCount = 0;
  foreach ( $groupTree as $key => $value ) {
    if ( $key === 'info' ) {
      continue;
    }
    $groupCount++;
  }
  $formattedGroupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree,
  $groupCount,
  CRM_Core_DAO::$_nullObject );
  $defaults = array( );
  CRM_Core_BAO_CustomGroup::setDefaults( $formattedGroupTree, $defaults );
  if ( !empty( $defaults ) ) {
    foreach ( $defaults as $key => $val ) {
      $customData[  $params['activity_id']][$key] = $val;
    }
  }

  return $customData;
}

/**
 * Retrieve a set of Activities specific to given contact Id.
 * @param int $contactID.
 *
 * @return array (reference)  array of activities.
 * @access public
 *
 * @todo Erik Hommel 16 dec 2010 Incoming params have to be array
 * @todo Erik Hommel 16 dec 2010 test mandatory contactId with utils function civicrm_verify_mandatory
 * @todo Erik Hommel 16 dec 2010 check permission with utils function civicrm_api_permission_check
 * @todo Erik Hommel 16 dec 2010 should function civicrm_activity_custom_get be separate? or with params['custom_date'] => 1?
 */
function &_civicrm_activities_get_by_contact( $contactID, $type = 'all' )
{
  $activities = CRM_Activity_BAO_Activity::getContactActivity( $contactID );


  //get the custom data.
  if ( is_array( $activities ) && !empty( $activities ) ) {
    require_once 'api/v3/Activity.php';
    foreach ( $activities as $activityId => $values ) {
      $customParams =  array( 'activity_id'      => $activityId,
                                    'activity_type_id' => CRM_Utils_Array::value( 'activity_type_id', $values ) );

      $customData = _civicrm_activity_custom_get( $customParams );

      if ( is_array( $customData ) && !empty( $customData ) ) {
        $activities[$activityId] = array_merge( $activities[$activityId], $customData );
      }
    }
  }

  return $activities;
}


