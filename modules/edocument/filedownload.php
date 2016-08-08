<?php
// modules/edocument/filedownload.php
@session_cache_limiter('none');
@session_start();
// datas
$file = $_SESSION[$_GET['id']];
if (is_file($file['file'])) {
  // ดาวน์โหลดไฟล์
  header('Cache-Control: private');
  header('Content-Type: application/octet-stream');
  header("Content-Type: application/download");
  header('Content-Disposition: attachment; filename="'.$file['name'].'"');
  header('Content-Transfer-Encoding: binary');
  header('Accept-Ranges: bytes');
  set_time_limit(0);
  readfile($file['file']);
} else {
  header("HTTP/1.0 404 Not Found");
}