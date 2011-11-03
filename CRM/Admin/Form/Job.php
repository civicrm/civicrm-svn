<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id: $
 *
 */

require_once 'CRM/Admin/Form.php';

/**
 * 
 */
class CRM_Admin_Form_Job extends CRM_Admin_Form
{
    protected $_id     = null;

    function preProcess( ) {
        parent::preProcess( );

        CRM_Utils_System::setTitle(ts('Manage - Scheduled Jobs'));

        if ( $this->_id ) {
            $refreshURL = CRM_Utils_System::url( 'civicrm/admin/job',
                                                 "reset=1&action=update&id={$this->_id}",
                                                 false, null, false );
        } else {
            $refreshURL = CRM_Utils_System::url( 'civicrm/admin/job',
                                                 "reset=1&action=add",
                                                 false, null, false );
        }
        
        $this->assign( 'refreshURL', $refreshURL );

    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( $check = false ) 
    {
        parent::buildQuickForm( );

        if ($this->_action & CRM_Core_Action::DELETE ) { 
            return;
        }
        
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_Job' );

        $this->add( 'text', 'name', ts( 'Name' ),
                    $attributes['name'], true );

        $this->addRule( 'name', ts('Name already exists in Database.'), 'objectExists', array( 'CRM_Core_DAO_Job', $this->_id ) );
        
        $this->add( 'text', 'description', ts( 'Description' ),
                    $attributes['description'] );

        $this->add( 'text', 'command', ts( 'API Call (command)' ),
                    $attributes['command'], true );

        $this->add( 'select', 'run_frequency', ts( 'Run frequency' ),
                    array( 'Daily' => ts('Daily'), 'Hourly' => ts('Hourly'), 'Always' => ts('Every time cron job is run') ) );


        $this->add('textarea', 'parameters', ts('Command parameters'), 
                           "cols=50 rows=6" );
                           
        // is this job active ?
        $this->add('checkbox', 'is_active' , ts('Is this Scheduled Job active?') );

        $this->addFormRule( array( 'CRM_Admin_Form_Job', 'formRule' ) );

    }

    static function formRule( $fields ) {

        $errors = array( );

        require_once 'api/api.php';

        // FIXME: hackish, need better way maybe
        $pcs = split( '_', $fields['command']);
        civicrm_api_include( $pcs[2] );
        
        if( ! function_exists( $fields['command'] ) ) {
            $errors['command'] = ts( 'Given API command is not defined.' );
        }

        if ( ! empty( $errors ) ) {
            return $errors;
        }

        return empty( $errors ) ? true : $errors;
    }

    function setDefaultValues( ) {
        $defaults = array( );

        if ( ! $this->_id ) {
            $defaults['is_active']       = $defaults['is_default'] = 1;
            return $defaults;
        }
        $domainID = CRM_Core_Config::domainID( );
        
        $dao = new CRM_Core_DAO_Job( );
        $dao->id        = $this->_id;
        $dao->domain_id = $domainID;
        if ( ! $dao->find( true ) ) {
            return $defaults;
        }

        CRM_Core_DAO::storeValues( $dao, $defaults );
        
        return $defaults;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        CRM_Utils_System::flushCache( 'CRM_Core_DAO_Job' );

        if ( $this->_action & CRM_Core_Action::DELETE ) {
            CRM_Core_BAO_Job::del( $this->_id );
            CRM_Core_Session::setStatus( ts('Selected Scheduled Job has been deleted.') );
            return;
        }

        $values   = $this->controller->exportValues( $this->_name );
        $domainID = CRM_Core_Config::domainID( );

        $dao = new CRM_Core_DAO_Job( );

        $dao->id         = $this->_id;
        $dao->domain_id  = $domainID;
        $dao->run_frequency = $values['run_frequency'];
        $dao->parameters = $values['parameters'];        
        $dao->name                   = $values['name'];
        $dao->command                   = $values['command'];
        $dao->description            = $values['description'];        
        $dao->is_active  = CRM_Utils_Array::value( 'is_active' , $values, 0 );

        $dao->save( );

    }//end of function

}


