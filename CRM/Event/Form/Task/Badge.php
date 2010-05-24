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
        $label = array("5160" => "5160",
                       "5161" => "5161",
                       "5162" => "5162", 
                       "5163" => "5163", 
                       "5164" => "5164", 
                       "8600" => "8600",
                       "L7160" => "L7160",
                       "L7161" => "L7161",
                       "L7163" => "L7163");
        
        $this->add('select', 'label_id', ts('Select Badge format'), array( '' => ts('- select label -')) + $label, true);

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
        $fv = $this->controller->exportValues('Search');
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

        require_once 'CRM/Event/Badge/Chevalet.php';
        $badge = new CRM_Event_Badge_Chevalet( );
        $badge->run( $rows );
    }
    
}
