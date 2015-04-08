<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $htaccess_array = null;
  $htpasswd_array = null;
  $is_iis = stripos($HTTP_SERVER_VARS['SERVER_SOFTWARE'], 'iis');

  $authuserfile_array = array('##### OSCOMMERCE ADMIN PROTECTION - BEGIN #####',
                              'AuthType Basic',
                              'AuthName "osCommerce Online Merchant Administration Tool"',
                              'AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce',
                              'Require valid-user',
                              '##### OSCOMMERCE ADMIN PROTECTION - END #####');

  if (!$is_iis && file_exists(DIR_FS_ADMIN . '.htpasswd_oscommerce') && tep_is_writable(DIR_FS_ADMIN . '.htpasswd_oscommerce') && file_exists(DIR_FS_ADMIN . '.htaccess') && tep_is_writable(DIR_FS_ADMIN . '.htaccess')) {
    $htaccess_array = array();
    $htpasswd_array = array();

    if (filesize(DIR_FS_ADMIN . '.htaccess') > 0) {
      $fg = fopen(DIR_FS_ADMIN . '.htaccess', 'rb');
      $data = fread($fg, filesize(DIR_FS_ADMIN . '.htaccess'));
      fclose($fg);

      $htaccess_array = explode("\n", $data);
    }

    if (filesize(DIR_FS_ADMIN . '.htpasswd_oscommerce') > 0) {
      $fg = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'rb');
      $data = fread($fg, filesize(DIR_FS_ADMIN . '.htpasswd_oscommerce'));
      fclose($fg);

      $htpasswd_array = explode("\n", $data);
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        require('includes/functions/password_funcs.php');

        $username = tep_db_prepare_input($_POST['username']);
        $password = tep_db_prepare_input($_POST['password']);

        $check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " where user_name = '" . tep_db_input($username) . "' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          tep_db_query("insert into " . TABLE_ADMINISTRATORS . " (user_name, user_password) values ('" . tep_db_input($username) . "', '" . tep_db_input(tep_encrypt_password($password)) . "')");

          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }

            if (isset($_POST['htaccess']) && ($_POST['htaccess'] == 'true')) {
              $htpasswd_array[] = $username . ':' . tep_crypt_apr_md5($password);
            }

            $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
            fwrite($fp, implode("\n", $htpasswd_array));
            fclose($fp);

            if (!in_array('AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce', $htaccess_array) && !empty($htpasswd_array)) {
              array_splice($htaccess_array, sizeof($htaccess_array), 0, $authuserfile_array);
            } elseif (empty($htpasswd_array)) {
              for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
                if (in_array($htaccess_array[$i], $authuserfile_array)) {
                  unset($htaccess_array[$i]);
                }
              }
            }

            $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
            fwrite($fp, implode("\n", $htaccess_array));
            fclose($fp);
          }
          $messageStack->add_session(SUCCESS_ADMINISTRATOR_INSERT, 'success');
        } else {
          $messageStack->add_session(ERROR_ADMINISTRATOR_EXISTS, 'error');
        }

        tep_redirect(tep_href_link(FILENAME_ADMINISTRATORS));
        break;
      case 'save':
        require('includes/functions/password_funcs.php');

        $username = tep_db_prepare_input($_POST['username']);
        $password = tep_db_prepare_input($_POST['password']);

        $check_query = tep_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$_GET['aID'] . "'");
        $check = tep_db_fetch_array($check_query);

        // update username in current session if changed
        if ( ($check['id'] == $admin['id']) && ($check['user_name'] != $admin['username']) ) {
          $admin['username'] = $username;
        }

        // update username in htpasswd if changed
        if (is_array($htpasswd_array)) {
          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

            if ( ($check['user_name'] == $ht_username) && ($check['user_name'] != $username) ) {
              $htpasswd_array[$i] = $username . ':' . $ht_password;
            }
          }
        }

        tep_db_query("update " . TABLE_ADMINISTRATORS . " set user_name = '" . tep_db_input($username) . "' where id = '" . (int)$_GET['aID'] . "'");

        if (tep_not_null($password)) {
          // update password in htpasswd
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }

            if (isset($_POST['htaccess']) && ($_POST['htaccess'] == 'true')) {
              $htpasswd_array[] = $username . ':' . tep_crypt_apr_md5($password);
            }
          }

          tep_db_query("update " . TABLE_ADMINISTRATORS . " set user_password = '" . tep_db_input(tep_encrypt_password($password)) . "' where id = '" . (int)$_GET['aID'] . "'");
        } elseif (!isset($_POST['htaccess']) || ($_POST['htaccess'] != 'true')) {
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }
          }
        }

        // write new htpasswd file
        if (is_array($htpasswd_array)) {
          $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
          fwrite($fp, implode("\n", $htpasswd_array));
          fclose($fp);

          if (!in_array('AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce', $htaccess_array) && !empty($htpasswd_array)) {
            array_splice($htaccess_array, sizeof($htaccess_array), 0, $authuserfile_array);
          } elseif (empty($htpasswd_array)) {
            for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
              if (in_array($htaccess_array[$i], $authuserfile_array)) {
                unset($htaccess_array[$i]);
              }
            }
          }

          $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
          fwrite($fp, implode("\n", $htaccess_array));
          fclose($fp);
        }

        tep_redirect(tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . (int)$_GET['aID']));
        break;
      case 'deleteconfirm':
        $id = tep_db_prepare_input($_GET['aID']);

        $check_query = tep_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$id . "'");
        $check = tep_db_fetch_array($check_query);

        if ($admin['id'] == $check['id']) {
          unset($_SESSION['admin']);
        }

        tep_db_query("delete from " . TABLE_ADMINISTRATORS . " where id = '" . (int)$id . "'");

        if (is_array($htpasswd_array)) {
          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

            if ($ht_username == $check['user_name']) {
              unset($htpasswd_array[$i]);
            }
          }

          $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
          fwrite($fp, implode("\n", $htpasswd_array));
          fclose($fp);

          if (empty($htpasswd_array)) {
            for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
              if (in_array($htaccess_array[$i], $authuserfile_array)) {
                unset($htaccess_array[$i]);
              }
            }

            $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
            fwrite($fp, implode("\n", $htaccess_array));
            fclose($fp);
          }
        }

        tep_redirect(tep_href_link(FILENAME_ADMINISTRATORS));
        break;
    }
  }

  $secMessageStack = new messageStack(); 

  if (is_array($htpasswd_array)) {
    if (empty($htpasswd_array)) {
      $secMessageStack->add(sprintf(HTPASSWD_INFO, implode('<br />', $authuserfile_array)), 'error');
    } else {
      $secMessageStack->add(HTPASSWD_SECURED, 'success');
    }
  } else if (!$is_iis) {
    $secMessageStack->add(HTPASSWD_PERMISSIONS, 'error');
  }
  
  require(DIR_WS_INCLUDES . 'template_top.php');
?>                                                                            
            <div class="row mt10 mb10">
              <div class="col-lg-10 col-md-9 col-sm-8 col-xs-7">
                <h2 class="mt0"><?php echo HEADING_TITLE; ?></h2>
              </div>
              <div class="col-lg-2 col-md-3 col-sm-4 col-xs-5">
                <?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-8">
                <div class="table-responsive brtr3 brtl3">
                  <table class="table table-hover table-striped bds1 bdsilver">
                    <thead class="bgsilver">
                      <tr>
                        <th><?php echo TABLE_HEADING_ADMINISTRATORS; ?></th>
                        <th class="text-center hide-below-480"><?php echo TABLE_HEADING_HTPASSWD; ?></th>
                        <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $admins_query = tep_db_query("select id, user_name from " . TABLE_ADMINISTRATORS . " order by user_name");
                        while ($admins = tep_db_fetch_array($admins_query)) {
                          if ((!isset($_GET['aID']) || (isset($_GET['aID']) && ($_GET['aID'] == $admins['id']))) && !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
                            $aInfo = new objectInfo($admins);
                          }


                          $htpasswd_secured = '<i class="fa fa-square red"></i>';

                          if ($is_iis) {
                            $htpasswd_secured = 'N/A';
                          }

                          if (is_array($htpasswd_array)) {
                            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
                              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

                              if ($ht_username == $admins['user_name']) {
                                $htpasswd_secured = '<i class="fa fa-check-square green"></i>';
                                break;
                              }
                            }
                          }

                          if ( (isset($aInfo) && is_object($aInfo)) && ($admins['id'] == $aInfo->id) ) {
                            echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=edit') . '\'">' . "\n";
                          } else {
                            echo '                  <tr onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $admins['id']) . '\'">' . "\n";
                          }
                        ?>
                        <td><i class="fa fa-user fs16i mr5"></i><?php echo $admins['user_name']; ?></td>
                        <td class="text-center hide-below-480"><?php echo $htpasswd_secured; ?></td>
                        <td class="text-right">
                        <?php 
                          if ( (isset($aInfo) && is_object($aInfo)) && ($admins['id'] == $aInfo->id) ) { 
                            echo '<i class="fa fa-hand-o-right blue fs16i"></i>'; 
                          } else { 
                            echo '<a href="' . tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $admins['id']) . '"><i class="fa fa-info-circle blue fs16i"></i></a>'; 
                          } ?>
                          </td>
                      </tr>
                      <?php
                        }
                      ?>
                      <tr>
                        <td colspan="3" class="text-right">
                          <button class="btn btn-primary" type="button" onclick="location.href='<?php echo tep_href_link(FILENAME_ADMINISTRATORS, 'action=new'); ?>'"><i class="fa fa-plus mr5"></i><?php echo IMAGE_INSERT; ?></button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>              
              <div class="col-lg-4">
                <div class="panel panel-primary">
                  <div class="panel-heading">
                    <h3 class="panel-title">
                      <?php
                        switch ($action) {
                          case 'new':
                            echo TEXT_INFO_HEADING_NEW_ADMINISTRATOR;
                            break;
                          case 'edit':
                            echo $aInfo->user_name; 
                            break;
                          case 'delete':
                            echo $aInfo->user_name;
                            break;
                          default:
                            echo $aInfo->user_name;
                            break;
                        }
                      ?>
                    </h3>
                  </div>
                  <div class="panel-body">
                  <?php
                    switch ($action) {
                      case 'new':
                        echo tep_draw_form('administrator', FILENAME_ADMINISTRATORS, 'action=insert', 'post', 'autocomplete="off"');
                        echo '  <p>' . TEXT_INFO_INSERT_INTRO . '</p>';
                        echo '  <div class="form-group">';
                        echo '    <label>' . TEXT_INFO_USERNAME . '</label>' . tep_draw_input_field('username', '', 'class="form-control mt10"');
                        echo '  </div>';
                        echo '  <div class="form-group">';
                        echo '    <label>' . TEXT_INFO_PASSWORD . '</label>' . tep_draw_password_field('password');
                        echo '  </div>';
                        
                        if (is_array($htpasswd_array)) {
                          echo '  <div class="form-group">
                                    <div class="checkbox">
                                      <label>
                                        ' . tep_draw_checkbox_field('htaccess', 'true') . ' ' . TEXT_INFO_PROTECT_WITH_HTPASSWD . '
                                      </label>
                                    </div>
                                  </div>';
                        }

                        echo '  <p class="text-center">';
                        echo '    <button class="btn btn-success mr15" type="submit"><i class="fa fa-save mr5"></i>' . IMAGE_SAVE . '</button>';
                        echo '    <button class="btn btn-default" type="button" onclick="location.href=\'' . tep_href_link(FILENAME_ADMINISTRATORS) . '\'"><i class="fa fa-ban mr5"></i>' . IMAGE_CANCEL . '</button>';
                        echo '  </p>';
                        echo '</form>';
                        break;
                      case 'edit':
                        echo tep_draw_form('administrator', FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=save', 'post', 'autocomplete="off"');
                        echo '  <p>' . TEXT_INFO_EDIT_INTRO . '</p>';
                        echo '  <div class="form-group">';
                        echo '    <label>' . TEXT_INFO_USERNAME . '</label>' . tep_draw_input_field('username', $aInfo->user_name, 'class="form-control mt10"');
                        echo '  </div>';
                        echo '  <div class="form-group">';
                        echo '    <label>' . TEXT_INFO_PASSWORD . '</label>' . tep_draw_password_field('password');
                        echo '  </div>';
                        
                        if (is_array($htpasswd_array)) {
                          $default_flag = false;

                          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
                            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

                            if ($ht_username == $aInfo->user_name) {
                              $default_flag = true;
                              break;
                            }
                          }
                          echo '  <div class="form-group">
                                    <div class="checkbox">
                                      <label>
                                        ' . tep_draw_checkbox_field('htaccess', 'true', $default_flag) . ' ' . TEXT_INFO_PROTECT_WITH_HTPASSWD . '
                                      </label>
                                    </div>
                                  </div>';
                        }
                        echo '  <p class="text-center">';
                        echo '    <button class="btn btn-success mr15" type="submit"><i class="fa fa-save mr5"></i> ' . IMAGE_SAVE . '</button>';
                        echo '    <button class="btn btn-default" type="button" onclick="location.href=\'' . tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id) . '\'"><i class="fa fa-ban mr5"></i>' . IMAGE_CANCEL . '</button>';
                        echo '  </p>';
                        echo '</form>';
                        break;
                      case 'delete':
                        echo tep_draw_form('administrator', FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=deleteconfirm');
                        echo '<p>' . TEXT_INFO_DELETE_INTRO . '</p>';
                        echo '<p class="text-center">';
                        echo '  <button class="btn btn-danger mr15" type="submit"><i class="fa fa-trash mr5"></i>' . IMAGE_DELETE . '</button>';
                        echo '  <button class="btn btn-default" type="button" onclick="location.href=\'' . tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id) . '\'"><i class="fa fa-ban mr5"></i>' . IMAGE_CANCEL . '</button>';
                        echo '</form>';
                        break;
                      default:
                        if (isset($aInfo) && is_object($aInfo)) {
                          echo '<p class="text-center mb0">';
                          echo '  <button class="btn btn-primary mr15" type="button" onclick="location.href=\'' . tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=edit') . '\'"><i class="fa fa-edit mr5"></i>' . IMAGE_EDIT . '</button>';
                          echo '  <button class="btn btn-danger" type="button" onclick="location.href=\'' . tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=delete') . '\'"><i class="fa fa-trash mr5"></i>' . IMAGE_DELETE . '</button>';
                          echo '</p>';
                        }
                        break;
                    }
                  ?>
                  </div>
                </div>
              </div>
            </div>    
            <div class="row">
              <div class="col-lg-12">
                <?php echo $secMessageStack->output(); ?>
              </div>
            </div>
            <?php
              require(DIR_WS_INCLUDES . 'template_bottom.php');
              require(DIR_WS_INCLUDES . 'application_bottom.php');
            ?>
