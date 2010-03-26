<tr>
  {if $form.activity_type_id}
     <td colspan="1"><label>{ts}Activity Type(s){/ts}</label>
        <div id="Tag" class="listing-box">
          {foreach from=$form.activity_type_id item="activity_type_val"} 
             <div class="{cycle values="odd-row,even-row"}">
               {$activity_type_val.html}
             </div>
          {/foreach}
        </div>
     </td>
  {else}
      <td colspan="2">&nbsp;</td>
  {/if} 
  {if $form.activity_tags }
    <td width ="20%"> <label>{ts}Tag(s){/ts}</label>
      <div id ="Tags" class="listing-box">
         {foreach from=$form.activity_tags item="tag_val"} 
              <div class="{cycle values="odd-row,even-row"}">
                   {$tag_val.html}
              </div>
         {/foreach}
     </td>
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
	  {$form.activity_role.label}&nbsp;(<a href="#" title="unselect" onclick="unselectRadio('activity_role', 'Advanced'); return false;" >unselect</a>)<br />
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
<tr>
   <td colspan="2">
	  {include file="CRM/Custom/Form/Search.tpl" groupTree=$activityGroupTree showHideLinks=false}
   </td>
</tr>
{/if}