<?php
/**
 * @filesource index/models/languages.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Languages;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Config;

/**
 * บันทึกรายการภาษาที่ติดตั้งแล้ว
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * บันทึกจาก ajax
   */
  public function save()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // โหลด config
        $config = Config::load(ROOT_PATH.'settings/config.php');
        // รับค่าจากการ POST
        $post = self::$request->getParsedBody();
        // do not saved
        $save = false;
        $reload = false;
        if ($post['action'] === 'import') {
          self::import();
        } else if ($post['action'] === 'changed' || $post['action'] === 'move') {
          if ($post['action'] === 'changed') {
            // เปลี่ยนแปลงสถานะการเผยแพร่ภาษา
            $config->languages = explode(',', str_replace('check_', '', $post['data']));
          } else {
            // จัดลำดับภาษา
            $languages = $config->languages;
            $config->languages = array();
            foreach (explode(',', str_replace('L_', '', $post['data'])) as $lng) {
              if (in_array($lng, $languages)) {
                $config->languages[] = $lng;
              }
            }
          }
          $save = true;
        } elseif ($post['action'] === 'droplang' && preg_match('/^([a-z]{2,2})$/', $post['data'], $match)) {
          // ลบภาษา
          $model = new \Kotchasan\Model;
          $language_table = $model->getTableName('language');
          if ($model->db()->fieldExists($language_table, $match[1])) {
            $model->db()->query("ALTER TABLE `$language_table` DROP `$match[1]`");
          }
          // ลบไฟล์
          @unlink(ROOT_PATH.'language/'.$match[1].'.php');
          @unlink(ROOT_PATH.'language/'.$match[1].'.js');
          @unlink(ROOT_PATH.'language/'.$match[1].'.gif');
          $languages = array();
          foreach ($config->languages as $item) {
            if ($match[1] !== $item) {
              $languages[] = $item;
            }
          }
          $config->languages = $languages;
          $save = true;
          $reload = true;
        }
        if ($save) {
          // save config
          if (Config::save($config, ROOT_PATH.'settings/config.php')) {
            if ($reload) {
              $ret['location'] = 'reload';
            }
          } else {
            $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
          }
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }

  /**
   * นำเข้าข้อมูลไฟล์ภาษา
   */
  public static function import()
  {
    $dir = ROOT_PATH.'language/';
    if (is_dir($dir)) {
      // Model
      $model = new \Kotchasan\Model;
      // ตาราง language
      $language_table = $model->getTableName('language');
      $f = opendir($dir);
      while (false !== ($text = readdir($f))) {
        if (preg_match('/([a-z]{2,2})\.(php|js)/', $text, $match)) {
          if ($model->db()->fieldExists($language_table, $match[1]) == false) {
            // เพิ่อมคอลัมน์ภาษา ถ้ายังไม่มีภาษาที่ต้องการ
            $model->db()->query("ALTER TABLE `$language_table` ADD `$match[1]` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci AFTER `key`");
          }
          if ($match[2] == 'php') {
            self::importPHP($model, $language_table, $match[1], $dir.$text);
          } else {
            self::importJS($model, $language_table, $match[1], $dir.$text);
          }
        }
      }
      closedir($f);
    }
  }

  /**
   * นำเข้าข้อมูลไฟล์ภาษา PHP
   *
   * @param \Kotchasan\Model $model Database Object
   * @param string $language_table ชื่อตาราง language
   * @param string $lang ชื่อภาษา
   * @param string $file_name ไฟล์ภาษา
   */
  public static function importPHP($model, $language_table, $lang, $file_name)
  {
    foreach (include ($file_name) AS $key => $value) {
      $type = is_array($value) ? 'array' : 'text';
      $search = $model->db()->first($language_table, array(
        array('key', $key),
        array('js', 0),
        array('type', $type)
      ));
      if ($type == 'array') {
        $value = serialize($value);
      }
      if ($search) {
        $model->db()->update($language_table, $search->id, array(
          $lang => $value,
        ));
      } else {
        $model->db()->insert($language_table, array(
          'key' => $key,
          'js' => 0,
          'type' => $type,
          'owner' => 'index',
          $lang => $value,
        ));
      }
    }
  }

  /**
   * นำเข้าข้อมูลไฟล์ภาษา Javascript
   *
   * @param \Kotchasan\Model $model Database Object
   * @param string $language_table ชื่อตาราง language
   * @param string $lang ชื่อภาษา
   * @param string $file_name ไฟล์ภาษา
   */
  public static function importJS($model, $language_table, $lang, $file_name)
  {
    $patt = '/^var[\s]+([A-Z0-9_]+)[\s]{0,}=[\s]{0,}[\'"](.*)[\'"];$/';
    foreach (file($file_name) AS $item) {
      $item = trim($item);
      if ($item != '') {
        if (preg_match($patt, $item, $match)) {
          $search = $model->db()->first($language_table, array(
            array('key', $match[1]),
            array('js', 1)
          ));
          if ($search) {
            $model->db()->update($language_table, $search->id, array(
              $lang => $match[2],
            ));
          } else {
            $model->db()->insert($language_table, array(
              'key' => $match[1],
              'js' => 1,
              'type' => 'text',
              'owner' => 'index',
              $lang => $match[2],
            ));
          }
        }
      }
    }
  }
}