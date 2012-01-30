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

require_once 'CRM/Report/Form.php';

class CRM_Report_Form_Mailing_Detail extends CRM_Report_Form {

    protected $_exposeContactID = false;

    function __construct( ) {
        $this->_columns = array(); 
		
		$this->_columns['civicrm_contact'] = 
            array(
                  'dao' => 'CRM_Contact_DAO_Contact',
                  'fields' => 
                  array(
                        'id' => 
                        array('name'  => 'id', 
                              'title' => ts('Contact ID'),
                              'required'   => true,
                              'no_display' => true,
                               ), 						
                        'sort_name' => 
                        array( 
                              'title' => ts( 'Contact Name' ),
                              'required'  => true, 
                               ),
                        ),
                  'filters' => 
                  array( 
                        'sort_name' => 
                        array( 
                              'title' => ts( 'Contact Name' )
                               ),
                        'id'=> 
                        array( 
                              'title'=> ts( 'Contact ID' ),
                              'no_display' => true ,
                               ), 
                         ),
                  'order_bys'  =>
                  array( 'sort_name' =>
                         array( 'title' => ts( 'Contact Name'), 
                                'default_order' => 'ASC') ),
                  'grouping'  => 'contact-fields',		
                  );
		
		$this->_columns['civicrm_mailing'] = 
            array(
                  'dao' => 'CRM_Mailing_DAO_Mailing',
                  'fields' => 
                  array(
                        'mailing_name' => 
                        array('name' => 'name',
                              'title' => ts('Mailing'),
                              'default' => true,
                              ),
                        ),
                  'filters' => 
                  array(
                        'mailing_name' => 
                        array(
                              'name' => 'name',
                              'title' => ts('Mailing'),
                              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                              'type'         => CRM_Utils_Type::T_STRING,
                              'options'  => self::mailingList( ),
                              ),					
                        ),
                  'order_bys'  =>
                  array( 'mailing_name' =>
                         array( 'name' => 'name',
                                'title' => ts( 'Mailing') ) ),
                  'grouping'  => 'mailing-fields',
                  );
						
        // adding dao just to have alias
		$this->_columns['civicrm_mailing_event_bounce'] = 
            array(
                  'dao' => 'CRM_Mailing_Event_DAO_Bounce',
                  );

		$this->_columns['civicrm_mailing_event_delivered'] = 
            array(
                  'dao' => 'CRM_Mailing_Event_DAO_Delivered',
                  'fields' => 
                  array(
                        'delivered_id' => 
                        array(
                              'name'  => 'id',
                              'title' => ts('Delivery Status'),
                              'default' => true,
                              ),
                        ),
                  'filters' => 
                  array(
                        'delivered_status' => 
                        array(
                              'name'  => 'delivered_status',
                              'title' => ts('Delivery Status'),
                              'operatorType' => CRM_Report_Form::OP_SELECT,
                              'type'         => CRM_Utils_Type::T_STRING,
                              'options'      => array( '' => 'Any',
                                                       'successful' => 'Successful',
                                                       'bounced'    => 'Bounced' ),
                              ),
                        ),
                  'grouping'  => 'mailing-fields',		
                  );

		$this->_columns['civicrm_mailing_event_unsubscribe'] = 
            array(
                  'dao'    => 'CRM_Mailing_Event_DAO_Unsubscribe',
                  'fields' => 
                  array(
                        'unsubscribe_id' => 
                        array(
                              'name' => 'id',
                              'title' => ts('Unsubscribe'),
                              'default' => true,
                              ),
                        ),
                  'filters' => 
                  array(
                        'is_unsubscribed' => 
                        array(
                              'name'  => 'id',
                              'title' => ts('Unsubscribed'),
                              'type'      => CRM_Utils_Type::T_INT,
                              'operatorType' => CRM_Report_Form::OP_SELECT,
                              'options'   => array( '' => ts('Any'), '0' => ts('No'), '1' => ts('Yes') ), 
                              'clause'    => 'mailing_event_unsubscribe_civireport.id IS NULL',
                              ),
                        ),
                  'grouping'  => 'mailing-fields',		
                  );

		$this->_columns['civicrm_mailing_event_reply'] = 
            array(
                  'dao'    => 'CRM_Mailing_Event_DAO_Reply',
                  'fields' => 
                  array(
                        'reply_id' => 
                        array(
                              'name' => 'id',
                              'title' => ts('Reply'),
                              ),
                        ),
                  'filters' => 
                  array(
                        'is_replied' => 
                        array(
                              'name' => 'id',
                              'title' => ts('Replied'),
                              'type'      => CRM_Utils_Type::T_INT,
                              'operatorType' => CRM_Report_Form::OP_SELECT,
                              'options'   => array( '' => ts('Any'), '0' => ts('No'), '1' => ts('Yes') ), 
                              'clause'    => 'mailing_event_reply_civireport.id IS NULL',
                              ),
                        ),
                  'grouping'  => 'mailing-fields',		
                  );

		$this->_columns['civicrm_mailing_event_forward'] = 
            array(
                  'dao' => 'CRM_Mailing_Event_DAO_Forward',
                  'fields' => 
                  array(
                        'forward_id' => 
                        array(
                              'name' => 'id',
                              'title' => ts('Forwarded to Email'),
                              ),
                        ),
                  'filters' => 
                  array(
                        'is_forwarded' => 
                        array(
                              'name' => 'id',
                              'title' => ts('Forwarded'),
                              'type'      => CRM_Utils_Type::T_INT,
                              'operatorType' => CRM_Report_Form::OP_SELECT,
                              'options'   => array( '' => ts('Any'), '0' => ts('No'), '1' => ts('Yes') ), 
                              'clause'    => 'mailing_event_forward_civireport.id IS NULL',
                              ),
                        ),
                  'grouping'  => 'mailing-fields',		
                  );

		$this->_columns['civicrm_email']  = 
            array( 
                  'dao'=> 'CRM_Core_DAO_Email',
                  'fields'=> 
                  array( 
                        'email' => 
                        array( 
                              'title' => ts( 'Email' ),
                              //'default'  => true,
                              'required' => true,
                               ),
                        'on_hold' => 
                        array( 
                              'title'    => ts( 'Opt-out' ),
                              'default'  => true,
                              'grouping' => 'mailing-fields', 
                               ),
                         ),
                  'filters' => 
                  array( 
                        'on_hold' => 
                        array( 
                              'title'  => ts( 'Opted-out' ),
                              'type'      => CRM_Utils_Type::T_INT,
                              'operatorType' => CRM_Report_Form::OP_SELECT,
                              'options'   => array( '' => ts('Any'), '0' => ts('No'), '1' => ts('Yes') ), 
                               ),
                         ), 
                  'grouping'  => 'contact-fields', 
                   );
        
        $this->_columns['civicrm_phone'] = 
            array( 
                  'dao' => 'CRM_Core_DAO_Phone',
                  'fields' => array( 'phone' => null),
                  'grouping'  => 'contact-fields',
                   );
		
		$this->_columns['civicrm_group'] = 
            array( 
                  'dao'    => 'CRM_Contact_DAO_Group',
                  'alias'  => 'cgroup',
                  'filters' => 
                  array( 
                        'gid' => 
                        array( 
                              'name'    => 'group_id',
                              'title'   => ts( 'Group' ),
                              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                              'group'   => true,
                              'options' => CRM_Core_PseudoConstant::group( ), 
                               ), 
                         ), 
                   );
        
        parent::__construct( );
    }
    
    function select( ) {
        $select = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        if ( in_array($fieldName, array('unsubscribe_id', 'forward_id', 'reply_id')) ) {
                            $select[] = "IF({$field['dbAlias']} IS NULL, 'No', 'Yes') as {$tableName}_{$fieldName}";
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = 
                                CRM_Utils_Array::value( 'type', $field );
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['no_display'] = 
                                CRM_Utils_Array::value( 'no_display', $field );
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = 
                                CRM_Utils_Array::value( 'title', $field );
                            unset($this->_columns[$tableName]['fields'][$fieldName]);

                        } else if ( $fieldName == 'delivered_id' ) {
                            $select[] = "IF(mailing_event_delivered_civireport.id IS NOT NULL, 'Successful', IF(mailing_event_bounce_civireport.id IS NOT NULL, 'Bounced ', 'Unknown')) as {$tableName}_{$fieldName}";
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = 
                                CRM_Utils_Array::value( 'type', $field );
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['no_display'] = 
                                CRM_Utils_Array::value( 'no_display', $field );
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = 
                                CRM_Utils_Array::value( 'title', $field );
                            unset($this->_columns[$tableName]['fields'][$fieldName]);
                        }
                    }
                }
            }
        }

        parent::select();
        if ( !empty($select) ) {
            $this->_select .= ', ' . implode( ', ', $select ) . " ";
        }

        // simple sort
        ksort( $this->_columnHeaders );
    }

    function from( ) {
        $this->_from = "
        FROM civicrm_contact {$this->_aliases['civicrm_contact']}";
        
        $this->_from .= "
				INNER JOIN civicrm_mailing_event_queue
					ON civicrm_mailing_event_queue.contact_id = {$this->_aliases['civicrm_contact']}.id
				INNER JOIN civicrm_email {$this->_aliases['civicrm_email']}
					ON civicrm_mailing_event_queue.email_id = {$this->_aliases['civicrm_email']}.id";

        if ( array_key_exists( 'delivered_id', $this->_params['fields'] ) ) {
            $this->_from .= "
                LEFT JOIN  civicrm_mailing_event_delivered {$this->_aliases['civicrm_mailing_event_delivered']}
                    ON  {$this->_aliases['civicrm_mailing_event_delivered']}.event_queue_id = civicrm_mailing_event_queue.id
				LEFT JOIN civicrm_mailing_event_bounce {$this->_aliases['civicrm_mailing_event_bounce']}
					ON {$this->_aliases['civicrm_mailing_event_bounce']}.event_queue_id = civicrm_mailing_event_queue.id";
            if ( $this->_params['delivered_status_value'] == 'bounced' ) {
                $this->_columns['civicrm_mailing_event_delivered']['filters']['delivered_status']['clause'] = 
                    "{$this->_aliases['civicrm_mailing_event_bounce']}.id IS NOT NULL";
            } else if ( $this->_params['delivered_status_value'] == 'successful' ) {
                $this->_columns['civicrm_mailing_event_delivered']['filters']['delivered_status']['clause'] = 
                    "{$this->_aliases['civicrm_mailing_event_delivered']}.id IS NOT NULL";
            }
        } else {
            unset($this->_columns['civicrm_mailing_event_delivered']['filters']['delivered_status']);
        }

        if ( array_key_exists( 'reply_id', $this->_params['fields'] ) ) {
            if ( $this->_params['is_replied_value'] == 1 ) {
                $joinType = 'INNER';
                unset($this->_columns['civicrm_mailing_event_reply']['filters']['is_replied']);
            } else {
                $joinType = 'LEFT';
            }
            $this->_from .= "
                {$joinType} JOIN  civicrm_mailing_event_reply {$this->_aliases['civicrm_mailing_event_reply']}
                    ON  {$this->_aliases['civicrm_mailing_event_reply']}.event_queue_id = civicrm_mailing_event_queue.id";
        } else {
            unset($this->_columns['civicrm_mailing_event_reply']['filters']['is_replied']);
        }

        if ( array_key_exists( 'unsubscribe_id', $this->_params['fields'] ) ) {
            if ( $this->_params['is_unsubscribed_value'] == 1 ) {
                $joinType = 'INNER';
                unset($this->_columns['civicrm_mailing_event_unsubscribe']['filters']['is_unsubscribed']);
            } else {
                $joinType = 'LEFT';
            }
            $this->_from .= "
                {$joinType} JOIN  civicrm_mailing_event_unsubscribe {$this->_aliases['civicrm_mailing_event_unsubscribe']}
                    ON  {$this->_aliases['civicrm_mailing_event_unsubscribe']}.event_queue_id = civicrm_mailing_event_queue.id";
        } else {
            unset($this->_columns['civicrm_mailing_event_unsubscribe']['filters']['is_unsubscribed']);
        }

        if ( array_key_exists( 'forward_id', $this->_params['fields'] ) ) {
            if ( $this->_params['is_forwarded_value'] == 1 ) {
                $joinType = 'INNER';
                unset($this->_columns['civicrm_mailing_event_forward']['filters']['is_forwarded']);
            } else {
                $joinType = 'LEFT';
            }
            $this->_from .= "
                {$joinType} JOIN  civicrm_mailing_event_forward {$this->_aliases['civicrm_mailing_event_forward']}
                    ON  {$this->_aliases['civicrm_mailing_event_forward']}.event_queue_id = civicrm_mailing_event_queue.id";
        } else {
            unset($this->_columns['civicrm_mailing_event_forward']['filters']['is_forwarded']);
        }

        $this->_from .= "
				INNER JOIN civicrm_mailing_job
					ON civicrm_mailing_event_queue.job_id = civicrm_mailing_job.id
				INNER JOIN civicrm_mailing {$this->_aliases['civicrm_mailing']}
					ON civicrm_mailing_job.mailing_id = {$this->_aliases['civicrm_mailing']}.id
					AND civicrm_mailing_job.is_test = 0";

        if ( $this->_phoneField ) {
            $this->_from .= "
            LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']} 
                   ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND 
                      {$this->_aliases['civicrm_phone']}.is_primary = 1 ";
        }
    }

	function mailingList() {
		require_once('CRM/Mailing/BAO/Mailing.php');
		
		$data = array( );
		$mailing = new CRM_Mailing_BAO_Mailing();
		$query = "SELECT name FROM civicrm_mailing ";
		$mailing->query($query);
		
		while($mailing->fetch()) {
			$data[mysql_real_escape_string($mailing->name)] = $mailing->name;
		}

		return $data;
	}
}