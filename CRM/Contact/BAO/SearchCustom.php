<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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

class CRM_Contact_BAO_SearchCustom {

    static function details( $csID, $ssID = null, $gID = null ) {
        $error = array( null, null, null );

        if ( ! $csID &&
             ! $ssID &&
             ! $gID ) {
            return $error;
        }

        $customSearchID = $csID;
        $formValues     = array( );
        if ( $ssID || $gID ) {
            if ( $gID ) {
                $ssID = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group', $gID, 'saved_search_id' );
            }

            $formValues = CRM_Contact_BAO_SavedSearch::getFormValues( $ssID );
            $customSearchID    = CRM_Utils_Array::value( 'customSearchID',
                                                         $formValues );
        }

        if ( ! $customSearchID ) {
            return $error;
        }

        // check that the csid exists in the db along with the right file
        // and implements the right interface
        require_once 'CRM/Core/OptionGroup.php';
        $customSearchClass = CRM_Core_OptionGroup::getLabel( 'custom_search',
                                                             $customSearchID );
        if ( ! $customSearchClass ) {
            return $error;
        }

        $customSearchFile = str_replace( '_',
                                         DIRECTORY_SEPARATOR,
                                         $customSearchClass ) . '.php';
        
        $error = include_once( $customSearchFile );
        if ( $error == false ) {
            return $error;
        }

        return array( $customSearchID, $customSearchClass, $formValues );
    }

    static function customClass( $csID, $ssID ) {
        list( $customSearchID, $customSearchClass, $formValues ) =
            self::details( $csID, $ssID );

        if ( ! $customSearchID ) {
            CRM_Core_Error::fatal( 'Could not resolve custom search ID' );
        }

        // instantiate the new class
        eval( '$customClass = new ' . $customSearchClass . '( $formValues );' );

        return $customClass;
    }

    static function contactIDSQL( $csID, $ssID ) {
        $customClass = self::customClass( $csID, $ssID );
        return $customClass->contactIDs( );
    }

    static function fromWhereEmail( $csID, $ssID ) {
        $customClass = self::customClass( $csID, $ssID );

        $from  = $customClass->from ( );
        $where = $customClass->where( );


        return array( $from, $where );
    }

}


