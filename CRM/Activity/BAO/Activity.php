<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Activity/DAO/Activity.php';

/**
 * This class is for activity functions
 *
 */
class CRM_Activity_BAO_Activity extends CRM_Activity_DAO_Activity
{
    
    /**
     * static field for all the activity information that we can potentially import
     *
     * @var array
     * @static
     */
    static $_importableFields = null;

    /**
     * Check if there is absolute minimum of data to add the object
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return boolean
     * @access public
     */
    public function dataExists( &$params ) 
    {
        if ( CRM_Utils_Array::value( 'subject', $params) &&
             CRM_Utils_Array::value( 'source_contact_id', $params ) ) {
            return true;
        }
        return false;
    }


    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array  $params   (reference ) an assoc array of name/value pairs
     * @param array  $defaults (reference ) an assoc array to hold the flattened values
     * @param string $activityType activity type
     *
     * @return object CRM_Core_BAO_Meeting object
     * @access public
     */
    public function retrieve ( &$params, &$defaults ) 
    {
        $activity =& new CRM_Activity_DAO_Activity( );
        $activity->copyValues( $params );

        if ( $activity->find( true ) ) {
            require_once "CRM/Contact/BAO/Contact.php";
            // TODO: at some stage we'll have to deal
            // TODO: with multiple values for assignees and targets, but
            // TODO: for now, let's just fetch first row
            require_once 'CRM/Activity/BAO/ActivityAssignment.php';
            $assignment =& new CRM_Activity_BAO_ActivityAssignment( );
            $assigneeContactId = $assignment->retrieveAssigneeIdByActivityId( $activity->id );
            if ( $assigneeContactId ) { 
                $defaults['assignee_contact_id'] = $assigneeContactId;
                $defaults['assignee_contact'] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                                             $assigneeContactId,
                                                                             'sort_name' );
            }
            
            require_once 'CRM/Activity/BAO/ActivityTarget.php';
            $target =& new CRM_Activity_BAO_ActivityTarget( );
            $targetContactId = $target->retrieveTargetIdByActivityId( $activity->id );
            if ( $targetContactId ) { 
                $defaults['target_contact_id'] = $targetContactId; 
                $defaults['target_contact'] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                                           $targetContactId,
                                                                           'sort_name' );
            }

            if ( $activity->source_contact_id ) {
                $defaults['source_contact'] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                                           $activity->source_contact_id,
                                                                           'sort_name' );
            }
            
            //get case subject
            require_once "CRM/Case/BAO/Case.php";
            $defaults['case_subject'] = CRM_Case_BAO_Case::getCaseSubject( $activity->id );

            CRM_Core_DAO::storeValues( $activity, $defaults );

            return $activity;
        }
        return null;
    }

    /**
     * Function to delete the activity
     * @param array  $params  associated array 
     *
     * @return void
     * @access public
     *
     */
    public function deleteActivity( &$params ) 
    {
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $activity     =& new CRM_Activity_DAO_Activity( );
        $activity->copyValues( $params );
        $result = $activity->delete( );
        
        $transaction->commit( );
        return $result;
    }
    
    /**
     * Delete activity assignment record
     *
     * @param int    $id  activity id
     *
     * @return null
     * @access public
     */
    public function deleteActivityAssignment( $activityId ) 
    {
        require_once 'CRM/Activity/BAO/ActivityAssignment.php';
        $assignment              =& new CRM_Activity_BAO_ActivityAssignment( );
        $assignment->activity_id = $activityId;
        $assignment->delete( );
    }

    /**
     * Delete activity target record
     *
     * @param int    $id  activity id
     *
     * @return null
     * @access public
     */
    public function deleteActivityTarget( $activityId ) 
    {
        require_once 'CRM/Activity/BAO/ActivityTarget.php';
        $target              =& new CRM_Activity_BAO_ActivityTarget( );
        $target->activity_id = $activityId;
        $target->delete( );
    }

    /**
     * delete all records for this contact id
     *
     * @param int    $id  ID of the contact for which the records needs to be deleted.
     * @param string $activityType activity type 
     * 
     * @return void
     * 
     * @access public
     */
    public function deleteContact($id)
    {
        $activity = array("Meeting", "Phonecall", "Activity");
        foreach ($activity as $key) {
            // need to delete for both source and target
            eval ('$dao =& new CRM_Activity_DAO_' . $key . '();');
            $dao->source_contact_id = $id;
            $dao->delete();

            eval ('$dao =& new CRM_Activity_DAO_' . $key . '();');
            $dao->target_entity_table = 'civicrm_contact';
            $dao->target_entity_id    = $id;        
            $dao->delete();
        }
    }

    /**
     * Function to process the activities
     *
     * @param object $form         form object
     * @param array  $params       associated array of the submitted values
     * @param array  $ids          array of ids
     * @param string $activityType activity Type
     * @param boolean $record   true if it is Record Activity 
     * @access public
     * @return
     */
    public function create( &$params )
    {
        // check required params
        if ( ! self::dataExists( $params ) ) {
            CRM_Core_Error::fatal( 'Not enough data to create activity object,' );
        }
        
        $activity =& new CRM_Activity_DAO_Activity( );

        //convert duration hour/ minutes to minutes
        require_once "CRM/Utils/Date.php";
        $params['duration'] = CRM_Utils_Date::standardizeTime( CRM_Utils_Array::value( 'duration_hours', $params ),
                                                               CRM_Utils_Array::value( 'duration_minutes', $params )
                                                               );
        if ( !$params['status_id'] ) {
            if ( $params['activity_date_time'] && $params['activity_date_time'] < date('Ymd') ) {
                $params['status_id'] = 2;
            } else {
                $params['status_id'] = 1;
            }
        }
        $activity->copyValues( $params );

        // start transaction        
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        $result = $activity->save( );        
        
        $activityId = $result->id;

        // attempt to save activity assignment
        if ( CRM_Utils_Array::value( 'assignee_contact_id', $params ) ) {
            require_once 'CRM/Activity/BAO/ActivityAssignment.php';
            
            $assignmentParams = array( 'activity_id'         => $activityId,
                                       'assignee_contact_id' => $params['assignee_contact_id'] );
            
            if ( CRM_Utils_Array::value( 'id', $params ) ) {
                $assignment =& new CRM_Activity_BAO_ActivityAssignment( );
                $assignment->activity_id = $activityId;
                $assignment->find( true );

                if ( $assignment->assignee_contact_id != $params['assignee_contact_id'] ) {
                    $assignmentParams['id'] = $assignment->id;
                    $resultAssignment       = CRM_Activity_BAO_ActivityAssignment::create( $assignmentParams );
                }
            } else {
                if ( ! is_a( $result, 'CRM_Core_Error' ) ) {
                    $resultAssignment = CRM_Activity_BAO_ActivityAssignment::create( $assignmentParams );
                }
            }
        } else {       
            $resultAssignment = array( );
            self::deleteActivityAssignment( $activityId );
        }

        // attempt to save activity targets
        if ( CRM_Utils_Array::value( 'target_contact_id', $params ) ) {
            require_once 'CRM/Activity/BAO/ActivityTarget.php';

            $targetParams = array( 'activity_id'       => $activityId,
                                   'target_contact_id' => $params['target_contact_id'] );
            
            if ( CRM_Utils_Array::value( 'id', $params ) ) {
                $target =& new CRM_Activity_BAO_ActivityTarget( );
                $target->activity_id = $activityId;
                $target->find( true );
                
                if ( $target->target_contact_id != $params['target_contact_id'] ) {
                    $targetParams['id'] = $target->id;
                    $resultTarget       = CRM_Activity_BAO_ActivityTarget::create( $targetParams );
                }
            } else {
                if ( ! is_a( $result, 'CRM_Core_Error' ) ) {
                    $resultTarget = CRM_Activity_BAO_ActivityTarget::create( $targetParams );
                }
            }
        } else {
            $resultTarget = array( );
            self::deleteActivityTarget( $activityId );
        }

        // write to changelog before transation is committed/rolled
        // back (and prepare status to display)
        if ( CRM_Utils_Array::value( 'id', $params ) ) {
            $logMsg = "Activity (id: {$result->id} ) updated with ";
        } else {
            $logMsg = "Activity created for ";
        }
        
        $logMsg .= "source = {$params['source_contact_id']}, target = {$params['target_contact_id']}, assignee ={$params['assignee_contact_id']}";

        self::logActivityAction( $result, $logMsg );

        // roll back if error occured
        if ( is_a( $result, 'CRM_Core_Error' ) ) {
            $transaction->rollback( );
            return $result;
        } elseif( is_a( $resultAssignment, 'CRM_Core_Error' ) ) {
            $transaction->rollback( );
            return $resultAssignment;
        } elseif( is_a( $resultTarget, 'CRM_Core_Error' ) ) {
            $transaction->rollback( );
            return $resultTarget;
        }
        if ( CRM_Utils_Array::value( 'custom', $params ) &&
             is_array( $params['custom'] ) ) {
            require_once 'CRM/Core/BAO/CustomValueTable.php';
            CRM_Core_BAO_CustomValueTable::store( $params['custom'], 'civicrm_activity', $result->id );
        }

        $transaction->commit( );  

        return $result;
    }
        
    public function logActivityAction( $activity, $logMessage = null ) 
    {
        $session = & CRM_Core_Session::singleton();
        $id = $session->get('userID');
        require_once 'CRM/Core/BAO/Log.php';
        $logParams = array(
                           'entity_table'  => 'civicrm_activity' ,
                           'entity_id'     => $activity->id,
                           'modified_id'   => $id,
                           'modified_date' => date('Ymd'),
                           'data'          => $logMessage
                           );
        CRM_Core_BAO_Log::add( $logParams );
        return true;
    }

    /**
     * function to get the list of open Actvities
     *
     * @param array reference $params  array of parameters 
     * @param int     $offset          which row to start from ?
     * @param int     $rowCount        how many rows to fetch
     * @param object|array  $sort      object or array describing sort order for sql query.
     * @param type    $type            type of activity we're interested in
     * @param boolean $admin           if contact is admin
     * @param int     $caseId          case id
     * @param string  $context         context , page on which selector is build
     *
     * @return array (reference)      $values the relevant data object values of open activitie
     *
     * @access public
     * @static
     */
    static function &getActivities( &$data, $offset = null, $rowCount = null, $sort = null,
                                    $type ='Activity', $admin = false, $caseId = null, $context = null ) 
    {
        $dao =& new CRM_Core_DAO();

        $params = array( );
        $clause = 1 ;

        if ( !$admin ) {
            $clause = " ( source_contact_id = %1 or target_contact_id = %1 or assignee_contact_id = %1 or civicrm_case.contact_id = %1 ) ";
            $params = array( 1 => array( $data['contact_id'], 'Integer' ) );
        }
        
        $statusClause = 1 ;
        if ( $context == 'home' ) {
            $statusClause = " civicrm_activity.status_id = 1 "; 
        }

        // Exclude Contribution-related activity records if user doesn't have 'access CiviContribute' permission
        $contributionFilter = 1;
        if ( ! CRM_Core_Permission::check('access CiviContribute') ) {
            $contributionFilter = " civicrm_activity.activity_type_id != 6 ";
        }

        // Filter on case ID if looking at activities for a specific case
        $case = 1;
        if ( $caseId ) {
            $case = " civicrm_case_activity.case_id = $caseId ";
        }
        
        $query = "select DISTINCT(civicrm_activity.id), civicrm_activity.*,
                         sourceContact.sort_name as source_contact_name,
                         civicrm_activity_target.target_contact_id,
                         targetContact.sort_name as target_contact_name,
                         civicrm_activity_assignment.assignee_contact_id,
                         assigneeContact.sort_name as assignee_contact_name,
                         civicrm_option_value.value as activity_type_id,
                         civicrm_option_value.label as activity_type,
                         civicrm_case_activity.case_id as case_id,
                         civicrm_case.subject as case_subject
                  from civicrm_activity 
                  left join civicrm_activity_target on 
                            civicrm_activity.id = civicrm_activity_target.activity_id 
                  left join civicrm_activity_assignment on 
                            civicrm_activity.id = civicrm_activity_assignment.activity_id 
                  left join civicrm_contact sourceContact on 
                            source_contact_id = sourceContact.id 
                  left join civicrm_contact targetContact on 
                            target_contact_id = targetContact.id 
                  left join civicrm_contact assigneeContact on 
                            assignee_contact_id = assigneeContact.id
                  left join civicrm_option_value on
                            ( civicrm_activity.activity_type_id = civicrm_option_value.value )
                  left join civicrm_option_group on  
                            civicrm_option_group.id = civicrm_option_value.option_group_id
                  left join civicrm_case_activity on
                            civicrm_case_activity.activity_id = civicrm_activity.id
                  left join civicrm_case on
                            civicrm_case_activity.case_id = civicrm_case.id
                  where {$clause}
                        and civicrm_option_group.name = 'activity_type' 
                        and is_test = 0  and {$contributionFilter} and {$case} and {$statusClause}";

        $order = '';

        if ($sort) {
            $orderBy = $sort->orderBy();
            if ( ! empty( $orderBy ) ) {
                $order = " ORDER BY $orderBy";
            }
        }

        if ( empty( $order ) ) {
            if ( $context == 'activity' ) {
                $order = " ORDER BY activity_date_time desc ";
            } else {
                $order = " ORDER BY status_id asc, activity_date_time asc ";
            }
        }

        if ( $rowCount > 0 ) {
            $limit = " LIMIT $offset, $rowCount ";
        }

        $queryString = $query . $order . $limit;
        $dao =& CRM_Core_DAO::executeQuery( $queryString, $params );
        
        $selectorFields = array( 'activity_type_id',
                                 'activity_type',
                                 'id',
                                 'activity_date_time',
                                 'status_id',
                                 'subject',                                 
                                 'source_contact_name',
                                 'source_contact_id',
                                 'target_contact_name',
                                 'target_contact_id',
                                 'assignee_contact_name',
                                 'assignee_contact_id',
                                 'source_record_id',
                                 'case_id',
                                 'case_subject' );

        $values =array();
        $rowCnt = 0;
        while($dao->fetch()) {
            foreach( $selectorFields as $dc => $field ) {
                $values[$rowCnt][$field] = $dao->$field;
            }
            $rowCnt++;
        }
        return $values;
    }

    /**
     * Get total number of open activities
     *
     * @param  int $id id of the contact
     * @param string  $context         context , page on which selector is build
     *
     * @return int $numRow - total number of open activities    
     *
     * @static
     * @access public
     */
    static function getNumOpenActivity( $id, $admin = false, $context = null ) 
    {
        $params = array( );
        $clause = 1 ;

        if ( !$admin ) {
            $clause = " ( source_contact_id = %1 or target_contact_id = %1 or assignee_contact_id = %1 ) ";
            $params = array( 1 => array( $id, 'Integer' ) );
        }
        
        $statusClause = 1 ;
        if ( $context == 'home' ) {
            $statusClause = " civicrm_activity.status_id = 1 "; 
        }


        // Exclude Contribution-related activity records if user doesn't have 'access CiviContribute' permission
        $contributionFilter = 1;
        if ( ! CRM_Core_Permission::check('access CiviContribute') ) {
            $contributionFilter = " civicrm_activity.activity_type_id != 6 ";
        }
        
        $query = "select count(civicrm_activity.id) from civicrm_activity
                  left join civicrm_activity_target on 
                            civicrm_activity.id = civicrm_activity_target.activity_id
                  left join civicrm_activity_assignment on 
                            civicrm_activity.id = civicrm_activity_assignment.activity_id
                  where {$clause}
                  and is_test = 0 and {$contributionFilter} and {$statusClause}";

        return CRM_Core_DAO::singleValueQuery( $query, $params );
    }
    
    /**
     * send the message to all the contacts and also insert a
     * contact activity in each contacts record
     *
     * @param array  $contactIds   the array of contact ids to send the email
     * @param string $subject      the subject of the message
     * @param string $message      the message contents
     * @param string $emailAddress use this 'to' email address instead of the default Primary address
     * @param int    userID        use this userID if set
     * @return array             (total, added, notAdded) count of emails sent
     * @access public
     * @static
     */
    static function sendEmail( &$contactIds, &$subject, &$text, &$html, $emailAddress, $userID = null, $from = null ) 
    {        
        if ( $userID == null ) {
            $session =& CRM_Core_Session::singleton( );
            $userID  =  $session->get( 'userID' );
        }
        list( $fromDisplayName, $fromEmail, $fromDoNotEmail ) = CRM_Contact_BAO_Contact::getContactDetails( $userID );
        if ( ! $fromEmail ) {
            return array( count($contactIds), 0, count($contactIds) );
        }
        if ( ! trim($fromDisplayName) ) {
            $fromDisplayName = $fromEmail;
        }
        
        $matches = array();
        preg_match_all( '/(?<!\{|\\\\)\{(\w+\.\w+)\}(?!\})/',
                        $text,
                        $matches,
                        PREG_PATTERN_ORDER);
        
        if ( $matches[1] ) {
            foreach ( $matches[1] as $token ) {
                list($type,$name) = split( '\.', $token, 2 );
                if ( $name ) {
                    if ( ! isset( $messageToken['contact'] ) ) {
                        $messageToken['contact'] = array( );
                    }
                    $messageToken['contact'][] = $name;
                }
            }
        }
        
        $matches = array();
        preg_match_all( '/(?<!\{|\\\\)\{(\w+\.\w+)\}(?!\})/',
                        $subject,
                        $matches,
                        PREG_PATTERN_ORDER);
        
        if ( $matches[1] ) {
            foreach ( $matches[1] as $token ) {
                list($type,$name) = split( '\.', $token, 2 );
                if ( $name ) {
                    if ( ! isset( $subjectToken['contact'] ) ) {
                        $subjectToken['contact'] = array( );
                    }
                    $subjectToken['contact'][] = $name;
                }
            }
        }
        
        $matches = array();
        preg_match_all( '/(?<!\{|\\\\)\{(\w+\.\w+)\}(?!\})/',
                        $html,
                        $matches,
                        PREG_PATTERN_ORDER);
        
        if ( $matches[1] ) {
            foreach ( $matches[1] as $token ) {
                list($type,$name) = split( '\.', $token, 2 );
                if ( $name ) {
                    if ( ! isset( $messageToken['contact'] ) ) {
                        $messageToken['contact'] = array( );
                    }
                    $messageToken['contact'][] = $name;
                }
            }
        }
        require_once 'CRM/Utils/Mail.php';
        if (!$from ) {
            $from = CRM_Utils_Mail::encodeAddressHeader($fromDisplayName, $fromEmail);
        }
        // create the meta level record first
        //         TO DO
        //         $params =  array( 'subject'    => $subject,
        //                               'message'    => $message,
        //                               'contact_id' => $userID );
        
        //         $email  =& self::add( $params );
        
        $sent = $notSent = array();
        $returnProperties = array();
        if( isset( $messageToken['contact'] ) ) { 
            foreach ( $messageToken['contact'] as $key => $value ) {
                $returnProperties[$value] = 1; 
            }
        }
        
        if( isset( $subjectToken['contact'] ) ) { 
            foreach ( $subjectToken['contact'] as $key => $value ) {
                if ( !isset( $returnProperties[$value] ) ) {
                    $returnProperties[$value] = 1;
                }
            }
        }
        
        require_once 'CRM/Mailing/BAO/Mailing.php';
        $mailing   = & new CRM_Mailing_BAO_Mailing();
        $details   = $mailing->getDetails($contactIds, $returnProperties );
        
        require_once 'api/v2/Contact.php';
        foreach ( $contactIds as $contactId ) {
            // replace contact tokens
            $params  = array( 'contact_id' => $contactId, 'is_deceased' => 0 );

            //$contact =& crm_fetch_contact( $params );
            $contact = civicrm_contact_get( $params );
            
            if ( civicrm_error( $contact ) ) {
                $notSent[] = $contactId;
                continue;
            }
            
            if( is_array( $details[0]["{$contactId}"] ) ) {
                $contact = array_merge( $contact, $details[0]["{$contactId}"] );
            }
            
            $tokenText = CRM_Utils_Token::replaceContactTokens( $text, $contact, false, $messageToken);
            $tokenSubject = CRM_Utils_Token::replaceContactTokens( $subject, $contact, false, $subjectToken);
            $tokenHtml = CRM_Utils_Token::replaceContactTokens( $html, $contact, false, $messageToken);
            if ( self::sendMessage( $from, $userID, $contactId, $tokenSubject, $tokenText, $tokenHtml, $emailAddress, $email->id ) ) {
                $sent[] =  $contactId;
            } else {
                $notSent[] = $contactId;
            } 
        }
        
        return array( count($contactIds), $sent, $notSent );
    }
    
    /**
     * send the message to a specific contact
     *
     * @param string $from         the name and email of the sender
     * @param int    $toID         the contact id of the recipient       
     * @param string $subject      the subject of the message
     * @param string $message      the message contents
     * @param string $emailAddress use this 'to' email address instead of the default Primary address 
     * @param int    $activityID   the activity ID that tracks the message
     *
     * @return boolean             true if successfull else false.
     * @access public
     * @static
     */
    static function sendMessage( $from, $fromID, $toID, &$subject, &$text_message, &$html_message, $emailAddress, $activityID ) 
    {
        list( $toDisplayName, $toEmail, $toDoNotEmail ) = CRM_Contact_BAO_Contact::getContactDetails( $toID );
        if ( $emailAddress ) {
            $toEmail = trim( $emailAddress );
        }
        
        // make sure both email addresses are valid
        // and that the recipient wants to receive email
        if ( empty( $toEmail ) or $toDoNotEmail ) {
            return false;
        }
        if ( ! trim($toDisplayName) ) {
            $toDisplayName = $toEmail;
        }
        
        if ( ! CRM_Utils_Mail::send( $from,
                                     $toDisplayName, $toEmail,
                                     $subject,
                                     $text_message,
                                     null,
                                     null,
                                     null,
                                     $html_message) ) {
            return false;
        }

        // add activity histroy record for every mail that is send
        $activityTypeID = CRM_Core_OptionGroup::getValue( 'activity_type',
                                                          'Email',
                                                          'name' );
        
        $activity = array('source_contact_id'    => $fromID,
                          'target_contact_id'    => $toID,
                          'activity_type_id'     => $activityTypeID,
                          'activity_date_time'   => date('YmdHis'),
                          'subject'              => $subject,
                          'details'              => $text_message,
                          'status_id'            => 2
                          );
        
        require_once 'api/v2/Activity.php';
        if ( is_a( civicrm_activity_create($activity, 'Email'), 'CRM_Core_Error' ) ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * combine all the importable fields from the lower levels object
     *
     * The ordering is important, since currently we do not have a weight
     * scheme. Adding weight is super important and should be done in the
     * next week or so, before this can be called complete.
     *
     * @param NULL
     * 
     * @return array    array of importable Fields
     * @access public
     */
    function &importableFields( ) 
    {
        if ( ! self::$_importableFields ) {
            if ( ! self::$_importableFields ) {
                self::$_importableFields = array();
            }
            if (!$status) {
                $fields = array( '' => array( 'title' => ts('- do not import -') ) );
            } else {
                $fields = array( '' => array( 'title' => ts('- Activity Fields -') ) );
            }
            
            require_once 'CRM/Activity/DAO/Activity.php';
            $tmpFields     = CRM_Activity_DAO_Activity::import( );
            require_once 'CRM/Contact/BAO/Contact.php';
            $contactFields = CRM_Contact_BAO_Contact::importableFields('Individual', null );
            require_once 'CRM/Core/DAO/DupeMatch.php';
            $dao = & new CRM_Core_DAO_DupeMatch();;
            $dao->find(true);
            $fieldsArray = explode('AND',$dao->rule);
            $tmpConatctField = array();
            if( is_array($fieldsArray) ) {
                foreach ( $fieldsArray as $value) {
                    $tmpConatctField[trim($value)] = $contactFields[trim($value)];
                    $tmpConatctField[trim($value)]['title'] = $tmpConatctField[trim($value)]['title']." (match to contact)" ;
                }
            }
            $tmpConatctField['external_identifier'] = $contactFields['external_identifier'];
            $tmpConatctField['external_identifier']['title'] = $contactFields['external_identifier']['title'] . " (match to contact)";
            $fields = array_merge($fields, $tmpConatctField);
            $fields = array_merge($fields, $tmpFields);
            $fields = array_merge($fields, CRM_Core_BAO_CustomField::getFieldsForImport('Activity'));
            self::$_importableFields = $fields;
        }
        return self::$_importableFields;
    }

 /**
     * To get the Activities of a target contact
     *
     * @param $contactId    Integer  ContactId of the contact whose activities
     *                               need to find
     * 
     * @return array    array of activity fields
     * @access public
     */
    
    function getContactActivity( $contactId )
    {
        $query ="SELECT * FROM civicrm_activity activity, civicrm_activity_target target
                 WHERE activity.id = target.activity_id
                 AND target.target_contact_id =$contactId";
                
        $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        $activities = array();
        while( $dao->fetch( ) ){
            $activities[$dao->activity_id]['source_contact_id'] = $dao->source_contact_id;
            $activities[$dao->activity_id]['activity_type_id']  = $dao->activity_type_id;
            $activities[$dao->activity_id]['subject']           = $dao->subject;
            $activities[$dao->activity_id]['location']          = $dao->location;
            $activities[$dao->activity_id]['activity_date_time']= $dao->activity_date_time;
            $activities[$dao->activity_id]['details']           = $dao->details;
            $activities[$dao->activity_id]['status_id']         = $dao->status_id;
            $activities[$dao->activity_id]['activity_name']     = CRM_Core_OptionGroup::getLabel('activity_type',
                                                                                                 $dao->activity_type_id );
            $activities[$dao->activity_id]['status']            = CRM_Core_OptionGroup::getLabel('activity_status',
                                                                                                 $dao->status_id );
        }
        return $activities;
    }
}


