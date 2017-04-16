<?php
/**
 * @filesource personnel/models/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Personnel\View;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 * อ่านข้อมูลโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลโมดูล
   *
   * @param Request $request
   * @param Object $index
   * @return Object
   */
  public static function get(Request $request, $index)
  {
    $model = new static;
    // ตรวจสอบรายการที่เลือก
    $search = $model->db()->createQuery()
      ->from('personnel P')
      ->join('index_detail D', 'INNER', array(array('D.module_id', 'P.module_id'), array('D.language', array('', Language::name()))))
      ->join('index I', 'INNER', array(array('I.id', 'D.id'), array('I.module_id', 'D.module_id'), array('I.index', '1'), array('I.language', 'D.language')))
      ->join('category C', 'LEFT', array(array('C.category_id', 'P.category_id'), array('C.module_id', 'P.module_id')))
      ->where(array(array('P.id', $request->request('id')->toInt()), array('P.module_id', (int)$index->module_id)))
      ->toArray()
      ->cacheOn()
      ->first('P.*', 'D.topic', 'D.description', 'D.keywords', 'C.topic category');
    if ($search) {
      $search['category'] = Gcms::ser2Str($search['category']);
      foreach ($search as $key => $value) {
        $index->$key = $value;
      }
      // คืนค่า
      return $index;
    }
    return null;
  }
}