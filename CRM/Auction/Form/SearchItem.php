<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/OptionGroup.php';

class CRM_Auction_Form_SearchItem extends CRM_Core_Form 
{
    function setDefaultValues( )
    {
        $defaults = array( );
        $defaults['auctionsByDates'] = 0;

        return $defaults;
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    public function buildQuickForm( ) 
    {
        $this->add( 'text', 'title', ts( 'Find' ),
                    array(CRM_Core_DAO::getAttribute('CRM_Auction_DAO_Auction', 'title') ) );
        
        $this->addButtons(array( 
                                array ('type'      => 'refresh', 
                                       'name'      => ts('Search'), 
                                       'isDefault' => true ), 
                                ) ); 
    }


    function postProcess( ) 
    {
        $params = $this->controller->exportValues( $this->_name );
        $parent = $this->controller->getParent( );
        $parent->set( 'searchResult', 1 );
        if ( ! empty( $params ) ) {
            $fields = array( 'title', 'item_type_id' );
            foreach ( $fields as $field ) {
                if ( isset( $params[$field] ) &&
                     ! CRM_Utils_System::isNull( $params[$field] ) ) {
                    $parent->set( $field, $params[$field] );
                } else {
                    $parent->set( $field, null );
                }
            }
        }
    }
}


