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

require_once 'CRM/Campaign/Form/Petition.php';

/**
 * form to process actions on the group aspect of Custom Data
 */
class CRM_Campaign_Form_Petition_Custom extends CRM_Core_Form

{
    /**
     * the id of the survey (petition) we are proceessing
     *
     * @var int
     * @protected
     */
    public $_surveyId;

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        require_once "CRM/Core/BAO/UFGroup.php";
        require_once "CRM/Contact/BAO/ContactType.php";
        $types    = array_merge( array( 'Contact', 'Individual'),
                                 CRM_Contact_BAO_ContactType::subTypes( 'Individual' ) );
        
        $profiles = CRM_Core_BAO_UFGroup::getProfiles( $types ); 

        if ( empty( $profiles ) ) {
            $this->assign( 'noProfile', true );
        }

        $this->add( 'select', 'custom_pre_id' , ts('Include Profile') . '<br />' . ts('(top of page)'), array('' => ts('- select -')) + $profiles );
        $this->add( 'select', 'custom_post_id', ts('Include Profile') . '<br />' . ts('(bottom of page)'), array('' => ts('- select -')) + $profiles );
        
		$this->addButtons(array(
                                array ('type'      => 'upload',
                                       'name'      => ts('Save'),
                                       'isDefault' => true),
                                array ('type'      => 'cancel',
                                       'name'      => ts('Cancel')),
                                )
                          ); 
    }

    /** 
     * This function sets the default values for the form. Note that in edit/view mode 
     * the default values are retrieved from the database 
     * 
     * @access public 
     * @return void 
     */ 
    function setDefaultValues() 
    { 
    	//get the survey id
        $this->_surveyId 	= CRM_Utils_Request::retrieve('sid', 'Positive', $this );
            
        require_once 'CRM/Core/BAO/UFJoin.php';

        $ufJoinParams = array( 'entity_table' => 'civicrm_survey',  
                               'entity_id'    => $this->_surveyId );
        list( $defaults['custom_pre_id'],
              $defaults['custom_post_id'] ) = 
            CRM_Core_BAO_UFJoin::getUFGroupIds( $ufJoinParams ); 
        
        return $defaults;
    }

    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
        // get the submitted form values.
        $params = $this->controller->exportValues( $this->_name );

        if ($this->_action & CRM_Core_Action::UPDATE) {
            $params['id'] = $this->_id;
        }

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
         
        // also update uf join table
        $ufJoinParams = array( 'is_active'    => 1, 
                               'module'       => 'CiviCampaign',
                               'entity_table' => 'civicrm_survey', 
                               'entity_id'    => $this->_surveyId );

        require_once 'CRM/Core/BAO/UFJoin.php';
        // first delete all past entries
        CRM_Core_BAO_UFJoin::deleteAll( $ufJoinParams );

        if ( ! empty( $params['custom_pre_id'] ) ) {
            $ufJoinParams['weight'     ] = 1;
            $ufJoinParams['uf_group_id'] = $params['custom_pre_id'];
            CRM_Core_BAO_UFJoin::create( $ufJoinParams );
        }

        unset( $ufJoinParams['id'] );

        if ( ! empty( $params['custom_post_id'] ) ) {
            $ufJoinParams['weight'     ] = 2; 
            $ufJoinParams['uf_group_id'] = $params['custom_post_id'];  
            CRM_Core_BAO_UFJoin::create( $ufJoinParams ); 
        }

        $transaction->commit( ); 
    }

}


