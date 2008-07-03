{if $context EQ 'Search'}
    {include file="CRM/common/pager.tpl" location="top"}
{/if}

{strip}
<table class="selector">
  <tr class="columnheader">
{if ! $single and $context eq 'Search' }
  <th scope="col" title="Select Rows">{$form.toggleSelect.html}</th> 
{/if}
  {foreach from=$columnHeaders item=header}
    <th scope="col">
    {if $header.sort}
      {assign var='key' value=$header.sort}
      {$sort->_response.$key.link}
    {else}
      {$header.name}
    {/if}
    </th>
  {/foreach}
  </tr>
  {counter start=0 skip=1 print=false}
  {foreach from=$rows item=row}
  <tr id='rowid{$row.pledge_id}' class="{cycle values="odd-row,even-row"}">
     {if ! $single }
        {if $context eq 'Search' }       
            {assign var=cbName value=$row.checkbox}
            <td>{$form.$cbName.html}</td> 
        {/if}	
	<td>{$row.contact_type}</td>
    	<td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{$row.sort_name}</a></td>
    {/if}
    <td>{$row.pledge_amount|crmMoney}</td>	
    <td>{$row.pledge_create_date|truncate:10:''|crmDate}</td>
    <td>{$row.pledge_frequency_interval} {$row.pledge_frequency_unit|capitalize:true}(s) </td>	
    <td>{$row.pledge_start_date|truncate:10:''|crmDate}</td>
    <td>{$row.pledge_status_id}</td>	
    <td>{$row.action}</td>
   </tr>
   <tr id="{$row.pledge_id}_show">
       <td colspan="8">
          <a href="#" onclick="show('paymentDetails{$row.pledge_id}', 'table-row'); buildPaymentDetails('{$row.pledge_id}'); hide('{$row.pledge_id}_show');show('{$row.pledge_id}_hide','table-row');return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/>{ts}Show Payments{/ts}</a>
       </td>
   </tr>
   <tr id="{$row.pledge_id}_hide">
     <td colspan="8">
         <a href="#" onclick="show('{$row.pledge_id}_show', 'table-row');hide('{$row.pledge_id}_hide');return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}open section{/ts}"/>{ts}Hide Payments{/ts}</a>
       <br/>
       <div id="paymentDetails{$row.pledge_id}"></div>
     </td>
  </tr>
 <script type="text/javascript">
     hide('{$row.pledge_id}_hide');
 </script>
  {/foreach}
</table>
{/strip}

{if $context EQ 'Search'}
 <script type="text/javascript">
 {* this function is called to change the color of selected row(s) *}
    var fname = "{$form.formName}";	
    on_load_init_checkboxes(fname);
 </script>
{/if}

{if $context EQ 'Search'}
    {include file="CRM/common/pager.tpl" location="bottom"}
{/if}

{* Build pledge payment details*}
{literal}
<script type="text/javascript">

function buildPaymentDetails( pledgeId )
{
    var dataUrl = {/literal}"{crmURL p='civicrm/pledge/payment' h=0 q='snippet=4&pledgeId='}"{literal} + pledgeId;
	
    var result = dojo.xhrGet({
        url: dataUrl,
        handleAs: "text",
        timeout: 5000, //Time in milliseconds
        handle: function(response, ioArgs){
                if(response instanceof Error){
                        if(response.dojoType == "cancel"){
                                //The request was canceled by some other JavaScript code.
                                console.debug("Request canceled.");
                        }else if(response.dojoType == "timeout"){
                                //The request took over 5 seconds to complete.
                                console.debug("Request timed out.");
                        }else{
                                //Some other error happened.
                                console.error(response);
                        }
                } else {
		   // on success
                   dojo.byId('paymentDetails' + pledgeId).innerHTML = response;
	       }
        }
     });


}
</script>

{/literal}	