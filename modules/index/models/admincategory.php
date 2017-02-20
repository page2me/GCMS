<?php
/*
 * @filesource index/models/admincategory.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Admincategory;

use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\ArrayTool;

/**
 * อ่านข้อมูลหมวดหมู่ (Backend)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลหมวดหมู่ทั้งหมด (admin)
   *
   * @param int $module_id
   * @param boolean $all true (default) คืนค่าทุกรายการ, false คืนค่าเฉพาะรายการที่เผยแพร่
   * @return array คืนค่าแอเรย์ของ Object ไม่มีคืนค่าแอเรย์ว่าง
   */
  public static function all($module_id, $all = true)
  {
    $result = array();
    if (is_int($module_id) && $module_id > 0) {
      $model = new static;
      $where = array(
        array('module_id', $module_id)
      );
      if (!$all) {
        $where[] = array('published', '1');
      }
      $query = $model->db()->createQuery()
        ->select('id', 'module_id', 'category_id', 'group_id', 'published', '0 can_reply', 'topic', 'detail', 'config', 'icon', 'c1', 'c2')
        ->from('category')
        ->where($where)
        ->order('category_id')
        ->toArray();
      foreach ($query->execute() as $item) {
        $item = ArrayTool::unserialize($item['config'], $item);
        unset($item['config']);
        $result[$item['category_id']] = $item;
      }
    }
    return $result;
  }

  /**
   * action ของตารางหมวดหมู่
   */
  public function action()
  {
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isMember()) {
      if ($login['email'] == 'demo') {
        echo Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $id = self::$request->post('id')->toString();
        $value = self::$request->post('value')->toInt();
        if (preg_match('/^category_([0-9]+)$/', $id, $match)) {
          $action = 'category';
          $module_id = (int)$match[1];
        } else {
          $action = self::$request->post('action')->toString();
          $module_id = self::$request->post('mid')->toInt();
        }
        // ตรวจสอบโมดูล
        $index = false;
        if (!empty($module_id)) {
          $index = \Index\Adminmodule\Model::get(null, $module_id);
        }
        if ($action === 'category') {
          // อ่านข้อมูลหมวดหมู่ตอนเขียนบทความ
          $category = $this->db()->first($this->getTableName('category'), array(
            array('category_id', (int)$value),
            array('module_id', (int)$index->module_id)
          ));
          if ($category) {
            $config = @unserialize($category->config);
            if (!is_array($config)) {
              $config = array('can_reply' => 0);
            }
            $ret = array(
              'can_reply' => empty($config['can_reply']) ? 0 : 1,
              'published' => empty($category->published) ? 0 : 1
            );
          }
        } elseif ($index && Gcms::canConfig($login, $index, 'can_config')) {
          if ($action === 'delete' && preg_match('/^[0-9,]+$/', $id)) {
            // ลบหมวด
            $query = $this->db()->createQuery()
              ->select('id', 'icon')
              ->from('category')
              ->where(array(
                array('id', explode(',', $id)),
                array('module_id', $index->module_id)
              ))
              ->toArray();
            $ids = array();
            foreach ($query->execute() as $item) {
              $ids[] = $item['id'];
              foreach (ArrayTool::unserialize($item['icon']) as $icon) {
                @unlink(ROOT_PATH.DATA_FOLDER.$item['owner'].'/'.$icon);
              }
            }
            if (!empty($ids)) {
              // ลบหมวดหมู่
              $this->db()->createQuery()->delete('category', array(
                array('id', $ids),
                array('module_id', $index->module_id)
              ))->execute();
            }
            // คืนค่า
            $ret['location'] = 'reload';
          } else {
            $category = $this->db()->first($this->getTableName('category'), array(
              array('id', (int)$id),
              array('module_id', (int)$index->module_id)
            ));
            if ($category) {
              if ($action === 'categoryid') {
                // แก้ไข category_id หน้ารายการหมวดหมู่
                // ค้นหาหมวดหมู่ซ้ำ
                $search = $this->db()->createQuery()
                  ->from('category')
                  ->where(array(
                    array('module_id', (int)$index->module_id),
                    array('category_id', $value)
                  ))
                  ->first('id');
                if ($search) {
                  // มี category_id อยู่ก่อนแล้วคืนค่ารายการเดิม
                  $ret['categoryid_'.$index->module_id.'_'.$category->id] = $category->category_id;
                } else {
                  // save
                  $this->db()->createQuery()
                    ->update('category')
                    ->set(array('category_id' => $value))
                    ->where((int)$category->id)
                    ->execute();
                  // คืนค่ารายการใหม่
                  $ret['categoryid_'.$index->module_id.'_'.$category->id] = $value;
                }
              } elseif ($action === 'published' || $action === 'can_reply') {
                // เผยแพร่, การแสดงความคิดเห็น
                if ($action === 'can_reply') {
                  $config = @unserialize($category->config);
                  if (!is_array($config)) {
                    $config = array();
                  }
                  $config['can_reply'] = empty($config['can_reply']) ? 1 : 0;
                  $save = array('config' => serialize($config));
                  // คืนค่า
                  $ret['elem'] = 'can_reply_'.$category->id;
                  $lng = Language::get('REPLIES');
                  $ret['title'] = $lng[$config['can_reply']];
                  $ret['class'] = 'icon-reply reply'.$config['can_reply'];
                } else {
                  $save = array('published' => $category->published == 1 ? 0 : 1);
                  // คืนค่า
                  $ret['elem'] = 'published_'.$category->id;
                  $lng = Language::get('PUBLISHEDS');
                  $ret['title'] = $lng[$save['published']];
                  $ret['class'] = 'icon-published'.$save['published'];
                }
                $this->db()->update($this->getTableName('category'), $category->id, $save);
              }
            }
          }
        }
        // คืนค่าเป็น JSON
        if (!empty($ret)) {
          echo json_encode($ret);
        }
      }
    } else {
      echo Language::get('Unable to complete the transaction');
    }
  }
}