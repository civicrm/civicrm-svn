{if $paid} {* We retrieve this tpl when event is selected - keep it empty if event is not paid *} 
<fieldset>
    <div class="form-item">
    <table class="form-layout">
    {if $priceSet}
     <tr>  
     <td class="label">{$form.amount.label}</td>
     <td><table class="form-layout-compressed">
         {if $priceSet.help_pre AND $action eq 1}
            <tr><td colspan=2><span class="description">{$priceSet.help_pre}</span></td></tr>
         {/if}
      {foreach from=$priceSet.fields item=element key=field_id}
         {if ($element.html_type eq 'CheckBox' || $element.html_type == 'Radio') && $element.options_per_line}
            {assign var="element_name" value=price_$field_id}
            {assign var="count" value="1"}
            <tr><td class="label">{$form.$element_name.label}</td>
                <td class="view-value">
                <table class="form-layout-compressed">
                <tr>	
		{foreach name=outer key=key item=item from=$form.$element_name}
                    {if is_numeric($key) }
                        <td class="labels font-light"><td>{$form.$element_name.$key.html}</td>
                        {if $count == $element.options_per_line}
                            {assign var="count" value="1"}
                            </tr>
                            <tr>			
                        {else}
                            {assign var="count" value=`$count+1`}
                        {/if}
                    {/if}
                {/foreach}
                </tr>
                {if $element.help_post AND $action eq 1}
                    <tr><td></td><td><span class="description">{$element.help_post}</span></td></tr>
                {/if}
                </table>
              </td>
            </tr>
          {else}	
            {assign var="name" value=`$element.name`}
            {assign var="element_name" value="price_"|cat:$field_id}
            <tr><td class="label"> {$form.$element_name.label}</td>
                <td class="view-value">{$form.$element_name.html}
                    {if $element.help_post AND $action eq 1}
                        <br /><span class="description">{$element.help_post}</span>
                    {/if}
               </td>
            </tr>
          {/if}
       {/foreach}
         {if $priceSet.help_post AND $action eq 1}
            <tr><td colspan=2><span class="description">{$priceSet.help_post}</span></td></tr>
         {/if}
      </table>
    </td>
</tr>
<tr><td></td>
    <td align="left">
      <table id="pricelabel" style="display:none" class="form-layout">
        <tr>  <td class="label"><label>Total Fee(s)</label> </td> <td id="pricevalue" class="view-value"></td> </tr>
      </table>
    </td>
    </tr>
    {else} {* NOT Price Set *}
     <tr>
     <td class ='html-adjust' colspan=2>
     	<dl class="html-adjust">
        {if $form.discount_id.label}
            <dt class="label">{$form.discount_id.label}</dt><dd>{$form.discount_id.html}</dd>
        {/if}
        <dt class="label">{$form.amount.label}</dt><dd>{$form.amount.html}
        {if $action EQ 1}
            <br /><span class="description">{ts}Event Fee Level (if applicable).{/ts}</span>
        {/if}
        </dd>
     	</dl>
     </td>
     </tr>
    {/if}

    {if $accessContribution and ! $participantMode and ! ($action eq 2 and $rows.0.contribution_id) }
        <tr>
        <td class="label">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$form.record_contribution.label}</td>
        <td class="html-adjust">{$form.record_contribution.html}<br />
            <span class="description">{ts}Check this box to enter payment information. You will also be able to generate a customized receipt.{/ts}</span>
        </td>
        </tr>
        <tr id="payment_information">
           <td class ='html-adjust' colspan=2>
           <fieldset><legend>{ts}Payment Information{/ts}</legend>
             <dl id="recordContribution" class="html-adjust">	        
                <dt class="label">{$form.contribution_type_id.label}</dt>
                <dd>{$form.contribution_type_id.html}<br /><span class="description">{ts}Select the appropriate contribution type for this payment.{/ts}</span></dd>
                <dt class="label" >{$form.receive_date.label}</dt><dd>{$form.receive_date.html}
                {include file="CRM/common/calendar/desc.tpl" trigger=trigger_membership}
                {include file="CRM/common/calendar/body.tpl" dateVar=receive_date startDate=currentYear endDate=endYear offset=10 trigger=trigger_membership}</dd> 
                <dt class="label">{$form.payment_instrument_id.label}</dt><dd>{$form.payment_instrument_id.html}</dd>
        	<div id="checkNumber"><dt class="label">{$form.check_number.label}</dt><dd>{$form.check_number.html}</dd></div>
	        {if $showTransactionId }	
                    <dt class="label">{$form.trxn_id.label}</dt><dd>{$form.trxn_id.html}</dd>	
                {/if}	
                <dt class="label">{$form.contribution_status_id.label}</dt><dd>{$form.contribution_status_id.html}</dd>      
             </dl>
           </fieldset>
           </td>
        </tr>

        {* Record contribution field only present if we are NOT in submit credit card mode (! participantMode). *}
        {include file="CRM/common/showHideByFieldValue.tpl" 
            trigger_field_id    ="record_contribution"
            trigger_value       =""
            target_element_id   ="payment_information" 
            target_element_type ="table-row"
            field_type          ="radio"
            invert              = 0
        }
    {/if}
    </table>
    </div>
</fieldset>
{/if}

{* credit card block when it is live or test mode*}
{if $participantMode and $paid}	
  <div class="spacer"></div>
  {include file='CRM/Core/BillingBlock.tpl'}
{/if}

{if ($email OR $batchEmail) and $outBound_option != 2}
    <fieldset><legend>{if $paid}{ts}Registration Confirmation and Receipt{/ts}{else}{ts}Registration Confirmation{/ts}{/if}</legend>  
      <div class="form-item">
		 <dl> 
            <dt class="label">{if $paid}{ts}Send Confirmation and Receipt{/ts}{else}{ts}Send Confirmation{/ts}{/if}</dt>
            <dd class ='html-adjust' >{$form.send_receipt.html}<br>
                     <span class="description">{ts}Automatically email a confirmation {if $paid} and receipt {/if} to {$email}?{/ts}</span></dd>
        </dl>
        <div id='notice'>
            <dl>
 			<dt class="label">{$form.receipt_text.label}</dt> 
                	<dd class="html-adjust"><span class="description">{ts}Enter a message you want included at the beginning of the confirmation email. EXAMPLE: 'Thanks for registering for this event.'{/ts}</span><br/>
                    {$form.receipt_text.html|crmReplace:class:huge}</dd>
            </dl>
        </div> 
      </div>
    </fieldset>
{/if}
{if $email and $outBound_option != 2} {* Send receipt field only present if contact has a valid email address. *}
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    ="send_receipt"
    trigger_value       =""
    target_element_id   ="notice" 
    target_element_type ="block"
    field_type          ="radio"
    invert              = 0
}
{/if}
{literal} 
<script type="text/javascript">
var totalfee=0;
var symbol = '{/literal}{$currencySymbol}{literal}';
if ('{/literal}{$defaultScriptFee}{literal}'!= '' ) {
  document.Participant.scriptFee.value = parseFloat('{/literal}{$defaultScriptFee}{literal}');
  document.Participant.scriptArray.value = ',{/literal}{$defaultScriptArray}{literal}';
}
if(document.Participant.scriptFee.value){
  totalfee = parseFloat(document.Participant.scriptFee.value);
  document.getElementById('pricelabel').style.display = "block";
  document.getElementById('pricevalue').innerHTML = "<b>"+symbol+"</b> "+totalfee;
  document.Participant.scriptFee.value = parseFloat('0');
}
var price = new Array();
if(document.Participant.scriptArray.value){
  price = document.Participant.scriptArray.value.split(',');
}
function addPrice(priceVal, priceId) {
  var op  = document.getElementById(priceId).type;
  var ele = document.getElementById(priceId).name.substr(6);
  if (op == 'checkbox') {
    var chek = ele.split('[');
    ele = chek[0];
  }
  if(!price[ele]) {
    price[ele] = parseFloat('0');
  }
  var addprice = 0;
  var priceset = 0;
  if(op != 'select-one') {
    priceset = priceVal.split(symbol);
  
    var priceArray = priceset[1].split(',');
    var Actualprice= "";
    for( i=0 ;i<priceArray.length ; i++ ){
      Actualprice =Actualprice+priceArray[i]; 
    }
  }
  if (priceset != 0) {
    var addprice = parseFloat(Actualprice);
  }
  switch(op)
    {
    case 'checkbox':
      if(document.getElementById(priceId).checked) {
	totalfee   += addprice;
	price[ele] += addprice;
      }else{
	totalfee   -= addprice;
	price[ele] -= addprice;
      }
      break;    
      
    case 'radio':
      totalfee = parseFloat(totalfee) + addprice - parseFloat(price[ele]);
      price[ele] = addprice;
      break;
      
    case 'text':
      var textval = parseFloat(document.getElementById(priceId).value);
      var curval = textval * addprice;
      if(textval>=0){
	totalfee = parseFloat(totalfee) + curval - parseFloat(price[ele]);
	price[ele] = curval;
      }else {
	totalfee = parseFloat(totalfee) - parseFloat(price[ele]);	
	price[ele] = parseFloat('0');
      }

      break;
      
    case 'select-one':
      var index = parseInt(document.getElementById(priceId).selectedIndex);
      var myarray = ['','{/literal}{$selectarray}{literal}'];
      if(index>0) {
	var selectvalue = myarray[index].split(symbol);
	totalfee = parseFloat(totalfee) + parseFloat(selectvalue[1]) - parseFloat(price[ele]);
	price[ele] = parseFloat(selectvalue[1]);
      }else {
	totalfee = parseFloat(totalfee) - parseFloat(price[ele]);
	price[ele] = parseFloat('0');
      }	
      break;
      
    }//End of swtich loop
  
  if( totalfee>0 ){
    document.getElementById('pricelabel').style.display = "block";
    document.getElementById('pricevalue').innerHTML = "<b>"+symbol+"</b> "+totalfee;
    document.Participant.scriptFee.value = totalfee;
    document.Participant.scriptArray.value = price;
  } else{
    document.getElementById('pricelabel').style.display = "none";
  }
}
</script>
{/literal}
{if $action eq 1 and !$participantMode} 
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    ="payment_instrument_id"
    trigger_value       = '4'
    target_element_id   ="checkNumber" 
    target_element_type ="table-row"
    field_type          ="select"
    invert              = 0
}
{/if} {* ADD mode if *}    