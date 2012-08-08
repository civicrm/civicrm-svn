{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
<div class="crm-inline-edit-form crm-table2div-layout">
  <div class="crm-inline-button">
    {include file="CRM/common/formButtons.tpl"}
  </div>
  <br/><br/>
  <table class="form-layout-compressed">
    <tr>
    {if $contactType eq 'Individual'}
      {if $form.prefix_id}
      <td>
        {$form.prefix_id.label}<br/>
        {$form.prefix_id.html}
      </td>    
      {/if}
      <td>
        {$form.first_name.label}<br /> 
        {$form.first_name.html}
      </td>
      <td>
        {$form.middle_name.label}<br />
        {$form.middle_name.html}
      </td>
      <td>
        {$form.last_name.label}<br />
        {$form.last_name.html}
      </td>
      {if $form.suffix_id}
      <td>
        {$form.suffix_id.label}<br/>
        {$form.suffix_id.html}
      </td>
      {/if}
    {elseif $contactType eq 'Organization'}
      <td>{$form.organization_name.label}</td>
      <td>{$form.organization_name.html}</td>
    {elseif $contactType eq 'Household'}
      <td>{$form.household_name.label}</td>
      <td>{$form.household_name.html}</td>
    {/if}
    </tr> 
  </table>
</div>
{include file="CRM/Contact/Form/Inline/InlineCommon.tpl"}

{literal}
<script type="text/javascript">

cj( function() {
// add ajax form submitting
inlineEditForm( 'ContactName', 'contactname-block', {/literal}{$contactId}{literal} ); 
});
</script>
{/literal}

