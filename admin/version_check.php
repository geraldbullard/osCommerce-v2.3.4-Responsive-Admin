<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $current_version = tep_get_version();
  $major_version = (int)substr($current_version, 0, 1);

  $releases = null;
  $new_versions = array();
  $check_message = array();

  if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://www.oscommerce.com/version/online_merchant/' . $major_version);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = trim(curl_exec($ch));
    curl_close($ch);

    if (!empty($response)) {
      $releases = explode("\n", $response);
    }
  } else {
    if ($fp = @fsockopen('www.oscommerce.com', 80, $errno, $errstr, 30)) {
      $header = 'GET /version/online_merchant/' . $major_version . ' HTTP/1.0' . "\r\n" .
                'Host: www.oscommerce.com' . "\r\n" .
                'Connection: close' . "\r\n\r\n";

      fwrite($fp, $header);

      $response = '';
      while (!feof($fp)) {
        $response .= fgets($fp, 1024);
      }

      fclose($fp);

      $response = explode("\r\n\r\n", $response); // split header and content

      if (isset($response[1]) && !empty($response[1])) {
        $releases = explode("\n", trim($response[1]));
      }
    }
  }

  if (is_array($releases) && !empty($releases)) {
    $serialized = serialize($releases);
    if ($f = @fopen(DIR_FS_CACHE . 'oscommerce_version_check.cache', 'w')) {
      fwrite ($f, $serialized, strlen($serialized));
      fclose($f);
    }

    foreach ($releases as $version) {
      $version_array = explode('|', $version);

      if (version_compare($current_version, $version_array[0], '<')) {
        $new_versions[] = $version_array;
      }
    }

    if (!empty($new_versions)) {
      $check_message = array('class' => 'secWarning',
                             'message' => sprintf('<i class="fa fa-warning mr10"></i>' . VERSION_UPGRADES_AVAILABLE, $new_versions[0][0]),
                             'alert' => 'warning');
    } else {
      $check_message = array('class' => 'secSuccess',
                             'message' => '<i class="fa fa-check mr10"></i>' . VERSION_RUNNING_LATEST,
                             'alert' => 'success');
    }
  } else {
    $check_message = array('class' => 'secError',
                           'message' => '<i class="fa fa-bolt mr10"></i>' . ERROR_COULD_NOT_CONNECT,
                           'alert' => 'danger');
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
              <div class="col-lg-12">
                <div class="alert alert-info">
                  <i class="fa fa-info mr10"></i><?php echo TITLE_INSTALLED_VERSION . ' <strong>osCommerce Online Merchant v' . $current_version . '</strong>'; ?>  
                </div>                
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12">
                <div class="alert alert-<?php echo $check_message['alert']; ?>">
                  <?php echo $check_message['message']; ?>  
                </div>                
              </div>
            </div>
            <?php
              if (!empty($new_versions)) {
            ?>
            <div class="table-responsive brtr3 brtl3">
              <table class="table table-hover table-striped bds1 bdsilver">
                <thead class="bgsilver">
                  <tr>
                    <th><?php echo TABLE_HEADING_VERSION; ?></th>
                    <th class="hide-below-768"><?php echo TABLE_HEADING_RELEASED; ?></th>
                    <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    foreach ($new_versions as $version) {
                  ?>
                  <tr onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                    <td><?php echo '<a href="' . $version[2] . '" target="_blank"><span class="hide-below-480">osCommerce Online Merchant</span> v' . $version[0] . '</a>'; ?></td>
                    <td class="hide-below-768"><?php echo tep_date_long(substr($version[1], 0, 4) . '-' . substr($version[1], 4, 2) . '-' . substr($version[1], 6, 2)); ?></td>
                    <td class="text-right"><?php echo '<a href="' . $version[2] . '" target="_blank"><i class="fa fa-info-circle blue fs18i"></i></a>'; ?>&nbsp;</td>
                  </tr>
                  <?php
                    }
                  ?>
                </tbody>
              </table>
            </div>
            <?php
              }
              require(DIR_WS_INCLUDES . 'template_bottom.php');
              require(DIR_WS_INCLUDES . 'application_bottom.php');
            ?>
