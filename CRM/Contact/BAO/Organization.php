<?php
/*
 +----------------------------------------------------------------------+
 | CiviCRM version 1.0                                                  |
 +----------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                    |
 +----------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                      |
 |                                                                      |
 | CiviCRM is free software; you can redistribute it and/or modify it   |
 | under the terms of the Affero General Public License Version 1,      |
 | March 2002.                                                          |
 |                                                                      |
 | CiviCRM is distributed in the hope that it will be useful, but       |
 | WITHOUT ANY WARRANTY; without even the implied warranty of           |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                 |
 | See the Affero General Public License for more details at            |
 | http://www.affero.org/oagpl.html                                     |
 |                                                                      |
 | A copy of the Affero General Public License has been been            |
 | distributed along with this program (affero_gpl.txt)                 |
 +----------------------------------------------------------------------+
*/

/**
 * This class contains basic functions for Contact Organization
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

require_once 'CRM/Contact/DAO/Contact.php';
require_once 'CRM/Contact/DAO/Organization.php';
require_once 'CRM/Contact/DAO/Location.php';
require_once 'CRM/Contact/DAO/Address.php';
require_once 'CRM/Contact/DAO/Phone.php';
require_once 'CRM/Contact/DAO/IM.php';
require_once 'CRM/Contact/DAO/Email.php';

class CRM_Contact_BAO_Organization extends CRM_Contact_DAO_Organization
{
    /**
     * This is a contructor of the class.
     */
    function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * takes an associative array and creates a contact object
     *
     * the function extract all the params it needs to initialize the create a
     * contact object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids
     *
     * @return object CRM_Contact_BAO_Organization object
     * @access public
     * @static
     */
    static function add( &$params, &$ids ) {
        $organization =& new CRM_Contact_BAO_Organization( );

        $organization->copyValues( $params );

        $organization->id = CRM_Utils_Array::value( 'organization', $ids );
        return $organization->save( );
    }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $params input parameters to find object
     * @param array $values output values of the object
     * @param array $ids    the array that holds all the db ids
     *
     * @return CRM_Contact_BAO_Organization|null the found object or null
     * @access public
     * @static
     */
    static function getValues( &$params, &$values, &$ids ) {
        $organization =& new CRM_Contact_BAO_Organization( );
        
        $organization->copyValues( $params );
        if ( $organization->find(true) ) {
            $ids['organization'] = $organization->id;
            CRM_Core_DAO::storeValues( $organization, $values );

            return $organization;
        }
        return null;
    }
        
}

?>