<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.6                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2006                                  |
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
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]civicrm[DOT]org.  If you have questions       |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://www.civicrm.org/licensing/                                  |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@civicrm.org>
 * @copyright CiviCRM LLC (c) 2004-2006
 * $Id$
 *
 */

require_once 'CRM/Quest/StateMachine/MatchApp.php';

/**
 * State machine for managing different states of the Quest process.
 *
 */
class CRM_Quest_StateMachine_MatchApp_Household extends CRM_Quest_StateMachine_MatchApp {

    static $_dependency = null;

    public function rebuild( &$controller, $action = CRM_Core_Action::NONE ) {
        // ensure the states array is reset
        $this->_states = array( );

        $this->_pages = array( );
        self::setPages( $this->_pages, $this, $controller );

        parent::rebuild( $controller, $action );
    }

    static public function setPages( &$pages, &$stateMachine, &$controller ) {
        $pages['CRM_Quest_Form_MatchApp_Household'] = null;

        $dynamic = array( 'Household', 'Sibling', 'Income' );
        foreach ( $dynamic as $d ) {
            require_once "CRM/Quest/Form/MatchApp/$d.php";
            eval( '$newPages =& CRM_Quest_Form_MatchApp_' . $d . '::getPages( $controller );' );
            $pages = array_merge( $pages, $newPages );
        }

        if ( self::includeNonCustodial( $stateMachine, $controller ) ) {
            $pages['CRM_Quest_Form_MatchApp_Noncustodial'] = null;
        }
    }

    public function &getDependency( ) {
        if ( self::$_dependency == null ) {
            self::$_dependency = array( 'Household'    => array( ),
                                        'Guardian'     => array( 'Household'  => 1 ),
                                        'Sibling'      => array( ),
                                        'Income'       => array( 'Guardian'   => 1 ),
                                        'Noncustodial' => array( ),
                                        );
        }

        return self::$_dependency;
    }

    public function includeNonCustodial( &$stateMachine, &$controller, $force = false ) {
        $includeNonCustodial = $controller->get( 'includeNonCustodial' );
        if ( $includeNonCustodial === null || $force ) {
            $cid = $controller->get( 'contactID' );
            $query = "
SELECT count( p.id )
  FROM quest_person p
 WHERE p.contact_id              = $cid
   AND p.is_parent_guardian      = 1
   AND p.is_contact_with_student = 0
";
            $includeNonCustodial = CRM_Core_DAO::singleValueQuery( $query, CRM_Core_DAO::$_nullArray ) ? 1 : 0;
            $controller->set( 'includeNonCustodial', $includeNonCustodial );
        }
        return $includeNonCustodial;
    }

}

?>
