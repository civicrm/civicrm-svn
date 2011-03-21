<span id='fileOnCaseStatusMsg' style="display:none;"></span>
<div class="crm-accordion-wrapper crm-search_filters-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{ts}Filter by Activity Type{/ts}</a>
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">

  <table class="no-border form-layout-compressed" id="searchOptions">
    <tr>
        <td class="crm-contact-form-block-activity_type_filter_id">
            {$form.activity_type_filter_id.html}
        </td>
        <!--td style="vertical-align: bottom;">
		<span class="crm-button"><input class="form-submit default" name="_qf_Basic_refresh" value="Search" type="button" onclick="buildContactActivities( true )"; /></span>
	</td-->
    </tr>
  </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

<table id="contact-activity-selector">
    <thead>
        <tr>
            <th class='crm-contact-activity-activity_type'>{ts}Type{/ts}</th>
            <th class='crm-contact-activity_subject'>{ts}Subject{/ts}</th>
            <th class='crm-contact-activity-source_contact'>{ts}Added By{/ts}</th>
            <th class='crm-contact-activity-target_contact nosort'>{ts}With{/ts}</th>
            <th class='crm-contact-activity-assignee_contact nosort'>{ts}Assigneed{/ts}</th>
            <th class='crm-contact-activity-activity_date'>{ts}Date{/ts}</th>
            <th class='crm-contact-activity-activity_status'>{ts}Status{/ts}</th>
            <th class='crm-contact-activity-links nosort'>&nbsp;</th>
            <th class='hiddenElement'>&nbsp;</th>
        </tr>
    </thead>
</table>
{include file="CRM/Case/Form/ActivityToCase.tpl" contactID=$contactId}
{literal}
<script type="text/javascript">
var oTable;

cj( function ( ) {
   cj().crmaccordions(); 
   buildContactActivities( false );
   cj('#activity_type_filter_id').change( function( ) {
       buildContactActivities( true );
   });
});

function buildContactActivities( filterSearch ) {
    if ( filterSearch ) {
        oTable.fnDestroy();
    }
    
    var columns = '';
    var sourceUrl = {/literal}'{crmURL p="civicrm/ajax/contactactivity" h=0 q="snippet=4&context=$context&cid=$contactId"}'{literal};

    cj('#contact-activity-selector th').each( function( ) {
        if ( !cj(this).hasClass('nosort') ) {
            columns += '{"sClass": "' + cj(this).attr('class') +'"},';
        } else {
            columns += '{ "bSortable": false },';
        }
    });
    
    var ZeroRecordText = {/literal}{ts}'No matches found'{/ts}{literal};
    if ( cj("select#activity_type_filter_id").val( ) ) {
      ZeroRecordText += {/literal}{ts}' for Activity Type = "'{/ts}{literal} +  cj("select#activity_type_filter_id :selected").text( ) + '"';
    } else {
      ZeroRecordText += '.';
    }

    columns    = columns.substring(0, columns.length - 1 );
    eval('columns =[' + columns + ']');
    oTable = cj('#contact-activity-selector').dataTable({
        "bFilter"    : false,
        "bAutoWidth" : false,
        "aaSorting"  : [],
        "aoColumns"  : columns,
        "bProcessing": true,
        "sPaginationType": "full_numbers",
        "sDom"       : '<"crm-datatable-pager-top"lfp>rt<"crm-datatable-pager-bottom"ip>',	
        "bServerSide": true,
        "sAjaxSource": sourceUrl,
        "iDisplayLength": 50,
	"oLanguage": { "sZeroRecords":  ZeroRecordText },
        "fnDrawCallback": function() { setSelectorClass(); },
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            aoData.push( {name:'contact_id', value: {/literal}{$contactId}{literal}},
                         {name:'admin',   value: {/literal}'{$admin}'{literal}}
            );
            if ( filterSearch ) {
                aoData.push(	     
                    {name:'activity_type_id', value: cj("select#activity_type_filter_id").val()}
                );                
            }	
            cj.ajax( {
                "dataType": 'json', 
                "type": "POST", 
                "url": sSource, 
                "data": aoData, 
                "success": fnCallback
            } ); 
        }
    });
}

function setSelectorClass( ) {
    cj("#contact-activity-selector td:last-child").each( function( ) {
       cj(this).parent().addClass(cj(this).text() );
    });
}
</script>
{/literal}
