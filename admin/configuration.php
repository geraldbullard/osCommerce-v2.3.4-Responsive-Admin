<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        $configuration_value = tep_db_prepare_input($_POST['configuration_value']);
        $cID = tep_db_prepare_input($_GET['cID']);

        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($configuration_value) . "', last_modified = now() where configuration_id = '" . (int)$cID . "'");

        tep_redirect(tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cID));
        break;
    }
  }

  $gID = (isset($_GET['gID'])) ? $_GET['gID'] : 1;

  $cfg_group_query = tep_db_query("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '" . (int)$gID . "'");
  $cfg_group = tep_db_fetch_array($cfg_group_query);

  require(DIR_WS_INCLUDES . 'template_top.php');
?>
            <div class="row mt10">
              <h2 class="mt0 ml15"><?php echo $cfg_group['configuration_group_title']; ?></h2>
              <div class="col-lg-8">
                <div class="table-responsive brtr3 brtl3">
                  <table class="table table-hover table-striped bds1 bdsilver">
                    <thead class="bgsilver">
                      <tr>
                        <th><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></th>
                        <th class="hide-below-768"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></th>
                        <th class="text-right width-action-below-480"><?php echo TABLE_HEADING_ACTION; ?></th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                      $configuration_query = tep_db_query("select configuration_id, configuration_title, configuration_value, use_function from " . TABLE_CONFIGURATION . " where configuration_group_id = '" . (int)$gID . "' order by sort_order");
                      while ($configuration = tep_db_fetch_array($configuration_query)) {
                        if (tep_not_null($configuration['use_function'])) {
                          $use_function = $configuration['use_function'];
                          if (preg_match('/->/', $use_function)) {
                            $class_method = explode('->', $use_function);
                            if (!is_object(${$class_method[0]})) {
                              include(DIR_WS_CLASSES . $class_method[0] . '.php');
                              ${$class_method[0]} = new $class_method[0]();
                            }
                            $cfgValue = tep_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
                          } else {
                            $cfgValue = tep_call_function($use_function, $configuration['configuration_value']);
                          }
                        } else {
                          $cfgValue = $configuration['configuration_value'];
                        }

                        if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $configuration['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
                          $cfg_extra_query = tep_db_query("select configuration_key, configuration_description, date_added, last_modified, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_id = '" . (int)$configuration['configuration_id'] . "'");
                          $cfg_extra = tep_db_fetch_array($cfg_extra_query);

                          $cInfo_array = array_merge($configuration, $cfg_extra);
                          $cInfo = new objectInfo($cInfo_array);
                        }

                        if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) {
                          echo '<tr id="defaultSelected" onclick="document.location.href=\'' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'">' . "\n";
                        } else {
                          echo '<tr onclick="document.location.href=\'' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $configuration['configuration_id'] . '&action=edit') . '\'">' . "\n";
                        }
                      ?>
                        <td class="truncate-below-480"><?php echo $configuration['configuration_title']; ?></td>
                        <td class="hide-below-768"><?php echo htmlspecialchars($cfgValue); ?></td>
                        <td class="text-right width-action-below-480">
                        <?php 
                          if ( (isset($cInfo) && is_object($cInfo)) && ($configuration['configuration_id'] == $cInfo->configuration_id) ) { 
                            echo '<i class="fa fa-hand-o-right blue fs16i"></i>'; 
                          } else { 
                            echo '<a href="' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $configuration['configuration_id']) . '"><i class="fa fa-info-circle blue fs16i"></i></a>'; 
                          } 
                        ?>
                        </td>
                      </tr>
                      <?php
                        }
                      ?> 
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="panel panel-primary">
                  <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $cInfo->configuration_title; ?></h3>
                  </div>
                  <div class="panel-body">
                  <?php
                    switch ($action) {
                      case 'edit':
                        if ($cInfo->set_function) {
                          eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value) . '");');
                        } else {
                          $value_field = tep_draw_input_field('configuration_value', $cInfo->configuration_value, 'class="form-control mt10"');
                        }

                        echo tep_draw_form('configuration', FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=save');
                        echo '<p>' . TEXT_INFO_EDIT_INTRO . '<p>';
                        echo '<p>' . $cInfo->configuration_description . '<br />' . $value_field . '</p>';
                        echo '<p class="pt10 text-center"><button class="btn btn-success mr15" type="submit"><i class="fa fa-save mr5"></i>' . IMAGE_SAVE . '</button><button class="btn btn-default" type="button" onclick="location.href=\'' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id) . '\'"><i class="fa fa-ban mr5"></i>' . IMAGE_CANCEL . '</button></p>';
                        break;
                      default:
                        if (isset($cInfo) && is_object($cInfo)) {
                          echo '<p>' . $cInfo->configuration_description . '<p>';
                          echo '<p>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added) . '</p>';
                          if (tep_not_null($cInfo->last_modified)) echo '<p>' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified) . '</p>';
                          echo '<p class="text-center"><button class="btn btn-primary" type="button" onclick="location.href=\'' . tep_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'"><i class="fa fa-edit mr5"></i>' . IMAGE_EDIT . '</button></p>';
                        }
                        break;
                    }
                  ?>
                  </div>
                </div>
              </div>
            </div>
            <?php
              require(DIR_WS_INCLUDES . 'template_bottom.php');
              require(DIR_WS_INCLUDES . 'application_bottom.php');
            ?>
