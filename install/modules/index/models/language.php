<?php
/*
 * @filesource language.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Description
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class language
{

  public function reload()
  {
// โหลดภาษา
    $datas = Language::installed($js);
    $installed_language = Language::installedLanguage();
    $model = new \Kotchasan\Model;
    $model->db()->emptyTable('gcms_language');
    foreach (array('php', 'js') as $lng) {
      foreach (Language::installed($lng) as $item) {
        if (!isset($item['en'])) {
//$item['en'] = $item['key'];
        }
        if (!empty($item['array'])) {
          $item['type'] = 'array';
          $item['th'] = serialize($item['th']);
          if (isset($item['en'])) {
            $item['en'] = serialize($item['en']);
          }
        } elseif (is_int($item['th'])) {
          $item['type'] = 'int';
        } else {
          $item['type'] = 'text';
        }
        unset($item['id']);
        unset($item['array']);
        $item['js'] = $lng == 'js' ? 1 : 0;
        $item['owner'] = 'index';
        $model->db()->insert('gcms_language', $item);
      }
    }
  }
}