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
{if ! $suppressForm}
<form {$form.attributes} >
{/if}

{include file="CRM/Form/body.tpl"}

{include file=$tplFile}

{if ! $suppressForm}
</form>

  {if $smarty.get.snippet neq 5}
{literal}
<script type="text/javascript" >
cj( function($) {
  var params = CRM.validate_params || {};
  var default_params = { 
    'errorClass': 'crm-error',
    messages: {{/literal}
      required: "{ts}This field is required.{/ts}",
      remote: "{ts}Please fix this field.{/ts}",
      email: "{ts}Please enter a valid email address.{/ts}",
      url: "{ts}Please enter a valid URL.{/ts}",
      date: "{ts}Please enter a valid date.{/ts}",
      dateISO: "{ts}Please enter a valid date (ISO).{/ts}",
      number: "{ts}Please enter a valid number.{/ts}",
      digits: "{ts}Please enter only digits.{/ts}",
      creditcard: "{ts}Please enter a valid credit card number.{/ts}",
      equalTo: "{ts}Please enter the same value again.{/ts}",
      accept: "{ts}Please enter a value with a valid extension.{/ts}",
      maxlength: $.validator.format("{ts}Please enter no more than {ldelim}0{rdelim} characters.{/ts}"),
      minlength: $.validator.format("{ts}Please enter at least {ldelim}0{rdelim} characters.{/ts}"),
      rangelength: $.validator.format("{ts}Please enter a value between {ldelim}0{rdelim} and {ldelim}1{rdelim} characters long.{/ts}"),
      range: $.validator.format("{ts}Please enter a value between {ldelim}0} and {ldelim}1{rdelim}.{/ts}"),
      max: $.validator.format("{ts}Please enter a value less than or equal to {ldelim}0{rdelim}.{/ts}"),
      min: $.validator.format("{ts}Please enter a value greater than or equal to {ldelim}0{rdelim}.{/ts}")
    {literal}}
  };

  // use civicrm notifications when there are errors
  if(!CRM.urlIsPublic) {
    function error_count(errors) {
      return (errors == 1) ? (errors+' {/literal}{ts}error{/ts}{literal}') : (errors+' {/literal}{ts}errors{/ts}{literal}');
    }

    var crm_error = null;
    default_params.invalidHandler = function(form, validator){
      var errors = validator.numberOfInvalids();
      if (errors) {
        var message = '{/literal}{ts}Please correct the highlighted form errors{/ts} (<span id="crm-form-error-count">'+error_count(errors)+'</span>){literal}';
        crm_error = $().crmAlert(message, 'Form Errors', 'error');
      }
    }
    default_params.success = function(label){
      var errors = $('form#EventInfo').data('validator').numberOfInvalids();
      if(!errors) {
        if(crm_error && crm_error.close) crm_error.close();
      } else {
        $('#crm-form-error-count').html(error_count(errors));
      }
    }
  }

  var new_params = cj.extend(true, default_params, params, true);
  cj("#{/literal}{$form.formName}{literal}").validate(new_params);
});
</script>
{/literal}
{/if}
{/if}
