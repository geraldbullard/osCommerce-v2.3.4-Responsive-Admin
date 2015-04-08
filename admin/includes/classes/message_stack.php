<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License

  Example usage:

  $messageStack = new messageStack();
  $messageStack->add('Error: Error 1', 'error');
  $messageStack->add('Error: Error 2', 'warning');
  if ($messageStack->size > 0) echo $messageStack->output();
*/

  class messageStack extends tableBlock {
    var $size = 0;

    function messageStack() {
      global $messageToStack;

      $this->errors = array();

      if (isset($_SESSION['messageToStack'])) {
        for ($i = 0, $n = sizeof($messageToStack); $i < $n; $i++) {
          $this->add($messageToStack[$i]['text'], $messageToStack[$i]['type']);
        }
        unset($_SESSION['messageToStack']);
      }
    }

    function add($message, $type = 'error') {
      if ($type == 'error') {
        $this->errors[] = array('params' => 'danger', 'text' => '<i class="fa fa-exclamation mr10"></i>' . $message);
      } elseif ($type == 'warning') {
        $this->errors[] = array('params' => 'warning', 'text' => '<i class="fa fa-warning mr10"></i>' . $message);
      } elseif ($type == 'info') {
        $this->errors[] = array('params' => 'info', 'text' => '<i class="fa fa-info mr10"></i>' . $message);
      } elseif ($type == 'success') {
        $this->errors[] = array('params' => 'success', 'text' => '<i class="fa fa-check mr10"></i>' . $message);
      } else {
        $this->errors[] = array('params' => 'info', 'text' => '<i class="fa fa-info mr10"></i>' . $message);
      }

      $this->size++;
    }

    function add_session($message, $type = 'error') {
      global $messageToStack;

      if (!isset($_SESSION['messageToStack'])) {
        tep_session_register('messageToStack');
        $messageToStack = array();
      }

      $messageToStack[] = array('text' => $message, 'type' => $type);
    }

    function reset() {
      $this->errors = array();
      $this->size = 0;
    }

    function output() {
      $this->table_data_parameters = 'class="messageBox"';
      return $this->tableBlock($this->errors);
    }
  }
?>
