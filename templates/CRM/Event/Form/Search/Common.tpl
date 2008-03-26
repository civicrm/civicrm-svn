<tr>
 <td class="right">{$form.event_title.label}</td> 
    {if $event_title_value}
    <script type="text/javascript">
        dojo.addOnLoad( function( ) {ldelim}
        dijit.byId( 'event_title' ).setDisplayedValue( "{$event_title_value}")
        {rdelim} );
    </script>
    {/if}
    <td>
       <div dojoType="dojox.data.QueryReadStore" jsId="eventStore" url="{$dataURLEvent}" class="tundra">
        {$form.event_title.html}
        </div>
    </td>
    <td>{$form.event_type.label}</td>
    {if $event_type_value}
    <script type="text/javascript">
        dojo.addOnLoad( function( ) {ldelim}
        dijit.byId( 'event_type' ).setDisplayedValue( "{$event_type_value}")
        {rdelim} );
    </script>
    {/if}
    <td>
        <div dojoType="dojox.data.QueryReadStore" jsId="eventTypeStore" url="{$dataURLEventType}" align="left" class="tundra">
        {$form.event_type.html}
        </div>
    </td>
 </tr>     
 <tr> 
    <td class="label"> {$form.event_start_date_low.label} </td>
    <td>
       {$form.event_start_date_low.html}&nbsp;<br />
       {include file="CRM/common/calendar/desc.tpl" trigger=trigger_search_event_1}
       {include file="CRM/common/calendar/body.tpl" dateVar=event_start_date_low startDate=startYear endDate=endYear offset=5 trigger=trigger_search_event_1}
    </td>
    <td colspan="2"> 
       {$form.event_end_date_high.label} {$form.event_end_date_high.html}<br />
             &nbsp; &nbsp; {include file="CRM/common/calendar/desc.tpl" trigger=trigger_search_event_2}
       {include file="CRM/common/calendar/body.tpl" dateVar=event_end_date_high startDate=startYear endDate=endYear offset=5 trigger=trigger_search_event_2}
    </td> 
</tr>

 <tr>
    <td class="label"><label>{ts}Participant Status{/ts}</label></td> 
    <td>
        <div class="listing-box" style="width: auto; height: 120px">
            {foreach from=$form.participant_status_id item="participant_status_val"} 
            <div class="{cycle values="odd-row,even-row"}">
            {$participant_status_val.html}
            </div>
            {/foreach}
        </div>
    </td>
    <td><label>{ts}Participant Role{/ts}</label></td>
    <td>
        <div class="listing-box" style="width: auto; height: 120px">
            {foreach from=$form.participant_role_id item="participant_role_id_val"}
                <div class="{cycle values="odd-row,even-row"}">
                {$participant_role_id_val.html}
                </div>
            {/foreach}
        </div>
    </td>
  
 </tr> 
 <tr>
    <td colspan="2">&nbsp;</td>
    <td colspan="2">{$form.participant_test.html}&nbsp;{$form.participant_test.label}</td> 
 </tr>
 <tr>
    <td colspan="4">
       {include file="CRM/Custom/Form/Search.tpl" groupTree=$participantGroupTree showHideLinks=false}
    </td>
 </tr>
