<?php
/*
 * @filesource document/models/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Write;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\ArrayTool;
use \Kotchasan\Date;
use \Kotchasan\File;
use \Kotchasan\Http\UploadedFile;
use \Kotchasan\Database\Sql;

/**
 * บันทึกบทความ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * บันทึกบทความ
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
        // details
        $details = array();
        $alias_topic = '';
        $languages = Language::installedLanguage();
        foreach ($languages as $lng) {
          $topic = $request->post('topic_'.$lng)->topic();
          $alias = Gcms::aliasName($request->post('topic_'.$lng)->toString());
          $relate = $request->post('relate_'.$lng)->quote();
          $keywords = $request->post('keywords_'.$lng)->keywords();
          $description = $request->post('description_'.$lng)->description();
          if (!empty($topic)) {
            $save = array();
            $save['topic'] = $topic;
            $save['keywords'] = empty($keywords) ? $request->post('topic_'.$lng)->keywords(255) : $keywords;
            $save['description'] = empty($description) ? $request->post('details_'.$lng)->description(255) : $description;
            $save['detail'] = $request->post('details_'.$lng)->detail();
            $save['language'] = $lng;
            $save['relate'] = empty($relate) ? $save['keywords'] : $relate;
            $details[$lng] = $save;
            $alias_topic = empty($alias_topic) ? $alias : $alias_topic;
          }
        }
        $save = array(
          'alias' => Gcms::aliasName($request->post('alias')->toString()),
          'category_id' => $request->post('category_id')->toInt(),
          'create_date' => Date::sqlDateTimeToMktime($request->post('create_date')->date().' '.$request->post('create_time')->date()),
        );
        // id ที่แก้ไข
        $id = $request->post('id')->toInt();
        $module_id = $request->post('module_id')->toInt();
        // ตาราง index
        $table_index = $this->getTableName('index');
        // query builder
        $query = $this->db()->createQuery();
        if (empty($id)) {
          // ตรวจสอบโมดูล (ใหม่)
          $query->select('M.id module_id', 'M.module', 'M.config', '0 category_id', Sql::NEXT('id', $table_index, array('module_id', 'M.id'), 'id'))
            ->from('modules M')
            ->where(array(
              array('M.id', $module_id),
              array('M.owner', 'document'),
            ))
            ->limit(1);
        } else {
          // ตรวจสอบโมดูล หรือ เรื่องที่เลือก (แก้ไข)
          $query->select('I.id', 'I.module_id', 'M.module', 'I.category_id', 'M.config', 'I.picture', 'I.member_id')
            ->from('modules M')
            ->join('index I', 'INNER', array(array('I.module_id', 'M.id'), array('I.id', $id), array('I.index', '0')))
            ->where(array(
              array('M.id', $module_id),
              array('M.owner', 'document')
            ))
            ->limit(1);
        }
        $index = $query->toArray()->execute();
        if (empty($index)) {
          $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
        } else {
          $index = ArrayTool::unserialize($index[0]['config'], $index[0]);
          unset($index['config']);
          if (empty($id)) {
            // เขียนใหม่ตรวจสอบกับ can_write
            $canWrite = in_array($login['status'], $index['can_write']);
          } else {
            // แก้ไข ตรวจสอบเจ้าของ
            $canWrite = $index['member_id'] == $login['id'];
          }
          if ($canWrite) {
            // ตรวจสอบข้อมูลที่กรอก
            if (empty($details)) {
              $lng = reset($languages);
              $ret['ret_topic_'.$lng] = 'this';
            } else {
              foreach ($details as $lng => $values) {
                if (mb_strlen($values['topic']) < 3) {
                  $ret['ret_topic_'.$lng] = 'this';
                }
              }
            }
            // มีข้อมูลมาภาษาเดียวให้แสดงในทุกภาษา
            if (sizeof($details) == 1) {
              foreach ($details as $i => $item) {
                $details[$i]['language'] = '';
              }
            }
            // alias
            if ($save['alias'] == '') {
              $save['alias'] = $alias_topic;
            }
            if (in_array($save['alias'], Gcms::$MODULE_RESERVE) || is_dir(ROOT_PATH."modules/$save[alias]") || is_dir(ROOT_PATH."widgets/$save[alias]")) {
              // ชื่อสงวน หรือ ชื่อโฟลเดอร์
              $ret['ret_alias'] = 'this';
            } else {
              // ค้นหาชื่อเรื่องซ้ำ
              $search = $this->db()->first($table_index, array(
                array('alias', $save['alias']),
                array('language', array('', Language::name())),
                array('index', '0')
              ));
              if ($search && ($id == 0 || $id != $search->id)) {
                $ret['ret_alias'] = Language::replace('This :name already exist', array(':name' => Language::get('Alias')));
              }
            }
            if (empty($ret)) {
              // อัปโหลดไฟล์
              foreach ($request->getUploadedFiles() as $item => $file) {
                /* @var $file UploadedFile */
                if ($file->hasUploadFile()) {
                  if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'document/')) {
                    // ไดเรคทอรี่ไม่สามารถสร้างได้
                    $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'document/');
                  } else {
                    // อัปโหลด
                    $save[$item] = $item.'-'.$index['module_id'].'-'.$index['id'].'.'.$file->getClientFileExt();
                    try {
                      $file->cropImage($index['img_typies'], ROOT_PATH.DATA_FOLDER.'document/'.$save[$item], $index['icon_width'], $index['icon_height']);
                      if (!empty($index[$item]) && $index[$item] != $save[$item]) {
                        // ลบรูปภาพเก่า
                        @unlink(ROOT_PATH.DATA_FOLDER.'document/'.$index[$item]);
                      }
                    } catch (\Exception $exc) {
                      // ไม่สามารถอัปโหลดได้
                      $ret['ret_'.$item] = Language::get($exc->getMessage());
                    }
                  }
                }
              }
            }
            if ($save['category_id'] > 0 && ($id == 0 || $save['category_id'] != $index['category_id'])) {
              // ใหม่ หรือมีการเปลี่ยนหมวดหมู่ ใช้ค่ากำหนดจากหมวด
              $category = \Index\Category\Model::get($save['category_id'], $index['module_id']);
              if ($category) {
                $save['published'] = $category->published;
                $save['can_reply'] = $category->can_reply;
              }
            } elseif ($id == 0) {
              // ใหม่ ไม่มีหมวด ใช้ค่ากำหนดจากโมดูล
              $save['published'] = $index['published'];
              $save['can_reply'] = empty($index['can_reply']) ? 0 : 1;
            }
            if (empty($ret)) {
              $save['last_update'] = time();
              $save['index'] = 0;
              $save['ip'] = $request->getClientIp();
              if (empty($id)) {
                // ใหม่
                $save['show_news'] = '';
                $save['published_date'] = date('Y-m-d');
                $save['module_id'] = $index['module_id'];
                $save['member_id'] = $login['id'];
                $index['id'] = $this->db()->insert($table_index, $save);
              } else {
                // แก้ไข
                $this->db()->update($table_index, $index['id'], $save);
              }
              // details
              $index_detail = $this->getTableName('index_detail');
              $this->db()->delete($index_detail, array(array('id', $index['id']), array('module_id', $index['module_id'])), 0);
              foreach ($details AS $save1) {
                $save1['module_id'] = $index['module_id'];
                $save1['id'] = $index['id'];
                $this->db()->insert($index_detail, $save1);
              }
              // อัปเดทหมวดหมู่
              if ($save['category_id'] > 0) {
                \Document\Admin\Write\Model::updateCategories((int)$index['module_id']);
              }
              // ส่งค่ากลับ
              $ret['alert'] = Language::get('Saved successfully');
              $ret['location'] = 'back';
            }
          } else {
            $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
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