

<h1 style="font-size: 1.8em;">{ts}Settings - Address Settings{/ts}</h1>
<table class="ui-widget">
	<tr><td>


		&nbsp;{$form.buttons.html}

		<fieldset><legend>{ts}Mailing Labels{/ts}</legend>
			{$form.mailing_format.label}
			{$form.mailing_format.html|crmReplace:class:huge}
			<span class="description">{ts}Address format for mailing labels. Use the {literal}{contact.state_province}{/literal} token for state/province abbreviation or {literal}{contact.state_province_name}{/literal} for full name.{/ts}{help id='label-tokens'}</span>
		</fieldset>

		<fieldset><legend>{ts}Address Display{/ts}</legend>
			{$form.address_format.label}
			{$form.address_format.html|crmReplace:class:huge}
			<span class="description">{ts}Format for displaying addresses in the Contact Summary and Event Information screens.<br />Use {literal}{contact.state_province}{/literal} for state/province abbreviation or {literal}{contact.state_province_name}{/literal} for state province name.{/ts}{help id='address-tokens'}</span>
		</fieldset>
		
		<fieldset><legend>{ts}Address Editing{/ts}</legend>
			{$form.address_options.label}
			<div class="checkboxgroup">{$form.address_options.html}</div>
			<span class="description">{ts}Select the fields to be included when editing a contact or event address.{/ts}</span>

			{$form.location_count.label}
			{$form.location_count.html}
			<span class="description">{ts}Enter the maximum number of different locations/addresses that can be entered for a contact.{/ts}</span>
		</fieldset>

		<fieldset><legend>{ts}Address Standardization{/ts}</legend>
			<span class="description">{ts 1=http://www.usps.com/webtools/address.htm}CiviCRM includes an optional plugin for interfacing the the United States Postal Services (USPS) Address Standardization web service. You must register to use the USPS service at <a href='%1'>%1</a>. If you are approved, they will provide you with a User ID and the URL for the service.{/ts}</span>

			{$form.address_standardization_provider.label}
			{$form.address_standardization_provider.html}<br />
			<span class="description">{ts}Address Standardization Provider. Currently, only 'USPS' is supported.{/ts}</span>

			{$form.address_standardization_userid.label}
			{$form.address_standardization_userid.html}<br />
			<span class="description">{ts}USPS-provided User ID.{/ts}</span>

			{$form.address_standardization_url.label}
			{$form.address_standardization_url.html}<br />
			<span class="description">{ts}USPS-provided web service URL.{/ts}</span>
		</fieldset>

		&nbsp;{$form.buttons.html}

	</td></tr>
</table>

<div class="spacer"></div>

