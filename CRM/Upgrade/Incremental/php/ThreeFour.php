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

class CRM_Upgrade_Incremental_php_ThreeFour {
    
    function verifyPreDBstate ( &$errors ) {
        return true;
    }
    
    function upgrade_3_4_alpha3( $rev ) 
    {
        // CRM-7681, update report instance criteria.
        require_once 'CRM/Report/DAO/Instance.php';
        $modifiedReportIds = 
            array( 'contact/summary', 'contact/detail',  'event/participantListing', 'member/summary', 'pledge/summary', 'pledge/pbnp', 'member/detail',  'member/lapse', 'grant/detail', 'contribute/bookkeeping', 'contribute/lybunt', 'contribute/summary', 'contribute/repeat', 'contribute/detail', 'contribute/organizationSummary', 'contribute/sybunt', 'contribute/householdSummary', 'contact/relationship', 'contact/currentEmployer', 'case/demographics', 'walklist', 'case/detail', 'contact/log', 'activitySummary', 'case/timespent', 'case/summary' );
        
        $instances = CRM_Core_DAO::executeQuery("SELECT id, form_values, report_id FROM civicrm_report_instance WHERE report_id IN ('". implode("','", $modifiedReportIds )."')");
        
        while( $instances->fetch( ) ) {
            $formValues = unserialize( $instances->form_values );
            
            // replace display_name fields by sort_name
            if ( CRM_Utils_Array::value('fields', $formValues) && isset($formValues['fields']['display_name']) ) {
                $formValues['fields']['sort_name'] = $formValues['fields']['display_name'];
                unset($formValues['fields']['display_name']);
            }
            
            // replace display_name filters by sort_name
            if ( isset($formValues['display_name_op']) ) {
                $formValues['sort_name_op'] = $formValues['display_name_op'];
                unset($formValues['display_name_op']);
            }
            if ( isset($formValues['display_name_value']) ) {
                $formValues['sort_name_value'] = $formValues['display_name_value'];
                unset($formValues['display_name_value']);
            }
            
            // for report id 'contact/log' replace field
            // display_name_touched by sort_name_touched
            if ( $instances->report_id == 'contact/log' && isset($formValues['fields']['display_name_touched']) ) {
                $formValues['fields']['sort_name_touched'] = $formValues['fields']['display_name_touched'];
                unset($formValues['fields']['display_name_touched']);
            }
            
            // for report id 'contact/relationship' replace field
            // display_name_a by sort_name_a and display_name_b by sort_name_b
            if ( $instances->report_id == 'contact/relationship' ) {
                if ( isset($formValues['fields']['display_name_a']) ) {
                    $formValues['fields']['sort_name_a'] =  $formValues['fields']['display_name_a'];
                    unset( $formValues['fields']['display_name_a'] );
                }

                if (  isset($formValues['fields']['display_name_b'] ) ) {
                    $formValues['fields']['sort_name_b'] =  $formValues['fields']['display_name_b'];
                    unset( $formValues['fields']['display_name_b'] );
                }
            }
            
            // save updated instance criteria
            $dao = new CRM_Report_DAO_Instance( );
            $dao->id = $instances->id;
            $dao->form_values = serialize( $formValues );
            $dao->save( );
            $dao->free( );
        }

        // Handled for typo in 3.3.2.mysql.tpl, rename column visibilty to
        // visibility in table civicrm_mailing
        $renameColumnVisibility = CRM_Core_DAO::checkFieldExists( 'civicrm_mailing', 'visibilty' ); 
        
        $upgrade =& new CRM_Upgrade_Form( );
        $upgrade->assign( 'renameColumnVisibility', $renameColumnVisibility);
        $upgrade->processSQL( $rev );
    }   
    
    function upgrade_3_4_beta2( $rev )
    {
        require_once 'CRM/Core/DAO.php';
        $addPetitionOptionGroup = !(boolean) CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup', 'msg_tpl_workflow_petition', 'id', 'name' );
        $upgrade =& new CRM_Upgrade_Form( );
        $upgrade->assign( 'addPetitionOptionGroup', $addPetitionOptionGroup );
        $upgrade->processSQL( $rev );
    }

    function upgrade_3_4_beta3($rev)
    {
        // do the regular upgrade
        $upgrade = new CRM_Upgrade_Form;
        $upgrade->processSQL($rev);

        if ($upgrade->multilingual) {
            require_once 'CRM/Core/I18n/Schema.php';
            require_once 'CRM/Core/I18n/SchemaStructure_3_4_beta2.php';

            // rebuild schema, because due to a CRM-7854 mis-fix some indices might be missing
            CRM_Core_I18n_Schema::rebuildMultilingualSchema($upgrade->locales, $rev);

            // turn a set of columns singlelingual
            $config =& CRM_Core_Config::singleton();
            $tables = array('civicrm_address', 'civicrm_contact', 'civicrm_mailing', 'civicrm_mailing_component');
            foreach ($tables as $table) {
                CRM_Core_I18n_Schema::makeSinglelingualTable($config->lcMessages, $table, 'CRM_Core_I18n_SchemaStructure_3_4_beta2');
            }
        }
    }
  }
