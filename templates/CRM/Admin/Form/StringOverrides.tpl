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
{if $addRow}
<div id="addRow"></div>
{else} 
{* this template is used for adding/editing string overrides  *}
<div class="form-item">
<fieldset><legend>{ts}String Overrides{/ts}</legend>
<table class="form-layout-compressed">
	<tr>
	    <td>
	    <fieldset>
    	    <table>
		<tr class="columnheader">
		    <td>{ts}Enabled{/ts}</td>
		    <td>{ts}Original{/ts}</td>
    		    <td>{ts}Replacement{/ts}</td>
    		    <td>{ts}Exact Match?{/ts}</td>
    		</tr>

 		{section name="numStrings" start=1 step=1 loop=$numStrings}
			 {assign var='temp' value='enabled_'}
       			 {assign var='enabledName'  value=$temp|cat:"`$smarty.section.numStrings.index`"}
			 {assign var='temp' value='old_'}
			 {assign var='oldName' value=$temp|cat:"`$smarty.section.numStrings.index`"}
       			 {assign var='temp' value='new_'}
       			 {assign var='newName' value=$temp|cat:"`$smarty.section.numStrings.index`"}
       			 {assign var='temp' value='cb_'}
       			 {assign var='cbName'  value=$temp|cat:"`$smarty.section.numStrings.index`"}
                <div id="addRow"> 
		<tr>
		    <td class="even-row">{$form.$enabledName.html}</td>	
		    <td class="even-row">{$form.$oldName.html}</td>
    		    <td class="even-row">{$form.$newName.html}</td>
 		    <td class="even-row">{$form.$cbName.html}</td>
    		</tr>
                </div> 
    		{/section}
    	    </table>
	    </fieldset>
	    </td>
	</tr>
	<tr>
	    <td> <a class="button" onClick="Javascript:addRow();return false;"><span><div class="icon add-icon"></div>{ts}Add new Row{/ts}</span></a> {$form.buttons.html}</td>
	</tr>
</table>
</fieldset>
</div>
{/if}
{literal}
<script type="text/javascript">
function addRow( ) {
   var dataUrl  = {/literal}"{crmURL q='snippet=4'}"{literal} ;
   dataUrl     += "?addrow=1";
    cj.ajax({ 
              url     : dataUrl,   
              async   : false,
              success : function(html){
                           cj('#addRow').after(html);
        }
     });
}   
</script>
{/literal} 