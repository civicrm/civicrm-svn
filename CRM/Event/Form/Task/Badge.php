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

require_once 'CRM/Event/Form/Task.php';

/**
 * This class helps to print the labels for contacts
 * 
 */
class CRM_Event_Form_Task_Badge extends CRM_Event_Form_Task 
{

    /**
     * Build the form 
     *    
     * @access public
     * @return void
     */
    function buildQuickForm()
    {
        CRM_Utils_System::setTitle( ts('Make Event Badges') );

        //add select for label
        require_once 'CRM/Core/OptionGroup.php';
        $label = CRM_Core_OptionGroup::values('event_badge');

        $this->add('select',
                   'badge_id',
                   ts('Select Name Badge format'),
                   array( '' => ts('- select -')) + $label, true);

        $this->addDefaultButtons( ts('Make Event Badges'));
       
    }
    
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return void
     */
    public function postProcess ( )
    {
        $params = $this->controller->exportValues($this->_name);
        $config = CRM_Core_Config::singleton();

        $values = $this->controller->exportValues( 'Search' ); 

        $queryParams = $this->get( 'queryParams' );

        require_once 'CRM/Event/BAO/Query.php';
        require_once 'CRM/Contact/BAO/Query.php';
        
        $returnProperties =& CRM_Event_BAO_Query::defaultReturnProperties( CRM_Contact_BAO_Query::MODE_EVENT );
        $additionalFields = array( 'first_name', 'last_name', 'middle_name', 'current_employer' );
        foreach ( $additionalFields as $field ) {
            $returnProperties[$field] = 1;
        }

        $query = new CRM_Contact_BAO_Query( $queryParams, $returnProperties, null, false, false,
                                            CRM_Contact_BAO_Query::MODE_EVENT );
        
        list( $select, $from, $where ) = $query->query( );
        if ( empty( $where ) ) {
            $where = "WHERE {$this->_componentClause}";
        } else {
            $where .= " AND {$this->_componentClause}";
        }
        
        $queryString = "$select $from $where";

        $dao = CRM_Core_DAO::executeQuery( $queryString );
        $rows = array( );
        while( $dao->fetch( ) ) {
            $rows[$dao->participant_id] = array( );
            foreach ( $returnProperties as $key => $dontCare ) {
                $rows[$dao->participant_id][$key] = isset( $dao->$key ) ? $dao->$key : null;
            }

        }

        // get the class name from the participantListingID
        require_once 'CRM/Core/OptionGroup.php';
        $className = CRM_Core_OptionGroup::getValue( 'event_badge',
                                                     $params['badge_id'],
                                                     'value',
                                                     'Integer',
                                                     'name' );

        $classFile = str_replace( '_',
                                  DIRECTORY_SEPARATOR,
                                  $className ) . '.php';
        $error = include_once( $classFile );
        if ( $error == false ) {
            CRM_Core_Error::fatal( 'Event Badge code file: ' . $classFile . ' does not exist. Please verify your custom event badge settings in CiviCRM administrative panel.' );
        }

        eval( "\$eventBadgeClass = new $className( );" );
        

        $eventBadgeClass->run( $rows );
    }
    
}
