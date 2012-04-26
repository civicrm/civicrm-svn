<!-- FIXME: CSS conventions and polish -->
<div class="crm-block crm-form-block crm-queue-runner-form-block">
  <div id="crm-queue-runner-progress"></div>
  <div id="crm-queue-runner-desc">
    <div id="crm-queue-runner-buttonset" style="right:20px;position:absolute;">
      <button id="crm-queue-runner-retry">Retry</button>
      <button id="crm-queue-runner-skip">Skip</button>
    </div>
    <div>[<span id="crm-queue-runner-title"></span>]</div>
  </div>
  <div id="crm-queue-runner-message"></div>
</div>

{literal}
<script type="text/javascript">

cj(function() {
  // Note: Queue API provides "#remaining tasks" but not "#completed tasks" or "#total tasks".
  // To compute a %complete, we manually track #completed. This only works nicely if we
  // assume that the queue began with a fixed #tasks.
  
  var queueRunnerData = {/literal}{$queueRunnerData|@json}{literal};

  var displayResponseData = function(data, textStatus, jqXHR) {
    // console.log(data);

    if (!data.is_error) {
      queueRunnerData.completed++;
      queueRunnerData.numberOfItems = parseInt(data.numberOfItems);
    }
    var pct = 100 * queueRunnerData.completed / (queueRunnerData.completed + queueRunnerData.numberOfItems);
    cj("#crm-queue-runner-progress").progressbar({ value: pct });
    // console.log('comp='+queueRunnerData.completed + ' rem='+queueRunnerData.numberOfItems + ' pct='+pct); // REMOVE
        
    if (data.is_error) {
      cj("#crm-queue-runner-buttonset").show();
      cj('#crm-queue-runner-title').text('Error: ' + data.last_task_title);
    } else {
      cj('#crm-queue-runner-title').text('Executed: ' + data.last_task_title);
    }
    if (data.message) {
      cj('#crm-queue-runner-message').html('');
      cj('<pre></pre>').text(data.message).prependTo('#crm-queue-runner-message');
    }
    if (data.is_continue) {
      window.setTimeout(runNext, 50);
    }
  };
  
  var displayError = function(jqXHR, textStatus, errorThrown) {
    // Do this regardless of whether the response was well-formed
    cj("#crm-queue-runner-buttonset").show();
        
    var data = cj.parseJSON(jqXHR.responseText)
    if (data) {
      displayResponseData(data);
    }
  };
  
  // Dequeue and execute the next item
  var runNext = function() {
    cj.ajax({
      type: 'POST',
      url: queueRunnerData.runNextUrl,
      data: {
        qrid: queueRunnerData.qrid
      },
      dataType: 'json',
      beforeSend: function(jqXHR, settings) {
          cj("#crm-queue-runner-buttonset").hide();
      },
      error: displayError,
      success: displayResponseData
    });
  }
  
  var retryNext = function() {
    cj('#crm-queue-runner-message').html('');
    runNext();
  }
  
  // Dequeue and the next item, then move on to runNext for the subsequent items
  var skipNext = function() {
    cj.ajax({
      type: 'POST',
      url: queueRunnerData.skipNextUrl,
      data: {
        qrid: queueRunnerData.qrid
      },
      dataType: 'json',
      beforeSend: function(jqXHR, settings) {
        cj('#crm-queue-runner-message').html('');
          cj("#crm-queue-runner-buttonset").hide();
      },
      error: displayError,
      success: displayResponseData
    });
  }
  
  // Set up the UI
  
  cj("#crm-queue-runner-progress").progressbar({ value: 0 });
  cj("#crm-queue-runner-retry").button({
    text: false,
    icons: {primary: 'ui-icon-refresh'}
  }).click(retryNext);
  cj("#crm-queue-runner-skip").button({
    text: false,
    icons: {primary: 'ui-icon-seek-next'}
  }).click(skipNext);
  cj("#crm-queue-runner-buttonset").buttonset();
  cj("#crm-queue-runner-buttonset").hide();
  window.setTimeout(runNext, 50);
});

</script>
{/literal}