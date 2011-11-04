Dear {contact.display_name},
        This is being sent to you as a receipt of payment.

	Your order number is #{$trxn->trxn_id}. Please print this confirmation for your records. Information about event registration will be sent separately to each participant.
Here's a summary of your transaction placed on {$trxn->trxn_date|date_format:"%D %I:%M %p %Z"}:

{if $billing_name}
===========================================================
{ts}Billing Name and Address{/ts}

===========================================================
{$billing_name}

{$billing_street_address}

{$billing_city}, {$billing_state} {$billing_postal_code}

{$email}
{/if}

{if $source}
{$source}
{/if}


{foreach from=$line_items item=line_item}
{$line_item.event->title} ({$line_item.event->start_date|date_format:"%D"})
{if $isShowLocation}
  {if $location.address.1.name}
    {$location.address.1.name}
  {/if}
  {if $location.address.1.street_address}
    {$location.address.1.street_address}
  {/if}
  {if $location.address.1.supplemental_address_1}
    {$location.address.1.supplemental_address_1}
  {/if}
  {if $location.address.1.supplemental_address_2}
    {$location.address.1.supplemental_address_2}
  {/if}
  {if $location.address.1.city}
    {$location.address.1.city}, {$location.address.1.state_province} {$location.address.1.postal_code}
  {/if}
{/if}{*End of isShowLocation condition*}
{$line_item.event->start_date|date_format:"%D %I:%M %p"} - {$line_item.event->end_date|date_format:"%I:%M %p"}

  Quantity: {$line_item.num_participants}

{if $line_item.num_participants > 0}
  {foreach from=$line_item.participants item=participant}
    {$participant->first_name} {$participant->last_name}
  {/foreach}
{/if}
{if $line_item.num_waiting_participants > 0}
  Waitlisted:
    {foreach from=$line_item.waiting_participants item=participant}
      {$participant->first_name} {$participant->last_name}
    {/foreach}
{/if}
Cost: {$line_item.cost|crmMoney:$currency|string_format:"%10s"}
Total For This Event: {$line_item.amount|crmMoney:$currency|string_format:"%10s"}

{/foreach}

{if $discounts}
Subtotal: {$sub_total|crmMoney:$currency|string_format:"%10s"}
--------------------------------------
Discounts
{foreach from=$discounts key=myId item=i}
  {$i.title}: -{$i.amount|crmMoney:$currency|string_format:"%10s"}
{/foreach}
{/if}
======================================
Total: {$total|crmMoney:$currency|string_format:"%10s"}

{if $credit_card_type}
===========================================================
{ts}Payment Information{/ts}

===========================================================
{$credit_card_type}
{$credit_card_number}
{ts}Expires{/ts}: {$credit_card_exp_date|truncate:7:''|crmDate}
{/if}
