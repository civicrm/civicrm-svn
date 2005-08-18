<?php 

/* 
 +--------------------------------------------------------------------+ 
 | CiviCRM version 1.1                                                | 
 +--------------------------------------------------------------------+ 
 | Copyright (c) 2005 Social Source Foundation                        | 
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
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have | 
 | questions about the Affero General Public License or the licensing | 
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   | 
 | at http://www.openngo.org/faqs/licensing.html                      | 
 +--------------------------------------------------------------------+ 
*/ 
 
/** 
 * 
 * @package CRM 
 * @author Donald A. Lobo <lobo@yahoo.com> 
 * @copyright Donald A. Lobo 01/15/2005 
 * $Id$ 
 * 
 */ 

/**
 * This implements the profile page for all contacts. It uses a selector
 * object to do the actual dispay. The fields displayd are controlled by
 * the admin
 */
class CRM_Contact_Page_Profile extends CRM_Core_Page {

    /**
     * all the fields that are listings related
     *
     * @var array
     * @access protected
     */
    protected $_fields;

    /**
     * list of all the fields that influence the search criteria
     *
     * @var array
     * @access protected
     */
    protected $_values;

    /**
     * extracts the parameters from the request and constructs information for
     * the selectror object to do a query
     *
     * @return void 
     * @access public 
     * 
     */ 
    function preProcess( ) {
        $this->_fields = CRM_Core_BAO_UFGroup::getListingFields( CRM_Core_Action::UPDATE,
                                                                 CRM_Core_BAO_UFGroup::LISTINGS_VISIBILITY );

        $where  = array( );
        $tables = array( );

        foreach ( $this->_fields as $key => $field ) {
            $value = CRM_Utils_Request::retrieve( $field['name'] );
            if ( isset( $value ) ) {
                $this->_fields[$key]['value'] = $value;
                $this->_values[$key] = $value;

                $value = strtolower( $value ); 
                $where[] = 'LOWER(' . $field['where'] . ') = "' . addslashes( $value ) . '"'; 

                list( $tableName, $fieldName ) = explode( '.', $field['where'], 2 ); 
                if ( isset( $tableName ) ) { 
                    $tables[$tableName] = 1; 
                } 
            }
        }

        // get the permissions for this user
        $where[] = CRM_Core_Permission::whereClause( CRM_Core_Permission::VIEW, $tables );

        $clause = null; 
        if ( ! empty( $where ) ) { 
            $clause = implode( ' AND ', $where ); 
        } 

    }

    /** 
     * run this page (figure out the action needed and perform it). 
     * 
     * @return void 
     */ 
    function run( ) {
    }

}

?>
