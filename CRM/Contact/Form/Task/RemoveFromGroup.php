<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
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

require_once 'CRM/Contact/Form/Task.php';

/**
 * This class provides the functionality to delete a group of
 * contacts. This class provides functionality for the actual
 * addition of contacts to groups.
 */
class CRM_Contact_Form_Task_RemoveFromGroup extends CRM_Contact_Form_Task {
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
        // add select for groups
        $group = array( '' => ts('- select group -')) + CRM_Core_PseudoConstant::group( );
        $groupElement = $this->add('select', 'group_id', ts('Select Group'), $group, true);

        CRM_Utils_System::setTitle( ts('Remove Contacts from Group') );
        $this->addDefaultButtons( ts('Remove from Group') );
    }

    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues() {
        $defaults = array();

        if ( $this->get( 'context' ) === 'smog' ) {
            $defaults['group_id'] = $this->get( 'gid' );
        }
        return $defaults;
    }


    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
        $groupId  =  $this->controller->exportValue( 'RemoveFromGroup', 'group_id'  );
        $group    =& CRM_Core_PseudoConstant::group( );

        list( $total, $removed, $notRemoved ) = CRM_Contact_BAO_GroupContact::removeContactsFromGroup( $this->_contactIds, $groupId );
        $status = array(
                        ts('Removed Contact(s) from %1', array(1 => $group[$groupId])),
                        ts('Total Selected Contact(s): %1', array(1 => $total))
                        );
        if ( $removed ) {
            $status[] = ts('Total Contact(s) removed from group: %1', array(1 => $removed));
        }
        if ( $notRemoved ) {
            $status[] = ts('Total Contact(s) not in group: %1', array(1 => $notRemoved));
        }
        CRM_Core_Session::setStatus( $status );

    }//end of function


}


