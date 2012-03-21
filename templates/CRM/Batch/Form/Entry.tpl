{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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
<div class="batch-entry form-item">
<div id="help">
    {ts}Batch entry form{/ts}
</div>
    
<table>
    <thead>
        <tr class="columnheader">
            <td>{ts}Contact{/ts}</td>
        {foreach from=$fields item=field key=fieldName}
            <td>{$field.title}</td>
        {/foreach}
        </tr>
    </thead>
    {section name='i' start=1 loop=$rowCount} 
    {assign var='rowNumber' value=$smarty.section.i.index} 
    <tr class="{cycle values="odd-row,even-row"} selector-rows" entity_id="{$rowNumber}">
        {* contact select/create option*}
        <td class="compressed">
            <table>
            {include file="CRM/Contact/Form/NewContact.tpl" blockNo = $rowNumber}
            </table>
        </td>

        {foreach from=$fields item=field key=fieldName}
        {assign var=n value=$field.name}
        {if ( $fields.$n.data_type eq 'Date') or ( $n eq 'thankyou_date' ) or ( $n eq 'cancel_date' ) or ( $n eq 'receipt_date' ) or ( $n eq 'receive_date' )}
            <td class="compressed">{include file="CRM/common/jcalendar.tpl" elementName=$n elementIndex=$rowNumber batchUpdate=1}</td>
        {else}
            <td class="compressed">{$form.field.$rowNumber.$n.html}</td> 
        {/if}
        {/foreach}
    </tr>
    {/section}
</table>
<div class="crm-submit-buttons">{if $fields}{$form._qf_Batch_refresh.html}{/if} &nbsp; {$form.buttons.html}</div>
</div>
{literal}
<script type="text/javascript">
    cj(function(){
        cj('.selector-rows').click(function(){
            cj(this).addClass('processed');
            //cj("#Entry").submit();
        });

        var options = {
             'url' : {/literal}"{crmURL p='civicrm/ajax/batch' h=0}"{literal}       
        };

        //cj("#Entry").ajaxForm(options);
    });
</script>
{/literal}
