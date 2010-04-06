<tr>
  {if $form.activity_type_id}
     <td><label>{ts}Activity Type(s){/ts}</label>
        <div id="Tag" class="listing-box">
          {foreach from=$form.activity_type_id item="activity_type_val"} 
             <div class="{cycle values="odd-row,even-row"}">
               {$activity_type_val.html}
             </div>
          {/foreach}
        </div>
     </td>
  {else}
      <td>&nbsp;</td>
  {/if} 
  {if $form.activity_tags }
    <td><label>{ts}Activity Tag(s){/ts}</label>
      <div id ="Tags" class="listing-box">
         {foreach from=$form.activity_tags item="tag_val"} 
              <div class="{cycle values="odd-row,even-row"}">
                   {$tag_val.html}
              </div>
         {/foreach}
    </td>
  {else}
  	 <td>&nbsp;</td>
  {/if} 
</tr>
<tr>
   <td>
      {$form.activity_date_low.label}<br/>
	  {include file="CRM/common/jcalendar.tpl" elementName=activity_date_low} 
   </td>
   <td>
	  {$form.activity_date_high.label}<br/>
	  {include file="CRM/common/jcalendar.tpl" elementName=activity_date_high}
   </td>
</tr>
<tr>
   <td>
	  {$form.activity_role.label}<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('activity_role', '{$form.formName}'); return false;" >{ts}clear{/ts}</a>)</span><br />
      {$form.activity_role.html}
   </td>
   <td colspan="2"><br />
	  {$form.activity_target_name.html}<br />
      <span class="description font-italic">{ts}Complete OR partial Contact Name.{/ts}</span><br /><br />
	  {$form.activity_test.label} &nbsp; {$form.activity_test.html} 
   </td>
</tr>
<tr>
   <td>
      {$form.activity_subject.label}<br />
      {$form.activity_subject.html|crmReplace:class:big} 
   </td>
   <td colspan="2">
      {$form.activity_status.label}<br />
      {$form.activity_status.html} 
   </td>
</tr>
{if $activityGroupTree}
<tr id="activityCustom">
   <td id="activityCustomData" colspan="2">
	  {include file="CRM/Custom/Form/Search.tpl" groupTree=$activityGroupTree showHideLinks=false}
   </td>
</tr>
{/if}

{literal}
<script type="text/javascript">
    cj(document).ready(function() { 
        cj('#activityCustom').children().each( function() {
            cj( '#'+cj( this ).attr( 'id' )+' div' ).each( function() {
                if ( cj( this ).children().attr( 'id' ) ) {
                    cj( '#'+cj( this ).attr( 'id' ) ).hide();
                }
            });
        });
    });
</script>


<script type="text/javascript">
function showCustomData( chkbox ) 
{		 
    if ( document.getElementById( chkbox ).checked ) {
        var element = chkbox.split("[");
        var splitElement = element[1].split("]");    
        cj( '#activityCustom').children().each( function( ) {
            cj( '#'+cj( this ).attr( 'id' )+' div' ).each( function( ) {
                if ( cj( this ).children().attr( 'id' ) ) {
                    if ( cj( '#'+cj( this ).attr( 'id' )+( ' fieldset' )).attr( 'id' ) ) {
                        var fieldsetId = cj('#'+cj( this ).attr( 'id' )+( ' fieldset' )).attr( 'id' ).split( "" );
                        var activityTypeId = jQuery.inArray( splitElement[0], fieldsetId );                                     
                        if ( fieldsetId[activityTypeId] == splitElement[0] ) {
                            cj( this ).show();
                        }                            
                    } 
                }
            });
        });
    } else {
        var setcount = 0;
        var element = chkbox.split( "[" );
        var splitElement = element[1].split( "]" );
            cj( '#activityCustom').children().each( function( ) {
                cj( '#'+cj( this ).attr( 'id' )+' div' ).each(function() {
                    if ( cj( this ).children().attr( 'id' ) ) {
                        if ( cj( '#'+cj( this ).attr( 'id' )+( ' fieldset') ).attr( 'id' ) ) {
                            var fieldsetId = cj( '#'+cj( this ).attr( 'id' )+( ' fieldset' ) ).attr( 'id' ).split( "" );
                            var activityTypeId = jQuery.inArray( splitElement[0],fieldsetId );
                                if ( fieldsetId[activityTypeId] ==  splitElement[0] ) {
                                    cj( '#'+cj( this ).attr( 'id' ) ).each( function() {
                                        if ( cj( this ).children().attr( 'id' ) ) {
                                            cj( '#'+cj( this ).attr( 'id' )+( ' fieldset' ) ).each( function( ) {
                                                var splitFieldsetId = cj( this ).attr( 'id' ).split( "" );
                                                var splitFieldsetLength = splitFieldsetId.length;
                                                for( var i=0;i<splitFieldsetLength;i++ ) {
                                                    var setActivityTypeId = splitFieldsetId[i];
                                                        if ( parseInt( setActivityTypeId ) ) {
                                                            var activityTypeId = 'activity_type_id['+setActivityTypeId+']';
                                                            if ( document.getElementById( activityTypeId ).checked ) {
                                                                return false;
                                                            } else {
                                                                setcount++;
                                                            }
                                                        }                   
                                                }                                  
                                                if ( setcount > 0 ) {
                                                    cj( '#'+cj( this ).parent().attr( 'id' ) ).hide();
                                                }                                  
                                            });
                                        }
                                    });
                                }
                        } 
                    }
                });
            });
    } 
}
{/literal}	     
</script>