<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Campaign/BAO/Survey.php';

/**
 * This class generates form components for processing a survey 
 * 
 */

class CRM_Campaign_Form_Survey extends CRM_Core_Form
{
    /**
     * The id of the object being edited
     *
     * @var int
     */
    protected $_surveyId;

    /**
     * The id of the object being edited
     *
     * @var int
     */
    protected $_resultId;
    
    /**
     * action
     *
     * @var int
     */
    protected $_action;
    
    /**
     * Function to set variables up before form is built
     * 
     * @param null
     * 
     * @return void
     * @access public
     */

    const
        NUM_OPTION = 11;
    
    public function preProcess()
    {
        if ( !CRM_Core_Permission::check( 'administer CiviCampaign' ) ) {
            CRM_Utils_System::permissionDenied( );
        }
        $this->_action   = CRM_Utils_Request::retrieve('action', 'String', $this );
        
        if ( $this->_action & ( CRM_Core_Action::UPDATE | CRM_Core_Action::DELETE ) ) {
            $this->_surveyId = CRM_Utils_Request::retrieve('id', 'Positive', $this, true);
        }

        $session = CRM_Core_Session::singleton();
        $url     = CRM_Utils_System::url('civicrm/campaign', 'reset=1&subPage=survey'); 
        $session->pushUserContext( $url );

        $this->assign( 'action', $this->_action );
    }
    
    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @param null
     * 
     * @return array    array of default values
     * @access public
     */
    function setDefaultValues()
    {
        $defaults = array();

        if ( $this->_surveyId ) {
            require_once 'CRM/Core/BAO/UFJoin.php';
            
            $params = array( 'id' => $this->_surveyId );
            CRM_Campaign_BAO_Survey::retrieve( $params, $defaults );
            
            if ( CRM_Utils_Array::value('result_id', $defaults) &&
                 CRM_Utils_Array::value('recontact_interval', $defaults) ) {
                require_once 'CRM/Core/OptionValue.php';
                
                $resultId          = $defaults['result_id'];
                $recontactInterval = unserialize($defaults['recontact_interval']);

                unset($defaults['recontact_interval']);
                $defaults['option_group_id'] = $resultId;

                $values = array( );
                $groupParams = array( 'id' => $resultId );
                CRM_Core_OptionValue::getValues($groupParams, $values);
                if ( !empty($values) ) {
                    $i = 1;
                    foreach( $values as $id => $opValue ) {
                        if ( $i > self::NUM_OPTION ) {
                            break;
                        }
                        $defaults['option_label['.$i.']'] = $opValue['label'];
                        $defaults['option_value['.$i.']'] = $opValue['value'];
                        $defaults['option_weight['.$i.']'] = $opValue['weight'];
                        if ( CRM_Utils_Array::value( $opValue['value'], $recontactInterval) ) {
                            $defaults['option_interval['.$i.']'] = $recontactInterval[$opValue['value']];
                        }
                        $i++;
                    }
                }
            } 
                
            $ufJoinParams = array( 'entity_table' => 'civicrm_survey',
                                   'entity_id'    => $this->_surveyId,
                                   'weight'       => 1);

            if ( $ufGroupId = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams ) ) {
                $defaults['profile_id'] = $ufGroupId;
            }
        }
        if ( !isset($defaults['is_active']) ) {
            $defaults['is_active'] = 1;
        }

        $defaultSurveys = CRM_Campaign_BAO_Survey::getSurvey(false, false, true);
        if ( !isset($defaults['is_default'] ) && empty($defaultSurveys) ) {
            $defaults['is_default'] = 1;  
        }
        return $defaults;
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

        if ( $this->_action & CRM_Core_Action::DELETE ) {
            
            $this->addButtons( array(
                                     array ( 'type'      => 'next',
                                             'name'      => ts('Delete'),
                                             'isDefault' => true   ),
                                     array ( 'type'      => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );
            return;
        }

        require_once 'CRM/Event/PseudoConstant.php';
        require_once 'CRM/Core/BAO/UFGroup.php';
       
        $this->add('text', 'title', ts('Survey Title'), CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'title'), true );

        $surveyActivityTypes = CRM_Campaign_BAO_Survey::getSurveyActivityType( );
        // Activity Type id
        $this->add('select', 'activity_type_id', ts('Select Activity Type'), array( '' => ts('- select -') ) + $surveyActivityTypes, true );
        
        // Campaign id
        require_once 'CRM/Campaign/BAO/Campaign.php';
        $campaigns = CRM_Campaign_BAO_Campaign::getAllCampaign( );
        $this->add('select', 'campaign_id', ts('Select Campaign'), array( '' => ts('- select -') ) + $campaigns );
        
        $customProfiles = CRM_Core_BAO_UFGroup::getProfiles( array('Activity') );
        // custom group id
        $this->add('select', 'profile_id', ts('Select Profile'), 
                   array( '' => ts('- select -')) + $customProfiles );

        
        if ( $this->_surveyId ) {
            $params = array( 'id' => $this->_surveyId );
            CRM_Campaign_BAO_Survey::retrieve( $params, $defaults );
            if ( $defaults['result_id'] ) {
                $this->_resultId = $defaults['result_id'];
            }
        } 

        // form fields of Custom Option rows
        $defaultOption = array();
        require_once 'CRM/Core/ShowHideBlocks.php';
        $_showHide = new CRM_Core_ShowHideBlocks('','');
        for($i = 1; $i <= self::NUM_OPTION; $i++) {
            
            //the show hide blocks
            $showBlocks = 'optionField_'.$i;
            if ($i > 2) {
                $_showHide->addHide($showBlocks);
                if ($i == self::NUM_OPTION)
                    $_showHide->addHide('additionalOption');
            } else {
                $_showHide->addShow($showBlocks);
            }
            
            $optionAttributes =& CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_OptionValue' );
            // label
            $this->add('text','option_label['.$i.']', ts('Label'),
                       $optionAttributes['label']);

            // value
            $this->add('text', 'option_value['.$i.']', ts('Value'),
                       $optionAttributes['value'] );

            // weight
            $this->add('text', "option_weight[$i]", ts('Order'),
                       $optionAttributes['weight']);
            
            $this->add('text', 'option_interval['.$i.']', ts('Recontact Interval'),
                       CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'release_frequency') );
            
            $defaultOption[$i] = $this->createElement('radio', null, null, null, $i);

        }

        //default option selection
        $this->addGroup($defaultOption, 'default_option');
        
        $_showHide->addToTemplate();      

        // script / instructions
        $this->add( 'textarea', 'instructions', ts('Instructions for volunteers'), array( 'rows' => 5, 'cols' => 40 ) );
        
        // release frequency
        $this->add('text', 'release_frequency', ts('Release Frequency'), CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'release_frequency') );

        $this->addRule('release_frequency', ts('Frequenct interval should be a positive number') , 'positiveInteger');

        // max number of contacts
        $this->add('text', 'max_number_of_contacts', ts('Maximum number of contacts '), CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'max_number_of_contacts') );

        $this->addRule('max_number_of_contacts', ts('Maximum number of contacts should be a positive number') , 'positiveInteger');
        
        // default number of contacts
        $this->add('text', 'default_number_of_contacts', ts('Default number of contacts'), CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'default_number_of_contacts') );
        $this->addRule('default_number_of_contacts', ts('Default number of contacts should be a positive number') , 'positiveInteger');    
        
        // is active ?
        $this->add('checkbox', 'is_active', ts('Is Active?'));
        
        // is default ?
        $this->add('checkbox', 'is_default', ts('Is Default?'));

        // add buttons
        $this->addButtons(array(
                                array ('type'      => 'next',
                                       'name'      => ts('Save'),
                                       'isDefault' => true),
                                array ('type'      => 'next',
                                       'name'      => ts('Save and New'),
                                       'subName'   => 'new'),
                                array ('type'      => 'cancel',
                                       'name'      => ts('Cancel')),
                                )
                          ); 
        
        // add a form rule to check default value
        $this->addFormRule( array( 'CRM_Campaign_Form_Survey', 'formRule' ),$this );

    }
    
    /**
     * global validation rules for the form
     *
     */
    static function formRule( $fields, $files, $form ) {
        
        $errors = array( );
        
                //capture duplicate Custom option values
            if ( ! empty($fields['option_value']) ) {
                $countValue = count($fields['option_value']);
                $uniqueCount = count(array_unique($fields['option_value']));
                
                if ( $countValue > $uniqueCount) {
                    
                    $start=1;
                    while ($start < self::NUM_OPTION) { 
                        $nextIndex = $start + 1;
                            
                        while ($nextIndex <= self::NUM_OPTION) {
                            
                            if ( $fields['option_value'][$start] == $fields['option_value'][$nextIndex] &&
                                 !empty($fields['option_value'][$nextIndex]) ) {

                                $errors['option_value['.$start.']']     = ts( 'Duplicate Option values' );
                                $errors['option_value['.$nextIndex.']'] = ts( 'Duplicate Option values' );
                                $_flagOption = 1;
                            }
                            $nextIndex++;
                        }
                        $start++;
                    }
                }
            }
            
            //capture duplicate Custom Option label
            if ( ! empty( $fields['option_label'] ) ) {
                $countValue = count($fields['option_label']);
                $uniqueCount = count(array_unique($fields['option_label']));
                
                if ( $countValue > $uniqueCount) {
                    $start=1;
                    while ($start < self::NUM_OPTION) { 
                        $nextIndex = $start + 1;
                        
                        while ($nextIndex <= self::NUM_OPTION) {
                            
                            if ( $fields['option_label'][$start] == $fields['option_label'][$nextIndex] && !empty($fields['option_label'][$nextIndex]) ) {
                                $errors['option_label['.$start.']']     =  ts( 'Duplicate Option label' );
                                $errors['option_label['.$nextIndex.']'] = ts( 'Duplicate Option label' );
                                $_flagOption = 1;
                            }
                            $nextIndex++;
                        }
                        $start++;
                    }
                }
            }

            for($i=1; $i<= self::NUM_OPTION; $i++) {
                if (!$fields['option_label'][$i]) {
                    if ($fields['option_value'][$i]) {
                        $errors['option_label['.$i.']'] = ts( 'Option label cannot be empty' );
                        $_flagOption = 1;
                    } else {
                        $_emptyRow = 1;
                    }
                } else if (!strlen(trim($fields['option_value'][$i]))) {
                        if (!$fields['option_value'][$i]) {
                            $errors['option_value['.$i.']'] = ts( 'Option value cannot be empty' );
                            $_flagOption = 1;
                        }
                } else if (!strlen(trim($fields['option_interval'][$i]))) {
                    if (!$fields[''][$i]) {
                            $errors['option_interval['.$i.']'] = ts( 'Recontact Interval cannot be empty' );
                            $_flagOption = 1;
                    }
                }
                if ( CRM_Utils_Array::value($i, $fields['option_interval']) && !CRM_Utils_Rule::integer( $fields['option_interval'][$i] ) ) {
                            $_flagOption = 1;
                            $errors['option_interval['.$i.']'] = ts( 'Please enter a valid integer.' );
                }
                
            }

        return empty($errors) ? true : $errors;
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
        // store the submitted values in an array
        $params = $this->controller->exportValues( $this->_name );
               
        $session = CRM_Core_Session::singleton( );

        $params['last_modified_id'] = $session->get( 'userID' );
        $params['last_modified_date'] = date('YmdHis');
        
        require_once 'CRM/Core/BAO/OptionValue.php';
        require_once 'CRM/Core/BAO/OptionGroup.php';

        if ( $this->_surveyId ) {

            if ( $this->_action & CRM_Core_Action::DELETE ) {
                CRM_Campaign_BAO_Survey::del( $this->_surveyId );
                CRM_Core_Session::setStatus(ts(' Survey has been deleted.'));
                $session->replaceUserContext( CRM_Utils_System::url('civicrm/campaign', 'reset=1&subPage=survey' ) ); 
                return;
            }

            $params['id'] = $this->_surveyId;

        } else { 
            $params['created_id']   = $session->get( 'userID' );
            $params['created_date'] = date('YmdHis');
        } 

        $params['is_active' ] = CRM_Utils_Array::value('is_active', $params, 0);
        $params['is_default'] = CRM_Utils_Array::value('is_default', $params, 0);

        $recontactInterval =  array( );

        if ( $this->_resultId ) {
            $optionValue = new CRM_Core_DAO_OptionValue( );
            $optionValue->option_group_id = $this->_resultId;
            $optionValue->delete();

            $params['result_id'] = $this->_resultId;
            
        } else {
            $opGroupName = 'civicrm_survey_'.rand(10,1000).'_'.date( 'YmdHis' );
            
            $optionGroup            = new CRM_Core_DAO_OptionGroup( );
            $optionGroup->name      =  $opGroupName;
            $optionGroup->label     =  $params['title'];
            $optionGroup->is_active = 1;
            $optionGroup->save( );

            $params['result_id'] = $optionGroup->id;
        }

        require_once 'CRM/Core/BAO/OptionValue.php';
        foreach ($params['option_value'] as $k => $v) {
            if (strlen(trim($v))) {
                $optionValue                  = new CRM_Core_DAO_OptionValue( );
                $optionValue->option_group_id = $params['result_id'];
                $optionValue->label           = $params['option_label'][$k];
                $optionValue->name            = CRM_Utils_String::titleToVar( $params['option_label'][$k] );
                $optionValue->value           = trim($v);
                $optionValue->weight          = $params['option_weight'][$k];
                $optionValue->is_active       = 1;
                
                if ( CRM_Utils_Array::value('default_option', $params) &&
                     $params['default_option'] == $k ) {
                    $optionValue->is_default = 1;
                }
                
                $optionValue->save( );
                $recontactInterval[$optionValue->value]  = $params['option_interval'][$k];
            }
        }
        
        $params['recontact_interval'] = serialize($recontactInterval);
        $surveyId = CRM_Campaign_BAO_Survey::create( $params  );
        
        require_once 'CRM/Core/BAO/UFJoin.php';
        
        // also update the ProfileModule tables 
        $ufJoinParams = array( 'is_active'    => 1, 
                               'module'       => 'CiviCampaign',
                               'entity_table' => 'civicrm_survey', 
                               'entity_id'    => $surveyId->id );
        
        // first delete all past entries
        if ( $this->_surveyId ) {
            CRM_Core_BAO_UFJoin::deleteAll( $ufJoinParams );
        }    
        if ( CRM_Utils_Array::value('profile_id' , $params) ) {

            $ufJoinParams['weight'     ] = 1;
            $ufJoinParams['uf_group_id'] = $params['profile_id'];
            CRM_Core_BAO_UFJoin::create( $ufJoinParams ); 
        }
        
        if( ! is_a( $surveyId, 'CRM_Core_Error' ) ) {
            CRM_Core_Session::setStatus(ts('Survey has been saved.'));
        }
        
        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName == $this->getButtonName( 'next', 'new' ) ) {
            CRM_Core_Session::setStatus(ts(' You can add another Survey.'));
            $session->replaceUserContext( CRM_Utils_System::url('civicrm/survey/add', 'reset=1&action=add' ) );
        } else {
            $session->replaceUserContext( CRM_Utils_System::url('civicrm/campaign', 'reset=1&subPage=survey' ) ); 
        }
    }
 }

