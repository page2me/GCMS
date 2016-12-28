<?php
/*
 * @filesource index/models/language.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Language;

use \Kotchasan\Login;
use \Kotchasan\Language;

/**
 * บันทึกรายการภาษา
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Orm\Field
{
  /**
   * ชื่อตาราง
   *
   * @var string
   */
  protected $table = 'language';

  /**
   * รับค่าจาก action
   */
  public function action()
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if (empty($login['fb'])) {
        // ค่าที่ส่งมา
        $id = self::$request->post('id')->filter('0-9,');
        $action = self::$request->post('action')->toString();
        if ($action == 'delete') {
          $model = new \Kotchasan\Model;
          $model->db()->delete($model->getTableName('language'), array('id', explode(',', $id)), 0);
          // อัปเดทไฟล์ ภาษา
          $error = self::updateLanguageFile();
          if (empty($error)) {
            $ret['location'] = 'reload';
          } else {
            $ret['alert'] = $error;
          }
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    echo json_encode($ret);
  }

  /**
   * อัปเดทไฟล์ ภาษา
   */
  public static function updateLanguageFile()
  {
    // ภาษาที่ติดตั้ง
    $languages = \Gcms\Gcms::installedLanguage();
    // query ข้อมูลภาษา
    $model = new \Kotchasan\Model;
    $query = $model->db()->createQuery()->select()->from('language');
    // เตรียมข้อมูล
    $datas = array();
    foreach ($query->toArray()->execute() as $item) {
      $save = array('key' => $item['key']);
      foreach ($languages as $lng) {
        if (isset($item[$lng]) && $item[$lng] != '') {
          if ($item['type'] == 'array') {
            $save[$lng] = unserialize($item[$lng]);
          } elseif ($item['type'] == 'int') {
            $save[$lng] = (int)$item[$lng];
          } else {
            $save[$lng] = $item[$lng];
          }
        }
      }
      $datas[$item['js'] == 1 ? 'js' : 'php'][] = $save;
    }
    // บันทึกไฟล์ภาษา
    $error = '';
    foreach ($datas as $type => $items) {
      $error .= \Kotchasan\Language::save($items, $type);
    }
    return $error;
  }
}