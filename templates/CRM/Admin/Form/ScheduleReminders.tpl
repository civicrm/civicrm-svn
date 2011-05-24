{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (C) 2011 Marty Wright                                    |
 | Licensed to CiviCRM under the Academic Free License version 3.0.   |
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
{* This template is used for adding/scheduling reminders.  *}
<div class="crm-block crm-form-block crm-scheduleReminder-form-block">
 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

{if $action eq 8}
  <div class="messages status">  
      <div class="icon inform-icon"></div> 
        {ts 1=$formatName}WARNING: You are about to delete the Reminder titled <strong>%1</strong>.{/ts} {ts}Do you want to continue?{/ts}
  </div>
{elseif $action eq 16384}
  <div class="messages status">  
      <div class="icon inform-icon"></div> 
        {ts 1=$formatName}Are you sure you would like to make a copy of the Reminder titled <strong>%1</strong>?{/ts}
  </div>
{else}
  <table class="form-layout-compressed">
    <tr class="crm-scheduleReminder-form-block-name">
        <td class="right">{$form.title.label}</td><td colspan="3">{$form.title.html}</td>
    </tr>

     <tr>	
        <td class="label">{$form.entity.label}</td>
        <td>{$form.entity.html}</td>
    </tr>

    <tr class="crm-scheduleReminder-form-block-description">
        <td class="right">{$form.reminder_interval.label}</td>
	<td colspan="3">{$form.reminder_interval.html}&nbsp;&nbsp;&nbsp;{$form.reminder_frequency.html}&nbsp;&nbsp;&nbsp;
			{$form.action_condition.html}
	</td>
    </tr>
    <tr class="crm-scheduleReminder-form-block-is_repeat"><th scope="row" class="label" width="20%">{$form.is_repeat.label}</th>
        <td>{$form.is_repeat.html}&nbsp;&nbsp;<span class="description">{ts}Enable repetition.{/ts}</span></td>
    </tr>
    <tr id="repeatFields" class="crm-scheduleReminder-form-block-repeatFields"><td></td><td>
        <table class="form-layout-compressed">
            <tr class="crm-scheduleReminder-form-block-repetition_start_frequency_interval"><th scope="row" class="label">{$form.repetition_start_frequency_interval.label}</th>
                <td>{$form.repetition_start_frequency_interval.html}&nbsp;&nbsp;&nbsp;{$form.repetition_start_frequency_unit.html}</td>
            </tr>
	    <tr class="crm-scheduleReminder-form-block-repetition_start_frequency_interval"><th scope="row" class="label">{$form.repetition_end_frequency_interval.label}</th>
                <td>{$form.repetition_end_frequency_interval.html}&nbsp;&nbsp;&nbsp;{$form.repetition_end_frequency_unit.html}&nbsp;&nbsp;&nbsp;{$form.repetition_end_action.html}</td>
            </tr>
        </table>
        </td>
    </tr>
  </table>
  <fieldset id="compose_id"><legend>{ts}Email{/ts}</legend>
     {include file="CRM/Contact/Form/Task/EmailCommon.tpl" upload=1 noAttach=1}
  </fieldset>

{/if} 

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>

{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    = "is_repeat"
    trigger_value       = "true"
    target_element_id   = "repeatFields" 
    target_element_type = "table-row"
    field_type          = "radio"
    invert              = "false"
}


