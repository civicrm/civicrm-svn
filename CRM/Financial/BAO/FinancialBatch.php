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

require_once 'CRM/Financial/DAO/FinancialBatch.php';

class CRM_Financial_BAO_FinancialBatch extends CRM_Financial_DAO_FinancialBatch {

    /**
     * static holder for the default LT
     */
    static $_defaultContributionType = null;
    
    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }
    
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Contribute_BAO_ContributionType object
     * @access public
     * @static
     */
  static function retrieve( &$params, &$defaults ) {
        $financialBatch = new CRM_Financial_DAO_FinancialBatch( );
        $financialBatch->copyValues( $params );
        if ( $financialBatch->find( true ) ) {
            CRM_Core_DAO::storeValues( $financialBatch, $defaults );
            return $financialBatch;
        }
        return null;
    }
  
    /**
     * function to add the financial types
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id
     * 
     * @access public
     * @static 
     * @return object
     */
  static function add(&$params) {
        // action is taken depending upon the mode
        $financialBatch              = new CRM_Financial_DAO_FinancialBatch( );
    $financialBatch->copyValues( $params );
    if (CRM_Utils_Array::value('batch_id', $params)) {
      $financialBatch->id = CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_FinancialBatch', $params['batch_id'], 'id', 'batch_id');
    }
        $financialBatch->save( );
        return $financialBatch;
    }
}

