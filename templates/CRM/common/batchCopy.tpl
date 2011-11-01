{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{literal}
<script type="text/javascript">
cj( function() {
    //bind the click event for action icon
    cj('.action-icon').click( function( ) {
        copyFieldValues( cj(this).attr('fname') );
    });
});

/**
 * This function use to copy fieldsi
 *
 * @param fname string field name
 * @return void
 */
function copyFieldValues( fname ) {
    // this is the most common pattern for elements, so first check if it exits
    // this check field starting with "field[" and contains [fname] and is not
    // hidden ( for checkbox hidden element is created )
    var elementId    = cj('#Batch [name^="field["][name*="[' + fname +']"][type!=hidden]');
    
    // get the first element and it's value
    var firstElement = elementId.eq(0);
    var firstElementValue = firstElement.val();
    
    //console.log( elementId );
    //console.log( firstElement );
    //console.log( firstElementValue );
    
    //check if it is date element
    var isDateElement     = elementId.attr('format');

    //get the element type
    var elementType       = elementId.attr('type'); 
    
    // set the value for all the elements, elements needs to be handled are
    // select, checkbox, radio, date fields, text, textarea, multu-select
    // advanced multi-select, wysiwyg editor
    if ( elementType == 'radio' ) {
        firstElementValue = elementId.filter(':checked').eq(0).val();
        elementId.filter("[value=" + firstElementValue + "]").prop("checked",true);
    } else if ( elementType == 'checkbox' ) {
        // handle checkbox
        // get the contact id of first element
        var firstContactId = firstElement.parent().parent().attr('contact_id');
       
        // lets uncheck all the checkbox except first one
        cj('#Batch [type=checkbox]:not([name^="field['+ firstContactId +']['+ fname +']["])').removeProp('checked');
        
        //here for each checkbox for first row, check if it is checked and set remaining checkboxes
        cj('#Batch [type=checkbox][name^="field['+ firstContactId +']['+ fname +']"][type!=hidden]').each(function() {
            if (cj(this).prop('checked') ) {
                var elementName = cj(this).attr('name');
                var correctIndex = elementName.split('field['+ firstContactId +']['+ fname +'][');
                correctIndexValue = correctIndex[1].replace(']', '');
                cj('#Batch [type=checkbox][name^="field["][name*="['+ fname +']['+ correctIndexValue+']"][type!=hidden]').prop('checked',true);
            }
        });
    } else {
        elementId.val( firstElementValue );
    }

    // since we use different display field for date we also need to set it.
    // also check for date time field and set the value correctly
    if ( isDateElement ) {
        copyValuesDate( fname );
    }
}

/**
 * Special function to handle setting values for date fields
 *
 * @param fname string field name
 * @return void
 */
function copyValuesDate(fname) {
    var fnameDisplay = fname + '_display';
    var fnameTime    = fname + '_time';
    
    var displayElement = cj('[name^="field_"][name$="_' + fnameDisplay +'"]');
    var timeElement    = cj('[name^="field_"][name$="_' + fnameTime +'"]');
  
    displayElement.val( displayElement.eq(0).val() );
    timeElement.val( timeElement.eq(0).val() );
}

function setStatusesTo( statusId ) {
    var cId = new Array();
    var i = 0;
    {/literal}
    {foreach from=$componentIds item=field}
    {literal}cId[i++]{/literal} = {$field}
    {/foreach}
    {literal}
    for (k = 0; k < cId.length; k++) {
        document.getElementById("field_"+cId[k]+"_participant_status").value = statusId;
    }
}

function copyValues(fieldName, source)
{
    var cId = new Array();
    var i = 0;
    var editor = {/literal}"{$editor}"{literal};
    {/literal}
    {foreach from=$componentIds item=field}
        {literal}cId[i++]{/literal} = {$field}
    {/foreach}
    {literal}

    if (source === undefined) source = "field_"+cId[0]+"_"+fieldName;

    if ( document.getElementById(source) ) {
        if ( document.getElementById(source).type == 'select-multiple' ) {
        } else if ( document.getElementById(source).getAttribute("class") == "tinymce" ) {
            if ( editor == "tinymce" ) {
                for ( k=0; k<cId.length; k++ ) {
                    cj( '#field_' + cId[k] + '_' + fieldName ).html( cj('#'+ source).html( ) );
                }
            }
        } else {
	    var copyHidden = false;
            if ( document.getElementById(source).type == 'text' &&
	         cj('#'+ source +'_id').length > 0 &&
		 cj('#field_'+ cId[1] + '_' + fieldName + '_id').length > 0 ) {
		 copyHidden = true;
            }
            for ( k=0; k<cId.length; k++ ) {
                document.getElementById("field_"+cId[k]+"_"+fieldName).value = document.getElementById(source).value;
		if ( copyHidden ) {
		  document.getElementById("field_"+cId[k]+"_"+fieldName+'_id').value = document.getElementById(source+'_id').value;
		}
            }
        }
    }
}


</script>
{/literal}
