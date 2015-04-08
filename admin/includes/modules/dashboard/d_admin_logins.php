<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class d_admin_logins {
    var $code = 'd_admin_logins';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_admin_logins() {
      $this->title = MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_STATUS == 'True');
      }
    }

    function getOutput() {
      $output = '<div class="table-responsive brtr3 brtl3">' .
                '  <table class="table table-hover table-striped bds1 bdsilver">' .
                '    <thead class="bglightgrey">' .
                '      <tr>' .
                '        <th class="w20 hide-below-768">&nbsp;</th>' .
                '        <th>' . MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_TITLE . '</th>' .
                '        <th class="text-right width-action-below-768">' . MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_DATE . '</th>' .
                '      </tr>' .
                '    </thead>' .
                '    <tbody>';

      $logins_query = tep_db_query("select id, user_name, success, date_added from " . TABLE_ACTION_RECORDER . " where module = 'ar_admin_login' order by date_added desc limit 6");
      while ($logins = tep_db_fetch_array($logins_query)) {
        $output .= '      <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
                   '        <td class="text-center w20 hide-below-768">' . tep_image(DIR_WS_IMAGES . 'icons/' . (($logins['success'] == '1') ? 'tick.gif' : 'cross.gif')) . '</td>' .
                   '        <td class="truncate-below-768"><a href="' . tep_href_link(FILENAME_ACTION_RECORDER, 'module=ar_admin_login&aID=' . (int)$logins['id']) . '">' . tep_output_string_protected($logins['user_name']) . '</a></td>' .
                   '        <td class="text-right width-action-below-768">' . tep_date_short($logins['date_added']) . '</td>' .
                   '      </tr>';
      }

      $output .= '    </tbody>' . 
                 '  </table>' . 
                 '</div>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Administrator Logins Module', 'MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_STATUS', 'True', 'Do you want to show the latest administrator logins on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_STATUS', 'MODULE_ADMIN_DASHBOARD_ADMIN_LOGINS_SORT_ORDER');
    }
  }
?>
