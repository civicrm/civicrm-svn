<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (C) 2011 Marty Wright                                    |
 | Licensed to CiviCRM under the Academic Free License version 3.0.   |
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

require_once 'CRM/Admin/Form.php';

/**
 * This class generates form components for Scheduling Reminders
 * 
 */
class CRM_Admin_Form_ScheduleReminders extends CRM_Admin_Form
{
    /**
     * Scheduled Reminder ID
     */
    protected $_id     = null;

    public $_freqUnits;

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        parent::buildQuickForm( );

        if ( $this->_action & (CRM_Core_Action::DELETE ) ) { 
            $reminderName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_ActionSchedule', $this->_id, 'title' );
            $this->assign('reminderName', $reminderName);
            return;
        }
        
        $this->add( 'text', 'title', ts( 'Title' ) );

        require_once 'CRM/Core/BAO/ScheduleReminders.php';
        list( $sel1, $sel2, $sel3, $sel4, $sel5 ) = CRM_Core_BAO_ScheduleReminders::getSelection(  ) ;
        
        $sel =& $this->add('hierselect',
                           'entity',
                           ts('Entity'),
                           array(
                                 'name'    => 'entity[0]',
                                 'style'   => 'vertical-align: top;'),
                           true);
        $sel->setOptions( array( $sel1, $sel2, $sel3 ) );

        if ( is_a($sel->_elements[1], 'HTML_QuickForm_select') ) {
            // make second selector a multi-select -
            $sel->_elements[1]->setMultiple(true);
            $sel->_elements[1]->setSize(5);
        }

        if ( is_a($sel->_elements[2], 'HTML_QuickForm_select') ) {
            // make third selector a multi-select -
            $sel->_elements[2]->setMultiple(true);
            $sel->_elements[2]->setSize(5);
        }

        //get the frequency units.
        require_once 'CRM/Core/OptionGroup.php';
        $this->_freqUnits = CRM_Core_OptionGroup::values('recur_frequency_units');
        
        require_once 'CRM/Core/BAO/ScheduleReminders.php';
        $mappings = CRM_Core_BAO_ScheduleReminders::getMapping(  );

        $numericOptions = array( 1 => ts('1'), 2 => ts('2'), 3 => ts('3'), 4 => ts('4'), 5 => ts('5' ),
                                 6 => ts('6'), 7 => ts('7'), 8 => ts('8'), 9 => ts('9'), 10 => ts('10') );
        //reminder_interval
        $this->add( 'select', 'first_action_offset', ts('When'), $numericOptions );
        
        foreach ($this->_freqUnits as $val => $label) {
            $freqUnitsDisplay[$val] = ts('%1(s)', array(1 => $label));
        }
        //reminder_frequency
        $this->add( 'select', 'first_action_unit', ts( 'Frequency' ), $freqUnitsDisplay, true );

        $condition =  array( 'before' => ts('before'), 
                             'after'  => ts('after') );
        //reminder_action
        $this->add( 'select', 'first_action_condition', ts( 'Action Condition' ), $condition );
                
        $this->add( 'select', 'entity_date', ts( 'Date Field' ), $sel4, true );

        require_once 'CRM/Core/OptionGroup.php';
        $this->addElement( 'checkbox', 'is_repeat', ts('Repeat') , 
                           null, array('onclick' => "return showHideByValue('is_repeat',true,'repeatFields','table-row','radio',false);") );

        $this->add( 'select', 'repetition_start_frequency_unit', ts( 'every' ), $freqUnitsDisplay );
        $this->add( 'select', 'repetition_start_frequency_interval', ts( 'every' ), $numericOptions );
        $this->add( 'select', 'repetition_end_frequency_unit', ts( 'until' ), $freqUnitsDisplay );
        $this->add( 'select', 'repetition_end_frequency_interval', ts( 'until' ), $numericOptions );
        $this->add( 'select', 'repetition_end_action', ts( 'Repetition Condition' ), $condition, true );
       
        $this->add( 'select', 'recipient', ts( 'Recipient' ), $sel5['activity_contacts'],
                    false, array( 'onChange' => "return showHideByValue('recipient','0','recipientManual','table-row','select',false);") 
                    );
        
        //autocomplete url
        $dataUrl = CRM_Utils_System::url( "civicrm/ajax/rest",
                                          "className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=activity&reset=1",
                                          false, null, false );

        $this->assign( 'dataUrl',$dataUrl );
        //tokeninput url
        $tokenUrl = CRM_Utils_System::url( "civicrm/ajax/checkemail",
                                           "noemail=1",
                                           false, null, false );
        $this->assign( 'tokenUrl', $tokenUrl );
        $this->add( 'text', 'recipient_manual_id', ts('Manual Recipients') );

        require_once 'CRM/Mailing/BAO/Mailing.php';
        CRM_Mailing_BAO_Mailing::commonCompose( $this );

        $this->add('text', 'subject', ts('Subject'), 
                   CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_ActionSchedule', 'subject' ), true);

        $this->add('checkbox', 'is_active', ts('Send email'));
        $this->add('checkbox', 'record_activity', ts('Logging'));

    }

    function setDefaultValues( )
    {
        if ( $this->_action & CRM_Core_Action::ADD ) {
            $defaults['is_active'] = 1;
            $defaults['record_activity'] = 1;
        } else {
            $defaults = $this->_values;
            $entityValue = explode( CRM_Core_DAO::VALUE_SEPARATOR, $defaults['entity_value'] );
            $entityStatus = explode( CRM_Core_DAO::VALUE_SEPARATOR, $defaults['entity_status'] );
            $defaults['entity'][1] = $entityValue;
            $defaults['entity'][2] = $entityStatus;
        }  

        return $defaults;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            // delete reminder
            CRM_Core_BAO_ScheduleReminders::del( $this->_id );
            CRM_Core_Session::setStatus( ts('Selected Reminder has been deleted.') );
            return;
        }
        $values = $this->controller->exportValues( $this->getName() );

        $keys = array('title', 'first_action_offset' ,'first_action_unit',
                      'first_action_condition', 'is_repeat',
                      'repetition_start_frequency_unit',
                      'repetition_start_frequency_interval',
                      'repetition_end_frequency_unit',
                      'repetition_end_frequency_interval',
                      'repetition_end_action',
                      'subject',
                      );
        
        foreach ( $keys as $key ) {
            $params[$key] = CRM_Utils_Array::value( $key, $values );
        }
        
        $params['body_text'] = CRM_Utils_Array::value( 'text_message', $values );
        $params['body_html'] = CRM_Utils_Array::value( 'html_message', $values );

        if ( CRM_Utils_Array::value( 'recipient', $values ) == 0 ) {
            $params['recipient_manual'] = CRM_Utils_Array::value( 'recipient_manual_id', $values );
        } else {
            $params['recipient'] = CRM_Utils_Array::value( 'recipient', $values );
        }

        $params['mapping_id'] = $values['entity'][0];
        $entity_value  = $values['entity'][1];
        $entity_status = $values['entity'][2];

        foreach ( array('entity_value', 'entity_status') as $key ) {
            $params[$key] = implode( CRM_Core_DAO::VALUE_SEPARATOR, $$key );
        }

        $params['is_active' ] =  CRM_Utils_Array::value( 'is_active', $values, 0 );
        $params['record_activity'] = CRM_Utils_Array::value( 'record_activity', $values, 0 );

        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $params['id' ] = $this->_id;
        }
        CRM_Core_BAO_ScheduleReminders::add($params, $ids);

        $status = ts( "Your new Reminder titled <strong>{$values['title']}</strong> has been saved." );
        if ( $this->_action & CRM_Core_Action::UPDATE ) { 
            $status = ts( "Your Reminder titled <strong>{$values['title']}</strong> has been updated." );
        }
        CRM_Core_Session::setStatus( $status );

    }

}
