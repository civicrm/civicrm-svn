<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Contact/Form/Search/Custom/Base.php';

class CRM_Contact_Form_Search_Custom_FindVoters
   extends    CRM_Contact_Form_Search_Custom_Base
   implements CRM_Contact_Form_Search_Interface {

    function __construct( &$formValues ) {
        parent::__construct( $formValues );

        $this->_columns = array(
                                 ts('Contact Name')   => 'display_name',
                                 ts('Street Number')  => 'street_number',
                                 ts('Street Address') => 'street_address',
                                 ts('City')           => 'city',
                                 ts('Postal Code')    => 'postal_code',
                                 ts('State')          => 'state_province',
                                 ts('Country')        => 'country',
                                 ts('Email')          => 'email',
                                 ts('Phone')          => 'phone' );

        $params           =& CRM_Contact_BAO_Query::convertFormValues( $this->_formValues );
        $returnProperties = array( );
        foreach ( $this->_columns as $name => $field ) {
            $returnProperties[$field] = 1;
        }

        $this->_query = new CRM_Contact_BAO_Query( $params, $returnProperties, null,
                                                    false, false, 1, false, false );

    }

    function buildForm( &$form ) {
        
        $form->add( 'text', 'sort_name', ts( 'Contact Name' ), true );
        $form->add( 'text', 'street_number', ts( 'Street Number' ), true );
        $form->add( 'text', 'street_address', ts( 'Street Address' ), true );
        $form->add( 'text', 'city', ts( 'City' ), true );
        
        $form->assign( 'elements', array( 'sort_name', 'street_number', 'street_address', 'city' ) );
        $this->setTitle('Find Voters');
    }

    // function summary( ) {
    //     $summary = array( 'summary' => 'This is a summary',
    //                       'total' => 50.0 );
    //     return $summary;
    // }


    function count( ) {
        return $this->_query->searchQuery( 0, 0, null, true );
    } 

    function all( $offset = 0, $rowcount = 0, $sort = null,
                  $includeContactIDs = false ) {

        return $this->_query->searchQuery( $offset, $rowCount, $sort,
                                           false, $includeContactIDs,
                                           false, false, true );

    }
    
    function from( ) {
        return $this->_query->_fromClause;
    }

    function where( $includeContactIDs = false ) {

        if ( $whereClause = $this->_query->whereClause( ) ) {
            return $whereClause;
        }
        return ' (1) ' ;
    }

    function templateFile( ) {
        return 'CRM/Contact/Form/Search/Custom/FindVoters.tpl';
    }

    function setTitle( $title ) {
            CRM_Utils_System::setTitle( $title );
    }

}


