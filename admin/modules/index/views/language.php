<?php
/*
 * @filesource index/views/language.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Language;

use \Kotchasan\DataTable;
use \Kotchasan\Text;

/**
 * module=language
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{
  private $languages;

  /**
   * ตารางภาษา
   *
   * @return string
   */
  public function render()
  {
    // ชนิดของภาษาที่เลือก php,js
    $js = self::$request->get('js')->toBoolean();
    $this->languages = \Gcms\Gcms::installedLanguage();
    // Uri
    $uri = self::$request->getUri();
    // ตารางภาษา
    $table = new DataTable(array(
      'id' => 'language_table',
      /* Model */
      'model' => 'Index\Language\Model',
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('type', 'js'),
      /* แบ่งหน้า */
      'perPage' => max(10, self::$request->cookie('language_perPage', 30)->toInt()),
      /* เรียงลำดับ */
      'sort' => self::$request->cookie('language_sort', 'id DESC')->toString(),
      'searchColumns' => array_merge(array('key'), $this->languages),
      'headers' => array(
        'id' => array(
          'text' => '{LNG_ID}',
          'sort' => 'id'
        ),
        'key' => array(
          'text' => '{LNG_Key}',
          'sort' => 'key'
        ),
        'owner' => array(
          'text' => '{LNG_Module}',
          'class' => 'center',
          'sort' => 'owner'
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'owner' => array(
          'class' => 'center'
        ),
      ),
      'action' => 'index.php/index/model/language/action?js='.$js,
      'actionCallback' => 'doFormSubmit',
      'actionConfirm' => 'confirmAction',
      'actions' => array(
        array(
          'id' => 'action',
          'class' => 'ok',
          'text' => '{LNG_With selected}',
          'options' => array(
            'delete' => '{LNG_Delete}'
          )
        ),
        array(
          'class' => 'button add icon-plus',
          'href' => $uri->createBackUri(array('module' => 'languageedit', 'id' => null, 'js' => $js)),
          'text' => '{LNG_Add New}'
        )
      ),
      'buttons' => array(
        array(
          'class' => 'icon-edit button green',
          'href' => $uri->createBackUri(array('module' => 'languageedit', 'id' => ':id', 'js' => $js)),
          'text' => '{LNG_Edit}'
        )
      ),
      'filters' => array(
        'js' => array(
          'name' => 'js',
          'text' => '{LNG_Type}',
          'options' => array(0 => 'php', 1 => 'js'),
          'value' => $js
        )
      )
    ));
    foreach ($this->languages as $lng) {
      $table->headers[$lng] ['sort'] = $lng;
    }
    // save cookie
    setcookie('language_perPage', $table->perPage, time() + 3600 * 24 * 365, '/');
    setcookie('language_sort', $table->sort, time() + 3600 * 24 * 365, '/');
    $table->script('initLanguageTable("language_table");');
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
    foreach ($this->languages as $lng) {
      if ($item['type'] == 'array') {
        if ($item[$lng] != '') {
          $item[$lng] = implode(', ', unserialize($item[$lng]));
        }
      }
      $item[$lng] = $item[$lng] == '' ? '' : '<span title="'.htmlspecialchars($item[$lng]).'">'.self::toText($item[$lng]).'</span>';
    }
    $item['key'] = '<a class="icon-copy" title="'.htmlspecialchars($item['key']).'">'.self::toText($item['key']).'</a>';
    return $item;
  }

  private static function toText($text)
  {
    return Text::cut(str_replace(array("\r", "\n", '&'), array('', ' ', '&amp;'), strip_tags($text)), 50);
  }
}