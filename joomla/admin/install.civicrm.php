<?php

defined('_JEXEC') or die('No direct access allowed'); 

function com_install() {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'configure.php';
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'civicrm'. DIRECTORY_SEPARATOR .'CRM' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'System.php';

    global $civicrmUpgrade;
    
    $liveSite      = substr_replace(JURI::root(), '', -1, 1);
    $configTaskUrl = $liveSite . '/administrator/index2.php?option=com_civicrm&task=civicrm/admin/configtask&reset=1';
    $upgradeUrl    = $liveSite . '/administrator/index2.php?option=com_civicrm&task=civicrm/upgrade&reset=1';

    if ( $civicrmUpgrade ) {
        $docLink = CRM_Utils_System::docURL2( 'Installation and Upgrades', true, 'Upgrade Guide' );    
        // UPGRADE successful status and links
        $content = '
  <center>
  <table width="100%" border="0">
    <tr>
        <td>
            <strong>CiviCRM component files have been UPGRADED <font color="green">succesfully</font></strong>.
            <p><strong>Please run the <a href="' . $upgradeUrl . '">CiviCRM Database Upgrade Utility</a> now. This utility will check your database and perform any needed upgrades.</strong></p>
            <p>Also review the <a href="'. $docLink .'">Upgrade Guide</a> for any additional steps required to complete this upgrade.</p>
        </td>
    </tr>
  </table>
  </center>';

    } else {
        $docLink = CRM_Utils_System::docURL2( 'Installation and Upgrades', false, 'Installation Guide' );
        
        $frontEnd = CRM_Utils_System::docURL2( 'Configuring Front-end Profile Listings and Forms in Joomla! Sites', false, 'Create front-end forms and searchable directories using Profiles' );
        $contri   = CRM_Utils_System::docURL2( 'Displaying Online Contribution Pages in Joomla! Frontend Sites', false, 'Create online contribution pages' );
        $event    = CRM_Utils_System::docURL2( 'Configuring Front-end Event Info and Registration in Joomla! Sites', false, 'Create events with online event registration' );
        
        // INSTALL successful status and links
        $content = '
  <center>
  <table width="100%" border="0">
    <tr>
        <td>
            <strong>CiviCRM component files and database tables have been INSTALLED <font color="green">succesfully</font></strong>.
            <p><strong>Please review the '. $docLink .' for any additional steps required to complete the installation.</strong></p>
            <p><strong>Then use the <a href="' . $configTaskUrl . '">Configuration Checklist</a> to review and configure CiviCRM settings for your new site.</strong></p>
            <p><strong>Additional Resources:</strong>
                <ul>
                    <li>' . $frontEnd . '</li>
                    <li>' . $contri . '</li>
                    <li>' . $event. '</li>
                </ul>
            </p>
        </td>
    </tr>
  </table>
  </center>';
    }
    
    echo $content;
}