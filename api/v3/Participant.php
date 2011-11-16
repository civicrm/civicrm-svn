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
 * File for the CiviCRM APIv3 participant functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Participant
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Participant.php 30486 2010-11-02 16:12:09Z shot $
 *
 */

/**
 * Files required for this package
 */
 require_once 'CRM/Event/BAO/Participant.php';

/**
 * Create an Event Participant
 *
 * This API is used for creating a participants in an event.
 * Required parameters : event_id AND contact_id for new creation
 *                     : participant as name/value with participantid for edit
 * @param   array  $params     an associative array of name/value property values of civicrm_participant
 *
 * @return array apiresult
 * {@getfields participant_create}
 * @access public
 */
function civicrm_api3_participant_create($params)
{
        $errors= _civicrm_api3_participant_check_params( $params );
        if ( civicrm_error( $errors ) ) {
            return $errors;
        }
        $value = array();
        _civicrm_api3_custom_format_params( $params, $values, 'Participant' );
        $params = array_merge($values,$params);  
        require_once 'CRM/Event/BAO/Participant.php';

        $participantBAO = CRM_Event_BAO_Participant::create($params);
        _civicrm_api3_object_to_array($participantBAO , $participant[$participantBAO->id]);
        return civicrm_api3_create_success( $participant,$params,'participant','create',$participantBAO );
    

}
/*
 * Adjust Metadata for Create action
 * 
 * The metadata is used for setting defaults, documentation & validation
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_participant_create_spec(&$params){
  $params['status_id']['api.default'] = "1";
  $params['register_date']['api.default'] = "now";
  $params['event_id']['api.required'] =1;
  $params['contact_id']['api.required'] =1;
}
/**
 * Retrieve a specific participant, given a set of input params
 * If more than one matching participant exists, return an error, unless
 * the client has requested to return the first found contact
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return array (reference )        array of properties, if error an array with an error id and error message
 * {@getfields participant_get}
 * @access public
 */
function civicrm_api3_participant_get( $params ) {

        $values = array( );
        if ( isset ( $params['id'] ) ) {
            $params['participant_id' ] = $params['id'];
            unset( $params['id'] );
        }

            $inputParams      = array( );
    $returnProperties = array( );
    $otherVars = array( 'sort', 'offset', 'rowCount' );

    $sort     = null;
    $offset   = 0;
    $rowCount = 25;
    foreach ( $params as $n => $v ) {
        if ( substr( $n, 0, 7 ) == 'return.' ) {
            $returnProperties[ substr( $n, 7 ) ] = $v;
        } elseif ( in_array ( $n, $otherVars ) ) {
            $$n = $v;
        } else {
            $inputParams[$n] = $v;
        }
    }

    // add is_test to the clause if not present
    if ( ! array_key_exists( 'participant_test', $inputParams ) ) {
        $inputParams['participant_test'] = 0;
    }

    require_once 'CRM/Contact/BAO/Query.php';
    require_once 'CRM/Event/BAO/Query.php';
    if ( empty( $returnProperties ) ) {
        $returnProperties = CRM_Event_BAO_Query::defaultReturnProperties( CRM_Contact_BAO_Query::MODE_EVENT );
    }

    $newParams = CRM_Contact_BAO_Query::convertFormValues( $params);
    $query = new CRM_Contact_BAO_Query( $newParams, $returnProperties, null,
                                        false, false, CRM_Contact_BAO_Query::MODE_EVENT );
    list( $select, $from, $where , $having) = $query->query( );

    $sql = "$select $from $where $having";

    if ( ! empty( $sort ) ) {
        $sql .= " ORDER BY $sort ";
    }
    $sql .= " LIMIT $offset, $rowCount ";
    $dao = CRM_Core_DAO::executeQuery( $sql );

    $participant = array( );
    while ( $dao->fetch( ) ) {
        $participant[$dao->participant_id] = $query->store( $dao );
          _civicrm_api3_custom_data_get($participant[$dao->participant_id],'Participant',$dao->participant_id,null);          
    }

        return civicrm_api3_create_success($participant,$params, 'participant','get',$dao);

}


/**
 * Deletes an existing contact participant
 *
 * This API is used for deleting a contact participant
 *
 * @param  Int  $participantID   Id of the contact participant to be deleted
 *
 * {@getfields participant_delete}
 * @access public
 */
function &civicrm_api3_participant_delete( $params )
{
        $participant = new CRM_Event_BAO_Participant();
        $result = $participant->deleteParticipant( $params['id'] );

        if ( $result ) {
            return  civicrm_api3_create_success( );
        } else {
            return  civicrm_api3_create_error('Error while deleting participant');
        }

}


/**
 * @deprecated - this is part of the import parser not the API & needs to be moved on out
 * @param <type> $params
 * @param <type> $onDuplicate
 * @return <type>
 */
function _civicrm_api3_create_participant_formatted( $params , $onDuplicate )
{
    require_once 'CRM/Event/Import/Parser.php';
    if ( $onDuplicate != CRM_Event_Import_Parser::DUPLICATE_NOCHECK) {
        CRM_Core_Error::reset( );
        $error = _civicrm_api3_participant_check_params( $params ,true );
        if ( civicrm_error( $error ) ) {
            return $error;
        }
    }

    return civicrm_api3_participant_create( $params );
}

/**
 *
 * @param <type> $params
 * @return <type>
 */
function _civicrm_api3_participant_check_params( $params ,$checkDuplicate = false )
{

    //check if participant id is valid or not
    if( CRM_Utils_Array::value( 'id', $params ) ) {
        $participant = new CRM_Event_BAO_Participant();
        $participant->id = $params['id'];
        if ( !$participant->find( true )) {
            return civicrm_api3_create_error( ts( 'Participant  id is not valid' ));
        }
    }
    require_once 'CRM/Contact/BAO/Contact.php';
    //check if contact id is valid or not
    if( CRM_Utils_Array::value( 'contact_id', $params ) ) {
        $contact = new CRM_Contact_BAO_Contact();
        $contact->id = $params['contact_id'];
        if ( !$contact->find( true )) {
            return civicrm_api3_create_error( ts( 'Contact id is not valid' ));
        }
    }

    //check that event id is not an template
    if( CRM_Utils_Array::value( 'event_id', $params ) ) {
        $isTemplate = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event', $params['event_id'], 'is_template' );
        if ( !empty( $isTemplate ) ) {
            return civicrm_api3_create_error( ts( 'Event templates are not meant to be registered' ));
        }
    }

    $result = array( );
    if( $checkDuplicate ) {
        if( CRM_Event_BAO_Participant::checkDuplicate( $params, $result ) ) {
            $participantID = array_pop( $result );

            $error = CRM_Core_Error::createError( "Found matching participant record.",
                                                  CRM_Core_Error::DUPLICATE_PARTICIPANT,
                                                  'Fatal', $participantID );

            return civicrm_api3_create_error( $error->pop( ),
                                              array( 'contactID'     => $params['contact_id'],
                                                     'participantID' => $participantID ) );
        }
    }
    return true;
}
