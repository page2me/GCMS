<?php
/*
 * @filesource index/views/language.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Language;

use \Kotchasan\Language;
use \Kotchasan\DataTable;

/**
 * module=language
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * ตารางภาษา
   *
   * @return string
   */
  public function render()
  {
    // ชนิดของภาษาที่เลือก php,js
    $type = self::$request->get('type')->toString();
    $type = $type == 'js' ? 'js' : 'php';
    // โหลดภาษา
    $datas = Language::installed($type);
    $installed_language = Language::installedLanguage();
    // Uri
    $uri = self::$request->getUri();
    // ตารางภาษา
    $table = new DataTable(array(
      'datas' => $datas,
      'onRow' => array($this, 'onRow'),
      'perPage' => max(10, self::$request->cookie('language_perPage', 30)->toInt()),
      'sort' => self::$request->cookie('language_sort', 'key')->toString(),
      'searchColumns' => array_merge(array('key'), $installed_language),
      'headers' => array(
        'id' => array(
          'text' => '{LNG_ID}',
          'sort' => 'id'
        ),
        'key' => array(
          'text' => '{LNG_Key}',
          'sort' => 'key'
        )
      ),
      'action' => 'index.php/index/model/language/action?type='.$type,
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
          'href' => $uri->createBackUri(array('module' => 'languageedit', 'id' => null, 'type' => $type)),
          'text' => '{LNG_Add New}'
        )
      ),
      'buttons' => array(
        array(
          'class' => 'icon-edit button green',
          'href' => $uri->createBackUri(array('module' => 'languageedit', 'id' => ':id', 'type' => $type)),
          'text' => '{LNG_Edit}'
        )
      ),
      'filters' => array(
        'type' => array(
          'name' => 'type',
          'text' => '{LNG_Type}',
          'options' => array('php' => 'php', 'js' => 'js'),
          'value' => $type
        )
      )
    ));
    foreach ($installed_language as $lng) {
      $table->headers[$lng] ['sort'] = $lng;
    }
    // save cookie
    setcookie('language_perPage', $table->perPage, time() + 3600 * 24 * 365, '/');
    setcookie('language_sort', $table->sort, time() + 3600 * 24 * 365, '/');
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
    foreach ($item as $key => $value) {
      if ($key != 'id') {
        $text = \Kotchasan\Text::toEditor(is_array($value) ? implode(', ', $value) : $value);
        $item[$key] = '<span title="'.$text.'">'.\Kotchasan\Text::cut($text, 50).'</span>';
      }
    }
    return $item;
  }
}