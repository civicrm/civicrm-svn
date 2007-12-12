{if $form.location.$index.address.country_id}

{literal}

<script type="text/javascript">

function getStateProvince{/literal}{$index}{literal}( obj, lno ) {

    // get the typed value
    var value = obj.getValue( );

    //load state province only if couuntry value exits
    if ( value ) {
       //get state province id
       var widget = dijit.byId('location_' + lno + '_address_state_province_id');

       //enable state province only if country value exists
       widget.setDisabled( false );
    
       //with (widget.downArrowNode.style) { width = "15px";	height = "15px";}

       //translate select
       var sel = {/literal}"{ts} - type first letter(s) - {/ts}"{literal}; 

       //set state province combo if it is not set
       if ( !widget.getValue( ) ) {
           widget.setValue( sel,'' );
       } 

       //data url for state
       var res = {/literal}"{$stateUrl}"{literal};

       var queryUrl = res + '&node=' + value;

       var queryStore = new dojox.data.QueryReadStore({url: queryUrl } );
       widget.store   = queryStore;
   } 
}

</script>
{/literal}

<div class="form-item">
    <span class="labels">
    {$form.location.$index.address.country_id.label}
    </span>
    <div class ="tundra" dojoType="dojox.data.QueryReadStore" jsId="country_idStore" url="{$countryUrl}">
    <span class="fields">
        {$form.location.$index.address.country_id.html}
        <br class="spacer"/>
        <span class="description font-italic">
            {ts}Type in the first few letters of the country and then select from the drop-down. After selecting a country, the State / Province field provides a choice of states or provinces in that country.{/ts}
        </span>
    </span>
</div>

{/if}


