<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
 * File for the CiviCRM APIv2 activity functions
 *
 * @package CiviCRM_APIv2
 * @subpackage API_Case
 * @copyright CiviCRM LLC (c) 2004-2010
 *
 */

/**
 * Include common API util functions
 */
require_once 'api/v2/utils.php';

/**
 * create/update case
 * 
 * This API is used to create new case
 * In case of updating existing group, id of that particular case must
 * be in $params array. Either id or name is required field in the
 * $params array
 * 
 * @param array $params  (referance) Associative array of property
 *                       name/value pairs to insert in new 'case'
 * 
 * @return array   returns id of the case created if success,
 *                 error message otherwise
 * 
 * @access public
 */
function civicrm_case_create( &$params )
{
    _civicrm_initialize( );
    
    $errors = array( );
    
    //check for various error and required conditions
    $errors = _civicrm_case_check_params( $params, true ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
    
    // processing for custom data
    $values = array();
    _civicrm_custom_format_params( $params, $values, 'Activity' );
    
    require_once 'CRM/Case/BAO/Case.php';
    $case = CRM_Case_BAO_Case::create( $params );
    
    if ( is_null( $case ) ) {
        return civicrm_create_error( 'Case not created' );
    } else{
			return civicrm_create_success( $case->id );
	    }
}

/**
 *
 * @param <type> $params
 * @param <type> $returnCustom
 * @return <type>
 */
function civicrm_case_read( &$params )
{
    _civicrm_initialize( );
    
    $caseId = $params['case_id'];
    if ( empty( $caseId ) ) {
        return civicrm_create_error( ts ("CaseID not provided." ) );
    }
    
    if ( !is_numeric( $caseId ) ) {
        return civicrm_create_error( ts ( "Invalid Case Id: {$params['case_id']}" ) );
    }
    
    $case = _civicrm_case_get( $caseId );
    
    if ( $case ) {
        return civicrm_create_success( $case );
    } else {
        return civicrm_create_error( ts( 'Invalid Data' ) );
    }
}

/**
 * Retrieve a specific Case by Id.
 *
 * @param int $caseId
 *
 * @return array (reference) case object
 * @access public
 */
function _civicrm_activity_read( $caseId ) {
    $dao = new CRM_Activity_BAO_Case();
    $dao->id = $caseId;
    if( $dao->find( true ) ) {
        $case = array();
        _civicrm_object_to_array( $dao, $case );

        return $case;
    } else {
        return false;
    }
}

/**
 * Update a specified case.
 *
 * Updates case with the values passed in the 'params' array. An
 * error is returned if an invalid id is passed 
 * @param CRM_Activity $activity A valid Activity object
 * @param array       $params  Associative array of property
 *                             name/value pairs to be updated. 
 *  
 * @return CRM_Activity|CRM_Core_Error  Return the updated ActivtyType Object else
 *                                Error Object (if integrity violation)
 *
 * @access public
 *
 */
function civicrm_case_update( &$params )
{
    _civicrm_initialize( );
    
    $errors = array( );
    //check for various error and required conditions
    $errors = _civicrm_case_check_params( $params ) ;

    if ( !empty( $errors ) ) {
        return $errors;
    }
    
    $case = _civicrm_case_update( $params );
    return $case;
}

/**
 * Function to update a case
 * @param CRM_Case $case Case object to be deleted
 *
 * @return void|CRM_Core_Error  An error if 'caseName or ID' is invalid,
 *                         permissions are insufficient, etc.
 *
 * @access public
 *
 */
function _civicrm_case_update( $params ) 
{
    require_once 'CRM/Case/BAO/Case.php';
    $dao =& new CRM_Case_BAO_Case();
    $dao->id = $params['id'];
    
    // extracted from the activity_date_time verification
    $dao->copyValues( $params );
    $dao->save( );
    
    $case = array();
    //TODO: Locate the _civicrm_object_to_array function declaration.
    _civicrm_object_to_array( $dao, $case );
    
    return $case;
}


/**
 * Delete a case with given case id
 *
 * @param  array   	  $params (reference ) input parameters, case_id element required
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 */
function civicrm_case_delete( &$params )
{
    _civicrm_initialize( );
    
    $caseID = CRM_Utils_Array::value( 'case_id', $params );
	if ( ! $caseID ) {
        return civicrm_create_error( ts( 'Could not find case_id in the input parameters.' ) );
    }
    
    if ( CRM_Contact_BAO_Contact::deleteCase( $caseID ) ) {
        return civicrm_create_success( );
    } else {
        return civicrm_create_error( ts( "Could not delete case: {$params['case_id']}" ) );
    }
    
}

function _civicrm_case_check_params( &$params, $createMode = false, $readMode = false, $updateMode = false ) 
{
    // return error if we do not get any params
    if ( is_null( $params ) || ! is_array( $params ) || empty( $params ) ) {
        return civicrm_create_error( ts( 'Invalid or missing Input Parameters. Must provide an array.' ) );
    }
	// createMode validations
	if ( $createMode && ! isset( $params['case_type_id'] )) {
        return civicrm_create_error( ts( 'Required parameter "case_type_id" not found' ) );
    }

	if ( $createMode && ! isset( $params['case_status_id'] )) {
        return civicrm_create_error( ts( 'Required parameter "case_status_id" not found' ) );
    }
    
	if ( ! $createMode && ! isset( $params['id'] )) {
        return civicrm_create_error( ts( 'Required parameter "id" not found' ) );
    }
   //TODO: validate readMode params
	// readMode validations
	
   //TODO: validate updateMode params	
	// updateMode validations
   
   
   // General validations 
	// case_type_id
		// exists
		if ( ! array_key_exists( 'case_type_id', $params ) ) {
			return civicrm_create_error( ts( 'Missing Case Type' ) ); 
		}
		// is string
		if ( ! is_string( $params['case_type_id'] ) ) {
        return civicrm_create_error( ts("Invalid Case Type: {$params['case_type_id']}" ) );
        }
	
	// subject
	
	// start_date
		// If start_date exists it must be date YYYY-mm-dd or yyyymmdd

	// end_date
		// if (end_date != NULL) end_date >= start_date
		
	// details
	
	// status_id
		// exists
		if ( empty( $params['status_id'] ) ) {
        return  civicrm_create_error( ts('Missing Status ID') );
    	} 
		// is numeric
		if ( !is_numeric( $params['status_id'] ) ) {
        return civicrm_create_error( ts("Invalid Status ID: {$params['status_id']}" ) );
        }
		
	// is_deleted
		// != 1 (?) BOOLEAN
}

/**
 * Create a case for a contact.
 *
 * @param  array   $params: case_id, contct_id, activity_suubject,
 * 	location, start_date, duration, medium_id, activity_details
 *
 */
function civicrm_case_add_activities ( $params ) {
	// Create a new case for contact
        $contactParams = array('case_id'    => $params['case_id'],
                               'contact_id' => $params['contact_id']
                               );
        require_once 'CRM/Case/BAO/Case.php';
        CRM_Case_BAO_Case::addCaseToContact( $contactParams );

        // Initialize XML processor with $params
        require_once 'CRM/Case/XMLProcessor/Process.php';
        $xmlProcessor = new CRM_Case_XMLProcessor_Process( );
        $xmlProcessorParams = array( 'clientID'           => strval($params['contact_id']),
                                     'creatorID'          => $params['creator_id'],
                                     'standardTimeline'   => 1,
                                     'activityTypeName'   => 'Open Case',
                                     'caseID'             => $params['case_id'],
                                     'subject'            => $params['activity_subject'],
                                     'location'           => $params['location'],
                                     'activity_date_time' => $params['start_date'],
                                     'duration'           => $params['duration'],
                                     'medium_id'          => $params['medium_id'],
                                     'details'            => $params['activity_details'],
                                     );

        $xmlProcessorParams['custom'] = array();


	// Do it! :-D
        $xmlProcessor->run( $params['case_type'], $xmlProcessorParams );

        // status msg
        $params['statusMsg'] = ts('Case opened successfully.');
        
	// return case create success
	$case = _civicrm_case_get( $params['case_id'] );
    return civicrm_create_success( $case );

}
