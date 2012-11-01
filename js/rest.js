/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
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
*/
/*
* Copyright (C) 2009-2010 Xavier Dutoit
* Licensed to CiviCRM under the Academic Free License version 3.0.
*
*/

(function($) {

  /**
   * Almost like {crmURL} but on the client side
   * eg: var url = cj.crmURL ('civicrm/contact/view', {reset:1,cid:42});
   * or: $('a.crmURL').crmURL();
   */
  var tplURL = '/civicrm/example?placeholder';
  $.extend ({ 'crmURL':
    function (p, params) {
      if (p == "init") {
        tplURL = params;
        return;
      }
      params = params || '';
      var frag = p.split ('?');
      var url = tplURL.replace("civicrm/example", frag[0]);

      if (typeof(params) == 'string') {
        url = url.replace("placeholder", params);
      }
      else {
        url = url.replace("placeholder", $.param(params));
      }
      if (frag[1]) {
        url += (url.indexOf('?') === (url.length - 1) ? '' : '&') + frag[1];
      }
      // remove trailing "?"
      if (url.indexOf('?') === (url.length - 1)) {
        url = url.slice(0, (url.length - 1));
      }
      return url;
    }
  });

  $.fn.crmURL = function () {
    return this.each(function() {
      if (this.href) {
        this.href = $.crmURL(this.href);
      }
    });
  };

  var ts = {};

  var defaults = {
    success: function(result,settings){
      $().crmAlert('', CRM.ts.saved, 'success');
      return true;
    },
    error: function(result,settings){
      $().crmError(result.error_message, CRM.ts.error);
      return false;
    },
    callBack: function(result,settings){
      if (result.is_error == 1) {
        return settings.error.call(this,result,settings);
        return false;
      }
      return settings.success.call(this,result,settings);
    },
    ajaxURL: 'civicrm/ajax/rest',
  };

  $.fn.crmAPI = function(entity, action, params, options) {
    params ['entity'] = entity;
    params ['action'] = action;
    params ['json'] = 1;
    var settings = $.extend({}, defaults, options);
    $.ajax({
      url: $.crmURL(settings.ajaxURL),
      dataType: 'json',
      data: params,
      type:'POST',
      context:this,
      success: function(result) {
        settings.callBack.call(this, result, settings);
      }
    });
  };

  $.fn.crmAutocomplete = function (params, options) {
    if (typeof params == 'undefined') params = {};
    if (typeof options == 'undefined') options = {};
    params = $().extend( {
      rowCount:35,
      json:1,
      entity:'Contact',
      action:'quicksearch',
      sequential:1
    }, params);

    options = $().extend({}, {
        field :'name',
        skip : ['id','contact_id','contact_type','contact_is_deleted',"email_id",'address_id', 'country_id'],
        result: function(data){
             console.log(data);
        return false;
      },
      formatItem: function(data,i,max,value,term){
        var tmp = [];
        for (attr in data) {
          if ($.inArray (attr, options.skip) == -1 && data[attr]) {
            tmp.push(data[attr]);
          }
        }
        return  tmp.join(' :: ');
      },
      parse: function (data){
             var acd = new Array();
             for(cid in data.values){
               delete data.values[cid]["data"];// to be removed once quicksearch doesn't return data
               acd.push({ data:data.values[cid], value:data.values[cid].sort_name, result:data.values[cid].sort_name });
             }
             return acd;
      },
      delay:100,
      minChars:1
      }, options
    );
    var contactUrl = $.crmURL(defaults.ajaxURL, params);

  return this.each(function() {
    var selector = this;
    if (typeof $.fn.autocomplete != 'function')
        $.fn.autocomplete = cj.fn.autocomplete;//to work around the fubar cj
        var extraP = {};
        extraP [options.field] = function () {return $(selector).val();};
        $(this).autocomplete( contactUrl, {
          extraParams:extraP,
          formatItem: function(data,i,max,value,term){
            return options.formatItem(data,i,max,value,term);
          },
          parse: function(data){ return options.parse(data);},
          width: 250,
          delay:options.delay,
          max:25,
          dataType:'json',
          minChars:options.minChars,
          selectFirst: true
       }).result(function(event, data, formatted) {
            options.result(data);
        });
     });
   }

})(jQuery);
