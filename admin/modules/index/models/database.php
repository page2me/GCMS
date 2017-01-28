<?php
/*
 * @filesource index/models/database.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Database;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Http\Response;

/**
 * ตรวจสอบข้อมูลสมาชิกด้วย Ajax
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * export database to file
   */
  public function export()
  {
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] != 'demo' && empty($login['fb'])) {
        $sqls = array();
        $rows = array();
        $database = array();
        $datas = array();
        foreach (self::$request->getParsedBody() AS $table => $values) {
          foreach ($values AS $k => $v) {
            if (isset($datas[$table][$v])) {
              $datas[$table][$v] ++;
            } else {
              $datas[$table][$v] = 1;
            }
          }
        }
        $web_url = str_replace(array('http://', 'https://', 'www.'), '', WEB_URL);
        $web_url = '/http(s)?:\/\/(www\.)?'.preg_quote($web_url, '/').'/';
        // database
        $model = new static;
        // ชื่อฐานข้อมูล
        $fname = $model->getSetting('dbname').'.sql';
        // memory limit
        ini_set('memory_limit', '1024M');
        // prefix
        $prefix = $model->getSetting('prefix');
        // ตารางทั้งหมด
        $tables = $model->db()->customQuery('SHOW TABLE STATUS', true);
        // ตารางทั้งหมด
        foreach ($tables as $table) {
          if (preg_match('/^'.$prefix.'(.*?)$/', $table['Name']) && isset($datas[$table['Name']])) {
            $fields = $model->db()->customQuery('SHOW FULL FIELDS FROM '.$table['Name'], true);
            $primarykey = array();
            $fulltext = array();
            $rows = array();
            foreach ($fields AS $field) {
              if ($field['Key'] == 'PRI') {
                $primarykey[] = '`'.$field['Field'].'`';
              }
              if ($field['Key'] == 'MUL') {
                $fulltext[] = 'FULLTEXT KEY `'.$field['Field'].'` (`'.$field['Field'].'`)';
              }
              $database[$table['Name']]['Field'][] = $field['Field'];
              $rows[] = '`'.$field['Field'].'` '.$field['Type'].($field['Collation'] != '' ? ' collate '.$field['Collation'] : '').($field['Null'] == 'NO' ? ' NOT NULL' : '').($field['Default'] != '' ? " DEFAULT '".$field['Default']."'" : '').($field['Extra'] != '' ? ' '.$field['Extra'] : '');
            }
            if (!empty($primarykey)) {
              $rows[] = 'PRIMARY KEY ('.implode(',', $primarykey).')';
            }
            if (!empty($fulltext)) {
              $rows[] = implode(',', $fulltext);
            }
            if (isset($datas[$table['Name']]['sturcture'])) {
              $table_name = $prefix == '' ? $table['Name'] : preg_replace('/^'.$prefix.'/', '{prefix}', $table['Name']);
              $sqls[] = 'DROP TABLE IF EXISTS `'.$table_name.'`;';
              $q = 'CREATE TABLE `'.$table_name.'` ('.implode(',', $rows).') ENGINE='.$table['Engine'];
              $q .= ' DEFAULT CHARSET='.preg_replace('/([a-zA-Z0-9]+)_.*?/Uu', '\\1', $table['Collation']).' COLLATE='.$table['Collation'];
              $q .= ($table['Create_options'] != '' ? ' '.strtoupper($table['Create_options']) : '').';';
              $sqls[] = $q;
            }
          }
        }
        // ข้อมูลในตาราง
        foreach ($tables AS $table) {
          if (preg_match('/^'.$prefix.'(.*?)$/', $table['Name'], $match)) {
            if ($match[1] == '_language') {
              if (isset($_POST['language_lang']) && isset($_POST['language_owner'])) {
                $l = array_merge(array('key', 'type', 'owner', 'js'), $_POST['language_lang']);
                foreach ($_POST['language_owner'] AS $lang) {
                  $languages[] = "'$lang'";
                  if ($lang == 'index') {
                    $languages[] = "''";
                  }
                }
                $table_name = $prefix == '' ? $table['Name'] : preg_replace('/^'.$prefix.'/', '{prefix}', $table['Name']);
                $data = "INSERT INTO `$table_name` (`".implode('`, `', $l)."`) VALUES ('%s');";
                $sql = "SELECT `".implode('`,`', $l)."` FROM `".$table['Name']."` WHERE `owner` IN (".implode(',', $languages).") ORDER BY `owner`,`key`,`js`";
                foreach ($model->db()->customQuery($sql, true) AS $record) {
                  foreach ($record as $field => $value) {
                    $record[$field] = ($field == 'owner' && $value == '') ? 'index' : addslashes($value);
                  }
                  $sqls[] = preg_replace(array('/[\r]/u', '/[\n]/u'), array('\r', '\n'), sprintf($data, implode("','", $record)));
                }
              }
            } elseif ($match[1] == '_emailtemplate') {
              if (isset($datas[$table['Name']]['datas'])) {
                if (($key = array_search('id', $database[$table['Name']]['Field'])) !== false) {
                  unset($database[$table['Name']]['Field'][$key]);
                }
                $table_name = $prefix == '' ? $table['Name'] : preg_replace('/^'.$prefix.'/', '{prefix}', $table['Name']);
                $data = "INSERT INTO `$table_name` (`".implode('`, `', $database[$table['Name']]['Field'])."`) VALUES ('%s');";
                $records = $model->db()->customQuery('SELECT * FROM '.$table['Name'], true);
                foreach ($records AS $record) {
                  foreach ($record AS $field => $value) {
                    if ($field === 'copy_to' || $field === 'from_email') {
                      $record[$field] = $value == $login['email'] ? '{WEBMASTER}' : '';
                    } elseif ($field == 'id') {
                      unset($record['id']);
                    } else {
                      $record[$field] = addslashes(preg_replace($web_url, '{WEBURL}', $value));
                    }
                  }
                  $sqls[] = preg_replace(array('/[\r]/u', '/[\n]/u'), array('\r', '\n'), sprintf($data, implode("','", $record)));
                }
              }
            } elseif (isset($datas[$table['Name']]['datas'])) {
              $table_name = $prefix == '' ? $table['Name'] : preg_replace('/^'.$prefix.'/', '{prefix}', $table['Name']);
              $data = "INSERT INTO `$table_name` (`".implode('`, `', $database[$table['Name']]['Field'])."`) VALUES ('%s');";
              $records = $model->db()->customQuery('SELECT * FROM '.$table['Name'], true);
              foreach ($records AS $record) {
                foreach ($record AS $field => $value) {
                  $record[$field] = addslashes(preg_replace($web_url, '{WEBURL}', $value));
                }
                $sqls[] = preg_replace(array('/[\r]/u', '/[\n]/u'), array('\r', '\n'), sprintf($data, implode("','", $record)));
              }
            }
          }
        }
        // send file
        $response = new Response();
        $response->withHeaders(array(
          'Content-Type' => 'application/force-download',
          'Content-Disposition' => 'attachment; filename='.$fname
        ))->withContent(preg_replace(array('/[\\\\]+/', '/\\\"/'), array('\\', '"'), implode("\r\n", $sqls)))->send();
        exit;
      }
    }
    // ไม่สามารถดาวน์โหลดได้
    $response = new Response(404);
    $response->withContent('File Not Found!')->send();
  }

  /**
   * import database
   */
  public function import()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      // ไฟล์ที่ส่งมา
      $file = $_FILES['import_file'];
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } elseif ($file['tmp_name'] != '') {
        // long time
        set_time_limit(0);
        // database
        $model = new static;
        // prefix
        $prefix = $model->getSetting('prefix');
        // อัปโหลด
        $fr = file($file['tmp_name']);
        // query ทีละบรรทัด
        foreach ($fr as $value) {
          $sql = str_replace(array('\r', '\n', '{prefix}', '/{WEBMASTER}/', '/{WEBURL}/'), array("\r", "\n", $prefix, $login['email'], WEB_URL), trim($value));
          if ($sql != '') {
            $model->db()->query($sql);
          }
        }
        // คืนค่า
        $ret['alert'] = Language::get('Data import completed Please reload the page to see the changes');
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}