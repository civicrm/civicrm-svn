 <tr>
     <td class="font-size12pt" colspan="2">{$form.pb_pledge_name.label}&nbsp;&nbsp;
    {if $pledge_name_value}
    <script type="text/javascript">
	dojo.addOnLoad( function( ) {ldelim}
        dijit.byId( 'pb_pledge_name' ).setValue( "{$pb_pledge_name_value}")
        {rdelim} );
    </script>
    {/if}
 <div dojoType="dojox.data.QueryReadStore" jsId="pledgeNameStore" url="{$dataURLPledgeName}" class="tundra">
{$form.pb_pledge_name.html}
     </td>       
 </tr>
 <tr> 
     <td>{$form.pb_pledge_is_active.html}&nbsp;{$form.pb_pledge_is_active.label}</td> 
     <td>{$form.pb_signer_is_done.html}&nbsp;{$form.pb_signer_is_done.label}</td> 
 </tr>
   