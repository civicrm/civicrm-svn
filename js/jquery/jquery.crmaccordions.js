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
  $.fn.crmAccordions = function(speed) {
    if (speed === undefined) {
      speed = 200;
    }
    if ($(this).length > 0) {
      var container = $(this);
    }
    else {
      var container = $('#crm-container');
    }
    if (container.length > 0 && !container.hasClass('crm-accordion-processed')) {
      // Allow normal clicking of links
      container.on('click', 'div.crm-accordion-header a', function (e) {
        e.stopPropagation && e.stopPropagation();
      });
      container.on('click', '.crm-accordion-header, .crm-collapsible .collapsible-title', function () {
        if ($(this).parent().hasClass('collapsed')) {
          $(this).next().css('display', 'none').slideDown(speed);
        }
        else {
          $(this).next().css('display', 'block').slideUp(speed);
        }
        $(this).parent().toggleClass('collapsed');
        return false;
      });
      container.addClass('crm-accordion-processed');
    };
  };
  $.fn.crmAccordionToggle = function(speed) {
    $(this).each(function() {
      var wrapper = $(this);
      if (wrapper.hasClass('collapsed')) {
        $('.crm-accordion-body', wrapper).first().css('display', 'none').slideDown(speed);
      }
      else {
        $('.crm-accordion-body', wrapper).first().css('display', 'block').slideUp(speed);
      }
      wrapper.toggleClass('collapsed');
    });
  };
})(jQuery);
