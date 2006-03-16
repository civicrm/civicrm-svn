{* Import Wizard - Step 4 (summary of import results AFTER actual data loading) *}
{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller *}

 {* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
 {include file="CRM/WizardHeader.tpl}
 
 <div id="help">
    <p>
    {ts}<strong>Import has completed successfully.</strong> The information below summarizes the results.{/ts}
    </p>
    
   {if $unMatchCount }
        <p class="error">
        {ts count=$unMatchCount plural='CiviCRM has detected mismatched contact IDs. These records have not been updated.'}CiviCRM has detected mismatched contact ID. This record have not been updated.{/ts}
        </p>
        <p class="error">
        {ts 1=$downloadMismatchRecordsUrl}You can <a href="%1">Download Mismatched Contacts</a>. You may then correct them, and import the new file with the corrected data.{/ts}
        </p>
    {/if} 
   
    {if $invalidRowCount }
        <p class="error">
        {ts count=$invalidRowCount plural='CiviCRM has detected invalid data and/or formatting errors in %count records. These records have not been imported.'}CiviCRM has detected invalid data and/or formatting errors in one record. This record have not been imported.{/ts}
        </p>
        <p class="error">
        {ts 1=$downloadErrorRecordsUrl}You can <a href="%1">Download Errors</a>. You may then correct them, and import the new file with the corrected data.{/ts}
        </p>
    {/if}

    {if $conflictRowCount}
        <p class="error">
        {ts count=$conflictRowCount plural='CiviCRM has detected %count records with conflicting email addresses within this data file or relative to existing contact records. These records have not been imported.'}CiviCRM has detected one record with conflicting email addresses within this data file or relative to existing contact records. This record have not been imported.{/ts} {ts}CiviCRM does not allow multiple contact records to have the same primary email address.{/ts}
        </p>
        <p class="error">
        {ts 1=$downloadConflictRecordsUrl}You can <a href="%1">Download Conflicts</a>. You may then review these records to determine if they are actually conflicts, and correct the email addresses for those that are not.{/ts}
        </p>
    {/if}

    {if $duplicateRowCount}
        <p {if $dupeError}class="error"{/if}>
        {ts count=$duplicateRowCount plural='CiviCRM has detected %count records which are duplicates of existing CiviCRM contact records.'}CiviCRM has detected one record which is a duplicate of existing CiviCRM contact record.{/ts} {$dupeActionString}
        </p>
        <p {if $dupeError}class="error"{/if}>
        {ts 1=$downloadDuplicateRecordsUrl}You can <a href="%1">Download Duplicates</a>. You may then review these records to determine if they are actually duplicates, and correct the email address for those that are not.{/ts}
        </p>
    {/if}
 </div>
    
 {* Summary of Import Results (record counts) *}
 <table id="summary-counts" class="report">
    <tr><td class="label">{ts}Total Rows{/ts}</td>
        <td class="data">{$totalRowCount}</td>
        <td class="explanation">{ts}Total rows (contact records) in uploaded file.{/ts}</td>
    </tr>

    {if $invalidRowCount }
    <tr class="error"><td class="label">{ts}Invalid Rows (skipped){/ts}</td>
        <td class="data">{$invalidRowCount}</td>
        <td class="explanation">{ts}Rows with invalid data in one or more fields (for example, invalid email address formatting). These rows will be skipped (not imported).{/ts}
            {if $invalidRowCount}
                <p><a href="{$downloadErrorRecordsUrl}">{ts}Download Errors{/ts}</a></p>
            {/if}
        </td>
    </tr>
    {/if}
    
    {if $unMatchCount }
    <tr class="error"><td class="label">{ts}Mismatched Rows (skipped){/ts}</td>
        <td class="data">{$unMatchCount}</td>
        <td class="explanation">{ts}Rows with mismatched contact IDs... (NOT updated).{/ts}
            {if $unMatchCount}
                <p><a href="{$downloadMismatchRecordsUrl}">{ts}Download Mismatched Contacts{/ts}</a></p>
            {/if}
        </td>
    </tr>
    {/if}
    
    {if $conflictRowCount}
    <tr class="error"><td class="label">{ts}Conflicting Rows (skipped){/ts}</td>
        <td class="data">{$conflictRowCount}</td>
        <td class="explanation">{ts}Rows with conflicting email addresses (NOT imported).{/ts}
            {if $conflictRowCount}
                <p><a href="{$downloadConflictRecordsUrl}">{ts}Download Conflicts{/ts}</a></p>
            {/if}
        </td>
    </tr>
    {/if}

    {if $duplicateRowCount && $dupeError}
    <tr class="error"><td class="label">{ts}Duplicate Rows{/ts}</td>
        <td class="data">{$duplicateRowCount}</td>
        <td class="explanation">{ts}Rows which are duplicates of existing CiviCRM contact records.{/ts} {$dupeActionString}
            {if $duplicateRowCount}
                <p><a href="{$downloadDuplicateRecordsUrl}">{ts}Download Duplicates{/ts}</a></p>
            {/if}
        </td>
    </tr>
    {/if}
    
    <tr><td class="label">{ts}Records Imported{/ts}</td>
        <td class="data">{$validRowCount}</td>
        <td class="explanation">{ts}Rows imported successfully.{/ts}</td>
    </tr>

    {if $groupAdditions}
    <tr><td class="label">{ts}Import to Groups{/ts}</td>
        <td colspan="2" class="explanation">
            {foreach from="$groupAdditions" item="group"}
                <label><a href="{$group.url}">{$group.name}</a></label>:
                {if $group.new}
                    {ts count=$group.added plural='%count contacts added to this new group.'}One contact added to this new group.{/ts}
                {else}
                    {ts count=$group.added plural='%count contacts added to this existing group.'}One contact added to this existing group.{/ts}
                {/if}
                {if $group.notAdded}{ts count=$group.notAdded plural='%count contacts NOT added (already in this group).'}One contact NOT added (already in this group).{/ts}{/if}<br>
            {/foreach}
        </td>
    </tr>
    {/if}

    {if $tagAdditions}
    <tr><td class="label">{ts}Tagged imported contacts{/ts}</td>
        <td colspan="2" class="explanation">
            {foreach from="$tagAdditions" item="tag"}
                <label>{$tag.name}</label>:
                {ts count=$tag.added plural='%count contacts are tagged with this tag.'}One contact is tagged with this tag.{/ts}
                {if $tag.notAdded}{ts count=$tag.notAdded plural='%count contacts NOT tagged (already tagged to this tag).'}One contact NOT tagged (already tagged to this tag).{/ts}{/if}<br />
            {/foreach}
        </td>
    </tr>
    {/if}

 </table>
 
 <div id="crm-submit-buttons">
    {$form.buttons.html}
 </div>
 
