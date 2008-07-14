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

require_once 'CRM/Upgrade/Form.php';

class CRM_Upgrade_TwoOne_Form_Step1 extends CRM_Upgrade_Form {

    function verifyPreDBState( &$errorMessage ) {
        $errorMessage = ts('Database check failed - the current database is not v2.0.');

        // abort if already 2.1
        if ( $this->checkVersion( '2.1' ) ) {
            $errorMessage = ts('Database check failed - it looks like you have already upgraded to the latest version (v2.1) of the database.');
            return false;
        }

        // check if 2.0 version
        if ( ! $this->checkVersion( '2.0' ) ) {
            return false;
        }

        // check if 2.0 tables exists
        if ( ! CRM_Core_DAO::checkTableExists( 'civicrm_activity' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_activity_assignment') ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_activity_target') ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_address') ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_address') ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_case') ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_case_activity') ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_component') ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_contribution_widget' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_grant' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_group_nesting' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_group_organization' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_loc_block' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_openid' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_openid_associations' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_openid_nonces' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_preferences_date' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_tell_friend' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_timezone' ) ||
             ! CRM_Core_DAO::checkTableExists( 'civicrm_worldregion' )
             ) {
            // db is not 2.0
            $errorMessage .= ' Few 2.0 tables were found missing.';
            return false;
        }
        
        // check fields which MUST be present if a proper 2.0 db
        if ( ! CRM_Core_DAO::checkFieldExists( 'civicrm_activity', 'source_record_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_activity', 'activity_date_time' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_activity', 'status_id' ) ||
             
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contact', 'first_name' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contact', 'last_name' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contact', 'gender_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contact', 'mail_to_household_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contact', 'user_unique_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contact', 'household_name' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contact', 'organization_name' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contribution', 'honor_type_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contribution_page', 'is_pay_later' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_contribution_page', 'pay_later_text' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_country', 'region_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_custom_field', 'column_name' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_custom_field', 'option_group_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_custom_group', 'table_name' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_custom_group', 'is_multiple' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_domain', 'version' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_domain', 'loc_block_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_email', 'contact_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_email', 'location_type_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_email', 'is_billing' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_entity_tag', 'contact_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_event', 'participant_listing_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_event', 'loc_block_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_event', 'receipt_text' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_event_page', 'is_pay_later' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_event_page', 'pay_later_text' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_financial_trxn', 'contribution_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_im', 'contact_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_im', 'location_type_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_membership_payment', 'contribution_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_membership_type', 'receipt_text_signup' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_membership_type', 'receipt_text_renewal' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_option_value', 'component_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_participant_payment', 'contribution_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_payment_processor', 'url_api' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_payment_processor_type', 'url_api_default' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_phone', 'contact_id' ) ||
             ! CRM_Core_DAO::checkFieldExists( 'civicrm_phone', 'location_type_id' ) ||

             ! CRM_Core_DAO::checkFieldExists( 'civicrm_uf_match', 'uf_name' )
             ) {
            // db looks to have stuck somewhere between 2.0 & 2.1
            $errorMessage .= ' Few important fields were found missing in some of the tables.';
            return false;
        }

        // check tables which should not exist for v2.x
        if ( CRM_Core_DAO::checkTableExists( 'civicrm_custom_option' ) ||
             CRM_Core_DAO::checkTableExists( 'civicrm_custom_value' ) ||
             CRM_Core_DAO::checkTableExists( 'civicrm_email_history' ) ||
             CRM_Core_DAO::checkTableExists( 'civicrm_geo_coord' ) ||
             CRM_Core_DAO::checkTableExists( 'civicrm_individual' ) ||
             CRM_Core_DAO::checkTableExists( 'civicrm_location' ) ||
             CRM_Core_DAO::checkTableExists( 'civicrm_meeting' ) ||
             CRM_Core_DAO::checkTableExists( 'civicrm_organization' ) ||
             CRM_Core_DAO::checkTableExists( 'civicrm_phonecall' ) ||
             CRM_Core_DAO::checkTableExists( 'civicrm_sms_history' ) ||
             CRM_Core_DAO::checkTableExists( 'civicrm_validation' )
             ) {
            // table(s) found in the db which are no longer required
            // for v2.x, though would not do any harm it's recommended
            // to remove them. 
            CRM_Core_Session::setStatus( ts("Table(s) found in your db which are no longer required for v2.x, though would not do any harm it's recommended to remove them") );
        }

        // show error if any of the tables, use 'MyISAM' storage engine. 
        // just check the first 10 civicrm tables, rather than checking all 106!
        if ( CRM_Core_DAO::isDBMyISAM( 10 ) ) {
            $errorMessage = ts('Your database is configured to use the MyISAM database engine. CiviCRM  requires InnoDB. You will need to convert any MyISAM tables in your database to InnoDB before proceeding.');
            return false;
        }

        return true;
    }

    function upgrade( ) {
        $currentDir = dirname( __FILE__ );

        // remove domain_ids from the entire db
        $sqlFile    = implode( DIRECTORY_SEPARATOR,
                               array( $currentDir, '../sql', 'domain_ids.mysql' ) );
        $this->source( $sqlFile );
        
        $this->setVersion( '2.01' );
    }
    
    function verifyPostDBState( &$errorMessage ) {
        $errorMessage = ts('Post-condition failed for upgrade step %1.', array(1 => '1'));
        return $this->checkVersion( '2.01' );
    }

    function getTitle( ) {
        return ts( 'CiviCRM 2.1 Upgrade: Step One (Domain Ids)' );
    }

    function getTemplateMessage( ) {
        $msg = '<p><strong>' . ts('This process will upgrade your v2.0 CiviCRM database to the v2.1 database format.') . '</strong></p><div class="messsages status"><ul><li><strong>' . ts('Make sure you have a current and complete backup of your CiviCRM database and codebase files before starting the upgrade process.') . '</strong></li><li>' . '</li></ul></div><p>' . ts('Click <strong>Begin Upgrade</strong> to begin the process.') . '</p>';
        
        return $msg;
    }
            
    function getButtonTitle( ) {
        return ts( 'Begin Upgrade' );
    }
}

