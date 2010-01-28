<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for Participant
 * 
 */
class CRM_Event_Form_ParticipantView extends CRM_Core_Form
{
    /**  
     * Function to set variables up before form is built  
     *                                                            
     * @return void  
     * @access public  
     */
    public function preProcess( ) 
    {
        require_once 'CRM/Event/BAO/Participant.php';
        $values = $ids = array( ); 
        $params = array( 'id' => $this->get( 'id' ) );

        CRM_Event_BAO_Participant::getValues( $params, 
                                              $values, 
                                              $ids );
                                              
        if (empty($values)) {
            require_once 'CRM/Core/Error.php';
            CRM_Core_Error::statusBounce(ts('The requested participant record does not exist (possibly the record was deleted).'));
        }
        
        CRM_Event_BAO_Participant::resolveDefaults( $values[$params['id']] );
        
        if ( CRM_Utils_Array::value( 'fee_level', $values[$params['id']] ) ) {
            CRM_Event_BAO_Participant::fixEventLevel( $values[$params['id']]['fee_level'] );
        }
        
        if( $values[$params['id']]['is_test'] ) {
            $values[$params['id']]['status'] .= ' (test) ';
        }
        
        // Get Note
        $noteValue = CRM_Core_BAO_Note::getNote( $values[$params['id']]['id'], 'civicrm_participant' );
        
        $values[$params['id']]['note'] = array_values( $noteValue );
        
        require_once 'CRM/Price/BAO/LineItem.php';

        // Get Line Items
        $lineItem = CRM_Price_BAO_LineItem::getLineItems( $params['id'] );
        
        if (!CRM_Utils_System::isNull($lineItem)) {
            $values[$params['id']]['lineItem'][] = $lineItem;
        }
        $values[$params['id']]['totalAmount'] = $values[$params['id']]['fee_amount'];
        // get the option value for custom data type 	
        $roleCustomDataTypeID      = CRM_Core_OptionGroup::getValue( 'custom_data_type', 'ParticipantRole', 'name' );
        $eventNameCustomDataTypeID = CRM_Core_OptionGroup::getValue( 'custom_data_type', 'ParticipantEventName', 'name' );
        $eventTypeCustomDataTypeID = CRM_Core_OptionGroup::getValue( 'custom_data_type', 'ParticipantEventType', 'name' );
        
        $roleGroupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Participant', $this, $params['id'], null, 
                                                             $values[$params['id']]['role_id'], $roleCustomDataTypeID );
        
        $eventGroupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Participant', $this, $params['id'], null, 
                                                              $values[$params['id']]['event_id'], $eventNameCustomDataTypeID );
        $eventTypeID = CRM_Core_DAO::getFieldValue( "CRM_Event_DAO_Event", 
                                                    $values[$params['id']]['event_id'], 'event_type_id', 'id' );
        $eventTypeGroupTree =& 
            CRM_Core_BAO_CustomGroup::getTree( 'Participant', $this, $params['id'], null, 
                                               $eventTypeID, $eventTypeCustomDataTypeID );

        $groupTree = CRM_Utils_Array::crmArrayMerge( $roleGroupTree, $eventGroupTree );
        $groupTree = CRM_Utils_Array::crmArrayMerge( $groupTree, $eventTypeGroupTree );
        $groupTree = CRM_Utils_Array::crmArrayMerge( $groupTree, CRM_Core_BAO_CustomGroup::getTree( 'Participant', $this, $params['id'] ) );
        
        CRM_Core_BAO_CustomGroup::buildCustomDataView( $this, $groupTree );
        $this->assign( $values[$params['id']] );
        
        // add viewed participant to recent items list
        require_once 'CRM/Utils/Recent.php';
        require_once 'CRM/Contact/BAO/Contact.php';
        $url = CRM_Utils_System::url( 'civicrm/contact/view/participant', 
                                      "action=view&reset=1&id={$values[$params['id']]['id']}&cid={$values[$params['id']]['contact_id']}" );
        
        $participantRoles = CRM_Event_PseudoConstant::participantRole();
        $eventTitle = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event', $values[$params['id']]['event_id'], 'title' );
        $title = CRM_Contact_BAO_Contact::displayName( $values[$params['id']]['contact_id'] ) . ' (' . $participantRoles[$values[$params['id']]['role_id']] . ' - ' . $eventTitle . ')' ;
        
        // add Participant to Recent Items
        CRM_Utils_Recent::add( $title,
                               $url,
                               $values[$params['id']]['id'],
                               'Participant',
                               $values[$params['id']]['contact_id'],
                               null );
        
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->addButtons(array(  
                                array ( 'type'      => 'next',  
                                        'name'      => ts('Done'),  
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                        'isDefault' => true   )
                                )
                          );
    }

}


