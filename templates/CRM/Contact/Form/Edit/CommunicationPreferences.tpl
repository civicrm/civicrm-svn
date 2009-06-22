{* This file provides the plugin for the communication preferences in all the three types of contact *}
{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller *}

<h3 class="head"> 
    <span class="ui-icon ui-icon-triangle-1-e"></span><a href="#">{ts}{$title}{/ts}</a>
</h3>
<div id="commPrefs">
    <table class="form-layout-compressed" >
        <tr>
            <td><label for="greeting_type_id">Greeting</label><br />
            <select onchange=" showGreeting();" name="greeting_type_id" id="greeting_type_id" class="form-select">
            <option value="">- select -</option>
            <option value="1" selected="selected">Dear [first]</option>
            <option value="2">Dear [prefix] [first] [last]</option>
            <option value="3">Dear [prefix] [last]</option>
            <option value="4">Customized</option>
            </select>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>{$form.privacy.label}<br />
                {assign var="index" value="1"}
                {foreach key=key item=item from=$form.privacy}
                   {if $index < 10}
                     {assign var="index" value=`$index+1`}
                   {else}
                     {$form.privacy.$key.html}<br />
                   {/if}
                {/foreach}   
            </td>
            <td>{$form.preferred_communication_method.label} <img src="../../i/quiz.png" / alt="help"><br />
                {assign var="index" value="1"}
                {foreach key=key item=item from=$form.preferred_communication_method}
                   {if $index < 10}
                     {assign var="index" value=`$index+1`}
                   {else}
                     {$form.preferred_communication_method.$key.html}<br />
                   {/if}
                {/foreach}                
            </td>
        </tr>
        <tr>
            <td>{$form.is_opt_out.html} {$form.is_opt_out.label} {help id="id-optOut"}</td>
            <td>{$form.preferred_mail_format.label} &nbsp;
                {$form.preferred_mail_format.html} {help id="id-emailFormat"}
            </td>
        </tr>
    </table>
</div>
