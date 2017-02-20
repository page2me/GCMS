<?php
/*
 * @filesource index/models/pages.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Pages;

use \Kotchasan\Login;
use \Kotchasan\Language;

/**
 * โมเดลสำหรับแสดงรายการหน้าเว็บไซต์ที่สร้างแล้ว (pages.php)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Orm\Field
{
  /**
   * ชื่อตาราง
   *
   * @var string
   */
  protected $table = 'modules M';

  /**
   * query หน้าเพจ เรียงลำดับตาม module,language
   *
   * @return array
   */
  public function getConfig()
  {

    return array(
      'select' => array(
        'I.id',
        'M.id AS module_id',
        'D.topic',
        'I.published',
        'I.language',
        'M.module',
        'M.owner',
        'I.last_update',
        'I.visited'
      ),
      'join' => array(
        array(
          'INNER',
          'Index\Detail\Model',
          array(
            array('D.module_id', 'M.id'),
          )
        ),
        array(
          'INNER',
          'Index\Index\Model',
          array(
            array('I.id', 'D.id'),
            array('I.module_id', 'M.id'),
            array('I.language', 'D.language')
          )
        )
      ),
      'order' => array(
        'M.module',
        'I.language'
      )
    );
  }

  /**
   * รับค่าจาก action ของ table
   */
  public function action()
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // ค่าที่ส่งมา
        $action = self::$request->post('action')->toString();
        $id = self::$request->post('id')->toInt();
        // Model
        $model = new \Kotchasan\Model;
        if ($action === 'published') {
          // เผยแพร่
          $index = $model->db()->first($model->getTableName('index'), $id);
          if ($index) {
            $published = $index->published == 1 ? 0 : 1;
            $model->db()->update($model->getTableName('index'), $index->id, array('published' => $published));
            // คืนค่า
            $ret['elem'] = 'published_'.$index->id;
            $lng = Language::get('PUBLISHEDS');
            $ret['title'] = $lng[$published];
            $ret['class'] = 'icon-published'.$published;
          }
        } elseif ($action === 'delete') {
          // ลบโมดูลและหน้าเพจ ไม่ลบข้อมูลของโมดูล
          $query = $model->db()->createQuery()
            ->select('id', 'module_id')
            ->from('index')
            ->where(array(
            array('index', 1),
            array('module_id', $model->db()->createQuery()->select('module_id')->from('index')->where(array('id', $id)))
          ));
          $count = 0;
          foreach ($query->execute() as $field) {
            $count++;
            if ($field->id == $id) {
              $model->db()->delete($model->getTableName('index'), $id);
              $model->db()->delete($model->getTableName('index_detail'), $id);
            }
          }
          // ลบโมดูล ถ้าไม่มีรายการในภาษาอื่น
          if ($count < 2) {
            $model->db()->delete($model->getTableName('modules'), $field->module_id);
          }
          // คืนค่า
          $ret['delete_id'] = self::$request->post('src')->toString().'_'.$id;
          $ret['alert'] = Language::get('Deleted successfully');
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}