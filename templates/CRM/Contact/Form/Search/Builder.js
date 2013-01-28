// http://civicrm.org/licensing
(function($, CRM) {
  // @var: default select operator options
  var operators, operatorCount;

  /**
   * Handle Field Selection
   */
  function handleFieldSelection() {
    var field = $(this).val();
    var row = $(this).closest('tr');
    if (!CRM.pseudoconstant[field]) {
      removeSelect(row);
    }
    if ($.inArray(field, CRM.searchBuilder.dateFields) < 0) {
      removeDate(row);
      if (CRM.pseudoconstant[field]) {
        buildSelect(row, field);
      }
    }
    else {
      buildDate(row);
    }
  }

  /**
   * Handle Search Operator Selection
   */
  function handleOperatorSelection() {
    var noValue = ['', 'IS EMPTY', 'IS NOT EMPTY', 'IS NULL', 'IS NOT NULL'];
    var row = $(this).closest('tr');
    if ($.inArray($(this).val(), noValue) < 0) {
      $('.crm-search-value', row).show();
      // Change between multiselect and select when using "IN" operator
      var select = $('.crm-search-value select', row);
      if (select.length) {
        var value = select.val();
        var multi = ($(this).val() == 'IN' || $(this).val() == 'NOT IN');
        select.attr('multiple', multi);
        if (multi) {
          $('option[value=""]', select).remove();
        }
        else if ($('option[value=""]', select).length < 1) {
          $(select).prepend('<option value="">' + ts('- select -') + '</option>');
        }
        select.val(value).change();
      }
    }
    // Hide value field if the operator doesn't take a value
    else {
      $('.crm-search-value', row).hide().find('input, select').val('');
    }
  }

  /**
   * Give user a list of options to choose from
   * @param row: jQuery object
   * @param field: string
   */
  function buildSelect(row, field) {
    // Remove operators that can't be used with a select
    removeOperators(row, ['>', '<', '>=', '<=', 'LIKE', 'RLIKE']);
    var op = $('select[id^=operator]', row);
    var multi = (op.val() == 'IN' || op.val() == 'NOT IN');
    if (multi) {
      var multiSelect = 'multiple="multiple">';
    }
    else {
      var multiSelect = '><option value="">' + ts('- select -') + '</option>';
    }
    $('.crm-search-value select', row).remove();
    $('input[id^=value]', row).hide().after('<select class="form-select" ' + multiSelect + '</select>');
    fetchOptions(row, field, multi);
  }

  /**
   * Retrieve option list for given row
   * @param row: jQuery object
   * @param field: string
   * @param multi: bool
   */
  function fetchOptions(row, field, multi) {
    if (CRM.pseudoconstant[field] === 'yesno') {
      CRM.pseudoconstant[field] = {1: ts('Yes'), 0: ts('No')};
    }
    if (typeof(CRM.pseudoconstant[field]) == 'string') {
      CRM.api(CRM.pseudoconstant[field], 'getoptions', {field: field}, {
        success: function(result) {
          if (result.count) {
            CRM.pseudoconstant[field] = result.values;
            buildOptions(row, field, multi);
          }
          else {
            removeSelect(row);
          }
        },
        error: function() {
          removeSelect(row);
        }
      });
    }
    else {
      buildOptions(row, field, multi);
    }
  }

  /**
   * Populate option list for given row
   * @param row: jQuery object
   * @param field: string
   */
  function buildOptions(row, field, multi) {
    var select = $('.crm-search-value select', row);
    var value = $('input[id^=value]', row).val();
    if (value.length && value.charAt(0) == '(' && value.charAt(value.length - 1) == ')') {
      value = value.slice(1, -1);
    }
    var options = value.split(',');
    if (!multi && options.length > 1) {
      options = [options[0]];
    }
    $.each(CRM.pseudoconstant[field], function(value, label) {
      var selected = ($.inArray(value, options) > -1) ? 'selected="selected"' : '';
      select.append('<option value="' + value + '"' + selected + '>' + label + '</option>');
    });
    select.change();
  }

  /**
   * Remove select options and restore input to a plain textfield
   * @param row: jQuery object
   */
  function removeSelect(row) {
    $('.crm-search-value input', row).show();
    $('.crm-search-value select', row).remove();
    restoreOperators(row);
  }

  /**
   * Add a datepicker
   * @param row: jQuery object
   */
  function buildDate(row) {
    var input = $('.crm-search-value input', row);
    if (!input.hasClass('hasDatepicker')) {
      // Remove operators that can't be used with a date
      removeOperators(row, ['IN', 'NOT IN', 'LIKE', 'RLIKE']);
      input.addClass('dateplugin').datepicker({
        dateFormat: 'yymmdd',
        changeMonth: true,
        changeYear: true,
        yearRange: '-100:+20'
      });
    }
  }

  /**
   * Remove datepicker
   * @param row: jQuery object
   */
  function removeDate(row) {
    var input = $('.crm-search-value input', row);
    if (input.hasClass('hasDatepicker')) {
      restoreOperators(row);
      input.removeClass('dateplugin').val('').datepicker('destroy');
    }
  }

  /**
   * Remove operators from a row
   * @param row: jQuery object
   * @param illegal: array
   */
  function removeOperators(row, illegal) {
    $('select[id^=operator] option', row).each(function() {
      if ($.inArray($(this).attr('value'), illegal) > -1) {
        $(this).remove();
      }
    });
  }

  /**
   * Restore operators to the default
   * @param row: jQuery object
   */
  function restoreOperators(row) {
    var op = $('select[id^=operator]', row);
    if ($('option', op).length != operatorCount) {
      var value = op.val();
      op.html(operators).val(value).change();
    }
  }

  $('document').ready(function() {
    operators = $('#operator_1_0').html();
    operatorCount = $('#operator_1_0 option').length;

    // Hide empty blocks & fields
    var newBlock = CRM.searchBuilder && CRM.searchBuilder.newBlock || 0;
    $('#Builder .crm-search-block').each(function(blockNo) {
      var block = $(this);
      var empty = blockNo + 1 > newBlock;
      var skippedRow = false;
      $('tr:not(.crm-search-builder-add-row)', block).each(function(rowNo) {
        var row = $(this);
        if ($('select:first', row).val() === '') {
          if (!skippedRow && (rowNo == 0 || blockNo + 1 == newBlock)) {
            skippedRow = true;
          }
          else {
            row.hide();
          }
        }
        else {
          empty = false;
        }
      });
      if (empty) {
        block.hide();
      }
    });
    $('#Builder input[id^=value]').wrap('<span class="crm-search-value" />');

    $('#Builder')
      // Reset and hide row
      .on('click', '.crm-reset-builder-row', function() {
        var row = $(this).closest('tr');
        $('input, select', row).val('').change();
        row.hide();
        // Hide entire block if this is the only visible row
        if (row.siblings(':visible').length < 2) {
          row.closest('.crm-search-block').hide();
        }
        return false;
      })
      // Add new field - if there's a hidden one, show it
      // Otherwise we submit form to fetch more from the server
      .on('click', 'input[name^=addMore]', function() {
        var table = $(this).closest('table');
        if ($('tr:hidden', table).length) {
          $('tr:hidden', table).first().show();
          return false;
        }
      })
      // Add new block - if there's a hidden one, show it
      // Otherwise we submit form to fetch more from the server
      .on('click', '#addBlock', function() {
        if ($('.crm-search-block:hidden', '#Builder').length) {
          var block = $('.crm-search-block:hidden', '#Builder').first();
          block.show();
          $('tr:first-child, tr.crm-search-builder-add-row', block).show();
          return false;
        }
      })
      // Handle field selection
      .on('change', 'select[id^=mapper][id$="_1"]', handleFieldSelection)
      // Handle operator selection
      .on('change', 'select[id^=operator]', handleOperatorSelection)
      // Handle option selection - update hidden value field
      .on('change', '.crm-search-value select', function() {
        var value = $(this).val();
        if ($(this).attr('multiple') == 'multiple') {
          value = '(' + value.join(',') + ')';
        }
        $(this).siblings('input').val(value);
      })
    ;
    $().crmAccordions();
    $('select[id^=operator]', '#Builder').each(handleOperatorSelection);
    $('select[id^=mapper][id$="_1"]', '#Builder').each(handleFieldSelection);
  });
})(cj, CRM);
