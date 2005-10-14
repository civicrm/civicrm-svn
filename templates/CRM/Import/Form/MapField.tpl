{* Import Wizard - Step 2 (map incoming data fields) *}
{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller *}

 {* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
 {include file="CRM/WizardHeader.tpl}

 <div id="help">
    {ts}
    <p>Review the values shown below from the first 2 rows of your import file and select the matching CiviCRM database
    fields from the drop-down lists in the right-hand column. Select '-do not import-' for any columns in the import file
    that you want ignored.</p>
    {if $savedMapping}
    <p>Click 'Load Saved Field Mapping' if data has been previously imported from the same source. You can then
    select the saved import mapping setup and load it automatically.<p>
    {/if}
    <p>If you think you may be importing additional data from the same data source, check 'Save this field mapping'
    at the bottom of the page before continuing. The saved mapping can then be easily reused the next time data is
    imported.</p>
    {/ts}
</div>
 {* Table for mapping data to CRM fields *}
 {include file="CRM/Import/Form/MapTable.tpl}
 <br />

 <div id="crm-submit-buttons">
    {$form.buttons.html}
 </div>
 {$initHideBoxes}
