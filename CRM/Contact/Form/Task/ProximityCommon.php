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

require_once 'CRM/Contact/Form/Task.php';

/**
 * This class provides the functionality to support Proximity Searches
 */
class CRM_Contact_Form_Task_ProximityCommon extends CRM_Contact_Form_Task {
    /**
     * The context that we are working on
     *
     * @var string
     */
    protected $_context;

    /**
     * the groupId retrieved from the GET vars
     *
     * @var int
     */
    protected $_id;

    /**
     * the title of the group
     *
     * @var string
     */
    protected $_title;

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        /*
         * initialize the task and row fields
         */
        parent::preProcess( );
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( $proxSearch ) {
        // is proximity search required (2) or optional (1)?
        $proxRequired  = ( $proxSearch == 2 ? true : false);
        $this->assign('proximity_search', true);

        $this->add( 'text', 'prox_street_address', ts( 'Street Address' ), null, $proxRequired );

        $this->add( 'text', 'prox_city', ts( 'City' ), null, $proxRequired );

        $this->add( 'text', 'prox_postal_code', ts( 'Postal Code' ), null, $proxRequired );

        $this->setDefaultValues( );
        if ( $defaults['prox_country_id'] ) {
            $stateProvince = array( '' => ts('- select -') ) + CRM_Core_PseudoConstant::stateProvinceForCountry( $defaults['prox_country_id'] );
        } else {
            $stateProvince = array( '' => ts('- select -') ) + CRM_Core_PseudoConstant::stateProvince( );
        }
        $this->add('select', 'prox_state_province_id', ts('State/Province'), $stateProvince, $proxRequired);        
        
        $country = array( '' => ts('- select -') ) + CRM_Core_PseudoConstant::country( );
        $this->add( 'select', 'prox_country_id', ts('Country'), $country, $proxRequired );
        
        $this->add( 'text', 'distance', ts( 'Distance (in km)' ), null, $proxRequired );

        // state country js, CRM-5233
        require_once 'CRM/Core/BAO/Address.php';
        $stateCountryMap   = array( );
        $stateCountryMap[] = array( 'state_province' => 'state_province_id',
                                    'country'        => 'country_id' );
        CRM_Core_BAO_Address::addStateCountryMap( $stateCountryMap ); 
        CRM_Core_BAO_Address::fixAllStateSelects( $this, $defaults );   
         
    }
    
    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues() {
        $defaults = array();
        require_once 'CRM/Core/Config.php';
    	$config = CRM_Core_Config::singleton( );
    	$countryDefault = $config->defaultContactCountry;
    	
    	if ($countryDefault) {
    		$defaults['prox_country_id'] = $countryDefault;
    	}
        return $defaults;
    }

    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) 
    {
        $this->addFormRule( array( 'CRM_Contact_Form_task_AddToGroup', 'formRule') );
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $params ) 
    {
        $errors = array( );
       
        if ( $params['group_option'] && !$params['title'] ) {
            $errors['title'] = "Group Name is a required field";
        } else if ( !$params['group_option'] && !$params['group_id']) {
            $errors['group_id'] = "Select Group is a required field.";
        }
        
        return empty($errors) ? true : $errors;
    }
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
        $params = $this->controller->exportValues( );

    }//end of function


}


