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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

class CRM_Core_Payment_BaseIPN {

    static $_now = null;

    function __construct( ) {
        self::$_now = date( 'YmdHis' );
    }

    function validateData( &$input, &$ids, &$objects, $required = true , $paymentProcessorID = null ) {

        // make sure contact exists and is valid
        $contact = new CRM_Contact_DAO_Contact( );
        $contact->id = $ids['contact'];
        if ( ! $contact->find( true ) ) {
            CRM_Core_Error::debug_log_message( "Could not find contact record: {$ids['contact']}" );
            echo "Failure: Could not find contact record: {$ids['contact']}<p>";
            return false;
        }
        
        // make sure contribution exists and is valid
        $contribution = new CRM_Contribute_DAO_Contribution( );
        $contribution->id = $ids['contribution'];
        if ( ! $contribution->find( true ) ) {
            CRM_Core_Error::debug_log_message( "Could not find contribution record: $contributionID" );
            echo "Failure: Could not find contribution record for $contributionID<p>";
            return false;
        }
        $contribution->receive_date = CRM_Utils_Date::isoToMysql($contribution->receive_date); 

        $objects['contact']          =& $contact;
        $objects['contribution']     =& $contribution;
        if ( ! $this->loadObjects( $input, $ids, $objects, $required, $paymentProcessorID ) ) {
            return false;
        }
        
        return true;
    }

    function createContact( &$input, &$ids, &$objects ) {
        $params    = array( );
        $billingID = $ids['billing'];
        $lookup    = array( 'first_name'                  ,
                            'last_name'                   ,
                            "street_address-{$billingID}" ,
                            "city-{$billingID}"           ,
                            "state-{$billingID}"          ,
                            "postal_code-{$billingID}"    ,
                            "country-{$billingID}"        , );
        foreach ( $lookup as $name ) {
            $params[$name] = $input[$name];
        }
        if ( ! empty( $params ) ) {
            // update contact record
            $contact = CRM_Contact_BAO_Contact::createProfileContact( $params, CRM_Core_DAO::$_nullArray, $ids['contact'] );
        }
        
        return true;
    }
/*
 * Load objects related to contribution
 * 
 * @input array information from Payment processor
 */
  function loadObjects( &$input, &$ids, &$objects, $required, $paymentProcessorID ) {
    $ids['paymentProcessorID'] = $paymentProcessorID;
    $contribution =& $objects['contribution'];
    $success = $contribution->loadRelatedObjects($input, $ids, $required );
    $objects = array_merge($objects,$contribution->_relatedObjects); 
    return $success;
  }

    function failed( &$objects, &$transaction ) {
        $contribution =& $objects['contribution'];
        $memberships   =& $objects['membership']  ;
        if ( is_numeric( $memberships ) ) {
            $memberships = array( $objects['membership'] );     
        }
        $participant  =& $objects['participant'] ;

        $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
        
        $contribution->contribution_status_id = array_search( 'Failed', $contributionStatus );
        $contribution->save( );
        foreach ($memberships as $membership) {
            if ( $membership ) {
                $membership->status_id = 4;
                $membership->save( );
                
                //update related Memberships.
                $params = array( 'status_id' => 4 );
                CRM_Member_BAO_Membership::updateRelatedMemberships( $membership->id, $params );
            }
        }
        if ( $participant ) {
            $participant->status_id = 4;
            $participant->save( );
        }
            
        $transaction->commit( );
        CRM_Core_Error::debug_log_message( "Setting contribution status to failed" );
        //echo "Success: Setting contribution status to failed<p>";
        return true;
    }

    function pending( &$objects, &$transaction ) {
        $transaction->commit( );
        CRM_Core_Error::debug_log_message( "returning since contribution status is pending" );
        echo "Success: Returning since contribution status is pending<p>";
        return true;
    }

    function cancelled( &$objects, &$transaction ) {
        $contribution =& $objects['contribution'];
        $memberships   =& $objects['membership']  ;
        if ( is_numeric( $memberships ) ) {
            $memberships = array( $objects['membership'] );     
        }
        
        $participant  =& $objects['participant'] ;

        $contribution->contribution_status_id = 3;
        $contribution->cancel_date = self::$_now;
        $contribution->cancel_reason = CRM_Utils_Array::value( 'reasonCode', $input );
        $contribution->save( );

        foreach ($memberships as $membership) {
            if ( $membership ) {
                $membership->status_id = 6;
                $membership->save( );
                
                //update related Memberships.
                $params = array( 'status_id' => 6 );
                CRM_Member_BAO_Membership::updateRelatedMemberships( $membership->id, $params );
            }
        }
        
        if ( $participant ) {
            $participant->status_id = 4;
            $participant->save( );
        }

        $transaction->commit( );
        CRM_Core_Error::debug_log_message( "Setting contribution status to cancelled" );
        //echo "Success: Setting contribution status to cancelled<p>";
        return true;
    }

    function unhandled( &$objects, &$transaction ) {
        $transaction->rollback( );
        // we dont handle this as yet
        CRM_Core_Error::debug_log_message( "returning since contribution status: $status is not handled" );
        echo "Failure: contribution status $status is not handled<p>";
        return false;
    }

    function completeTransaction( &$input, &$ids, &$objects, &$transaction, $recur = false ) {
        $contribution =& $objects['contribution'];
        $memberships  =& $objects['membership'] ;
        if ( is_numeric( $memberships ) ) {
            $memberships = array( $objects['membership'] );     
        }
        $participant  =& $objects['participant'] ;
        $event        =& $objects['event']       ;
        $changeToday  =  CRM_Utils_Array::value( 'trxn_date', $input, self::$_now );
        $recurContrib =& $objects['contributionRecur'];
        
        $values = array( );
        if ( $input['component'] == 'contribute' ) {
            if ( $contribution->contribution_page_id ) {
                CRM_Contribute_BAO_ContributionPage::setValues( $contribution->contribution_page_id, $values ); 
                $source = ts( 'Online Contribution' ) . ': ' . $values['title'];
            } else if ( $recurContrib->id ) {
                $contribution->contribution_page_id = null;
                $values['amount'] = $recurContrib->amount;
                $values['contribution_type_id'] = $objects['contributionType']->id;
                $values['title'] = $source = ts( 'Offline Recurring Contribution' );
                $values['is_email_receipt'] = $recurContrib->is_email_receipt;
                $domainValues = CRM_Core_BAO_Domain::getNameAndEmail( );
                $values['receipt_from_name'] = $domainValues[0];
                $values['receipt_from_email'] = $domainValues[1];
            }
            $contribution->source = $source;  
            if ( CRM_Utils_Array::value( 'is_email_receipt', $values ) ) {
                $contribution->receipt_date = self::$_now;
            }
            if ( !empty( $memberships ) ) {
                foreach ($memberships as $membership) {
                    if ( $membership ) {
                        $format       = '%Y%m%d';
                        
                        $currentMembership =  CRM_Member_BAO_Membership::getContactMembership( $membership->contact_id, 
                                                                                               $membership->membership_type_id, 
                                                                                               $membership->is_test, $membership->id );
                        
                        // CRM-8141 update the membership type with the value recorded in log when membership created/renewed
                        // this picks up membership type changes during renewals
                        $sql = "
SELECT    membership_type_id 
FROM      civicrm_membership_log 
WHERE     membership_id=$membership->id 
ORDER BY  id DESC 
LIMIT 1;";
                        $dao = new CRM_Core_DAO;
                        $dao->query( $sql );
                        if ( $dao->fetch( ) ) {
                            if ( ! empty( $dao->membership_type_id ) ) {
                                $membership->membership_type_id = $dao->membership_type_id;
                                $membership->save( );
                            } // else fall back to using current membership type
                        } // else fall back to using current membership type
                        $dao->free();
                        
                        if ( $currentMembership ) {
                            /*
                             * Fixed FOR CRM-4433
                             * In BAO/Membership.php(renewMembership function), we skip the extend membership date and status 
                             * when Contribution mode is notify and membership is for renewal ) 
                             */
                            CRM_Member_BAO_Membership::fixMembershipStatusBeforeRenew( $currentMembership, $changeToday );
                            
                            $dates = CRM_Member_BAO_MembershipType::getRenewalDatesForMembershipType( $membership->id , 
                                                                                                      $changeToday );
                            $dates['join_date'] =  CRM_Utils_Date::customFormat($currentMembership['join_date'], $format );
                        } else {
                            $dates = CRM_Member_BAO_MembershipType::getDatesForMembershipType($membership->membership_type_id);
                        }
                        
                        //get the status for membership.
                        $calcStatus = CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate( $dates['start_date'], 
                                                                                                  $dates['end_date'], 
                                                                                                  $dates['join_date'],
                                                                                                  'today', 
                                                                                                  true );
                        
                        $formatedParams = array( 'status_id'     => CRM_Utils_Array::value( 'id', $calcStatus, 2 ),
                                                 'join_date'     => CRM_Utils_Date::customFormat( $dates['join_date'],     $format ),
                                                 'start_date'    => CRM_Utils_Date::customFormat( $dates['start_date'],    $format ),
                                                 'end_date'      => CRM_Utils_Date::customFormat( $dates['end_date'],      $format ),
                                                 'reminder_date' => CRM_Utils_Date::customFormat( $dates['reminder_date'], $format ) );
                        //we might be renewing membership, 
                        //so make status override false.  
                        $formatedParams['is_override'] = false;
                        $membership->copyValues( $formatedParams );
                        $membership->save( );
                        
                        //updating the membership log
                        $membershipLog = array();
                        $membershipLog = $formatedParams;
                        
                        $logStartDate  = $formatedParams['start_date'];
                        if ( CRM_Utils_Array::value( 'log_start_date', $dates ) ) {
                            $logStartDate = CRM_Utils_Date::customFormat( $dates['log_start_date'], $format ); 
                            $logStartDate = CRM_Utils_Date::isoToMysql( $logStartDate );
                        }
                        
                        $membershipLog['start_date']    = $logStartDate;
                        $membershipLog['membership_id'] = $membership->id;
                        $membershipLog['modified_id']   = $membership->contact_id;
                        $membershipLog['modified_date'] = date('Ymd');
                        $membershipLog['membership_type_id'] = $membership->membership_type_id;
                        
                        CRM_Member_BAO_MembershipLog::add( $membershipLog, CRM_Core_DAO::$_nullArray);
                        
                        //update related Memberships.              
                        CRM_Member_BAO_Membership::updateRelatedMemberships( $membership->id, $formatedParams );
                    }
                }
            }
        } else {
            // event
            $eventParams     = array( 'id' => $objects['event']->id );
            $values['event'] = array( );

            CRM_Event_BAO_Event::retrieve( $eventParams, $values['event'] );
        
            $eventParams = array( 'id' => $objects['event']->id );
            $values['event'] = array( );

            CRM_Event_BAO_Event::retrieve( $eventParams, $values['event'] );

            //get location details
            $locationParams = array( 'entity_id' => $objects['event']->id, 'entity_table' => 'civicrm_event' );
            $values['location'] = CRM_Core_BAO_Location::getValues($locationParams);

            $ufJoinParams = array( 'entity_table' => 'civicrm_event',
                                   'entity_id'    => $ids['event'],
                                   'weight'       => 1 );
        
            $values['custom_pre_id'] = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams );
        
            $ufJoinParams['weight'] = 2;
            $values['custom_post_id'] = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams );

            $contribution->source                  = ts( 'Online Event Registration' ) . ': ' . $values['event']['title'];

            if ( $values['event']['is_email_confirm'] ) {
                $contribution->receipt_date = self::$_now;
                $values['is_email_receipt'] = 1;
            }

            $participant->status_id = 1;
            $participant->save( );
        }

        if ( CRM_Utils_Array::value( 'net_amount', $input, 0 ) == 0 && 
             CRM_Utils_Array::value( 'fee_amount', $input, 0 ) != 0 ) {
            $input['net_amount'] = $input['amount'] - $input['fee_amount'];
        }

        $contribution->contribution_status_id  = 1;
        $contribution->is_test       = $input['is_test'];
        $contribution->fee_amount    = CRM_Utils_Array::value( 'fee_amount', $input, 0 );
        $contribution->net_amount    = CRM_Utils_Array::value( 'net_amount', $input, 0 );
        $contribution->trxn_id       = $input['trxn_id'];
        $contribution->receive_date  = CRM_Utils_Date::isoToMysql($contribution->receive_date);
        $contribution->thankyou_date = CRM_Utils_Date::isoToMysql($contribution->thankyou_date);
        $contribution->cancel_date   = 'null';
        
        if ( CRM_Utils_Array::value('check_number', $input) ) {
            $contribution->check_number = $input['check_number'];
        }
       
        if ( CRM_Utils_Array::value('payment_instrument_id', $input) ) {
            $contribution->payment_instrument_id = $input['payment_instrument_id'];
        }
        
        $contribution->save( );
        
        // next create the transaction record
        $paymentProcessor = '';
        if ( isset( $objects['paymentProcessor'] ) ) {
            if ( is_array( $objects['paymentProcessor'] ) ) {
                $paymentProcessor = $objects['paymentProcessor']['payment_processor_type'];    
            } else {
                $paymentProcessor = $objects['paymentProcessor']->payment_processor_type;    
            }
        }

        if ( $contribution->trxn_id ) {
            
            $trxnParams = array(
                                'contribution_id'   => $contribution->id,
                                'trxn_date'         => isset( $input['trxn_date'] ) ? $input['trxn_date'] : self::$_now,
                                'trxn_type'         => 'Debit',
                                'total_amount'      => $input['amount'],
                                'fee_amount'        => $contribution->fee_amount,
                                'net_amount'        => $contribution->net_amount,
                                'currency'          => $contribution->currency,
                                'payment_processor' => $paymentProcessor,
                                'trxn_id'           => $contribution->trxn_id,
                                );
            
            $trxn = CRM_Core_BAO_FinancialTrxn::create( $trxnParams );
        }
        
        self::updateRecurLinkedPledge( $contribution);

        // create an activity record
        if ( $input['component'] == 'contribute' ) {
            //CRM-4027
            $targetContactID = null;
            if ( CRM_Utils_Array::value( 'related_contact', $ids ) ) {
                $targetContactID = $contribution->contact_id;
                $contribution->contact_id = $ids['related_contact']; 
            }
            CRM_Activity_BAO_Activity::addActivity( $contribution, null, $targetContactID );
        } else { // event 
            CRM_Activity_BAO_Activity::addActivity( $participant );
        }
       
        CRM_Core_Error::debug_log_message( "Contribution record updated successfully" );
        $transaction->commit( );
        
        // CRM-9132 legacy behaviour was that receipts were sent out in all instances. Still sending
        // when array_key 'is_email_receipt doesn't exist in case some instances where is needs setting haven't been set 
        if( !array_key_exists('is_email_receipt', $values) || 
            $values['is_email_receipt'] == 1 ) {
          self::sendMail( $input, $ids, $objects, $values, $recur, false );
        }

        CRM_Core_Error::debug_log_message( "Success: Database updated and mail sent" );
    }
    
    function getBillingID( &$ids ) {
        // get the billing location type
        $locationTypes  = CRM_Core_PseudoConstant::locationType( );
        // CRM-8108 remove the ts around the Billing locationtype
        //$ids['billing'] =  array_search( ts('Billing'),  $locationTypes );
        $ids['billing'] =  array_search( 'Billing',  $locationTypes );
        if ( ! $ids['billing'] ) {
            CRM_Core_Error::debug_log_message( ts( 'Please set a location type of %1', array( 1 => 'Billing' ) ) );
            echo "Failure: Could not find billing location type<p>";
            return false;
        }
        return true;
    }

    /*
     * Send receipt from contribution. Note that the compose message part has been moved to contribution
     * You should call loadRelatedObjects before this to get the objects
     */
    function sendMail( &$input, &$ids, &$objects, &$values, $recur = false, $returnMessageText = false ) {
      $contribution =& $objects['contribution'];
      // set receipt from e-mail and name in value
      if ( !$returnMessageText ) {
        $session  = CRM_Core_Session::singleton( );
        $userID   = $session->get( 'userID' );
        list( $userName, $userEmail ) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $userID );
        $values['receipt_from_email'] = $userEmail;
        $values['receipt_from_name']  = $userName;
     }
     return $returnMessageText = $contribution->composeMessageArray($input, $ids, $objects,$values, $recur, $returnMessageText );
   }
}
