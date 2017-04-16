<?php
/**
 * @filesource event/views/admin/setup.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Event\Admin\Setup;

use \Kotchasan\DataTable;
use \Kotchasan\Language;
use \Kotchasan\Date;

/**
 * module=event-setup
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
  private $module;

  /**
   * แสดงรายการอีเว้นต์
   *
   * @param object $index
   * @return string
   */
  public function render($index)
  {
    $this->module = $index->module;
    $this->publisheds = Language::get('PUBLISHEDS');
    // Uri
    $uri = self::$request->getUri();
    // model
    $model = new \Kotchasan\Model;
    // ตาราง
    $table = new DataTable(array(
      /* Model */
      'model' => 'Event\Admin\Setup\Model',
      /* รายการต่อหน้า */
      'perPage' => self::$request->cookie('event_perPage', 30)->toInt(),
      /* ฟิลด์ที่กำหนด (หากแตกต่างจาก Model) */
      'fields' => array(
        'id',
        'topic',
        'color',
        'begin_date',
        'end_date',
        'last_update',
        'published',
        array($model->db()->createQuery()->select('email')->from('user U')->where(array('U.id', 'A.member_id')), 'writer')
      ),
      /* query where */
      'defaultFilters' => array(
        array('A.module_id', (int)$index->module_id)
      ),
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('id', 'end_date', 'color'),
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/event/model/admin/setup/action?mid='.$index->module_id,
      'actionCallback' => 'indexActionCallback',
      'actionConfirm' => 'confirmAction',
      'actions' => array(
        array(
          'id' => 'action',
          'class' => 'ok',
          'text' => '{LNG_With selected}',
          'options' => array(
            'delete' => '{LNG_Delete}'
          )
        )
      ),
      /* คอลัมน์ที่สามารถค้นหาได้ */
      'searchColumns' => array('topic', 'detail'),
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'topic' => array(
          'text' => '{LNG_Topic}'
        ),
        'begin_date' => array(
          'text' => '{LNG_Date and time of event}',
          'class' => 'center'
        ),
        'published' => array(
          'text' => '{LNG_Published}',
          'class' => 'center'
        ),
        'last_update' => array(
          'text' => '{LNG_Last updated}',
          'class' => 'center'
        ),
        'writer' => array(
          'text' => '{LNG_Writer}',
          'class' => 'center'
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'published' => array(
          'class' => 'center'
        ),
        'begin_date' => array(
          'class' => 'center date'
        ),
        'last_update' => array(
          'class' => 'center date'
        ),
        'writer' => array(
          'class' => 'center'
        )
      ),
      /* ปุ่มแสดงในแต่ละแถว */
      'buttons' => array(
        'edit' => array(
          'class' => 'icon-edit button green',
          'href' => $uri->createBackUri(array('module' => 'event-write', 'id' => ':id')),
          'text' => '{LNG_Edit}'
        )
      ),
      /* ปุ่มเพิ่ม */
      'addNew' => array(
        'class' => 'button green icon-plus',
        'href' => $uri->createBackUri(array('module' => 'event-write', 'mid' => $index->module_id)),
        'text' => '{LNG_Add New} {LNG_Event}'
      )
    ));
    // save cookie
    setcookie('event_perPage', $table->perPage, time() + 3600 * 24 * 365, '/');
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
    $item['published'] = '<a id=published_'.$item['id'].' class="icon-published'.$item['published'].'" title="'.$this->publisheds[$item['published']].'"></a>';
    $item['topic'] = '<span class=event_color style="background-color:'.$item['color'].'"></span><a href="../index.php?module='.$this->module.'&amp;id='.$item['id'].'" target=_blank>'.$item['topic'].'</a>';
    $item['last_update'] = Date::format($item['last_update'], 'd M Y H:i');
    $item['begin_date'] = Date::format($item['begin_date'], 'd M Y H:i').($item['end_date'] === '0000-00-00 00:00:00' ? '' : Date::format($item['end_date'], ' - H:i'));
    return $item;
  }
}
