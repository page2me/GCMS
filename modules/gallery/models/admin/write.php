<?php
/*
 * @filesource gallery/models/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gallery\Admin\Write;

use Kotchasan\Language;
use Gcms\Gcms;
use Kotchasan\Login;
use \Kotchasan\ArrayTool;
use \Kotchasan\File;
use \Kotchasan\Http\UploadedFile;

/**
 * อ่านข้อมูลโมดูล.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลรายการที่เลือก
   *
   * @param int $module_id ของโมดูล
   * @param int $id ID
   * @param boolean $new false (default) คืนค่า ID 0 สำหรับรายการใหม่, true คืนค่า ID ถัดไปสำหรับรายการใหม่
   * @return object|null คืนค่าข้อมูล object ไม่พบคืนค่า null
   */
  public static function get($module_id, $id, $new = false)
  {
    // model
    $model = new static();
    $query = $model->db()->createQuery();
    if (empty($id) && !empty($module_id)) {
      // ใหม่ ตรวจสอบโมดูล
      if ($new) {
        $query->select($model->buildNext('id', 'gallery_album'), 'M.id module_id', 'M.owner', 'M.module', 'M.config');
      } else {
        $query->select('0 id', 'M.id module_id', 'M.owner', 'M.module', 'M.config');
      }
      $query->from('modules M')
        ->where(array(
          array('M.id', $module_id),
          array('M.owner', 'gallery'),
      ));
    } else {
      // แก้ไข ตรวจสอบรายการที่เลือก
      $query = $model->db()->createQuery()
        ->select('G.image')
        ->from('gallery G')
        ->where(array(array('G.album_id', 'A.id'), array('G.module_id', 'A.module_id')))
        ->order('count')
        ->limit(1);
      $query->select('A.*', array($query, 'image'), 'M.owner', 'M.module', 'M.config')
        ->from('gallery_album A')
        ->join('modules M', 'INNER', array(array('M.id', 'A.module_id'), array('M.owner', 'gallery')))
        ->where(array('A.id', $id));
    }
    $result = $query->limit(1)->toArray()->execute();
    if (sizeof($result) == 1) {
      $result = ArrayTool::unserialize($result[0]['config'], $result[0], empty($id));
      unset($result['config']);
      if (empty($id)) {
        $result['topic'] = '';
        $result['detail'] = '';
      }
      return (object)$result;
    }
    return null;
  }

  /**
   * query รูปภาพทั้งหมดของอัลบัม
   *
   * @param object $index
   * @return array
   */
  public static function pictures($index)
  {
    // model
    $model = new static();
    return $model->db()->createQuery()
        ->select('id', 'image', 'count')
        ->from('gallery')
        ->where(array(
          array('album_id', (int)$index->id),
          array('module_id', (int)$index->module_id)
        ))
        ->order('count')
        ->execute();
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
        // ค่าที่ส่งมา
        $save = array(
          'topic' => self::$request->post('topic')->topic(),
          'detail' => self::$request->post('detail')->textarea()
        );
        $id = self::$request->post('id')->toInt();
        // ตรวจสอบรายการที่เลือก
        $index = self::get(self::$request->post('module_id')->toInt(), $id, true);
        if ($index && Gcms::canConfig($login, $index, 'can_write')) {
          // เวลานี้
          $mktime = time();
          // ตรวจสอบค่าที่ส่งมา
          if ($save['topic'] == '') {
            $ret['ret_topic'] = 'this';
          } elseif ($save['detail'] == '') {
            $ret['ret_detail'] = 'this';
          } else {
            // อัปโหลดไฟล์
            foreach (self::$request->getUploadedFiles() as $item => $file) {
              /* @var $file UploadedFile */
              if ($file->hasUploadFile()) {
                $dir = ROOT_PATH.DATA_FOLDER.'gallery/'.$index->id.'/';
                if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'gallery/') || !File::makeDirectory($dir)) {
                  // ไดเรคทอรี่ไม่สามารถสร้างได้
                  $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'gallery/');
                } elseif (!$file->validFileExt($index->img_typies)) {
                  // ชนิดของไฟล์ไม่ถูกต้อง
                  $ret['ret_'.$item] = Language::get('The type of file is invalid');
                } else {
                  // อัปโหลด
                  $image = '0.'.$file->getClientFileExt();
                  try {
                    // image
                    $image = $file->resizeImage($index->img_typies, $dir, $image, $index->image_width);
                    // thumb
                    $file->cropImage($index->img_typies, $dir.'thumb_'.$image['name'], $index->icon_width, $index->icon_height);
                  } catch (\Exception $exc) {
                    // ไม่สามารถอัปโหลดได้
                    $ret['ret_'.$item] = Language::get($exc->getMessage());
                  }
                }
              } elseif ($id == 0) {
                // ใหม่ ต้องมีรูปภาพ
                $ret['ret_'.$item] = Language::get('Please select an image of the album cover');
              }
            }
          }
          if (empty($ret)) {
            $save['last_update'] = $mktime;
            if ($id == 0) {
              // ใหม่
              $save['id'] = $index->id;
              $save['module_id'] = $index->module_id;
              $save['count'] = 1;
              $save['visited'] = 0;
              $this->db()->insert($this->getTableName('gallery_album'), $save);
            } else {
              // แก้ไข
              $this->db()->update($this->getTableName('gallery_album'), $index->id, $save);
            }
            if (isset($image)) {
              $this->db()->delete($this->getTableName('gallery'), array(array('album_id', $index->id), array('module_id', $index->module_id), array('count', 0)), 0);
              $save2 = array(
                'album_id' => $index->id,
                'module_id' => $index->module_id,
                'image' => $image['name'],
                'last_update' => $mktime,
                'count' => 0
              );
              $this->db()->insert($this->getTableName('gallery'), $save2);
            }
            // ส่งค่ากลับ
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = self::$request->getUri()->postBack('index.php', array('mid' => $index->module_id, 'module' => 'gallery-setup'));
          }
        } else {
          $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
        }
      }
    } else {
      $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}