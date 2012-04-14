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

class CRM_Report_Form_Price_Lineitemparticipant extends CRM_Report_Form_Extended {
    protected $_addressField = false;

    protected $_emailField   = false;

    protected $_summary      = null;

    protected $_customGroupExtends = array( 'Participant', 'Individual' , 'Contact');

    protected $_baseTable = 'civicrm_line_item';

    protected $_aclTable = 'civicrm_contact';

    function __construct($child = 0 ) {
      if(empty($child)){
        // hack because we are currently using this as base for other report
        // plan is to move functions into Form.php instead & won't be required
        $this->_columns = $this->getContactColumns()

                        + $this->getLineItemColumns()
                        + $this->getPriceFieldValueColumns()
                        + $this->getPriceFieldColumns()
                        + $this->getParticipantColumns()
                        + $this->getEventColumns()
                        + $this->getContributionColumns()

                        ;
      }
        parent::__construct( );
    }

    function preProcess( ) {
        parent::preProcess( );
    }

    function select( ) {
        parent::select( ); }

       /*
    * select from clauses to use (from those advertised using
    * $this->getAvailableJoins())
    */
    function fromClauses( ) {
      return array(
        'priceFieldValue_from_lineItem',
        'priceField_from_lineItem',
        'participant_from_lineItem',
        'contribution_from_lineItem',
        'contact_from_participant',
        'event_from_participant'
      );
    }
    function groupBy( ) {
       parent::groupBy();

    }

    function orderBy( ) {
       parent::orderBy();
    }

    function statistics( &$rows ) {
        return parent::statistics( $rows );
    }

    function postProcess( ) {
      parent::postProcess( );
    }

    function alterDisplay( &$rows ) {
       parent::alterDisplay($rows);

    }
}