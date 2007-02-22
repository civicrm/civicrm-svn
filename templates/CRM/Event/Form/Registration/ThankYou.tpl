<div class="form-item">
   
     <div id="thankyou_title">
        <p>
        {$thankyou_title}
        </p>
    </div>  
    <div id="thankyou_text">
        <p>
        {$thankyou_text}
        </p>
    </div>
    <div id="help">
        {* PayPal_Standard sets contribution_mode to 'notify'. We don't know if transaction is successful until we receive the IPN (payment notification) *}
        {if $contributeMode EQ 'notify' and $paidEvent}
            <p>
            {ts}Your contribution has been submitted to PayPal for processing. Please print this page for your records.{/ts}
        {/if}
    </div>

    {if $paidEvent}
    <div class="header-dark">
        {ts}Fee Amount{/ts}
    </div>
    <div class="display-block">
        {if $amount}
            {ts}Total Amount{/ts}: <strong>{$amount|crmMoney} {if $amount_level } - {$amount_level} {/if}</strong>
        {/if}
      {ts}Date{/ts}: <strong>{$receive_date|crmDate}</strong><br />
        {if $contributeMode ne 'notify'}
          {ts}Transaction #{/ts}: {$trxn_id}<br />
        {/if}
    </div>
    {else}
    <div class="header-dark">
        {ts}Email{/ts}
    </div>
    <div class="display-block">
        {$email}
    </div>
    {/if}

    {if $customPre}
         {foreach from=$customPre item=field key=cname}
              {if $field.groupTitle}
                {assign var=groupTitlePre  value=$field.groupTitle} 
              {/if}
         {/foreach}
        <div class="header-dark">
          {ts}{$groupTitlePre}{/ts}
         </div>  
         {include file="CRM/UF/Form/Block.tpl" fields=$customPre}
    {/if}

    {if $contributeMode ne 'notify' and $paidEvent}    
    <div class="header-dark">
        {ts}Billing Name and Address{/ts}
    </div>
    <div class="display-block">
        <strong>{$name}</strong><br />
        {$address|nl2br}
    </div>
    {/if}

    {if $contributeMode eq 'direct' and $paidEvent}
    <div class="header-dark">
        {ts}Credit or Debit Card Information{/ts}
    </div>
    <div class="display-block">
        {$credit_card_type}<br />
        {$credit_card_number}<br />
        {ts}Expires{/ts}: {$credit_card_exp_date|truncate:7:''|crmDate}
    </div>
    {/if}

    {if $customPost}
         {foreach from=$customPost item=field key=cname}
              {if $field.groupTitle}
                {assign var=groupTitlePost  value=$field.groupTitle} 
              {/if}
         {/foreach}
        <div class="header-dark">
          {ts}{$groupTitlePost}{/ts}
         </div>  
         {include file="CRM/UF/Form/Block.tpl" fields=$customPost}
    {/if}

    <div id="thankyou_footer_text">
        <p>
        {$thankyou_footer_text}
        </p>
    </div>
</div>
