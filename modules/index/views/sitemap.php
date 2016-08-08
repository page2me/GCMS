<?php
/*
 * @filesource index/views/sitemap.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Sitemap;

/**
 * register, forgot page
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * สร้างรายการ sitemap
   *
   * @param string $url
   * @param string $date
   * @return string
   */
  public function render($url, $date)
  {
    return '<url><loc>'.$url.'</loc><lastmod>'.$date.'</lastmod><changefreq>daily</changefreq><priority>0.5</priority></url>';
  }
}