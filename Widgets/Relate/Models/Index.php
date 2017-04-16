<?php
/**
 * @filesource Widgets/Relate/Models/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Relate\Models;

use \Kotchasan\Language;
use \Kotchasan\Date;
use \Kotchasan\Database\Sql;

/**
 * อ่านข้อมูลโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูล
   *
   * @param int $id
   * @param int $rows
   * @param int $cols
   * @return Object
   */
  public static function get($id, $rows, $cols)
  {
    $model = new static;
    if ($id > 0) {
      // อ่านโมดูล จาก id ของ บทความ
      $index = $model->db()->createQuery()
        ->from('index Q')
        ->join('index_detail D', 'INNER', array(array('D.id', 'Q.id'), array('D.module_id', 'Q.module_id')))
        ->where(array(
          array('Q.id', $id),
          array('Q.index', '0')
        ))
        ->toArray()
        ->cacheOn()
        ->first('D.relate', 'Q.id', 'Q.module_id');
      if ($index && $index['relate'] !== '') {
        // relate
        $qs = array();
        foreach (explode(',', $index['relate']) as $q) {
          $qs[] = "D.`relate` LIKE '%$q%'";
        }
        // ชื่อตาราง
        $table_index = $model->getTableName('index');
        $table_index_detail = $model->getTableName('index_detail');
        $table_user = $model->getTableName('user');
        $select = array('Q.id', 'D.topic', 'Q.alias', 'Q.picture', 'Q.comment_date', 'Q.last_update', 'Q.create_date', 'D.description', 'Q.comments', 'Q.visited', 'Q.member_id', 'D.language');
        $where = array(
          array('Q.module_id', (int)$index['module_id']),
          array('Q.published', '1'),
          array('Q.published_date', '<=', Date::mktimeToSqlDate()),
          array('Q.index', '0'),
          array('Q.id', '>', $id),
          Sql::create(implode(' OR ', $qs)),
          array('D.language', array(Language::name(), ''))
        );
        // newest
        $q1 = $model->db()->createQuery()
          ->select($select)
          ->from('index Q')
          ->join('index_detail D', 'INNER', array(array('D.id', 'Q.id'), array('D.module_id', 'Q.module_id')))
          ->where($where)
          ->order('Q.create_date');
        $sql1 = 'SELECT @n:=@n+1 AS `row`,Q.* FROM ('.$q1->text().') AS Q, (SELECT @n:=0) AS R';
        // older
        $where[4][1] = '<';
        $q1->select($select)->where($where)->order('Q.create_date DESC');
        $sql2 = 'SELECT @m:=@m+1 AS `row`,Q.* FROM ('.$q1->text().') AS Q, (SELECT @m:=0) AS L';
        $sql3 = $model->db()->createQuery()
          ->select()
          ->from(array("($sql1) UNION ($sql2)", 'N'))
          ->order('N.row')
          ->limit($rows * $cols);
        $query = $model->db()->createQuery()
          ->select('Y.id', 'Y.topic', 'Y.alias', 'Y.picture', 'Y.comment_date', 'Y.last_update', 'Y.create_date', 'Y.description', 'Y.comments', 'Y.visited', 'U.status', 'U.id member_id', 'U.fname', 'U.lname', 'U.email')
          ->from(array($sql3, 'Y'))
          ->join('user U', 'LEFT', array('U.id', 'Y.member_id'))
          ->order('Y.create_date');
        $index['items'] = $query->cacheOn()->execute();
        $index['cols'] = $cols;
        return (object)$index;
      }
    }
    return false;
  }
}