<?php
/**
 +----------------------------------------------------------------------+
 | CiviCRM version 1.0                                                  |
 +----------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                    |
 +----------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                      |
 |                                                                      |
 | CiviCRM is free software; you can redistribute it and/or modify it   |
 | under the terms of the Affero General Public License Version 1,      |
 | March 2002.                                                          |
 |                                                                      |
 | CiviCRM is distributed in the hope that it will be useful, but       |
 | WITHOUT ANY WARRANTY; without even the implied warranty of           |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                 |
 | See the Affero General Public License for more details at            |
 | http://www.affero.org/oagpl.html                                     |
 |                                                                      |
 | A copy of the Affero General Public License has been been            |
 | distributed along with this program (affero_gpl.txt)                 |
 +----------------------------------------------------------------------+
*/

/**
 *
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

require_once 'CRM/Core/SelectValues.php';
require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for tags
 * 
 */
class CRM_Tag_Form_Tag extends CRM_Core_Form
{

    /**
     * The contact id, used when add/edit tag
     *
     * @var int
     */
    protected $_contactId;
    
    function preProcess( ) 
    {
        $this->_contactId   = $this->get('contactId');
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        // get categories for the contact id
        $entityTag =& CRM_Core_BAO_EntityTag::getTag('civicrm_contact', $this->_contactId);
        
        // get the list of all the categories
        $allTag =& CRM_Core_PseudoConstant::tag();
        
        // need to append the array with the " checked " if contact is tagged with the tag
        foreach ($allTag as $tagID => $varValue) {
            $strChecked = '';
            if( in_array($tagID, $entityTag)) {
                $strChecked = 'checked';
            }
            //$tagChk[$tagID] = $this->createElement('checkbox', $tagID, '', '', $strChecked );
            $tagChk[$tagID] = $this->createElement('checkbox', $tagID, '', '', array('onclick' => "return changeRowColor('rowid$tagID')", $strChecked => 'checked','id' => $tagID ) );
        }

        $this->addGroup($tagChk, 'tagList');
        
        $this->assign('tag', $allTag);

        if ( $this->_action & CRM_Core_Action::BROWSE ) {
            $this->freeze();
        } else {

            $this->addButtons( array(
                                     array ( 'type'      => 'next',
                                             'name'      => ts('Update Tags'),
                                             'isDefault' => true   ),
                                     array ( 'type'       => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );
        }
    }

       
    /**
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $data = $aEntityTag = $contactTag = $aTag = $params = array();
        // store the submitted values in an array
        $data = $this->exportValues();

        // get categories for the contact id
        $entityTag =& CRM_Core_BAO_EntityTag::getTag('civicrm_contact', $this->_contactId);

        // get the list of all the categories
        $allTag =& CRM_Core_PseudoConstant::tag();

        // array contains the posted values
        // exportvalues is not used because its give value 1 of the checkbox which were checked by default, 
        // even after unchecking them before submitting them
        // $aContactTag = $data['tagList'];
        $contactTag = $_POST['tagList'];

        // this fix is done to prevent warning generated by array_key_exits incase of empty array is given as input
        if (!is_array($contactTag)) {
            $contactTag[0] = 0;
        }
        
        // this fix is done to prevent warning generated by array_key_exits incase of empty array is given as input
        if (!is_array($entityTag)) {
            $entityTag[0] = 0;
        }

        // check which values has to be inserted/deleted for contact
        foreach ($allTag as $key => $varValue) {
            $params['entity_id'] = $this->_contactId;
            $params['entity_table'] = 'civicrm_contact';
            $params['tag_id'] = $key;
            
            if (array_key_exists($key, $contactTag) && !array_key_exists($key, $entityTag) ) {
                // insert a new record
                    CRM_Core_BAO_EntityTag::add($params);
            } else if (!array_key_exists($key, $contactTag) && array_key_exists($key, $entityTag) ) {
                // delete a record for existing contact
                CRM_Core_BAO_EntityTag::del($params);
            }
        }
        
        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            CRM_Core_Session::setStatus( ts('Your update(s) have been saved.') );
        }
        
    }//end of function

}

?>
