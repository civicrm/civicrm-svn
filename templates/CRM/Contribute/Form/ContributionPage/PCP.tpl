{include file="CRM/common/WizardHeader.tpl"}
<div id="pcp-form" class="form-item">
<fieldset>
{if !$profile}
	{capture assign=pUrl}{crmURL p='civicrm/admin/uf/group' q="reset=1"}{/capture}
	<div class="status message">
	{ts 1=$pUrl}No Profile with a user account registration option has been configured / enabled for your site. You need to <a href='%1'>configure a Supporter profile</a> first. It will be used to collect or update basic information from users while they are creating a Personal Campaign Page.{/ts}
	</div>
{/if}
<div id="help">
{ts}Allow constituents to create their own personal fundraising pages linked to this contribution page.{/ts}
</div>

<table class="form-layout">
	<tr>
	    <td class="label">&nbsp;</td>
	    <td>{$form.is_active.html} {$form.is_active.label}</td>
	</tr>
</table>

<div class="spacer"></div>

<div id="pcpFields">
<table class="form-layout">
    <tr>
	    <td class="label">{$form.is_approval_needed.label}</td>
	    <td>{$form.is_approval_needed.html} {help id="id-approval_needed"}</td>
   </tr>

    <tr>
	    <td class="label">{$form.notify_email.label}</td>
	    <td>{$form.notify_email.html} {help id="id-notify"}</td>
   </tr>
          
    <tr>
	    <td class="label">{$form.supporter_profile_id.label} <span class="marker"> *</span></td>
	    <td>{$form.supporter_profile_id.html} {help id="id-supporter_profile"}</td>
    </tr>

    <tr>
	    <td class="label">{$form.is_tellfriend_enabled.label}</td>
	    <td>{$form.is_tellfriend_enabled.html} {help id="id-is_tellfriend"}</td>
	</tr>

	<tr id="tflimit">
	    <td class="label">{$form.tellfriend_limit.label}</td>
	    <td>{$form.tellfriend_limit.html|crmReplace:class:four} {help id="id-tellfriend_limit"}</td>
	</tr>

	<tr>
	    <td class="label">{$form.link_text.label}</td>
	    <td>{$form.link_text.html|crmReplace:class:huge} {help id="id-link_text"}</td>
	</tr>
</table>
</div>
<div class="spacer"></div>
<div id="crm-submit-buttons">
<dl>
	<dt>&nbsp;</dt>
	<dd>{$form.buttons.html}</dd>
</dl>
</div>
</fieldset>
</div>
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    = "is_active"
    trigger_value       = "true"
    target_element_id   = "pcpFields" 
    target_element_type = "block"
    field_type          = "radio"
    invert              = "false"
}
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    = "is_tellfriend_enabled"
    trigger_value       = "true"
    target_element_id   = "tflimit" 
    target_element_type = "table-row"
    field_type          = "radio"
    invert              = "false"
}

{* include jscript to warn if unsaved form field changes *}
{include file="CRM/common/formNavigate.tpl"}
