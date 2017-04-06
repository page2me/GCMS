<?php
/*
 * @filesource documentation/models/admin/setup.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Documentation\Admin\Setup;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\Database\Sql;

/**
 * โมเดลสำหรับแสดงรายการบทความ (setup.php)
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
  protected $table = 'index P';

  /**
   * query หน้าเพจ เรียงลำดับตาม module,language
   *
   * @return array
   */
  public function getConfig()
  {
    return array(
      'select' => array(
        'P.id',
        'D.topic',
        'P.published',
        'P.category_id',
        Sql::create('(CASE WHEN ISNULL(U.`id`) THEN P.`email` WHEN U.`displayname`=\'\' THEN U.`email` ELSE U.`displayname` END) AS `writer`'),
        'P.last_update',
        'P.member_id',
        'P.visited',
        'U.status',
        'P.module_id'
      ),
      'join' => array(
        array(
          'INNER',
          'Index\Detail\Model',
          array(
            array('D.id', 'P.id'),
            array('D.module_id', 'P.module_id')
          )
        ),
        array(
          'LEFT',
          'Index\User\Model',
          array(
            array('U.id', 'P.member_id')
          )
        )
      ),
      'order' => array(
        'P.create_date DESC'
      )
    );
  }

  /**
   * รับค่าจาก action ของ table
   */
  public static function action()
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isMember()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $id = self::$request->post('id')->toString();
        $action = self::$request->post('action')->toString();
        $index = \Index\Module\Model::getModule(self::$request->post('mid')->toInt());
        if ($index && Gcms::canConfig($login, $index, 'can_write')) {
          // Model
          $model = new \Kotchasan\Model;
          if ($action === 'published') {
            // สถานะการเผยแพร่
            $table_index = $model->getTableName('index');
            $search = $model->db()->first($table_index, array(array('id', (int)$id), array('module_id', (int)$index->id)));
            if ($search) {
              $published = $search->published == 1 ? 0 : 1;
              $model->db()->update($table_index, $search->id, array('published' => $published));
              // คืนค่า
              $ret['elem'] = 'published_'.$search->id;
              $lng = Language::get('PUBLISHEDS');
              $ret['title'] = $lng[$published];
              $ret['class'] = 'icon-published'.$published;
            }
          } elseif ($action === 'delete' && preg_match('/^[0-9,]+$/', $id)) {
            // ลบรายการที่เลือก
            $model->db()->createQuery()->delete('index', array(array('id', explode(',', $id)), array('module_id', (int)$index->id)))->execute();
            $model->db()->createQuery()->delete('index_detail', array(array('id', explode(',', $id)), array('module_id', (int)$index->id)))->execute();
            // reload
            $ret['location'] = 'reload';
          } elseif ($action === 'move') {
            $data = self::$request->post('data')->toString();
            if (preg_match('/[0-9,]+/', $data)) {
              $data = explode(',', $data);
              $top = 0;
              $ids = array();
              $table_index = $model->getTableName('index');
              foreach ($model->db()->find($table_index, array(array('id', $data), array('module_id', (int)$index->id))) as $item) {
                $top = max($top, $item->create_date);
                $ids[$item->id] = $item->create_date;
              }
              foreach ($data as $id) {
                if (isset($ids[$id])) {
                  $model->db()->update($table_index, $id, array('create_date' => $top));
                  $top--;
                }
              }
            }
          }
        } else {
          $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    if (!empty($ret)) {
      // คืนค่าเป็น JSON
      echo json_encode($ret);
    }
  }
}