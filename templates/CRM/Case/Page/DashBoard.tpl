{* CiviCase DashBoard (launch page) *}
<h3>{ts}Case Summary{/ts}</h3>
<table class="report">
<tr class="columnheader-dark">
    <th>&nbsp;</th>
    <th scope="col" colspan="2" class="right" style="padding-right: 10px;">{ts}Active{/ts}</th>
    <th scope="col" colspan="2" class="right" style="padding-right: 10px;">{ts}Closed{/ts}</th>
    <th scope="col" colspan="2" class="right" style="padding-right: 10px;">{ts}New{/ts}</th>
    <th scope="col" colspan="2" class="right" style="padding-right: 10px;">{ts}On hold/Inactive{/ts}</th>
</tr>
 {foreach from=$caseType key=type item=content}
<tr>
<td><strong>{ts}{$type}{/ts}</strong></td>
<td class="label" colspan="2" ><a href="{$content.Active.purl}">{$content.Active.case_count}</a></td>
<td class="label" colspan="2" ><a href="{$content.Closed.purl}">{$content.Closed.case_count}</a></td>
<td class="label" colspan="2" ><a href="{$content.New.purl}">{$content.New.case_count}</a></td>
<td class="label" colspan="2"><a href="{$content.Inactive.purl}">{$content.Inactive.case_count}</a></td>
</tr>
{/foreach}
</table>

<div class="spacer"></div>

{if $pager->_totalItems}
    <h3>{ts}Recent Cases{/ts}</h3>
    <div class="form-item">
        {include file="CRM/Case/Form/Selector.tpl" context="dashboard"}
    </div>
{/if}
