
(function($){ $.fn.toolTip = function(){
  var clickedElement = null;
  return this.each(function() {
    var text = $(this).children().find('div.crm-help').html();
    if(text != undefined) {
      $(this).bind( 'click', function(e){
		$(document).unbind('click');
		$("#crm-toolTip").remove();
		if ( clickedElement == $(this).children().attr('id') ) { clickedElement = null; return; }
		 $("body").append('<div id="crm-toolTip" style="z-index: 100;"><div id="hide-tooltip" class="ui-icon ui-icon-close"></div>' + text + "</div>");
		  $("#crm-toolTip").fadeIn("medium");
		  clickedElement = cj(this).children().attr('id');
	      })
	      .bind( 'mouseout', function() {
			$('#hide-tooltip').click( function() {
			  $("#crm-toolTip").hide();
			  $(document).unbind('click');
			});
	     });
    	}
  	});
}})(jQuery);

