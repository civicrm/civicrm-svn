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

<script type="text/javascript">
  cj(function($) {ldelim}
    {* Initialize cj.crmURL *}
    $.crmURL('init', '{crmURL p="civicrm/example" q="placeholder"}');
  {rdelim});

{*
 * Here we define the CRM object,
 * A single global variable to hold those things which absolutely MUST be global
 * (so in most cases scope your vars and don't put them here!).
 * Translated strings can be stored in the CRM.ts object
 * Very common strings are included here for convenience. Others should be added dynamically per-template.
 *
 * To extend this object for use in your own template, follow this example:
 * <script type="text/javascript">
 *   var CRM = CRM || {};
 *   CRM.myVar = '{$serverVariable}';
 *   CRM.ts.cow = '{ts escape="js"}Cow{/ts}';
 *   CRM.myFunction = function() {
 *     cj().crmAlert('{ts escape="js"}Avoid global functions!{/ts}', CRM.ts.ok);
 *   };
 * </script>
 *}
  {literal}
  var CRM = CRM || {};
  CRM = cj.extend(true, {
    ts: {{/literal}
      ok: '{ts escape="js"}OK{/ts}',
      cancel: '{ts escape="js"}Cancel{/ts}',
      yes: '{ts escape="js"}Yes{/ts}',
      no: '{ts escape="js"}No{/ts}',
      saved: '{ts escape="js"}Saved{/ts}',
      error: '{ts escape="js"}Error{/ts}'
    {rdelim},
    urlIsPublic: {if $urlIsPublic}true{else}false{/if},
    userFramework: '{$config->userFramework}',
    {literal}
    validate: {
      use: false,
      params: {},
      functions: []
    }
  }, CRM);
  {/literal}
</script>
