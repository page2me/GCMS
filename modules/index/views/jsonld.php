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
   * สร้างโค้ดสำหรับ JSON-LD สำหรับ WebSite
   *
   * @param object $page
   * @return array
   */
  public static function webpage($page)
  {
    return array(
      '@context' => 'http://schema.org',
      '@type' => 'WebPage',
      'url' => $page->canonical,
      'name' => $page->topic,
      'description' => $page->description,
      'image' => isset($page->image) ? $page->image : array(),
      'breadcrumb' => Gcms::$view->getBreadcrumbJsonld(),
      'publisher' => Gcms::$site,
    );
  }

  /**
   * สร้างโค้ดสำหรับ JSON-LD
   *
   * @param object $index
   * @return array
   */
  public static function search($index)
  {
    // หน้าค้นหา
    $items = array();
    foreach ($index->items as $n => $item) {
      $items[] = array(
        '@type' => 'ListItem',
        'position' => $n + 1,
        'item' => array(
          '@type' => 'TechArticle',
          'headline' => $item->topic,
          'url' => $item->url,
        )
      );
    }
    return array(
      '@context' => 'http://schema.org',
      '@type' => 'SearchResultsPage',
      'mainEntity' => array(
        '@type' => 'ItemList',
        'name' => $index->topic,
        'itemListOrder' => 'http://schema.org/ItemListOrderAscending',
        'itemListElement' => $items
      )
    );
  }
}