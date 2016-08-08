<?php
/*
 * @filesource index/views/search.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Search;

use \Gcms\Gcms;
use \Kotchasan\Template;
use \Kotchasan\Grid;
use \Kotchasan\Language;

/**
 * หน้าเพจจากโมดูล index
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * แสดงผล
   *
   * @param object $index ข้อมูลโมดูล
   */
  public function render($index)
  {
    // รายการ
    $listitem = Grid::create('search', 'search', 'searchitem');
    foreach ($index->items as $item) {
      if ($item->index == 0 && $item->owner == 'document') {
        // document
        $uri1 = \Document\Index\Controller::url($item->module, $item->alias, $item->id);
        $uri2 = \Document\Index\Controller::url($item->module, $item->alias, $item->id, false);
      } elseif ($item->index == 0 && $item->owner == 'board') {
        // board
        $uri1 = \Board\Index\Controller::url($item->module, 0, $item->id);
        $uri2 = $uri1;
      } else {
        // other
        if (self::$cfg->module_url == 1) {
          $uri1 = Gcms::createUrl($item->module, $item->alias);
          $uri2 = Gcms::createUrl($item->module, $item->alias, 0, 0, '', false);
        } else {
          $uri1 = Gcms::createUrl($item->module, '', 0, $item->id, '');
          $uri2 = $uri1;
        }
      }
      $listitem->add(array(
        '/{URL}/' => $uri1,
        '/{TOPIC}/' => $item->topic,
        '/{LINK}/' => $uri2,
        '/{DETAIL}/' => $item->description
      ));
    }
    // template search/search.html
    $template = Template::create('search', 'search', 'search');
    // canonical
    $index->canonical = Gcms::createUrl($index->module);
    // current URL
    $uri = \Kotchasan\Http\Uri::createFromUri($index->canonical);
    if ($index->total > 0) {
      $list = Gcms::highlightSearch($listitem->render(), $index->q);
    } else {
      $list = $index->q == '' ? '' : '<div>'.Language::get('No results were found for').' <strong>'.$index->q.'</strong></div>';
      $list .= '<div><strong>'.Language::get('Search tips').' :</strong>'.Language::get('<ul><li>make sure that the spelling correct</li><li>try changing or new phrases. synonyms</li><li>try to identify a non-specific too</li><li>specific keywords to search the most concise</li></ul>').'</div>';
    }
    // add template
    $template->add(array(
      '/{LIST}/' => $list,
      '/{SPLITPAGE}/' => $uri->pagination($index->totalpage, $index->page),
      '/{SEARCH}/' => $index->q,
      '/{MODULE}/' => 'search',
      '/{RESULT}/' => $index->total == 0 ? '' : sprintf(Language::get('Search results <strong>%d - %d</strong> of about <strong>%d</strong> for <strong>%s</strong> (%s sec)'), $index->start + 1, $index->end, $index->total, $index->q, number_format(microtime(true) - REQUEST_TIME, 4))
    ));
    $search = Language::get('Search');
    $index->detail = $template->render();
    $index->topic = ($index->q == '' ? '' : $index->q.' - ').$search;
    $index->description = $index->topic;
    $index->keywords = $index->topic;
    $index->menu = 'search';
    // breadcrumb ของหน้า
    Gcms::$view->addBreadcrumb($index->canonical, $search, $search);
    return $index;
  }
}