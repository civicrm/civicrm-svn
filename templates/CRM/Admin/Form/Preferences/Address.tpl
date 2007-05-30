<div class="form-item">
<fieldset><legend>{ts}Mailing Labels{/ts}</legend>
      <table class="form-layout">
        <tr><td class="label">{$form.mailing_format.label}</td><td>{$form.mailing_format.html|crmReplace:class:huge}</td></tr>
        <tr><td>&nbsp;</td><td class="description">{ts}Address format for mailing labels.<br />Use the {literal}{state_province}{/literal} token for state/province abbreviation or {literal}{state_province_name}{/literal} for full name.{/ts}{help id='label-tokens'}</td></tr>
        <tr><td class="label">{$form.individual_name_format.label}</td><td>{$form.individual_name_format.html|crmReplace:class:huge}</td></tr>
        <tr><td>&nbsp;</td><td class="description">{ts}Formatting for individual contact names when {literal}{contact_name}{/literal} token is included in mailing labels.{/ts} {help id='name-tokens'}</td></tr>
    </table>
</fieldset>
<fieldset><legend>{ts}Address Display{/ts}</legend>
      <table class="form-layout">
        <tr><td class="label">{$form.address_format.label}</td><td>{$form.address_format.html|crmReplace:class:huge}</td></tr>
        <tr><td>&nbsp;</td><td class="description">{ts}Format for displaying addresses in the Contact Summary and Event Information screens.<br />Use {literal}{state_province}{/literal} for state/province abbreviation or {literal}{state_province_name}{/literal} for state province name.{/ts}{help id='address-tokens'}</td></tr>
      </table>
</fieldset>
<fieldset><legend>{ts}Address Editing{/ts}</legend>
      <table class="form-layout">
        <tr><td class="label">{$form.address_options.label}</td><td>{$form.address_options.html}</td></tr>
        <tr><td>&nbsp;</td><td class="description">{ts}Select the fields to be included when editing a contact or event address.{/ts}</td></tr>
        <tr><td class="label">{$form.location_count.label}</td><td>{$form.location_count.html|crmReplace:class:two}</td></tr>
        <tr><td>&nbsp;</td><td class="description">{ts}Enter the maximum number of different locations/addresses that can be entered for a contact.{/ts}</td></tr>
      </table>
</fieldset>
<fieldset><legend>{ts}Address Standardization{/ts}</legend>
    <div class="description">
        {ts}CiviCRM includes an optional plugin for interfacing the the United States Postal Services (USPS) Address Standardization web service. You must register to use the USPS service at <a href="http://www.usps.com/webtools/address.htm">http://www.usps.com/webtools/address.htm</a>. If you are approved, they will provide you with a User ID and the URL for the service.{/ts}
    </div>
      <table class="form-layout">
        <tr><td class="label">{$form.address_standardization_provider.label}</td><td>{$form.address_standardization_provider.html}</td></tr>    
        <tr><td>&nbsp;</td><td class="description">{ts}Address Standardization Provider. Currently, only 'USPS' is supported.{/ts}</td></tr>
        <tr><td class="label">{$form.address_standardization_userid.label}</td><td>{$form.address_standardization_userid.html}</td></tr>    
        <tr><td>&nbsp;</td><td class="description">{ts}USPS-provided User ID.{/ts}</td></tr>
        <tr><td class="label">{$form.address_standardization_url.label}</td><td>{$form.address_standardization_url.html}</td></tr>    
        <tr><td>&nbsp;</td><td class="description">{ts}USPS-provided web service URL.{/ts}</td></tr>
    </table>
</fieldset>
<table class="form-layout">
    <tr><td>&nbsp;</td><td>{$form.buttons.html}</td></tr>
</table>
<div class="spacer"></div>
</div>
