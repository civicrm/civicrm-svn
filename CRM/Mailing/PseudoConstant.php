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

/**
 * This class holds all the Pseudo constants that are specific to Mass mailing. This avoids
 * polluting the core class and isolates the mass mailer class
 */
class CRM_Mailing_PseudoConstant extends CRM_Core_PseudoConstant {

    /**
     * mailing templates
     * @var array
     * @static
     */
    private static $template;

    /**
     * completed mailings
     * @var array
     * @static
     */
    private static $completed;

    /**
     * mailing components
     * @var array
     * @static
     */
    private static $component;

    /**
     * Get all the mailing components of a particular type
     *
     * @param $type the type of component needed
     * @access public
     * @return array - array reference of all mailing components
     * @static
     */
    public static function &component( $type = null ) {
        $name = $type ? $type : 'ALL';

        if ( ! self::$component || ! array_key_exists( $name, self::$component ) ) {
            if ( ! self::$component ) {
                self::$component = array( );
            }
            if ( ! $type ) {
                self::$component[$name] = null;
                CRM_Core_PseudoConstant::populate( self::$component[$name], 'CRM_Mailing_DAO_Component' );
            } else {
                // we need to add an additional filter for $type
                self::$component[$name] = array( );

                require_once 'CRM/Mailing/DAO/Component.php';

                $object =& new CRM_Mailing_DAO_Component( );
                $object->domain_id = CRM_Core_Config::domainID( );
                $object->component_type = $type;
                $object->selectAdd( );
                $object->selectAdd( "id, name" );
                $object->orderBy( 'is_default, name' );
                $object->is_active = 1;
                $object->find( );
                while ( $object->fetch( ) ) {
                    self::$component[$name][$object->id] = $object->name;
                }
            }
        }
        return self::$component[$name];
    }

    /**
     * Get all the mailing templates
     *
     * @access public
     * @return array - array reference of all mailing templates if any
     * @static
     */
    public static function &template( ) {
        if ( ! self::$template ) {
            CRM_Core_PseudoConstant::populate( self::$template, 'CRM_Mailing_DAO_Mailing', true, 'name', 'is_template' );
        }
        return self::$template;
    }

    /**
     * Get all the completed mailing
     *
     * @access public
     * @return array - array reference of all mailing templates if any
     * @static
     */
    public static function &completed( ) {
        if ( ! self::$completed ) {
            require_once 'CRM/Mailing/BAO/Mailing.php';
            $mailingACL = CRM_Mailing_BAO_Mailing::mailingACL( );
            CRM_Core_PseudoConstant::populate( self::$completed,
                                               'CRM_Mailing_DAO_Mailing',
                                               false,
                                               'name',
                                               'is_completed',
                                               $mailingACL );
        }
        return self::$completed;
    }


}

?>
