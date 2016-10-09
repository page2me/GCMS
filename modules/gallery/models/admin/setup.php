<?php
/*
 * @filesource gallery/models/admin/setup.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gallery\Admin\Setup;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\File;

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
  protected $table = 'gallery_album A';

  /**
   * query หน้าเพจ เรียงลำดับตาม module,language
   *
   * @return array
   */
  public function getConfig()
  {
    $model = new \Kotchasan\Model;
    $query = $model->db()->createQuery()
      ->select('G.image')
      ->from('gallery G')
      ->where(array(array('G.album_id', 'A.id'), array('G.module_id', 'A.module_id')))
      ->order('count')
      ->limit(1);
    return array(
      'select' => array(
        'A.id',
        'A.topic',
        array($query, 'image'),
        'A.count',
        'A.visited',
        'A.last_update'
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
        $index = \Index\Adminmodule\Model::get('gallery', self::$request->post('mid')->toInt());
        if ($index && Gcms::canConfig($login, $index, 'can_write') && preg_match('/^[0-9,]+$/', $id)) {
          $id = explode(',', $id);
          $module_id = (int)$index->module_id;
          // Model
          $model = new \Kotchasan\Model;
          if ($action === 'delete') {
            // ลบอัลบัม
            $query = $model->db()->createQuery()->select('album_id', 'image')->from('gallery')->where(array(array('album_id', $id), array('module_id', $module_id)))->toArray();
            foreach ($query->execute() as $item) {
              // ลบไดเรคทอรี่ของอัลบัม
              File::removeDirectory(ROOT_PATH.DATA_FOLDER.'gallery/'.$item['album_id'].'/');
            }
            // ลบฐานข้อมูล
            $model->db()->createQuery()->delete('gallery', array(array('album_id', $id), array('module_id', $module_id)))->execute();
            $model->db()->createQuery()->delete('gallery_album', array(array('id', $id), array('module_id', $module_id)))->execute();
            // คืนค่า
            $ret['alert'] = Language::get('Deleted successfully');
            $ret['location'] = 'reload';
          } elseif ($action === 'deletep') {
            // ลบรูปภาพ
            $query = $model->db()->createQuery()->select('album_id', 'image')->from('gallery')->where(array(
                array('id', $id),
                array('album_id', self::$request->post('album')->toInt()),
                array('module_id', $module_id)
              ))
              ->toArray();
            foreach ($query->execute() as $item) {
              // ลบรูปภาพ
              @unlink(ROOT_PATH.DATA_FOLDER.'gallery/'.$item['album_id'].'/'.$item['image']);
              @unlink(ROOT_PATH.DATA_FOLDER.'gallery/'.$item['album_id'].'/thumb_'.$item['image']);
            }
            // ลบฐานข้อมูล
            $model->db()->createQuery()->delete('gallery', array(
              array('id', $id),
              array('album_id', self::$request->post('album')->toInt()),
              array('module_id', $module_id)
            ))->execute();
            // คืนค่า
            $ret['alert'] = Language::get('Deleted successfully');
            $ret['location'] = 'reload';
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
