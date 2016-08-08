<?php
/*
 * @filesource index/models/getnews.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Getnews;

use \Kotchasan\Http\Response;

/**
 * ตรวจสอบข่าวสารจาก GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * ตรวจสอบข่าวสารจาก GCMS
   */
  public static function get()
  {
    // url ของข่าว
    $url = 'http://gcms.in.th/news.php';
    if ($feedRef = @fopen($url, 'rb')) {
      $contents = '';
      while (!feof($feedRef)) {
        $contents .= fread($feedRef, 1024);
      }
      fclose($feedRef);
    } elseif ($ch = @curl_init()) {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      // method ที่เราจะส่ง เป็น get หรือ post
      curl_setopt($ch, CURLOPT_POST, 0);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      // ผลการ execute กลับมาเป็น ข้อมูลใน url ที่เรา ส่งคำร้องขอไป
      $contents = curl_exec($ch);
      curl_close($ch);
    }
    $response = new Response;
    $response->withHeader('Content-type', 'text/html; charset=utf-8')->setContent($contents)->send();
  }
}