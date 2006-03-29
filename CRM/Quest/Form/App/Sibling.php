<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                  |
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
 | at http://www.openngo.org/faqs/licensing.html                      |
 +--------------------------------------------------------------------+
*/


/**
 * Personal Information Form Page
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo (c) 2005
 * $Id$
 *
 */

require_once 'CRM/Quest/Form/App.php';
require_once 'CRM/Core/OptionGroup.php';

/**
 * This class generates form components for relationship
 * 
 */
class CRM_Quest_Form_App_Sibling extends CRM_Quest_Form_App
{
    protected $_siblingID;

     /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        parent::preProcess();

        $this->_contactId = $this->get('contact_id');
        $this->_siblingID  = CRM_Utils_Array::value( 'siblingID', $this->_options );
    }
    
    /**
     * This function sets the default values for the form. Relationship that in edit/view action
     * the default values are retrieved from the database
     * 
     * @access public
     * @return void
     */
    function setDefaultValues( ) 
    {
        $defaults = array( );
        if ( $this->_siblingID ) {
            require_once 'CRM/Quest/DAO/Person.php';
            $dao = & new CRM_Quest_DAO_Person();
            $dao->id = $this->_siblingID;
            if ($dao->find(true)) {
                CRM_Core_DAO::storeValues( $dao , $defaults );
            }
            $defaults['sibling_relationship_id'] = $defaults['relationship_id'];
            
        }
        return $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $attributes = CRM_Core_DAO::getAttribute('CRM_Quest_DAO_Person');

        $this->addElement( 'text', "first_name",
                           ts('First Name'),
                           $attributes['first_name'] );
        $this->addRule("first_name",ts('Please Enter First Name'),'required');

        $this->addElement( 'text', "last_name",
                           ts('Last Name'),
                           $attributes['last_name'],'required' );
        $this->addRule("last_name",ts('Please Enter Last Name'),'required');
        
        $this->addSelect('sibling_relationship', ts( 'Relationship to you' ),null ,true );
       
        $this->addElement( 'text', "age",
                           ts('Age'),
                           $attributes['age'] );
        $this->addRule('age',ts('Please Enter Age'),'required');

        $this->addRadio( 'lived_with_period_id',
                         ts( 'How long have you lived with this person?' ),
                         CRM_Core_OptionGroup::values( 'years_lived' ));
       
        $this->addElement( 'text', "lived_with_from_age", ts( 'From Age' ),
                           $attributed['lived_with_from_age'] );
        $this->addElement( 'text', "lived_with_to_age", ts( 'To Age' ),
                           $attributed['lived_with_to_age'] );

        $this->addSelect('current_school_level', ts('Year in school'));
     
        $this->addElement( 'text', 'college_name', ts('College attending or attended (if any)'),
                           $attributes['college_name'] );
        $this->addElement( 'text', 'job_occupation',
                           ts('Occupation/Job Title'),
                           $attributes['job_occupation'] );
        $this->addElement( 'textarea',
                           'description',
                           ts('Comments'),
                           $attributes['description'] );
        parent::buildQuickForm();
    }//end of function


    public function postProcess()  
    {
        $params  = $this->controller->exportValues( $this->_name );
       
        $params['relationship_id'] = $params['sibling_relationship_id'];
        $params['contact_id']      = $this->get('contact_id'); 
        $params['is_sibling']      = true;

        require_once 'CRM/Quest/BAO/Person.php';

        $ids = array();
        $ids['id'] = $this->_siblingID;

        $sibling = CRM_Quest_BAO_Person::create( $params , $ids);

        // also fix the form name
        $details = $this->controller->get( 'siblingDetails' );
        $details[$this->_name]['title']   = "Sibling {$params['first_name']} {$params['last_name']}";
        $details[$this->_name]['options']['siblingID'] = $sibling->id;
        $this->controller->set( 'siblingDetails', $details );
    }

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle()
    {
        return $this->_title ? $this->_title : ts('Sibling Information');
    }

    static function &getPages( &$controller ) {
        $details = $controller->get( 'siblingDetails' );
        if ( ! $details ) {
            // now adjust the ones that have a record in them
            require_once 'CRM/Quest/DAO/Person.php';
            $dao = & new CRM_Quest_DAO_Person();
            $dao->contact_id = $controller->get( 'contact_id' );
            $dao->is_parent_guardian = false;
            $dao->find();
            $i = 1;
            while ( $dao->fetch( ) ) {
                $details["Sibling-{$i}"] = array( 'className' => 'CRM_Quest_Form_App_Sibling',
                                                  'title' => trim( "Sibling {$dao->first_name} {$dao->last_name}" ),
                                                  'options' => array( 'index' => $i,
                                                                      'siblingID' => $dao->id ) );
                $i++;
            }

            $totalSiblings = $controller->exportValue( 'Personal', 'number_siblings' );
            if ( is_numeric( $totalSiblings ) && $totalSiblings > $i ) {
                for ( ; $i <= $totalSiblings; $i++ ) {
                    $details["Sibling-{$i}"] = array( 'className' => 'CRM_Quest_Form_App_Sibling', 
                                                      'title'   => "Sibling $i",
                                                      'options' => array( 'index' => $i ) );
                }
            }
            
            $controller->set( 'siblingDetails', $details );
        }

        if ( ! $details ) {
            $details = array( );
        }

        return $details;
    }
}

?>