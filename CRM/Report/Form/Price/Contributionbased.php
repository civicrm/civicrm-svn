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
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */


class CRM_Report_Form_Price_Contributionbased extends CRM_Report_Form_Extended {

    protected $_baseTable = 'civicrm_contribution' ;
    
    function __construct( ) {
        
        $this->_columns = $this->getContactColumns()
                        + $this->getLineItemColumns()
                        + $this->getContributionColumns() 
                        ;
       parent::__construct( );
    }

    function from( ) {
      //@todo I think the first line here would make sense as the parent::from function
       $this->_from = "FROM " . $this->_baseTable  . " " . $this->_aliases[$this->_baseTable];
       $this->joinLineItemFromContribution();
       $this->joinContactFromContribution();
    }
     
}