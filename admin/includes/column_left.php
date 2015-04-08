<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if (isset($_SESSION['admin'])) {
    $cl_box_groups = array();
    if ($dir = @dir(DIR_FS_ADMIN . 'includes/boxes')) {
      $files = array();
      while ($file = $dir->read()) {
        if (!is_dir($dir->path . '/' . $file)) {
          if (substr($file, strrpos($file, '.')) == '.php') {
            $files[] = $file;
          }
        }
      }
      $dir->close();
      natcasesort($files);
      foreach ($files as $file) {
        if (file_exists(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/boxes/' . $file)) {
          include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/boxes/' . $file);
        }
        include($dir->path . '/' . $file);
      }
    }
    function tep_sort_admin_boxes($a, $b) {
      return strcasecmp($a['heading'], $b['heading']);
    }
    usort($cl_box_groups, 'tep_sort_admin_boxes');
    function tep_sort_admin_boxes_links($a, $b) {
      return strcasecmp($a['title'], $b['title']);
    }
    foreach ($cl_box_groups as &$group) {
      usort($group['apps'], 'tep_sort_admin_boxes_links');
    }
?>
      <!-- START Sidebar Menu Items -->
      <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul id="sideDiv" class="nav navbar-nav side-nav">
          <?php
            foreach ($cl_box_groups as $groups) {
              $nameparts = explode(".", $groups['heading']);
              $code = $nameparts[0];
              echo '<li id="' . strtolower(preg_replace('/\PL+/u', '', $code)) . 'menu">';
              echo '  <a href="javascript:;" data-toggle="collapse" data-target="#' . strtolower(preg_replace('/\PL+/u', '', $code)) . '">';
              echo '    ' . $groups['heading'] . ' <i class="fa fa-fw fa-caret-right"></i>';
              echo '  </a>';
              if (count($groups['apps']) > 0) {
                echo '  <ul id="' . strtolower(preg_replace('/\PL+/u', '', $code)) . '" class="collapse">';
                foreach ($groups['apps'] as $app) {
                  if (($app['code'] == $PHP_SELF)) {
                    $currlink = $app['code'];
                  }
                  echo '<li><a href="' . $app['link'] . '">' . $app['title'] . '</a></li>';
                }
                echo '</ul>';
              }
              echo '</li>';
            } 
          ?>  
        </ul>
      </div>
      <?php 
        } 
      ?>
