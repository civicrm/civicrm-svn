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

require_once 'CRM/Core/DAO/Batch.php';

/**
 *
 */
class CRM_Core_BAO_Batch extends CRM_Core_DAO_Batch {
    /**
     * Cache for the current batch object
     */
    static $_batch = null;


    /**
     * Create a new batch
     *
     * @return batch array
     * @access public
     */
    static function create( $params ) {
        if ( ! CRM_Utils_Array::value( 'id', $params ) ) { 
            require_once 'CRM/Utils/String.php';
            $params['name'] = CRM_Utils_String::titleToVar( $params['title'] );
        }

        $batch = new CRM_Core_DAO_Batch( );
        $batch->copyValues( $params );
        $batch->save( );
        return $batch;
    }

   /**
    * Retrieve the information about the batch
    *
    * @param array $params   (reference ) an assoc array of name/value pairs
    * @param array $defaults (reference ) an assoc array to hold the flattened values
    *
    * @return array CRM_Core_BAO_Batch object on success, null otherwise
    * @access public
    * @static
    */
    static function retrieve( &$params, &$defaults ) {
        $batch = new CRM_Core_DAO_Batch( );
        $batch->copyValues( $params );
        if ( $batch->find( true ) ) {
            CRM_Core_DAO::storeValues( $batch, $defaults );
            return $batch;
        }
        return null;
    }

    /**
     * Get profile id associated with the batch type
     *
     * @param int   $batchTypeId batch type id
     * @return int  $profileId   profile id
     * @static
     */
    static function getProfileId( $batchTypeId  ) {
        //retrieve the profile specific to batch type
        //FIX ME
        $profileId = 10;

        return $profileId;
    }
}

