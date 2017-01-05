<?php
/*
 * @filesource product/views/amp.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Product\Jsonld;

use \Gcms\Gcms;

/**
 * generate JSON-LD
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\KBase
{

  /**
   * สร้างโค้ดสำหรับ JSON-LD
   *
   * @param object $index
   * @return array
   */
  public static function generate($index)
  {
    // คืนค่าข้อมูล JSON-LD
    return array(
      '@context' => 'http://schema.org',
      '@type' => 'Product',
      'mainEntityOfPage' => array(
        '@type' => 'WebPage',
        '@id' => Gcms::createUrl($index->module),
        'breadcrumb' => Gcms::$view->getBreadcrumbJsonld(),
      ),
      'name' => $index->topic,
      'image' => isset($index->image) ? $index->image : '',
      'description' => $index->description,
      'url' => $index->canonical,
      'offers' => array(
        '@type' => 'Offer',
        'priceCurrency' => $index->currency_unit,
        'price' => $index->net[$index->currency_unit],
        'availability' => 'http://schema.org/InStock',
        'seller' => Gcms::$site,
      ),
    );
  }
}