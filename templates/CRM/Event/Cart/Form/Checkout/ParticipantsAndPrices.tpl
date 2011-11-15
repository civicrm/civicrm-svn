{include file="CRM/common/TrackingFields.tpl"}

{capture assign='reqMark'}<span class="marker"  title="{ts}This field is required.{/ts}">*</span>{/capture}

{foreach from=$events_in_carts key=index item=event_in_cart}
 {if !$event_in_cart.main_conference_event_id}
  {assign var=event_id value=$event_in_cart->event_id}
  <h3 class="event-title">
    {$event_in_cart->event->title} ({$event_in_cart->event->start_date|date_format:"%m/%d/%Y %l:%M%p"})
  </h3>
  <fieldset class="event_form">
    <div class="participants crm-section" id="event_{$event_in_cart->event_id}_participants">
      {foreach from=$event_in_cart->participants item=participant}
	{include file="CRM/Event/Cart/Form/Checkout/Participant.tpl"}
      {/foreach}
      <a class="link-add" href="#" onclick="add_participant({$event_in_cart->event_cart->id}, {$event_in_cart->event_id}); return false;">Add Another Participant</a>
    </div>
    {if $event_in_cart->event->is_monetary }
      <div class="price_choices crm-section">
	{foreach from=$price_fields_for_event.$event_id key=price_index item=price_field_name}
	  <div class="label">
	    {$form.$price_field_name.label}
	  </div>
	  <div class="content">
	    {$form.$price_field_name.html|replace:'/label>':'/label><br>'}
	  </div>
	{/foreach}
      </div>
    {else}
      <p>There is no charge for this event.</p>
    {/if}
  </fieldset>
 {/if}
{/foreach}

<!--<div id="discount-entry" class="discount-entry">
  <p>{$form.discountcode.label}: {$form.discountcode.html}</p>
</div>-->

<div id="crm-submit-buttons" class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
<script type="text/javascript">
//<![CDATA[
function add_participant( cart_id, event_id ) {
  var max_index = 0;
  var matcher = new RegExp("event_" + event_id + "_participant_(\\d+)");
  
  cj('#event_' + event_id + '_participants .participant').each(
    function(index) {
      matches = matcher.exec(cj(this).attr('id'));
      index = parseInt(matches[1]);
      if (index > max_index)
      {
        max_index = index;
      }
    }
  );

  cj.get("/civicrm/ajax/event/add_participant_to_cart?&cart_id=" + cart_id + "&event_id=" + event_id, 
    function(data) {
      cj('#event_' + event_id + '_participants').append(data);
    }
  );
}

function delete_participant( event_id, participant_id )
{
  cj('#event_' + event_id + '_participant_' + participant_id).remove();
  cj.get("/civicrm/ajax/event/remove_participant_from_cart?&id=" + participant_id);
}


//XXX missing
cj('#ajax_error').ajaxError(
  function( e, xrh, settings, exception ) {
    cj(this).append('<div class="error">Error adding a participant at ' + settings.url + ': ' + exception);
  }
);
//]]>
</script>
{/literal}
