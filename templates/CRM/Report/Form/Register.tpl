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
<div class="crm-block crm-form-block crm-report-form-block">	
<fieldset>
{if $action eq 8} 
    <legend>{ts}Delete Report Template{/ts}</legend>
    <table class="form-layout">
    <tr>
        <td colspan=2>
        <div class="messages status"> 
		  <div class="icon inform-icon"></div> &nbsp; 
        {ts}WARNING: Deleting this option will result in the loss of all Report related records which use the option. This may mean the loss of a substantial amount of data, and the action cannot be undone. Do you want to continue?{/ts}
        </div>        
        </td>
    </tr>
{else}
  	
    <legend>{if $action eq 2}{ts}Edit Report Template{/ts}{else}{ts}New Report Template{/ts}{/if}</legend>
    <table class="form-layout">
        <tr class="crm-report-form-block-title">
            <td class="label">{$form.label.label}</td>
            <td class="view-value">{$form.label.html} <br /><span class="description">{ts}Report title appear in the display screen.{/ts}</span>
            </td>
        </tr>	   
        <tr class="crm-report-form-block-description">
            <td class="label">{$form.description.label}</td>
            <td class="view-value">{$form.description.html} <br /><span class="description">{ts}Report description appear in the display screen.{/ts}</span>
            </td>
        </tr>	   
        <tr class="crm-report-form-block-url">
            <td class="label">{$form.value.label}</td>
            <td class="view-value">{$form.value.html} <br /><span class="description">{ts}Report Url must be like "contribute/summary"{/ts}</span>
            </td>
        </tr>
        <tr class="crm-report-form-block-class">
            <td class="label">{$form.name.label}</td>
            <td class="view-value">{$form.name.html} <br /><span class="description">{ts}Report Class must be present before adding the report here, e.g. 'CRM_Report_Form_Contribute_Summary'{/ts}</span>
            </td>
        </tr>
        <tr class="crm-report-form-block-weight">
            <td class="label">{$form.weight.label}</td>
            <td class="view-value">{$form.weight.html}</td>
        </tr>
        <tr class="crm-report-form-block-component">
            <td class="label">{$form.component_id.label}</td>
            <td class="view-value">{$form.component_id.html} <br /><span class="description">{ts}Specify the Report if it is belongs to any component like "CiviContribute"{/ts}</span>
            </td>
        </tr>
        <tr class="crm-report-form-block-is_active">
            <td class="label">{$form.is_active.label}</td>
            <td class="view-value">{$form.is_active.html}</td>
        </tr>  
{/if} 
    <tr class="buttons">
        <td><div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>
        </td>
        <td></td>
    </tr>
    </table>  
</fieldset>
</div>
