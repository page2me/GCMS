<?php
/*
 * @filesource board/models/admin/categorywrite.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Admin\Categorywrite;

use \Kotchasan\ArrayTool;
use \Kotchasan\Login;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\File;

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
   * อ่านข้อมูลหมวดหมู่
   *
   * @param int $module_id
   * @param int $id
   * @return Object ถ้าไม่พบคืนค่า null
   */
  public static function get($module_id, $id)
  {
    if (is_int($module_id) && $module_id > 0) {
      $model = new static;
      if ($id == 0) {
        // ใหม่, ตรวจสอบโมดูลที่เรียก
        $select = array(
          '0 id',
          'M.id module_id',
          'M.module',
          'M.config mconfig',
          "'' topic",
          "'' detail",
          "'' icon",
          '1 published',
          $model->buildNext('category_id', 'category', array('module_id', 'M.id'))
        );
        $index = $model->db()->createQuery()
          ->from('modules M')
          ->where(array(array('M.id', $module_id), array('M.owner', 'board')))
          ->toArray()
          ->first($select);
      } else {
        // แก้ไข ตรวจสอบโมดูลและหมวดที่เลือก
        $index = $model->db()->createQuery()
          ->from('category C')
          ->join('modules M', 'INNER', array(array('M.id', 'C.module_id'), array('M.owner', 'board')))
          ->where(array(array('C.id', $id), array('C.module_id', $module_id)))
          ->toArray()
          ->first('C.*', 'M.module', 'M.config mconfig');
      }
      if ($index) {
        // การเผยแพร่จากหมวด
        $published = $index['published'];
        // config จาก module
        $index = ArrayTool::unserialize($index['mconfig'], $index);
        unset($index['mconfig']);
        // config จากหมวด
        if (isset($index['config'])) {
          $index = ArrayTool::unserialize($index['config'], $index);
          unset($index['config']);
        }
        $index['published'] = $published;
        return (object)$index;
      }
    }
    return null;
  }

  /**
   * บันทึกหมวดหมู่
   */
  public function save()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $save = array(
          'published' => 1,
          'config' => array(
            'img_upload_type' => self::$request->post('img_upload_type', array())->toString(),
            'img_upload_size' => self::$request->post('img_upload_size', array())->toInt(),
            'img_law' => self::$request->post('img_law')->toBoolean(),
            'can_post' => self::$request->post('can_post', array())->toInt(),
            'can_reply' => self::$request->post('can_reply', array())->toInt(),
            'can_view' => self::$request->post('can_view', array())->toInt(),
            'moderator' => self::$request->post('moderator', array())->toInt()
          )
        );
        $id = self::$request->post('id')->toInt();
        $module_id = self::$request->post('module_id')->toInt();
        $category_id = self::$request->post('category_id')->toInt();
        $q1 = $this->db()->createQuery()
          ->select('id')
          ->from('category')
          ->where(array(array('category_id', $category_id), array('module_id', 'M.id')));
        if ($id > 0) {
          $select = array(
            'C.id',
            'C.module_id',
            'C.icon',
            'C.config',
            'M.config mconfig',
            array($q1, 'cid')
          );
          $index = $this->db()->createQuery()
            ->from('category C')
            ->join('modules M', 'INNER', array('M.id', 'C.module_id'))
            ->where(array(array('C.id', $id), array('C.module_id', $module_id), array('M.owner', 'board')))
            ->toArray()
            ->first($select);
        } else {
          // ใหม่, ตรวจสอบโมดูลที่เรียก
          $select = array(
            'M.id module_id',
            '"" icon',
            'M.config mconfig',
            $this->buildNext('id', 'category'),
            array($q1, 'cid')
          );
          $index = $this->db()->createQuery()
            ->from('modules M')
            ->where(array(array('M.id', $module_id), array('M.owner', 'board')))
            ->toArray()
            ->first($select);
        }
        if ($index === false) {
          $ret['alert'] = Language::get('Sorry, Item not found It&#39;s may be deleted');
        } else {
          // config จาก module
          $index = ArrayTool::unserialize($index['mconfig'], $index);
          if (Gcms::canConfig($login, $index, 'can_config')) {
            unset($index['mconfig']);
            $topic = array();
            foreach (self::$request->post('topic')->topic() as $key => $value) {
              if ($value != '') {
                $topic[$key] = $value;
              }
            }
            $detail = array();
            foreach (self::$request->post('detail')->topic() as $key => $value) {
              if ($value != '') {
                $detail[$key] = $value;
              }
            }
            // ตรวจสอบค่าที่ส่งมา
            $input = false;
            if ($category_id == 0) {
              $input = 'category_id';
            } elseif ($index['cid'] > 0 && $index['cid'] != $index['id']) {
              $input = 'category_id';
              $ret['ret_category_id'] = str_replace(':name', Language::get('ID'), Language::get('This :name already exist'));
            } elseif (empty($topic)) {
              $input = 'topic_'.Language::name();
              $ret['ret_'.$input] = Language::get('Please fill in');
            } elseif (empty($detail)) {
              $input = 'detail_'.Language::name();
              $ret['ret_'.$input] = Language::get('Please fill in');
            } else {
              // อัปโหลดไฟล์
              $icon = ArrayTool::unserialize($index['icon']);
              foreach (self::$request->getUploadedFiles() as $item => $file) {
                if ($file->hasUploadFile()) {
                  if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'board/')) {
                    // ไดเรคทอรี่ไม่สามารถสร้างได้
                    $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'board/');
                    $input = !$input ? $item : $input;
                  } elseif (!$file->validFileExt(array('jpg', 'gif', 'png'))) {
                    $ret['ret_'.$item] = Language::get('The type of file is invalid');
                    $input = !$input ? $item : $input;
                  } else {
                    $old_icon = empty($icon[$item]) ? '' : $icon[$item];
                    $icon[$item] = "cat-$item-$index[id].".$file->getClientFileExt();
                    try {
                      $file->moveTo(ROOT_PATH.DATA_FOLDER.'board/'.$icon[$item]);
                      if ($old_icon != $icon[$item]) {
                        @unlink(ROOT_PATH.DATA_FOLDER.'board/'.$old_icon);
                      }
                    } catch (\Exception $exc) {
                      // ไม่สามารถอัปโหลดได้
                      $ret['ret_icon_'.$item] = Language::get($exc->getMessage());
                      $input = !$input ? 'icon_'.$item : $input;
                    }
                  }
                }
              }
              if (!empty($icon)) {
                $save['icon'] = Gcms::array2Ser($icon);
              }
            }
            if (!$input) {
              $save['category_id'] = $category_id;
              $save['topic'] = Gcms::array2Ser($topic);
              $save['detail'] = Gcms::array2Ser($detail);
              $save['config']['can_post'][] = 1;
              $save['config']['can_reply'][] = 1;
              $save['config']['can_view'][] = 1;
              $save['config']['moderator'][] = 1;
              $save['config'] = serialize($save['config']);
              if ($id == 0) {
                // ใหม่
                $save['module_id'] = $index['module_id'];
                $this->db()->insert($this->getFullTableName('category'), $save);
              } else {
                // แก้ไข
                $this->db()->update($this->getFullTableName('category'), $id, $save);
              }
              // อัปเดทจำนวนเรื่อง และ ความคิดเห็น ในหมวด
              \Board\Admin\Write\Model::updateCategories((int)$index['module_id']);
              // ส่งค่ากลับ
              $ret['alert'] = Language::get('Saved successfully');
              $ret['location'] = self::$request->getUri()->postBack('index.php', array('id' => $index['module_id'], 'module' => 'board-category'));
            } else {
              $ret['input'] = $input;
            }
          } else {
            $ret['alert'] = Language::get('Unable to complete the transaction');
          }
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}