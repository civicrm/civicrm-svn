/*
* +--------------------------------------------------------------------+
* | CiviCRM version 4.2                                                |
* +--------------------------------------------------------------------+
* | Copyright CiviCRM LLC (c) 2004-2012                                |
* +--------------------------------------------------------------------+
* | This file is a part of CiviCRM.                                    |
* |                                                                    |
* | CiviCRM is free software; you can copy, modify, and distribute it  |
* | under the terms of the GNU Affero General Public License           |
* | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
* |                                                                    |
* | CiviCRM is distributed in the hope that it will be useful, but     |
* | WITHOUT ANY WARRANTY; without even the implied warranty of         |
* | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
* | See the GNU Affero General Public License for more details.        |
* |                                                                    |
* | You should have received a copy of the GNU Affero General Public   |
* | License and the CiviCRM Licensing Exception along                  |
* | with this program; if not, contact CiviCRM LLC                     |
* | at info[AT]civicrm[DOT]org. If you have questions about the        |
* | GNU Affero General Public License or the licensing of CiviCRM,     |
* | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
* +--------------------------------------------------------------------+
*/
(function($, undefined) {
  $.fn.crmtooltip = function(){
    $('a.crm-summary-link')
    .addClass('crm-processed')
    .live('mouseover',
      function(e)  {
          $(this).addClass('crm-tooltip-active');
          topDistance = e.pageY - $(window).scrollTop();
          if (topDistance < 300 | topDistance < $(this).children('.crm-tooltip-wrapper').height()) {
                $(this).addClass('crm-tooltip-down');
            }
        if ($(this).children('.crm-tooltip-wrapper').length == '') {
          $(this).append('<div class="crm-tooltip-wrapper"><div class="crm-tooltip"></div></div>');
          $(this).children().children('.crm-tooltip')
            .html('<div class="crm-loading-element"></div>')
            .load(this.href);
        }
      })
      .live('mouseout',
      function(){
        $(this).removeClass('crm-tooltip-active');
        $(this).removeClass('crm-tooltip-down');
        }
      )
    .live('click',
      function(){
        return false;
        }
      );
  };
  
  var h;
  CRM.help = function(title, params) {
    h && h.close && h.close();
    var options = {
      expires: 0
    };
    h = CRM.alert('...', title, 'crm-help crm-msg-loading', options);
    params.class_name = 'CRM_Core_Page_Help';
    params.type = 'page';
    $.ajax(CRM.url('civicrm/ajax/inline'),
      {
        data: params,
        dataType: 'html',
        success: function(data) {
          $('#crm-notification-container .crm-help .notify-content:last').html(data);
          $('#crm-notification-container .crm-help').removeClass('crm-msg-loading').addClass('info');
        },
        error: function(data) {
          $('#crm-notification-container .crm-help .notify-content:last').html('Unable to load help file.');
          $('#crm-notification-container .crm-help').removeClass('crm-msg-loading').addClass('error');
        }
      }
    );
  };

  CRM.alert = function(text, title, type, options) {
    type = type || 'alert';
    title = title || '';
    options = options || {};
    if ($('#crm-notification-container').length) {
      var params = {
        text: text,
        title: title,
        type: type
      };
      // By default, don't expire errors and messages containing links
      var extra = {
        expires: (type == 'error' || text.indexOf('<a ') > -1) ? 0 : (text ? 10000 : 5000),
        unique: true
      };
      options = $.extend(extra, options);
      options.expires = options.expires === false ? 0 : parseInt(options.expires);
      if (options.unique && options.unique !== '0') {
        $('#crm-notification-container .ui-notify-message').each(function() {
          if (title === $('h1', this).html() && text === $('.notify-content', this).html()) {
            $('.icon.ui-notify-close', this).click();
          }
        });
      }
      return $('#crm-notification-container').notify('create', params, options);
    }
    else {
      if (title.length) {
        text = title + "\n" + text;
      }
      alert(text);
      return null;
    }
  }

  /**
   * Sets an error message
   * If called for a form item, title and removal condition will be handled automatically
   */
  $.fn.crmError = function(text, title, options) {
    title = title || '';
    text = text || '';
    options = options || {};

    var extra = {
      expires: 0
    };
    if ($(this).length) {
      if (title == '') {
        var label = $('label[for="' + $(this).attr('name') + '"], label[for="' + $(this).attr('id') + '"]').not('[generated=true]');
        if (label.length) {
          label.addClass('crm-error');
          var $label = label.clone();
          if (text == '' && $('.crm-marker', $label).length > 0) {
            text = $('.crm-marker', $label).attr('title');
          }
          $('.crm-marker', $label).remove();
          title = $label.text();
        }
      }
      $(this).addClass('error');
    }
    var msg = CRM.alert(text, title, 'error', $.extend(extra, options));
    if ($(this).length) {
      var ele = $(this);
      setTimeout(function() {ele.one('change', function() {
        msg && msg.close && msg.close();
        ele.removeClass('error');
        label.removeClass('crm-error');
      });}, 1000);
    }
    return msg;
  }
  
  $(document).ready(function() {
    if (CRM && CRM.config && CRM.config.urlIsPublic === false) {
      // Initialize notifications
      $('#crm-notification-container').notify();
      // Display system alerts through js notifications
      $('#crm-container div.messages:visible').not('.help').not('.no-popup').each(function() {
        $(this).removeClass('status messages');
        var type = $(this).attr('class').split(' ')[0] || 'alert';
        type = type.replace('crm-', '');
        $('.icon', this).remove();
        var title = '';
        if ($('.msg-text', this).length > 0) {
          var text = $('.msg-text', this).html();
          title = $('.msg-title', this).html();
        }
        else {
          var text = $(this).html();
        }
        var options = $(this).data('options') || {};
        $(this).remove();
        // Duplicates were already removed server-side
        options.unique = false;
        CRM.alert(text, title, type, options);
      });
      // Handle qf form errors
      $('#crm-container form :input.error').one('blur', function() {
        $('.ui-notify-message.error a.ui-notify-close').click();
        $(this).removeClass('error');
        $(this).next('span.crm-error').remove();
        $('label[for="' + $(this).attr('name') + '"], label[for="' + $(this).attr('id') + '"]').removeClass('crm-error').find('.crm-error').removeClass('crm-error');
      });
    }
  });
})(jQuery);
