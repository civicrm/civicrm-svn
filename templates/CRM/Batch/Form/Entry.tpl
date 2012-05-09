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
{if $batchAmountMismatch}
  <div class="status message">
    {ts}Batch total amount and entered amount mismatch.{/ts}&nbsp;&nbsp;
    <span class="crm-button crm-button_qf_Entry_upload_force-save">
      {$form._qf_Entry_upload_force.html}
    </span>
    <br/>
  </div>
  <div class="clear"></div>
{/if}
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
            <td class="compressed"><span class="crm-batch-{$n}-{$rowNumber}">{include file="CRM/common/jcalendar.tpl" elementName=$n elementIndex=$rowNumber batchUpdate=1}</span></td>
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

        // hide the receipt date if send receipt is checked
        cj( 'input[id*="][send_receipt]"]').change( function() {
          var rowID = cj(this).closest('tr').attr('entity_id');
          var element = 'field_' + rowID + '_receipt_date';
          if ( cj(this).prop('checked') ) {
            cj('.crm-batch-receipt_date-'+ rowID ).hide();
          } else {
            cj('.crm-batch-receipt_date-'+ rowID ).show();
          }
        });

        //set the focus on first element
        cj('#primary_contact_1').focus();

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
          setFieldValue( actualFldName, value, blockNo )
        });
      }
    });
  }

/**
 * This function is use to setdefault elements via ajax 
 *
 * @param fname string field name
 * @return void
 */
function setFieldValue( fname, fieldValue, blockNo ) {
    var elementId = cj('[name="field['+ blockNo +']['+ fname +']"]');
    
    if ( elementId.length == 0 ) {
      elementId =  cj('input[type=checkbox][name^="field['+ blockNo +']['+ fname +']"][type!=hidden]');
    }
   
    // if element not found than return
    if ( elementId.length == 0 ) {
      return;
    }

    //check if it is date element
    var isDateElement = elementId.attr('format');
    
    // check if it is wysiwyg element
    var editor = elementId.attr('editor');

    //get the element type
    var elementType = elementId.attr('type'); 
    
    // set the value for all the elements, elements needs to be handled are
    // select, checkbox, radio, date fields, text, textarea, multi-select
    // wysiwyg editor, advanced multi-select ( to do )
    if ( elementType == 'radio' && fieldValue ) {
      elementId.filter("[value=" + fieldValue + "]").prop("checked",true);
    } else if ( elementType == 'checkbox' ) {
      // handle checkbox
      elementId.removeProp('checked');
      if ( fieldValue ) {
        cj.each( fieldValue, function( key, value ) {
          cj('input[name="field['+ blockNo +']['+ fname +']['+ value +']"]').prop('checked', true); 
        });
      }
    } else if ( editor ) {
      switch ( editor ) {
        case 'ckeditor':
          var elemtId = elementId.attr('id');
          oEditor = CKEDITOR.instances[elemtId];
          oEditor.setData( htmlContent );
          break;
        case 'tinymce':
          var elemtId = element.attr('id');
          tinyMCE.get( elemtId ).setContent( htmlContent ); 
          break;
        case 'joomlaeditor':
          // TO DO
        case 'drupalwysiwyg':
          // TO DO
        default:
          elementId.val( fieldValue );
      }
    } else {
      elementId.val( fieldValue );
    }

    // since we use different display field for date we also need to set it.
    // also check for date time field and set the value correctly
    if ( isDateElement && fieldValue ) {
     var dateValues = fieldValue.split(' ');
     
     var actualDateElement =  cj('#field_'+ blockNo +'_' + fname );
     var date_format = actualDateElement.attr('format');
     var altDateFormat = 'yy-mm-dd';

     var displayDateValue = cj.datepicker.parseDate( altDateFormat, dateValues[0] );

     // format date according to display field
     var displayDateValue = cj.datepicker.formatDate( date_format, displayDateValue );
     
     cj('#field_'+ blockNo +'_' + fname + '_display').val( displayDateValue );
     cj('#field_'+ blockNo +'_' + fname + '_time').val(dateValues[1].substr(0,5));
    }
}

</script>
{/literal}

{*include batch copy js js file*}
{include file="CRM/common/batchCopy.tpl"}
