<?php
/**
 * @filesource edocument/filedownload.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
// session
@session_cache_limiter('none');
@session_start();
// datas
$file = $_SESSION[$_GET['id']];
if (is_file($file['file'])) {
  $f = @fopen($file['file'], 'rb');
  if ($f) {
    // ดาวน์โหลดไฟล์
    header("Pragma: public");
    header("Expires: -1");
    header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
    header("Content-Disposition: attachment; filename=$file[name]");
    header('Content-Type: application/octet-stream');
    header('Content-Length: '.filesize($file['file']));
    header('Accept-Ranges: bytes');
    while (!feof($f)) {
      print(@fread($f, 1024 * 8));
      ob_flush();
      flush();
      if (connection_status() != 0) {
        @fclose($f);
        exit;
      }
    }
    @fclose($f);
    exit;
  }
} else {
  header("HTTP/1.0 404 Not Found");
}