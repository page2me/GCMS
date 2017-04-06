<?php
/*
 * @filesource personnel/models/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Personnel\Admin\Write;

use Kotchasan\Language;
use Gcms\Gcms;
use Kotchasan\Login;
use \Kotchasan\ArrayTool;
use \Kotchasan\File;
use \Kotchasan\Http\UploadedFile;
use \Kotchasan\Database\Sql;

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
   * @param boolean $new true คืนค่า ID ถัดไป, false (default) คืนค่า $id ที่ส่งเข้ามา
   * @return object|null คืนค่าข้อมูล object ไม่พบคืนค่า null
   */
  public static function get($module_id, $id, $new = false)
  {
    // model
    $model = new static();
    $query = $model->db()->createQuery();
    if (empty($id)) {
      // ใหม่ ตรวจสอบโมดูล
      if ($new) {
        $query->select(Sql::NEXT('id', $model->getTableName('personnel'), null, 'id'), 'M.id module_id', 'M.owner', 'M.module', 'M.config');
      } else {
        $query->select('0 id', 'M.id module_id', 'M.owner', 'M.module', 'M.config');
      }
      $query->from('modules M')
        ->where(array(
          array('M.id', $module_id),
          array('M.owner', 'personnel'),
      ));
    } else {
      // แก้ไข ตรวจสอบรายการที่เลือก
      $query->select('A.*', 'M.owner', 'M.module', 'M.config')
        ->from('personnel A')
        ->join('modules M', 'INNER', array(array('M.id', 'A.module_id'), array('M.owner', 'personnel')))
        ->where(array('A.id', $id));
    }
    $result = $query->limit(1)->toArray()->execute();
    if (sizeof($result) == 1) {
      $result = ArrayTool::unserialize($result[0]['config'], $result[0], empty($id));
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
        // ค่าที่ส่งมา
        $save = array(
          'name' => self::$request->post('name')->topic(),
          'category_id' => self::$request->post('category_id')->toInt(),
          'order' => self::$request->post('order')->toInt(),
          'position' => self::$request->post('position')->topic(),
          'detail' => self::$request->post('detail')->topic(),
          'address' => self::$request->post('address')->topic(),
          'phone' => self::$request->post('phone')->topic(),
          'email' => self::$request->post('email')->url()
        );
        $id = self::$request->post('id')->toInt();
        // ตรวจสอบรายการที่เลือก
        $index = self::get(self::$request->post('module_id')->toInt(), $id, true);
        if ($index && Gcms::canConfig($login, $index, 'can_write')) {
          // name
          if ($save['name'] == '') {
            $ret['ret_name'] = 'this';
          } else {
            // อัปโหลดไฟล์
            foreach (self::$request->getUploadedFiles() as $item => $file) {
              /* @var $file UploadedFile */
              if ($file->hasUploadFile()) {
                if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'personnel/')) {
                  // ไดเรคทอรี่ไม่สามารถสร้างได้
                  $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'personnel/');
                } elseif (!$file->validFileExt(array('jpg', 'jpeg', 'png', 'gif'))) {
                  // ชนิดของไฟล์ไม่ถูกต้อง
                  $ret['ret_'.$item] = Language::get('The type of file is invalid');
                } else {
                  $save[$item] = $index->id.'.'.$file->getClientFileExt();
                  try {
                    $file->cropImage(array('jpg', 'jpeg', 'png', 'gif'), ROOT_PATH.DATA_FOLDER.'personnel/'.$save[$item], $index->image_width, $index->image_height);
                  } catch (\Exception $exc) {
                    // ไม่สามารถอัปโหลดได้
                    $ret['ret_'.$item] = Language::get($exc->getMessage());
                  }
                }
              } elseif ($id == 0) {
                // ใหม่ ต้องมีไฟล์
                $ret['ret_'.$item] = Language::get('Please select file');
              }
            }
            if (empty($ret)) {
              if ($id == 0) {
                // ใหม่
                $save['module_id'] = $index->module_id;
                $this->db()->insert($this->getTableName('personnel'), $save);
              } else {
                // แก้ไข
                $this->db()->update($this->getTableName('personnel'), $id, $save);
              }
              // ส่งค่ากลับ
              $ret['alert'] = Language::get('Saved successfully');
              $ret['location'] = self::$request->getUri()->postBack('index.php', array('mid' => $index->module_id, 'module' => 'personnel-setup'));
            }
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