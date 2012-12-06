{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
{if $action == 1 || $action == ""}
<div class="crm-section int_amount-section" >
{if $paymentMode eq 'online'}
 <table> 
   <tr>
      <td>
         <div class="label">{$form.int_amount.label}</div>     
         <div class='content'> {$form.int_amount.html}
           <div class="crm-section {$form.initial_amount.name}-section">
	    {$form.initial_amount.html}
            <p><span class="description">{$initialAmountHelpText}</span></p>
           </div>
         </div>
        <div class="crm-section {$form.initial_amount.name}-section">
        {if $context}
        <table id = 'option_item'>
          <tr id="adjust-option-items" class="crm-contribution-form-block-option_type">
            <td>{$form.option_items.html}</td>
          </tr>
        </table>
      {/if}
      {if $context}
        <div id='dynamic_elements'></div>
        <div id='unallocatedAmount'></div>
      {/if}
    </div>
  </td>
 </tr>
</table>
{else}
 <table class="form-layout-compressed"><tr>
    <td class="label">
      {$form.int_amount.html}</td>
    <td class="label">{$form.int_amount.label}</td></tr>
  </table>
  <div>
    <div class="crm-section {$form.initial_amount.name}-section">
      <table class="form-layout-compressed">
        <tr><td class="label">{$form.initial_amount.label}</td>
          <td >
            {$form.initial_amount.html}
            <p><span class="description">{$initialAmountHelpText}</span></p>
          </td></tr>
      </table>
      {if $context}
        <table id = 'option_item'>
          <tr id="adjust-option-items" class="crm-contribution-form-block-option_type">
            <td>{$form.option_items.html}</td>
          </tr>
        </table>
      {/if}
      {if $context}
        <div id='dynamic_elements'></div>
        <div id='unallocatedAmount'></div>
      {/if}
    </div>
  </div>
{/if}
</div>
{elseif $action == 2 }
<div class="content">
  <table>
    <tr class="crm-contribution-form-block-receive_date">
      <td class="label" >{$form.initial_amount.label}</td>
      <td>{$form.initial_amount.html}</td>
    </tr>

    {foreach from=$form item=element key=keys}
      {if $keys eq 'txt-price'}
        <tr>
          <td>{ts}Applied to items{/ts}</td>
          <td><div id = "initialPayment">
            <table>
              <th>{ts}Item{/ts}</th>
              <th>{ts}Item Amount{/ts}</th>
              <th>{ts}Payment Amount{/ts}</th>
              {foreach from=$element item=subElement key=subkeys}
                <tr>
                  <td>{$subElement.label}</td>
                  <td>{$line[$subElement.name]}</td>
                  <td>{$subElement.html}&nbsp;<input type='checkbox' id = 'cb-{$subElement.id|substr:4}' name = 'cb-{$subElement.id|substr:4}' class = 'payFull' price = '{$line[$subElement.name]}' > Pay Full </td>
                </tr>
              {/foreach}
            </table>
            <div id='unallocatedAmount'></div>
          </div>
          </td>
        </tr>
      {/if}
    {/foreach}

  </table>
</div>
{/if}
<script type="text/javascript">
{literal}
var optionSep      = '|';
var totalfee = 0;
cj(function(){
  cj('.initial_amount-section').hide();
  cj('.distribute').live('blur', function() {
    cj('input:radio[name=option_items]:eq(1)').attr('checked','checked');
    var enterAmount = parseFloat(cj(this).val());
    var cbID = Array( );
    cbID = cj(this).attr('id').split('-');
    var ID = 'cb-'+cbID[1] ;
    var totalAmount = parseFloat(cj("input[id='"+ID+"']").attr('price'));

    if(totalAmount == enterAmount) {
      cj("input[id='"+ID+"']").attr('checked','checked');
    }else if(totalAmount < enterAmount){
      cj("input[id='"+ID+"']").removeAttr('checked');
      alert('Amount greater');
      cj(this).val(totalAmount);
    }

    calculateUnlocate();
  });

  cj('#int_amount').click(function(){
    if ( cj( this ).attr('checked') )
      apendValue();
  });

  
  if( cj('.int_amount-section input:checkbox').attr('checked') ){
    cj('.initial_amount-section').show();
  }else{
    cj('.initial_amount-section').hide();
  }

  cj('.payFull').live('click', function(){
    var txtID = Array( );
    txtID =cj(this).attr('id').split('-');
    var quickConfig = '{/literal}{$quickConfig}{literal}';
    if (quickConfig != 0) {
      var amount = cj(this).attr('price');
    } else {
      var amount = parseFloat('{/literal}{$totalAmount}{literal}').toFixed(2);
      isNaN( amount )? amount = totalfee : "" ;
    }
    if (cj(this).attr('checked')) {
      var fullAmount = parseFloat( cj(this).attr('price')).toFixed(2);
      cj('input:radio[name=option_items]:eq(1)').attr('checked','checked');
    }else{
      fullAmount = cj('#initial_amount').val();
      fullAmountRate = parseFloat( amount /fullAmount );
      var price = cj(this).attr('price');
      fullAmount = parseFloat( price /fullAmountRate).toFixed(2);
    }
    var ID = 'txt-'+txtID[1] ;
    cj("input[id='"+ID+"']").val(fullAmount);
    calculateUnlocate();
  });

  cj('input:radio[name=option_items]').click(function(){
    var quickConfig = '{/literal}{$quickConfig}{literal}';
    if (quickConfig != 0)	{
      eval( 'var option = ' + cj('#priceset input:radio:checked').attr('price') ) ;
      var amount   = parseFloat( option[1].split('|'));
    } else {
      var amount =  parseFloat('{/literal}{$totalAmount}{literal}').toFixed(2);
      isNaN( amount )? amount = totalfee : "" ;
    }
    var int_amount = cj('#initial_amount').val();
    if (amount < int_amount) {
      alert('Initial Amount is Greater');
      return false;
    }
    apendValue();
  });

  cj('#priceset .form-select').change(function() {
    apendValue();
  });

  cj('.int_amount-section input:checkbox').click(function(){
    if (cj( this ).attr('checked') ) {
      cj('.initial_amount-section').show();
      cj('#dynamic_elements,#unallocatedAmount, #option_item').hide();
    } else {
      cj('.initial_amount-section').hide();
      cj('#dynamic_elements,#unallocatedAmount, #option_item').hide();
    }
    if (cj('#pricevalue').length) {
      apendValue();
    }
  });

  cj("#priceset input,#priceset select,#priceset,#feeBlock input,#feeBlock select,#feeBlock").click(function(){
    apendValue();
  });

  cj('#priceset input:text').blur(function(){
    apendValue();
  });

  cj('#initial_amount').change(function(){
    var quickConfig = '{/literal}{$quickConfig}{literal}';
    if (quickConfig != 0)	{
      eval( 'var option = ' + cj('#priceset input:radio:checked').attr('price') ) ;
      var amount   = parseFloat( option[1].split('|'));
    } else {
      var amount = parseFloat('{/literal}{$totalAmount}{literal}').toFixed(2);
      if (cj('#pricevalue').length) {
        isNaN( amount )? amount = totalfee : "" ;
      } else {
        amount = parseInt( cj("#total_amount").val() );
      }
    }
    var int_amount = parseInt( cj('#initial_amount').val() );
    if (amount < int_amount) {
      alert('Initial Amount is Greater');
      return false;
    }

    var action = '{/literal}{$action}{literal}';
    if (action == 2) {
      feelTextbox();
    } else {
      cj('#pricevalue').length ? apendValue(): "";
    }
  });
});

cj.fn.getParent = function(num) {
  var last = this[0];
  for (var i = 0; i < num; i++) {
    last = last.parentNode;
  }
  return cj(last);
};

function apendValue(){
  var counter = 0;

  var options = "<div id = 'initialPayment'><table><th>Item</th><th>Amount</th>";
var quickConfig = '{/literal}{$quickConfig}{literal}';
  if(quickConfig != 0)	{
    eval( 'var option = ' + cj('#priceset input:radio:checked').attr('price') ) ;
    var amount   = parseFloat( option[1].split('|'));
  }else{
    amount = totalfee;
  }

  if( cj('.int_amount-section input:checkbox').attr('checked') ){
    cj('.initial_amount-section').show();
  }else{
    cj('.initial_amount-section').hide();
  }

  var int_amount = parseFloat(cj('#initial_amount').val()).toFixed(2);
  var int_amt;
  if (amount != 0 && int_amount != 0 )	{
    int_amt =  parseFloat( amount / int_amount ).toFixed(3);
  }
  var unlocatedAmount = parseInt( int_amount );
  var distribute;

  cj("#priceset input,#priceset select,#priceset").each(function (){
    if ( cj(this).attr('price') ) {
      var eleType =  cj(this).attr('type');
      if ( this.tagName == 'SELECT' ) {
        eleType = 'select-one';
      }

      switch( eleType ) {
        case 'checkbox':
          var checkboxparentName = cj(this).getParent(3).prev().find("label").html();
          if ( cj(this).attr('checked') ) {
            counter ++;
            if (int_amount != 0 && int_amount != "") {
              eval( 'var option = ' + cj(this).attr('price') ) ;
              optionPart = option[1].split(optionSep);
              var checkPrice =  optionPart[0];
              checkAmount   = parseFloat( checkPrice/int_amt ).toFixed(2);
            } else {
              checkAmount = 0;
            }

            var for1 = cj(this).attr('id');
            var lable = cj("label[for='"+for1+"']").text();
            options += "<tr><td>"+checkboxparentName +"-"+ lable +"</td><td><input type='text' id= 'txt-"+for1+"' name = 'txt-"+for1+"' value ='"+checkAmount +"' size='4' class= 'distribute'>&nbsp<input type='checkbox' id= 'cb-"+for1+"' name = 'cb-"+for1+"' price = '"+ checkPrice +"' class = 'payFull' /> Pay Full</td></tr>" ;
          }
          break;

        case 'radio':
          var radioparentName = cj(this).getParent(3).prev().find("label").html();
          if ( cj(this).attr('checked') && cj(this).val() != 0 )	{
            counter ++;
            if ( int_amount != 0 && int_amount != ""){
              eval( 'var option = ' + cj(this).attr('price') ) ;
              optionPart = option[1].split(optionSep);
              var radioPrice = optionPart[0] ;
              radioAmount   = parseFloat(radioPrice/int_amt).toFixed(2);
            } else {
              radioAmount = 0;
            }
            var id =  cj(this).attr('name');
            var for1 = cj(this).attr('id');
            var value = cj(this).attr('value');
            var lable = cj("label[for='"+for1+"']").text();
            options += "<tr><td>"+radioparentName +"-"+ lable +"</td><td><input type='text' id= 'txt-"+id+"["+value+"]' name = 'txt-"+id+"["+value+"]' value ='"+radioAmount +"' size='4' class= 'distribute' >&nbsp<input type='checkbox' id= 'cb-"+id+"["+value+"]' name = 'cb-"+id+"["+value+"]' price = '"+ radioPrice +"' class = 'payFull' /> Pay Full</td></tr>" ;
          }
          break;

        case 'text':
          var textboxparentName = cj(this).parent().prev().find("label").html();
          var textval = parseFloat( cj(this).val() ).toFixed(3);
          eval( 'var option = ' + cj(this).attr('price') ) ;
          if (textval) {
            counter ++;
            if (int_amount != 0 && int_amount != "") {
              optionPart = option[1].split(optionSep);
              var addprice   =  optionPart[0];
              var curval = parseFloat(textval * addprice).toFixed(2);
              txtprice   = parseFloat(curval/int_amt).toFixed(2);
            } else {
              txtprice = 0;
            }

            var value  = option[0];
            var id = cj(this).attr('id');
            options += "<tr><td>"+ textboxparentName +"-"+ curval +"</td><td><input type='text' id= 'txt-"+id+"["+value+"]' name = 'txt-"+id+"["+value+"]' value ='"+txtprice +"'size='4'  class= 'distribute' >&nbsp<input type='checkbox'  id= 'cb-"+id+"["+value+"]' name = 'cb-"+id+"["+value+"]' price = '"+ curval +"' class = 'payFull'/> Pay Full</td></tr>" ;
          }
          break;

        case 'select-one':
          var selectparentName = cj(this).parent().prev().find("label").html();
          if (cj(this).val( ) != "") {
            counter ++;
            if (int_amount != 0 && int_amount != "") {
              eval( 'var selectedText = ' + cj(this).attr('price') );
              optionPart  = selectedText[cj(this).val( )].split(optionSep);
              var  selectPrice = optionPart[0];
              selectAmount = parseFloat(selectPrice /int_amt).toFixed(2);
            } else {
              selectAmount = 0;
            }
            var lable = cj(this).find(":selected").text();
            var value = cj(this).attr('value');
            var id = cj(this).attr('id');
            options += "<tr><td>"+ selectparentName +"-"+ lable +"</td><td><input type='text' id= 'txt-"+id+"["+value+"]' name = 'txt-"+id+"["+value+"]' value ='"+ selectAmount +"' size='4' class= 'distribute' >&nbsp<input type='checkbox' id= 'cb-"+id+"["+value+"]' name = 'cb-"+id+"["+value+"]' price = '"+ selectPrice +"' class = 'payFull' /> Pay Full</td></tr>" ;
          }
          break;
      }
    }

  });
  cj('#dynamic_elements').html(options + "</table></div>");
  if (counter <=1){
    cj('#dynamic_elements,#unallocatedAmount, #option_item').hide();
  } else {
    cj('#dynamic_elements,#unallocatedAmount, #option_item').show();
  }
  calculateUnlocate();
}

function calculateUnlocate(){
  var unlocatedAmount = cj('#initial_amount').val();
  var totalAmount = 0;
  cj(".distribute").each(function(){
    unlocatedAmount = parseFloat( unlocatedAmount - cj(this).val( )).toFixed(2);
    totalAmount     = parseFloat( totalAmount ) + parseFloat( cj(this).val( ) );
  });
  var action = '{/literal}{$action}{literal}';
  var roundAmount =  parseFloat( cj('#initialPayment').find('input:first').val()) + parseFloat(unlocatedAmount);
  if ( unlocatedAmount > 0.00 && action == 2){
    cj('#initialPayment').find('input:first').val(roundAmount);
    unlocatedAmount = '0.00';
  } else if ( unlocatedAmount > 0.00 && action != 2 && cj('input:radio[name=option_items]:eq(0)').attr('checked') ){
    cj('#initialPayment').find('input:first').val(roundAmount);
    unlocatedAmount = '0.00';
  }
  cj('#unallocatedAmount').html("<table><tr><td>Unallocated Amount:<strong> $ "+ unlocatedAmount +"</strong></td></tr></table><input type= 'hidden' id= 'hidden-amount' name= 'hidden-amount' value='"+totalAmount+"' >");
}

function feelTextbox(){
  var amount = parseFloat('{/literal}{$totalAmount}{literal}').toFixed(2);
  cj('#initialPayment input:text').each(function (){
    fullAmount = cj('#initial_amount').val();
    fullAmountRate = parseFloat( amount /fullAmount );
    var price = cj(this).attr('price');
    fullAmount = parseFloat( price /fullAmountRate).toFixed(2);
    cj(this).val(fullAmount);
  });
  calculateUnlocate();
}
{/literal}
</script>