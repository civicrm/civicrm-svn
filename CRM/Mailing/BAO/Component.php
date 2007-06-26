<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.8                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
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

require_once 'CRM/Mailing/DAO/Component.php';
class CRM_Mailing_BAO_Component extends CRM_Mailing_DAO_Component {

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
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_BAO_LocaationType object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) {
        $component =& new CRM_Mailing_DAO_Component( );
        $component->copyValues( $params );
        if ( $component->find( true ) ) {
            CRM_Core_DAO::storeValues( $component, $defaults );
            return $component;
        }
        return null;
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
    static function setIsActive( $id, $is_active ) {
        return CRM_Core_DAO::setFieldValue( 'CRM_Mailing_DAO_Component', $id, 'is_active', $is_active );
    }
    
    /**
     * Create and Update mailing component 
     * 
     * @params array $params (reference ) an assoc array of name/value pairs
     * @return object CRM_Mailing_BAO_Component object
     *
     * @access public
     * @static
     */

    static function add( &$params )
    {
        // action is taken depending upon the mode
        $component                 =& new CRM_Mailing_DAO_Component( );
        $component->domain_id      =  CRM_Core_Config::domainID( );
        $component->name           =  $params['name'];
        $component->component_type =  $params['component_type'];
        $component->subject        =  $params['subject'];
        if ($params['body_text']) {
            $component->body_text  =  $params['body_text'];
        } else {
            $component->body_text  =  CRM_Utils_String::htmlToText($params['body_html']);
        }
        $component->body_html      =  $params['body_html'];
        $component->is_active      =  CRM_Utils_Array::value( 'is_active' , $params, false );
        $component->is_default     = CRM_Utils_Array::value( 'is_default', $params, false );
        
        $query = "UPDATE civicrm_mailing_component SET is_default = 0 WHERE domain_id = {$component->domain_id} AND component_type ='{$component->component_type}'";
        CRM_Core_DAO::executeQuery($query, CRM_Core_DAO::$_nullArray);
        
        if ($params['action'] & CRM_Core_Action::UPDATE ) {
            $component->id = $params['id'];
        }
        
        $component->save( );

        CRM_Core_Session::setStatus( ts('The mailing component "%1" has been saved.',
                                        array( 1 => $component->name )) );
    }
}

?>
