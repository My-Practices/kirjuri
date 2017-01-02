<?php

require_once './include_functions.php';
ksess_verify(1);

$id = preg_replace('/[^0-9]/', '', (substr($_GET['case'], 0, 5)));

$target_dir = 'attachments/'.$id.'/';
$skip = false;
$upload_error = false;

if ($settings['allow_attachments'] !== '1') {
    header('Location: '.preg_replace('/\?.*/', '', $_SERVER['HTTP_REFERER']).'?case='.substr($_GET['case'], 0, 5).'');
    die;
}

if (!file_exists($target_dir)) {
    if (mkdir($target_dir, 0755) !== true) {
        trigger_error('Can not create subdirectory to attachments/. Check folder permissions.');

        header('Location: '.preg_replace('/\?.*/', '', $_SERVER['HTTP_REFERER']).'?case='.substr($_GET['case'], 0, 5).'');
        die;
    };
};

$total = count($_FILES['fileToUpload']['name']);

  for ($i = 0; $i < $total; ++$i) {
      $target_file = $target_dir.basename($_FILES['fileToUpload']['name'][$i]);
      if (file_exists($target_file)) {
          $_SESSION['failed_uploads'][] = $_FILES['fileToUpload']['name'][$i];
          $skip = true;
          continue;
      };

      if ($_FILES['fileToUpload']['size'][$i] > $settings['max_attachment_size']) {
          $_SESSION['failed_uploads'][] = $_FILES['fileToUpload']['name'][$i];
          logline($id, 'Error', 'Attachment upload failure (size): '.$target_file);
          $skip = true;
          continue;
      };

      if ((strtolower(pathinfo($target_file, PATHINFO_EXTENSION)) === 'js') ||
         (strtolower(pathinfo($target_file, PATHINFO_EXTENSION)) === 'php') ||
         (strtolower(pathinfo($target_file, PATHINFO_EXTENSION)) === 'html') ||
         (strtolower(pathinfo($target_file, PATHINFO_EXTENSION)) === 'htm')) {
          $target_file = $target_file.'.txt';
      }

      if ((move_uploaded_file($_FILES['fileToUpload']['tmp_name'][$i], $target_file)) && ($skip !== true)) {
          logline($id, 'Action', 'Attachment uploaded: '.$target_file);
          $upload_error = false;
      } else {
          $upload_error = true;
      }
  }

if ($upload_error === false) {
    header('Location: '.preg_replace('/\?.*/', '', $_SERVER['HTTP_REFERER']).'?case='.substr($_GET['case'], 0, 5));
    die;
} else {
    logline($id, 'Error', 'Attachment upload failure (other): '.$target_file);
    header('Location: '.preg_replace('/\?.*/', '', $_SERVER['HTTP_REFERER']).'?case='.substr($_GET['case'], 0, 5));
    die;
};
