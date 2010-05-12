{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
<div id="help">
{ts}It's a good idea to test your mailing by sending it to yourself and/or a selected group of people in your organization. You can also view your content by clicking (+) Preview Mailing.{/ts} {help id="test-intro"}
</div>
<div class="crm-form-block">
{include file="CRM/common/WizardHeader.tpl"}
{include file="CRM/Mailing/Form/Count.tpl"}


<div class="form-item">
<fieldset>
  <legend>Test Mailing</legend>
  <table class="form-layout">
    <tr><td class="label">{$form.test_email.label}</td><td>{$form.test_email.html} {ts}(filled with your contact's token values){/ts}</td></tr>
    <tr><td class="label">{$form.test_group.label}</td><td>{$form.test_group.html}</td></tr>
    <tr><td></td><td>{$form.sendtest.html}</td>  
  </table>
</fieldset>

<table>
    <tr><td></td><td>{include file="CRM/common/formButtons.tpl"}</td></tr>
</table>

<div class="section-hidden section-hidden-border" id="previewMailing_show">
  <a href="#" onclick="hide('previewMailing_show'); show('previewMailing'); document.getElementById('previewMailing').style.visibility = 'visible'; return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a><label>{ts}Preview Mailing{/ts}</label><br />
</div>

<div id="previewMailing" style="visibility: hidden;">
  <fieldset>
    <legend><a href="#" onclick="hide('previewMailing'); show('previewMailing_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{ts}Preview Mailing{/ts}</legend>
    <table class="form-layout">
      <tr><td class="label">{ts}Subject:{/ts}</td><td>{$subject}</td></tr>
{if $preview.attachment}
      <tr><td class="label">{ts}Attachment(s):{/ts}</td><td>{$preview.attachment}</td></tr>
{/if}
      {if $preview.text_link}
      <tr><td class="label">{ts}Text Version:{/ts}</td><td><iframe height="300" src="{$preview.text_link}" width="80%"><a href="{$preview.text_link}" onclick="window.open(this.href); return false;">{ts}Text Version{/ts}</a></iframe></td></tr>
      {/if}
      {if $preview.html_link}
      <tr><td class="label">{ts}HTML Version:{/ts}</td><td><iframe height="300" src="{$preview.html_link}" width="80%"><a href="{$preview.html_link}" onclick="window.open(this.href); return false;">{ts}HTML Version{/ts}</a></iframe></td></tr>
      {/if}
    </table>
  </fieldset>
  <table>
    <tr><td></td><td>{include file="CRM/common/formButtons.tpl"}</td></tr>
  </table>
</div>
    
</div>

{* include jscript to warn if unsaved form field changes *}
{include file="CRM/common/formNavigate.tpl"}
</div>

