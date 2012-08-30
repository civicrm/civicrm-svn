{if $modified_date}
<div class="messages crm-error" id='update_modified_date' data:latest_modified_date='{$modified_date}'>
   <div class="crm-submit-buttons">
     {$form.qf_Ignore.html}&nbsp;{$form.qf_StartOver.html}
   </div>
</div>

  {literal}
  <script type="text/javascript">
  cj(function() {
    if (cj('#update_modified_date').length == 0) {
      return;
    }
    
    cj('#qf_Ignore').click(function() {
      cj('input[name="modified_date"]').val(
        cj('#update_modified_date').attr('data:latest_modified_date')
      );
      cj('#update_modified_date').hide();
      return false;
    });
    
    cj('#qf_StartOver').click(function() {
      window.location = cj.crmURL('civicrm/contact/add', {
        reset: 1,
        action: 'update',
        cid: {/literal}{$contactId}{literal}
      });
      return false;
    });
  });
  </script>
  {/literal}
{/if}
