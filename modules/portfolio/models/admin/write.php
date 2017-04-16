<?php
/**
 * @filesource portfolio/models/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Portfolio\Admin\Write;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\ArrayTool;
use \Kotchasan\File;
use \Kotchasan\Http\UploadedFile;
use \Kotchasan\Date;
use \Kotchasan\Database\Sql;

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
   * อ่านข้อมูลรายการที่เลือก
   *
   * @param int $module_id ของโมดูล
   * @param int $id ID
   * @param boolean $next false (default) คืนค่า id ตาม $id ที่ส่งมา, true คืนค่า id ใหม่
   * @return object|null คืนค่าข้อมูล object ไม่พบคืนค่า null
   */
  public static function get($module_id, $id, $next = false)
  {
    // model
    $model = new static;
    $query = $model->db()->createQuery();
    if (empty($id)) {
      // ใหม่ ตรวจสอบโมดูล
      if ($next) {
        $query->select(Sql::NEXT('id', $model->getTableName('portfolio'), null, 'id'), 'M.id module_id', 'M.owner', 'M.module', 'M.config');
      } else {
        $query->select('0 id', 'M.id module_id', 'M.owner', 'M.module', 'M.config');
      }
      $query->from('modules M')
        ->where(array(
          array('M.id', $module_id),
          array('M.owner', 'portfolio'),
      ));
    } else {
      // แก้ไข ตรวจสอบรายการที่เลือก
      $query->select('A.*', 'M.owner', 'M.module', 'M.config')
        ->from('portfolio A')
        ->join('modules M', 'INNER', array(array('M.id', 'A.module_id'), array('M.owner', 'portfolio')))
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
   *
   * @param Request $request
   */
  public function save(Request $request)
  {
    $ret = array();
    // referer, session, member
    if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // ค่าที่ส่งมา
        $save = array(
          'title' => $request->post('title')->topic(),
          'detail' => $request->post('detail')->detail(),
          'keywords' => $request->post('keywords')->keywords(255),
          'url' => $request->post('url')->url(),
          'published' => $request->post('published')->toBoolean(),
          'create_date' => Date::sqlDateTimeToMktime($request->post('create_date')->date()),
        );
        $id = $request->post('id')->toInt();
        // ตรวจสอบรายการที่เลือก
        $index = self::get($request->post('module_id')->toInt(), $id, true);
        if (!$index || !Gcms::canConfig($login, $index, 'can_write')) {
          // ไม่พบ หรือไม่สามารถเขียนได้
          $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
        } else {
          if (mb_strlen($save['title']) < 3) {
            // title short
            $ret['ret_title'] = 'this';
          } elseif ($save['detail'] == '') {
            // detail empty
            $ret['ret_detail'] = 'this';
          } else {
            // อัปโหลดไฟล์
            foreach ($request->getUploadedFiles() as $item => $file) {
              /* @var $file UploadedFile */
              if ($file->hasUploadFile()) {
                if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'portfolio/')) {
                  // ไดเรคทอรี่ไม่สามารถสร้างได้
                  $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'portfolio/');
                } elseif (!$file->validFileExt(array('jpg', 'jpeg', 'png'))) {
                  // ชนิดของไฟล์ไม่ถูกต้อง
                  $ret['ret_'.$item] = Language::get('The type of file is invalid');
                } elseif ($item == 'thumbnail') {
                  // thumbnail
                  try {
                    $file->cropImage(array('jpg', 'jpeg', 'png'), ROOT_PATH.DATA_FOLDER.'portfolio/thumb_'.$index->id.'.jpg', $index->width, $index->height);
                  } catch (\Exception $exc) {
                    // ไม่สามารถอัปโหลดได้
                    $ret['ret_'.$item] = Language::get($exc->getMessage());
                  }
                } else {
                  // อัปโหลด ขนาดจริง
                  $save[$item] = $index->id.'.'.$file->getClientFileExt();
                  try {
                    $file->moveTo(ROOT_PATH.DATA_FOLDER.'portfolio/'.$save[$item]);
                  } catch (\Exception $exc) {
                    // ไม่สามารถอัปโหลดได้
                    $ret['ret_'.$item] = Language::get($exc->getMessage());
                  }
                }
              }
            }
          }
          if (empty($ret)) {
            if ($id == 0) {
              // ใหม่
              $save['id'] = $index->id;
              $save['module_id'] = $index->module_id;
              $save['visited'] = 0;
              $this->db()->insert($this->getTableName('portfolio'), $save);
            } else {
              // แก้ไข
              $this->db()->update($this->getTableName('portfolio'), $id, $save);
            }
            // ส่งค่ากลับ
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'portfolio-setup', 'mid' => $index->module_id));
          }
        }
      }
    } else {
      $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}