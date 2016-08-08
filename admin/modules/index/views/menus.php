<?php
/*
 * @filesource index/view/menus.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menus;

use \Kotchasan\DataTable;
use \Kotchasan\Language;

/**
 * ตารางรายการเมนู
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * module=menus
   *
   * @return string
   */
  public function render()
  {
    // menu ที่เลือก default คือ MAINMENU
    $parent = self::$request->get('parent')->toString();
    $installed_menus = Language::find('MENU_PARENTS', array('MAINMENU' => 'Main menu'));
    $menus = array_keys($installed_menus);
    $parent = in_array($parent, $menus) ? $parent : reset($menus);
    $this->toplvl = -1;
    // Uri
    $uri = self::$request->getUri();
    // ตารางรายการเมนู
    $table = new DataTable(array(
      /* model */
      'model' => 'Index\Menus\Model',
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('id', 'index_id', 'level', 'menu_url', 'ilanguage'),
      /* enable drag row */
      'dragColumn' => 4,
      /* table action */
      'action' => 'index.php/index/model/menus/action',
      'actionCallback' => 'indexActionCallback',
      'actionConfirm' => 'confirmAction',
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'menu_text' => array(
          'text' => '{LNG_Menu}'
        ),
        'move_left' => array(
          'text' => ''
        ),
        'move_right' => array(
          'text' => ''
        ),
        'alias' => array(
          'text' => '{LNG_Alias}'
        ),
        'published' => array(
          'text' => '{LNG_Status}',
          'class' => 'center'
        ),
        'language' => array(
          'text' => '{LNG_Language}',
          'class' => 'center'
        ),
        'menu_tooltip' => array(
          'text' => '{LNG_Tooltip}'
        ),
        'accesskey' => array(
          'text' => '{LNG_Accesskey}',
          'class' => 'center'
        ),
        'module' => array(
          'text' => '{LNG_Link}/{LNG_Module}'
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'published' => array(
          'class' => 'center'
        ),
        'language' => array(
          'class' => 'center'
        ),
        'accesskey' => array(
          'class' => 'center'
        )
      ),
      /* ปุ่มแสดงในแต่ละแถว */
      'buttons' => array(
        'edit' => array(
          'class' => 'icon-edit button green',
          'href' => $uri->createBackUri(array('module' => 'menuwrite', 'id' => ':id')),
          'text' => '{LNG_Edit}'
        ),
        'delete' => array(
          'class' => 'icon-delete button red',
          'id' => ':id',
          'text' => '{LNG_Delete}'
        )
      ),
      /* ปุ่มเพิ่ม */
      'addNew' => array(
        'class' => 'button green icon-plus',
        'href' => $uri->createBackUri(array('module' => 'menuwrite', 'id' => '0')),
        'text' => '{LNG_Add New} {LNG_Menu}'
      ),
      /* ฟิลเตอร์ของตาราง */
      'filters' => array(
        'parent' => array(
          'name' => 'parent',
          'text' => '{LNG_Choose}',
          'options' => $installed_menus,
          'value' => $parent
        )
      ),
    ));
    return $table->render();
  }

  /**
   * จัดรูปแบบการแสดงผลในแต่ละแถว
   *
   * @param array $item
   * @return array
   */
  public function onRow($item)
  {
    $url = empty($item['menu_url']) ? WEB_URL.'index.php?module='.$item['module'] : $item['menu_url'];
    $text = '';
    for ($i = 0; $i < $item['level']; $i++) {
      $text .= '&nbsp;&nbsp;&nbsp;';
    }
    $item['menu_text'] = (empty($text) ? '' : $text.'↳&nbsp;').$item['menu_text'];
    $item['move_left'] = '<a id=move_left_'.$item['move_left'].' title="{LNG_Move submenu to the top}" class='.($item['level'] == 0 ? 'hidden' : 'icon-move_left').'></a>';
    $item['move_right'] = '<a id=move_right_'.$item['move_right'].' title="{LNG_Move menu to submenu of the top}" class='.($item['level'] > $this->toplvl ? 'hidden' : 'icon-move_right').'></a>';
    $menu_publisheds = Language::get('MENU_PUBLISHEDS');
    $item['published'] = $menu_publisheds[$item['published']];
    $item['language'] = empty($item['language']) ? '' : '<img src="'.WEB_URL.'language/'.$item['language'].'.gif" alt="'.$item['language'].'">';
    if (empty($item['index_id'])) {
      $item['module'] = str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), $item['menu_url']);
    } else {
      $item['module'] .= empty($item['ilanguage']) ? '' : '&nbsp;<img src="'.WEB_URL.'language/'.$item['ilanguage'].'.gif" alt="'.$item['ilanguage'].'">';
    }
    $this->toplvl = $item['level'];
    return $item;
  }
}