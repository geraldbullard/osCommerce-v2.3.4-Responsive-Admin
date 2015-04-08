<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>
    <div id="footer-wrapper">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-12">
            <div class="footer-text white pt10">  
              <?php if (sizeof($languages_array) > 1) { ?>
              <div class="pull-right ml20">
                <?php
                  echo tep_draw_form('adminlanguage', FILENAME_DEFAULT, '', 'get');
                  echo tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onchange="this.form.submit();" class="form-control w100 mb10"');
                  echo tep_hide_session_id();
                  echo '</form>';                
                ?> 
              </div>
              <?php } ?>
              <div class="pull-right nmt3 mb5">
                osCommerce <span class="red fwbld">Responsive</span> Online Merchant v<?php echo tep_get_version(); ?><br>
                Copyright &copy; 2000-<?php echo date('Y'); ?> <a href="http://www.oscommerce.com" target="_blank"><strong>osCommerce</strong></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
        