<?php
/*
 * @filesource board/models/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Write;

use \Kotchasan\Http\Request;
use \Kotchasan\ArrayTool;
use \Gcms\Login;
use \Gcms\Gcms;
use \Kotchasan\Language;
use \Kotchasan\Validator;
use \Kotchasan\File;
use \Kotchasan\Date;
use \Kotchasan\Antispam;

/**
 *  Model สำหรับบันทึกกระทู้
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * บันทึกกระทู้
   *
   * @param Request $request
   */
  public function save(Request $request)
  {
    if ($request->initSession() && $request->isReferer()) {
      $ret = array();
      // login
      $login = Login::isMember();
      if ($login && $login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // ค่าที่ส่งมา
        $email = $request->post('board_email')->topic();
        $password = $request->post('board_password')->topic();
        $post = array(
          'topic' => $request->post('board_topic')->topic(),
          'detail' => $request->post('board_detail')->textarea(),
          'category_id' => $request->post('board_category_id')->toInt()
        );
        $id = $request->post('board_id')->toInt();
        // ตรวจสอบค่าที่ส่งมา
        $antispam = new Antispam($request->post('board_antispamid')->toString());
        if (!$antispam->valid($request->post('board_antispam')->toString())) {
          // Antispam ไม่ถูกต้อง
          $ret['ret_board_antispam'] = 'this';
        } else {
          // อ่านข้อมูล
          $index = $this->get($id, $request->post('module_id')->toInt(), $post['category_id']);
          if ($index) {
            // ผู้ดูแล
            $moderator = Gcms::canConfig($login, $index, 'moderator');
            // login ใช้ email และ password ของคน login
            if ($login) {
              $email = $login['email'];
              $password = $login['password'];
            }
            // true = guest โพสต์ได้
            $guest = in_array(-1, $index->can_post);
            // รายการไฟล์อัปโหลด
            $fileUpload = array();
            if (empty($index->img_upload_type)) {
              // ไม่สามารถอัปโหลดได้ ต้องมีรายละเอียด
              $requireDetail = true;
            } else {
              // ต้องมีรายละเอียด ถ้าเป็นโพสต์ใหม่ หรือ แก้ไขและไม่มีรูป
              $requireDetail = ($id == 0 || ($id > 0 && empty($index->picture)));
              foreach ($request->getUploadedFiles() as $item => $file) {
                if ($file->hasUploadFile()) {
                  $fileUpload[$item] = $file;
                  // ไม่ต้องมีรายละเอียด ถ้ามีการอัปโหลดรูปภาพมาด้วย
                  $requireDetail = false;
                }
              }
            }
            // moderator สามารถ แก้ไขวันที่ได้
            if ($id > 0 && $moderator) {
              $post['create_date'] = Date::sqlDateTimeToMktime($request->post('board_create_date')->toString().' '.$request->post('board_create_time')->toString());
            }
          }
          if (!$index || empty($index->can_post)) {
            // ไม่พบรายการที่ต้องการ หรือไม่สามารถโพสต์ได้
            $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
          } elseif (!empty($fileUpload) && !File::makeDirectory(ROOT_PATH.DATA_FOLDER.'board/')) {
            // ไดเรคทอรี่ไม่สามารถสร้างได้
            $ret['alert'] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'board/');
          } elseif ($post['topic'] == '') {
            // คำถาม ไม่ได้กรอกคำถาม
            $ret['ret_board_topic'] = 'this';
          } elseif ($index->categories > 0 && $post['category_id'] == 0) {
            // คำถาม มีหมวด ไม่ได้เลือกหมวด
            $ret['ret_board_category_id'] = 'this';
          } elseif ($post['detail'] == '' && $requireDetail) {
            // ไม่ได้กรอกรายละเอียด และ ไม่มีรูป
            $ret['ret_board_detail'] = Language::get('Please fill in').' '.Language::get('Detail');
          } elseif ($id == 0) {
            // ใหม่
            if ($email == '') {
              // ไม่ได้กรอกอีเมล์
              $ret['ret_board_email'] = Language::get('Please fill in').' '.Language::get('Email');
            } elseif ($password == '' && !$guest) {
              // สมาชิกเท่านั้น และ ไม่ได้กรอกรหัสผ่าน
              $ret['ret_board_password'] = Language::get('Please fill in').' '.Language::get('Password');
            } elseif ($email != '' && $password != '') {
              $user = Login::checkMember($email, $password);
              if (is_string($user)) {
                if (Login::$login_input == 'password') {
                  $ret['ret_board_password'] = $user;
                } else {
                  $ret['ret_board_email'] = $user;
                }
              } elseif (!in_array($user['status'], $index->can_reply)) {
                // ไม่สามารถแสดงความคิดเห็นได้
                $ret['alert'] = Language::get('Sorry, you do not have permission to comment');
              } else {
                // สมาชิก สามารถโพสต์ได้
                $post['member_id'] = $user['id'];
                $post['email'] = $user['email'];
                $post['sender'] = empty($user['displayname']) ? $user['email'] : $user['displayname'];
              }
            } elseif ($guest) {
              // ตรวจสอบอีเมล์ซ้ำกับสมาชิก สำหรับบุคคลทั่วไป
              $search = $this->db()->createQuery()
                ->from('user')
                ->where(array('email', $email))
                ->first('id');
              if ($search) {
                // พบอีเมล์ ต้องการ password
                $ret['ret_board_password'] = Language::get('Please fill in').' '.Language::get('Password');
              } elseif (!Validator::email($email)) {
                // อีเมล์ไม่ถูกต้อง
                $ret['ret_board_email'] = str_replace(':name', Language::get('Email'), Language::get('Invalid :name'));
              } else {
                // guest
                $post['member_id'] = 0;
                $post['email'] = $email;
              }
            } else {
              // สมาชิกเท่านั้น
              $ret['alert'] = Language::get('Members Only');
            }
          } elseif (!($index->member_id == $login['id'] || $moderator)) {
            // แก้ไข ไม่ใช่เจ้าของ และ ไม่ใช่ผู้ดูแล
            $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
          }
          if ($id == 0 && empty($ret) && $post['detail'] != '') {
            // ตรวจสอบโพสต์ซ้ำภายใน 1 วัน
            $search = $this->db()->createQuery()
              ->from('board_q')
              ->where(array(
                array('topic', $post['topic']),
                array('detail', $post['detail']),
                array('email', $post['email']),
                array('module_id', $index->module_id),
                array('last_update', '>', time() - 86400),
              ))
              ->first('id');
            if ($search) {
              $ret['alert'] = Language::get('Your post is already exists. You do not need to post this.');
            }
          }
          // เวลาปัจจุบัน
          $mktime = time();
          // ไฟล์อัปโหลด
          if (empty($ret) && !empty($index->img_upload_type)) {
            foreach ($fileUpload as $item => $file) {
              $k = str_replace('board_', '', $item);
              if (!$file->validFileExt($index->img_upload_type)) {
                $ret['ret_'.$item] = Language::get('The type of file is invalid');
              } elseif ($file->getSize() > ($index->img_upload_size * 1024)) {
                $ret['ret_'.$item] = Language::get('The file size larger than the limit');
              } else {
                // อัปโหลดได้
                $ext = $file->getClientFileExt();
                $post[$k] = "$mktime.$ext";
                while (is_file(ROOT_PATH.DATA_FOLDER.'board/'.$post[$k])) {
                  $mmktime++;
                  $post[$k] = "$mktime.$ext";
                }
                try {
                  $file->cropImage($index->img_upload_type, ROOT_PATH.DATA_FOLDER.'board/thumb-'.$post[$k], $index->icon_width, $index->icon_height);
                  // ลบรูปภาพเก่า
                  if (!empty($index->$k) && $index->$k != $post[$k]) {
                    @unlink(ROOT_PATH.DATA_FOLDER.'board/thumb-'.$index->$k);
                  }
                } catch (\Exception $exc) {
                  // ไม่สามารถอัปโหลดได้
                  $ret['ret_'.$item] = Language::get($exc->getMessage());
                }
                try {
                  $file->moveTo(ROOT_PATH.DATA_FOLDER.'board/'.$post[$k]);
                  // ลบรูปภาพเก่า
                  if (!empty($index->$k) && $index->$k != $post[$k]) {
                    @unlink(ROOT_PATH.DATA_FOLDER.'board/'.$index->$k);
                  }
                } catch (\Exception $exc) {
                  // ไม่สามารถอัปโหลดได้
                  $ret['ret_'.$item] = Language::get($exc->getMessage());
                }
              }
            }
          }
          if (empty($ret)) {
            $post['last_update'] = $mktime;
            $post['can_reply'] = empty($index->can_reply) ? 0 : 1;
            if ($id > 0) {
              // แก้ไข
              $this->db()->update($this->getFullTableName('board_q'), $id, $post);
              // คืนค่า
              $ret['alert'] = Language::get('Edit post successfully');
            } else {
              // ใหม่
              $post['ip'] = $request->getClientIp();
              $post['create_date'] = $mktime;
              $post['module_id'] = $index->module_id;
              $id = $this->db()->insert($this->getFullTableName('board_q'), $post);
              // อัปเดทสมาชิก
              if ($post['member_id'] > 0) {
                $this->db()->createQuery()->update('user')->set('`post`=`post`+1')->where($post['member_id'])->execute();
              }
              // คืนค่า
              $ret['alert'] = Language::get('Thank you for your post');
            }
            if ($post['category_id'] > 0) {
              // อัปเดทจำนวนเรื่อง และ ความคิดเห็น ในหมวด
              \Board\Admin\Write\Model::updateCategories($index->module_id);
            }
            // เคลียร์ antispam
            $antispam->delete();
            // คืนค่า url ของบอร์ด
            $ret['location'] = WEB_URL.'index.php?module='.$index->module.'&id='.$id.'&visited='.$mktime;
          }
        }
      }
      // คืนค่าเป็น JSON
      echo json_encode($ret);
    }
  }

  /**
   * อ่านข้อมูล คำถาม
   *
   * @param int $id ID ของคำถาม, 0 ถ้าเป็นคำถามใหม่
   * @param int $module_id ID ของโมดูล
   * @param int $category_id หมวดหมู่ที่เลือก
   * @return object|bool คืนค่าผลลัพท์ที่พบ (Object) ไม่พบข้อมูลคืนค่า false
   */
  private function get($id, $module_id, $category_id)
  {
    $query = $this->db()->createQuery()->selectCount()->from('category G')->where(array('G.module_id', 'M.id'));
    if ($id > 0) {
      // แก้ไข
      $index = $this->db()->createQuery()
        ->from('board_q Q')
        ->join('modules M', 'INNER', array('M.id', 'Q.module_id'))
        ->join('category C', 'LEFT', array(array('C.module_id', 'M.id'), array('C.category_id', $category_id)))
        ->where(array(array('Q.id', $id), array('Q.module_id', $module_id)))
        ->toArray()
        ->cacheOn()
        ->first('Q.picture', 'Q.module_id', 'Q.member_id', 'M.module', 'C.category_id', 'M.config mconfig', 'C.config', array($query, 'categories'));
    } else {
      // ใหม่
      $index = $this->db()->createQuery()
        ->from('modules M')
        ->join('category C', 'LEFT', array(array('C.module_id', 'M.id'), array('C.category_id', $category_id)))
        ->where(array('M.id', $module_id))
        ->toArray()
        ->cacheOn()
        ->first('M.id module_id', 'M.module', 'C.category_id', 'M.config mconfig', 'C.config', array($query, 'categories'));
    }
    if ($index) {
      // config จากโมดูล
      $index = ArrayTool::unserialize($index['mconfig'], $index);
      // config จากหมวด แทนที่ config จากโมดูล
      if (!empty($index['category_id'])) {
        $index = ArrayTool::unserialize($index['config'], $index);
      }
      unset($index['mconfig']);
      unset($index['config']);
      return (object)$index;
    }
    return false;
  }
}