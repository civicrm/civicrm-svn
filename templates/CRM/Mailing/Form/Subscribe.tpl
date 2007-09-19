{* this template is used for web-based subscriptions to mailing list type groups  *}
<div class="form-item">
<fieldset>
{if $single}
    <div id="help">
        {ts}Enter your email address and click <strong>Subscribe</strong>. You will receive a confirmation request via email shortly.
        Your subscription will be activated after you respond to that email.{/ts}
    </div>
{else}
    <div id="help">
        {ts}Enter your email address and check the box next to each mailing list you want to join. Then click the
        <strong>Subscribe</strong> button. You will receive a confirmation request via email for each selected list.
        Activate your subscription to each list by responding to the corresponding confirmation email.{/ts}
    </div>
{/if}

<dl>
    <dt>{$form.email.label}</dt><dd>{$form.email.html}</dd>
</dl>

{if ! $single} {* Show all public mailing list groups. Page was loaded w/o a specific group param (gid=N not in query string). *}
    <table summary="{ts}Group Listings.{/ts}" class="report">
    {counter start=0 skip=1 print=false}
    {foreach from=$rows item=row}
    <tr id='rowid{$row.id}' class="{cycle values="odd-row,even-row"}">
        {assign var=cbName value=$row.checkbox}
        <td style="border-right: 1px none gray;">{$form.$cbName.html} &nbsp; <strong>{$row.title}</strong></td>
        <td style="border-left: 1px none gray;">{$row.description}</td>
    </tr>
    {/foreach}  
    </table>
{/if}

<dl>
    <dt></dt><dd>{$form.buttons.html}</dd>
</dl>
</fieldset>
</div>
