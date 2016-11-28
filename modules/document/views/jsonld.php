<?php
/*
 * @filesource document/views/amp.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Jsonld;

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
      'publisher' => array(
        '@type' => 'Organization',
        'name' => strip_tags(self::$cfg->web_title),
      ),
      'mainEntityOfPage' => array(
        '@type' => 'WebPage',
        '@id' => Gcms::createUrl($index->module)
      ),
      'headline' => $index->topic,
      'url' => $index->canonical,
      'author' => $index->displayname,
      'datePublished' => date(DATE_ISO8601, strtotime($index->published_date)),
      'dateModified' => date(DATE_ISO8601, $index->last_update),
      'dateCreated' => date(DATE_ISO8601, $index->create_date),
      'description' => $index->description
    );
  }
}