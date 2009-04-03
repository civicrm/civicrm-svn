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

require_once 'CRM/Case/Form/Task.php';

/**
 * This class provides the functionality to restore a group of
 * participations. This class provides functionality for the actual
 * deletion.
 */
class CRM_Case_Form_Task_Restore extends CRM_Case_Form_Task 
{
    /**
     * Are we operating in "single mode", i.e. deleting one
     * specific case?
     *
     * @var boolean
     */
    protected $_single = false;
    
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        parent::preProcess( );
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        $this->addDefaultButtons( ts( 'Restore Cases' ), 'done' );
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess( ) 
    {
        $restoredCases = 0;
        require_once 'CRM/Case/BAO/Case.php';
        foreach ( $this->_caseIds as $caseId ) {
            if ( CRM_Case_BAO_Case::restoreCase( $caseId ) ) {
                $restoredCases++;
            }
        }

        $status = array(
                        ts( 'Restored Case(s): %1',         array( 1 => $restoredCases ) ),
                        ts( 'Total Selected Case(s): %1',   array( 1 => count($this->_caseIds ) ) ),
                        );
        CRM_Core_Session::setStatus( $status );

    }
}


