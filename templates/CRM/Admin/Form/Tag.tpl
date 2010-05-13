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
{* this template is used for adding/editing a tag (admin)  *}
<div class="form-item">
<h3>{if $action eq 1}{ts}New Tag {if $isTagSet}Set{/if}{/ts}{elseif $action eq 2}{ts}Edit Tag {if $isTagSet}Set{/if}{/ts}{else}{ts}Delete Tag {if $isTagSet}Set{/if}{/ts}{/if}</h3>
    {if $action eq 1 or $action eq 2 }
        <dl>
        <dt>{$form.name.label}</dt><dd>{$form.name.html}</dd>
        <dt>{$form.description.label}</dt><dd>{$form.description.html}</dd>
        {if $form.parent_id.html}
	    <dt>{$form.parent_id.label}</dt><dd>{$form.parent_id.html}</dd>
	    {/if}
	    <dt>{$form.used_for.label}</dt>
	        <dd>{$form.used_for.html}
	            <br />
	            <span class="description">
	                {if $is_parent}{ts}You can change the types of records which this tag can be used for by editing the 'Parent' tag.{/ts}
	                {else}{ts}What types of record(s) can this tag be used for?{/ts}
	                {/if}
	            </span>
	        </dd>
	{*if $accessHidden}
	    <dt>{$form.is_hidden.label}</dt>
	        <dd>
	            {$form.is_hidden.html}
	            <br /><span class="description">
	                {ts}Hidden tags are not displayed to users in the built-in Tag selection and display fields. They can only be assigned and displayed by custom extensions using the CiviCRM APIs.{/ts}
                    {if $is_parent} {ts}You can change the 'Hidden' property of this tag by editing the 'Parent' tag.{/ts}{/if}
                </span>
	        </dd>
	{/if*}
        <dt>{$form.is_reserved.label}</dt>
            <dd>{$form.is_reserved.html}
                <br /><span class="description">{ts}'Reserved' tags can be applied to records by any user with edit permission on that record. However only users with 'administer reserved tags' permission can modify the tags themselves. You must uncheck 'Reserved' (and delete any child tags) before you can delete a tag.{/ts} 
            </dd>
        </dl>
        {if $parent_tags|@count > 0}
        <table class="form-layout-compressed">
            <tr><td><label>{ts}Remove Parent?{/ts}</label></td></tr>
            {foreach from=$parent_tags item=ctag key=tag_id}
                {assign var="element_name" value="remove_parent_tag_"|cat:$tag_id}
                <tr><td>&nbsp;&nbsp;{$form.$element_name.html}&nbsp;{$form.$element_name.label}</td></tr>
            {/foreach}
        </table><br />
        {/if}
    {else}
        <div class="status">{ts 1=$delName}Are you sure you want to delete <b>%1</b> Tag?{/ts}<br />{ts}This tag will be removed from any currently tagged contacts, and users will no longer be able to assign contacts to this tag.{/ts}</div>
    {/if}
    <dl>
    <dt></dt><dd>{include file="CRM/common/formButtons.tpl"}</dd>
    </dl>
    <div class="spacer"></div>
</div>
