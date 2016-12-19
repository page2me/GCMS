<?php
/*
 * @filesource index/views/mailtemplate.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Mailtemplate;

use \Kotchasan\DataTable;

/**
 * ตารางแม่แบบอีเมล์
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{

  /**
   * module=mailtemplate
   *
   * @return string
   */
  public function render()
  {
    // ตารางแม่แบบอีเมล์
    $table = new DataTable(array(
      /* Model */
      'model' => 'Index\Mailtemplate\Model',
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('id', 'email_id', 'subject'),
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/index/model/mailtemplate/action',
      'actionCallback' => 'indexActionCallback',
      'actionConfirm' => 'confirmAction',
      /* ฟังก์ชั่นตรวจสอบการแสดงผลปุ่มในแถว */
      'onCreateButton' => array($this, 'onCreateButton'),
      'headers' => array(
        'name' => array(
          'text' => '{LNG_Name}'
        ),
        'language' => array(
          'text' => '{LNG_Language}',
          'class' => 'center'
        ),
        'module' => array(
          'text' => '{LNG_Module}',
          'class' => 'center'
        )
      ),
      'cols' => array(
        'language' => array(
          'class' => 'center'
        ),
        'module' => array(
          'class' => 'center'
        )
      ),
      'buttons' => array(
        'edit' => array(
          'class' => 'icon-edit button green',
          'href' => self::$request->getUri()->withParams(array('module' => 'mailwrite', 'id' => ':id'), true),
          'text' => '{LNG_Edit}'
        ),
        'delete' => array(
          'class' => 'icon-delete button red',
          'id' => ':id',
          'text' => '{LNG_Delete}'
        )
      )
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
    $item['name'] = $item['module'] == 'mailmerge' ? $item['subject'] : $item['name'];
    $item['language'] = empty($item['language']) ? '' : '<img src="'.WEB_URL.'language/'.$item['language'].'.gif" alt="'.$item['language'].'">';
    return $item;
  }

  /**
   * ฟังกชั่นตรวจสอบว่าสามารถสร้างปุ่มได้หรือไม่
   *
   * @param array $item
   * @return array
   */
  public function onCreateButton($btn, $attributes, $items)
  {
    return $btn != 'delete' || $items['email_id'] == 0 ? $attributes : false;
  }
}