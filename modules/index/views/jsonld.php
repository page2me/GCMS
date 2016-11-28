<?php
/*
 * @filesource index/views/jsonld.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Jsonld;

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
    return array(
      '@context' => 'http://schema.org',
      '@type' => 'WebSite',
      'name' => $index->topic,
      'url' => $index->canonical,
      'description' => $index->description,
      'publisher' => array(
        '@type' => 'Organization',
        'name' => strip_tags(self::$cfg->web_title),
      ),
    );
  }
}