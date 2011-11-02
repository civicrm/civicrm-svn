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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

class CRM_Upgrade_Incremental_php_FourOne {
    
    function verifyPreDBstate ( &$errors ) {
    	$config = CRM_Core_Config::singleton( );
        if ( in_array( 'CiviCase', $config->enableComponents ) ) {
            if (! CRM_Core_DAO::checkTriggerViewPermission( true, false ) ) {
                $errors[] = ts('CiviCase now requires CREATE VIEW and DROP VIEW permissions for the database user.');
                return false;
            }
        }
        
        return true;
    }
    
    function upgrade_4_1_alpha1( $rev ) {
    	$config = CRM_Core_Config::singleton( );
        if ( in_array( 'CiviCase', $config->enableComponents ) ) {
            require_once 'CRM/Case/BAO/Case.php';
        	if ( ! CRM_Case_BAO_Case::createCaseViews( ) ) {
                $template = CRM_Core_Smarty::singleton( );
                $afterUpgradeMessage = '';
		        if ( $afterUpgradeMessage = $template->get_template_vars('afterUpgradeMessage') ) {
		        	$afterUpgradeMessage .= "<br/><br/>";
		        }
		        $afterUpgradeMessage .= '<div class="crm-upgrade-case-views-error" style="background-color: #E43D2B; padding: 10px;">' . ts( "There was a problem creating CiviCase database views. Please create the following views manually before using CiviCase:" );
		        $afterUpgradeMessage .= '<div class="crm-upgrade-case-views-query"><div>'
		            . CRM_Case_BAO_Case::createCaseViewsQuery( 'upcoming' ) . '</div><div>'
		            . CRM_Case_BAO_Case::createCaseViewsQuery( 'recent' ) . '</div>'
		            . '</div></div>';
		        $template->assign('afterUpgradeMessage', $afterUpgradeMessage);
        	}
        }

        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );

        require_once 'CRM/Core/BAO/Setting.php';

        $this->transferPreferencesToSettings( );
        $this->createNewSettings( );

        // now modify the config so that the directories are now stored in the settings table
        // CRM-8780
        require_once 'CRM/Core/BAO/ConfigSetting.php';
        $params = array( );
        CRM_Core_BAO_ConfigSetting::add( $params );
        
         // also reset navigation
        require_once 'CRM/Core/BAO/Navigation.php';
        CRM_Core_BAO_Navigation::resetNavigation( );
        
        require_once 'CRM/Dedupe/DAO/Rule.php';
        require_once 'CRM/Dedupe/BAO/RuleGroup.php';
       
        $rgBao = new CRM_Dedupe_BAO_RuleGroup();
        $rgBao->contact_type = 'Individual';
        $rgBao->level = 'Strict';
        $rgBao->is_default = 1;
        $rgBao->threshold = 10;
        if (!$rgBao->find(true)) {
            return;
        }
        $ruleDao = new CRM_Dedupe_DAO_Rule();
        $ruleDao->dedupe_rule_group_id = $rgBao->id;
        
        $ruleDao->find();
        $count = 0;
        $IndividualStrictFields = array( );
        while ($ruleDao->fetch()) {
            $IndividualStrictFields["where_$count"]  = "{$ruleDao->rule_table}.{$ruleDao->rule_field}";
            $IndividualStrictFields["length_$count"] = $ruleDao->rule_length;
            $IndividualStrictFields["weight_$count"] = $ruleDao->rule_weight;
            $count++;
        }
         
        if( $count > 1 || ( $count == 1 && CRM_Utils_Array::value( 'where_0', $IndividualStrictFields ) != 'civicrm_email.email' && CRM_Utils_Array::value( 'weight_0', $IndividualStrictFields ) != 10 ) ){
            
            $valuesArr = array( );
            $valuesArr['is_default'] = 0;
            $valuesArr['threshold'] = 15;
            $valuesArr['level'] = 'Strict';
            $valuesArr['name'] = 'IndividualComplete';
            $valuesArr['title'] = 'Individual-Complete';
            $valuesArr['is_reserved'] = 1;
            $valuesArr['where_0'] = 'civicrm_contact.first_name';
            $valuesArr['weight_0'] = 5;
            $valuesArr['where_1'] = 'civicrm_contact.last_name';
            $valuesArr['weight_1'] = 5;
            $valuesArr['where_2'] = 'civicrm_address.street_address';
            $valuesArr['weight_2'] = 5;
            $valuesArr['where_3'] = 'civicrm_contact.middle_name';
            $valuesArr['weight_3'] = 1;
            $valuesArr['where_4'] = 'civicrm_contact.suffix_id' ;
            $valuesArr['weight_4'] = 1;

            self::dedupeRuleAdd( $valuesArr );
        }
        else if( $count == 1 && CRM_Utils_Array::value( 'where_0', $IndividualStrictFields ) == 'civicrm_email.email' && CRM_Utils_Array::value( 'weight_0', $IndividualStrictFields ) == 10 ){
            
            $rgBaoForInsertion = new CRM_Dedupe_BAO_RuleGroup();
            $rgBaoForInsertion->id = $rgBao->id;
            $rgBaoForInsertion->is_reserved = 1;
            $rgBaoForInsertion->save();
        }         
    }
    
    function transferPreferencesToSettings( ) {
        // first transfer system preferences
        $domainColumnNames = 
            array( CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME => array( 
                                                                          'contact_view_options',
                                                                          'advanced_search_options',
                                                                          'user_dashboard_options',
                                                                          'address_options',
                                                                          'address_format',
                                                                          'mailing_format',
                                                                          'display_name_format',
                                                                          'sort_name_format',
                                                                          'editor_id',
                                                                          'contact_autocomplete_options',
                                                                           ),
                   CRM_Core_BAO_Setting::ADDRESS_STANDARDIZATION_PREFERENCES_NAME => array(
                                                                                           'address_standardization_provider',
                                                                                           'address_standardization_userid',
                                                                                           'address_standardization_url',
                                                                                           ),
                   CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME => array(
                                                                           'mailing_backend',
                                                                           ),
                   );
        
        $userColumnNames = array( CRM_Core_BAO_Setting::NAVIGATION_NAME => array(
                                                                                 'navigation',
                                                                                 ),
                                  );

        $sql = "
SELECT *
FROM   civicrm_preferences
WHERE  domain_id = %1
";
        $params = array( 1 => array( CRM_Core_Config::domainID( ), 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $domainID    = CRM_Core_Config::domainID( );
        $createdDate = date( 'YmdHis' );
        $session     = CRM_Core_Session::singleton( );
        $createdID   = $session->get( 'userID' );

        while ( $dao->fetch( ) ) {
            if ( $dao->is_domain ) {
                $values = array( );
                foreach ( $domainColumnNames as $groupName => $settingNames ) {
                    foreach ( $settingNames as $settingName ) {
                        $value = empty( $dao->$settingName ) ? null : serialize( $dao->$settingName );
                        
                        if( $value ){
                            $value = addslashes($value);
                        }
                        $value =  $value ? "'{$value}'" : 'null';
                        $values[] =  "('{$groupName}','{$settingName}', {$value}, {$domainID}, null, 1, '{$createdDate}', {$createdID})" ;
                    }
                }
            } else {
                // this is a user setting
                foreach ( $userColumnNames as $groupName => $settingNames ) {
                    foreach ( $settingNames as $settingName ) {
                        $value = empty( $dao->$settingName ) ? null : serialize( $dao->$settingName );
                        
                        if( $value ){
                            $value = addslashes($value);
                        }
                        $value = $value ? "'{$value}'" : 'null';
                        $values[] = "('{$groupName}', '{$settingName}', {$value}, {$domainID}, {$dao->contact_id}, 0, '{$createdDate}', {$createdID})" ;
                    }
                }
            }
        }

        $sql = "
INSERT INTO civicrm_setting( group_name, name, value, domain_id, contact_id, is_domain, created_date, created_id )
VALUES
";
        $sql .= implode( ",\n", $values );
        CRM_Core_DAO::executeQuery( $sql );

        // now drop the civicrm_preferences table
        $sql = "DROP TABLE civicrm_preferences";
        CRM_Core_DAO::executeQuery( $sql );
    }

    function createNewSettings( ) {
        $domainColumns = 
            array( CRM_Core_BAO_Setting::CONFIGURATION_PREFERENCES_NAME =>
                   array( array( 'contact_ajax_check_similar', 1 ),
                          array( 'tag_unconfirmed', 'Unconfirmed' ),
                          array( 'petition_contacts', 'Petition Contacts' ),
                          ),
                   CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME =>
                   array( array( 'profile_double_optin', 1 ),
                          array( 'profile_add_to_group_double_optin', 0 ),
                          array( 'track_civimail_replies', 0 ),
                          array( 'activity_assignee_notification', 1 ),
                          array( 'civimail_workflow', 0 ),
                          ),
                   CRM_Core_BAO_Setting::MULTISITE_PREFERENCES_NAME =>
                   array( array( 'is_enabled', 0 ),
                          array( 'uniq_email_per_site', 0 ),
                          array( 'domain_group_id', 0 ),
                          array( 'event_price_set_domain_id', 0 ),
                          ),
                   CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME =>
                   array( array( 'uploadDir', null ),
                          array( 'imageUploadDir', null ),
                          array( 'customFileUploadDir', null ),
                          array( 'customPHPPathDir', null ),
                          array( 'extensionsDir', null ),
                          ),
                   CRM_Core_BAO_Setting::URL_PREFERENCES_NAME =>
                   array( array( 'userFrameworkResourceURL', null ),
                          array( 'imageUploadURL', null ),
                          array( 'customCSSURL', null ),
                          ),
                   );

        $domainID    = CRM_Core_Config::domainID( );
        $createdDate = date( 'YmdHis' );
        $session     = CRM_Core_Session::singleton( );
        $createdID = $contactID = $session->get( 'userID' );

        $dbSettings = array( );
        self::retrieveDirectoryAndURLPaths( $dbSettings );

        foreach ( $domainColumns as $groupName => $settings ) {
            foreach ( $settings as $setting ) {

                if ( isset($dbSettings[$groupName][$setting[0]]) && !empty($dbSettings[$groupName][$setting[0]]) ) {
                    $setting[1] = $dbSettings[$groupName][$setting[0]];
                }
                
                $value = $setting[1] === null ? null : serialize( $setting[1] );
                
                if( $value ){
                    $value = addslashes($value);
                }
                
                $value = $value ? "'{$value}'" : 'null';
                $values[] = "('{$groupName}', '{$setting[0]}', {$value}, {$domainID}, {$contactID}, 0, '{$createdDate}', {$createdID})" ;
        
            }
        }
        $sql = "
INSERT INTO civicrm_setting( group_name, name, value, domain_id, contact_id, is_domain, created_date, created_id )
VALUES
";
        $sql .= implode( ",\n", $values );
        CRM_Core_DAO::executeQuery( $sql );
    }

    static function retrieveDirectoryAndURLPaths( &$params ) {
                
        $sql = "
SELECT v.name as valueName, v.value, g.name as optionName
FROM   civicrm_option_value v,
       civicrm_option_group g
WHERE  ( g.name = 'directory_preferences'
OR       g.name = 'url_preferences' )
AND    v.option_group_id = g.id
AND    v.is_active = 1
";        
        $dao    = CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch( ) ) {
            if ( ! $dao->value ) {
                continue;
            }
           
            $groupName = CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME;
            if ( $dao->optionName == 'url_preferences' ) {
                $groupName = CRM_Core_BAO_Setting::URL_PREFERENCES_NAME;
            }
            $params[$groupName][$dao->valueName] = $dao->value;
            
        }
    }
    
    public function dedupeRuleAdd( $values ) 
    {
        require_once 'CRM/Contact/Form/DedupeRules.php';

        $isDefault = CRM_Utils_Array::value( 'is_default', $values, false );
        // reset defaults
        if ( $isDefault ) {
            $query = "
UPDATE civicrm_dedupe_rule_group 
   SET is_default = 0
 WHERE contact_type = %1 
   AND level = %2";
            $queryParams = array( 1 => array( 'Individual', 'String' ),
                                  2 => array( $values['level'], 'String' ) ); 
            CRM_Core_DAO::executeQuery( $query, $queryParams );
        }

        $rgDao            = new CRM_Dedupe_DAO_RuleGroup();

        $rgDao->threshold    = $values['threshold'];
        $rgDao->title        = $values['title'];
        $rgDao->name         = $values['name'];
        $rgDao->level        = $values['level'];
        $rgDao->contact_type = 'Individual';
        $rgDao->is_reserved  = CRM_Utils_Array::value( 'is_reserved', $values, false );
        $rgDao->is_default   = $isDefault;
        $rgDao->save();
        
        $ruleDao = new CRM_Dedupe_DAO_Rule();
        $ruleDao->dedupe_rule_group_id = $rgDao->id;
        $ruleDao->delete();
        $ruleDao->free();

        $substrLenghts = array();

        $tables = array( );
        for ($count = 0; $count < CRM_Contact_Form_DedupeRules::RULES_COUNT; $count++) {
            if ( ! CRM_Utils_Array::value( "where_$count", $values ) ) {
                continue;
            }
            list($table, $field) = explode('.', CRM_Utils_Array::value( "where_$count", $values ) );
            $length = CRM_Utils_Array::value( "length_$count", $values ) ? CRM_Utils_Array::value( "length_$count", $values ) : null;
            $weight = $values["weight_$count"];
            if ($table and $field) {
                $ruleDao = new CRM_Dedupe_DAO_Rule();
                $ruleDao->dedupe_rule_group_id = $rgDao->id;
                $ruleDao->rule_table           = $table;
                $ruleDao->rule_field           = $field;
                $ruleDao->rule_length          = $length;
                $ruleDao->rule_weight          = $weight;
                $ruleDao->save();
                $ruleDao->free();

                if ( ! array_key_exists( $table, $tables ) ) {
                    $tables[$table] = array( );
                }
                $tables[$table][] = $field;
            }

            // CRM-6245: we must pass table/field/length triples to the createIndexes() call below
            if ($length) {
                if (!isset($substrLenghts[$table])) $substrLenghts[$table] = array();
                $substrLenghts[$table][$field] = $length;
            }
        }

        // also create an index for this dedupe rule
        // CRM-3837
        require_once 'CRM/Core/BAO/SchemaHandler.php';
        CRM_Core_BAO_SchemaHandler::createIndexes( $tables, 'dedupe_index', $substrLenghts );
                  
    }
    
  }