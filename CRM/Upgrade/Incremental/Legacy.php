<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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
 * $Id$
 *
 */


/**
 * This class is a container for legacy upgrade logic which predates
 * the current 'CRM/Incremental/php/*' structure.
 */
class CRM_Upgrade_Incremental_Legacy {

    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_2_2_alpha1( $rev ) {
        for ( $stepID = 1; $stepID <= 4; $stepID++ ) {
            $formName = "CRM_Upgrade_TwoTwo_Form_Step{$stepID}";
            eval( "\$form = new $formName( );" );
            
            $error = null;
            if ( ! $form->verifyPreDBState( $error ) ) {
                if ( ! isset( $error ) ) {
                    $error = "pre-condition failed for current upgrade step $stepID, rev $rev";
                }
                CRM_Core_Error::fatal( $error );
            }
            
            if ( $stepID == 4 ) {
                return;
            }

            $template = CRM_Core_Smarty::singleton( );

            $eventFees = array( );
            $query = "SELECT og.id ogid FROM civicrm_option_group og WHERE og.name LIKE  %1";
            $params = array( 1 => array(  'civicrm_event_page.amount%', 'String' ) );
            $dao = CRM_Core_DAO::executeQuery( $query, $params );
            while ( $dao->fetch( ) ) { 
                $eventFees[$dao->ogid] = $dao->ogid;  
            }
            $template->assign( 'eventFees', $eventFees );    
            
            $form->upgrade( );
            
            if ( ! $form->verifyPostDBState( $error ) ) {
                if ( ! isset( $error ) ) {
                    $error = "post-condition failed for current upgrade step $stepID, rev $rev";
                }
                CRM_Core_Error::fatal( $error );
            }
        }
    }

    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_2_1_2( $rev ) {
        $formName = "CRM_Upgrade_TwoOne_Form_TwoOneTwo";
        eval( "\$form = new $formName( '$rev' );" );
        
        $error = null;
        if ( ! $form->verifyPreDBState( $error ) ) {
            if ( ! isset( $error ) ) {
                $error = "pre-condition failed for current upgrade for $rev";
            }
            CRM_Core_Error::fatal( $error );
        }

        $form->upgrade( );

        if ( ! $form->verifyPostDBState( $error ) ) {
            if ( ! isset( $error ) ) {
                $error = "post-condition failed for current upgrade for $rev";
            }
            CRM_Core_Error::fatal( $error );
        }
    }

    /**
     * This function should check if if need to skip current sql file
     * Name of this function will change according to the latest release 
     *   
     */
    static function upgrade_2_2_alpha3( $rev ) {
        // skip processing sql file, if fresh install -
        if ( ! CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup','mail_protocol','id','name' ) ) {
            $upgrade  = new CRM_Upgrade_Form( );
            $upgrade->processSQL( $rev );
        }
        return true;
    }

    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_2_2_beta1( $rev ) {
        if ( ! CRM_Core_DAO::checkFieldExists( 'civicrm_pcp_block', 'notify_email' ) ) {
            $template = CRM_Core_Smarty::singleton( );
            $template->assign( 'notifyAbsent', true );
        }
        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }

    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_2_2_beta2( $rev ) {
        $template = CRM_Core_Smarty::singleton( );
        if ( ! CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', 
                                            'CRM_Contact_Form_Search_Custom_ZipCodeRange','id','name' ) ) {
            $template->assign( 'customSearchAbsentAll', true );
        } else if ( ! CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', 
                                                   'CRM_Contact_Form_Search_Custom_MultipleValues','id','name' ) ) {
            $template->assign( 'customSearchAbsent', true );
        }
        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }
    
    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_2_2_beta3( $rev ) {
        $template = CRM_Core_Smarty::singleton( );
        if ( ! CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup','custom_data_type','id','name' ) ) {
            $template->assign( 'customDataType', true );
        }
        
        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }
    
    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_3_0_alpha1( $rev ) {

        $threeZero = new CRM_Upgrade_ThreeZero_ThreeZero( );
        
        $error = null;
        if ( ! $threeZero->verifyPreDBState( $error ) ) {
            if ( ! isset( $error ) ) {
                $error = 'pre-condition failed for current upgrade for 3.0.alpha2';
            }
            CRM_Core_Error::fatal( $error );
        }
        
        $threeZero->upgrade( $rev );
    }

    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_3_1_alpha1( $rev ) {

        $threeOne = new CRM_Upgrade_ThreeOne_ThreeOne( );
        
        $error = null;
        if ( ! $threeOne->verifyPreDBState( $error ) ) {
            if ( ! isset( $error ) ) {
                $error = 'pre-condition failed for current upgrade for 3.0.alpha2';
            }
            CRM_Core_Error::fatal( $error );
        }
        
        $threeOne->upgrade( $rev );
    }
    
    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_2_2_7( $rev ) {
        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
        $sql = "UPDATE civicrm_report_instance 
                       SET form_values = REPLACE(form_values,'#',';') ";
        CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

        // make report component enabled by default
        $domain = new CRM_Core_DAO_Domain();
        $domain->selectAdd( );
        $domain->selectAdd( 'config_backend' );
        $domain->find(true);
        if ($domain->config_backend) {
            $defaults = unserialize($domain->config_backend);

            if ( is_array($defaults['enableComponents']) ) {
                $compId   = 
                    CRM_Core_DAO::singleValueQuery( "SELECT id FROM civicrm_component WHERE name = 'CiviReport'" );
                if ( $compId ) {
                    $defaults['enableComponents'][]   = 'CiviReport';
                    $defaults['enableComponentIDs'][] = $compId;

                    CRM_Core_BAO_ConfigSetting::add($defaults);            
                }
            }
        }
    }
  
    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_3_0_2( $rev ) {
        
        $template = CRM_Core_Smarty::singleton( );
        //check whether upgraded from 2.1.x or 2.2.x 
        $inboundEmailID = CRM_Core_OptionGroup::getValue('activity_type', 'Inbound Email', 'name' );
       
        if ( !empty($inboundEmailID) ) {
            $template->assign( 'addInboundEmail', false );
        } else {
            $template->assign( 'addInboundEmail', true ); 
        }

        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }

    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_3_0_4( $rev ) 
    {
        //make sure 'Deceased' membership status present in db,CRM-5636
        $template = CRM_Core_Smarty::singleton( );
        
        $addDeceasedStatus = false;
        $sql = "SELECT max(id) FROM civicrm_membership_status where name = 'Deceased'"; 
        if ( !CRM_Core_DAO::singleValueQuery( $sql ) ) {
            $addDeceasedStatus = true;  
        }
        $template->assign( 'addDeceasedStatus', $addDeceasedStatus ); 
        
        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }

    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_3_1_0 ( $rev ) 
    {
        // upgrade all roles who have 'access CiviEvent' permission, to also have 
        // newly added permission 'edit_all_events', CRM-5472
        $config = CRM_Core_Config::singleton( );
        if ( $config->userSystem->is_drupal ) {
            $roles = user_roles(false, 'access CiviEvent');
            if ( !empty($roles) ) {
                // CRM-7896
                foreach( array_keys($roles) as $rid ) {
                    user_role_grant_permissions($rid, array( 'edit all events' ));
                }
            }
        }

        //make sure 'Deceased' membership status present in db,CRM-5636
        $template = CRM_Core_Smarty::singleton( );
        
        $addDeceasedStatus = false;
        $sql = "SELECT max(id) FROM civicrm_membership_status where name = 'Deceased'"; 
        if ( !CRM_Core_DAO::singleValueQuery( $sql ) ) {
            $addDeceasedStatus = true;  
        }
        $template->assign( 'addDeceasedStatus', $addDeceasedStatus ); 

        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }

    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_3_1_3 ( $rev ) 
    {     
        $threeOne = new CRM_Upgrade_ThreeOne_ThreeOne( );
        $threeOne->upgrade_3_1_3( );
        
        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }

    /**
     * Perform an incremental upgrade
     *
     * @param $rev string, the revision to which we are upgrading (Note: When processing a series of upgrades, this is the immediate upgrade - not the final)
     */
    static function upgrade_3_1_4 ( $rev ) 
    {     
        $threeOne = new CRM_Upgrade_ThreeOne_ThreeOne( );
        $threeOne->upgrade_3_1_4( );
        
        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }
}
