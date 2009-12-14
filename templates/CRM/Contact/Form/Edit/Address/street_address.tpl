{if $form.address.$blockId.street_address}
    <!--table id="streetAddress_{$blockId}" style="border:0;"-->
    <tr id="streetAddress_{$blockId}">
        <td>
           {$form.address.$blockId.street_address.label}<br />
           {$form.address.$blockId.street_address.html}<br />
           <span class="description font-italic">Street number, street name, apartment/unit/suite - OR P.O. box</span>
        </td>
        {if $parseStreetAddress eq 1 && $action eq 2}
           <td><br />
           <a href="#" title="{ts}Edit Address Elements{/ts}" onClick="showHideAddress( 'addressElements' , '{$blockId}' );return false;">{ts}Edit Address Elements{/ts}</a>
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
               <a href="#" title="{ts}Edit Street Address{/ts}" onClick="showHideAddress( 'streetAddress', '{$blockId}' );return false;">{ts}Edit Street Address{/ts}</a><br />
               </td>
           </tr>
        </table>
    {/if}
{/if}
{literal}
<script type="text/javascript">
function showHideAddress( id, blockId ) {
        if ( id == 'addressElements' ) {
             show( 'addressElements_'+blockId );
             hide( 'streetAddress_'+blockId );
        } else {
             show( 'streetAddress_'+blockId );
             hide( 'addressElements_'+blockId );
        }     
}
</script>
{/literal}