{* common dupe contacts processing *}
<div id='processDupes' class="success-status" style="display:none;"></div>
{literal}
<script type='text/javascript'>

cj( '#processDupes' ).hide( );

function processDupes( cid, oid, oper ) {
        //currently we are doing in a single way.
        //later we might want two way operations.
   
        if ( !cid || !oid || !oper ) return;
        
	var title = {/literal}'{ts escape="js"}Marked as non duplicates.{/ts}'{literal};
	var msg = {/literal}'{ts escape="js"}Are you sure you want to save these contacts as non duplicates.{/ts}'{literal};
        if ( oper == 'nondupe-dupe' ) {
	  var title = {/literal}'{ts escape="js"}Marked as duplicates.{/ts}'{literal};
          var msg = {/literal}'{ts escape="js"}Are you sure you want to save these contacts as duplicates.{/ts}'{literal};
        }
    
	cj("#processDupes").show( );
	cj("#processDupes").dialog({
		title: title,
		modal: true,
		bgiframe: true,
		overlay: { 
			opacity: 0.5, 
			background: "black" 
		},

		open:function() {
		   cj( '#processDupes' ).show( ).html( msg );
		},
	
		buttons: { 
			"Cancel": function() { 
				cj(this).dialog("close"); 
			},
			"OK": function() { 	    
			        saveProcessDupes( cid, oid, oper );
			        cj(this).dialog( 'close' );			        
			}
		} 
	});
}


function saveProcessDupes( cid, oid, oper ) {
    //currently we are doing in a single way.
    //later we might want two way operations.
   
    if ( !cid || !oid || !oper ) return;
    
    var statusMsg = {/literal}'{ts escape="js"}Marked as non duplicates.{/ts}'{literal};	
    if ( oper == 'nondupe-dupe' ) {
       var statusMsg = {/literal}'{ts escape="js"}Marked as duplicates.{/ts}'{literal};
    }
    
    var url = {/literal}"{crmURL p='civicrm/ajax/rest' q='className=CRM_Contact_Page_AJAX&fnName=processDupes' h=0 }"{literal};	
    //post the data to process dupes.	
    cj.post( url, 
     	     {cid: cid, oid: oid, op: oper}, 
             function( result ) {
		 if ( result.status == oper ) {
                    if ( oper == 'dupe-nondupe' ) {
		       cj( "#dupeRow_" + cid + '_' + oid ).addClass( "disabled" );    
		    } else {
		       cj( "#dupeRow_" + cid + '_' + oid ).removeClass( "disabled" );
		    }
       	         }
	     },
	     'json' );
}
</script>
{/literal}
