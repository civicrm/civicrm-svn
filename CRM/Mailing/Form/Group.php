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
 | at http://www.openngo.org/faqs/licensing.html                       |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Social Source Foundation (c) 2005
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';

/**
 * Choose include / exclude groups and mailings
 *
 */
class CRM_Mailing_Form_Group extends CRM_Core_Form {

    /**
     * The number of groups / mailings we will process
     */
    const NUMBER_OF_ELEMENTS = 5;

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
    $template = '
<table{class}>
<tr><td>{unselected}</td><td>{selected}</tr></tr>
<tr><td>{add}</td><td>{remove}</tr></tr>
</table>';
        $groups =& CRM_Core_PseudoConstant::group();
        $inG =& $this->addElement('advmultiselect', 'includeGroups', 
            ts('Include group(s) '), $groups,
            array('size' => 5, 'style' => 'width:240px'));
        $outG =& $this->addElement('advmultiselect', 'excludeGroups', 
            ts('Exclude group(s) '), $groups,
            array('size' => 5, 'style' => 'width:240px'));
        $inG->setButtonAttributes('add', array('value' => ts('Add >>')));;
        $outG->setButtonAttributes('add', array('value' => ts('Add >>')));;
        $inG->setButtonAttributes('remove', array('value' => ts('<< Remove')));;
        $outG->setButtonAttributes('remove', array('value' => ts('<< Remove')));;
//         $inG->setElementTemplate($template);
//         $outG->setElementTemplate($template);
        

        $mailings =& CRM_Mailing_PseudoConstant::completed();
        if (! $mailings) {
            $mailings = array();
        }
        $inM =& $this->addElement('advmultiselect', 'includeMailings', 
            ts('Include mailing(s) '), $mailings,
            array('size' => 5, 'style' => 'width:240px'));
        $outM =& $this->addElement('advmultiselect', 'excludeMailings', 
            ts('Exclude mailing(s) '), $mailings,
            array('size' => 5, 'style' => 'width:240px'));

        $inM->setButtonAttributes('add', array('value' => ts('Add >>')));;
        $outM->setButtonAttributes('add', array('value' => ts('Add >>')));;
        $inM->setButtonAttributes('remove', array('value' => ts('<< Remove')));;
        $outM->setButtonAttributes('remove', array('value' => ts('<< Remove')));;
//         $inM->setElementTemplate($template);
//         $outM->setElementTemplate($template);
        


        $this->addButtons( array(
                                 array ( 'type'      => 'back',
                                         'name'      => ts('<< Previous') ),
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Next >>'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );

        $this->assign('groupCount', count($groups));
        $this->assign('mailingCount', count($mailings));
    }

    public function postProcess() {
        $inGroups = $this->controller->exportValue($this->_name, 'includeGroups');
        $outGroups = $this->controller->exportValue($this->_name, 'excludeGroups');
        $inMailings = $this->controller->exportValue($this->_name, 'includeMailings');
        $outMailings = $this->controller->exportValue($this->_name, 'excludeMailings');
        $groups = array();
        if (is_array($inGroups)) {
            foreach($inGroups as $key => $id) {
                if ($id) {
                    $groups['include'][] = $id;
                }
            }
        }
        if (is_array($outGroups)) {
            foreach($outGroups as $key => $id) {
                if ($id) {
                    $groups['exclude'][] = $id;
                }
            }
        }

        $mailings = array();
        if (is_array($inMailings)) {
            foreach($inMailings as $key => $id) {
                if ($id) {
                    $mailings['include'][] = $id;
                }
            }
        }
        if (is_array($outMailings)) {
            foreach($outMailings as $key => $id) {
                if ($id) {
                    $mailings['exclude'][] = $id;
                }
            }
        }

        $this->set('groups', $groups);
        $this->set('mailings', $mailings);
    }

    /**
     * Display Name of the form
     *
     * @access public
     * @return string
     */
    public function getTitle( ) {
        return ts( 'Select Mailing Recipients' );
    }

}

?>
