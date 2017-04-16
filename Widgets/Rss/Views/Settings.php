<?php
/**
 * @filesource Widgets/Rss/Views/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Rss\Views;

use \Kotchasan\Language;
use \Kotchasan\DataTable;

/**
 * โมดูลสำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Settings extends \Gcms\Adminview
{
  private $publisheds;

  /**
   * module=Rss-settings
   *
   * @return string
   */
  public function render()
  {
    $this->publisheds = Language::get('PUBLISHEDS');
    // Uri
    $uri = self::$request->getUri();
    // name
    $typies = array('' => '{LNG_all items}');
    $actions = array();
    foreach (Language::get('PUBLISHEDS') as $key => $value) {
      $actions['published_'.$key] = $value;
    }
    $actions['delete'] = '{LNG_Delete}';
    // ตาราง
    if (isset(self::$cfg->rss_tabs)) {
      foreach (self::$cfg->rss_tabs as $k => $vs) {
        self::$cfg->rss_tabs[$k]['id'] = $k;
      }
    } else {
      self::$cfg->rss_tabs = array();
    }
    // ตาราง
    $table = new DataTable(array(
      'datas' => self::$cfg->rss_tabs,
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('id', 'cols'),
      /* enable drag row */
      'dragColumn' => 1,
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/Widgets/Rss/Models/Action/get',
      'actionCallback' => 'indexActionCallback',
      'actionConfirm' => 'confirmAction',
      'actions' => array(
        array(
          'id' => 'action',
          'class' => 'ok',
          'text' => '{LNG_With selected}',
          'options' => array('delete' => '{LNG_Delete}')
        )
      ),
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'url' => array(
          'text' => '{LNG_URL}'
        ),
        'topic' => array(
          'text' => '{LNG_Topic}'
        ),
        'index' => array(
          'text' => '{LNG_ID}',
          'class' => 'center'
        ),
        'rows' => array(
          'text' => '{LNG_Rows} * {LNG_Cols}',
          'class' => 'center'
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'index' => array(
          'class' => 'center'
        ),
        'rows' => array(
          'class' => 'center'
        )
      ),
      /* ปุ่มแสดงในแต่ละแถว */
      'buttons' => array(
        'edit' => array(
          'class' => 'icon-edit button green',
          'href' => $uri->createBackUri(array('module' => 'Rss-write', 'id' => ':id')),
          'text' => '{LNG_Edit}'
        )
      ),
      /* ปุ่มเพิ่ม */
      'addNew' => array(
        'class' => 'button green icon-plus',
        'href' => $uri->createBackUri(array('module' => 'Rss-write')),
        'text' => '{LNG_Add New} {LNG_RSS Tab}'
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
    $item['url'] = '<a href="'.$item['url'].'" target=_blank>'.$item['url'].'</a>';
    $item['rows'] = $item['rows'].' * '.$item['cols'];
    return $item;
  }
}