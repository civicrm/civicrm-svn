{* Template for "Change Case Type" activities *}
    <tr><td class="label">{$form.case_type_id.label}</td><td>{$form.case_type_id.html}</td></tr>        
    <tr><td class="label">{$form.is_reset_timeline.label}</td><td>{$form.is_reset_timeline.html}</td></tr>  
    <tr id="resetTimeline">
        <td class="label">{$form.reset_date_time.label}</td>
        <td>{include file="CRM/common/jcalendar.tpl" elementName=reset_date_time}</td>
    </tr>

{include file="CRM/common/showHideByFieldValue.tpl" 
trigger_field_id    ="is_reset_timeline"
trigger_value       = true
target_element_id   ="resetTimeline" 
target_element_type ="table-row"
field_type          ="radio"
invert              = 0
}
