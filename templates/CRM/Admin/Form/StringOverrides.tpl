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
{* this template is used for adding/editing string overrides  *}
<div class="form-item">
<fieldset><legend>{ts}String Overrides{/ts}</legend>

  <dl>
    <dt>{ts}Original{/ts}</dt><dd>{ts}Replacement{/ts}&nbsp;{ts}Exact Match?{/ts}</dd>
    {section name="numStrings" start=1 step=1 loop=$numStrings}
       {assign var='temp' value='old_'}
       {assign var='oldName' value=$temp|cat:"`$smarty.section.numStrings.index`"}
       {assign var='temp' value='new_'}
       {assign var='newName' value=$temp|cat:"`$smarty.section.numStrings.index`"}
       {assign var='temp' value='cb_'}
       {assign var='cbName'  value=$temp|cat:"`$smarty.section.numStrings.index`"}
       <dt>{$form.$oldName.html}</dt><dd>{$form.$newName.html}&nbsp;{$form.$cbName.html}</dd>
    {/section}
  </dl>

  <dl> 
    <dt></dt><dd>{$form.buttons.html}</dd>
  </dl> 
</fieldset>
</div>
