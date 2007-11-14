{if ! empty( $fields )}                                           
    {strip} 
   {if $help_pre && $action neq 4}<div class="messages help">{$help_pre}</div>{/if} 
    {assign var=zeroField value="Initial Non Existent Fieldset"} 
    {assign var=fieldset  value=$zeroField} 
    {foreach from=$fields item=field key=name} 
    {if $field.groupTitle != $fieldset} 
        {if $fieldset != $zeroField} 
           </table> 
           {if $groupHelpPost && $action neq 4} 
              <div class="messages help">{$groupHelpPost}</div> 
           {/if} 
           {if $mode ne 8} 
              </fieldset> 
           {/if} 
        {/if} 

        {if $mode ne 8 && $action ne 1028 && $action ne 4} 
            <fieldset><legend>{$field.groupTitle}</legend> 
        {/if} 
        {assign var=fieldset  value=`$field.groupTitle`} 
        {assign var=groupHelpPost  value=`$field.groupHelpPost`} 
        {if $field.groupHelpPre && $action neq 4 && $action neq 1028} 
            <div class="messages help">{$field.groupHelpPre}</div> 
        {/if} 
        <table class="form-layout-compressed"> 
    {/if} 
     
    {assign var=n value=$field.name} 

    {if $field.options_per_line != 0} 
        <tr> 
        <td class="option-label">{$form.$n.label}</td> 
        <td> 
             {assign var="count" value="1"} 
        {strip} 
        <table class="form-layout-compressed"> 
        <tr> 
          {* sort by fails for option per line. Added a variable to iterate through the element array*} 
          {assign var="index" value="1"} 
          {foreach name=outer key=key item=item from=$form.$n} 
          {if $index < 10} 
              {assign var="index" value=`$index+1`} 
          {else} 
              <td class="labels font-light">{$form.$n.$key.html}</td> 
              {if $count == $field.options_per_line} 
                  </tr> 
                   <tr> 
                   {assign var="count" value="1"} 
              {else} 
                       {assign var="count" value=`$count+1`} 
              {/if} 
          {/if} 
          {/foreach} 
        </tr> 
        </table> 
        {/strip} 
        </td> 
    </tr> 
        {else} 
        <tr>
           <td class="label">{$form.$n.label}</td>
	   <td>
             {if $n|substr:0:3 eq 'im-'}
               {assign var="provider" value=$n|cat:"-provider_id"}
               {$form.$provider.html}&nbsp;
             {/if}
             {$form.$n.html}
           </td>
        </tr> 
        {/if} 
        {* Show explanatory text for field if not in 'view' or 'preview' modes *} 
        {if $field.help_post && $action neq 4 && $action neq 1028}
            <tr><td>&nbsp;</td><td class="description">{$field.help_post}</td></tr> 
        {/if} 
    {/foreach} 
   </table> 
{if $field.groupHelpPost && $action neq 4  && $action neq 1028} 
    <div class="messages help">{$field.groupHelpPost}</div> 
{/if} 
{if $mode eq 4} 
<div class="crm-submit-buttons">  
     {$form.buttons.html} 
</div> 
{/if} 
  {if $mode ne 8 && $action neq 1028} 
    </fieldset> 
  {/if} 
     
        {if $help_post && $action neq 4}<br /><div class="messages help">{$help_post}</div>
        {/if} 
    {/strip} 
 
{/if} {* fields array is not empty *} 

