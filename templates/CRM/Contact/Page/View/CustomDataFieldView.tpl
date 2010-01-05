<tr class= "{if $cd_edit.collapse_display}hiddenElement{/if}">
{if $element.options_per_line != 0}
      <td class="label">{$element.field_title}</td>
      <td>
          {* sort by fails for option per line. Added a variable to iterate through the element array*}
          {foreach from=$element.field_value item=val}
              {$val}
          {/foreach}
      </td>
  {else}
      <td class="label">{$element.field_title}</td>
      {if $element.field_type == 'File'}
          {if $element.field_value.displayURL}
              <td><a href="javascript:imagePopUp('{$element.field_value.displayURL}')" ><img src="{$element.field_value.displayURL}" height = "100" width="100"></a></td>
          {else}
              <td class="html-adjust"><a href="{$element.field_value.fileURL}">{$element.field_value.fileName}</a></td>
          {/if}
      {else}
          <td class="html-adjust">{$element.field_value}</td>
      {/if}
{/if}
</tr>
