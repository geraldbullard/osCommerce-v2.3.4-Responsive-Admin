<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title><?php echo TITLE; ?></title>
  <base href="<?php echo ($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_ADMIN : HTTP_SERVER . DIR_WS_ADMIN; ?>" />
  <link rel="stylesheet" href="<?php echo tep_catalog_href_link('ext/jquery/ui/redmond/jquery-ui-1.10.4.min.css', '', 'SSL'); ?>">
  <!-- Bootstrap Core CSS -->
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/sb-admin.css">
  <link rel="stylesheet" href="assets/css/sb-admin-responsive.css">
  <?php if (!isset($_SESSION['admin'])) { ?>
  <link rel="stylesheet" href="assets/css/login.css">
  <?php } ?>
  <!-- Custom Fonts -->
  <link rel="stylesheet" href="assets/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/css/perfect-scrollbar.css" media="screen"/>
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
  <!--[if IE]><script src="<?php echo tep_catalog_href_link('ext/flot/excanvas.min.js', '', 'SSL'); ?>"></script><![endif]-->
  <script src="<?php echo tep_catalog_href_link('ext/jquery/jquery-1.11.1.min.js', '', 'SSL'); ?>"></script>
  <?php
    if (tep_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
  ?>
  <script src="<?php echo tep_catalog_href_link('ext/jquery/ui/i18n/jquery.ui.datepicker-' . JQUERY_DATEPICKER_I18N_CODE . '.js', '', 'SSL'); ?>"></script>
  <script>
  $.datepicker.setDefaults($.datepicker.regional['<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>']);
  </script>
  <?php
    }
  ?>
  <script src="<?php echo tep_catalog_href_link('ext/flot/jquery.flot.min.js', '', 'SSL'); ?>"></script>
  <script src="<?php echo tep_catalog_href_link('ext/flot/jquery.flot.time.min.js', '', 'SSL'); ?>"></script>
  <script src="includes/general.js"></script>
  <script src="assets/js/perfect-scrollbar.jquery.js"></script>
  <script>
    $(function() {
      $('#sideDiv').perfectScrollbar();
    });
  </script>  
</head>
<body>
  <!-- START Main Wrapper --> 
  <div id="wrapper">
    <!-- START Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <?php include(DIR_WS_INCLUDES . 'header.php'); ?>
      <?php include(DIR_WS_INCLUDES . 'column_left.php'); ?>
    </nav>
    <!-- START Page Content Wrapper -->
    <div id="page-wrapper">
      <div class="container-fluid">
        <?php if ($messageStack->size > 0) { ?>
        <div class="row">
          <div class="col-lg-12">
            <?php echo $messageStack->output(); ?>
          </div>
        </div>
        <?php  } ?>
        <div class="row">
          <div class="col-lg-12">
