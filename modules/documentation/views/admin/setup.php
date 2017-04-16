<?php
/**
 * @filesource documentation/views/admin/setup.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Documentation\Admin\Setup;

use \Kotchasan\DataTable;
use \Kotchasan\Language;
use \Kotchasan\Date;
use \Kotchasan\ArrayTool;

/**
 * module=documentation-setup
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
  private $index;
  private $publisheds;
  private $categories;

  /**
   * ตารางรายการ
   *
   * @param object $index
   * @return string
   */
  public function render($index)
  {
    $this->index = $index;
    $this->publisheds = Language::get('PUBLISHEDS');
    $this->categories = \Index\Category\Model::categories((int)$index->module_id);
    $category_id = self::$request->get('cat', 0)->toInt();
    // Uri
    $uri = self::$request->getUri();
    // ตาราง
    $table = new DataTable(array(
      /* Model */
      'model' => 'Documentation\Admin\Setup\Model',
      /* รายการต่อหน้า */
      'perPage' => self::$request->cookie('documentation_perPage', 30)->toInt(),
      /* query where */
      'defaultFilters' => array(
        array('P.module_id', (int)$index->module_id),
        array('P.index', 0),
        array('D.language', array(Language::name(), ''))
      ),
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('member_id', 'id', 'status', 'module_id'),
      /* enable drag row */
      'dragColumn' => 1,
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/documentation/model/admin/setup/action?mid='.$index->module_id,
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
      /* ตัวเลือกการแสดงผลที่ส่วนหัว */
      'filters' => array(
        'category_id' => array(
          'name' => 'cat',
          'text' => '{LNG_Category}',
          'options' => ArrayTool::merge(array(0 => '{LNG_all items}'), $this->categories),
          'default' => 0,
          'value' => $category_id
        )
      ),
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'topic' => array(
          'text' => '{LNG_Topic}'
        ),
        'published' => array(
          'text' => ''
        ),
        'category_id' => array(
          'text' => '{LNG_Category}',
          'class' => 'center'
        ),
        'writer' => array(
          'text' => '{LNG_Writer}'
        ),
        'last_update' => array(
          'text' => '{LNG_Last updated}',
          'class' => 'center'
        ),
        'visited' => array(
          'text' => '{LNG_Viewing}',
          'class' => 'center'
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'published' => array(
          'class' => 'center'
        ),
        'category_id' => array(
          'class' => 'center'
        ),
        'last_update' => array(
          'class' => 'center date'
        ),
        'visited' => array(
          'class' => 'visited center'
        )
      ),
      /* ปุ่มแสดงในแต่ละแถว */
      'buttons' => array(
        'edit' => array(
          'class' => 'icon-edit button green',
          'href' => $uri->createBackUri(array('module' => 'documentation-write', 'id' => ':id')),
          'text' => '{LNG_Edit}'
        )
      ),
      /* ปุ่มเพิ่ม */
      'addNew' => array(
        'class' => 'button green icon-plus',
        'href' => $uri->createBackUri(array('module' => 'documentation-write', 'mid' => $index->module_id, 'cat' => $category_id)),
        'text' => '{LNG_Add New} {LNG_Content}'
      )
    ));
    // save cookie
    setcookie('documentation_perPage', $table->perPage, time() + 3600 * 24 * 365, '/');
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
    $item['topic'] = '<a href="../index.php?module='.$this->index->module.'&amp;id='.$item['id'].'" target=_blank>'.$item['topic'].'</a>';
    $item['category_id'] = empty($item['category_id']) || empty($this->categories[$item['category_id']]) ? '{LNG_Uncategorized}' : $this->categories[$item['category_id']];
    $item['last_update'] = Date::format($item['last_update'], 'd M Y H:i');
    $item['writer'] = '<span class="status'.$item['status'].'">'.$item['writer'].'</span>';
    $item['published'] = '<a id=published_'.$item['id'].' class="icon-published'.$item['published'].'" title="'.$this->publisheds[$item['published']].'"></a>';
    return $item;
  }
}
