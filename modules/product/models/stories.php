<?php
/*
 * @filesource product/models/module.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Product\Stories;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;

/**
 * ลิสต์รายการสินค้า
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * ลิสต์รายการสินค้า
   *
   * @param Request $request
   * @param Object $index
   * @return Object
   */
  public static function stories(Request $request, $index)
  {
    if (isset($index->module_id)) {
      // Model
      $model = new static;
      // query
      $query = $model->db()->createQuery()
        ->from('product P')
        ->where(array(
        array('P.module_id', (int)$index->module_id),
        array('P.published', 1)
      ));
      // จำนวน
      $index->total = $query->cacheOn()->count();
      // ข้อมูลแบ่งหน้า
      if (empty($index->rows)) {
        $index->rows = 20;
      }
      if (empty($index->cols)) {
        $index->cols = 1;
      }
      $list_per_page = $index->rows * $index->cols;
      $index->page = $request->request('page')->toInt();
      $index->totalpage = ceil($index->total / $list_per_page);
      $index->page = max(1, ($index->page > $index->totalpage ? $index->totalpage : $index->page));
      $index->start = $list_per_page * ($index->page - 1);
      // เรียงลำดับ
      $sorts = array(
        array('P.product_no DESC'),
        array('P.last_update DESC'),
        array('RAND()')
      );
      if (empty($index->sort) || !isset($sorts[$index->sort])) {
        $index->sort = 0;
      }
      $query->select('P.id', 'P.product_no', 'P.picture', 'P.alias', 'P.last_update', 'D.topic', 'D.description', 'P.visited', 'R.price', 'R.net')
        ->join('product_detail D', 'INNER', array(array('D.id', 'P.id'), array('D.language', array(Language::name(), ''))))
        ->join('product_price R', 'INNER', array('R.id', 'P.id'))
        ->order(isset($sorts[$index->sort]) ? $sorts[$index->sort] : $sorts[0])
        ->limit($list_per_page, $index->start)
        ->toArray()
        ->cacheOn();
      $index->items = array();
      foreach ($query->execute() as $item) {
        $item['price'] = \Product\View\Model::getPrice($item['price'], $index->currency_unit);
        $item['net'] = \Product\View\Model::getPrice($item['net'], $index->currency_unit);
        $index->items[] = (object)$item;
      }

      // คืนค่า
      return $index;
    }
  }
}