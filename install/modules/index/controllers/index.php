<?php
/*
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Kotchasan\Http\Request;

/**
 * Controller หลัก สำหรับแสดง backend ของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * แสดงผลหน้าหลักเว็บไซต์
   *
   * @param Request $request
   */
  public function index(Request $request)
  {
    // session
    $request->initSession();
    define('INSTALL', __FILE__);
    $content = array();
    if (empty(self::$cfg->version)) {
      // อัปเกรดจาก GCMS 10
      if (is_file('../bin/config.php') && is_file('../bin/vars.php')) {
        // โหลด config
        $config = array();
        include '../bin/config.php';
        $_SESSION['cfg'] = $config;
        // โหลด vars
        $defines = array();
        foreach (file('../bin/vars.php') AS $value) {
          if (preg_match('/^define\([\'"](VERSION|PREFIX|EN_KEY)[\'"][\s,]+[\'"](.*)[\'"]\);$/', trim($value), $match)) {
            if ($match[1] == 'VERSION') {
              self::$cfg->version = $match[2];
            } elseif ($match[1] == 'PREFIX') {
              $_SESSION['prefix'] = $match[2];
            } elseif ($match[1] == 'EN_KEY') {
              self::$cfg->password_key = $match[2];
            }
          } elseif (preg_match('/^define\([\'"]DB_([A-Z_]+)[\'"][\s,]+PREFIX\.[\'"]_(.*)[\'"]\);$/', trim($value), $match)) {
            $_SESSION['tables'][strtolower($match[1])] = $match[2];
          }
        }
        // ตรวจสอบเวอร์ชั่นที่สามารถอัปเกรดได้
        if (!$request->request('install')->exists() && version_compare('10.1.2', self::$cfg->version, '<=') == -1) {
          // อัปเกรด
          $class = 'Index\Upgrade'.$request->request('step')->toInt().'\View';
          if (class_exists($class) && method_exists($class, 'render')) {
            $page = createClass($class)->render($request);
          } else {
            $page = createClass('Index\Upgrade\View')->render($request);
          }
        } else {
          // ติดตั้งใหม่เท่านั้น
          $class = 'Index\Install'.$request->request('step')->toInt().'\View';
          if (class_exists($class) && method_exists($class, 'render')) {
            $page = createClass($class)->render($request);
          } else {
            $page = createClass('Index\Install\View')->render($request);
          }
        }
      } else {
        // ติดตั้งครั้งแรก
        $class = 'Index\Install'.$request->request('step')->toInt().'\View';
        if (class_exists($class) && method_exists($class, 'render')) {
          $page = createClass($class)->render($request);
        } else {
          $page = createClass('Index\Install\View')->render($request);
        }
      }
    } elseif (version_compare(self::$cfg->version, self::$cfg->new_version) == -1) {
      // อัปเกรดจาก GCMS 11
      $cfg = include(ROOT_PATH.'settings/database.php');
      $_SESSION['cfg'] = array(
        'db_username' => $cfg['mysql']['username'],
        'db_password' => $cfg['mysql']['password'],
        'db_name' => $cfg['mysql']['dbname'],
        'db_server' => empty($cfg['mysql']['hostname']) ? 'localhost' : $cfg['mysql']['hostname']
      );
      $_SESSION['prefix'] = $cfg['mysql']['prefix'];
      $class = 'Index\Upgrade'.$request->request('step')->toInt().'\View';
      if (class_exists($class) && method_exists($class, 'render')) {
        $page = createClass($class)->render($request);
      } else {
        $page = createClass('Index\Upgrade\View')->render($request);
      }
    } else {
      // ติดตั้งแล้ว
      $page = createClass('Index\Success\View')->render($request);
    }
    // แสดงผล
    $view = new \Gcms\View;
    $view->setContents(array(
      '/{CONTENT}/' => $page->content,
      '/{TITLE}/' => $page->title
    ));
    echo $view->renderHTML(file_get_contents(ROOT_PATH.'install/modules/index/views/index.html'));
  }
}