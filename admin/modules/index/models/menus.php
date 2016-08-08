<?php
/*
 * @filesource index/models/menus.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menus;

use \Kotchasan\Login;
use \Kotchasan\Language;

/**
 * โมเดลสำหรับแสดงรายการเมนู (menus.php)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Orm\Field
{
  /**
   * ชื่อตาราง
   *
   * @var string
   */
  protected $table = 'menus U';

  /**
   * query เมนูเรียงตามลำดับ menu_order
   *
   * @return array
   */
  public function getConfig()
  {
    return array(
      'select' => array(
        'U.menu_text',
        'U.alias',
        'U.id move_left',
        'U.id move_right',
        'U.published',
        'U.level',
        'U.language',
        'U.menu_tooltip',
        'U.accesskey',
        'U.index_id',
        'U.menu_url',
        'U.id',
        'M.module',
        'I.language ilanguage'
      ),
      'join' => array(
        array(
          'LEFT',
          'Index\Index\Model',
          array(
            array('I.id', 'U.index_id')
          )
        ),
        array(
          'LEFT',
          'Index\Pages\Model',
          array(
            array('M.id', 'I.module_id')
          )
        )
      ),
      'order' => array(
        'U.menu_order'
      )
    );
  }

  /**
   * รับค่าจาก action ของ table
   */
  public function action()
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $action = self::$request->post('action')->toString();
        // Model
        $model = new \Kotchasan\Model;
        $table_menus = $model->getFullTableName('menus');
        if ($action === 'move') {
          // move menu
          $data = self::$request->post('data')->toString();
          if (preg_match('/[0-9,]+/', $data)) {
            $ids = explode(',', $data);
            $query = $model->db()->createQuery()
              ->select('id', 'level', 'menu_text')
              ->from('menus')
              ->where(array('id', $ids));
            foreach ($query->toArray()->execute() AS $item) {
              $levels[$item['id']] = $item;
            }
            // reorder
            $save['menu_order'] = 0;
            $top_id = 0;
            foreach ($ids AS $i) {
              $save['menu_order'] ++;
              if ($top_id == 0) {
                $save['level'] = 0;
              } else {
                $save['level'] = max(0, min($levels[$top_id]['level'] + 1, $levels[$i]['level']));
              }
              $top_id = $i;
              // save
              $model->db()->update($table_menus, $i, $save);
              // คืนค่า
              $text = '';
              for ($b = 0; $b < $save['level']; $b++) {
                $text .= '&nbsp;&nbsp;&nbsp;';
              }
              $ret["r$i"] = ($text == '' ? '' : $text.'↳&nbsp;').$levels[$i]['menu_text']."|$save[level]|$i";
            }
          }
        } elseif ($action == 'move_left' || $action == 'move_right') {
          $top_level = 0;
          $id = self::$request->post('id')->toInt();
          // query menu ทั้งหมด
          $query = $model->db()->createQuery()
            ->select('id', 'level', 'menu_text')
            ->from('menus')
            ->where(array('parent', $model->db()->createQuery()->select('parent')->from('menus')->where($id)))
            ->order('menu_order');
          foreach ($query->toArray()->execute() as $a => $item) {
            $save = array();
            if ($a == 0) {
              $save['level'] = 0;
            } elseif ($item['id'] == $id) {
              if ($action == 'move_right') {
                $save['level'] = min($top_level + 1, $item['level'] + 1, 2);
              } else {
                $save['level'] = max(0, $item['level'] - 1);
              }
            } else {
              $save['level'] = max(0, min($top_level + 1, $item['level']));
            }
            $top_level = $save['level'];
            if ($save['level'] != $item['level']) {
              // save
              $model->db()->update($table_menus, $item['id'], $save);
            }
            // คืนค่า
            $text = '';
            for ($i = 0; $i < $save['level']; $i++) {
              $text .= '&nbsp;&nbsp;&nbsp;';
            }
            $ret["r$item[id]"] = ($text == '' ? '' : $text.'↳&nbsp;').$item['menu_text']."|$save[level]|$item[id]";
          }
        } elseif ($action === 'delete') {
          // ลบเมนู
          $id = self::$request->post('id')->toInt();
          $model->db()->delete($table_menus, $id);
          // คืนค่า
          $ret['delete_id'] = self::$request->post('src')->topic().'_'.$id;
          $ret['alert'] = Language::get('Deleted successfully');
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}