{* this template is used for adding/editing/deleting Message Templates *}
<div id="help>
    {ts 1="http://wiki.civicrm.org/confluence//x/gCY"}Use this form to add or edit re-usable message templates. Once you save a message template, you can use it when sending mail to
    one or more contacts. If you are using the CiviMember component, you can also use a message template to send Membership Renewal Reminders.
    You may include tokens to represent fields (like a contact's "first name") in the message subject and body. These will be replaced with the actual value of the corresponding
    field in the outgoing message (EXAMPLE: Dear {contact.first_name}). <a href="%1">More info...</a>{/ts}
</div>
<div class="form-item" id=membership_status>
<fieldset><legend>{if $action eq 1}{ts}New Message Template{/ts}{elseif $action eq 2}{ts}Edit Message Template{/ts}{else}{ts}Delete Message Template{/ts}{/if}</legend>
  
   {if $action eq 8}
      <div class="messages status">
        <dl>
          <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>
          <dd>    
          {ts}Do you want to delete this message template ?{/ts}
          </dd>
       </dl>
      </div>
   {else}
        <dl>
        <dt>{$form.msg_title.label}</dt><dd class="html-adjust">{$form.msg_title.html}</dd>
        <dt>&nbsp;</dt><dd class="description html-adjust">{ts}Descriptive title of message - used for template selection.{/ts}</dd>
        <dt>{$form.msg_subject.label}</dt><dd class="html-adjust">{$form.msg_subject.html}</dd>
        <dt>&nbsp;</dt><dd class="description html-adjust">{ts}Subject for email message. Tokens may be included.{/ts}</dd>
        <dt>{$form.msg_text.label}</dt><dd class="html-adjust">{$form.msg_text.html}</dd>
        <dt>&nbsp;</dt><dd class="description html-adjust">{ts}Text formatted message. Tokens may be included.{/ts}
        <dt>{$form.msg_html.label}</dt><dd class="html-adjust">{$form.msg_html.html}</dd>
        <dt>&nbsp;</dt><dd class="description html-adjust">{ts}HTML formatted message. Tokens may be included.{/ts}</dd>
        <dt>{$form.is_active.label}</dt><dd class="html-adjust">{$form.is_active.html}</dd>
        <dt>&nbsp;</dt><dd class="description html-adjust">{ts}Is this template enabled.{/ts}</dd>
        </dl> 
  {/if}
  <dl>   
      <dt></dt><dd class="html-adjust">{$form.buttons.html}</dd>
  </dl>
  <br clear="all" />
</fieldset>
</div>
