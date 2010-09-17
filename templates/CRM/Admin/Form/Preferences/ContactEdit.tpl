{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
{* this template is used for editing Site Preferences  *}
<div style="width:40%">
<ul id="contactEditSortable">
{foreach from = $contactEditOptions item = "title" key="id"}
<li id="preference-{$id}-contactedit" class="crm-accordion-header"><strong>{$title}</strong></li>
{/foreach}
</ul>
</div>
{literal}
<script type="text/javascript" >
var params = new Array();

cj(function( ) {
	cj("#contactEditSortable").sortable({
			placeholder: 'ui-state-highlight',
			update: getSorting
		});
		cj("#sortable").disableSelection();
});
 
function getSorting(e, ui) {
  var items = cj("#contactEditSortable li");
  for( var x=0; x<items.length; x++ ) {
  var idState = items[x].id.split('-');
    params[x+1] = idState[1];    
  }
  cj('#contact_edit_prefences').val( params.toString( ) );
}
</script>
{/literal}