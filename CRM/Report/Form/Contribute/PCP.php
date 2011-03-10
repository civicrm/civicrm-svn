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

class CRM_Report_Form_Contribute_PCP extends CRM_Report_Form {

    function __construct( ) {
        $this->_columns = 
            array( 
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
                   
                   'civicrm_contact' =>
                   array( 'dao'      => 'CRM_Contact_DAO_Contact',
                          'fields'   =>
                          array( 'display_name' => 
                                 array( 'title' => ts( 'Supporter' ), ), ), 
                          'grouping' => 'pcp-fields',
                          ),

                   'civicrm_contribution' =>
                   array( 'dao'    => 'CRM_Contribute_DAO_Contribution',
                          'fields' =>
                          array( 'contribution_status_id' => 
                                 array( 'title'      => ts( 'Contribution Status' ), 
                                        'no_display' => true ),
                                 'receive_date' => 
                                 array( 'title'      => ts( 'Most Recent Donation' ), 
                                        'statistics' => 
                                        array('max'  => ts( 'Most Recent Donation' ), ), ),
                                 ),
                          'grouping' => 'pcp-fields',
                          ),

                   );

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
    }

    function groupBy( ) {
        $this->_groupBy = "GROUP BY {$this->_aliases['civicrm_pcp']}.id";
    }
}
