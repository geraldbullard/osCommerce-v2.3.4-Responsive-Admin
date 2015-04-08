<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require(DIR_WS_INCLUDES . 'template_top.php');
?>
            <div class="row mt10 mb10">
              <div class="col-lg-10 col-md-9 col-sm-8 col-xs-7">
                <h2 class="mt0"><?php echo STORE_NAME; ?></h2>
              </div>
              <div class="col-lg-2 col-md-3 col-sm-4 col-xs-5">
              <?php
                //if (sizeof($languages_array) > 1) {
                echo tep_draw_form('adminlanguage', FILENAME_DEFAULT, '', 'get');
                echo tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onchange="this.form.submit();" class="form-control"');
                echo tep_hide_session_id();
                echo '</form>'; 
                //}
              ?>
              </div>
            </div>
<?php
  if ( defined('MODULE_ADMIN_DASHBOARD_INSTALLED') && tep_not_null(MODULE_ADMIN_DASHBOARD_INSTALLED) ) {
    $adm_array = explode(';', MODULE_ADMIN_DASHBOARD_INSTALLED);
    $col = 0;
    for ( $i=0, $n=sizeof($adm_array); $i<$n; $i++ ) {
      $adm = $adm_array[$i]; 
      $class = substr($adm, 0, strrpos($adm, '.'));
      if ( !class_exists($class) ) {
        include(DIR_WS_LANGUAGES . $language . '/modules/dashboard/' . $adm);
        include(DIR_WS_MODULES . 'dashboard/' . $class . '.php');
      } 
      $ad = new $class();
      if ( $ad->isEnabled() ) {
        if ($col < 1) {
          echo '            <div class="row">' . "\n";
        }
        $col++;
        if ($col <= 2) {
          echo '              <div class="col-lg-6">' . "\n";
        }
        echo $ad->getOutput(); 
        if ($col <= 2) {
          echo '              </div>' . "\n";
        }
        if ( !isset($adm_array[$i+1]) || ($col == 2) ) {
          if ( !isset($adm_array[$i+1]) && ($col == 1) ) {
            echo '            <div class="col-lg-10">&nbsp;</div>' . "\n";
          }
          $col = 0;
          echo '            </div>' . "\n";
        }
      }
    }
  }
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
