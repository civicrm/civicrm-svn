<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.8                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
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

/**
 * form to process actions on Price Sets
 */
class CRM_Price_Form_Set extends CRM_Core_Form {

    /**
     * the set id saved to the session for an update
     *
     * @var int
     * @access protected
     */
    protected $_id;

    /**
     * Function to set variables up before form is built
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function preProcess()
    {
        require_once 'CRM/Core/BAO/PriceSet.php';
        // current set id
        $this->_id = $this->get('id');

        // setting title for html page
        if ($this->_action == CRM_Core_Action::UPDATE) {
            $title = CRM_Core_BAO_PriceSet::getTitle($this->_id);
            CRM_Utils_System::setTitle(ts('Edit %1', array(1 => $title)));
        } else if ($this->_action == CRM_Core_Action::VIEW) {
            $title = CRM_Core_BAO_PriceSet::getTitle($this->_id);
            CRM_Utils_System::setTitle(ts('Preview %1', array(1 => $title)));
        } else {
            CRM_Utils_System::setTitle(ts('New Price Set'));
        }
    }
     
    /**
     * global form rule
     *
     * @param array $fields  the input form values
     * @param array $files   the uploaded files if any
     * @param array $options additional user data
     *
     * @return true if no errors, else array of errors
     * @access public
     * @static
     */
    static function formRule(&$fields, &$files, $options) {
        $errors = array();

        //checks the given price set doesnot start with digit
        $title = $fields['title']; 
        $asciiValue = ord($title{0});//gives the ascii value
        if($asciiValue>=48 && $asciiValue<=57) {
            $errors['title'] = ts("Set's Name should not start with digit");
        } 
        return empty($errors) ? true : $errors;
    }
    
    

    /**
     * This function is used to add the rules (mainly global rules) for form.
     * All local rules are added near the element
     *
     * @param null
     * 
     * @return void
     * @access public
     * @see valid_date
     */
    function addRules( )
    {
        $this->addFormRule( array( 'CRM_Price_Form_Set', 'formRule' ) );
    }
    
    /**
     * Function to actually build the form
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        $this->applyFilter('__ALL__', 'trim');

        // title
        $this->add('text', 'title', ts('Set Name'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_PriceSet', 'title'), true);
        $this->addRule( 'title', ts('Name already exists in Database.'),
                        'objectExists', array( 'CRM_Core_DAO_PriceSet', $this->_id, 'title' ) );

        // help text
        $this->add('textarea', 'help_pre',  ts('Pre-form Help'),  CRM_Core_DAO::getAttribute('CRM_Core_DAO_PriceSet', 'help_pre'));
        $this->add('textarea', 'help_post',  ts('Post-form Help'),  CRM_Core_DAO::getAttribute('CRM_Core_DAO_PriceSet', 'help_post'));

        // is this set active ?
        $this->addElement('checkbox', 'is_active', ts('Is this Price Set active?') );

        // $this->addFormRule(array('CRM_Price_Form_Set', 'formRule'));
        $this->addButtons(array(
                                array ( 'type'      => 'next',
                                        'name'      => ts('Save'),
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                        'isDefault' => true   ),
                                array ( 'type'      => 'cancel',
                                        'name'      => ts('Cancel') ),
                                )
                          );

        // views are implemented as frozen form
        if ($this->_action & CRM_Core_Action::VIEW) {
            $this->freeze();
            $this->addElement('button', 'done', ts('Done'), array('onclick' => "location.href='civicrm/admin/price?reset=1&action=browse'"));
        }
    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @param null
     * 
     * @return array   array of default values
     * @access public
     */
    function setDefaultValues()
    {
        $defaults = array();
    
        if ($this->_action == CRM_Core_Action::ADD) {
            $defSet =& new CRM_Core_DAO_PriceSet();
            $defSet->domain_id = CRM_Core_Config::domainID( );
            $defSet->orderBy('title');
            $defSet->find( );
            
        }

        if (isset($this->_id)) {
            $params = array('id' => $this->_id);
            CRM_Core_BAO_PriceSet::retrieve($params, $defaults);
            
        } else {
            $defaults['is_active'] = 1;
        }

        return $defaults;
    }
    
    /**
     * Process the form
     * 
     * @param null
     * 
     * @return void
     * @access public
     */
    public function postProcess()
    {
        // get the submitted form values.
        $params = $this->controller->exportValues('Set');
        
        // create price set dao, populate fields and then save.           
        $set =& new CRM_Core_DAO_PriceSet();
        $set->title            = $params['title'];
        $set->name             = CRM_Utils_String::titleToVar($params['title']);
        $set->help_pre         = $params['help_pre'];
        $set->help_post        = $params['help_post'];
        $set->is_active        = CRM_Utils_Array::value('is_active'      , $params, false);
        $set->domain_id        = CRM_Core_Config::domainID( );

        if ($this->_action & CRM_Core_Action::UPDATE) {
            $set->id = $this->_id;
        }
        $set->save();

        if ($this->_action & CRM_Core_Action::UPDATE) {
            CRM_Core_Session::setStatus(ts('Your Set "%1" has been saved.', array(1 => $set->title)));
        } else {
            $url = CRM_Utils_System::url( 'civicrm/admin/price/field', 'reset=1&action=add&gid=' . $set->id);
            CRM_Core_Session::setStatus(ts('Your Set "%1" has been added. You can <a href="%2">add fields</a> to this set now.', array(1 => $set->title, 2 => $url)));
        }
    }
}
?>
