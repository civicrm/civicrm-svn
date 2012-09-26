{if $inplace}
{literal}<style>
aaa.ui-dialog #profile {font-size:10px;}
#helper-editdialog-dialog, #helper-clonedialog-dialog {display:none;}
.ui-dialog #helper-editdialog-dialog, .ui-dialog #helper-clonedialog-dialog {display:block;}

</style>{/literal}
<input id="editdialog" type="submit" value="Edit" disabled="disabled" data-url="{crmURL p='civicrm/inline/uf/edit_fields' q="id="}" />
<input id="clonedialog" type="submit" value="Copy Profile" title="Copy Profile" disabled="disabled" data-url="{crmURL p='civicrm/admin/uf/group' q="action=copy&json=1&gid="}" />
<input id="createdialog" type="submit" value="Create" />
<div id="helper-editdialog-dialog"><div id="helper-createdialog-dialog"><input id="name-new-dialog" class="crmf-title"></div></div>
<div id="helper-clonedialog-dialog"><div id="helper-createdialog-dialog"><input id="name-clone-dialog" class="crmf-title"></div></div>
<script type="text/javascript">
var selector = '{$selector}';
{literal}
    //show edit profile field links
    cj(function($) {

      $('#createdialog').click (function() {
        $("#helper-createdialog-dialog").dialog ({autoOpen:true, modal: true, width:900, closeOnEscape: true,
          title:"Create a new profile",
          buttons: {
            "Create": function() {
              var pop=$(this);
              var title = pop.find('.crmf-title').val();
              if (title == '') {
                $().crmNotification("Please give a title");
                return;
              }
              $().crmAPI('UFGroup','Create',{group_type:'Contact',title:title},{
                success:function(data) {
                  $('<option value="'+data.id+'" selected="selected">'+title+'</option>').attr('selected','selected').appendTo (selector);
                  pop.dialog( "close" );
                },
                error:function(data) {
                  alert (data.error_message);
                },

              });
            },
            Cancel: function() {
              $( this ).dialog( "close" );
            }
          },
          draggable:true,
          width:250,
          minHeight:150
        });
        return false;
      });

      $('#clonedialog').click (function() {
        var profile_id = $(selector + " option:selected").val();

        if (profile_id < 1) {
          $().crmNotification ("you must select a profile above");
        }
        var cloneUrl = $(this).data('url');
        cloneUrl = cloneUrl + profile_id;
        $("#helper-clonedialog-dialog").dialog ({autoOpen:true, modal: true, width:900, closeOnEscape: true,
          title:"Create a new profile from cloning "+ $(selector + " option:selected").text() ,
          buttons: {
            "Create": function() {
              var title = $('#name-clone-dialog').val();
              if (title == '') {
                $().crmNotification ("Please enter a name");
                return;
              }
              $.getJSON(cloneUrl+'&title='+title, function (data) {
                  $('<option value="'+data.id+'" selected="selected">'+title+'</option>').attr('selected','selected').appendTo (selector);
                console.log (data);
              });
              $( this ).dialog( "close" );
            },
            Cancel: function() {
              $( this ).dialog( "close" );
            }
          },
          draggable:true,
          width:250,
          minHeight:150
        });
        return false;
      });

      $('#editdialog').click (function() {
        btn=$(this);
        btn.val ('Loading...');
        var url=btn.data('url');
        var profile_id = $(selector + " option:selected").val();
        if (profile_id < 1)
          $().crmNotification ("you must select a profile above");
        //$("#helper-editdialog-dialog").load ('/civicrm/inline/uf/edit_fields?id='+1,function () {
        $("#helper-editdialog-dialog").load ('/civicrm/admin/uf/group?snippet=1&edit_in_place=1&action=preview&field=0&id='+profile_id,function () {
          btn.val ('Edit');
          $("#helper-editdialog-dialog #crm-container-inner").attr('id','crm-container');
          $("#helper-editdialog-dialog").dialog ({autoOpen:true, modal: true, width:900, closeOnEscape: true,
            close: function () { $(this).remove();},
            draggable:true,
            height:555
          });
        });
        return false;
      });

       
     
        // show edit for profile
     $(selector).after ("<div id='embed_profile'></div>");
     $(selector).change( function( ) {
       $('#editdialog').removeAttr('disabled');
       $('#clonedialog').removeAttr('disabled');
          
/*      $("#fields").html('Loading...');
      $this=$(this).find("option:selected");
      id=$this.val();
      $("#embed_profile").load ('/civicrm/inline/uf/edit_fields?id='+id);
*/
   });

        // show edit links on form loads
//        var profileField =  cj('select[id="profile_id"]'); 
//        buildLinks( profileField, profileField.val()); 
    });
</script>
{/literal}

{else}

{literal}
<script type="text/javascript">
    function buildLinks( element, profileId ) {
      if ( profileId >= 1 ) {
        var ufFieldUrl = {/literal}"{crmURL p='civicrm/admin/uf/group/field' q='reset=1&action=browse&gid=' h=0}"{literal};
        ufFieldUrl = ufFieldUrl + profileId;
        var editTitle = {/literal}"{ts}edit profile{/ts}"{literal};
        element.parent().find('span.profile-links').html('<a href="' + ufFieldUrl +'" target="_blank" title="'+ editTitle+'">'+ editTitle+'</a>');
      } else {
        element.parent().find('span.profile-links').html('');
      }
    }
</script>
{/literal}

{/if}

