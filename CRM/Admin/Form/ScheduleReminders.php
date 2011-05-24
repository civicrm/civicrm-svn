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

        $this->add( 'text', 'title', ts( 'Title' ) );

        require_once 'CRM/Core/BAO/ScheduleReminders.php';
        list( $sel1, $sel2, $sel3 ) = CRM_Core_BAO_ScheduleReminders::getSelection(  ) ;
        
        $sel =& $this->add('hierselect',
                           'entity',
                           ts('Entity'),
                           array(
                                 'name'    => 'entity[0]',
                                 'style'   => 'vertical-align: top;'),
                           true);
        $sel->setOptions( array( $sel1, $sel2 ) );

        if ( is_a($sel->_elements[1], 'HTML_QuickForm_select') ) {
            // make second selector a multi-select -
            $sel->_elements[1]->setMultiple(true);
            $sel->_elements[1]->setSize(5);
        }

        //get the frequency units.
        require_once 'CRM/Core/OptionGroup.php';
        $this->_freqUnits = CRM_Core_OptionGroup::values('recur_frequency_units');
        
        require_once 'CRM/Core/BAO/ScheduleReminders.php';
        $mappings = CRM_Core_BAO_ScheduleReminders::getMapping(  );

        $numericOptions = array( 1 => ts('1'), 2 => ts('2'), 3 => ts('3'), 4 => ts('4'), 5 => ts('5' ),
                                 6 => ts('6'), 7 => ts('7'), 8 => ts('8'), 9 => ts('9'), 10 => ts('10') );
        $this->add( 'select', 'reminder_interval', ts('When'), $numericOptions );
        
        foreach ($this->_freqUnits as $val => $label) {
            $freqUnitsDisplay[$val] = ts('%1(s)', array(1 => $label));
        }
        $this->add( 'select', 'reminder_frequency', ts( 'Frequency' ), $freqUnitsDisplay, true );

        $condition =  array( 'before' => ts('before'), 
                             'after'  => ts('after') );
        $this->add( 'select', 'action_condition', ts( 'Action Condition' ), $condition, true );
                
        require_once 'CRM/Core/OptionGroup.php';
        $this->addElement( 'checkbox', 'is_repeat', ts('Repeat') , 
                           null, array('onclick' => "return showHideByValue('is_repeat',true,'repeatFields','table-row','radio',false);") );

        $this->add( 'select', 'repetition_start_frequency_unit', ts( 'every' ), $freqUnitsDisplay );
        $this->add( 'select', 'repetition_start_frequency_interval', ts( 'every' ), $numericOptions );
        $this->add( 'select', 'repetition_end_frequency_unit', ts( 'until' ), $freqUnitsDisplay );
        $this->add( 'select', 'repetition_end_frequency_interval', ts( 'until' ), $numericOptions );
        $this->add( 'select', 'repetition_end_action', ts( 'Repetition Condition' ), $condition, true );
        
        require_once 'CRM/Mailing/BAO/Mailing.php';
        CRM_Mailing_BAO_Mailing::commonCompose( $this );

        $this->add('text', 'subject', ts('Subject'), 
                   CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_ActionSchedule', 'subject' ), true);

        $this->add('checkbox', 'is_active', ts('Send email'));
        $this->add('checkbox', 'record_activity', ts('Logging'));

    }

    function setDefaultValues( )
    {
        
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
    }

}
