<?php
/*
 * @filesource edocument/models/report.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Report;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;

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
    // รายการที่ต้องการ
    $id = $request->request('id')->toInt();
    // Model
    $model = new static;
    // ตรวจสอบรายการที่เลือก
    $edocument = $model->db()->createQuery()
      ->from('edocument P')
      ->join('index_detail D', 'INNER', array(array('D.module_id', 'P.module_id'), array('D.language', array('', Language::name()))))
      ->join('index I', 'INNER', array(array('I.id', 'D.id'), array('I.module_id', 'D.module_id'), array('I.index', '1'), array('I.language', 'D.language')))
      ->where(array(array('P.id', $id), array('P.module_id', (int)$index->module_id)))
      ->toArray()
      ->cacheOn()
      ->first('P.id', 'P.document_no', 'P.topic name', 'P.detail', 'D.topic', 'D.keywords', 'D.description');
    if ($edocument) {
      foreach ($edocument as $key => $value) {
        $index->$key = $value;
      }
      $query = $model->db()->createQuery()
        ->from('edocument_download D')
        ->join('user U', 'LEFT', array('U.id', 'D.member_id'))
        ->where(array(array('D.document_id', $id), array('D.module_id', (int)$index->module_id)));
      // จำนวน
      $index->total = $query->cacheOn()->count();
      // ข้อมูลแบ่งหน้า
      if (empty($index->list_per_page)) {
        $index->list_per_page = 20;
      }
      $index->page = $request->request('page')->toInt();
      $index->totalpage = ceil($index->total / $index->list_per_page);
      $index->page = max(1, ($index->page > $index->totalpage ? $index->totalpage : $index->page));
      $index->start = $index->list_per_page * ($index->page - 1);
      // query
      $query->select('D.*', 'U.fname', 'U.lname', 'U.email', 'U.status')
        ->order('D.last_update DESC')
        ->limit($index->list_per_page, $index->start);
      $index->items = $query->cacheOn()->execute();
      // คืนค่า
      return $index;
    }
    return null;
  }
}