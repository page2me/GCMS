<?php
/*
 * @filesource index/views/upgrade1012.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Upgrade1012;

/**
 * อัปเกรด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Index\Upgrade\Model
{

  /**
   * อัปเกรดจากเวอร์ชั่น 10.1.2
   *
   * @return string
   */
  public static function upgrade($db)
  {
    $content = array();
    // อัปเกรด user
    $table = $_SESSION['prefix'].'_'.$_SESSION['tables']['user'];
    if (!self::fieldExists($db, $table, 'ban')) {
      $f = $db->query("ALTER TABLE `$table` ADD `ban` INT( 11 ) UNSIGNED NOT NULL;");
      $content[] = '<li class="'.($f ? 'correct' : 'incorrect').'">Update database <b>'.$table.'</b> complete...</li>';
    }
    if (isset($_SESSION['tables']['eventcalendar'])) {
      // อัปเกรด eventcalendar
      $table = $_SESSION['prefix'].'_'.$_SESSION['tables']['eventcalendar'];
      if (self::tableExists($db, $table)) {
        $f = $db->query($sql = "ALTER TABLE `$table` ADD `end_date` DATETIME NOT NULL AFTER `begin_date`;");
        $f = $db->query($sql = "ALTER TABLE `$table` CHANGE `create_date` `create_date` DATETIME NOT NULL;");
        $content[] = '<li class="'.($f ? 'correct' : 'incorrect').'">Update database <b>'.$table.'</b> complete...</li>';
      }
    }
    $table = $_SESSION['prefix'].'_'.$_SESSION['tables']['modules'];
    foreach ($db->customQuery('SELECT `id`,`config`,`owner` FROM `'.$table.'` WHERE `owner`!="index"') as $item) {
      $className = ucfirst($item->owner).'\Admin\Settings\Model';
      if (class_exists($className) && method_exists($className, 'defaultSettings')) {
        $config = $className::defaultSettings();
        if ($item->owner == 'document' || $item->owner == 'board') {
          // document, board
          $_config = self::r2config($item->config);
          foreach ($config as $key => $value) {
            if (isset($_config[$key])) {
              if (is_array($value) && !is_array($_config[$key])) {
                $config[$key] = explode(',', $_config[$key]);
              } else {
                $config[$key] = $_config[$key];
              }
            }
          }
        } else {
          foreach ($config as $key => $value) {
            if (isset($_SESSION['cfg'][$item->owner.'_'.$key])) {
              if (is_array($value) && !is_array($_SESSION['cfg'][$item->owner.'_'.$key])) {
                $config[$key] = explode(',', $_SESSION['cfg'][$item->owner.'_'.$key]);
              } else {
                $config[$key] = $_SESSION['cfg'][$item->owner.'_'.$key];
              }
            }
          }
        }
        $db->update($table, $item->id, array('config' => serialize($config)));
      }
    }
    $content[] = '<li class="correct">Update database <b>'.$table.'</b> complete...</li>';
    // category
    $table = $_SESSION['prefix'].'_'.$_SESSION['tables']['category'];
    foreach ($db->customQuery('SELECT `id`,`config` FROM `'.$table.'` WHERE `config`!=""') as $item) {
      $config = self::r2config($item->config);
      $db->update($table, $item->id, array('config' => serialize($config)));
    }
    $content[] = '<li class="correct">Update database <b>'.$table.'</b> complete...</li>';
    // settings/config.php
    foreach (self::$cfg as $key => $value) {
      if (isset($_SESSION['cfg'][$key])) {
        self::$cfg->$key = $_SESSION['cfg'][$key];
      }
    }
    self::$cfg->version = self::$cfg->new_version;
    unset(self::$cfg->new_version);
    $f = \Gcms\Config::save(self::$cfg, ROOT_PATH.'settings/config.php');
    $content[] = '<li class="'.($f ? 'correct' : 'incorrect').'">Update file <b>config.php</b> ...</li>';
    // settings/database.php
    $database_cfg = include(ROOT_PATH.'install/settings/database.php');
    $database_cfg['mysql']['username'] = $_SESSION['cfg']['db_username'];
    $database_cfg['mysql']['password'] = $_SESSION['cfg']['db_password'];
    $database_cfg['mysql']['dbname'] = $_SESSION['cfg']['db_name'];
    $database_cfg['mysql']['hostname'] = $_SESSION['cfg']['db_server'];
    $database_cfg['mysql']['prefix'] = $_SESSION['prefix'];
    foreach ($_SESSION['tables'] as $key => $value) {
      $database_cfg['tables'][$key] = $value;
    }
    $f = \Gcms\Config::save($database_cfg, ROOT_PATH.'settings/database.php');
    $content[] = '<li class="'.($f ? 'correct' : 'incorrect').'">Update file <b>database.php</b> ...</li>';
    $content[] = '<li class="correct">Upgrade to Version <b>11.0.0</b> complete.</li>';
    return (object)array(
        'content' => implode('', $content),
        'version' => '11.0.0'
    );
  }

  public static function r2config($data)
  {
    $config = @unserialize($data);
    if (!is_array($config)) {
      $config = array();
      foreach (explode("\n", $data) As $item) {
        if ($item != '') {
          if (preg_match('/^(.*)=(.*)$/U', $item, $match)) {
            $config[$match[1]] = trim($match[2]);
          }
        }
      }
    }
    return $config;
  }
}