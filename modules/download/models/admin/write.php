<?php
/*
 * @filesource download/models/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Download\Admin\Write;

use Kotchasan\Language;
use Gcms\Gcms;
use Kotchasan\Login;
use \Kotchasan\ArrayTool;
use \Kotchasan\File;
use \Kotchasan\Http\UploadedFile;
use \Kotchasan\Text;

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
   * @return object|null คืนค่าข้อมูล object ไม่พบคืนค่า null
   */
  public static function get($module_id, $id)
  {
    // model
    $model = new static();
    $query = $model->db()->createQuery();
    if (empty($id)) {
      // ใหม่ ตรวจสอบโมดูล
      $query->select('0 id', 'M.id module_id', 'M.owner', 'M.module', 'M.config')
        ->from('modules M')
        ->where(array(
          array('M.id', $module_id),
          array('M.owner', 'download'),
      ));
    } else {
      // แก้ไข ตรวจสอบรายการที่เลือก
      $query->select('A.*', 'M.owner', 'M.module', 'M.config')
        ->from('download A')
        ->join('modules M', 'INNER', array(array('M.id', 'A.module_id'), array('M.owner', 'download')))
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
          'detail' => self::$request->post('detail')->topic(),
          'file' => self::$request->post('file')->topic()
        );
        $id = self::$request->post('id')->toInt();
        // ตรวจสอบรายการที่เลือก
        $index = self::get(self::$request->post('module_id')->toInt(), $id);
        if ($index && Gcms::canConfig($login, $index, 'can_upload')) {
          $error = false;
          // detail
          if ($save['detail'] == '') {
            $ret['ret_detail'] = 'this';
            $error = true;
          } else {
            $ret['ret_detail'] = '';
          }
          if (!$error) {
            // อัปโหลดไฟล์
            foreach (self::$request->getUploadedFiles() as $item => $file) {
              /* @var $file UploadedFile */
              if ($file->hasUploadFile()) {
                if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'download/')) {
                  // ไดเรคทอรี่ไม่สามารถสร้างได้
                  $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'download/');
                  $error = true;
                } elseif (!$file->validFileExt($index->file_typies)) {
                  // ชนิดของไฟล์ไม่ถูกต้อง
                  $ret['ret_'.$item] = Language::get('The type of file is invalid');
                  $error = true;
                } elseif ($file->getSize() > $index->upload_size) {
                  // ขนาดของไฟล์ใหญ่เกินไป
                  $ret['ret_'.$item] = Language::get('The file size larger than the limit');
                  $error = true;
                } else {
                  $save['ext'] = $file->getClientFileExt();
                  $file_name = str_replace('.'.$save['ext'], '', $file->getClientFilename());
                  if ($file_name == '' && $save['name'] == '') {
                    $ret['ret_name'] = 'this';
                    $error = true;
                  } else {
                    // อัปโหลด
                    $save['file'] = DATA_FOLDER.'download/'.Text::rndname(10).'.'.$save['ext'];
                    while (file_exists(ROOT_PATH.$save['file'])) {
                      $save['file'] = DATA_FOLDER.'download/'.Text::rndname(10).'.'.$save['ext'];
                    }
                    try {
                      $file->moveTo(ROOT_PATH.$save['file']);
                      $save['size'] = $file->getSize();
                      if ($save['name'] == '') {
                        $save['name'] = $file_name;
                      }
                      if (!empty($index->file) && $save['file'] != $index->file) {
                        @unlink(ROOT_PATH.$index->file);
                      }
                    } catch (\Exception $exc) {
                      // ไม่สามารถอัปโหลดได้
                      $ret['ret_'.$item] = Language::get($exc->getMessage());
                      $error = true;
                    }
                  }
                }
              } elseif ($id == 0) {
                // ใหม่ ต้องมีไฟล์
                $ret['ret_'.$item] = Language::get('Please select file');
                $error = true;
              }
            }
          }
          if (!$error) {
            $save['last_update'] = time();
            if ($id == 0) {
              // ใหม่
              $save['module_id'] = $index->module_id;
              $save['downloads'] = 0;
              $save['member_id'] = $login['id'];
              $this->db()->insert($this->getFullTableName('download'), $save);
            } else {
              // แก้ไข
              $this->db()->update($this->getFullTableName('download'), $id, $save);
            }
            // ส่งค่ากลับ
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = self::$request->getUri()->postBack('index.php', array('module' => 'download-setup', 'mid' => $index->module_id));
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