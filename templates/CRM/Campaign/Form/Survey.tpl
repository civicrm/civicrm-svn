{*
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
*}

<div class="crm-block crm-form-block crm-campaign-survey-form-block">
<div id="help">
    {ts}Use this form to Add new Survey. You can create a new Activity type, specific to this Survey or select an existing activity type for this Survey. {/ts}
</div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
      <table class="form-layout"> 
      	<tr class="crm-campaign-survey-form-block-surveyTypeId">
           <td class="label">{$form.surveyTypeId.label}</td>
           <td>{$form.surveyTypeId.html}
	   <div class="description">{ts}Select the Survey Type.{/ts}</div></td>
        </tr> 
       <tr class="crm-campaign-survey-form-block-campaign_id">
           <td class="label">{$form.campaignId.label}</td>
           <td>{$form.campaignId.html}
	   <div class="description">{ts}Select the campaign for which survey is created.{/ts}</div></td>
       </tr> 
       <tr class="crm-campaign-survey-form-block-activityTypeId">
           <td class="label">{$form.activityTypeId.label}</td>
           <td>{$form.activityTypeId.html}
	   <div class="description">{ts}Select the Activity Type.{/ts}</div></td>
       </tr>
       <tr class="crm-campaign-survey-form-block-customGroupId">
           <td class="label">{$form.customGroupId.label}</td>
           <td>{$form.customGroupId.html}
	   <div class="description">{ts}Select the Custom Group.{/ts}</div></td>
       </tr>
       <tr class="crm-campaign-survey-form-block-instructions">
           <td class="label">{$form.instructions.label}</td>
           <td>{$form.instructions.html}
	   <div class="description">{ts}Release frequency unit for Survey.{/ts}</div></td>
       </tr>
       <tr class="crm-campaign-survey-form-block-release_frequency_unit">
           <td class="label">{$form.release_frequency_unit.label}</td>
           <td>{$form.release_frequency_unit.html}
	   <div class="description">{ts}Release frequency unit for Survey.{/ts}</div></td>
       </tr>
       <tr class="crm-campaign-survey-form-block-release_frequency_interval">
           <td class="label">{$form.release_frequency_interval.label}</td>
           <td>{$form.release_frequency_interval.html}
	   <div class="description">{ts}Release frequency interval for survey.{/ts}</div></td>
       </tr>
       <tr class="crm-campaign-survey-form-block-max_number_of_contacts">
           <td class="label">{$form.max_number_of_contacts.label}</td>
           <td>{$form.max_number_of_contacts.html}
	   <div class="description">{ts}Maximum number of contacts for survey.{/ts}</div></td>
       </tr>
       <tr class="crm-campaign-survey-form-block-default_number_of_contacts">
           <td class="label">{$form.default_number_of_contacts.label}</td>
           <td>{$form.default_number_of_contacts.html}
	   <div class="description">{ts}Default number of Contacts for survey.{/ts}</div></td>
       </tr>	
       <tr class="crm-campaign-survey-form-block-is_active">
           <td class="label">{$form.is_active.label}</td>
           <td>{$form.is_active.html}
	   <div class="description">{ts}Is this survey Active?.{/ts}</div></td>
       </tr>
       <tr class="crm-campaign-survey-form-block-is_default">
           <td class="label">{$form.is_default.label}</td>
           <td>{$form.is_default.html}
	   <div class="description">{ts}Is this survey default?.{/ts}</div></td>
       </tr>
      </table>

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

</div>