<?php
/*
 * @filesource gallery/views/admin/setup.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gallery\Admin\Setup;

use \Kotchasan\DataTable;
use \Kotchasan\Language;
use \Kotchasan\Date;

/**
 * module=gallery-setup
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
  private $thumbnails;
  private $module;

  /**
   * แสดงรายการอัลบัม
   *
   * @param object $index
   * @return string
   */
  public function render($index)
  {
    $this->module = $index->module;
    $this->thumbnails = Language::get('THUMBNAILS');
    // Uri
    $uri = self::$request->getUri();
    // ตาราง
    $table = new DataTable(array(
      /* Model */
      'model' => 'Gallery\Admin\Setup\Model',
      /* รายการต่อหน้า */
      'perPage' => self::$request->cookie('album_perPage', 30)->toInt(),
      /* query where */
      'defaultFilters' => array(
        array('A.module_id', (int)$index->module_id)
      ),
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('member_id', 'id', 'status', 'module_id'),
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/gallery/model/admin/setup/action?mid='.$index->module_id,
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
          'text' => '{LNG_Album}'
        ),
        'image' => array(
          'text' => '{LNG_Image}',
          'colspan' => 2
        ),
        'visited' => array(
          'text' => '{LNG_Preview}',
          'class' => 'center'
        ),
        'last_update' => array(
          'text' => '{LNG_Last updated}',
          'class' => 'center'
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'count' => array(
          'class' => 'center'
        ),
        'visited' => array(
          'class' => 'center'
        ),
        'last_update' => array(
          'class' => 'center'
        )
      ),
      /* ปุ่มแสดงในแต่ละแถว */
      'buttons' => array(
        'edit' => array(
          'class' => 'icon-edit button green',
          'href' => $uri->createBackUri(array('module' => 'gallery-write', 'id' => ':id')),
          'text' => '{LNG_Edit}'
        ),
        'upload' => array(
          'class' => 'icon-upload button orange',
          'href' => $uri->createBackUri(array('module' => 'gallery-upload', 'id' => ':id')),
          'text' => '{LNG_Upload}'
        )
      ),
      /* ปุ่มเพิ่ม */
      'addNew' => array(
        'class' => 'button green icon-plus',
        'href' => $uri->createBackUri(array('module' => 'gallery-write', 'mid' => $index->module_id)),
        'text' => '{LNG_Add New} {LNG_Album}'
      )
    ));
    // save cookie
    setcookie('album_perPage', $table->perPage, time() + 3600 * 24 * 365, '/');
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
    $item['topic'] = '<a href="../index.php?module='.$this->module.'&amp;id='.$item['id'].'">'.$item['topic'].'</a>';
    if (is_file(ROOT_PATH.DATA_FOLDER.'gallery/'.$item['id'].'/'.$item['image'])) {
      $item['image'] = '<img src="'.WEB_URL.DATA_FOLDER.'gallery/'.$item['id'].'/thumb_'.$item['image'].'" title="'.$this->thumbnails[1].'" style="max-width:50px;max-height:50px" alt=thumbnail>';
    } else {
      $item['image'] = '<span class=icon-thumbnail title="'.$this->thumbnails[0].'"></span>';
    }
    $item['count'] = '('.$item['count'].')';
    $item['last_update'] = Date::format($item['last_update'], 'd M Y H:i');
    return $item;
  }
}