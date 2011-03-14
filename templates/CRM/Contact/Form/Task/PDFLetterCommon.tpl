{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
{*common template for compose PDF letters*}
{if $form.template.html}
<table class="form-layout-compressed">
    <tr>
        <td class="label-left">{$form.template.label}</td>
	    <td>{$form.template.html}</td>
    </tr>
</table>
{/if}

<div class="crm-accordion-wrapper crm-html_email-accordion crm-accordion-open">
<div class="crm-accordion-header">
    {$form.html_message.label}
</div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
  {if $action neq 4}
  <span class="helpIcon" id="helphtml">
	<a href="#" onClick="return showToken('Html', 1);">{$form.token1.label}</a>
	{help id="id-token-html" file="CRM/Contact/Form/Task/Email.hlp"}
	<div id="tokenHtml" style="display:none;">
	    <input style="border:1px solid #999999;" type="text" id="filter1" size="20" name="filter1" onkeyup="filter(this, 1)"/><br />
	    <span class="description">{ts}Begin typing to filter list of tokens{/ts}</span><br/>
	    {$form.token1.html}
	</div>
  </span>
  {/if}
    <div class="clear"></div>
    <div class='html'>
	{if $editor EQ 'textarea'}
	    <div class="help description">{ts}NOTE: If you are composing HTML-formatted messages, you may want to enable a Rich Text (WYSIWYG) editor (Administer &raquo; Configure &raquo; Global Settings &raquo; Site Preferences).{/ts}</div>
	{/if}
	{$form.html_message.html}<br />
    </div>
  </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->


<div id="editMessageDetails">
    <div id="updateDetails" >
        {$form.updateTemplate.html}&nbsp;{$form.updateTemplate.label}
    </div>
    <div>
        {$form.saveTemplate.html}&nbsp;{$form.saveTemplate.label}
    </div>
</div>

<div id="saveDetails" class="section">
    <div class="label">{$form.saveTemplateName.label}</div>
    <div class="content">{$form.saveTemplateName.html|crmReplace:class:huge}</div>
</div>

{include file="CRM/Mailing/Form/InsertTokens.tpl"}

{literal}
<script type="text/javascript">

document.getElementById("editMessageDetails").style.display = "block";

function tokenReplHtml ( )
{
    var token1 = cj("#token1").val( )[0];
    var editor = {/literal}"{$editor}"{literal};
    if ( editor == "tinymce" ) {
        var content= tinyMCE.get('html_message').getContent() +token1;
        tinyMCE.get('html_message').setContent(content);
    } else if ( editor == "joomlaeditor" ) { 
        tinyMCE.execCommand('mceInsertContent',false, token1);
        var msg       = document.getElementById(html_message).value;
        var cursorlen = document.getElementById(html_message).selectionStart;
        var textlen   = msg.length;
        document.getElementById(html_message).value = msg.substring(0, cursorlen) + token1 + msg.substring(cursorlen, textlen);
        var cursorPos = (cursorlen + token1.length);
        document.getElementById(html_message).selectionStart = cursorPos;
        document.getElementById(html_message).selectionEnd   = cursorPos;
        document.getElementById(html_message).focus();
	} else if ( editor == "ckeditor" ) {
        oEditor = CKEDITOR.instances[html_message];
        oEditor.insertHtml(token1.toString() );        
    } else {
        //document.getElementById("html_message").value =  document.getElementById("html_message").value + token1;
		var msg       = document.getElementById(html_message).value;
        var cursorlen = document.getElementById(html_message).selectionStart;
        var textlen   = msg.length;
        document.getElementById(html_message).value = msg.substring(0, cursorlen) + token1 + msg.substring(cursorlen, textlen);
        var cursorPos = (cursorlen + token1.length);
        document.getElementById(html_message).selectionStart = cursorPos;
        document.getElementById(html_message).selectionEnd   = cursorPos;
        document.getElementById(html_message).focus();
    }
    verify();
}

cj(function() {
   cj().crmaccordions(); 
});
</script>
{/literal}

