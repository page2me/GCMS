<?php
/*
 * @filesource document/models/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Admin\Write;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\ArrayTool;
use \Kotchasan\Date;
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
        $tab = false;
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
          'can_reply' => $request->post('can_reply')->toBoolean(),
          'show_news' => $request->post('show_news', array())->text(),
          'published' => $request->post('published')->toBoolean(),
          'create_date' => Date::sqlDateTimeToMktime($request->post('create_date')->date().' '.$request->post('create_time')->date()),
          'published_date' => $request->post('published_date')->date(),
        );
        // id ที่แก้ไข
        $id = $request->post('id')->toInt();
        $module_id = $request->post('module_id')->toInt();
        // query builder
        $query = $this->db()->createQuery();
        if (empty($id)) {
          // ตรวจสอบโมดูล (ใหม่)
          $query->select('M.id module_id', 'M.module', 'M.config', $this->buildNext('id', 'index', array('module_id', 'M.id'), 'id'))
            ->from('modules M')
            ->where(array(
              array('M.id', $module_id),
              array('M.owner', 'document'),
            ))
            ->limit(1);
        } else {
          // ตรวจสอบโมดูล หรือ เรื่องที่เลือก (แก้ไข)
          $query->select('I.id', 'I.module_id', 'M.module', 'M.config', 'I.picture', 'I.member_id')
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
            // แก้ไข ตรวจสอบเจ้าของหรือ ผู้ดูแล
            $canWrite = ($index['member_id'] == $login['id'] || in_array($login['status'], $index['moderator']));
          }
          if ($canWrite) {
            // ตรวจสอบข้อมูลที่กรอก
            if (empty($details)) {
              $lng = reset($languages);
              $ret['ret_topic_'.$lng] = 'this';
              $tab = !$tab ? 'detail_'.$lng : $tab;
            } else {
              foreach ($details as $lng => $values) {
                if (mb_strlen($values['topic']) < 3) {
                  $ret['ret_topic_'.$lng] = 'this';
                  $tab = !$tab ? 'detail_'.$lng : $tab;
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
              $tab = !$tab ? 'options' : $tab;
            } else {
              // ค้นหาชื่อเรื่องซ้ำ
              $search = $this->db()->first($this->getFullTableName('index'), array(
                array('alias', $save['alias']),
                array('language', array('', Language::name())),
                array('index', '0')
              ));
              if ($search && ($id == 0 || $id != $search->id)) {
                $ret['ret_alias'] = Language::replace('This :name already exist', array(':name' => Language::get('Alias')));
                $tab = !$tab ? 'options' : $tab;
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
                    $tab = !$tab ? 'options' : $tab;
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
                      $tab = !$tab ? 'options' : $tab;
                    }
                  }
                }
              }
            }
            if (empty($ret)) {
              $save['last_update'] = time();
              $save['index'] = 0;
              $save['ip'] = $request->getClientIp();
              $show_news = array();
              foreach ($save['show_news'] as $item) {
                $show_news[] = "$item=1";
              }
              $save['show_news'] = implode("\n", $show_news);
              if (empty($id)) {
                // ใหม่
                $save['module_id'] = $index['module_id'];
                $save['member_id'] = $login['id'];
                $index['id'] = $this->db()->insert($this->getFullTableName('index'), $save);
              } else {
                // แก้ไข
                $this->db()->update($this->getFullTableName('index'), $index['id'], $save);
              }
              // details
              $index_detail = $this->getFullTableName('index_detail');
              $this->db()->delete($index_detail, array(array('id', $index['id']), array('module_id', $index['module_id'])), 0);
              foreach ($details AS $save1) {
                $save1['module_id'] = $index['module_id'];
                $save1['id'] = $index['id'];
                $this->db()->insert($index_detail, $save1);
              }
              // อัปเดทหมวดหมู่
              if ($save['category_id'] > 0) {
                self::updateCategories((int)$index['module_id']);
              }
              // ส่งค่ากลับ
              $ret['alert'] = Language::get('Saved successfully');
              $ret['location'] = $request->getUri()->postBack('index.php', array('mid' => $index['module_id'], 'module' => 'document-setup'));
            } elseif ($tab) {
              $ret['tab'] = $tab;
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

  /**
   * อ่านบทความที่ $id หรือ $alias.
   *
   * @param int $module_id ของโมดูล
   * @param int $id ID ของบทความ
   * @param int $category_id ID ของหมวดหมู่
   * @return object|null คืนค่าข้อมูล object ไม่พบคืนค่า null
   */
  public static function get($module_id, $id, $category_id)
  {
    if (is_int($module_id)) {
      // model
      $model = new static();
      $query = $model->db()->createQuery();
      if (empty($id)) {
        // ใหม่ ตรวจสอบโมดูล
        $query->select('M.id module_id', 'M.owner', 'M.module', 'M.config mconfig', 'C.config', 'C.category_id')
          ->from('modules M')
          ->join('category C', 'LEFT', array(array('C.category_id', $category_id), array('C.module_id', 'M.id')))
          ->where(array(
            array('M.id', $module_id),
            array('M.owner', 'document'),
        ));
      } else {
        // แก้ไข ตรวจสอบรายการที่เลือก
        $query->select('I.*', 'M.owner', 'M.module', 'M.config mconfig', 'C.config')
          ->from('index I')
          ->join('modules M', 'INNER', array(array('M.id', 'I.module_id'), array('M.owner', 'document')))
          ->join('index D', 'INNER', array(array('D.module_id', 'I.module_id'), array('D.index', 1), array('D.language', array('', Language::name()))))
          ->join('category C', 'LEFT', array(array('C.category_id', 'I.category_id'), array('C.module_id', 'I.module_id')))
          ->where(array(
            array('I.id', $id),
            array('I.index', 0),
        ));
      }
      $result = $query->limit(1)->toArray()->execute();
      if (sizeof($result) == 1) {
        $result = ArrayTool::unserialize($result[0]['mconfig'], $result[0], empty($id));
        unset($result['config']);
        unset($result['mconfig']);
        if (empty($id)) {
          $result['id'] = 0;
          $result['create_date'] = time();
          $result['show_news'] = '';
          $result['alias'] = '';
          $result['published_date'] = Date('Y-m-d H:i:s', $result['create_date']);
        }
        return (object)$result;
      }
    }
    return null;
  }

  /**
   * อ่านรายละเอียด (detail) ของบทความตามภาษา
   *
   * @param int $module_id
   * @param int $id
   * @param string $lng
   * @return array
   */
  public static function details($module_id, $id, $lng)
  {
    $result = array();
    if (is_int($module_id) && $module_id > 0) {
      // model
      $model = new static();
      $query = $model->db()
        ->createQuery()
        ->select('language', 'topic', 'keywords', 'relate', 'description', 'detail')
        ->from('index_detail')
        ->where(array(
          array('id', $id),
          array('module_id', $module_id),
        ))
        ->toArray();
      foreach ($query->execute() as $i => $item) {
        $item['language'] = ($i == 0 && $item['language'] == '') ? $lng : $item['language'];
        $result[$item['language']] = (object)$item;
      }
    }
    return $result;
  }

  /**
   * อัปเดทจำนวนบทความและความคิดเห็นในหมวดหมู่
   *
   * @param int $module_id
   */
  public static function updateCategories($module_id)
  {
    if (is_int($module_id) && $module_id > 0) {
      $model = new static;
      $sql1 = $model->db()->createQuery()->selectCount()->from('index')->where(array(
        array('category_id', 'C.category_id'),
        array('module_id', 'C.module_id'),
        array('index', '0')
      ));
      $sql2 = $model->db()->createQuery()->select('id')->from('index')->where(array(
        array('category_id', 'C.category_id'),
        array('module_id', 'C.module_id'),
        array('index', '0')
      ));
      $sql3 = $model->db()->createQuery()->selectCount()->from('comment')->where(array(
        array('index_id', 'IN', $sql2),
        array('module_id', 'C.module_id')
      ));
      $model->db()->createQuery()->update('category C')->set(array(
        'C.c1' => $sql1,
        'C.c2' => $sql3
      ))->where(array('C.module_id', $module_id))->execute();
    }
  }
}