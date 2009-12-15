{if $form.address.$blockId.street_address}
    <tr id="streetAddress_{$blockId}">
        <td>
           {$form.address.$blockId.street_address.label}<br />
           {$form.address.$blockId.street_address.html}<br />
           <span class="description font-italic">Street number, street name, apartment/unit/suite - OR P.O. box</span>
        </td>
        {if $parseStreetAddress eq 1 && $action eq 2}
           <td><br />
           <a href="#" title="{ts}Edit Address Elements{/ts}" onClick="processAddressFields( 'addressElements' , '{$blockId}' );return false;">{ts}Edit Address Elements{/ts}</a>
           </td> 
        {/if}   
    </tr>
        
    {if $parseStreetAddress eq 1 && $action eq 2}
        <table id="addressElements_{$blockId}" class=hiddenElement style="border:0;">
           <tr>
               <td>
                  {$form.address.$blockId.street_number.label}<br />
                  {$form.address.$blockId.street_number.html}<br />
                  <span class="description font-italic">Street number and prefix</span>
               </td>
           </tr>
           <tr>
               <td>
                  {$form.address.$blockId.street_name.label}<br />
                  {$form.address.$blockId.street_name.html}<br />
                  <span class="description font-italic">Street name</span>
               </td>
           </tr>
           <tr>
               <td>
                  {$form.address.$blockId.street_unit.label}<br />       
                  {$form.address.$blockId.street_unit.html}<br />
                  <span class="description font-italic">Apartment/Unit/Suite</span> 
               </td>
               <td>
               <a href="#" title="{ts}Edit Street Address{/ts}" onClick="processAddressFields( 'streetAddress', '{$blockId}' );return false;">{ts}Edit Street Address{/ts}</a><br />
               </td>
           </tr>
        </table>
    {/if}

{literal}

<script type="text/javascript">
function processAddressFields( name, blockId, onlyShowHide ) {
	if ( !onlyShowHide ) { 
	    var allAddressValues = {/literal}{$allAddressFieldValues}{literal}; 
        
	    var streetName    = eval( "allAddressValues.street_name_"    + blockId );
	    var streetUnit    = eval( "allAddressValues.street_unit_"    + blockId );
	    var streetNumber  = eval( "allAddressValues.street_number_"  + blockId );
	    var streetAddress = eval( "allAddressValues.street_address_" + blockId );
	}

	var showBlockName = '';
	var hideBlockName = '';

        if ( name == 'addressElements' ) {
             if ( !onlyShowHide ) {
	          streetAddress = '';
	     }
	     
             showBlockName = 'addressElements_' + blockId;		   
	     hideBlockName = 'streetAddress_' + blockId;
	} else {
             if ( !onlyShowHide ) {
                  streetNumber = streetName = streetUnit = ''; 
             }

             showBlockName = 'streetAddress_' +  blockId;
             hideBlockName = 'addressElements_'+ blockId;
       }

       show( showBlockName );
       hide( hideBlockName );

       // set the values.
       if ( !onlyShowHide ) {
          cj( '#address_' + blockId +'_street_name'    ).val( streetName    );   
          cj( '#address_' + blockId +'_street_unit'    ).val( streetUnit    );
          cj( '#address_' + blockId +'_street_number'  ).val( streetNumber  );
          cj( '#address_' + blockId +'_street_address' ).val( streetAddress );
       }
}

</script>
{/literal}
{/if}