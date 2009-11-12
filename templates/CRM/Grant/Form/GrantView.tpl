{* this template is used for viewing grant *} 
<fieldset>
     <legend>{ts}View Grant{/ts}</legend>
     <div class="form-item">
         <dl class="html-adjust">
              <dt class="font-size12pt">{ts}Name{/ts}</dt><dd class="font-size12pt"><strong>{$displayName}</strong>&nbsp;</dd>    
              <dt>{ts}Grant Status{/ts}          </dt> <dd>{$grantStatus}</dd>
              <dt>{ts}Grant Type{/ts}            </dt> <dd>{$grantType}</dd>
              <dt>{ts}Application Received{/ts}  </dt> <dd>{$application_received_date|crmDate}</dd>
              <dt>{ts}Grant Decision{/ts}        </dt> <dd>{$decision_date|crmDate}</dd>
              <dt>{ts}Money Transferred{/ts}     </dt> <dd>{$money_transfer_date|crmDate}</dd>
              <dt>{ts}Grant Report Due{/ts}      </dt> <dd>{$grant_due_date|crmDate}</dd>
              <dt>{ts}Amount Requested{/ts}      </dt> <dd>{$amount_total|crmMoney}</dd>
    	      <dt>{ts}Amount Requested <br />
    	          (original currency){/ts}   </dt> <dd>{$amount_requested|crmMoney}</dd>
              <dt>{ts}Amount Granted{/ts}        </dt> <dd>{$amount_granted|crmMoney}</dd>
              <dt>{ts}Grant Report Received?{/ts}</dt> <dd>{if $grant_report_received}{ts}Yes{/ts} {else}{ts}No{/ts}{/if} </dd>  
              <dt>{ts}Rationale{/ts}             </dt> <dd>{$rationale}</dd>
              <dt>{ts}Notes{/ts}                 </dt> <dd>{$note}</dd>
        </dl>
        {if $attachment}
    	    <div class="spacer"></div>
    	    <dl>
                <dt>{ts}Attachment(s){/ts}</dt><dd>{$attachment}</dd>
            </dl>
        {/if}
    	<div class="spacer"></div>
        {include file="CRM/Custom/Page/CustomDataView.tpl"} 
        <div class="spacer"></div>  
        <dl class="html-adjust buttons">
             <dt></dt><dd>{$form.buttons.html}</dd>
        </dl>
    </div>
</fieldset>
    
