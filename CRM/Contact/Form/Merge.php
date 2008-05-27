<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Dedupe/Merger.php';

class CRM_Contact_Form_Merge extends CRM_Core_Form
{
    // the id of the contact that tere's a duplicate for; this one will 
    // possibly inherit some of $_oid's properties and remain in the system
    var $_cid         = null;

    // the id of the other contact - the duplicate one that will get deleted
    var $_oid         = null;

    var $_contactType = null;

    // FIXME: QuickForm can't create advcheckboxes with value set to 0 or '0' :(
    // see HTML_QuickForm_advcheckbox::setValues() - but patching that doesn't 
    // help, as QF doesn't put the 0-value elements in exportValues() anyway...
    // to side-step this, we use the below UUID as a (re)placeholder
    var $_qfZeroBug = 'e8cddb72-a257-11dc-b9cc-0016d3330ee9';

    function preProcess()
    {
        require_once 'api/v2/Contact.php';
        require_once 'CRM/Core/BAO/CustomGroup.php';
        require_once 'CRM/Core/OptionGroup.php';
        require_once 'CRM/Core/OptionValue.php';
        if ( ! CRM_Core_Permission::check( 'administer CiviCRM' ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have access to this page' ) );
        }

        $cid   = CRM_Utils_Request::retrieve('cid', 'Positive', $this, false);
        $oid   = CRM_Utils_Request::retrieve('oid', 'Positive', $this, false);

        // ensure that oid is not the current user, if so refuse to do the merge
        $session =& CRM_Core_Session::singleton( );
        if ( $session->get( 'userID' ) == $oid ) {
            require_once 'CRM/Contact/BAO/Contact.php';
            $display_name = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $oid, 'display_name' );
            $message = ts( 'The contact record which is linked to the currently logged in user account - \'%1\' - can not be deleted.',
                           array( 1 => $display_name ) );
            CRM_Core_Error::statusBounce( $message );
        }

        $diffs = CRM_Dedupe_Merger::findDifferences($cid, $oid);

        $mainParams  = array('contact_id' => $cid, 'return.display_name' => 1);
        $otherParams = array('contact_id' => $oid, 'return.display_name' => 1);
        // API 2 has to have the requested fields spelt-out for it
        foreach (CRM_Dedupe_Merger::$validFields as $field) {
            $mainParams["return.$field"] = $otherParams["return.$field"] = 1;
        }
        $main  =& civicrm_contact_get($mainParams);
        $other =& civicrm_contact_get($otherParams);

        $this->assign('contact_type', $main['contact_type']);
        $this->assign('main_name',    $main['display_name']);
        $this->assign('other_name',   $other['display_name']);
        $this->assign('main_cid',     $main['contact_id']);
        $this->assign('other_cid',    $other['contact_id']);

        $this->_cid         = $cid;
        $this->_oid         = $oid;
        $this->_contactType = $main['contact_type'];
        $this->addElement('checkbox', 'toggleSelect', null, null, array('onchange' => "return toggleCheckboxVals('move_',this.form);"));

        require_once "CRM/Contact/DAO/Contact.php";
        $fields =& CRM_Contact_DAO_Contact::fields();

        // FIXME: there must be a better way
        foreach (array('main', 'other') as $moniker) {
            $contact =& $$moniker;
            $specialValues[$moniker] = array('preferred_communication_method' => $contact['preferred_communication_method']);
            $names = array('preferred_communication_method' => array('newName'   => 'preferred_communication_method_display',
                                                                     'groupName' => 'preferred_communication_method'));
            CRM_Core_OptionGroup::lookupValues($specialValues[$moniker], $names);
        }
        foreach (CRM_Core_OptionValue::getFields() as $field => $params) {
            $fields[$field]['title'] = $params['title'];
        }

        if (!isset($diffs['contact'])) $diffs['contact'] = array();
        foreach ($diffs['contact'] as $field) {
            foreach (array('main', 'other') as $moniker) {
                $contact =& $$moniker;
                $value = $contact[$field];
                $label = isset($specialValues[$moniker][$field]) ? $specialValues[$moniker]["{$field}_display"] : $value;
                if ($fields[$field]['type'] == CRM_Utils_Type::T_DATE) {
                    $value = str_replace('-', '', $value);
                    $label = CRM_Utils_Date::customFormat($label);
                } elseif ($fields[$field]['type'] == CRM_Utils_Type::T_BOOLEAN) {
                    if ($label === '0') $label = ts('No');
                    if ($label === '1') $label = ts('Yes');
                }
                $rows["move_$field"][$moniker] = $label;
                if ($moniker == 'other') {
                    if ($value === null) $value = 'null';
                    if ($value === 0 or $value === '0') $value = $this->_qfZeroBug;
                    $this->addElement('advcheckbox', "move_$field", null, null, null, $value);
                }
            }
            $rows["move_$field"]['title'] = $fields[$field]['title'];
        }

        // handle locations
        require_once 'api/v2/Location.php';
        $locations['main']  =& civicrm_location_get($mainParams);
        $locations['other'] =& civicrm_location_get($otherParams);
        foreach (CRM_Core_PseudoConstant::locationType() as $locTypeId => $locTypeName) {
            foreach (array('main', 'other') as $moniker) {
                $location = array();
                foreach ($locations[$moniker] as $loc) {
                    if ($loc['location_type_id'] == $locTypeId) {
                        $location = $loc;
                    }
                }
                if (empty($location)) {
                    $locValue[$moniker] = 0;
                    $locLabel[$moniker] = '[' . ts('EMPTY') . ']';
                } else {
                    $locValue[$moniker] = $locTypeId;
                    foreach (array('email','phone','im','openid') as $fieldType) {
                        if (!isset($location[$fieldType])) $location[$fieldType] = array();
                        foreach ($location[$fieldType] as $field) {
                            $locLabel[$moniker][$fieldType] = $field[$fieldType];
                            
                        }
                    }
                    $locLabel[$moniker]['address'] = $location['address']['display'];
                }
            }
            if ($locValue['other'] != 0) {
                foreach (array('email','phone','im','openid','address') as $fieldType) {
                    $rows["move_location_$fieldType"."_$locTypeId"]['main']  = $locLabel['main'][$fieldType];
                    $rows["move_location_$fieldType"."_$locTypeId"]['other'] = $locLabel['other'][$fieldType];
                    $rows["move_location_$fieldType"."_$locTypeId"]['title'] = ts('Location %1:%2', array(1 => $locTypeName, 2 => $fieldType));
                    $this->addElement('advcheckbox', "move_location_$fieldType"."_$locTypeId", null, null, null, $locValue['other']);
                }
            }
        }

        // handle custom fields
        $mainTree  =& CRM_Core_BAO_CustomGroup::getTree($this->_contactType, $this->_cid, -1);
        $otherTree =& CRM_Core_BAO_CustomGroup::getTree($this->_contactType, $this->_oid, -1);
        if (!isset($diffs['custom'])) $diffs['custom'] = array();
        foreach ($otherTree as $gid => $group) {
            $foundField = false;
            if ( ! isset( $group['fields'] ) ) {

                continue;
            }

            foreach ($group['fields'] as $fid => $field) {
                if (in_array($fid, $diffs['custom'])) {
                    if (!$foundField) {
                        $rows["custom_group_$gid"]['title'] = $group['title'];
                        $foundField = true;
                    }
                    // FIXME: is there a better way than getOptionLabel(), one that does not do a roundtrip to the database?
                    $rows["move_custom_$fid"]['main']  = CRM_Core_BAO_CustomOption::getOptionLabel($fid,  $mainTree[$gid]['fields'][$fid]['customValue']['data'], $field['html_type'], $field['data_type']);
                    $rows["move_custom_$fid"]['other'] = CRM_Core_BAO_CustomOption::getOptionLabel($fid, $otherTree[$gid]['fields'][$fid]['customValue']['data'], $field['html_type'], $field['data_type']);
                    $rows["move_custom_$fid"]['title'] = $field['label'];
                    $value = $field['customValue']['data'] ? $field['customValue']['data'] : $this->_qfZeroBug;
                    $this->addElement('advcheckbox', "move_custom_$fid", null, null, null, $value);
                }
            }
        }

        $this->assign('rows', $rows);

        // add the related tables and unset the ones that don't sport any of the duplicate contact's info
        $relTables = CRM_Dedupe_Merger::relTables();
        $activeRelTables = CRM_Dedupe_Merger::getActiveRelTables($oid);
        foreach ($relTables as $name => $null) {
            if (!in_array($name, $activeRelTables)) {
                unset($relTables[$name]);
                continue;
            }
            $this->addElement('checkbox', "move_$name");
            $relTables[$name]['main_url']  = str_replace('$cid', $cid, $relTables[$name]['url']);
            $relTables[$name]['other_url'] = str_replace('$cid', $oid, $relTables[$name]['url']);
        }
        foreach ($relTables as $name => $null) {
            $relTables["move_$name"] = $relTables[$name];
            unset($relTables[$name]);
        }
        $this->assign('rel_tables', $relTables);
    }
    
    function setDefaultValues()
    {
        return array('deleteOther' => 1);
    }
    
    function addRules()
    {
    }

    public function buildQuickForm()
    {
        CRM_Utils_System::setTitle(ts('Merge Contacts'));
        $this->addButtons(array(
            array('type' => 'next',   'name' => ts('Merge'), 'isDefault' => true),
            array('type' => 'cancel', 'name' => ts('Cancel')),
        ));
    }

    public function postProcess()
    {
        $formValues = $this->exportValues();

        $relTables =& CRM_Dedupe_Merger::relTables();
        $moveTables = array();
        foreach ($formValues as $key => $value) {
            if ($value == $this->_qfZeroBug) $value = '0';
            if ((in_array(substr($key, 5), CRM_Dedupe_Merger::$validFields) or substr($key, 0, 12) == 'move_custom_') and $value != null) {
                $submitted[substr($key, 5)] = $value;
            } elseif (substr($key, 0, 14) == 'move_location_' and $value != null) {
                $locations[substr($key, 14)] = $value;
            } elseif (substr($key, 0, 15) == 'move_rel_table_' and $value == '1') {
                $moveTables = array_merge($moveTables, $relTables[substr($key, 5)]['tables']);
            }
        }

        // FIXME: fix gender, prefix and postfix, so they're edible by createProfileContact()
        $names['gender']            = array('newName' => 'gender_id', 'groupName' => 'gender');
        $names['individual_prefix'] = array('newName' => 'prefix_id', 'groupName' => 'individual_prefix');
        $names['individual_suffix'] = array('newName' => 'suffix_id', 'groupName' => 'individual_suffix');
        CRM_Core_OptionGroup::lookupValues($submitted, $names, true);

        // FIXME: fix custom fields so they're edible by createProfileContact()
        $cgTree =& CRM_Core_BAO_CustomGroup::getTree($this->_contactType, null, -1);
        foreach ($cgTree as $key => $group) {
            if (!isset($group['fields'])) continue;
            foreach ($group['fields'] as $fid => $field) {
                $cFields[$fid]['attributes'] = $field;
            }
        }

        if (!isset($submitted)) $submitted = array();
        foreach ($submitted as $key => $value) {
            if (substr($key, 0, 7) == 'custom_') {
                $fid = (int) substr($key, 7);
                switch ($cFields[$fid]['attributes']['html_type']) {
                case 'File':
                    $customFiles[] = $fid;
                    unset($submitted["custom_$fid"]);
                    break;
                case 'Select Country':
                case 'Select State/Province':
                    $submitted[$key] = CRM_Core_BAO_CustomField::getDisplayValue($value, $fid, $cFields);
                    break;
                default:
                    break;
                }
            }
        }

        // FIXME: the simplest approach to locations
        $locTypes =& CRM_Core_PseudoConstant::locationType();
        if (!isset($locations)) $locations = array();
        foreach ($locations as $field => $locTypeId) {
            $field = substr($field, 0, -2);
            $mainParams = array('contact_id' => $this->_cid, 'location_type' => $locTypeId);
            
            // if the new one is 0, we're done
            if ($locTypeId == 0) continue;
            
            $locComponent = array(
                                  'email'   => 'Email',
                                  'phone'   => 'Phone',
                                  'im'      => 'IM',
                                  'openid'  => 'OpenID',
                                  'address' => 'Address',
                                  ); 
            //delete the existing location component first(if exists).
            eval("\$dao =& new CRM_Core_DAO_$locComponent[$field]();");
            $dao->contact_id = $this->_cid;
            $dao->location_type_id = $locTypeId;
            $dao->find();
            $dao->delete();
            $dao->free();
            //move duplicate contact's location component.
            eval("\$dao =& new CRM_Core_DAO_$locComponent[$field]();");
            $dao->contact_id = $this->_oid;
            $dao->location_type_id = $locTypeId;
            $dao->find();
            while ($dao->fetch()) {
                $dao->contact_id = $this->_cid;
                $dao->update();
            }
            $dao->free();
        }
       
        // handle the related tables
        if (isset($moveTables)) {
            CRM_Dedupe_Merger::moveContactBelongings($this->_cid, $this->_oid, $moveTables);
        }

        // move file custom fields
        // FIXME: move this someplace else (one of the BAOs) after discussing
        // where to, and whether CRM_Core_BAO_File::delete() shouldn't actually,
        // like, delete a file...
        require_once 'CRM/Core/BAO/File.php';
        require_once 'CRM/Core/DAO/CustomField.php';
        require_once 'CRM/Core/DAO/CustomGroup.php';
        require_once 'CRM/Core/DAO/EntityFile.php';
        require_once 'CRM/Core/Config.php';
        $domainId = CRM_Core_Config::domainID();

        if (!isset($customFiles)) $customFiles = array();
        foreach ($customFiles as $customId) {
            list($tableName, $columnName) = CRM_Core_BAO_CustomField::getTableColumnName($customId);

            // get the contact_id -> file_id mapping
            $fileIds = array();
            $sql = "SELECT entity_id, {$columnName} AS file_id FROM {$tableName} WHERE domain_id = {$domainId} AND entity_id IN ({$this->_cid}, {$this->_oid})";
            $dao =& CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
            while ($dao->fetch()) {
                $fileIds[$dao->entity_id] = $dao->file_id;
            }
            $dao->free();

            // delete the main contact's file
            CRM_Core_BAO_File::delete($fileIds[$this->_cid], $this->_cid, $customId);

            // move the other contact's file to main contact
            $sql = "UPDATE {$tableName} SET {$columnName} = {$fileIds[$this->_oid]} WHERE entity_id = {$this->_cid} AND domain_id = {$domainId}";
            CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
            $sql = "UPDATE civicrm_entity_file SET entity_id = {$this->_cid} WHERE entity_table = '{$tableName}' AND file_id = {$fileIds[$this->_oid]}";
            CRM_Core_DAO::executeQuery($sql, CRM_Core_DAO::$_nullArray);
        }

        // move other's belongings and delete the other contact
        CRM_Dedupe_Merger::moveContactBelongings($this->_cid, $this->_oid);
        $otherParams = array('contact_id' => $this->_oid);
        civicrm_contact_delete($otherParams);

        if (isset($submitted)) {
            $submitted['contact_id'] = $this->_cid;
            CRM_Contact_BAO_Contact::createProfileContact($submitted, CRM_Core_DAO::$_nullArray, $this->_cid);
        }
        CRM_Core_Session::setStatus(ts('The contacts have been merged.'));
    }
}


