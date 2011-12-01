{include file="CRM/common/TrackingFields.tpl"}

<div class="crm-section {$form.billing_contact_email.name}-section">	
<div class="label">{$form.billing_contact_email.label}</div>
<div class="content">{$form.billing_contact_email.html}</div>
<div class="clear"></div> 
</div>

<table>
  <thead>
    <tr>
      <th class="event-title">
	Event
      </th>
      <th class="participants-column">
	Participants
      </th>
      <th class="cost">
	Price
      </th>
      <th class="amount">
	Total
      </th>
    </tr>
  </thead>
  <tbody>
    {foreach from=$line_items item=line_item}
      <tr class="event-line-item {$line_item.class}">
	<td class="event-title">
	  {$line_item.event->title} ({$line_item.event->start_date})
	</td>
	<td class="participants-column">
	  {$line_item.num_participants}<br/>
	  {if $line_item.num_participants > 0}
	    <div class="participants" style="padding-left: 10px;">
	      {foreach from=$line_item.participants item=participant}
			{$participant.display_name}<br />
	      {/foreach}
	    </div>
	  {/if}
	  {if $line_item.num_waiting_participants > 0}
	    Waitlisted:<br/>
	    <div class="participants" style="padding-left: 10px;">
	      {foreach from=$line_item.waiting_participants item=participant}
			{$participant.display_name}<br />
	      {/foreach}
	    </div>
	  {/if}
	</td>
	<td class="cost">
	  {$line_item.cost|crmMoney:$currency|string_format:"%10s"}
	</td>
	<td class="amount">
	  &nbsp;{$line_item.amount|crmMoney:$currency|string_format:"%10s"}
	</td>
      </tr>
    {/foreach}
  </tbody>
  <tfoot>
  {if $discounts}
    <tr>
      <td>
      </td>
      <td>
      </td>
      <td>
	Subtotal:
      </td>
      <td>
	&nbsp;{$sub_total|crmMoney:$currency|string_format:"%10s"}
      </td>
    </tr>  
  {foreach from=$discounts key=myId item=i}
    <tr>
      <td>{$i.title}
      </td>
      <td>
      </td>
      <td>
      </td>
      <td>
   -{$i.amount|crmMoney:$currency|string_format:"%10s"}
      </td>
    </tr>
   {/foreach} 
   {/if} 
    <tr>
      <td>
      </td>
      <td>
      </td>
      <td class="total">
	Total:
      </td>
      <td class="total">
	&nbsp;{$total|crmMoney:$currency|string_format:"%10s"}
      </td>
    </tr>
  </tfoot>
</table>
{if $payment_required == true}
{include file='CRM/Core/BillingBlock.tpl'}
{/if}
<div id="crm-submit-buttons" class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{include file="CRM/Event/Cart/Form/viewCartLink.tpl"}
