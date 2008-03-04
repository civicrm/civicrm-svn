<?php 

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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

class CRM_Grant_BAO_Query 
{
    static function &getFields( ) 
    {
        $fields = array( );
        require_once 'CRM/Grant/DAO/Grant.php';
        $fields = array_merge( $fields, CRM_Grant_DAO_Grant::export( ) );
        return $fields;
    }
   
    /** 
     * build select for CiviGrant 
     * 
     * @return void  
     * @access public  
     */
    static function select( &$query ) 
    {
        if ( $query->_mode & CRM_Contact_BAO_Query::MODE_GRANT ) {

            $query->_select['grant_type_id'] = "civicrm_grant.grant_type_id as grant_type_id";
            $query->_select['grant_status_id' ]  = "civicrm_grant.status_id as grant_status_id";
            $query->_select['grant_amount_requested']  = "civicrm_grant.amount_requested as grant_amount_requested";
            $query->_select['grant_amount_granted']  = "civicrm_grant.amount_granted as grant_amount_granted";
            $query->_select['grant_amount_total']  = "civicrm_grant.amount_total as grant_amount_total";
            $query->_select['grant_application_received_date']  = "civicrm_grant.application_received_date as grant_application_received_date ";
            $query->_element['grant_type_id'] = 1;
            $query->_element['grant_status_id'] = 1;
            $query->_tables['civicrm_grant'] = 1;
            $query->_whereTables['civicrm_grant'] = 1;
        }
    }
    
    /** 
     * Given a list of conditions in params generate the required
     * where clause
     * 
     * @return void 
     * @access public 
     */ 
    static function where( &$query ) 
    {
        foreach ( array_keys( $query->_params ) as $id ) {
            if ( substr( $query->_params[$id][0], 0, 6) == 'grant_' ) {
                self::whereClauseSingle( $query->_params[$id], $query );
            }
        }
    }
  
    static function whereClauseSingle( &$values, &$query ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        switch( $name ) {
            
        case 'grant_money_transfer_date_low':
        case 'grant_money_transfer_date_high':
            $query->dateQueryBuilder( $values, 'civicrm_grant',
                                      'grant_money_transfer_date', 'money_transfer_date',
                                      'Money Transfer Date' );
            return;
        
        case 'grant_money_transfer_date_notset' :
            $query->_where[$grouping][] =   "civicrm_grant.money_transfer_date IS NULL";
            $query->_qill[$grouping][]  = "Grant Money Transfer Date is NULL";
            $query->_tables['civicrm_grant'] = $query->_whereTables['civicrm_grant'] = 1;
            return;

 
        case 'grant_application_received_date_low':
        case 'grant_application_received_date_high':
            $query->dateQueryBuilder( $values, 'civicrm_grant',
                                      'grant_application_received_date',
                                      'application_received_date', 'Application Received Date' );
            return;

        case 'grant_application_received_notset' :
            $query->_where[$grouping][] =   "civicrm_grant.application_received_date IS NULL";
            $query->_qill[$grouping][]  = "Grant Application Received Date is NULL";
            $query->_tables['civicrm_grant'] = $query->_whereTables['civicrm_grant'] = 1;
            return ;
        
        case 'grant_due_date_low':
        case 'grant_due_date_high':
            $query->dateQueryBuilder( $values, 'civicrm_grant',
                                      'grant_due_date',
                                      'grant_due_date', 'Grant Due Date' );
            return;

        case 'grant_due_date_notset':
           $query->_where[$grouping][] =   "civicrm_grant.grant_due_date IS NULL";
           $query->_qill[$grouping][]  = "Grant Due Date is NULL";
           $query->_tables['civicrm_grant'] = $query->_whereTables['civicrm_grant'] = 1;
           return ;


        case 'grant_decision_date_low':
        case 'grant_decision_date_high':
            $query->dateQueryBuilder( $values, 'civicrm_grant',
                                      'grant_decision_date',
                                      'decision_date', 'Grant Decision Date' );
            return;
        case 'grant_decision_date_notset':
          $query->_where[$grouping][] =   "civicrm_grant.decision_date IS NULL";
          $query->_qill[$grouping][]  = "Grant Decision Date is NULL";
          $query->_tables['civicrm_grant'] = $query->_whereTables['civicrm_grant'] = 1;
          return ;

        case 'grant_type_id':
            
            $value = strtolower(addslashes(trim($value)));

            $query->_where[$grouping][] = "civicrm_grant.grant_type_id $op '{$value}'";
            
            require_once 'CRM/Core/OptionGroup.php';
            $grantTypes  = CRM_Core_OptionGroup::values('grant_type' );
            $value = $grantTypes[$value];
            $query->_qill[$grouping ][] = ts( 'Grant Type %2 %1', array( 1 => $value, 2 => $op) );
            $query->_tables['civicrm_grant'] = $query->_whereTables['civicrm_grant'] = 1;

            return;

        case 'grant_status_id':
            
            $value = strtolower(addslashes(trim($value)));

            $query->_where[$grouping][] = "civicrm_grant.status_id $op '{$value}'";
            
            require_once 'CRM/Core/OptionGroup.php';

            $grantStatus  = CRM_Core_OptionGroup::values('grant_status' );
            $value = $grantStatus[$value];

            $query->_qill[$grouping ][] = ts( 'Grant Status %2 %1', array( 1 => $value, 2 => $op) );
            $query->_tables['civicrm_grant'] = $query->_whereTables['civicrm_grant'] = 1;

            return;
   
        case 'grant_report_received':

            if ( $value == 1 ) {
                $yesNo = 'Yes';
                $query->_where[$grouping][] =   "civicrm_grant.grant_report_received $op $value";
            } else if ( $value == 0 ){
                $yesNo = 'No';
                $query->_where[$grouping][]  = "civicrm_grant.grant_report_received IS NULL";
            }

            $query->_qill[$grouping][]  = "Grant Report Received = $yesNo ";
            $query->_tables['civicrm_grant'] = $query->_whereTables['civicrm_grant'] = 1;
            
            return;
     
        case 'grant_amount':
        case 'grant_amount_low':
        case 'grant_amount_high':
            $query->numberRangeBuilder( $values,
                                        'civicrm_grant', 'grant_amount', 'amount_total', 'Total Amount' );
        }
    }

    static function from( $name, $mode, $side ) 
    {
        $from = null;
        switch ( $name ) {
        
        case 'civicrm_grant':
            $from = " INNER JOIN civicrm_grant ON civicrm_grant.contact_id = contact_a.id ";
            break;
    
        }
        return $from;
    }

    /**
     * getter for the qill object
     *
     * @return string
     * @access public
     */
    function qill( ) {
        return (isset($this->_qill)) ? $this->_qill : "";
    }
   
    static function defaultReturnProperties( $mode ) 
    {
        $properties = null;
        if ( $mode & CRM_Contact_BAO_Query::MODE_GRANT ) {
            $properties = array(
                                'contact_type'                    => 1,
                                'sort_name'                       => 1,   
                                'grant_type_id'                   => 1, 
                                'grant_status_id'                 => 1, 
                                'grant_amount_requested'          => 1,
                                'grant_application_received_date' => 1
                                );
       
 
        }

        return $properties;
    }

    /**
     * add all the elements shared between grant search and advanaced search
     *
     * @access public 
     * @return void
     * @static
     */   
    static function buildSearchForm( &$form ) 
    {
        $config =& CRM_Core_Config::singleton( );
        $domainID = CRM_Core_Config::domainID( );
        require_once 'CRM/Core/OptionGroup.php'; 
        $grantType = CRM_Core_OptionGroup::values( 'grant_type' );
        $form->add('select', 'grant_type_id',  ts( 'Grant Type' ),
                   array( '' => ts( '-select-' ) ) + $grantType );

        $grantStatus = CRM_Core_OptionGroup::values( 'grant_status' );
        $form->add('select', 'grant_status_id',  ts( 'Grant Status' ),
                   array( '' => ts( '-select-' ) ) + $grantStatus );
        
        $form->addElement('date', 'grant_application_received_date_low', ts('App. Recieved Date - From'), 
                          CRM_Core_SelectValues::date('relative')); 
        $form->addRule('grant_application_received_date_low', ts('Select a valid date.'), 'qfDate'); 
        
        $form->addElement('date', 'grant_application_received_date_high', ts('To'), 
                          CRM_Core_SelectValues::date('relative')); 
        $form->addRule('grant_application_received_date_high', ts('Select a valid date.'), 'qfDate'); 
        $form->addElement('checkbox','grant_application_received_notset', ts(''),null );
        
        $form->addElement('date', 'grant_money_transfer_date_low', ts('Money Sent Date - From'), 
                          CRM_Core_SelectValues::date('relative')); 
        $form->addRule('grant_money_transfer_date_low', ts('Select a valid date.'), 'qfDate'); 
               
        $form->addElement('date', 'grant_money_transfer_date_high', ts('To'),
                          CRM_Core_SelectValues::date('relative')); 
        $form->addRule('grant_money_transfer_date_high', ts('Select a valid date.'), 'qfDate'); 
        
        $form->addElement('checkbox','grant_money_transfer_date_notset', ts(''),null );
        
        $form->addElement('date', 'grant_due_date_low', ts('Report Due Date - From'), 
                          CRM_Core_SelectValues::date('relative')); 
        $form->addRule('grant_due_date_low', ts('Select a valid date.'), 'qfDate'); 
               
        $form->addElement('date', 'grant_due_date_high', ts('To'),
                          CRM_Core_SelectValues::date('relative')); 
        $form->addRule('grant_due_date_high', ts('Select a valid date.'), 'qfDate');
        
        $form->addElement('checkbox','grant_due_date_notset', ts(''),null );
        
        $form->addElement('date', 'grant_decision_date_low', ts('Grant Decision Date - From'), 
                          CRM_Core_SelectValues::date('relative')); 
        $form->addRule('grant_decision_date_low', ts('Select a valid date.'), 'qfDate'); 
               
        $form->addElement('date', 'grant_decision_date_high', ts('To'),
                          CRM_Core_SelectValues::date('relative')); 
        $form->addRule('grant_decision_date_high', ts('Select a valid date.'), 'qfDate');
        
        $form->addElement('checkbox','grant_decision_date_notset', ts(''),null );
         
        $form->addYesNo( 'grant_report_received', ts( 'Grant report received?' ) );
        
        $form->add('text', 'grant_amount_low', ts('Minimum Amount'), array( 'size' => 8, 'maxlength' => 8 ) ); 
        $form->addRule( 'grant_amount_low', ts( 'Please enter a valid money value (e.g. 9.99).' ), 'money' );
        
        $form->add('text', 'grant_amount_high', ts('Maximum Amount'), array( 'size' => 8, 'maxlength' => 8 ) ); 
        $form->addRule( 'grant_amount_high', ts( 'Please enter a valid money value (e.g. 99.99).' ), 'money' );
        
        $form->assign( 'validGrant', true );
        
    }
    static function addShowHide( &$showHide ) 
    {
        $showHide->addHide( 'grantForm' );
        $showHide->addShow( 'grantForm_show' );
    }
    
    static function searchAction( &$row, $id ) 
    {
    }

    static function tableNames( &$tables ) 
    {
    }  
}


