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
<table class="crm-copy-fields">
    <thead>
        <tr class="columnheader">
            <td>&nbsp;</td>
            <td>{ts}Contact{/ts}</td>
        {foreach from=$fields item=field key=fieldName}
            <!--td>{$field.title}</td-->
            <td><img  src="{$config->resourceBase}i/copy.png" alt="{ts 1=$field.title}Click to copy %1 from row one to all rows.{/ts}" fname="{$field.name}" class="action-icon" title="{ts}Click here to copy the value in row one to ALL rows.{/ts}" />{$field.title}</td> 
        {/foreach}
        </tr>
    </thead>
    {section name='i' start=1 loop=$rowCount} 
    {assign var='rowNumber' value=$smarty.section.i.index} 
    <tr class="{cycle values="odd-row,even-row"} selector-rows" entity_id="{$rowNumber}">
        <td class="compressed"><span class="batch-edit"></span></td>
        {* contact select/create option*}
        <td class="compressed">
            {include file="CRM/Contact/Form/NewContact.tpl" blockNo = $rowNumber noLabel=true prefix="primary_" newContactCallback="updateContactInfo($rowNumber, 'primary_')"}
        </td>

        {foreach from=$fields item=field key=fieldName}
        {assign var=n value=$field.name}
        {if ( $fields.$n.data_type eq 'Date') or ( in_array( $n, array( 'thankyou_date', 'cancel_date', 'receipt_date', 'receive_date', 'join_date', 'membership_start_date', 'membership_end_date' ) ) ) }
            <td class="compressed">{include file="CRM/common/jcalendar.tpl" elementName=$n elementIndex=$rowNumber batchUpdate=1}</td>
        {elseif $n eq 'soft_credit'}
            <td class="compressed">{include file="CRM/Contact/Form/NewContact.tpl" blockNo = $rowNumber noLabel=true prefix="soft_credit_"}
            </td>
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
        cj('input[id*="_total_amount"]').keyup(function(){
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
        total += parseFloat(cj(this).val());
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

  function updateContactInfo( blockNo, prefix ) {
    var contactHiddenElement = 'input[name="' + prefix + 'contact_select_id[' + blockNo +']"]';
    var contactId = cj( contactHiddenElement ).val();; 
    
    var returnProperties = '';
    var profileFields = new Array();
    {/literal}
    {if $contactFields}
      {foreach from=$contactFields item=val key=fldName}
        var fldName = "{$fldName}";
        {literal}
          if ( returnProperties ) {
            returnProperties = returnProperties + ',';
          }
          var fld = fldName.split('-');
          returnProperties = returnProperties + fld[0];
          profileFields[fld[0]] = fldName;
        {/literal}  
      {/foreach}
    {/if}
    {literal}
    
    cj().crmAPI ('Contact','get',{
      'sequential' :'1', 
      'contact_id': contactId,
      'return': returnProperties },
      { success:function (data) {
        cj.each ( data.values[0], function( key, value ) {
          // set the values
          var actualFldName = profileFields[key];
          if ( key == 'country' || key == 'state_province' ) {
            idFldName = key + '_id';
            value = data.values[0][idFldName];
          }
          cj('[name="field['+ blockNo +']['+ actualFldName +']"]').val( value );
        });
      }
    });
  }
</script>
{/literal}

{*include batch copy js js file*}
{include file="CRM/common/batchCopy.tpl"}