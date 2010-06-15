<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

require_once 'CRM/Campaign/DAO/Survey.php';

Class CRM_Campaign_BAO_Survey extends CRM_Campaign_DAO_Survey
{
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * campaign_id. 
     *
     * @param array  $params   (reference ) an assoc array of name/value pairs
     * @param array  $defaults (reference ) an assoc array to hold the flattened values
     *
     * @access public
     */
    static function retrieve ( &$params, &$defaults ) 
    {
        $dao = new CRM_Campaign_DAO_Survey( );
        
        $dao->copyValues($params);
        
        if( $dao->find( true ) ) {
            CRM_Core_DAO::storeValues( $dao, $defaults );
            return $dao;
        }
        return null;  
    }

    /**
     * takes an associative array and creates a Survey object
     *
     * the function extract all the params it needs to initialize the create a
     * survey object.
     *
     * 
     * @return object CRM_Survey_DAO_Survey object
     * @access public
     * @static
     */
    static function create( &$params ) 
    {
        if ( empty( $params ) ) {
            return;
        }
        
        $dao = new CRM_Campaign_DAO_Survey();
        $dao->copyValues( $params );
        $dao->save();

        return $dao;
    }

     /**
     * Function to get Survey Details 
     * 
     * @param boolean $all
     * @param int $id
     * @static
     */
    static function getSurvey( $all = false, $id = false ) {
        $survey = array( );
        $dao = new CRM_Campaign_DAO_Survey( );

        if ( !$all ) {
            $dao->is_active = 1;
        } 
        if ( $id ) {
            $dao->id = $id;  
        }
        $dao->find( );
        while ( $dao->fetch() ) {
            CRM_Core_DAO::storeValues($dao, $survey[$dao->id]);
        }
        
        return $survey;
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */ 
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Campaign_DAO_Survey', $id, 'is_active', $is_active );
    }

    /**
     * Function to delete the survey
     *
     * @param int $id survey id
     *
     * @access public
     * @static
     *
     */
    static function del( $id )
    { 
        if ( !$id ) {
            return null;
        }

        $dao     = new CRM_Campaign_DAO_Survey( );
        $dao->id = $id;
        return $dao->delete( );
    }

}
