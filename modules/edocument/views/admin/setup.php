<?php
/*
 * @filesource edocument/views/admin/setup.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Admin\Setup;

use \Kotchasan\DataTable;
use \Kotchasan\Date;
use \Kotchasan\Text;
use \Gcms\Gcms;

/**
 * module=edocument-setup
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * ตารางรายการ
   *
   * @param object $index
   * @param array $login
   * @return string
   */
  public function render($index, $login)
  {
    // Uri
    $uri = self::$request->getUri();
    $where = array(array('A.module_id', (int)$index->module_id));
    if (!Gcms::canConfig($login, $index, 'moderator')) {
      $where[] = array('A.sender_id', (int)$login['id']);
    }
    // model
    $model = new \Kotchasan\Model;
    // ตาราง
    $table = new DataTable(array(
      /* Model */
      'model' => 'Edocument\Admin\Setup\Model',
      /* รายการต่อหน้า */
      'perPage' => self::$request->cookie('edocument_perPage', 30)->toInt(),
      /* ฟิลด์ที่กำหนด (หากแตกต่างจาก Model) */
      'fields' => array(
        'id',
        'document_no',
        'topic',
        'ext',
        'detail',
        array($model->db()->createQuery()->select('email')->from('user U')->where(array('U.id', 'A.sender_id')), 'sender'),
        'size',
        'last_update',
        'downloads',
        'file'
      ),
      /* query where */
      'defaultFilters' => $where,
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('id', 'ext', 'file'),
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/edocument/model/admin/setup/action?mid='.$index->module_id,
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
      'searchColumns' => array('topic', 'document_no', 'detail'),
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'document_no' => array(
          'text' => '{LNG_Document number}'
        ),
        'topic' => array(
          'text' => '{LNG_File Name}'
        ),
        'detail' => array(
          'text' => '{LNG_Description}',
        ),
        'sender' => array(
          'text' => '{LNG_Sender}',
          'class' => 'center'
        ),
        'size' => array(
          'text' => '{LNG_File size}',
          'class' => 'center'
        ),
        'last_update' => array(
          'text' => '{LNG_Last updated}',
          'class' => 'center'
        ),
        'downloads' => array(
          'text' => '{LNG_Download}',
          'class' => 'center'
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'sender' => array(
          'class' => 'center'
        ),
        'size' => array(
          'class' => 'center'
        ),
        'last_update' => array(
          'class' => 'center date'
        ),
        'downloads' => array(
          'class' => 'center visited'
        )
      ),
      /* ปุ่มแสดงในแต่ละแถว */
      'buttons' => array(
        'edit' => array(
          'class' => 'icon-edit button green',
          'href' => $uri->createBackUri(array('module' => 'edocument-write', 'id' => ':id')),
          'text' => '{LNG_Edit}'
        )
      ),
      /* ปุ่มเพิ่ม */
      'addNew' => array(
        'class' => 'button green icon-plus',
        'href' => $uri->createBackUri(array('module' => 'edocument-write', 'mid' => $index->module_id)),
        'text' => '{LNG_Add New} {LNG_E-Document}'
      )
    ));
    // save cookie
    setcookie('edocument_perPage', $table->perPage, time() + 3600 * 24 * 365, '/');
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
    $item['topic'] = '<a href="'.WEB_URL.DATA_FOLDER.'edocument/'.$item['file'].'" target=_blank>'.$item['topic'].'.'.$item['ext'].'</a>';
    $item['size'] = Text::formatFileSize($item['size']);
    $item['last_update'] = Date::format($item['last_update'], 'd M Y H:i');
    return $item;
  }
}