<?php
/*
 * @filesource edocument/views/member.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Member;

use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\DataTable;
use \Kotchasan\Login;
use \Kotchasan\Date;
use \Kotchasan\Http\Uri;
use \Kotchasan\ArrayTool;
use \Kotchasan\Template;

/**
 * รายการเอกสารที่อัปโหลด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
  /**
   * ข้อมูลโมดูล
   */
  private $modules;

  /**
   * รายการเอกสารที่อัปโหลด
   *
   * @param Request $request
   * @param object $index
   * @return object
   */
  public function render(Request $request, $index)
  {
    if ($login = Login::isMember()) {
      // รายการโมดูล edocument ที่ติดตั้งแล้ว
      foreach (Gcms::$module->findByOwner('edocument') as $item) {
        $this->modules[$item->module_id] = $item->topic;
      }
      if (!empty($this->modules)) {
        // โมดูลที่เลือก
        $module_id = $request->request('mid')->toInt();
        // Uri
        $uri = Uri::createFromUri(WEB_URL.'index.php?module=editprofile&tab='.$index->tab.($module_id > 0 ? '&mid='.$module_id : ''));
        // ตาราง
        $table = new DataTable(array(
          /* Model */
          'model' => 'Edocument\Admin\Setup\Model',
          /* รายชื่อฟิลด์ที่ query (ถ้าแตกต่างจาก Model) */
          'fields' => array(
            'document_no',
            'detail',
            'topic',
            'module_id',
            'last_update',
            'ext',
            'id',
          ),
          /* query where */
          'defaultFilters' => array(
            array('A.sender_id', (int)$login['id'])
          ),
          /* เรียงลำดับ */
          'sort' => 'A.last_update desc',
          /* คอลัมน์ที่ไม่ต้องแสดงผล */
          'hideColumns' => array('ext', 'id'),
          /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
          'action' => 'xhr.php/edocument/model/member/action',
          'actionCallback' => 'doFormSubmit',
          /* Uri */
          'uri' => $uri,
          /* รายการต่อหน้า */
          'perPage' => $request->cookie('edocument_perPage', 30)->toInt(),
          /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
          'onRow' => array($this, 'onRow'),
          /* คอลัมน์ที่สามารถค้นหาได้ */
          'searchColumns' => array('document_no', 'detail'),
          /* ตัวเลือกการแสดงผลที่ส่วนหัว */
          'filters' => array(
            'module_id' => array(
              'name' => 'mid',
              'text' => '{LNG_Module}',
              'options' => ArrayTool::merge(array(0 => '{LNG_all items}'), $this->modules),
              'default' => 0,
              'value' => $module_id
            )
          ),
          /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
          'headers' => array(
            'document_no' => array(
              'text' => '{LNG_No}',
              'class' => 'center'
            ),
            'detail' => array(
              'text' => '{LNG_Detail}'
            ),
            'topic' => array(
              'text' => ''
            ),
            'module_id' => array(
              'text' => '{LNG_Module}',
              'class' => 'center'
            ),
            'last_update' => array(
              'text' => '{LNG_Uploaded}',
              'class' => 'center'
            )
          ),
          /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
          'cols' => array(
            'document_no' => array(
              'class' => 'no center'
            ),
            'module_id' => array(
              'class' => 'center'
            ),
            'last_update' => array(
              'class' => 'date'
            )
          ),
          /* ปุ่มแสดงในแต่ละแถว */
          'buttons' => array(
            'edit' => array(
              'class' => 'icon-edit button green',
              'href' => $uri->withParams(array('tab' => 'edocumentwrite', 'id' => ':id', 'module' => 'editprofile')),
              'title' => '{LNG_Edit}'
            ),
            'delete' => array(
              'class' => 'icon-delete button red',
              'id' => ':id',
              'title' => '{LNG_Delete}'
            ),
            'report' => array(
              'class' => 'icon-report button orange',
              'href' => $uri->withParams(array('tab' => 'edocumentreport', 'id' => ':id', 'module' => 'editprofile')),
              'title' => '{LNG_Download Details}'
            ),
          ),
          /* ปุ่มเพิ่ม */
          'addNew' => array(
            'class' => 'button green icon-plus',
            'href' => $uri->withParams(array('tab' => 'edocumentwrite', 'mid' => $module_id)),
            'text' => '{LNG_Add New} {LNG_Document}'
          )
        ));
        // save cookie
        setcookie('edocument_perPage', $table->perPage, time() + 3600 * 24 * 365, '/');
        // ตาราง
        $detail = $table->render();
      } else {
        // ไม่เผยแพร่
        $detail = '<div class=error>{LNG_Can not be performed this request. Because they do not find the information you need or you are not allowed}</div>';
      }
      // template
      $template = Template::create('edocument', 'edocument', 'member');
      $template->add(array(
        '/{LIST}/' => $detail
      ));
      $index->detail = $template->render();
      // คืนค่า
      return $index;
    }
    return null;
  }

  /**
   * จัดรูปแบบการแสดงผลในแต่ละแถว
   *
   * @param array $item
   * @return array
   */
  public function onRow($item)
  {
    $item['module_id'] = $this->modules[$item['module_id']];
    $item['topic'] = $item['topic'].'.'.$item['ext'];
    $item['last_update'] = Date::format($item['last_update'], 'd M Y');
    return $item;
  }
}