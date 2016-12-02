<?php
/*
 * @filesource board/views/amp.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Jsonld;

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
    $suggestedAnswer = array();
    if (!empty($index->comment_items)) {
      foreach ($index->comment_items as $item) {
        $suggestedAnswer[] = array(
          '@type' => 'Answer',
          'text' => strip_tags($item->detail),
          'dateCreated' => date(DATE_ISO8601, $item->last_update),
          'author' => array(
            '@type' => 'Person',
            'name' => $item->displayname
          )
        );
      }
    }
    // คืนค่าข้อมูล JSON-LD
    return array(
      '@context' => 'http://schema.org',
      '@type' => 'Question',
      'mainEntityOfPage' => array(
        '@type' => 'WebPage',
        '@id' => Gcms::createUrl($index->module),
        'publisher' => Gcms::$site,
      ),
      'url' => $index->canonical,
      'name' => $index->topic,
      'text' => strip_tags($index->detail),
      'dateCreated' => date(DATE_ISO8601, $index->create_date),
      'answerCount' => $index->comments,
      'upvoteCount' => $index->visited,
      'author' => array(
        '@type' => 'Person',
        'name' => $index->name
      ),
      'suggestedAnswer' => $suggestedAnswer,
    );
  }
}