{if $elementIndex}
    {assign var='elementId'   value=$form.$elementName.$elementIndex.id}
    {assign var="timeElement" value=$elementName|cat:"_time.$elementIndex"}
    {$form.$elementName.$elementIndex.html|crmReplace:class:twelve}
{else}
    {assign var='elementId'   value=$form.$elementName.id}
    {assign var="timeElement" value=$elementName|cat:'_time'}
    {$form.$elementName.html|crmReplace:class:twelve}
{/if}
&nbsp;&nbsp;{$form.$timeElement.label}&nbsp;&nbsp;{$form.$timeElement.html|crmReplace:class:six}

<script type="text/javascript">
    var element_date   = "#{$elementId}"; 
    var cal_img        = "{$config->resourceBase}i/cal.gif";    

    {if $timeElement}
        var element_time  = "#{$timeElement}";
        {*var time_img    = "{$config->resourceBase}packages/jquery/css/images/calendar/spinnerDefault.png";*}
        var time_format   = cj( element_time ).attr('timeFormat');
        {literal}
            cj(element_time).timeEntry({ show24Hours : time_format });
        {/literal}
    {/if}

    var date_format = cj( element_date ).attr('format');
    var startYear   = cj( element_date ).attr('startOffset');
    var endYear     = cj( element_date ).attr('endOffset');

    {literal} 
    cj(element_date).datepicker({
                                    showOn            : 'both',
                                    closeAtTop        : true, 
                                    buttonImage       : cal_img, 
                                    buttonImageOnly   : true, 
                                    dateFormat        : date_format,
                                    changeMonth       : true,
                                    changeYear        : true,
                                    yearRange         : '-'+startYear+':+'+endYear
                                });
    
    cj(element_date).click( function( ) {
        hideYear( this );
    });  
    cj('.ui-datepicker-trigger').click( function( ) {
        hideYear( cj(this).prev() );
    });  
    
    function hideYear( element ) {
        var format = cj( element ).attr('format');
        if ( format == 'dd/mm' || format == 'mm/dd' ) {
            cj(".ui-datepicker-year").css( 'display', 'none' );
        }
    }
    {/literal}
</script>


