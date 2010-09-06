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

require_once 'CRM/Core/Page.php';

class CRM_Campaign_Page_Petition_ThankYou extends CRM_Core_Page 
{
    function run( ) {
		$id = CRM_Utils_Request::retrieve('id', 'Positive', $this );
		$petition_id = CRM_Utils_Request::retrieve('pid', 'Positive', $this );
		
			// send thank you or email verification emails
			/* 
			 * sendEmailMode
			 * 1 = connected user via login/pwd - thank you
			 * 	 	or dedupe contact matched who doesn't have a tag CIVICRM_TAG_UNCONFIRMED - thank you
			 * 	 	login using fb connect - thank you + click to add msg to fb wall
			 * 2 = send a confirmation request email     
			 */		
		switch ($id) {			
			case 2:
				$message = "An email has been sent to you to confirm your email address";				
				break;
			case 4: //already signed but waiting for email confirmation
				$message = "You have already signed this petition but we need to confirm your email address.";				
				break;	
			case 5: //already signed and email confirmed
				$message = "You have already signed this petition. Thank you.";				
				break;	
			default:
				$message = "Thank you for signing the petition.";
				break;
		}
		
		// assign url and Drupal node title for social networking / share links
		$this->assign('message', $message );
		
	    require_once 'CRM/Campaign/BAO/Petition.php';		    
	    $petition_node = CRM_Campaign_BAO_Petition::getPetitionDrupalNodeData($petition_id);
		$this->assign('url', $petition_node['url']);
		$this->assign('title', $petition_node['title']);
		
		parent::run();		
    }   
    
    
}

