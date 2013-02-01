// http://civicrm.org/licensing
(function($) {

  function crmFormInline(o) {
    var data = o.data('edit-params');
    if (o.is('.crm-edit-ready .crm-inline-edit') && data) {
      o.animate({height: '+=50px'}, 200);
      data.snippet = 5;
      data.reset = 1;
      o.addClass('form');
      o.closest('.crm-edit-ready').removeClass('crm-edit-ready');
      addCiviOverlay(o);
      $.ajax({
        url: CRM.url('civicrm/ajax/inline', data),
      }).done( function(response) {
        o.css('overflow', 'hidden').wrapInner('<div class="inline-edit-hidden-content" style="display:none" />').append(response);
        // Smooth resizing
        var newHeight = $('.crm-container-snippet', o).height();
        var diff = newHeight - parseInt(o.css('height'));
        if (diff < 0) {
          diff = 0 - diff;
        }
        o.animate({height: '' + newHeight + 'px'}, diff * 2, function() {
          o.removeAttr('style');
        });
        removeCiviOverlay(o);
        $('form', o).ajaxForm({
          dataType:'html',
          method:'POST',
          data: data,
          success: requestHandler
        });
        o.trigger('crmFormLoad');
      });
    }
  };

  function requestHandler(response) {
    var o = $('div.crm-inline-edit.form');
    addCiviOverlay($('.crm-container-snippet', o));

    o.trigger('crmFormSuccess', [response]);
    try {
      response = $.parseJSON(response);
      if ( response.status == 'save' || response.status == 'cancel' ) {
        o.closest('.crm-inline-edit-container').addClass('crm-edit-ready');
        var data = o.data('edit-params');
        var dependent = o.data('dependent-fields') || [];
        // Clone the add-new link if replacing it, and queue the clone to be refreshed as a dependant block
        if (o.hasClass('add-new')) {
          if (response.addressId) {
            data.aid = response.addressId;
          }
          var clone = o.parent().clone();
          o.data('edit-params', data);
          $('.crm-container-snippet', clone).remove();
          if (clone.hasClass('contactCardLeft')) {
            clone.removeClass('contactCardLeft').addClass('contactCardRight');
          }
          else if (clone.hasClass('contactCardRight')) {
            clone.removeClass('contactCardRight').addClass('contactCardLeft');
          }
          var cl = $('.crm-inline-edit', clone);
          var clData = cl.data('edit-params');
          var locNo = clData.locno++;
          cl.attr('id', cl.attr('id').replace(locNo, clData.locno)).removeClass('form')
            o.parent().after(clone);
          $.merge(dependent, $('.crm-inline-edit', clone));
        }
        // Reload this block plus all dependent blocks
        var update = $.merge([o], dependent);
        for (var i in update) {
          $(update[i]).each(function() {
            var data = $(this).data('edit-params');
            data.snippet = 1;
            data.reset = 1;
            data.class_name = data.class_name.replace('Form', 'Page');
            data.type = 'page';
            $(this).closest('.crm-summary-block').load(CRM.url('civicrm/ajax/inline', data), function() {$(this).trigger('load');});
          });
        }
        // Update changelog tab and contact footer
        $("#tab_log a em").html(response.changeLog.count);
        $("#crm-record-log").replaceWith(response.changeLog.markup);
        if ($('#Change_Log div').length) {
          $('#Change_Log').load($("#tab_log a").attr('href'));
        }

        CRM.alert('', ts('Saved'), 'success');
      }
    }
    catch(e) {
      //this is called incase of formRule error
      $('form', o).ajaxForm('destroy');
      $('.crm-container-snippet', o).replaceWith(response);
      var data = o.data('edit-params');
      $('form', o).ajaxForm({
        dataType:'html',
        method:'POST',
        data: data,
        success: requestHandler
      });

      o.trigger('crmFormError', [response]).trigger('crmFormLoad');
    }
  }; 

  /**
   * Configure optimistic locking mechanism for inplace editing
   *
   * options.ignoreLabel: string, text for a button
   * options.reloadLabel: string, text for a button
   */
  $.fn.crmFormContactLock = function(options) {
    var form = this;
    // AFTER ERROR: Render any "Ignore" and "Restart" buttons
    return this.on('crmFormError', function(event, obj, status) {
      var o = $(event.target);
      var data = o.data('edit-params');
      var errorTag = o.find('.update_oplock_ts');
      if (errorTag.length > 0) {
        $('<span>')
          .addClass('crm-lock-button')
          .appendTo(errorTag);

        var buttonContainer = o.find('.crm-lock-button');
        $('<button>')
          .addClass('crm-button')
          .text(options.saveAnywayLabel)
          .click(function() {
            $(form).find('input[name=oplock_ts]').val(errorTag.attr('data:update_oplock_ts'));
            errorTag.parent().hide();
            $(this).closest('form').find('.form-submit.default').first().click();
            return false;
          })
          .appendTo(buttonContainer)
          ;
        $('<button>')
          .addClass('crm-button')
          .text(options.reloadLabel)
          .click(function() {
            window.location.reload();
            return false;
          })
          .appendTo(buttonContainer)
          ;
      }
    });
  };

  $('document').ready(function() {
    // Set page title
    var oldName = 'CiviCRM';
    var nameTitle = $('#crm-remove-title');
    if (nameTitle.length > 0) {
      oldName = nameTitle.text();
      nameTitle.parent('h1').remove();
    }
    else {
      $('h1').each(function() {
        if ($(this).text() == oldName) {
          $(this).remove();
        }
      });
    }
    function refreshTitle() {
      var contactName = $('.crm-summary-display_name').text();
      contactName = $.trim(contactName);
      var title = $('title').html().replace(oldName, contactName);
      document.title = title;
      oldName = contactName;
    }
    $('#contactname-block').load(refreshTitle);
    refreshTitle();

    var clicking;
    $('.crm-inline-edit-container')
      .addClass('crm-edit-ready')
      // Allow links inside edit blocks to be clicked without triggering edit
      .on('mousedown', '.crm-inline-edit:not(.form) a, .crm-inline-edit:not(.form) .crm-accordion-header, .crm-inline-edit:not(.form) .collapsible-title', function(event) {
        if (event.which == 1) {
          event.stopPropagation();
          return false;
        }
      })
      // Respond to a click (not drag, not right-click) of crm-inline-edit blocks
      .on('mousedown', '.crm-inline-edit:not(.form)', function(button) {
        if (button.which == 1) {
          clicking = this;
          setTimeout(function() {clicking = null;}, 500);
        }
      })
      .on('mouseup', '.crm-inline-edit:not(.form)', function(button) {
        if (clicking === this && button.which == 1) {
          crmFormInline($(this));
        }
      })
      // Inline edit form cancel button
      .on('click', '.crm-inline-edit :submit[name$=cancel]', function() {
        var container = $(this).closest('.crm-inline-edit.form');
        $('.inline-edit-hidden-content', container).nextAll().remove();
        $('.inline-edit-hidden-content > *:first-child', container).unwrap();
        container.removeClass('form');
        container.closest('.crm-inline-edit-container').addClass('crm-edit-ready');
        return false;
      })
      // Switch tabs when clicking tag link
      .on('click', '#tagLink a', function() {
        $('#tab_tag a').click();
        return false;
      })
      // make sure only one is_primary radio is checked
      .on('change', '[class$=is_primary] input', function() {
        if ($(this).is(':checked')) {
          $('[class$=is_primary] input', $(this).closest('form')).not(this).prop('checked', false);
        }
      })
      // make sure only one builk_mail radio is checked
      .on('change', '.crm-email-bulkmail input', function(){
        if ($(this).is(':checked')) {
          $('.crm-email-bulkmail input').not(this).prop('checked', false);
        }
      })
      // handle delete link within blocks
      .on('click', '.crm-delete-inline', function() {
        var row = $(this).closest('tr');
        var form = $(this).closest('form');
        row.addClass('hiddenElement');
        $('input', row).val('');
        //if the primary is checked for deleted block
        //unset and set first as primary
        if ($('[class$=is_primary] input:checked', row).length > 0) {
          $('[class$=is_primary] input', row).prop('checked', false);
          $('[class$=is_primary] input:first', form).prop('checked', true );
        }
        $('.add-more-inline', form).show();
      })
      // add more and set focus to new row
      .on('click', '.add-more-inline', function() {
        var form = $(this).closest('form');
        var row = $('tr[class="hiddenElement"]:first', form);
        row.removeClass('hiddenElement');
        $('input:focus', form).blur();
        $('input:first', row).focus();
        if ($('tr[class="hiddenElement"]').length < 1) {
          $(this).hide();
        }
      });
    // Trigger cancel button on esc keypress
    $(document).keydown(function(key) {
      if (key.which == 27) {
        $('.crm-inline-edit.form :submit[name$=cancel]').click();
      }
    });
    // Switch tabs when clicking log link
    $('#crm-container').on('click', '#crm-record-log a.crm-log-view', function() {
      $('#tab_log a').click();
      return false;
    });
  });
})(cj);
