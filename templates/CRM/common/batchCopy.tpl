{literal}
<script type="text/javascript">
function setStatusesTo(statusId)
{
    var cId = new Array();
    var i = 0;
    {/literal}
    {foreach from=$componentIds item=field}
    {literal}cId[i++]{/literal} = {$field}
    {/foreach}
    {literal}
    for (k = 0; k < cId.length; k++) {
        document.getElementById("field_"+cId[k]+"_participant_status_id").value = statusId;
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
            var multiSelectList = document.getElementById(source).options;
            for ( k=0; k<cId.length; k++ ) {
                for ( i=0; i<multiSelectList.length; i++ ){
                    if ( multiSelectList[i].selected == true ){
                        document.getElementById( "field_"+cId[k]+"_"+fieldName ).options[i].selected = true ;
                    } else {
                        document.getElementById( "field_"+cId[k]+"_"+fieldName ).options[i].selected = false ;
                    }
                }
            }
        } else if ( document.getElementById(source).getAttribute("class") == "tinymce" ) {
            if ( editor == "tinymce" ) {
                for ( k=0; k<cId.length; k++ ) {
                    cj( '#field_' + cId[k] + '_' + fieldName ).html( cj('#'+ source).html( ) );
                }
            }
        } else {
            for ( k=0; k<cId.length; k++ ) {
                document.getElementById("field_"+cId[k]+"_"+fieldName).value = document.getElementById(source).value;
            }
        }
    } else if ( document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]") &&
            document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]").length > 0 ) {
        if ( document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]")[0].type == "radio" ) {
            for ( t=0; t<document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]").length; t++ ) {
                if  (document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]")[t].checked == true ) {break}
            }
            if ( t == document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]").length ) {
                for ( k=0; k<cId.length; k++ ) {
                    for ( t=0; t<document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]").length; t++ ) {
                        document.getElementsByName("field"+"["+cId[k]+"]"+"["+fieldName+"]")[t].checked = false;
                    }
                }
            } else {
                for ( k=0; k<cId.length; k++ ) {
                    document.getElementsByName("field"+"["+cId[k]+"]"+"["+fieldName+"]")[t].checked = document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]")[t].checked;
                }
            }
        } else if ( document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]")[0].type == "checkbox" ) {
            for ( k=0; k<cId.length; k++ ) {
                document.getElementsByName("field"+"["+cId[k]+"]"+"["+fieldName+"]")[0].checked = document.getElementsByName("field"+"["+cId[0]+"]"+"["+fieldName+"]")[0].checked;
            }
        } else if ( document.getElementById( "field"+"["+cId[0]+"]"+"["+fieldName+"]___Frame" ) ) {
            //currently FckEditor field is mapped accross ___frames. It can also be mapped accross ___config.
            if ( editor == "fckeditor" ) {
                fckEditor = FCKeditorAPI.GetInstance( "field"+"["+cId[0]+"]"+"["+fieldName+"]" );
                for ( k=0; k<cId.length; k++ ) {
                    FCKeditorAPI.GetInstance( "field"+"["+cId[k]+"]"+"["+fieldName+"]" ).SetHTML( fckEditor.GetHTML() );
                }
            }
        }
    } else {
        if ( f = document.getElementById('Batch') ) {
            if ( ts = f.getElementsByTagName('table') ) {
                if ( t = ts[0] ) {
                    tRows = t.getElementsByTagName('tr') ;
                    if ( tRows[1] ) {
                        secondRow = tRows[1] ;
                        inputs = secondRow.getElementsByTagName('input') ;
                        for ( ii = 0 ; ii<inputs.length ; ii++ ) {
                            pattern = 'field['+cId[0]+']['+fieldName+']';
                            if ( inputs[ii].name.search(pattern) && inputs[ii].type == 'checkbox' ) {
                                for ( k=1; k<cId.length; k++ ) {
                                    target = document.getElementsByName(inputs[ii].name.replace('field['+cId[0]+']', 'field['+cId[k]+']')) ;
                                    if ( target.length > 0 ) {
                                        target[1].checked = inputs[ii].checked ;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}


function copyValuesDate(fieldName)
{
    var cId = new Array();
    var i = 0;
    {/literal}
    {foreach from=$componentIds item=field}
        {literal}cId[i++]{/literal} = {$field}
    {/foreach}
    {literal}

    for ( k=0; k<cId.length; k++ ) {
        document.getElementById("field["+cId[k]+"]["+fieldName+"][Y]").value = document.getElementById("field["+cId[0]+"]["+fieldName+"][Y]").value;
        document.getElementById("field["+cId[k]+"]["+fieldName+"][M]").value = document.getElementById("field["+cId[0]+"]["+fieldName+"][M]").value;
        document.getElementById("field["+cId[k]+"]["+fieldName+"][d]").value = document.getElementById("field["+cId[0]+"]["+fieldName+"][d]").value;
    }
}

</script>
{/literal}
