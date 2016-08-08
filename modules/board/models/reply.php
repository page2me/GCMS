<?php
/*
 * @filesource board/models/reply.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Reply;

use \Kotchasan\Http\Request;
use \Kotchasan\ArrayTool;
use \Gcms\Login;
use \Gcms\Gcms;
use \Kotchasan\Language;
use \Kotchasan\Validator;
use \Kotchasan\File;
use \Kotchasan\Antispam;

/**
 *  Model สำหรับบันทึกความคิดเห็น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * บันทึกความคิดเห็น
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
        $email = $request->post('reply_email')->topic();
        $password = $request->post('reply_password')->topic();
        $post = array(
          'detail' => $request->post('reply_detail')->textarea()
        );
        $index_id = $request->post('index_id')->toInt();
        $id = $request->post('reply_id')->toInt();
        // ตรวจสอบค่าที่ส่งมา
        $ret = array();
        $antispam = new Antispam($request->post('reply_antispamid')->toString());
        if (!$antispam->valid($request->post('reply_antispam')->toString())) {
          // Antispam ไม่ถูกต้อง
          $ret['ret_reply_antispam'] = 'this';
        } else {
          // อ่านข้อมูล
          $index = $this->get($id, $request->post('module_id')->toInt(), $index_id);
          if ($index) {
            // ผู้ดูแล
            $moderator = Gcms::canConfig($login, $index, 'moderator');
            // login ใช้ email และ password ของคน login
            if ($login) {
              $email = $login['email'];
              $password = $login['password'];
            }
            // true = guest โพสต์ได้
            $guest = in_array(-1, $index['can_reply']);
            // รายการไฟล์อัปโหลด
            $fileUpload = array();
            if (empty($index['img_upload_type'])) {
              // ไม่สามารถอัปโหลดได้ ต้องมีรายละเอียด
              $requireDetail = true;
            } else {
              // ต้องมีรายละเอียด ถ้าเป็นโพสต์ใหม่ หรือ แก้ไขและไม่มีรูป
              $requireDetail = ($id == 0 || ($id > 0 && empty($index['picture'])));
              foreach ($request->getUploadedFiles() as $item => $file) {
                if ($file->hasUploadFile()) {
                  $fileUpload[$item] = $file;
                  // ไม่ต้องมีรายละเอียด ถ้ามีการอัปโหลดรูปภาพมาด้วย
                  $requireDetail = false;
                }
              }
            }
          }
          if (!$index) {
            // ไม่พบรายการที่ต้องการ หรือไม่สามารถโพสต์ได้
            $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
          } elseif ($index['locked'] == 1 && !$moderator) {
            // บอร์ด lock (ผู้ดูแลสามารถ post ได้)
            $ret['alert'] = Language::get('Sorry, can not be processed. Because the topic is closed');
          } elseif (!empty($fileUpload) && !File::makeDirectory(ROOT_PATH.DATA_FOLDER.'board/')) {
            // ไดเรคทอรี่ไม่สามารถสร้างได้
            $ret['alert'] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'board/');
          } elseif ($post['detail'] == '' && $requireDetail) {
            // ไม่ได้กรอกรายละเอียด และ ไม่มีรูป
            $ret['ret_reply_detail'] = Language::get('Please fill in').' '.Language::get('Detail');
          } elseif ($id == 0) {
            // ใหม่
            if ($email == '') {
              // ไม่ได้กรอกอีเมล์
              $ret['ret_reply_email'] = Language::get('Please fill in').' '.Language::get('Email');
            } elseif ($password == '' && !$guest) {
              // สมาชิกเท่านั้น และ ไม่ได้กรอกรหัสผ่าน
              $ret['ret_reply_password'] = Language::get('Please fill in').' '.Language::get('Password');
            } elseif ($email != '' && $password != '') {
              $user = Login::checkMember($email, $password);
              if (is_string($user)) {
                if (Login::$login_input == 'password') {
                  $ret['ret_reply_password'] = $user;
                } else {
                  $ret['ret_reply_email'] = $user;
                }
              } elseif (!in_array($user['status'], $index['can_reply'])) {
                // ไม่สามารถแสดงความคิดเห็นได้
                $ret['ret_reply_email'] = Language::get('Sorry, you do not have permission to comment');
              } else {
                // สมาชิก สามารถแสดงความคิดเห็นได้
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
                $ret['ret_reply_password'] = Language::get('Please fill in').' '.Language::get('Password');
              } elseif (!Validator::email($email)) {
                // อีเมล์ไม่ถูกต้อง
                $ret['ret_reply_email'] = str_replace(':name', Language::get('Email'), Language::get('Invalid :name'));
              } else {
                // guest
                $post['member_id'] = 0;
                $post['email'] = $email;
              }
            } else {
              // สมาชิกเท่านั้น
              $ret['alert'] = Language::get('Members Only');
            }
          } elseif (!($index['member_id'] == $login['id'] || $moderator)) {
            // แก้ไข ไม่ใช่เจ้าของ และ ไม่ใช่ผู้ดูแล
            $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
          }
          if ($id == 0 && empty($ret) && $post['detail'] != '') {
            // ตรวจสอบโพสต์ซ้ำภายใน 1 วัน
            $search = $this->db()->createQuery()
              ->from('board_r')
              ->where(array(
                array('detail', $post['detail']),
                array('email', $post['email']),
                array('module_id', $index['module_id']),
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
          if (empty($ret) && !empty($index['img_upload_type'])) {
            foreach ($fileUpload as $item => $file) {
              $k = str_replace('reply_', '', $item);
              if (!$file->validFileExt($index['img_upload_type'])) {
                $ret['ret_'.$item] = Language::get('The type of file is invalid');
              } elseif ($file->getSize() > ($index['img_upload_size'] * 1024)) {
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
                  $file->moveTo(ROOT_PATH.DATA_FOLDER.'board/'.$post[$k]);
                  // ลบรูปภาพเก่า
                  if (!empty($index[$k]) && $index[$k] != $post[$k]) {
                    @unlink(ROOT_PATH.DATA_FOLDER.'board/'.$index[$k]);
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
            if ($id > 0) {
              // แก้ไข
              $this->db()->update($this->getFullTableName('board_r'), $id, $post);
              // คืนค่า
              $ret['alert'] = Language::get('Edit comment successfully');
            } else {
              // ใหม่
              $post['ip'] = $request->getClientIp();
              $post['index_id'] = $index['id'];
              $post['module_id'] = $index['module_id'];
              $id = $this->db()->insert($this->getFullTableName('board_r'), $post);
              // อัปเดทคำถาม
              $q['commentator'] = empty($post['sender']) ? $post['email'] : $post['sender'];
              $q['commentator_id'] = $post['member_id'];
              $q['comments'] = $index['comments'] + 1;
              $q['comment_id'] = $id;
              // อัปเดทสมาชิก
              if ($post['member_id'] > 0) {
                $this->db()->createQuery()->update('user')->set('`reply`=`reply`+1')->where($post['member_id'])->execute();
              }
              if ($index['category_id'] > 0) {
                // อัปเดทจำนวนเรื่อง และ ความคิดเห็น ในหมวด
                \Board\Admin\Write\Model::updateCategories((int)$index['module_id']);
              }
              // คืนค่า
              $ret['alert'] = Language::get('Thank you for your comment');
            }
            // อัปเดทคำถาม
            $q['comment_date'] = $mktime;
            $q['last_update'] = $mktime;
            $this->db()->update($this->getFullTableName('board_q'), $index['id'], $q);
            // เคลียร์ antispam
            $antispam->delete();
            // reload
            $location = WEB_URL."index.php?module=$index[module]&id=$index_id&visited=$mktime";
            $location .= self::$cfg->use_ajax == 1 ? "&to=R_$id" : "#R_$id";
            $ret['location'] = $location;
          }
        }
      }
      // คืนค่าเป็น JSON
      echo json_encode($ret);
    }
  }

  /**
   * อ่านข้อมูล ความคิดเห็น จาก $id
   *
   * @param int $id ID ของความคิดเห็น, 0 ถ้าเป็นความคิดเห็นใหม่
   * @param int $module_id ID ของโมดูล
   * @param int $index_id ID ของคำถาม
   * @return array|bool คืนค่าผลลัพท์ที่พบ ไม่พบข้อมูลคืนค่า false
   */
  private function get($id, $module_id, $index_id)
  {
    if ($id > 0) {
      // แก้ไขความคิดเห็น อ่านข้อมูลจาก ความคิดเห็น
      $index = $this->db()->createQuery()
        ->from('board_r R')
        ->join('modules M', 'INNER', array('M.id', 'R.module_id'))
        ->join('board_q Q', 'INNER', array('Q.id', 'R.index_id'))
        ->join('category C', 'LEFT', array(array('C.module_id', 'Q.module_id'), array('C.category_id', 'Q.category_id')))
        ->where(array(array('R.id', $id), array('R.index_id', $index_id), array('R.module_id', $module_id)))
        ->toArray()
        ->cacheOn()
        ->first('R.member_id', 'Q.id', 'Q.comments', 'R.module_id', 'R.picture', 'Q.can_reply', 'Q.locked', 'M.module', 'M.config mconfig', 'C.config', 'C.category_id');
    } else {
      // ความคิดเห็นใหม่ ตรวจสอบคำถาม โมดูลและหมวดของคำถาม
      $index = $this->db()->createQuery()
        ->from('board_q Q')
        ->join('modules M', 'INNER', array('M.id', 'Q.module_id'))
        ->join('category C', 'LEFT', array(array('C.module_id', 'Q.module_id'), array('C.category_id', 'Q.category_id')))
        ->where(array(array('Q.id', $index_id), array('Q.module_id', $module_id)))
        ->toArray()
        ->cacheOn()
        ->first('Q.id', 'Q.comments', 'Q.module_id', 'Q.can_reply', 'Q.locked', 'M.module', 'M.config mconfig', 'C.config', 'C.category_id');
    }
    if ($index) {
      // config จากโมดูล
      $index = ArrayTool::unserialize($index['mconfig'], $index);
      // config จากหมวด
      if (!empty($index['category_id'])) {
        $index = ArrayTool::unserialize($index['config'], $index);
      }
      unset($index['mconfig']);
      unset($index['config']);
    }
    return $index;
  }
}