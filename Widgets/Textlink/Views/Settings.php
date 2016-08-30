<?php
/*
 * @filesource Widgets/Textlink/Views/Settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Textlink\Views;

use \Kotchasan\Language;
use \Kotchasan\DataTable;
use \Kotchasan\Date;

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
   * module=Textlink-settings
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
    $table = new DataTable(array(
      /* Model */
      'model' => 'Widgets\Textlink\Models\Settings',
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* รายชื่อฟิลด์ที่ query (ถ้าแตกต่างจาก Model) */
      'fields' => array(
        'id',
        'name',
        'description',
        'published',
        'type',
        'url',
        'text',
        'width',
        'height',
        'publish_start',
        'publish_end',
        'link_order'
      ),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('id', 'type', 'height', 'link_order'),
      /* เรียงลำดับ */
      'sort' => 'name, link_order ASC',
      /* enable drag row */
      'dragColumn' => 1,
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/Widgets/Textlink/Models/Action/get',
      'actionCallback' => 'indexActionCallback',
      'actionConfirm' => 'confirmAction',
      'actions' => array(
        array(
          'id' => 'action',
          'class' => 'ok',
          'text' => '{LNG_With selected}',
          'options' => $actions
        )
      ),
      /* ตัวเลือกการแสดงผลที่ส่วนหัว */
      'filters' => array(
        'name' => array(
          'name' => 'name',
          'text' => '{LNG_Type of link}',
          'options' => \Widgets\Textlink\Models\Index::getTypies(),
          'default' => '',
          'value' => self::$request->get('name')->topic()
        )
      ),
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'name' => array(
          'text' => '{LNG_Name}'
        ),
        'description' => array(
          'text' => '{LNG_Description} ({LNG_Type})'
        ),
        'url' => array(
          'text' => '{LNG_URL}'
        ),
        'text' => array(
          'text' => '{LNG_message}'
        ),
        'width' => array(
          'text' => '{LNG_Size of} {LNG_Image}',
          'class' => 'center'
        ),
        'publish_start' => array(
          'text' => '{LNG_Published date}',
          'class' => 'center'
        ),
        'publish_end' => array(
          'text' => '{LNG_Published close}',
          'class' => 'center'
        ),
        'published' => array(
          'text' => ''
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'published' => array(
          'class' => 'center'
        ),
        'width' => array(
          'class' => 'center'
        ),
        'publish_start' => array(
          'class' => 'center'
        ),
        'publish_end' => array(
          'class' => 'center'
        )
      ),
      /* ปุ่มแสดงในแต่ละแถว */
      'buttons' => array(
        'edit' => array(
          'class' => 'icon-edit button green',
          'href' => $uri->createBackUri(array('module' => 'Textlink-write', 'id' => ':id')),
          'text' => '{LNG_Edit}'
        )
      ),
      /* ปุ่มเพิ่ม */
      'addNew' => array(
        'class' => 'button green icon-plus',
        'href' => $uri->createBackUri(array('module' => 'Textlink-write')),
        'text' => '{LNG_Add New} {LNG_Text Links}'
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
    $item['description'] = $item['description'].' ('.$item['type'].')';
    $item['width'] = $item['width'].' * '.$item['height'];
    $item['publish_start'] = Date::format($item['publish_start'], 'd M Y');
    $item['publish_end'] = $item['publish_end'] == 0 ? '{LNG_Dateless}' : Date::format($item['publish_end'], 'd M Y');
    $item['published'] = '<a id=published_'.$item['id'].' class="icon-published'.$item['published'].'" title="'.$this->publisheds[$item['published']].'"></a>';
    return $item;
  }
}