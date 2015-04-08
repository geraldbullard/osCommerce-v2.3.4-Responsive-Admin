<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  $login_request = true;

  require('includes/application_top.php');
  require('includes/functions/password_funcs.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  // prepare to logout an active administrator if the login page is accessed again
  if (isset($_SESSION['admin'])) {
    $action = 'logoff';
  }

  if (tep_not_null($action)) {
    switch ($action) {
      case 'process':
        if (isset($_SESSION['redirect_origin']) && isset($redirect_origin['auth_user']) && !isset($_POST['username'])) {
          $username = tep_db_prepare_input($redirect_origin['auth_user']);
          $password = tep_db_prepare_input($redirect_origin['auth_pw']);
        } else {
          $username = tep_db_prepare_input($_POST['username']);
          $password = tep_db_prepare_input($_POST['password']);
        }

        $actionRecorder = new actionRecorderAdmin('ar_admin_login', null, $username);

        if ($actionRecorder->canPerform()) {
          $check_query = tep_db_query("select id, user_name, user_password from " . TABLE_ADMINISTRATORS . " where user_name = '" . tep_db_input($username) . "'");

          if (tep_db_num_rows($check_query) == 1) {
            $check = tep_db_fetch_array($check_query);

            if (tep_validate_password($password, $check['user_password'])) {
              // migrate old hashed password to new phpass password
              if (tep_password_type($check['user_password']) != 'phpass') {
                tep_db_query("update " . TABLE_ADMINISTRATORS . " set user_password = '" . tep_encrypt_password($password) . "' where id = '" . (int)$check['id'] . "'");
              }

              tep_session_register('admin');

              $admin = array('id' => $check['id'],
                             'username' => $check['user_name']);

              $actionRecorder->_user_id = $admin['id'];
              $actionRecorder->record();

              if (isset($_SESSION['redirect_origin'])) {
                $page = $redirect_origin['page'];
                $get_string = '';

                if (function_exists('http_build_query')) {
                  $get_string = http_build_query($redirect_origin['get']);
                }

                unset($_SESSION['redirect_origin']);

                tep_redirect(tep_href_link($page, $get_string));
              } else {
                tep_redirect(tep_href_link(FILENAME_DEFAULT));
              }
            }
          }

          if (isset($_POST['username'])) {
            $messageStack->add(ERROR_INVALID_ADMINISTRATOR, 'error');
          }
        } else {
          $messageStack->add(sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES') ? (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES : 5)));
        }

        if (isset($_POST['username'])) {
          $actionRecorder->record(false);
        }

        break;

      case 'logoff':
        unset($_SESSION['admin']);

        if (isset($HTTP_SERVER_VARS['PHP_AUTH_USER']) && !empty($HTTP_SERVER_VARS['PHP_AUTH_USER']) && isset($HTTP_SERVER_VARS['PHP_AUTH_PW']) && !empty($HTTP_SERVER_VARS['PHP_AUTH_PW'])) {
          tep_session_register('auth_ignore');
          $auth_ignore = true;
        }

        tep_redirect(tep_href_link(FILENAME_DEFAULT));

        break;

      case 'create':
        $check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " limit 1");

        if (tep_db_num_rows($check_query) == 0) {
          $username = tep_db_prepare_input($_POST['username']);
          $password = tep_db_prepare_input($_POST['password']);

          if ( !empty($username) ) {
            tep_db_query("insert into " . TABLE_ADMINISTRATORS . " (user_name, user_password) values ('" . tep_db_input($username) . "', '" . tep_db_input(tep_encrypt_password($password)) . "')");
          }
        }

        tep_redirect(tep_href_link(FILENAME_LOGIN));

        break;
    }
  }

  $languages = tep_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $language) {
      $languages_selected = $languages[$i]['code'];
    }
  }

  $admins_check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " limit 1");
  if (tep_db_num_rows($admins_check_query) < 1) {
    $messageStack->add(TEXT_CREATE_FIRST_ADMINISTRATOR, 'warning');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>
              <div class="row">
                <div class="col-lg-4 col-md-4"></div>
                <div class="col-lg-4 col-md-4 mb15 mt30">
                  <p class="text-center mt15"><img border="0" src="images/oscommerce.png" alt="Extreme osC v" title="Extreme osC v<?php echo tep_get_version(); ?>"></p>
                  <div class="panel panel-primary mt15">
                    <div class="panel-heading">
                      <h3 class="panel-title"><?php echo HEADING_TITLE; ?></h3>
                    </div>
                    <div class="panel-body">
                      <?php
                        if (tep_db_num_rows($admins_check_query) > 0) { 
                          if (sizeof($languages_array) > 1) { 
                      ?>
                      <div class="form-group">
                        <label><?php echo BOX_LOCALIZATION_LANGUAGES; ?></label>
                        <?php
                          echo tep_draw_form('adminlanguage', FILENAME_DEFAULT, '', 'get');
                          echo tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'class="form-control" onchange="this.form.submit();"') . tep_hide_session_id();
                          echo '</form>'; 
                        ?>
                      </div>
                      <?php                        
                          }
                          echo tep_draw_form('login', FILENAME_LOGIN, 'action=process');
                      ?>
                      <div class="form-group">
                        <label><?php echo TEXT_USERNAME; ?></label>
                        <?php echo tep_draw_input_field('username', '', 'class="form-control"'); ?>
                      </div>
                      <div class="form-group">
                        <label><?php echo TEXT_PASSWORD; ?></label>
                        <?php echo tep_draw_password_field('password'); ?>
                      </div>
                      <div class="text-center">
                        <button class="btn btn-success" type="submit"><i class="fa fa-key"></i> <?php echo BUTTON_LOGIN; ?></button>
                      </div>
                      <?php 
                          echo '</form>';
                        } else {
                          echo tep_draw_form('login', FILENAME_LOGIN, 'action=create');
                      ?>
                      <p><?php echo TEXT_CREATE_FIRST_ADMINISTRATOR; ?></p>
                      <div class="form-group">
                        <label><?php echo TEXT_USERNAME; ?></label>
                        <?php echo tep_draw_input_field('username', '', 'class="form-control"'); ?>
                      </div>
                      <div class="form-group">
                        <label><?php echo TEXT_PASSWORD; ?></label>
                        <?php echo tep_draw_password_field('password'); ?>
                      </div>
                      <div class="text-center">
                        <button class="btn btn-success" type="submit"><i class="fa fa-plus"></i> <?php echo BUTTON_CREATE_ADMINISTRATOR; ?></button>
                      </div>
                      <?php 
                          echo '</form>';
                        }
                      ?>
                    </div>
                  </div>
                </div>
              <div class="col-lg-4 col-md-4"></div>
              <?php
                require(DIR_WS_INCLUDES . 'template_bottom.php');
                require(DIR_WS_INCLUDES . 'application_bottom.php');
              ?>
