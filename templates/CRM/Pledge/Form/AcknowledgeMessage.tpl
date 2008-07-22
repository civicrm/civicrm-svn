{ts 1=$contact.display_name}Dear %1{/ts},

{ts}Thank you for your generous pledge. Please print this acknowledgment for your records.{/ts}

===========================================================
{ts}Pledge Information{/ts}

===========================================================
{ts}Pledge Received{/ts} : {$create_date|truncate:10:''|crmDate}
{ts}Total Pledge Amount{/ts} : {$amount|crmMoney}

===========================================================
{ts}Payment Schedule{/ts}

===========================================================
{ts 1=$eachPaymentAmount|crmMoney 2=$frequency_interval 3=$frequency_unit 4=$installments}%1 every %2 %3 for %4 installments.{/ts}
{if $frequency_day}

{ts 1=$frequency_day 2=$frequency_unit}Payments are due on day %1 of the %2.{/ts}
{/if}

{assign var="count" value="1"}
{foreach from=$payments item=payment}
Payment {$count} : {$payment.amount|crmMoney} due {$payment.due_date|truncate:10:''|crmDate}
{assign var="count" value=`$count+1`}
{/foreach}
 
{ts 1=$domain.phone 2=$domain.email}Please contact us at %1 or send email to %2 if you have questions
or need to modify your payment schedule.{/ts}


{if $honor_block_is_active}
===========================================================
{$honor_type}
===========================================================
{$honor_prefix} {$honor_first_name} {$honor_last_name}
{if $honor_email}
{ts}Honoree Email{/ts} : {$honor_email}
{/if}
{/if}

{if $customData}
===========================================================
{ts}{$customDataTitle} {/ts}

===========================================================
{foreach from=$customData item=customValue key=customName}
 {$customName} : {$customValue}
{/foreach}
{/if}
