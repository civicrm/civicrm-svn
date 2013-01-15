{literal}
<script type="text/javascript">
cj(function() {
  if (cj('#update_modified_date').length == 0) {
    return;
  }
  cj('<button>')
    .text("{/literal}{ts}Ignore{/ts}{literal}")
    .click(function() {
      cj('input[name="modified_date"]').val(
        cj('#update_modified_date').attr('data:latest_modified_date')
      );
      cj('#update_modified_date').parent().hide();
      return false;
    })
    .appendTo(cj('#update_modified_date'))
    ;
  cj('<button>')
    .text("{/literal}{ts}Start Over{/ts}{literal}")
    .click(function() {
      window.location = CRM.url('civicrm/contact/add', {
        reset: 1,
        action: 'update',
        cid: {/literal}{$contactId}{literal}
      });
      return false;
    })
    .appendTo(cj('#update_modified_date'))
    ;
});
</script>
{/literal}