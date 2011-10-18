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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Event/Form/ManageEvent.php';
require_once 'CRM/Core/BAO/CustomGroup.php';
require_once 'CRM/Custom/Form/CustomData.php';
require_once 'CRM/Core/BAO/CustomField.php';

/**
 * This class generates form components for processing Event  
 * 
 */
class CRM_Event_Form_ManageEvent_ScheduleReminders extends CRM_Event_Form_ManageEvent
{
    /**
     * Event type
     */
    protected $_eventType = null;

    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( )
    {
        //custom data related code
        $this->_cdType     = CRM_Utils_Array::value( 'type', $_GET );
        $this->assign('cdType', false);
        if ( $this->_cdType ) {
            $this->assign('cdType', true);
            return CRM_Custom_Form_CustomData::preProcess( $this );
        }
        parent::preProcess( );
                
        if ( $this->_id ) {
            $this->assign( 'entityID', $this->_id );
            $eventType = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                      $this->_id,
                                                      'event_type_id' );
        } else {
            $eventType = 'null';
        }
        
        $showLocation = false;
        // when custom data is included in this page
        if ( CRM_Utils_Array::value( 'hidden_custom', $_POST ) ) {
            $this->set('type',     'Event');
            $this->set('subType',  CRM_Utils_Array::value( 'event_type_id', $_POST ) );
            $this->set('entityId', $this->_id );

            CRM_Custom_Form_Customdata::preProcess( $this );
            CRM_Custom_Form_Customdata::buildQuickForm( $this );
            CRM_Custom_Form_Customdata::setDefaultValues( $this );
        }
        
    }
    
    /**
     * This function sets the default values for the form. For edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return None
     */
    function setDefaultValues( )
    {
        return $defaults;
    }
    
    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )  
    { 
        parent::buildQuickForm( );

        $this->add( 'text', 'title', ts( 'Reminder Name' ), 
                    array( 'size'=> 45,'maxlength' => 128 ), true );

        require_once 'CRM/Core/BAO/ActionSchedule.php';
        
        $mappingID = 3;
        list( $sel1, $sel2, $sel3, $sel4, $sel5 ) = CRM_Core_BAO_ActionSchedule::getSelection( $mappingID ) ;
        $this->add( 'select', 'entity', ts('Entity'), $sel3[$mappingID][0] );
        
        //get the frequency units.
        require_once 'CRM/Core/OptionGroup.php';
        $this->_freqUnits = array( 'hour' => 'hour' ) + CRM_Core_OptionGroup::values('recur_frequency_units');
        
        $mappings = CRM_Core_BAO_ActionSchedule::getMapping(  );

        $numericOptions = array( 0 => ts('0'), 1 => ts('1'), 2 => ts('2'), 3 => ts('3'), 4 => ts('4'), 5 => ts('5' ),
                                 6 => ts('6'), 7 => ts('7'), 8 => ts('8'), 9 => ts('9'), 10 => ts('10') );
        //reminder_interval
        $this->add( 'select', 'start_action_offset', ts('When'), $numericOptions );
        
        foreach ($this->_freqUnits as $val => $label) {
            $freqUnitsDisplay[$val] = ts('%1(s)', array(1 => $label));
        }

        $this->addDate( 'absolute_date', ts('Start Date'), false, 
                        array( 'formatType' => 'mailing' ) );

        //reminder_frequency
        $this->add( 'select', 'start_action_unit', ts( 'Frequency' ), $freqUnitsDisplay, true );

        $condition =  array( 'before' => ts('before'), 
                             'after'  => ts('after') );
        //reminder_action
        $this->add( 'select', 'start_action_condition', ts( 'Action Condition' ), $condition );
                
        $this->add( 'select', 'start_action_date', ts( 'Date Field' ), $sel4, true );

        require_once 'CRM/Core/OptionGroup.php';
        $this->addElement( 'checkbox', 'is_repeat', ts('Repeat') , 
                           null, array('onclick' => "return showHideByValue('is_repeat',true,'repeatFields','table-row','radio',false);") );

        $this->add( 'select', 'repetition_frequency_unit', ts( 'every' ), $freqUnitsDisplay );
        $this->add( 'select', 'repetition_frequency_interval', ts( 'every' ), $numericOptions );
        $this->add( 'select', 'end_frequency_unit', ts( 'until' ), $freqUnitsDisplay );
        $this->add( 'select', 'end_frequency_interval', ts( 'until' ), $numericOptions );
        $this->add( 'select', 'end_action', ts( 'Repetition Condition' ), $condition, true );
        $this->add( 'select', 'end_date', ts( 'Date Field' ), $sel4, true );

        $recipient = 'event_contacts';
        $this->add( 'select', 'recipient', ts( 'Recipient(s)' ), $sel5[$recipient],
                    false, array( 'onClick' => "showHideByValue('recipient','manual','recipientManual','table-row','select',false); showHideByValue('recipient','group','recipientGroup','table-row','select',false);") 
                    );
        $recipientListing = $this->add( 'select', 'recipientListing', ts('Recipient Listing'), 
                                        $sel3[$mappingID][0]);
        $recipientListing->setMultiple( true ); 
        
        //autocomplete url
        $dataUrl = CRM_Utils_System::url( 'civicrm/ajax/rest',
                                          "className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=activity&reset=1",
                                          false, null, false );

        $this->assign( 'dataUrl',$dataUrl );
        //tokeninput url
        $tokenUrl = CRM_Utils_System::url( 'civicrm/ajax/checkemail',
                                           'noemail=1',
                                           false, null, false );
        $this->assign( 'tokenUrl', $tokenUrl );
        $this->add( 'text', 'recipient_manual_id', ts('Manual Recipients') );

        $this->addElement( 'select', 'group_id', ts( 'Group' ), 
                           CRM_Core_PseudoConstant::staticGroup( ) );

        require_once 'CRM/Mailing/BAO/Mailing.php';
        CRM_Mailing_BAO_Mailing::commonCompose( $this );

        $this->add('text', 'subject', ts('Subject'), 
                   CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_ActionSchedule', 'subject' ) );

        $this->add('checkbox', 'is_active', ts('Send email'));

        //$this->addFormRule( array( 'CRM_Event_Form_ManageEvent_ScheduleReminders', 'formRule' ) );
    }
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $fields ) 
    {
        $errors = array( );
        if ( $fields['entity'][1][0] == 0 ||
             $fields['entity'][2][0] == 0) {
            $errors['entity'] = ts('Please select appropriate value');
        }
        
        if ( CRM_Utils_Array::value( 'is_active', $fields ) &&  
             CRM_Utils_System::isNull( $fields['subject'] ) ) {
            $errors['subject'] = ts('Subject is a required field.');
        }

        if ( ! empty( $errors ) ) {
            return $errors;
        }

        return empty( $errors ) ? true : $errors;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $values = $this->controller->exportValues( $this->getName() );
        $keys = array('title', 'start_action_offset' ,'start_action_unit',
                      'start_action_condition', 'start_action_date', 
                      'repetition_frequency_unit',
                      'repetition_frequency_interval',
                      'end_frequency_unit',
                      'end_frequency_interval',
                      'end_action', 'end_date',
                      'subject',
                      'absolute_date',
                      'group_id'
                      );
        
        foreach ( $keys as $key ) {
            $params[$key] = CRM_Utils_Array::value( $key, $values );
        }
        
        $params['body_text'] = CRM_Utils_Array::value( 'text_message', $values );
        $params['body_html'] = CRM_Utils_Array::value( 'html_message', $values );

        if ( CRM_Utils_Array::value( 'recipient', $values ) == 'manual' ) {
            $params['recipient_manual'] = CRM_Utils_Array::value( 'recipient_manual_id', $values );
            $params['group_id'] = $params['recipient'] = 'null';
        } else if ( CRM_Utils_Array::value( 'recipient', $values ) == 'group' ) {
            $params['group_id'] = $values['group_id'];
            $params['recipient_manual'] = $params['recipient'] = 'null';
        } else {
            $params['recipient'] = CRM_Utils_Array::value( 'recipient', $values );
            $params['group_id'] = $params['recipient_manual'] = 'null';
        }

        $params['mapping_id'] = $values['entity'][0];
        $entity_value  = $values['entity'][1];
        $entity_status = $values['entity'][2];

        foreach ( array('entity_value', 'entity_status') as $key ) {
            $params[$key] = implode( CRM_Core_DAO::VALUE_SEPARATOR, $$key );
        }

        $params['is_active' ] =  CRM_Utils_Array::value( 'is_active', $values, 0 );
        $params['is_repeat'] = CRM_Utils_Array::value( 'is_repeat', $values, 0 );

        if ( CRM_Utils_Array::value( 'is_repeat', $values ) == 0 ) {
            $params['repetition_frequency_unit'] = 'null';
            $params['repetition_frequency_interval'] = 'null';
            $params['end_frequency_unit'] = 'null';
            $params['end_frequency_interval'] = 'null';
            $params['end_action'] = 'null';
            $params['end_date'] = 'null';
        }
        
        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $params['id' ] = $this->_id;
        } elseif ( $this->_action & CRM_Core_Action::ADD ) {
            // we do this only once, so name never changes
            $params['name']   = CRM_Utils_String::munge($params['title'], '_', 64 );
        } 

        $composeFields = array ( 'template', 'saveTemplate',
                                 'updateTemplate', 'saveTemplateName' );
        $msgTemplate = null;
        //mail template is composed 
        
        foreach ( $composeFields as $key ) {
            if ( CRM_Utils_Array::value( $key, $values ) ) {
                $composeParams[$key] = $values[$key];
            }
        }          
        
        if ( CRM_Utils_Array::value( 'updateTemplate', $composeParams ) ) {
            $templateParams = array( 'msg_text'    => $params['body_text'],
                                     'msg_html'    => $params['body_html'],
                                     'msg_subject' => $params['subject'],
                                     'is_active'   => true
                                     );
            
            $templateParams['id'] = $values['template'];
            
            $msgTemplate = CRM_Core_BAO_MessageTemplates::add( $templateParams );  
        } 
        
        if ( CRM_Utils_Array::value( 'saveTemplate', $composeParams ) ) {
            $templateParams = array( 'msg_text'    => $params['body_text'],
                                     'msg_html'    => $params['body_html'],
                                     'msg_subject' => $params['subject'],
                                     'is_active'   => true
                                     );
            
            $templateParams['msg_title'] = $composeParams['saveTemplateName'];
            
            $msgTemplate = CRM_Core_BAO_MessageTemplates::add( $templateParams );  
        } 
        
        if ( isset($msgTemplate->id ) ) {
            $params['msg_template_id'] = $msgTemplate->id;
        } else {
            $params['msg_template_id'] = CRM_Utils_Array::value( 'template', $values );
        }
        
        CRM_Core_BAO_ActionSchedule::add($params, $ids);

        $status = ts( "Your new Reminder titled %1 has been saved." , array( 1 => "<strong>{$values['title']}</strong>") );
        if ( $this->_action & CRM_Core_Action::UPDATE ) { 
            $status = ts( "Your Reminder titled %1 has been updated." , array( 1 => "<strong>{$values['title']}</strong>") );
        }
        CRM_Core_Session::setStatus( $status );

    }//end of function
    
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) 
    {
        return ts('Event Schedule Reminder');
    }
    
    /* Retrieve event template custom data values 
     * and set as default values for current new event.
     *
     * @params int $tempId event template id.
     *
     * @return $defaults an array of custom data defaults.
     */
    public function templateCustomDataValues( $templateId ) 
    {
        $defaults = array( );
        if ( !$templateId ) {
            return $defaults;  
        }
        
        // pull template custom data as a default for event, CRM-5596
        $groupTree = CRM_Core_BAO_CustomGroup::getTree( $this->_type, $this, $templateId, null, $this->_subType );
        $groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, $this->_groupCount, $this );
        $customValues = array( );
        CRM_Core_BAO_CustomGroup::setDefaults( $groupTree, $customValues );
        foreach ( $customValues as $key => $val ) {
            if ( $fieldKey = CRM_Core_BAO_CustomField::getKeyID( $key ) ) {
                $defaults["custom_{$fieldKey}_-1"] = $val;
            }
        }
        
        return $defaults;
    }
}

