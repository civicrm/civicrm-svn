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
{* This template is used for adding/configuring Scheduled Jobs.  *}
<h3>{if $action eq 1}{ts}New Scheduled Job{/ts}{elseif $action eq 2}{ts}Edit Scheduled Job{/ts}{else}{ts}Delete Scheduled Job{/ts}{/if}</h3>
<div class="crm-block crm-form-block crm-job-form-block">
 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

{if $action eq 8}
  <div class="messages status">  
      <div class="icon inform-icon"></div> 
        {ts}WARNING: Deleting this Scheduled Job will cause some important site functionality to stop working.{/ts} {ts}Do you want to continue?{/ts}
  </div>
{else}
  <table class="form-layout-compressed">
    <tr class="crm-job-form-block-name">
        <td class="label">{$form.name.label}</td><td>{$form.name.html}</td>
    </tr>
    <tr class="crm-job-form-block-description">
        <td class="label">{$form.description.label}</td><td>{$form.description.html}</td>
    </tr>

    <tr class="crm-job-form-block-script">
        <td class="label">{$form.cron_string.label}</td><td>{$form.cron_string.html}</td>
    </tr>
    <tr class="crm-job-form-block-script">
        <td class="label">{$form.script.label}</td><td>{$form.script.html}</td>
    </tr>
    <tr class="crm-job-form-block-is_active">
        <td></td><td>{$form.is_active.html}&nbsp;{$form.is_active.label}</td>
    </tr>
  </table>
{/if} 
</table>
       <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
  </fieldset>
</div>

