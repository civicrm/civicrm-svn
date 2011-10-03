{*
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
*}
<div class="crm-block crm-form-block crm-preferences-address-form-block">
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
    <h3>{ts}Mailing Preferences{/ts}</h3>
        <table class="form-layout">
    		<tr class="crm-preferences-misc-form-block-profile-double-optin">
    		    <td>{$form.profile_double_optin.html}</td>
    		    <td class="label">{$form.profile_double_optin.label}</td>
    		</tr>
    		<tr class="crm-preferences-misc-form-block-profile-add-to-group-double-optin">
    		    <td>{$form.profile_add_to_group_double_optin.html}</td>
    		    <td class="label">{$form.profile_add_to_group_double_optin.label}</td>
    		</tr>
    		<tr class="crm-preferences-misc-form-block-track-civimail-replies">
    		    <td>{$form.track_civimail_replies.html}</td>
    		    <td class="label">{$form.track_civimail_replies.label}</td>
    		</tr>
    		<tr class="crm-preferences-misc-form-block-civimail-workflow">
    		    <td>{$form.civimail_workflow.html}</td>
    		    <td class="label">{$form.civimail_workflow.label}</td>
    		</tr>
    		<tr class="crm-preferences-misc-form-block-ativity-assignee-notification">
    		    <td>{$form.activity_assignee_notification.html}</td>
    		    <td class="label">{$form.activity_assignee_notification.label}</td>
    		</tr>
    	</table>

    <h3>{ts}Other Settings{/ts}</h3>
        <table class="form-layout">
             <tr class="crm-preferences-other-form-block-contact-ajax-check-similar">
                <td>{$form.contact_ajax_check_similar.html}</td>
                <td class="label">{$form.contact_ajax_check_similar.label}
             </tr>
             <tr class="crm-preferences-other-form-block-tag-unconfirmed">
                <td class="label">{$form.tag_unconfirmed.label}
                <td>{$form.tag_unconfirmed.html}</td>
             </tr>
             <tr class="crm-preferences-other-form-block-petition-contacts">
                <td class="label">{$form.petition_contacts.label}
                <td>{$form.petition_contacts.html}</td>
	     </tr>
        </table>

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>