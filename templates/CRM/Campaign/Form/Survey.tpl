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
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
{if $action eq 8}
  <table class="form-layout">
    <tr>
      <td colspan="2">
        <div class="status"><div class="icon inform-icon"></div>&nbsp;{ts}Are you sure you want to delete this Survey?{/ts}</div>
      </td>
    </tr>
  </table>
{else}
  {if $action  eq 1}
    <div id="help">
      {ts}Use this form to Add new Survey. You can create a new Activity type, specific to this Survey or select an existing activity type for this Survey.{/ts}
    </div>
  {/if}   
      <table class="form-layout"> 
       <tr class="crm-campaign-survey-form-block-title">
           <td class="label">{$form.title.label}</td>
           <td>{$form.title.html}
	   <div class="description">{ts}Title of the survey.{/ts}</div></td>
       </tr> 
       <tr class="crm-campaign-survey-form-block-campaign_id">
           <td class="label">{$form.campaign_id.label}</td>
           <td>{$form.campaign_id.html}
	   <div class="description">{ts}Select the campaign for which survey is created.{/ts}</div></td>
       </tr> 
       <tr class="crm-campaign-survey-form-block-activity_type_id">
           <td class="label">{$form.activity_type_id.label}</td>
           <td>{$form.activity_type_id.html}
	   <div class="description">{ts}Select the Activity Type.{/ts}</div></td>
       </tr>
       <tr class="crm-campaign-survey-form-block-profile_id">
           <td class="label">{$form.profile_id.label}</td>
           <td>{$form.profile_id.html}
	   <div class="description">{ts}Select the Profile for Survey.{/ts}</div></td>
       </tr>
       <tr class="crm-campaign-survey-form-block-instructions">
           <td class="label">{$form.instructions.label}</td>
           <td>{$form.instructions.html}
	   <div class="description">{ts}Instruction for volunteers.{/ts}</div></td>
       </tr>
       <tr class="crm-campaign-survey-form-block-release_frequency_unit">
           <td class="label">{$form.release_frequency_unit.label}</td>
           <td>{$form.release_frequency_unit.html}
           &nbsp;&nbsp; 
           <span class='label'>{$form.release_frequency_interval.label}</span>
           <span>{$form.release_frequency_interval.html}</span>
	   <div class="description">{ts}Release frequency unit and interval for Survey.{/ts}</div> 

       </tr>
       <tr class="crm-campaign-survey-form-block-max_number_of_contacts">
           <td class="label">{$form.max_number_of_contacts.label}</td>
           <td>{$form.max_number_of_contacts.html}
	   <div class="description">{ts}Maximum number of contacts can have for a survey.{/ts}</div></td>
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
{/if}
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

</div>
