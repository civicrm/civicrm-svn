{literal}
cj('#sort_name_navigation').crmAutocomplete({}, {
  result:function(data){
    document.location="{/literal}{crmURL p='civicrm/contact/view' h=0 q='reset=1&cid='}{literal}"+data.id;
  }
});
{/literal}
