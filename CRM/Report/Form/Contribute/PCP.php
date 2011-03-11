<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Report/Form.php';
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Contribute/PseudoConstant.php';

class CRM_Report_Form_Contribute_PCP extends CRM_Report_Form {

    function __construct( ) {
        $this->_columns = 
            array( 
                  'civicrm_contact' =>
                  array( 'dao'      => 'CRM_Contact_DAO_Contact',
                         'fields'   =>
                         array( 'sort_name' => 
                                array( 'title' => ts( 'Supporter' ), 
                                       'required'=> true ,
                                       'default' => true ), 
                                'id' => 
                                array( 'required'=> true ,
                                       'no_display' => true ) ),
                         'filters' =>             
                         array('sort_name'    => 
                               array( 'title'      => ts( 'Contact Name' ),
                                      'operator'   => 'like' ),
                               'id'    => 
                               array( 'title'      => ts( 'Contact ID' ),
                                      'no_display' => true ), ),
                         'grouping' => 'pcp-fields',
                         ),
                  'civicrm_contribution_page' =>
                  array( 'dao'          => 'CRM_Contribute_DAO_ContributionPage',
                         'fields'       =>
                         array( 'page_title' => 
                                array( 'title' => ts( 'Page Title' ), 
                                       'name'  => 'title', ),
                                ),
                         'grouping'     => 'pcp-fields',
                         ),
                  
                  'civicrm_pcp' =>
                  array( 'dao'    => 'CRM_Contribute_DAO_PCP',
                         'fields' =>
                         array( 'title'  => 
                                array( 'title' => ts( 'Campaign Title' ), ), 
                                'goal_amount'  => 
                                array( 'title' => ts( 'Goal Amount' ), 
                                       'type'  => CRM_Utils_Type::T_MONEY ), ),
                         'grouping'      => 'pcp-fields',
                         ),
                  
                  'civicrm_contribution_soft' =>
                   array( 'dao'    => 'CRM_Contribute_DAO_ContributionSoft',
                          'fields' =>
                          array( 'amount_1' => 
                                 array( 'title'      => ts('Committed Amount'),
                                        'name'       => 'amount',
                                        'type'       => CRM_Utils_Type::T_MONEY,
                                        'statistics' => 
                                        array('sum'  => ts( 'Committed Amount' ), ), ),
                                 'amount_2' =>
                                 array( 'title'      => ts('Amount Received'),
                                        'name'       => 'amount',
                                        'type'       => CRM_Utils_Type::T_MONEY,
                                        // nice trick with dbAlias
                                        'dbAlias'    => 'SUM(IF( contribution_civireport.contribution_status_id > 1, 0, contribution_soft_civireport.amount))',
                                        ),
                                 'soft_id' => 
                                 array( 'title'      => ts('Number of Donors'),
                                        'name'       => 'id',
                                        'statistics' => 
                                        array('count'  => ts( 'Number of Donors' ), ), ),
                                 ),
                          'grouping'  => 'pcp-fields',
                          ),
                  'civicrm_address' =>
                  array( 'dao' => 'CRM_Core_DAO_Address',
                         'filters' =>             
                         array( 'country_id' => 
                                array( 'title'        => ts( 'Country' ), 
                                       'type'         => CRM_Utils_Type::T_INT,
                                       'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                       'options'      => CRM_Core_PseudoConstant::country( ),), 
                                'state_province_id' => 
                                array( 'title'        => ts( 'State/Province' ), 
                                       'type'         => CRM_Utils_Type::T_INT,
                                       'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                       'options'      => CRM_Core_PseudoConstant::stateProvince( ),), ),
                         ),
                  
                  
                  'civicrm_contribution' =>
                  array( 'dao'    => 'CRM_Contribute_DAO_Contribution',
                         'fields' =>
                         array(
                               'contribution_id' => array( 
                                                          'name' => 'id',
                                                          'no_display' => true,
                                                          'required'   => true,
                                                           ),
                               'contribution_status_id' => 
                               array( 'title'      => ts( 'Contribution Status' ), 
                                      'no_display' => true ),
                               'receive_date' => 
                               array( 'title'      => ts( 'Most Recent Donation' ), 
                                      'statistics' => 
                                      array('max'  => ts( 'Most Recent Donation' ), ), ),
                               ),
                         'filters' =>             
                         array( 'receive_date'           => 
                                array( 'operatorType' => CRM_Report_Form::OP_DATE ),
                                'contribution_type_id'   =>
                                array( 'title'        => ts( 'Contribution Type' ), 
                                       'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                       'options'      => CRM_Contribute_PseudoConstant::contributionType( )
                                       ),
                                'contribution_status_id' => 
                                array( 'title'        => ts( 'Contribution Status' ), 
                                       'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                       'options'      => CRM_Contribute_PseudoConstant::contributionStatus( ),
                                       'default'      => array( 1 ),
                                       ),
                                'total_amount'           => 
                                array( 'title'        => ts( 'Contribution Amount' ) ),
                                ),
                         'grouping' => 'pcp-fields',
                         ),
                  'civicrm_group' => 
                  array( 'dao'    => 'CRM_Contact_DAO_GroupContact',
                         'alias'  => 'cgroup',
                         'filters' =>             
                         array( 'gid' => 
                                array( 'name'         => 'group_id',
                                       'title'        => ts( 'Group' ),
                                       'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                       'group'        => true,
                                       'options'      => CRM_Core_PseudoConstant::group( ) ), ), ),                  
                   );
        
        $this->_tagFilter = true;
        
        parent::__construct( );
    }
    
    function from( ) {
        $this->_from = "
FROM civicrm_pcp {$this->_aliases['civicrm_pcp']}

LEFT JOIN civicrm_contribution_soft {$this->_aliases['civicrm_contribution_soft']} 
          ON {$this->_aliases['civicrm_pcp']}.id = 
             {$this->_aliases['civicrm_contribution_soft']}.pcp_id

LEFT JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']} 
          ON {$this->_aliases['civicrm_contribution_soft']}.contribution_id = 
             {$this->_aliases['civicrm_contribution']}.id

LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']} 
          ON {$this->_aliases['civicrm_pcp']}.contact_id = 
             {$this->_aliases['civicrm_contact']}.id 

LEFT JOIN civicrm_contribution_page {$this->_aliases['civicrm_contribution_page']}
          ON {$this->_aliases['civicrm_pcp']}.contribution_page_id = 
             {$this->_aliases['civicrm_contribution_page']}.id";
        
        if ( $this->_addressField || 
             ( !empty( $this->_params['state_province_id_value'] ) || 
               !empty( $this->_params['country_id_value'] ) ) ) { 
            
            $this->_from .= "
            LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']} 
                   ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND 
                      {$this->_aliases['civicrm_address']}.is_primary = 1\n";        
        }
    }
    
    function groupBy( ) {
        $this->_groupBy = "GROUP BY {$this->_aliases['civicrm_pcp']}.id";
    }
    
    function orderBy( ) {
        $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_contact']}.sort_name ";
    }
    function alterDisplay( &$rows ) {
        // custom code to alter rows
        $entryFound = false;
        $checkList  =  array();
        foreach ( $rows as $rowNum => $row ) {
            if ( !empty($this->_noRepeats) && $this->_outputMode != 'csv' ) {
                // not repeat contact sort names if it matches with the one 
                // in previous row
                $repeatFound = false;
               
                foreach ( $row as $colName => $colVal ) {
                    if ( CRM_Utils_Array::value( $colName, $checkList ) && 
                         is_array( $checkList[$colName] ) && 
                         in_array( $colVal, $checkList[$colName] ) ) {
                        $rows[$rowNum][$colName] = "";
                        $repeatFound = true;
                    }
                    if ( in_array( $colName, $this->_noRepeats ) ) {
                        $checkList[$colName][] = $colVal;
                    }
                }
            }
            
            if ( array_key_exists( 'civicrm_contact_sort_name', $row ) && 
                 $rows[$rowNum]['civicrm_contact_sort_name'] && 
                 array_key_exists( 'civicrm_contact_id', $row ) ) {
                $url = CRM_Utils_System::url( "civicrm/contact/view"  , 
                                              'reset=1&cid=' . $row['civicrm_contact_id'],
                                              $this->_absoluteUrl );
                $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
                $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts( "View Contact Summary for this Contact." );
                $entryFound = true;
            }
            
            if ( !$entryFound ) {
                break;
            }
        }
      }
}
