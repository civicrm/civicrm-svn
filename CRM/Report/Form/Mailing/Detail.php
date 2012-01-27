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
                        array( 
                              'title' => ts('Contact ID'),
                              'required'  => true, 
                               ), 						
                        'sort_name' => 
                        array( 
                              'title' => ts( 'Contact Name' )
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
                              'options'  => self::mailing_select( ),
                              ),					
                        ),
                  'order_bys'  =>
                  array( 'mailing_name' =>
                         array( 'name' => 'name',
                                'title' => ts( 'Mailing') ) ),
                  'grouping'  => 'mailing-fields',
                  );
							  
		$this->_columns['civicrm_mailing_event_bounce'] = 
            array(
                  'dao' => 'CRM_Mailing_DAO_Mailing',
                  'fields' => 
                  array(
                        'bounce_reason' => 
                        array(
                              'title' => ts('Bounce Reason'),
                              ),
                        ),
                  'order_bys'  =>
                  array( 'bounce_reason' =>
                         array( 'title' => ts( 'Bounce Reason') ) ),
                  'grouping' => 'mailing-fields' 
                  );
		
		$this->_columns['civicrm_mailing_bounce_type'] = 
            array(
                  'dao' => 'CRM_Mailing_DAO_BounceType',
                  'fields' => 
                  array(
                        'bounce_name' => 
                        array(
                              'name' => 'name',
                              'title' => ts('Bounce Type'),
                              ),
                        ),
                  'filters' => 
                  array(
                        'bounce_type_name' => 
                        array(
                              'name' => 'name',
                              'title' => ts('Bounce Type'),
                              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                              'type'         => CRM_Utils_Type::T_STRING,
                              'options'  => self::bounce_type(),
                              ),
                        ),
                  'order_bys'  =>
                  array( 'bounce_name' =>
                         array( 'name' => 'name',
                                'title' => ts( 'Bounce Type') ) ),
                  'grouping' => 'mailing-fields' 
                  );

		$this->_columns['civicrm_mailing_event_delivered'] = 
            array(
                  'dao' => 'CRM_Mailing_DAO_Mailing',
                  'fields' => 
                  array(
                        'delivered_id' => 
                        array(
                              'name'  => 'delivery_status',
                              'title' => ts('Delivery Status'),
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
                  );

		$this->_columns['civicrm_mailing_event_unsubscribe'] = 
            array(
                  'dao'    => 'CRM_Mailing_DAO_Mailing',
                  'fields' => 
                  array(
                        'unsubscribe_id' => 
                        array(
                              'name' => 'id',
                              'title' => ts('Unsubscribe'),
                              ),
                        ),
                  'filters' => 
                  array(
                        'unsubscribe_status' => 
                        array(
                              'name' => 'unsubscribe_status',
                              'title' => ts('Unsubscribed'),
                              'type'      => CRM_Utils_Type::T_INT,
                              'operatorType' => CRM_Report_Form::OP_SELECT,
                              'options'   => array( '' => ts('Any'), '0' => ts('No'), '1' => ts('Yes') ), 
                              ),
                        ),
                  );

		$this->_columns['civicrm_mailing_event_reply'] = 
            array(
                  'dao'    => 'CRM_Mailing_DAO_Mailing',
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
                              'name' => 'is_replied',
                              'title' => ts('Replied'),
                              'type'      => CRM_Utils_Type::T_INT,
                              'operatorType' => CRM_Report_Form::OP_SELECT,
                              'options'   => array( '' => ts('Any'), '0' => ts('No'), '1' => ts('Yes') ), 
                              ),
                        ),
                  );

		$this->_columns['civicrm_mailing_event_forward'] = 
            array(
                  'dao' => 'CRM_Mailing_DAO_Mailing',
                  'fields' => 
                  array(
                        'forward_id' => 
                        array(
                              'name' => 'id',
                              'title' => ts('Forward'),
                              ),
                        ),
                  'filters' => 
                  array(
                        'is_forwarded' => 
                        array(
                              'name' => 'is_forwarded',
                              'title' => ts('Replied'),
                              'type'      => CRM_Utils_Type::T_INT,
                              'operatorType' => CRM_Report_Form::OP_SELECT,
                              'options'   => array( '' => ts('Any'), '0' => ts('No'), '1' => ts('Yes') ), 
                              ),
                        ),
                  );

		$this->_columns['civicrm_email']  = 
            array( 
                  'dao'=> 'CRM_Core_DAO_Email',
                  'fields'=> 
                  array( 
                        'email' => 
                        array( 
                              'title' => ts( 'Email' ),
                              'no_repeat'  => true,
                              'required' => true,
                               ),
                        'on_hold' => 
                        array( 
                              'title'  => ts( 'Opt-out' ),
                              'default' => true,
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
        $this->_columnHeaders = array();
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        if ( $tableName == 'civicrm_email' ) {
                            $this->_emailField = true;
                        }
						else if ( $tableName == 'civicrm_phone') {
							$this->_phoneField = true;
						}

                        $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['no_display'] = CRM_Utils_Array::value( 'no_display', $field );
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value( 'title', $field );
                    }
                }
            }
        }

        if ( CRM_Utils_Array::value('charts', $this->_params) ) {
            $select[] = "COUNT(civicrm_mailing_event_trackable_url_open.id) as civicrm_mailing_click_count";
            $this->_columnHeaders["civicrm_mailing_click_count"]['title'] = ts('Click Count'); 
        }

        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }

    function from( ) {
        $this->_from = "
        FROM civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}";
        
        $this->_from .= "
				INNER JOIN civicrm_mailing_event_queue
					ON civicrm_mailing_event_queue.contact_id = {$this->_aliases['civicrm_contact']}.id
				INNER JOIN civicrm_email {$this->_aliases['civicrm_email']}
					ON civicrm_mailing_event_queue.email_id = {$this->_aliases['civicrm_email']}.id
				INNER JOIN civicrm_mailing_event_bounce {$this->_aliases['civicrm_mailing_event_bounce']}
					ON {$this->_aliases['civicrm_mailing_event_bounce']}.event_queue_id = civicrm_mailing_event_queue.id
				LEFT JOIN civicrm_mailing_bounce_type {$this->_aliases['civicrm_mailing_bounce_type']}
					ON {$this->_aliases['civicrm_mailing_event_bounce']}.bounce_type_id = {$this->_aliases['civicrm_mailing_bounce_type']}.id
";

        if ( $this->_params['is_replied_value'] == 1 ) {
            $this->_from .= "
                INNER JOIN  civicrm_mailing_event_reply
                    ON  civicrm_mailing_event_reply.event_queue_id = civicrm_mailing_event_queue.id";
        } else if ( $this->_params['is_replied_value'] == 0 ) {
            $this->_from .= "
                LEFT JOIN  civicrm_mailing_event_reply
                    ON  civicrm_mailing_event_reply.event_queue_id = civicrm_mailing_event_queue.id";
        }

        if ( $this->_params['is_forwarded_value'] == 1 ) {
            $this->_from .= "
                INNER JOIN  civicrm_mailing_event_forward
                    ON  civicrm_mailing_event_forward.event_queue_id = civicrm_mailing_event_forward.id";
        } else if ( $this->_params['is_forwarded_value'] == 0 ) {
            $this->_from .= "
                LEFT  JOIN  civicrm_mailing_event_forward
                    ON  civicrm_mailing_event_forward.event_queue_id = civicrm_mailing_event_forward.id";
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
	
    function postProcess( ) {

        $this->beginPostProcess( );

        // get the acl clauses built before we assemble the query
        $this->buildACLClause( $this->_aliases['civicrm_contact'] );

        $sql  = $this->buildQuery( true );
		             
        $rows = $graphRows = array();
        $this->buildRows ( $sql, $rows );
        
        $this->formatDisplay( $rows );
        $this->doTemplateAssignment( $rows );
        $this->endPostProcess( $rows );	
    }

    function alterDisplay( &$rows ) {
        // custom code to alter rows
        $entryFound = false;
        foreach ( $rows as $rowNum => $row ) {
            // make count columns point to detail report
            // convert display name to links
            if ( array_key_exists('civicrm_contact_sort_name', $row) && 
                 array_key_exists('civicrm_contact_id', $row) ) {
                $url = CRM_Report_Utils_Report::getNextUrl( 'contact/detail', 
                                              'reset=1&force=1&id_op=eq&id_value=' . $row['civicrm_contact_id'],
                                              $this->_absoluteUrl, $this->_id );
                $rows[$rowNum]['civicrm_contact_sort_name_link' ] = $url;
                $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact details for this contact.");
                $entryFound = true;
            }

            // handle country
            if ( array_key_exists('civicrm_address_country_id', $row) ) {
                if ( $value = $row['civicrm_address_country_id'] ) {
                    $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country( $value, false );
                }
                $entryFound = true;
            }
            if ( array_key_exists('civicrm_address_state_province_id', $row) ) {
                if ( $value = $row['civicrm_address_state_province_id'] ) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = CRM_Core_PseudoConstant::stateProvince( $value, false );
                }
                $entryFound = true;
            }

            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
        }
    }

	function mailing_select() {
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

	function bounce_type() {
		require_once('CRM/Mailing/DAO/BounceType.php');
		
		$data = array();
		
		$bounce_type = new CRM_Mailing_DAO_BounceType();
		$query = "SELECT name FROM civicrm_mailing_bounce_type";
		$bounce_type->query($query);
		
		while($bounce_type->fetch()) {
			$data[$bounce_type->name] = $bounce_type->name;
		}
		
		return $data;
	}
}
