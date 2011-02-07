{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
{if $section eq 1}
    <div class="crm-block crm-content-block crm-report-layoutGraph-form-block">
        {*include the graph*}
        {include file="CRM/Report/Form/Layout/Graph.tpl"}
    </div>
{else}
    <div class="crm-block crm-form-block crm-report-field-form-block">
        {include file="CRM/Report/Form/Fields.tpl" componentName='Grant'}
    </div>
    
    <div class="crm-block crm-content-block crm-report-form-block">
        {*include actions*}
        {include file="CRM/Report/Form/Actions.tpl"}

        {*include the graph*}
        {include file="CRM/Report/Form/Layout/Graph.tpl"}
    
        <br />
        
        {include file="CRM/Report/Form/ErrorMessage.tpl"}
    </div>
{/if}

{if $totalStatistics}
<h3>{ts}Summary{/ts}</h2>
<table class="report-layout display">
  <th class="statistics" scope="row"></th>
  <th class="statistics right" scope="row">Count</th>
  <th class="statistics right" scope="row">Amount</th>
    {foreach from=$totalStatistics key=key item=val}
       <tr>
          <td>{$val.title}</td>
          <td class="right">{$val.count}</td>
	      <td class="right">{$val.amount|crmMoney}</td>
       </tr>      
    {/foreach}
</table>
{/if}

<h3>{ts}Statistics Breakdown{/ts}</h2>
{if $grantStatistics}
<table class="report-layout display">
  {foreach from=$grantStatistics item=values key=key}
    <th class="statistics" scope="row">{$values.title}</th>
    <th class="statistics right" scope="row">Number of Grants (%)</th>
    <th class="statistics right" scope="row">Total Amount (%)</th>
       {foreach from=$values.value item=row key=field}
           <tr>
              <td>{$field}</td>
              <td class="right">{$row.count} ({$row.percentage}%)</td>
              <td class="right">
                {foreach from=$row.currency key=fld item=val}
                   {$fld} {$val.value|crmMoney} ({$val.percentage}%)&nbsp;&nbsp;
                {/foreach} 
              </td>
           </tr>
         {if $row.unassigned_count}
           <tr>
              <td>{$field} (Unassigned)</td>
              <td class="right">{$row.unassigned_count} ({$row.unassigned_percentage}%)</td>
              <td class="right">
                {foreach from=$row.unassigned_currency key=fld item=val}
                   {$fld} {$val.value|crmMoney} ({$val.percentage}%)&nbsp;&nbsp;
                {/foreach} 
              </td>
           </tr>
         {/if}
       {/foreach}
       <tr><td colspan="3" style="border: none;">&nbsp;</td></tr>
  {/foreach}
</table>
{/if}