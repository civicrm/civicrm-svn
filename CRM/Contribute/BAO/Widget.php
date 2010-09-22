<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */
/**
 * Class to retrieve information about a contribution page
 */

require_once 'CRM/Contribute/DAO/Widget.php';

class CRM_Contribute_BAO_Widget extends CRM_Contribute_DAO_Widget {

    /**
	 * Gets all campaign related data and returns it as a std class.
	 *
	 * @param int $contributionPageID
	 * @param string $widgetID
	 * @return stdClass
	 */
	public function getContributionPageData( $contributionPageID, $widgetID ) {
        $config = CRM_Core_Config::singleton( );      

        $data = array( );
        $data['currencySymbol'] = $config->defaultCurrencySymbol;

        if ( empty( $contributionPageID ) ||
             CRM_Utils_Type::validate( $contributionPageID, 'Integer' ) == null ) {
            $data['is_error'] = true;
            CRM_Core_Error::debug_log_message( "$contributionPageID is not set" );
            return $data;
        }

        require_once 'CRM/Contribute/DAO/Widget.php';
        $widget = new CRM_Contribute_DAO_Widget( );
        $widget->contribution_page_id = $contributionPageID;
        if ( ! $widget->find( true ) ) {
            $data['is_error'] = true;
            CRM_Core_Error::debug_log_message( "$contributionPageID is not found" );
            return $data;
        }

        $data['is_error'] = false;
        if ( ! $widget->is_active ) {
            $data['is_active'] = false;
        }

        $data['is_active'   ] = true;
        $data['title'       ] = $widget->title;
        $data['logo'        ] = $widget->url_logo;
        $data['button_title'] = $widget->button_title;
        $data['about'       ] = $widget->about;

        $query = "
SELECT count( id ) as count,
       sum( total_amount) as amount
FROM   civicrm_contribution
WHERE  is_test = 0
AND    contribution_status_id = 1
AND    contribution_page_id = %1";
        $params = array( 1 => array( $contributionPageID, 'Integer' ) ) ;
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
        if ( $dao->fetch( ) ) {
            $data['num_donors'  ] = (int)$dao->count;
            $data['money_raised'] = (int)$dao->amount;
        } else {
            $data['num_donors'  ] = $data['money_raised'] = $data->money_raised = 0;
        }

        $query = "
SELECT goal_amount, start_date, end_date, is_active
FROM   civicrm_contribution_page
WHERE  id = %1";
        $params = array( 1 => array( $contributionPageID, 'Integer' ) ) ;
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
        if ( $dao->fetch( ) ) {
            require_once 'CRM/Utils/Date.php';
            $data['money_target'  ] = (int)$dao->goal_amount;
            $data['campaign_start'] = CRM_Utils_Date::customFormat( $dao->start_date, $config->dateformatFull );
            $data['campaign_end'  ] = CRM_Utils_Date::customFormat( $dao->end_date  , $config->dateformatFull );

            // check for time being between start and end date
            $now = time( );
            if ( $dao->start_date ) {
                $startDate = CRM_Utils_Date::unixTime( $dao->start_date );
                if ( $startDate &&
                     $startDate >= $now ) {
                    $data['is_active'] = false;
                }
            }

            if ( $dao->end_date ) {
                $endDate = CRM_Utils_Date::unixTime( $dao->end_date );
                if ( $endDate &&
                     $endDate < $now ) {
                    $data['is_active'] = false;
                }
            }
        } else {
            $data['is_active'] = false;
        }

        $data['money_low' ] = 0;
        $data['num_donors'] = $data['num_donors'  ] ." " .ts( 'Donors' );

        // if is_active is false, show this link and hide the contribute button
        $data['homepage_link'] = $widget->url_homepage;

        $data['colors'] = array( );

        $data['colors']["title"]     = $widget->color_title;
        $data['colors']["button"]    = $widget->color_button;
        $data['colors']["bar"]       = $widget->color_bar;
        $data['colors']["main_text"] = $widget->color_main_text;
        $data['colors']["main"]      = $widget->color_main;
        $data['colors']["main_bg"]   = $widget->color_main_bg;
        $data['colors']["bg"]        = $widget->color_bg;

        // these two have colors as normal hex format
        // because they're being used in a CSS object
        $data['colors']["about_link"]    = $widget->color_about_link;
        $data['colors']["homepage_link"] = $widget->color_homepage_link;

        require_once 'CRM/Core/Error.php';
        return $data;
	}
}