{if $action & 1024}
    {include file="CRM/Contribute/Form/Contribution/PreviewHeader.tpl"}
{/if}

{include file="CRM/common/TrackingFields.tpl"}

<div class="form-item">
    <div id="help">
        <p>{ts}Please verify the information below carefully. Click <strong>Go Back</strong> if you need to make changes.{/ts}
            {if $contributeMode EQ 'notify' and ! $is_pay_later}
                {if $paymentProcessor.payment_processor_type EQ 'Google_Checkout'} 
                    {ts}Click the <strong>Google Checkout</strong> button to checkout to Google, where you will select your payment method and complete the contribution.{/ts}
                {else} 
                    {ts 1=$paymentProcessor.processorName 2=$button}Click the <strong>%2</strong> button to go to %1, where you will select your payment method and complete the contribution.{/ts}
                {/if} 
            {elseif ! $is_monetary or $amount LE 0.0 or $is_pay_later}
                {ts 1=$button}To complete this transaction, click the <strong>%1</strong> button below.{/ts}
            {else}
                {ts 1=$button}To complete your contribution, click the <strong>%1</strong> button below.{/ts}
            {/if}
        </p> 
    </div>
    {if $is_pay_later}
        <div class="bold pay_later_receipt-section">{$pay_later_receipt}</div>
    {/if}
    
    {include file="CRM/Contribute/Form/Contribution/MembershipBlock.tpl" context="confirmContribution"}

    {if $amount GT 0 OR $minimum_fee GT 0 OR ( $priceSetID and $lineItem ) }
    <div class="amount_display-group">
        <div class="header-dark">
            {if !$membershipBlock AND $amount OR ( $priceSetID and $lineItem ) }{ts}Contribution Amount{/ts}{else}{ts}Membership Fee{/ts} {/if}
        </div>
        <div class="display-block">
            {if $lineItem and $priceSetID}
            {if !$amount}{assign var="amount" value=0}{/if}
            {assign var="totalAmount" value=$amount}
                {include file="CRM/Price/Page/LineItem.tpl" context="Contribution"}
            {elseif $is_separate_payment }
                {if $amount AND $minimum_fee}
                    {$membership_name} {ts}Membership{/ts}: <strong>{$minimum_fee|crmMoney}</strong><br />
                    {ts}Additional Contribution{/ts}: <strong>{$amount|crmMoney}</strong><br />
                    <strong> -------------------------------------------</strong><br />
                    {ts}Total{/ts}: <strong>{$amount+$minimum_fee|crmMoney}</strong><br />
                {elseif $amount }
                    {ts}Amount{/ts}: <strong>{$amount|crmMoney} {if $amount_level } - {$amount_level} {/if}</strong>
                {else}
                    {$membership_name} {ts}Membership{/ts}: <strong>{$minimum_fee|crmMoney}</strong>
                {/if}
            {else}
                {if $amount }
                    {ts}Total Amount{/ts}: <strong>{$amount|crmMoney} {if $amount_level } - {$amount_level} {/if}</strong>
                {else}
                    {$membership_name} {ts}Membership{/ts}: <strong>{$minimum_fee|crmMoney}</strong>
                {/if}
            {/if}
            {if $is_recur}
                {if $installments}
                    <p><strong>{ts 1=$frequency_interval 2=$frequency_unit 3=$installments}I want to contribute this amount every %1 %2(s) for %3 installments.{/ts}</strong></p>
                {else}
                    <p><strong>{ts 1=$frequency_interval 2=$frequency_unit}I want to contribute this amount every %1 %2(s).{/ts}</strong></p>
                {/if}
                <p>{ts}Your initial contribution will be processed once you complete the confirmation step. You will be able to modify or cancel future contributions at any time by logging in to your account.{/ts}</p>
            {/if}
            {if $is_pledge }
                {if $pledge_frequency_interval GT 1}
                    <p><strong>{ts 1=$pledge_frequency_interval 2=$pledge_frequency_unit 3=$pledge_installments}I pledge to contribute this amount every %1 %2s for %3 installments.{/ts}</strong></p>
                {else}
                    <p><strong>{ts 1=$pledge_frequency_interval 2=$pledge_frequency_unit 3=$pledge_installments}I pledge to contribute this amount every %2 for %3 installments.{/ts}</strong></p>
                {/if}
                {if $is_pay_later}
                    <p>{ts 1=$receiptFromEmail 2=$button}Click &quot;%2&quot; below to register your pledge. You will be able to modify or cancel future pledge payments at any time by logging in to your account or contacting us at %1.{/ts}</p>
                {else}
                    <p>{ts 1=$receiptFromEmail 2=$button}Your initial pledge payment will be processed when you click &quot;%2&quot; below. You will be able to modify or cancel future pledge payments at any time by logging in to your account or contacting us at %1.{/ts}</p>
                {/if}
            {/if}
        </div>
    </div>
    {/if}
        
    {include file="CRM/Contribute/Form/Contribution/Honor.tpl"}
    {if $customPre}
        {foreach from=$customPre item=field key=cname}
            {if $field.groupTitle}
                {assign var=groupTitlePre  value=$field.groupTitle} 
            {/if}
        {/foreach}
        <div class="header-dark">
            {$groupTitlePre}
        </div>  
        {include file="CRM/UF/Form/Block.tpl" fields=$customPre}
    {/if}
    {if $pcpBlock}
    <div class="pcp-display-group">
        <div class="header-dark">
            {ts}Contribution Honor Roll{/ts}
        </div>
        <div class="display-block">
            {if $pcp_display_in_roll}
                {ts}List my contribution{/ts}
                {if $pcp_is_anonymous}
                    <strong>{ts}anonymously{/ts}.</strong>
                {else}
		    {ts}under the name{/ts}: <strong>{$pcp_roll_nickname}</strong><br/>
                    {if $pcp_personal_note}
                        {ts}With the personal note{/ts}: <strong>{$pcp_personal_note}</strong>
                    {else}
                     <strong>{ts}With no personal note{/ts}</strong>
                     {/if}
                {/if}
            {else}
                {ts}Don't list my contribution in the honor roll.{/ts}
            {/if}
            <br />
        </div>
    </div>
    {/if}
    {if $onBehalfName}
    <div class="onBehalf-display-group">
        <div class="header-dark">
            {ts}On Behalf Of{/ts}
        </div>
        <div class="display-block">
            <strong>{$onBehalfName}</strong><br />
            {$onBehalfAddress|nl2br}
        </div>
        <div class="display-block">
            {$onBehalfEmail}
        </div>
    </div>
    {/if}

    {if ( $contributeMode ne 'notify' and ! $is_pay_later and $is_monetary and ( $amount GT 0 OR $minimum_fee GT 0 ) ) or $email }
    <div class="billing_name_address-group">
        {if $contributeMode ne 'notify' and ! $is_pay_later and $is_monetary and ( $amount GT 0 OR $minimum_fee GT 0 ) }
        <div class="header-dark">
            {ts}Billing Name and Address{/ts}
        </div>
        <div class="section billingName-section">
        		<div class="label">Name</div>
        		<div class="content">{$billingName}</div>
        		<div class="clear"/>
        	</div>
        	<div class="section billing_address-section">
        		<div class="label">Address</div>
        		<div class="content">{$address|nl2br}</div>
        		<div class="clear"/>
        	</div>
        {/if}
        {if $email}
        <div class="header-dark">
            {ts}Your Email{/ts}
        </div>
        <div class="display-block">
            {$email}
        </div>
        {/if}
    </div>
    {/if}
    
    {if $contributeMode eq 'direct' and ! $is_pay_later and $is_monetary and ( $amount GT 0 OR $minimum_fee GT 0 ) }
    <div class="credit_card-group">
        <div class="header-dark">
        {if $paymentProcessor.payment_type & 2}
             {ts}Direct Debit Information{/ts}
        {else}
            {ts}Credit Card Information{/ts}
        {/if}
        </div>
        <div class="display-block">
        {if $paymentProcessor.payment_type & 2}
            {ts}Account Holder{/ts}: {$account_holder}<br />
            {ts}Bank Account Number{/ts}: {$bank_account_number}<br />
            {ts}Bank Identification Number{/ts}: {$bank_identification_number}<br />
            {ts}Bank Name{/ts}: {$bank_name}<br />
        {else}
        	<div class="section credit_card_type-section">
        		<div class="label">Card Type</div>
        		<div class="content">{$credit_card_type}</div>
        		<div class="clear"/>
        	</div>
        	<div class="section credit_card_number-section">
        		<div class="label">Card Number</div>
        		<div class="content">{$credit_card_number}</div>
        		<div class="clear"/>
        	</div>
        	<div class="section credit_card_expiration-section">
        		<div class="label">{ts}Expires{/ts}</div>
        		<div class="content">{$credit_card_exp_date|truncate:7:''|crmDate}</div>
        		<div class="clear"/>
        	</div>
        {/if}
        </div>
    </div>
    {/if}
    
    {include file="CRM/Contribute/Form/Contribution/PremiumBlock.tpl" context="confirmContribution"}
    
    {if $customPost}
         {foreach from=$customPost item=field key=cname}
            {if $field.groupTitle}
                {assign var=groupTitlePost  value=$field.groupTitle} 
            {/if}
        {/foreach}
        <div class="header-dark">
            {$groupTitlePost}
        </div>  
        {include file="CRM/UF/Form/Block.tpl" fields=$customPost}
    {/if}
  
    {if $contributeMode eq 'direct' and $paymentProcessor.payment_type & 2}
    <div class="debit_agreement-section">
        <div class="header-dark">
            {ts}Agreement{/ts}
        </div>
        <div class="display-block">
        {ts}Your account data will be used to charge your bank account via direct debit. While submitting this form you agree to the charging of your bank account via direct debit.{/ts}
        </div>
    </div>
    {/if}

    {if $contributeMode NEQ 'notify' and $is_monetary and ( $amount GT 0 OR $minimum_fee GT 0 ) } {* In 'notify mode, contributor is taken to processor payment forms next *}
    <div class="messages status continue_instructions-section">
        <p>
        {if $is_pay_later OR $amount LE 0.0}
            {ts 1=$button}Your transaction will not be completed until you click the <strong>%1</strong> button. Please click the button one time only.{/ts}
        {else}
            {ts 1=$button}Your contribution will not be completed until you click the <strong>%1</strong> button. Please click the button one time only.{/ts}
        {/if}
        </p>
    </div>
    {/if}
    
    {if $paymentProcessor.payment_processor_type EQ 'Google_Checkout' and $is_monetary and ( $amount GT 0 OR $minimum_fee GT 0 ) and ! $is_pay_later}
        <fieldset class="google_checkout-group"><legend>{ts}Checkout with Google{/ts}</legend>
        <table class="form-layout-compressed">
            <tr>
                <td class="description">{ts}Click the Google Checkout button to continue.{/ts}</td>
            </tr>
            <tr>
                <td>{$form._qf_Confirm_next_checkout.html} <span style="font-size:11px; font-family: Arial, Verdana;">Checkout securely.  Pay without sharing your financial information. </span></td>
            </tr>
        </table>
        </fieldset>    
    {/if}

    <div id="crm-submit-buttons">
        {$form.buttons.html}
    </div>
</div>
