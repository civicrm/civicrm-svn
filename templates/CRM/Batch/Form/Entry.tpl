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
<div class="form-item batch-totals">
    <div class="label">{ts}Expected total amount{/ts}: <span class="batch-expected-total">{$batchTotal|crmMoney}</span></div>
    <div class="label">{ts}Actual total amount{/ts}: {$config->defaultCurrencySymbol} <span class="batch-actual-total"></span></div>
</div>
<br/>
<table>
    <thead>
        <tr class="columnheader">
            <td>&nbsp;</td>
            <td>{ts}Contact{/ts}</td>
        {foreach from=$fields item=field key=fieldName}
            <td>{$field.title}</td>
        {/foreach}
        </tr>
    </thead>
    {section name='i' start=1 loop=$rowCount} 
    {assign var='rowNumber' value=$smarty.section.i.index} 
    <tr class="{cycle values="odd-row,even-row"} selector-rows" entity_id="{$rowNumber}">
        <td class="compressed"><span class="batch-edit">&nbsp;&nbsp;&nbsp;</span></td>
        {* contact select/create option*}
        <td class="compressed">
            {include file="CRM/Contact/Form/NewContact.tpl" blockNo = $rowNumber noLabel=true}
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
        cj('.selector-rows').change(function(){
            var options = {
                'url' : {/literal}"{crmURL p='civicrm/ajax/batch' h=0}"{literal}       
            };

            cj("#Entry").ajaxSubmit(options);
            
            // validate rows
            checkColumns( cj(this) );
        });
        
        // validate rows
        validateRow( );

        //calculate the actual total for the batch
        calculateActualTotal();
        cj('input[id*="_total_amount"]').change(function(){
            calculateActualTotal();    
        });
   });

   function validateRow( ) {
      cj('.selector-rows').each(function(){
           checkColumns( cj(this) );
      });
   }
    
   function checkColumns( parentRow ) {
       // show valid row icon if all required data is field
       var validRow   = 0;
       var inValidRow = 0;
       var errorExists = false;
       parentRow.find('td .required').each(function(){
         if ( !cj(this).val( ) ) {
            inValidRow++;
         } else if ( cj(this).hasClass('error') && !cj(this).hasClass('valid') ) {
            errorExists = true;
         } else {
            validRow++;
         }
       });

       // this means use has entered some data
       if ( errorExists ) {
           parentRow.find("td:first span").prop('class', 'batch-invalid');
       } else if ( inValidRow == 0 && validRow > 0 ) {
            parentRow.find("td:first span").prop('class', 'batch-valid');
       } else {
            parentRow.find("td:first span").prop('class', 'batch-edit');
       }
   }
    
   function calculateActualTotal() {
       var total = 0;
       cj('input[id*="_total_amount"]').each(function(){
          if ( cj(this).val() ) {
            total += parseInt(cj(this).val());
          }
       });
       
       cj('.batch-actual-total').html(formatMoney(total)); 
   }

//money formatting/localization
function formatMoney ( amount ) {
var c = 2;
var t = '{/literal}{$config->monetaryThousandSeparator}{literal}';
var d = '{/literal}{$config->monetaryDecimalPoint}{literal}';

var n = amount, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "," : d, 
    t = t == undefined ? "." : t, s = n < 0 ? "-" : "", 
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
    j = (j = i.length) > 3 ? j % 3 : 0;
	return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}


</script>
{/literal}
