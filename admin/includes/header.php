<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand nm5" href="<?php echo tep_href_link(FILENAME_DEFAULT); ?>"><?php echo tep_image(DIR_WS_IMAGES . 'oscommerce.png', 'Extreme osC v' . tep_get_version()); ?></a>
      </div>
      <!-- START Top Menu Items -->
      <ul class="nav navbar-right top-nav">
        <li class="dropdown">
          <a href="../" target="_blank" title="View Shopping Cart"><i class="fa fa-shopping-cart white"></i></a>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user white"></i> <span class="white"><?php echo $admin['username']; ?></span> <b class="caret white"></b></a>
          <ul class="dropdown-menu">
            <li>
              <a href="<?php echo tep_href_link(FILENAME_ADMINISTRATORS, 'aID=' . $admin['id'] . '&action=edit'); ?>"><i class="fa fa-fw fa-user"></i> Edit Profile</a>
            </li>
            <li class="divider"></li>
            <li>
              <a href="<?php echo tep_href_link(FILENAME_LOGIN, 'action=logoff'); ?>"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
            </li>
          </ul>
        </li>
      </ul>
