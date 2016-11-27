<?php
/*
 * @filesource index/views/mods.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Mods;

use \Kotchasan\DataTable;
use \Kotchasan\Language;
use \Kotchasan\Date;

/**
 * แสดงรายการโมดูลที่ติดตั้งแล้ว
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{
  /**
   * ข้อมูลโมดูล
   */
  private $publisheds;

  /**
   * module=mods
   *
   * @return string
   */
  public function render()
  {
    $this->publisheds = Language::get('PUBLISHEDS');
    // Uri
    $uri = self::$request->getUri();
    // ตาราง
    $table = new DataTable(array(
      /* Model */
      'model' => 'Index\Mods\Model',
      'defaultFilters' => array(
        array('I.index', 1)
      ),
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('module_id', 'id'),
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/index/model/mods/action',
      'actionCallback' => 'indexActionCallback',
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'topic' => array(
          'text' => '{LNG_Topic}'
        ),
        'published' => array(
          'text' => '{LNG_Status}',
          'class' => 'center'
        ),
        'language' => array(
          'text' => '{LNG_Language}',
          'class' => 'center'
        ),
        'module' => array(
          'text' => '{LNG_module name}'
        ),
        'owner' => array(
          'text' => '&nbsp;'
        ),
        'last_update' => array(
          'text' => '{LNG_Last updated}',
          'class' => 'center'
        ),
        'visited' => array(
          'text' => '{LNG_Preview}',
          'class' => 'center'
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
        'last_update' => array(
          'class' => 'center'
        ),
        'visited' => array(
          'class' => 'visited'
        )
      ),
      /* ปุ่มแสดงในแต่ละแถว */
      'buttons' => array(
        'edit' => array(
          'class' => 'icon-edit button green',
          'href' => $uri->createBackUri(array('module' => 'pagewrite', 'id' => ':id')),
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
        'href' => $uri->createBackUri(array('module' => 'addmodule', 'id' => '0')),
        'text' => '{LNG_Add New} {LNG_Module}'
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
    $item['last_update'] = Date::format($item['last_update'], 'd M Y H:i');
    $item['language'] = empty($item['language']) ? '' : '<img src="'.WEB_URL.'language/'.$item['language'].'.gif" alt="'.$item['language'].'">';
    $item['published'] = '<a id=published_'.$item['id'].' class="icon-published'.$item['published'].'" title="'.$this->publisheds[$item['published']].'"></a>';
    return $item;
  }
}