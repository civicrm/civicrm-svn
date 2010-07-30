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
require_once 'CRM/Campaign/Form/Survey.php';

/**
 * This class generates form components for adding a petition 
 * 
 */

class CRM_Campaign_Form_Petition extends CRM_Campaign_Form_Survey
{
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
       
        $this->add('text', 'title', ts('Petition Title'), CRM_Core_DAO::getAttribute('CRM_Campaign_DAO_Survey', 'title'), true );

        $surveyActivityTypes = CRM_Campaign_BAO_Survey::getSurveyActivityType( );
        // Activity Type id
        $this->add('select', 'activity_type_id', ts('Select Activity Type'), array( '' => ts('- select -') ) + $surveyActivityTypes, true );
        
        // Campaign id
        require_once 'CRM/Campaign/BAO/Campaign.php';
        $campaigns = CRM_Campaign_BAO_Campaign::getAllCampaign( );
        $this->add('select', 'campaign_id', ts('Select Campaign'), array( '' => ts('- select -') ) + $campaigns );
        
        $customProfiles = CRM_Core_BAO_UFGroup::getProfiles( array('Individual') );
        // custom group id
        $this->add('select', 'profile_id', ts('Select Profile'), 
                   array( '' => ts('- select -')) + $customProfiles );
                
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
}


?>