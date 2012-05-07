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

*
* Copyright (C) 2012 Xavier Dutoit
* Licensed to CiviCRM under the Academic Free License version 3.0.
*
*
* This offers two features:
* - crmEditable() edit in place of a single field 
*  (mostly a wrapper that binds jeditable features with the ajax api and replies on crm-entity crmf-{field} html conventions)
*  if you want to add an edit in place on a template:
*  - add a class crm-entity and id {EntityName}-{Entityid} higher in the dom
*  - add a class crm-editable and crmf-{FieldName} around the field (you can add a span if needed)
*  crmf- stands for crm field
* - crmForm()
*   this embed a civicrm form and make it in place (load+ajaxForm) 
*   to make it easier to customize the form (eg. hide a button...) it triggers a 'load' event on the form. you can then catch the load on your code (using the $('#id_of_the_form').on(function(){//do something
*/


(function($){

    $.fn.crmEditable = function (options) {

      var checkable = function () {
        $(this).change (function() {
          var params={sequential:1};
          var checked = $(this).is(':checked');
          var id= $(this).closest('.crm-entity').attr('id');
          var fieldName=this.className.match(/crmf-(\S*)/)[1];
          if (!fieldName) {
            $().crmNotification ("FATAL crm-editable: Couldn't get the field name to modify. You need to set crmf-{field_name}",'notification',this);
            return false;
          }
          params['field']=fieldName;
          params['value']=checked?'1':'0';//seems that the ajax backend gets lost with boolean

          if (id) {
             var e=id.match(/(\S*)-(\S*)/);
             if (!e) 
               $().crmNotification ("Couldn't get the entity id. You need to set class='crm-entity' id='{entityName}-{id}'",'notification',this);
             entity=e[1];
             params.id=e[2];
          } else {
            $().crmNotification ("FATAL crm-editable: Couldn't get the entity id. You need to set class='crm-entity' id='{entityName}-{id}'",'notification',this);
            return false;
          }
          //$().crmAPI.call(this,entity,'create',params,{ create is still too buggy & perf
          $().crmAPI.call(this,entity,'setvalue',params,{
            error: function (data) {
              editableSettings.error.call(this,entity,fieldName,checked,data);
            },
            success: function (data) {
              editableSettings.success.call(this,entity,fieldName,checked,data);
            }
          });
        });
      };

      var defaults = {
        form:{},
        callBack:function(data){
          if (data.is_error) {
            editableSettings.error.call (this,data);
          } else {
             return editableSettings.success.call (this,data);
          }
        },
        error: function(entity,field,value,data) {
          $().crmNotification (data.error_message,'error',data);
          $(this).removeClass ('crm-editable-saving').addClass('crm-editable-error');
        },
        success: function(entity,field,value,data) {
          var $i=$(this);
          $().crmNotification (false);
          $i.removeClass ('crm-editable-saving').removeClass ('crm-editable-error');
          $i.html(value);
        },
      }

      var editableSettings = $.extend({}, defaults, options);
  	  return this.each(function() {
        var $i = $(this);
        var fieldName = "";
      
        if (this.nodeName == "INPUT" && this.type=="checkbox") {
          checkable.call(this,this);
          return;
        }

        if (this.nodeName = 'A') {
          if (this.className.indexOf('crmf-') == -1) { // it isn't a jeditable field
            var formSettings= $.extend({}, editableSettings.form ,
              {source: $i.attr('href')
              ,success: function (result) {
                if ($i.hasClass('crm-dialog')) {
                  $('.ui-dialog').dialog('close').remove();
                } else 
                  $i.next().slideUp().remove();
                $i.trigger('success',result);
              }
              });
            var id= $i.closest('.crm-entity').attr('id');
            if (id) {
              var e=id.match(/(\S*)-(\S*)/);
               if (!e) 
                 $().crmNotification ("Couldn't get the entity id. You need to set class='crm-entity' id='{entityName}-{id}'",'notification',this);
              formSettings.entity=e[1];
              formSettings.id=e[2];
            } 
            if ($i.hasClass('crm-dialog')) {
              $i.click (function () {
                var $n=$('<div>Loading</div>').appendTo('body');
                $n.dialog ({modal:true,width:500});
                $n.crmForm (formSettings);
                return false; 
              });
            } else {
              $i.click (function () {
                var $n=$i.next();
                if (!$n.hasClass('crm-target')) {
                  $n=$i.after('<div class="crm-target"></div>').next();
                } else {
                  $n.slideToggle();
                  return false;
                };
                $n.crmForm (formSettings);
                return false; 
              });
            }
            return;
          }
        }


        var settings = {
          tooltip   : 'Click to edit...',
          placeholder  : '<span class="crm-editable-placeholder">Click to edit</span>',
          data: function(value, settings) {
            return value.replace(/<(?:.|\n)*?>/gm, '');
          }
        };
        if ($i.data('placeholder')) {
          settings.placeholder = $i.data('placeholder');
        } else {
          settings.placeholder  = '<span class="crm-editable-placeholder">Click to edit</span>';
        }
        if ($i.data('tooltip')) {
          settings.placeholder = $i.data('tooltip')
        } else {
          settings.tooltip   = 'Click to edit...';
        }

        $i.addClass ('crm-editable-enabled');
        $i.editable(function(value,settings) {
        //$i.editable(function(value,editableSettings) {
          parent=$i.parent('.crm-entity');
          if (!parent) {
            $().crmNotification ("crm-editable: you need to define one parent element that has a class .crm-entity",'notification',this);
            return;
          }

          $i.addClass ('crm-editable-saving');
          var params = {};
          // trying to extract using the html5 data
          var entity=parent.data('entity');
          params.id = parent.data('id');
          if (!entity) { //trying to extract it from the id (format: entity-id, eg: id='contact-42') if no html5 data
             var id= cj(this).closest('.crm-entity').attr('id');
             if (id) {
               var e=id.match(/(\S*)-(\S*)/);
               if (!e) 
                 $().crmNotification ("Couldn't get the entity id. You need to set class='crm-entity' id='{entityName}-{id}'",'notification',this);
               entity=e[1];
               params.id=e[2];
             }
          }
          if (!params.id) {
            cj().crmNotification ("FATAL crm-editable: Couldn't get the id of the entity "+entity,'notification',this);
            return false;
          }

          if (params.id == "new") {
            params.id = '';
          }

          if ($i.data('field')) {
            //params[$i.data('field')] = value;
            fieldName = $i.data('field');
          } else {
            fieldName=this.className.match(/crmf-(\S*)/)[1];
            if (!fieldName) {
              cj().crmNotification ("FATAL crm-editable: Couldn't get the field name to modify. You need to set crmf-{field_name} or data-field='{field_name}' ",'notification',this);
              return false;
            }
             
          }  

          params['field']=fieldName
          params['value']=value;
          var self=this;
          $().crmAPI.call(this,entity,'setvalue',params,{
          //cj().crmAPI.call(this,entity,'create',params,{
              error: function (data) {
                editableSettings.error.call(this,entity,fieldName,value,data);
              },
              success: function (data) {
                editableSettings.success.call(this,entity,fieldName,value,data);
              }
            });
           },settings);
    });
 }
   
})(jQuery);
//})(cj);


(function($){

  $.fn.crmForm = function (options ) {
    var settings = $.extend( {
      'title':'',
      'entity':'',
      'action':'get',
      'id':0,
      'sequential':1,
      'dialog': false,
      'load' : function (target){},
      'success' : function (result) {
        $(this).html ("Saved");
       }
    }, options);


    return this.each(function() {
      var formLoaded = function (target) {
        var $this =$(target);
        var destination="<input type='hidden' name='civicrmDestination' value='"+$.crmURL('civicrm/ajax/rest',{
          'sequential':settings.sequential,
          'json':'html',
          'entity':settings.entity,
          'action':settings.action,
          'id':settings.id
          })+"' />";
        $this.find('form').ajaxForm({
          beforeSubmit :function () {
            $this.html("<div class='crm-editable-saving'>Saving...</div>");
            return true;
          },
          success:function(response) { 
            if (response.indexOf('crm-error') >= 0) { // we got an error, re-display the page
              $this.html(response);
              formLoaded(target);
            } else {
              if (response[0] == '{')
                settings.success($.parseJSON (response));
              else
                settings.success(response);
            }
          }
        }).append('<input type="hidden" name="snippet" value="1"/>'+destination).trigger('load'); 

        settings.load(target);
      };

      var $this = $(this);
       if (settings.source && settings.source.indexOf('snippet') == -1) {
         if (settings.source.indexOf('?') >= 0)
           settings.source = settings.source + "&snippet=1";
         else
           settings.source = settings.source + "?snippet=1";
       }


       $this.html ("Loading...");
       if (settings.dialog)
         $this.dialog({width:'auto',minWidth:600});
       $this.load (settings.source ,function (){formLoaded(this)});

    });
  };

})(jQuery);
//})(cj);

