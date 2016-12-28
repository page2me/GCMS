<?php
/*
 * @filesource documentation/views/amp.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Documentation\Jsonld;

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
      '@type' => 'TechArticle',
      'mainEntityOfPage' => array(
        '@type' => 'WebPage',
        '@id' => Gcms::createUrl($index->module),
        'publisher' => Gcms::$site,
      ),
      'headline' => $index->topic,
      'description' => $index->description,
      'url' => $index->canonical,
    );
  }
}