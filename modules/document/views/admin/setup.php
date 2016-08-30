<?php
/*
 * @filesource document/views/admin/setup.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Admin\Setup;

use \Kotchasan\DataTable;
use \Kotchasan\Language;
use \Kotchasan\Date;
use \Gcms\Gcms;

/**
 * module=document-setup
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{
  /**
   * ข้อมูลโมดูล
   *
   * @var object
   */
  private $index;
  private $publisheds;
  private $replies;
  private $thumbnails;
  private $default_icon;
  private $categories;

  /**
   * ตารางรายการบทความ
   *
   * @param object $index
   * @return string
   */
  public function render($index)
  {
    $this->index = $index;
    $this->publisheds = Language::get('PUBLISHEDS');
    $this->replies = Language::get('REPLIES');
    $this->thumbnails = Language::get('THUMBNAILS');
    $this->default_icon = WEB_URL.$index->default_icon;
    $this->categories = array(0 => '{LNG_all items}');
    foreach (\Index\Category\Model::categories((int)$index->module_id) as $item) {
      $this->categories[$item['category_id']] = Gcms::ser2Str($item, 'topic');
    }
    $category_id = self::$request->get('cat', 0)->toInt();
    // Uri
    $uri = self::$request->getUri();
    // ตาราง
    $table = new DataTable(array(
      /* Model */
      'model' => 'Document\Admin\Setup\Model',
      /* รายการต่อหน้า */
      'perPage' => self::$request->cookie('document_perPage', 30)->toInt(),
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
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/document/model/admin/setup/action?mid='.$index->module_id,
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
          'options' => $this->categories,
          'default' => 0,
          'value' => $category_id
        )
      ),
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'topic' => array(
          'text' => '{LNG_Topic}'
        ),
        'picture' => array(
          'text' => '',
          'colspan' => 4
        ),
        'category_id' => array(
          'text' => '{LNG_Category}',
          'class' => 'center'
        ),
        'writer' => array(
          'text' => '{LNG_Writer}'
        ),
        'create_date' => array(
          'text' => '{LNG_Article Date}',
          'class' => 'center'
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
        'picture' => array(
          'class' => 'center'
        ),
        'can_reply' => array(
          'class' => 'center'
        ),
        'published' => array(
          'class' => 'center'
        ),
        'show_news' => array(
          'class' => 'center'
        ),
        'category_id' => array(
          'class' => 'center'
        ),
        'create_date' => array(
          'class' => 'center date'
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
          'href' => $uri->createBackUri(array('module' => 'document-write', 'id' => ':id')),
          'text' => '{LNG_Edit}'
        )
      ),
      /* ปุ่มเพิ่ม */
      'addNew' => array(
        'class' => 'button green icon-plus',
        'href' => $uri->createBackUri(array('module' => 'document-write', 'mid' => $index->module_id, 'cat' => $category_id)),
        'text' => '{LNG_Add New} {LNG_Content}'
      )
    ));
    // save cookie
    setcookie('document_perPage', $table->perPage, time() + 3600 * 24 * 365, '/');
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
    if (is_file(ROOT_PATH.DATA_FOLDER.'document/'.$item['picture'])) {
      $item['picture'] = '<img src="'.WEB_URL.DATA_FOLDER.'document/'.$item['picture'].'" title="'.$this->thumbnails[1].'" width=22 height=22 alt=thumbnail>';
    } else {
      $item['picture'] = '<span class=icon-thumbnail title="'.$this->thumbnails[0].'"></span>';
    }
    $item['show_news'] = '<span class="icon-widgets reply'.(preg_match('/news=1/', $item['show_news']) ? 1 : 0).'"></span>';
    $item['create_date'] = Date::format($item['create_date'], 'd M Y H:i');
    $item['category_id'] = empty($item['category_id']) || empty($this->categories[$item['category_id']]) ? '{LNG_Uncategorized}' : $this->categories[$item['category_id']];
    $item['last_update'] = Date::format($item['last_update'], 'd M Y H:i');
    $item['writer'] = '<span class="status'.$item['status'].'">'.$item['writer'].'</span>';
    $item['can_reply'] = '<a id=can_reply_'.$item['id'].' class="icon-reply reply'.$item['can_reply'].'" title="'.$this->replies[$item['can_reply']].'"></a>';
    $item['published'] = '<a id=published_'.$item['id'].' class="icon-published'.$item['published'].'" title="'.$this->publisheds[$item['published']].'"></a>';
    return $item;
  }
}