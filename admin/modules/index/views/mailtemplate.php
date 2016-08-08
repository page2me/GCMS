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
 * module=mailtemplate
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * ตารางแม่แบบอีเมล์
   *
   * @return string
   */
  public function render()
  {
    // ตารางแม่แบบอีเมล์
    $table = new DataTable(array(
      'model' => 'Index\Mailtemplate\Model',
      'hideColumns' => array('id', 'email_id', 'subject'),
      'onRow' => array($this, 'onRow'),
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
      'action' => 'index.php/index/model/mailtemplate/action',
      'actionConfirm' => 'confirmAction',
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