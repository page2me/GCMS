<?php
/*
 * @filesource index/models/pagewrite.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Pagewrite;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Date;

/**
 * อ่าน/บันทึก ข้อมูลหน้าเพจ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านหน้าเพจ
   * id = 0 สร้างหน้าใหม่
   *
   * @param int $id
   * @param string $owner
   * @return object|null คืนค่า object ของข้อมูล ไม่พบคืนค่า null
   */
  public static function getIndex($id, $owner)
  {
    if (is_int($id)) {
      if (empty($id)) {
        // ใหม่
        $index = (object)array(
            'owner' => $owner,
            'id' => 0,
            'published' => 1,
            'module' => '',
            'topic' => '',
            'keywords' => '',
            'description' => '',
            'detail' => '',
            'last_update' => 0,
            'published_date' => Date::mktimeToSqlDate(),
            'language' => ''
        );
      } else {
        // แก้ไข
        $model = new static;
        $select = array(
          'I.id',
          'I.language',
          'D.topic',
          'D.keywords',
          'D.description',
          'D.detail',
          'I.last_update',
          'I.published',
          'I.published_date',
          'M.module',
          'M.owner'
        );
        $index = $model->db()->createQuery()
          ->select($select)
          ->from('index I')
          ->join('modules M', 'INNER', array(array('M.id', 'I.module_id')))
          ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'I.module_id'), array('D.language', 'I.language')))
          ->where(array(
            array('I.id', $id),
            array('I.index', 1)
          ))
          ->limit(1)
          ->execute();
        $index = sizeof($index) == 1 ? $index[0] : null;
      }
      return $index;
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
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $input = false;
        $module_id = 0;
        // index
        $index_save = array(
          'language' => strtolower(self::$request->post('language')->text()),
          'published' => self::$request->post('published')->toBoolean(),
          'published_date' => self::$request->post('published_date')->date()
        );
        // modules
        $module_save = array(
          'owner' => strtolower(self::$request->post('owner')->text()),
          'module' => strtolower(self::$request->post('module')->text())
        );
        // index_detail
        $detail_save = array(
          'language' => $index_save['language'],
          'topic' => self::$request->post('topic')->topic(),
          'keywords' => self::$request->post('keywords')->keywords(),
          'detail' => self::$request->post('detail')->detail(),
          'description' => self::$request->post('description')->description(),
        );
        $index_id = self::$request->post('id')->toInt();
        $detail_save['keywords'] = empty($detail_save['keywords']) ? self::$request->post('topic')->keywords(149) : $detail_save['keywords'];
        $detail_save['description'] = empty($detail_save['description']) ? self::$request->post('detail')->keywords(149) : $detail_save['description'];
        // model
        $model = new static;
        // ชื่อตาราง
        $table_index = $model->getFullTableName('index');
        $table_index_detail = $model->getFullTableName('index_detail');
        $table_modules = $model->getFullTableName('modules');
        if (!empty($index_id)) {
          // หน้าที่แก้ไข
          $index = $model->db()->createQuery()
            ->select('D.id', 'D.language', 'D.module_id')
            ->from('index I')
            ->join('index_detail D', 'INNER', array(array('D.id', 'I.id'), array('D.module_id', 'I.module_id'), array('D.language', 'I.language')))
            ->where(array('I.id', $index_id))
            ->limit(1)
            ->toArray()
            ->execute();
          $index = sizeof($index) == 1 ? $index[0] : false;
        }
        if ((!empty($index_id) && !$index) || !preg_match('/^[a-z]+$/', $module_save['owner']) || !is_dir(ROOT_PATH.'modules/'.$module_save['owner'])) {
          $ret['alert'] = Language::get('Unable to complete the transaction');
        } else {
          // ตรวจสอบค่าที่ส่งมา
          if ($module_save['owner'] === 'index') {
            // ตรวจสอบชื่อโมดูล
            if (empty($module_save['module'])) {
              $ret['ret_module'] = Language::get('Please fill in');
              $input = !$input ? 'module' : $input;
            } elseif (!preg_match('/^[a-z0-9]{1,}$/', $module_save['module'])) {
              $ret['ret_module'] = Language::get('English lowercase and number only');
              $input = !$input ? 'module' : $input;
            } elseif ((is_dir(ROOT_PATH.'modules/'.$module_save['module']) || is_dir(ROOT_PATH.'widgets/'.$module_save['module']) || is_dir(ROOT_PATH.$module_save['module']) || is_file(ROOT_PATH.$module_save['module'].'.php'))) {
              // เป็นชื่อโฟลเดอร์หรือชื่อไฟล์
              $ret['ret_module'] = Language::get('Invalid name');
              $input = !$input ? 'module' : $input;
            } else {
              // ค้นหาชื่อโมดูลซ้ำ
              $where = array(array('M.module', $module_save['module']));
              if ($index_id > 0) {
                $where[] = array('I.id', '!=', $index_id);
              }
              $query = $model->db()->createQuery()
                ->select('I.language', 'I.module_id')
                ->from('modules M')
                ->join('index I', 'INNER', array(array('I.module_id', 'M.id'), array('I.index', 1)))
                ->where($where);
              foreach ($query->toArray()->execute() as $item) {
                if (empty($detail_save['language'])) {
                  $ret['ret_module'] = str_replace(':name', Language::get('Module'), Language::get('This :name is already installed'));
                  $input = !$input ? 'module' : $input;
                } elseif (empty($item['language'])) {
                  $ret['ret_module'] = str_replace(':name', Language::get('Module'), Language::get('This :name is already installed'));
                  $input = !$input ? 'module' : $input;
                } elseif ($item['language'] == $detail_save['language']) {
                  $ret['ret_module'] = str_replace(':name', Language::get('Module'), Language::get('This :name is already installed'));
                  $input = !$input ? 'module' : $input;
                }
                $module_id = (int)$item['module_id'];
              }
              if (!$input) {
                $ret['ret_module'] = '';
              }
            }
          }
          // topic
          if (mb_strlen($detail_save['topic']) < 3) {
            $input = !$input ? 'topic' : $input;
          } else {
            // ค้นหาชื่อไตเติลซ้ำ
            $search = $model->db()->first($table_index_detail, array(
              array('topic', $detail_save['topic']),
              array('language', array('', $detail_save['language']))
            ));
            if ($search && (empty($index_id) || $index_id != $search->id)) {
              $ret['ret_topic'] = str_replace(':name', Language::get('Topic'), Language::get('This :name already exist'));
              $input = !$input ? 'topic' : $input;
            } else {
              $ret['ret_topic'] = '';
            }
          }
          if (!$input) {
            $index_save['ip'] = self::$request->getClientIp();
            $index_save['last_update'] = time();
            if (empty($index_id)) {
              // ใหม่
              if (empty($module_id)) {
                // โมดูลใหม่
                $class = ucfirst($module_save['owner']).'\Admin\Settings\Model';
                if (method_exists($class, 'defaultSettings')) {
                  $module_save['config'] = serialize($class::defaultSettings());
                }
                $module_id = $model->db()->insert($table_modules, $module_save);
              }
              $index_save['member_id'] = $login['id'];
              $index_save['create_date'] = $index_save['last_update'];
              $index_save['index'] = '1';
              $index_save['module_id'] = $module_id;
              $index_id = $model->db()->insert($table_index, $index_save);
              $detail_save['id'] = $index_id;
              $detail_save['module_id'] = $module_id;
              $model->db()->insert($table_index_detail, $detail_save);
            } else {
              // แก้ไข
              $model->db()->update($table_index, (int)$index['id'], $index_save);
              $model->db()->update($table_modules, (int)$index['module_id'], $module_save);
              $model->db()->update($table_index_detail, array(
                array('id', $index['id']),
                array('module_id', $index['module_id']),
                array('language', $index['language'])
                ), $detail_save);
            }
            // ส่งค่ากลับ
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = self::$request->getUri()->postBack('index.php', array('id' => $index_id, 'module' => 'pagewrite'));
          } else {
            // คืนค่า input ตัวแรกที่ error
            $ret['input'] = $input;
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
   * สำเนาหน้าเพจหรือโมดูลไปยังภาษาอื่น
   */
  public static function copy()
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        $id = self::$request->post('id')->toInt();
        $lng = self::$request->post('lng')->toString();
        // model
        $model = new static;
        // ชื่อตาราง
        $table_index = $model->getFullTableName('index');
        $table_index_detail = $model->getFullTableName('index_detail');
        // ตรวจสอบรายการที่เลือก
        $index = $model->db()->first($table_index, array(
          array('id', $id),
          array('index', 1)
        ));
        if ($index === false) {
          $ret['alert'] = Language::get('Sorry, Item not found It&#39;s may be deleted');
        } else {
          if ($index->language == '') {
            $ret['alert'] = Language::get('This entry is displayed in all languages');
          } else {
            // ตรวจสอบโมดูลซ้ำ
            $search = $model->db()->first($table_index, array(
              array('language', $lng),
              array('module_id', (int)$index->module_id)
            ));
            if ($search !== false) {
              $ret['alert'] = Language::get('This entry is in selected language');
            } else {
              $old_lng = $index->language;
              // อ่าน detail
              $detail = $model->db()->first($table_index_detail, array(
                array('id', (int)$index->id),
                array('module_id', (int)$index->module_id),
                array('language', $index->language)
              ));
              // เปลี่ยนรายการปัจจุบันเป็นรายการในภาษาใหม่
              $model->db()->update($table_index, $index->id, array('language' => $lng));
              $model->db()->update($table_index_detail, array(array('id', (int)$index->id), array('module_id', (int)$index->module_id), array('language', $old_lng)), array('language' => $lng));
              unset($index->id);
              // บันรายการเดิมเป็น ID ใหม่
              $detail->id = $model->db()->insert($table_index, $index);
              $model->db()->insert($table_index_detail, $detail);
              // คืนค่า
              $ret['alert'] = Language::get('Copy successfully, you can edit this entry');
            }
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