{*
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
*}
<div class="crm-block crm-form-block crm-campaign-form-block">
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>

    <table class="form-layout-compressed">
    	<tr class="crm-campaign-form-block-name">
	    <td class="label">{$form.name.label}</td>
	    <td class="view-value">{$form.name.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-title">
	    <td class="label">{$form.title.label}</td>
	    <td class="view-value">{$form.title.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-description">
	    <td class="label">{$form.description.label}</td>
	    <td class="view-value">{$form.description.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-start_date">
	    <td class="label">{$form.start_date.label}</td>
	    <td class="view-value">{include file="CRM/common/jcalendar.tpl" elementName=start_date}
	    </td>
	</tr>
	<tr class="crm-campaign-form-block-end_date">
	    <td class="label">{$form.end_date.label}</td>
	    <td class="view-value">{include file="CRM/common/jcalendar.tpl" elementName=end_date}</td>
	</tr>
	<tr class="crm-campaign-form-block-campaign_type_id">
	    <td class="label">{$form.campaign_type_id.label}</td>
	    <td class="view-value">{$form.campaign_type_id.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-status_id">
	    <td class="label">{$form.status_id.label}</td>
	    <td class="view-value">{$form.status_id.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-external_identifier">
	    <td class="label">{$form.external_identifier.label}</td>
	    <td class="view-value">{$form.external_identifier.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-campaign_id">
	    <td class="label">{$form.campaign_id.label}</td>
	    <td class="view-value">{$form.campaign_id.html}</td>
	</tr>
	<tr class="crm-campaign-form-block-is_active">
	    <td class="label">{$form.is_active.label}</td>
	    <td class="view-value">{$form.is_active.html}</td>
	</tr>
    </table>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>
</div>