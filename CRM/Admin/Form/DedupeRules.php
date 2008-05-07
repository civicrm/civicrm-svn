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

require_once 'CRM/Admin/Form.php';
require_once 'CRM/Dedupe/DAO/Rule.php';
require_once 'CRM/Dedupe/BAO/RuleGroup.php';

/**
 * This class generates form components for DedupeRules
 * 
 */
class CRM_Admin_Form_DedupeRules extends CRM_Admin_Form
{
    const RULES_COUNT = 5;
    protected $_contactType;
    protected $_defaults = array();
    protected $_fields   = array();
    protected $_rgid;

    /**
     * Function to pre processing
     *
     * @return None
     * @access public
     */
    function preProcess()
    {
        $this->_rgid      = CRM_Utils_Request::retrieve('id', 'Positive', $this, false, 0);
        $rgDao            =& new CRM_Dedupe_DAO_RuleGroup();
        $rgDao->domain_id = CRM_Core_Config::domainID();
        $rgDao->id        = $this->_rgid;
        $rgDao->find(true);
        $this->_defaults['threshold'] = $rgDao->threshold;
        $this->_contactType           = $rgDao->contact_type;

        $ruleDao =& new CRM_Dedupe_DAO_Rule();
        $ruleDao->dedupe_rule_group_id = $this->_rgid;
        $ruleDao->find();
        $count = 0;
        while ($ruleDao->fetch()) {
            $this->_defaults["where_$count"]  = "{$ruleDao->rule_table}.{$ruleDao->rule_field}";
            $this->_defaults["length_$count"] = $ruleDao->rule_length;
            $this->_defaults["weight_$count"] = $ruleDao->rule_weight;
            $count++;
        }

        $supported =& CRM_Dedupe_BAO_RuleGroup::supportedFields($this->_contactType);
        foreach($supported as $table => $fields) {
            foreach($fields as $field => $title) {
                $this->_fields["$table.$field"] = $title;
            }
        }
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm()
    {
        for ($count = 0; $count < self::RULES_COUNT; $count++) {
            $this->add('select', "where_$count", ts('Field'), array(null => ts('- none -')) + $this->_fields);
            $this->add('text', "length_$count", ts('Length'), array('class' => 'two', 'style' => 'text-align: right'));
            $this->add('text', "weight_$count", ts('Weight'), array('class' => 'two', 'style' => 'text-align: right'));
        }
        $this->add('text', 'threshold', ts("Weight Threshold to Consider Contacts 'Matching':"), array('class' => 'two', 'style' => 'text-align: right'));
        $this->addButtons(array(
            array('type' => 'next',   'name' => ts('Save'), 'isDefault' => true),
            array('type' => 'cancel', 'name' => ts('Cancel')),
        ));
        $this->assign('contact_type', $this->_contactType);
    }

    function setDefaultValues()
    {
        return $this->_defaults;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $values = $this->exportValues();

        $rgDao            =& new CRM_Dedupe_DAO_RuleGroup();
        $rgDao->domain_id = CRM_Core_Config::domainID();
        $rgDao->id        = $this->_rgid;
        $rgDao->find(true);
        $rgDao->threshold = $values['threshold'];
        $rgDao->save();

        $ruleDao =& new CRM_Dedupe_DAO_Rule();
        $ruleDao->dedupe_rule_group_id = $this->_rgid;
        $ruleDao->delete();
        $ruleDao->free();

        for ($count = 0; $count < self::RULES_COUNT; $count++) {
            list($table, $field) = explode('.', $values["where_$count"]);
            $length = $values["length_$count"] ? $values["length_$count"] : null;
            $weight = $values["weight_$count"];
            if ($table and $field) {
                $ruleDao =& new CRM_Dedupe_DAO_Rule();
                $ruleDao->dedupe_rule_group_id = $this->_rgid;
                $ruleDao->rule_table           = $table;
                $ruleDao->rule_field           = $field;
                $ruleDao->rule_length          = $length;
                $ruleDao->rule_weight          = $weight;
                $ruleDao->save();
                $ruleDao->free();
            }
        }
    }
    
}


