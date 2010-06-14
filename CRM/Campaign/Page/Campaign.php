<?php
+/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 --------------------------------------------------------------------+
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
require_once 'CRM/Campaign/BAO/Campaign.php';

/**
 * Page for displaying Surveys
 */
class CRM_Campaign_Page_Campaign extends CRM_Core_Page 
{
    
    function browse( ) {
        $campaigns = CRM_Campaign_BAO_Campaign::getCampaign( );
       
        if ( !empty($campaigns) ) {
            require_once 'CRM/Campaign/BAO/Campaign.php';
            $campaignType = CRM_Core_PseudoConstant::campaignType();
            $campaignStatus  = CRM_Core_PseudoConstant::campaignStatus();
            foreach( $campaigns as $cmpid => $campaign ) {
                $campaigns[$cmpid]['campaign_id']    = $campaign[$survey['campaign_id']];
                $campaigns[$cmpid]['title'] = $campaign['title'];
                $campaigns[$cmpid]['name'] = $campaign['name'];
                $campaigns[$cmpid]['description'] = $campaign['description'];
                $campaigns[$cmpid]['campaign_type_id'] = $campaignType[$campaign['campaign_type_id']];
                $campaigns[$cmpid]['status_id'] = $campaignStatus[$campaign['status_id']];
               
            }
        }
       
        $this->assign('campaigns', $campaigns);
    }
    
    function run( ) {
        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 0 ); 
        $this->assign('action', $action);
        $this->browse();
        
        parent::run();
    }
    
}