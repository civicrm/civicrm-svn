<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
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

require_once 'CRM/Core/DAO/Tag.php';

class CRM_Core_BAO_Tag extends CRM_Core_DAO_Tag {

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     * 
     * @param array $params      (reference ) an assoc array of name/value pairs
     * @param array $defaults    (reference ) an assoc array to hold the flattened values
     * 
     * @return object     CRM_Core_DAO_Tag object on success, otherwise null
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) {
        $tag =& new CRM_Core_DAO_Tag( );
        $tag->copyValues( $params );
        if ( $tag->find( true ) ) {
            CRM_Core_DAO::storeValues( $tag, $defaults );
            return $tag;
        }
        return null;
    }

    /**
     * Function to delete the tag 
     *
     * @param int $id   tag id
     *
     * @return boolean
     * @access public
     * @static
     *
     */
    static function del ( $id ) {
        // delete all crm_entity_tag records with the selected tag id
        require_once 'CRM/Core/DAO/EntityTag.php';
        $entityTag =& new CRM_Core_DAO_EntityTag( );
        $entityTag->tag_id = $id;
        if ( $entityTag->find( ) ) {
            while ( $entityTag->fetch() ) {
                $entityTag->delete();
            }
        }
        
        // delete from tag table
        $tag =& new CRM_Core_DAO_Tag( );
        $tag->id = $id;
        if ( $tag->delete( ) ) {
            CRM_Core_Session::setStatus( ts('Selected Tag has been Deleted Successfuly.') );
            return true;
        }
        return false;
    }

    /**
     * takes an associative array and creates a contact object
     * 
     * The function extract all the params it needs to initialize the create a
     * contact object. the params array could contain additional unused name/value
     * pairs
     * 
     * @param array  $params         (reference) an assoc array of name/value pairs
     * @param array  $ids            (reference) the array that holds all the db ids
     * 
     * @return object    CRM_Core_DAO_Tag object on success, otherwise null
     * @access public
     * @static
     */
    static function add( &$params, &$ids ) {
        if ( ! self::dataExists( $params ) ) {
            return null;
        }

        $tag               =& new CRM_Core_DAO_Tag( );
        $tag->domain_id    = CRM_Core_Config::domainID( );

        $tag->copyValues( $params );

        $tag->id = CRM_Utils_Array::value( 'tag', $ids );

        $tag->save( );
        
        CRM_Core_Session::setStatus( ts('The tag \'%1\' has been saved.', array(1 => $tag->name)) );
        
        return $tag;
    }

    /**
     * Check if there is data to create the object
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return boolean
     * @access public
     * @static
     */
    static function dataExists( &$params ) {
        
        if ( !empty( $params['name'] ) ) {
            return true;
        }
        
        return false;
    }
}

?>
