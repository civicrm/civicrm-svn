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

require_once 'CRM/Core/Page.php';
require_once 'CRM/Campaign/BAO/Survey.php';

/**
 * Page for displaying Surveys
 */
class CRM_Campaign_Page_Survey extends CRM_Core_Page 
{

    function browse( ) {
        $surveys = CRM_Campaign_BAO_Survey::getSurvey( );
        if ( !empty($surveys) ) {
            require_once 'CRM/Campaign/BAO/Campaign.php';
            $surveyType = CRM_Core_PseudoConstant::surveyType();
            $campaigns  = CRM_Campaign_BAO_Campaign::getAllCampaign();
            foreach( $surveys as $sid => $survey ) {
                $surveys[$sid]['campaign_id']    = $campaigns[$survey['campaign_id']];
                $surveys[$sid]['survey_type_id'] = $surveyType[$survey['survey_type_id']];
                $surveys[$sid]['release_frequency'] = $survey['release_frequency_interval'].' '.$survey['release_frequency_unit'];
            }
        }

        $this->assign('surveys', $surveys);
        
    }

    function run( ) {
        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 0 ); 
        $this->assign('action', $action);
        $this->browse();

        parent::run();
    }

}