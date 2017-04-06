<?php
/*
 * @filesource gallery/models/admin/upload.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gallery\Admin\Upload;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\ArrayTool;
use \Kotchasan\Database\Sql;

/**
 * อัปโหลดไฟล์.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านอัลบัม.
   *
   * @param int $module_id ของโมดูล
   * @param int $id ID ของอัลบัม
   * @param boolean $new false (default) คืนค่า ID 0 สำหรับรายการใหม่, true คืนค่า ID ถัดไปสำหรับรายการใหม่
   * @return object|null คืนค่าข้อมูล object ไม่พบคืนค่า null
   */
  public static function get($id)
  {
    // model
    $model = new static();
    // แก้ไข ตรวจสอบรายการที่เลือก
    $result = $model->db()->createQuery()
      ->from('gallery_album A')
      ->join('modules M', 'INNER', array(array('M.id', 'A.module_id'), array('M.owner', 'gallery')))
      ->where(array('A.id', $id))
      ->toArray()
      ->first('A.id', Sql::NEXT('count', $model->getTableName('gallery'), array(array('module_id', 'A.module_id'), array('album_id', 'A.id')), 'count'), 'M.id module_id', 'M.owner', 'M.module', 'M.config');
    if ($result) {
      $result = ArrayTool::unserialize($result['config'], $result);
      unset($result['config']);
      return (object)$result;
    }
    return null;
  }

  /**
   * บันทึก
   */
  public function save()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isMember()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // ตรวจสอบรายการที่เลือก
        $index = self::get(self::$request->post('albumId')->toInt());
        if ($index && Gcms::canConfig($login, $index, 'can_write')) {
          // อัปโหลดไฟล์
          foreach (self::$request->getUploadedFiles() as $item => $file) {
            /* @var $file UploadedFile */
            if ($file->hasUploadFile()) {
              if (!$file->validFileExt($index->img_typies)) {
                // ชนิดของไฟล์ไม่ถูกต้อง
                $ret['alert'] = Language::get('The type of file is invalid');
              } else {
                // อัปโหลด
                $image = $index->count.'.'.$file->getClientFileExt();
                try {
                  $dir = ROOT_PATH.DATA_FOLDER.'gallery/'.$index->id.'/';
                  $image = $file->resizeImage($index->img_typies, $dir, $image, $index->image_width);
                  $file->cropImage($index->img_typies, $dir.'thumb_'.$image['name'], $index->icon_width, $index->icon_height);
                  // save
                  $save = array(
                    'album_id' => $index->id,
                    'module_id' => $index->module_id,
                    'image' => $image['name'],
                    'last_update' => time(),
                    'count' => $index->count
                  );
                  $this->db()->insert($this->getTableName('gallery'), $save);
                } catch (\Exception $exc) {
                  // ไม่สามารถอัปโหลดได้
                  $ret['alert'] = Language::get($exc->getMessage());
                }
              }
            }
          }
          $q1 = $this->db()->createQuery()->selectCount()->from('gallery G')->where(array(
            array('G.album_id', 'A.id'),
            array('G.module_id', 'A.module_id')
          ));
          $this->db()->createQuery()
            ->update('gallery_album A')
            ->set(array(
              'last_update' => time(),
              'count' => $q1
            ))
            ->where(array(
              array('A.id', (int)$index->id),
              array('A.module_id', (int)$index->module_id)
            ))
            ->execute();
        }
      }
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}