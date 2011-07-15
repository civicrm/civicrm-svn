<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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

function civicrm_views_default_views() {
    $views = NULL;
    if (module_exists('date_api') && module_exists('calendar')) {
        $view = new view;
        $view->name = 'civicrm_events';
        $view->description = 'CiviCRM Event Calendar';
        $view->tag = 'CiviEvent';
        $view->base_table = 'civicrm_event';
        $view->human_name = '';
        $view->core = 0;
        $view->api_version = '3.0-alpha1';
        $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */
        
        /* Display: Defaults */
        $handler = $view->new_display('default', 'Defaults', 'default');
        $handler->display->display_options['title'] = 'Events';
        $handler->display->display_options['items_per_page'] = 0;
        $handler->display->display_options['access']['type'] = 'none';
        $handler->display->display_options['cache']['type'] = 'none';
        $handler->display->display_options['query']['type'] = 'views_query';
        $handler->display->display_options['exposed_form']['type'] = 'basic';
        $handler->display->display_options['pager']['type'] = 'full';
        $handler->display->display_options['style_plugin'] = 'calendar_nav';
        /* Field: CiviCRM Events: Start Date */
        $handler->display->display_options['fields']['start_date']['id'] = 'start_date';
        $handler->display->display_options['fields']['start_date']['table'] = 'civicrm_event';
        $handler->display->display_options['fields']['start_date']['field'] = 'start_date';
        $handler->display->display_options['fields']['start_date']['label'] = '';
        $handler->display->display_options['fields']['start_date']['alter']['alter_text'] = 0;
        $handler->display->display_options['fields']['start_date']['alter']['make_link'] = 0;
        $handler->display->display_options['fields']['start_date']['alter']['trim'] = 0;
        $handler->display->display_options['fields']['start_date']['alter']['word_boundary'] = 1;
        $handler->display->display_options['fields']['start_date']['alter']['ellipsis'] = 1;
        $handler->display->display_options['fields']['start_date']['alter']['strip_tags'] = 0;
        $handler->display->display_options['fields']['start_date']['alter']['html'] = 0;
        $handler->display->display_options['fields']['start_date']['date_format'] = 'medium';
        /* Field: CiviCRM Events: Title */
        $handler->display->display_options['fields']['title']['id'] = 'title';
        $handler->display->display_options['fields']['title']['table'] = 'civicrm_event';
        $handler->display->display_options['fields']['title']['field'] = 'title';
        $handler->display->display_options['fields']['title']['label'] = '';
        $handler->display->display_options['fields']['title']['alter']['alter_text'] = 0;
        $handler->display->display_options['fields']['title']['alter']['make_link'] = 0;
        $handler->display->display_options['fields']['title']['alter']['trim'] = 0;
        $handler->display->display_options['fields']['title']['alter']['word_boundary'] = 1;
        $handler->display->display_options['fields']['title']['alter']['ellipsis'] = 1;
        $handler->display->display_options['fields']['title']['alter']['strip_tags'] = 0;
        $handler->display->display_options['fields']['title']['alter']['html'] = 0;
        $handler->display->display_options['fields']['title']['link_to_civicrm_event'] = 'page';
        /* Sort criterion: CiviCRM Events: Start Date */
        $handler->display->display_options['sorts']['start_date']['id'] = 'start_date';
        $handler->display->display_options['sorts']['start_date']['table'] = 'civicrm_event';
        $handler->display->display_options['sorts']['start_date']['field'] = 'start_date';
        /* Contextual filter: Date: Date (civicrm_event) */
        $handler->display->display_options['arguments']['date_argument']['id'] = 'date_argument';
        $handler->display->display_options['arguments']['date_argument']['table'] = 'civicrm_event';
        $handler->display->display_options['arguments']['date_argument']['field'] = 'date_argument';
        $handler->display->display_options['arguments']['date_argument']['default_action'] = 'default';
        $handler->display->display_options['arguments']['date_argument']['exception']['title_enable'] = 1;
        $handler->display->display_options['arguments']['date_argument']['default_argument_type'] = 'date';
        $handler->display->display_options['arguments']['date_argument']['summary']['format'] = 'default_summary';
        $handler->display->display_options['arguments']['date_argument']['specify_validation'] = 1;
        $handler->display->display_options['arguments']['date_argument']['year_range'] = '-3:+3';
        $handler->display->display_options['arguments']['date_argument']['granularity'] = 'month';
        $handler->display->display_options['arguments']['date_argument']['date_fields'] = array(
          'civicrm_event.start_date' => 'civicrm_event.start_date',
        );
        $handler->display->display_options['arguments']['date_argument']['date_method'] = 'OR';
        $handler->display->display_options['arguments']['date_argument']['date_group'] = 'date';
        /* Filter criterion: CiviCRM Events: Is Active */
        $handler->display->display_options['filters']['is_active']['id'] = 'is_active';
        $handler->display->display_options['filters']['is_active']['table'] = 'civicrm_event';
        $handler->display->display_options['filters']['is_active']['field'] = 'is_active';
        $handler->display->display_options['filters']['is_active']['value'] = '1';
        $handler->display->display_options['filters']['is_active']['expose']['operator'] = FALSE;
        /* Filter criterion: CiviCRM Events: Is Public */
        $handler->display->display_options['filters']['is_public']['id'] = 'is_public';
        $handler->display->display_options['filters']['is_public']['table'] = 'civicrm_event';
        $handler->display->display_options['filters']['is_public']['field'] = 'is_public';
        $handler->display->display_options['filters']['is_public']['value'] = '1';
        $handler->display->display_options['filters']['is_public']['expose']['operator'] = FALSE;
        /* Filter criterion: CiviCRM Events: Include Template */
        $handler->display->display_options['filters']['is_template']['id'] = 'is_template';
        $handler->display->display_options['filters']['is_template']['table'] = 'civicrm_event';
        $handler->display->display_options['filters']['is_template']['field'] = 'is_template';
        $handler->display->display_options['filters']['is_template']['value'] = '0';
        $handler->display->display_options['filters']['is_template']['expose']['operator'] = FALSE;
        
        /* Display: Events page */
        $handler = $view->new_display('calendar', 'Events page', 'calendar_1');
        $handler->display->display_options['items_per_page'] = 0;
        $handler->display->display_options['pager']['type'] = 'some';
        $handler->display->display_options['style_plugin'] = 'calendar_nav';
        $handler->display->display_options['defaults']['style_options'] = FALSE;
        $handler->display->display_options['path'] = 'events';
        $handler->display->display_options['calendar_colors'] = array(
          0 => array(),
        );
        
        /* Display: Events block */
        $handler = $view->new_display('calendar_block', 'Events block', 'calendar_block_1');
        $handler->display->display_options['items_per_page'] = 0;
        $handler->display->display_options['pager']['type'] = 'some';
        $handler->display->display_options['style_plugin'] = 'calendar_nav';
        $handler->display->display_options['defaults']['style_options'] = FALSE;
        $handler->display->display_options['block_description'] = 'Events Calendar';
        
        /* Display: Year view */
        $handler = $view->new_display('calendar_period', 'Year view', 'calendar_period_1');
        $handler->display->display_options['defaults']['style_plugin'] = FALSE;
        $handler->display->display_options['style_plugin'] = 'calendar_style';
        $handler->display->display_options['style_options']['name_size'] = '1';
        $handler->display->display_options['style_options']['with_weekno'] = '0';
        $handler->display->display_options['style_options']['multiday_theme'] = '0';
        $handler->display->display_options['style_options']['theme_style'] = '0';
        $handler->display->display_options['style_options']['max_items'] = '0';
        $handler->display->display_options['style_options']['max_items_behavior'] = 'more';
        $handler->display->display_options['style_options']['groupby_times'] = 'hour';
        $handler->display->display_options['style_options']['groupby_times_custom'] = '';
        $handler->display->display_options['style_options']['groupby_field'] = '';
        $handler->display->display_options['defaults']['style_options'] = FALSE;
        $handler->display->display_options['defaults']['row_plugin'] = FALSE;
        $handler->display->display_options['defaults']['row_options'] = FALSE;
        $handler->display->display_options['displays'] = array(
          'calendar_1' => 'calendar_1',
          'default' => 0,
          'calendar_block_1' => 0,
        );
        $handler->display->display_options['attachment_position'] = 'after';
        $handler->display->display_options['inherit_exposed_filters'] = TRUE;
        
        /* Display: Month view */
        $handler = $view->new_display('calendar_period', 'Month view', 'calendar_period_2');
        $handler->display->display_options['defaults']['style_plugin'] = FALSE;
        $handler->display->display_options['style_plugin'] = 'calendar_style';
        $handler->display->display_options['style_options']['name_size'] = '99';
        $handler->display->display_options['style_options']['with_weekno'] = '1';
        $handler->display->display_options['style_options']['multiday_theme'] = '0';
        $handler->display->display_options['style_options']['theme_style'] = '0';
        $handler->display->display_options['style_options']['max_items'] = '0';
        $handler->display->display_options['style_options']['max_items_behavior'] = 'more';
        $handler->display->display_options['style_options']['groupby_times'] = 'hour';
        $handler->display->display_options['style_options']['groupby_times_custom'] = '';
        $handler->display->display_options['style_options']['groupby_field'] = '';
        $handler->display->display_options['defaults']['style_options'] = FALSE;
        $handler->display->display_options['defaults']['row_plugin'] = FALSE;
        $handler->display->display_options['defaults']['row_options'] = FALSE;
        $handler->display->display_options['displays'] = array(
          'calendar_1' => 'calendar_1',
          'default' => 0,
          'calendar_block_1' => 0,
        );
        $handler->display->display_options['attachment_position'] = 'after';
        $handler->display->display_options['inherit_exposed_filters'] = TRUE;
        
        /* Display: Day view */
        $handler = $view->new_display('calendar_period', 'Day view', 'calendar_period_3');
        $handler->display->display_options['defaults']['style_plugin'] = FALSE;
        $handler->display->display_options['style_plugin'] = 'calendar_style';
        $handler->display->display_options['style_options']['name_size'] = '99';
        $handler->display->display_options['style_options']['with_weekno'] = '0';
        $handler->display->display_options['style_options']['multiday_theme'] = '0';
        $handler->display->display_options['style_options']['theme_style'] = '0';
        $handler->display->display_options['style_options']['max_items'] = '0';
        $handler->display->display_options['style_options']['max_items_behavior'] = 'more';
        $handler->display->display_options['style_options']['groupby_times'] = 'hour';
        $handler->display->display_options['style_options']['groupby_times_custom'] = '';
        $handler->display->display_options['style_options']['groupby_field'] = '';
        $handler->display->display_options['defaults']['style_options'] = FALSE;
        $handler->display->display_options['defaults']['row_plugin'] = FALSE;
        $handler->display->display_options['defaults']['row_options'] = FALSE;
        $handler->display->display_options['displays'] = array(
          'calendar_1' => 'calendar_1',
          'default' => 0,
          'calendar_block_1' => 0,
        );
        $handler->display->display_options['attachment_position'] = 'after';
        $handler->display->display_options['inherit_exposed_filters'] = TRUE;
        
        /* Display: Week view */
        $handler = $view->new_display('calendar_period', 'Week view', 'calendar_period_4');
        $handler->display->display_options['defaults']['style_plugin'] = FALSE;
        $handler->display->display_options['style_plugin'] = 'calendar_style';
        $handler->display->display_options['style_options']['name_size'] = '99';
        $handler->display->display_options['style_options']['with_weekno'] = '0';
        $handler->display->display_options['style_options']['multiday_theme'] = '0';
        $handler->display->display_options['style_options']['theme_style'] = '0';
        $handler->display->display_options['style_options']['max_items'] = '0';
        $handler->display->display_options['style_options']['max_items_behavior'] = 'more';
        $handler->display->display_options['style_options']['groupby_times'] = 'hour';
        $handler->display->display_options['style_options']['groupby_times_custom'] = '';
        $handler->display->display_options['style_options']['groupby_field'] = '';
        $handler->display->display_options['defaults']['style_options'] = FALSE;
        $handler->display->display_options['defaults']['row_plugin'] = FALSE;
        $handler->display->display_options['defaults']['row_options'] = FALSE;
        $handler->display->display_options['displays'] = array(
          'calendar_1' => 'calendar_1',
          'default' => 0,
          'calendar_block_1' => 0,
        );
        $handler->display->display_options['attachment_position'] = 'after';
        $handler->display->display_options['inherit_exposed_filters'] = TRUE;
        
        /* Display: Block view */
        $handler = $view->new_display('calendar_period', 'Block view', 'calendar_period_5');
        $handler->display->display_options['defaults']['style_plugin'] = FALSE;
        $handler->display->display_options['style_plugin'] = 'calendar_style';
        $handler->display->display_options['style_options']['name_size'] = '1';
        $handler->display->display_options['style_options']['with_weekno'] = '0';
        $handler->display->display_options['style_options']['multiday_theme'] = '0';
        $handler->display->display_options['style_options']['theme_style'] = '0';
        $handler->display->display_options['style_options']['max_items'] = '0';
        $handler->display->display_options['style_options']['max_items_behavior'] = 'more';
        $handler->display->display_options['style_options']['groupby_times'] = 'hour';
        $handler->display->display_options['style_options']['groupby_times_custom'] = '';
        $handler->display->display_options['style_options']['groupby_field'] = '';
        $handler->display->display_options['defaults']['style_options'] = FALSE;
        $handler->display->display_options['defaults']['row_plugin'] = FALSE;
        $handler->display->display_options['defaults']['row_options'] = FALSE;
        $handler->display->display_options['displays'] = array(
          'calendar_1' => 0,
          'default' => 0,
          'calendar_block_1' => 'calendar_block_1',
        );
        $handler->display->display_options['attachment_position'] = 'after';
        $handler->display->display_options['inherit_exposed_filters'] = TRUE;
        
        /* Display: Upcoming Events */
        $handler = $view->new_display('block', 'Upcoming Events', 'block_1');
        $handler->display->display_options['defaults']['title'] = FALSE;
        $handler->display->display_options['title'] = 'Upcoming Events';
        $handler->display->display_options['defaults']['items_per_page'] = FALSE;
        $handler->display->display_options['defaults']['use_more'] = FALSE;
        $handler->display->display_options['use_more'] = TRUE;
        $handler->display->display_options['defaults']['style_plugin'] = FALSE;
        $handler->display->display_options['style_plugin'] = 'list';
        $handler->display->display_options['defaults']['style_options'] = FALSE;
        $handler->display->display_options['defaults']['row_plugin'] = FALSE;
        $handler->display->display_options['row_plugin'] = 'fields';
        $handler->display->display_options['defaults']['row_options'] = FALSE;
        $handler->display->display_options['defaults']['fields'] = FALSE;
        /* Field: CiviCRM Events: Start Date */
        $handler->display->display_options['fields']['start_date']['id'] = 'start_date';
        $handler->display->display_options['fields']['start_date']['table'] = 'civicrm_event';
        $handler->display->display_options['fields']['start_date']['field'] = 'start_date';
        $handler->display->display_options['fields']['start_date']['label'] = '';
        $handler->display->display_options['fields']['start_date']['alter']['alter_text'] = 0;
        $handler->display->display_options['fields']['start_date']['alter']['make_link'] = 0;
        $handler->display->display_options['fields']['start_date']['alter']['trim'] = 0;
        $handler->display->display_options['fields']['start_date']['alter']['word_boundary'] = 1;
        $handler->display->display_options['fields']['start_date']['alter']['ellipsis'] = 1;
        $handler->display->display_options['fields']['start_date']['alter']['strip_tags'] = 0;
        $handler->display->display_options['fields']['start_date']['alter']['html'] = 0;
        $handler->display->display_options['fields']['start_date']['date_format'] = 'medium';
        /* Field: CiviCRM Events: Title */
        $handler->display->display_options['fields']['title']['id'] = 'title';
        $handler->display->display_options['fields']['title']['table'] = 'civicrm_event';
        $handler->display->display_options['fields']['title']['field'] = 'title';
        $handler->display->display_options['fields']['title']['label'] = '';
        $handler->display->display_options['fields']['title']['alter']['alter_text'] = 0;
        $handler->display->display_options['fields']['title']['alter']['make_link'] = 0;
        $handler->display->display_options['fields']['title']['alter']['trim'] = 0;
        $handler->display->display_options['fields']['title']['alter']['word_boundary'] = 1;
        $handler->display->display_options['fields']['title']['alter']['ellipsis'] = 1;
        $handler->display->display_options['fields']['title']['alter']['strip_tags'] = 0;
        $handler->display->display_options['fields']['title']['alter']['html'] = 0;
        $handler->display->display_options['fields']['title']['link_to_civicrm_event'] = 'page';
        $handler->display->display_options['defaults']['arguments'] = FALSE;
        $handler->display->display_options['defaults']['filters'] = FALSE;
        /* Filter criterion: CiviCRM Events: Is Active */
        $handler->display->display_options['filters']['is_active']['id'] = 'is_active';
        $handler->display->display_options['filters']['is_active']['table'] = 'civicrm_event';
        $handler->display->display_options['filters']['is_active']['field'] = 'is_active';
        $handler->display->display_options['filters']['is_active']['value'] = '1';
        $handler->display->display_options['filters']['is_active']['expose']['operator'] = FALSE;
        /* Filter criterion: CiviCRM Events: Is Public */
        $handler->display->display_options['filters']['is_public']['id'] = 'is_public';
        $handler->display->display_options['filters']['is_public']['table'] = 'civicrm_event';
        $handler->display->display_options['filters']['is_public']['field'] = 'is_public';
        $handler->display->display_options['filters']['is_public']['value'] = '1';
        $handler->display->display_options['filters']['is_public']['expose']['operator'] = FALSE;
        /* Filter criterion: CiviCRM Events: Include Template */
        $handler->display->display_options['filters']['is_template']['id'] = 'is_template';
        $handler->display->display_options['filters']['is_template']['table'] = 'civicrm_event';
        $handler->display->display_options['filters']['is_template']['field'] = 'is_template';
        $handler->display->display_options['filters']['is_template']['value'] = '0';
        $handler->display->display_options['filters']['is_template']['expose']['operator'] = FALSE;
        $handler->display->display_options['block_description'] = 'Upcoming Events';
        $translatables['civicrm_events'] = array(
          t('Defaults'),
          t('Events'),
          t('more'),
          t('Apply'),
          t('Reset'),
          t('Sort by'),
          t('Asc'),
          t('Desc'),
          t('Items per page'),
          t('- All -'),
          t('Offset'),
          t('All'),
          t('Events page'),
          t('Events block'),
          t('Events Calendar'),
          t('Year view'),
          t('Month view'),
          t('Day view'),
          t('Week view'),
          t('Block view'),
          t('Upcoming Events'),
        );

        $views[$view->name] = $view;

    }

    return $views;
}
