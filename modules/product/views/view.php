<?php
/**
 * @filesource product/views/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Product\View;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Product\Index\Controller;
use \Kotchasan\Date;
use \Kotchasan\Language;
use \Kotchasan\Currency;

/**
 * แสดงบทความ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แสดงรายละเอียดสินค้า
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function index(Request $request, $index)
  {
    // ค่าที่ส่งมา
    $index->id = $request->get('id')->toInt();
    $index->alias = $request->get('alias')->text();
    $index->q = preg_replace('/[+\s]+/u', ' ', $request->get('q')->text());
    // อ่านรายการที่เลือก
    $index = \Product\View\Model::get($index);
    if ($index && ($index->published || Login::isAdmin())) {
      // URL ของหน้า
      $index->canonical = Controller::url($index->module, $index->alias, $index->id, false);
      // รูปภาพ
      $dir = DATA_FOLDER.'product/';
      $imagedir = ROOT_PATH.$dir;
      if (!empty($index->picture) && is_file($imagedir.$index->picture)) {
        $size = @getimagesize($imagedir.$index->picture);
        if ($size) {
          $index->image = array(
            '@type' => 'ImageObject',
            'url' => WEB_URL.$dir.$index->picture,
            'width' => $size[0],
            'height' => $size[1],
          );
        }
      }
      // breadcrumb ของโมดูล
      if (!Gcms::$menu->isHome($index->index_id)) {
        $menu = Gcms::$menu->findTopLevelMenu($index->index_id);
        if ($menu) {
          Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $menu->menu_text, $menu->menu_tooltip);
        }
      }
      // breadcrumb ของหน้า
      Gcms::$view->addBreadcrumb($index->canonical, $index->topic, $index->description);
      // AMP
      if (!empty(self::$cfg->amp)) {
        Gcms::$view->metas['amphtml'] = '<link rel="amphtml" href="'.WEB_URL.'amp.php?module='.$index->module.'&amp;id='.$index->id.'">';
      }
      // เนื้อหา
      $index->detail = Gcms::showDetail(str_replace(array('&#x007B;', '&#x007D;'), array('{', '}'), $index->detail), false, true, true);
      $replace = array(
        '/{TOPIC}/' => $index->topic,
        '/{IMG}/' => isset($index->image) ? $index->image['url'] : '',
        '/{DETAIL}/' => Gcms::HighlightSearch($index->detail, $index->q),
        '/{DATE}/' => Date::format($index->last_update),
        '/{VISITED}/' => number_format($index->visited),
        '/{ID}/' => $index->id,
        '/{URL}/' => $index->canonical,
        '/{MODULE}/' => $index->module,
        '/{SHOWPRICE}/' => empty($index->price[$index->currency_unit]) ? 'hidden' : 'price',
        '/{PRICE}/' => empty($index->price[$index->currency_unit]) ? '' : Currency::format($index->price[$index->currency_unit]),
        '/{NET}/' => empty($index->net[$index->currency_unit]) ? '{LNG_Contact Information}' : Currency::format($index->net[$index->currency_unit]),
        '/{CURRENCYUNIT}/' => Language::get('CURRENCY_UNITS')[$index->currency_unit],
      );
      // /product/view.html
      $detail = Template::create('product', $index->module, 'view')->add($replace);
      // JSON-LD
      Gcms::$view->setJsonLd(\Product\Jsonld\View::generate($index));
      // คืนค่า
      return (object)array(
          'image_src' => $index->picture == '' ? '' : WEB_URL.$index->picture,
          'canonical' => $index->canonical,
          'module' => $index->module,
          'topic' => $index->topic,
          'description' => $index->description,
          'keywords' => $index->keywords,
          'detail' => $detail->render()
      );
    }
    // 404
    return createClass('Index\PageNotFound\Controller')->init('product');
  }
}