<?php

/**
 * Base class for UF system integrations
 */
class CRM_Utils_System_Base {
    var $is_drupal = FALSE;
    var $is_joomla = FALSE;
    /*
     * Does the CMS allow CMS forms to be extended by hooks
     */
    var $supports_form_extensions = FALSE;
  
    function getVersion() {
        return 'Unknown';
    }

    /**
     * Format the url as per language Negotiation.
     * 
     * @param string $url
     *
     * @return string $url, formatted url.
     * @static
     */    
    function languageNegotiationURL( $url, 
                                            $addLanguagePart    = true, 
                                            $removeLanguagePart = false ) 
    {
        return $url;
    }
    
    /*
     * Currently this is just helping out the test class as defaults is calling it - maybe move fix to defaults
     */
    function cmsRootPath( ) 
    {
    
    }
}
