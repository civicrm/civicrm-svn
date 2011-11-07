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
    		    <td class="label">{$form.profile_double_optin.label}</td>
    		    <td>{$form.profile_double_optin.html}</td>
    		</tr>
    		<tr class="crm-preferences-misc-form-block-profile-add-to-group-double-optin">
    		    <td class="label">{$form.profile_add_to_group_double_optin.label}</td>
    		    <td>{$form.profile_add_to_group_double_optin.html}</td>
    		</tr>
    		<tr class="crm-preferences-misc-form-block-track-civimail-replies">
    		    <td class="label">{$form.track_civimail_replies.label}</td>
    		    <td>{$form.track_civimail_replies.html}</td>
    		</tr>
    		<tr class="crm-preferences-misc-form-block-civimail-workflow">
    		    <td class="label">{$form.civimail_workflow.label}</td>
    		    <td>{$form.civimail_workflow.html}</td>
    		</tr>
    		<tr class="crm-preferences-misc-form-block-civimail-server-wide-lock">
    		    <td class="label">{$form.civimail_server_wide_lock.label}</td>
    		    <td>{$form.civimail_server_wide_lock.html}</td>
    		</tr>
    		<tr class="crm-preferences-misc-form-block-ativity-assignee-notification">
    		    <td class="label">{$form.activity_assignee_notification.label}</td>
    		    <td>{$form.activity_assignee_notification.html}</td>
    		</tr>
    	</table>

    <h3>{ts}AJAX Settings{/ts}</h3>
        <table class="form-layout">
             <tr class="crm-preferences-other-form-block-contact-ajax-check-similar">
                <td class="label">{$form.contact_ajax_check_similar.label}
                <td>{$form.contact_ajax_check_similar.html}</td>
             </tr>
        </table>

    <h3>{ts}Campaign Settings{/ts}</h3>
        <table class="form-layout">
             <tr class="crm-preferences-campaign-form-block-tag-unconfirmed">
                <td class="label">{$form.tag_unconfirmed.label}</td>
                <td>{$form.tag_unconfirmed.html}<br/>
                    <span class="description">{ts}Tag to assign to contacts that are created when a petition is signed{/ts}</span>
                </td>
             </tr>
             <tr class="crm-preferences-campaign-form-block-petition-contacts">
                <td class="label">{$form.petition_contacts.label}</td>
                <td>{$form.petition_contacts.html}<br/>
                    <span class="description">{ts}Group to assign all contacts that have signed a petition{/ts}</span>
                </td>
	     </tr>
        </table>

    <h3>{ts}Multi Site Settings{/ts}</h3>
        <table class="form-layout">
             <tr class="crm-preferences-multisite-form-block-enable">
                <td class="label">{$form.is_enabled.label}</td>
                <td>{$form.is_enabled.html}</td>
             </tr>
             <tr class="crm-preferences-multisite-form-block-uniq-email-per-site">
                <td class="label">{$form.uniq_email_per_site.label}</td>
                <td>{$form.uniq_email_per_site.html}</td>
             </tr>
             <tr class="crm-preferences-multisite-form-block-domain-group-id">
                <td class="label">{$form.domain_group_id.label}</td>
                <td>{$form.domain_group_id.html}</td>
             </tr>
             <tr class="crm-preferences-multisite-form-block-event-price-set-domain-id">
                <td class="label">{$form.event_price_set_domain_id.label}</td>
                <td>{$form.event_price_set_domain_id.html}</td>
             </tr>
        </table>


<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>