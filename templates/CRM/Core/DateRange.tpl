{*this is included inside the table*}
{assign var=relativeName   value=$fieldName|cat:"_relative"}
<td >{$form.$relativeName.html}</td>
<td>   
    <span id="absolute_{$relativeName}"> 
        {assign var=fromName   value=$fieldName|cat:"_from"}
        {$form.$fromName.label}
        {include file="CRM/common/jcalendar.tpl" elementName=$fromName} 
        {assign var=toName   value=$fieldName|cat:"_to"}
        {$form.$toName.label}
        {include file="CRM/common/jcalendar.tpl" elementName=$toName} 
    </span>   
            
</td>
{literal}
<script type="text/javascript">
    var val       = document.getElementById("{/literal}{$relativeName}{literal}").value;
    var fieldName = "{/literal}{$relativeName}{literal}";
    showAbsoluteRange( val, fieldName );

    function showAbsoluteRange( val, fieldName ) {
        if ( val == "0" ) {
            cj('#absolute_'+ fieldName).show();
        } else {
            cj('#absolute_'+ fieldName).hide();
        }
    }
</script>
{/literal}        
