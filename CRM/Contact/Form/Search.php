<?php
/*
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
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo 01/15/2005
 * $Id$
 *
 */

/**
 * Files required
 */
require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Core/Selector/Controller.php';
require_once 'CRM/Contact/Selector.php';

/**
 * Base Search / View form for *all* listing of multiple 
 * contacts
 */
class CRM_Contact_Form_Search extends CRM_Form {

    /**
     * Class construtor
     *
     * @param string    $name  name of the form
     * @param CRM_State $state State object that is controlling this form
     * @param int       $mode  Mode of operation for this form
     *
     * @return CRM_Contact_Form_Search
     * @access public
     */
    function __construct($name, $state, $mode = self::MODE_NONE)
    {
        parent::__construct($name, $state, $mode);
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        CRM_PseudoConstant::getGroup();
        CRM_PseudoConstant::getCategory();

        switch($this->_mode) {
        case CRM_Form::MODE_BASIC:
            $this->buildBasicSearchForm();
            break;
        case CRM_Form::MODE_ADVANCED:
            $this->buildAdvancedSearchForm();
            break;        
        }
    }

    /**
     * Build the basic search form
     *
     * @access public
     * @return void
     */
    function buildBasicSearchForm( ) 
    {
        CRM_Error::le_method();

        $contactType = array('any' => ' - any contact - ') + CRM_PseudoConstant::$contactType;
        $this->add('select', 'contact_type', 'Show me.... ', $contactType);

        // add select for groups
        $group = array('any' => ' - any group - ') + CRM_PseudoConstant::$group;
        $this->add('select', 'group', 'in', $group);

        // add select for categories
        $category = array('any' => ' - any category - ') + CRM_PseudoConstant::$category;
        $this->add('select', 'category', 'Category', $category);

        // text for sort_name
        $this->add('text', 'sort_name', 'Name:', CRM_DAO::getAttribute('CRM_Contact_DAO_Contact', 'sort_name') );
        
        // some tasks.. what do we want to do with the selected contacts ?
        $tasks = array( '' => '- actions -' ) + CRM_Contact_Task::$tasks;
        $this->add('select', 'task'   , 'Actions: '    , $tasks    );

        $rows = $this->get( 'rows' );
        if ( is_array( $rows ) ) {
            foreach ( $rows as &$row ) {
                $this->addElement( 'checkbox', $row['checkbox'] );
            }
        }

        // add buttons
        $this->addButtons( array(
                                 array ( 'type'      => 'refresh',
                                         'name'      => 'Search' ,
                                         'isDefault' => true     )
                                 )        
                           );
        
        /*
         * add the go button for the action form, note it is of type 'next' rather than of type 'submit'
         *
         */
        $this->add('submit', $this->getButtonName( 'next' ), 'Perform Action!', array( 'class' => 'form-submit' ) );

        CRM_Error::le_method();
    }

    /**
     * Build the advanced search form
     *
     * @access public
     * @return void
     */
    function buildAdvancedSearchForm() 
    {

        // populate stateprovince, country, locationtype
        CRM_PseudoConstant::getStateProvince();
        CRM_PseudoConstant::getCountry();
        CRM_PseudoConstant::getLocationType();

        // add checkboxes for contact type
        $cb_contact_type = array( );
        foreach (CRM_PseudoConstant::$contactType as $k => $v) {
            $cb_contact_type[] = HTML_QuickForm::createElement('checkbox', $k, null, $v);
        }
        $this->addGroup($cb_contact_type, 'cb_contact_type', 'Show Me....', '<br />');
        
        // checkboxes for groups
        $cb_group = array();
        foreach (CRM_PseudoConstant::$group as $groupID => $groupName) {
            $this->addElement('checkbox', "cb_group[$groupID]", null, $groupName);
        }

        // checkboxes for categories
        $cb_category = array();
        foreach (CRM_PseudoConstant::$category as $categoryID => $categoryName) {
            $cb_category[] = $this->addElement('checkbox', "cb_category[$categoryID]", null, $categoryName);
        }

        // add text box for last name, first name, street name, city
        $this->addElement('text', 'sort_name', 'Contact Name', CRM_DAO::getAttribute('CRM_Contact_DAO_Contact', 'sort_name') );
        $this->addElement('text', 'street_name', 'Street Name:', CRM_DAO::getAttribute('CRM_Contact_DAO_Address', 'street_name'));
        $this->addElement('text', 'city', 'City:',CRM_DAO::getAttribute('CRM_Contact_DAO_Address', 'city'));

        // select for state province
        $stateProvince = array('' => ' - any state/province - ') + CRM_PseudoConstant::$stateProvince;
        $this->addElement('select', 'state_province', 'State/Province', $stateProvince);

        // select for country
        $country = array('' => ' - any country - ') + CRM_PseudoConstant::$country;
        $this->addElement('select', 'country', 'Country', $country);

        // add text box for postal code
        $this->addElement('text', 'postal_code', 'Postal Code', CRM_DAO::getAttribute('CRM_Contact_DAO_Address', 'postal_code') );
        $this->addElement('text', 'postal_code_low', 'Postal Code Range From', CRM_DAO::getAttribute('CRM_Contact_DAO_Address', 'postal_code') );
        $this->addElement('text', 'postal_code_high', 'To', CRM_DAO::getAttribute('CRM_Contact_DAO_Address', 'postal_code') );

        // checkboxes for location type
        $cb_location_type = array();
        $locationType = CRM_PseudoConstant::$locationType + array('any' => 'Any Locations');
        foreach ($locationType as $locationTypeID => $locationTypeName) {
            $cb_location_type[] = HTML_QuickForm::createElement('checkbox', $locationTypeID, null, $locationTypeName);
        }
        $this->addGroup($cb_location_type, 'cb_location_type', 'Include these locations', '&nbsp;');
        
        // checkbox for primary location only
        $this->addElement('checkbox', 'cb_primary_location', null, 'Search for primary locations only');        

        // add components for saving the search
        //$this->addElement('checkbox', 'cb_ss', null, 'Save Search ?');
        //$this->addElement('text', 'ss_name', 'Name', CRM_DAO::getAttribute('CRM_Contact_DAO_SavedSearch', 'name') );
        //$this->addElement('text', 'ss_description', 'Description', CRM_DAO::getAttribute('CRM_Contact_DAO_SavedSearch', 'description') );

        // add the buttons
        $this->addButtons(array(
                                array ( 'type'      => 'refresh',
                                        'name'      => 'Search',
                                        'isDefault' => true   ),
                                array ( 'type'      => 'reset',
                                        'name'      => 'Reset'),
                                )
                          );

    }

    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues() {
        
        CRM_Error::le_method();

        $defaults = array();
        $csv = array();

        $session = CRM_Session::singleton( );        
        $session->getVars($csv, "commonSearchValues");
      
        CRM_Error::debug_var('csv', $csv);

        switch($this->_mode) {
        case CRM_Form::MODE_BASIC:
            $defaults = $csv;
            break;
        case CRM_Form::MODE_ADVANCED:
            //$defaults['sort_name'] = 
            //$csv['cb_contact_type'][$csv['contact_type']] = 1;
            //$csv['cb_group'][$csv['group']] = 1;
            //$csv['cb_category'][$csv['category']] = 1;
            //$defaults = $csv;
            
            $defaults['sort_name'] = $csv['name'];
            if($csv['contact_type']) {
                $defaults['cb_contact_type'] = array($csv['contact_type'] => 1);
            }
            if($csv['group']) {
                $defaults['cb_group'] = array($csv['group'] => 1);
            }
            if($csv['category']) {
                $defaults['cb_category'] = array($csv['category'] => 1);
            }

            break;        
        }

        // $name = $session->get("name", "commonSearchValues");        
        // $contact_type = $session->get("name", "commonSearchValues");        


        CRM_Error::debug_var('defaults', $defaults);

        CRM_Error::ll_method();

        return $defaults;
    }

    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) {
        $this->addFormRule( array( 'CRM_Contact_Form_Search', 'formRule' ) );
    }

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        /*
         * since all output is generated by postProcess which will never be invoked by a GET call
         * we need to explicitly call it if we are invoked by a GET call
         *
         * Scenarios where we make a GET call include
         *  - pageID/sortID change
         *  - user hits reload
         *  - user clicks on menu
        if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
            $this->postProcess( );
        }
         */
        
        CRM_Error::le_method();

        $formValues = $this->controller->exportValues($this->_name);
        $selector = new CRM_Contact_Selector($formValues, $this->_mode);
        $controller = new CRM_Selector_Controller($selector , null, null, CRM_Action::VIEW, $this, CRM_Selector_Controller::TRANSFER );
        if ( $controller->hasChanged( ) ) {
            $this->postProcess( );
        }
        $controller->moveFromSessionToTemplate( );

        CRM_Error::ll_method();
    }

    function postProcess() 
    {

        CRM_Error::le_method();
        // if we are in reset state, i.e. just entered the form, dont display any result
        if($_GET['reset'] == 1) {
            CRM_Error::ll_method();
            return;
        }
        
        
        // check actionName and if next, then do not repeat a search, since we are going to the next page
        list( $pageName, $action ) = $this->controller->getActionName( );
        if ( $action == 'next' ) {
            CRM_Error::ll_method();
            return;
        }

        // get user submitted values
        $formValues = $this->controller->exportValues($this->_name);

        // store the user submitted values in the common search values scope
        $session = CRM_Session::singleton( );
        $session->set("name", $formValues['sort_name'], "commonSearchValues");        

        if ($this->_mode == CRM_Form::MODE_ADVANCED) {
            // important - we need to store the formValues in the session in case we want to save it.
            $session->set("formValues", serialize($formValues), "advancedSearch");
            // store contact_type, group and category
            $session->set("contact_type", $formValues['cb_contact_type'] ? key($formValues['cb_contact_type']) : "", "commonSearchValues");
            $session->set("group", $formValues['cb_group'] ? key($formValues['cb_group']) : "", "commonSearchValues");
            $session->set("category", $formValues['cb_category'] ? key($formValues['cb_category']) : "", "commonSearchValues");
        } else {
            $session->set("contact_type", ($formValues['contact_type']=='any') ? "" : $formValues['contact_type'], "commonSearchValues");
            $session->set("group", ($formValues['group']=='any') ? "" : $formValues['group'], "commonSearchValues");
            $session->set("category", ($formValues['category']=='any') ? "" : $formValues['category'], "commonSearchValues");
        }

        if($ssid=CRM_Request::retrieve('ssid')) {
            CRM_Error::debug_log_message("ssid is set");

            // ssid is set hence we need to set the formValues for it.
            // also we need to set the values in the form...

            $ssDAO = new CRM_Contact_DAO_SavedSearch();
            $ssDAO->id = $ssid;
            $ssDAO->selectAdd();
            $ssDAO->selectAdd('id, form_values');
            if($ssDAO->find(1)) {
                $formValues = $ssDAO->form_values;
            }

        } else {
            CRM_Error::debug_log_message("ssid is not set");
        }

        
        $session->getVars($csv, "commonSearchValues");
        CRM_Error::debug_var('formValues', $formValues);
        CRM_Error::debug_var('csv', $csv);
        

        // CRM_Error::debug_var('session', $session);

        $selector = new CRM_Contact_Selector($formValues, $this->_mode);
        $controller = new CRM_Selector_Controller($selector , null, null, CRM_Action::VIEW, $this, CRM_Selector_Controller::SESSION );
        $controller->run();

        CRM_Error::ll_method();
    }

    /**
     * Add a form rule for this form. If Go is pressed then we must select some checkboxes
     * and an action
     */
    static function formRule( &$fields ) {
        // check actionName and if next, then do not repeat a search, since we are going to the next page
        
        if ( array_key_exists( '_qf_Search_next', $fields ) ) {
            if ( ! CRM_Array::value( 'task', $fields ) ) {
                return array( 'task' => 'Please select a valid action.' );
            }

            foreach ( $fields as $name => $dontCare ) {
                if ( substr( $name, 0, self::CB_PREFIX_LEN ) == self::CB_PREFIX ) {
                    return true;
                }
            }
            return array( 'task' => 'Please select one or more checkboxes to perform the action on.' );
        }
        return true;
    }

}
?>