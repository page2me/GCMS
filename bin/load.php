<?php
/**
 * bin/load.php
 * เตรียมตัวแปรต่างๆสำหรับการโหลด GCMS
 * สงวนลิขสิทธ์ ห้ามซื้อขาย ให้นำไปใช้ได้ฟรีเท่านั้น
 *
 * @copyright http://www.goragod.com
 * @author กรกฎ วิริยะ
 * @version 21-05-58
 */
$root_path = str_replace('/bin/load.php', '', str_replace('\\', '/', __FILE__));
$baseurl = $_SERVER['HTTP_HOST'];
$baseurl = $baseurl == '' ? $_SERVER['SERVER_NAME'] : $baseurl;
if (isset($_SERVER['CONTEXT_PREFIX'])) {
  $baseurl .= $_SERVER['CONTEXT_PREFIX'];
  $document_root = str_replace('\\', '/', $_SERVER['CONTEXT_DOCUMENT_ROOT']);
  $_ds = explode($document_root, $root_path);
  $basepath = ltrim($_ds[1], '/');
} else {
  if (isset($_SERVER['APPL_PHYSICAL_PATH'])) {
    $document_root = str_replace('\\', '/', $_SERVER['APPL_PHYSICAL_PATH']);
  } else {
    $document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
  }
  if ($document_root == '') {
    // windows server
    $basepath = end(explode('/', $root_path));
  } else {
    $a = strpos($root_path, $document_root);
    if ($a === false) {
      $basepath = '';
    } else {
      if ($a > 0) {
        $document_root = substr($document_root, $a);
      }
      $basepath = str_replace(array("$document_root/", $document_root), array('', ''), $root_path);
    }
  }
}
/**
 *  @var string ROOT_PATH root ของ server เช่น D:/htdocs/gcms/
 */
define('ROOT_PATH', "$root_path/");
// root ของ document
// เช่น cms/
define('BASE_PATH', ($basepath == '' ? '' : "$basepath/"));
// url ของ server รวม path (ไม่มี / ปิดท้าย)
// เช่น http://domain.tld/gcms
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
  define("WEB_URL", "https://$baseurl".($basepath == "" ? "" : "/$basepath"));
} else {
  define("WEB_URL", "http://$baseurl".($basepath == "" ? "" : "/$basepath"));
}
// โฟลเดอร์สำหรับเก็บข้อมูลต่างๆ นับจาก root ของ server
define('DATA_FOLDER', 'datas/');
define('DATA_PATH', ROOT_PATH.DATA_FOLDER);
define('DATA_URL', WEB_URL.'/'.DATA_FOLDER);
// load variable
if (is_file(ROOT_PATH.'bin/vars.php')) {
  include (ROOT_PATH.'bin/vars.php');
}
// debug mode
// true ถ้าต้องการให้แสดง error
// false ปิดการแสดงผล error (ตอนใช้งานจริง)
define('DEBUG_MODE', false);
// display error
if (DEBUG_MODE) {
  // ขณะออกแบบ แสดง error และ warning ของ PHP
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(-1);
} else {
  // ขณะใช้งานจริง
  error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}
// ไฟล์ config
define('CONFIG', ROOT_PATH.'bin/config.php');
// เวลาที่บอกว่า user logout
// ควรมากกว่า counter_refresh_time อย่างน้อย 2 เท่า
define('COUNTER_GAP', 120);
// ชื่อสงวนสำหรับโมดูล ที่ไม่สามารถนำมาตั้งได้ นอกจากชื่อของโฟลเดอร์หรือไฟล์ต่างๆบนระบบ
// ภาษาอังกฤษตัวพิมพ์เล็กเท่านั้น
define('MODULE_RESERVE', 'admin,register,forgot,editprofile,sendpm,sendmail,email');
// tab สำหรับ member
$member_tabs = array();
$member_tabs['editprofile'] = array('{LNG_MEMBER_PROFILE}', 'modules/member/editprofile');
$member_tabs['password'] = array('{LNG_MEMBER_EDIT_PASSWORD}', 'modules/member/editprofile');
$member_tabs['address'] = array('{LNG_ADDRESS_DETAIL}', 'modules/member/editprofile');
// รายการโมดูลที่ติดตั้งแล้วทั้งหมด เรียงตามลำดับเมนู
$install_modules = array();
// รายการโมดูลที่ติดตั้ง เรียงตาม owner
$install_owners = array();
// รายชื่อโมดูลที่ติดตั้งแล้ว
$module_list = array();
// รายชื่อ module และ owner ที่ติดตั้งแล้ว
$owner_list = array();
// รายชื่อของหน้าสมาชิกต่างๆ (ไม่สามารถใช้เป็นชื่อโมดูลได้)
$member_modules = array('login', 'dologin', 'register', 'forgot', 'editprofile', 'sendmail', 'unsubscrib');
// รายชื่อที่อนุญาติให้ใช้เป็นชื่อโมดูลได้
$allow_module = array('news', 'contact');
// config
$config = array();
if (is_file(CONFIG)) {
  include CONFIG;
} else {
  // defailt
  $config['hour'] = 0;
  $config['languages'][0] = 'th';
  $config['skin'] = 'bighead';
}
// gcms class
include ROOT_PATH.'bin/class.gcms.php';
// language
$language = gcms::getVars('GET,SESSION,COOKIE', 'lang,gcms_language,gcms_language', $config['languages'][0]);
$language = is_file(DATA_PATH."language/$language.php") ? $language : 'th';
setCookie('gcms_language', $language, time() + 3600 * 24 * 365);
$_SESSION['gcms_language'] = $language;
// เวอร์ชั่นของ PHP
define('OLD_PHP', version_compare(PHP_VERSION, '5.2.0', '<'));
// ภาษาที่เลือก
define('LANGUAGE', $language);
// โหลดไฟล์ภาษา
if (is_file(DATA_PATH."language/$language.php")) {
  include DATA_PATH."language/$language.php";
}
/**
 * database driver
 * support mysql, mysqli, pdo
 */
define('DB_DRIVER', 'pdo');
// database class
include ROOT_PATH.'bin/class.db.php';
// ftp class
include ROOT_PATH.'bin/class.ftp.php';
// cache class
include ROOT_PATH.'bin/class.cache.php';
// เรียกใช้งาน ftp
$ftp = new ftp($config['ftp_host'], $config['ftp_username'], $config['ftp_password'], $config['ftp_root'], $config['ftp_port']);
if (!empty($config['db_username']) || !empty($config['db_name'])) {
  // เรียกใช้งานฐานข้อมูล
  if (DB_DRIVER == 'mysql') {
    // mysql database
    $db = sql("mysql://$config[db_username]:$config[db_password]@$config[db_server]/$config[db_name]");
  } else if (DB_DRIVER == 'mysqli') {
    // mysqli database
    $db = sql("mysqli://$config[db_username]:$config[db_password]@$config[db_server]/$config[db_name]");
  } else {
    // PDO mysql database
    $db = sql("pdo://$config[db_username]:$config[db_password]@$config[db_server]/$config[db_name]?dbdriver=mysql");
  }
  // cache
  $cache = new gcmsCache(DATA_PATH.'cache/', $config['index_page_cache'], $ftp);
}
// skin
$skin = gcms::getVars('GET,SESSION', 'skin,my_skin', $config['skin']);
$config['skin'] = is_file(ROOT_PATH."skin/$skin/style.css") ? $skin : 'bighead';
$_SESSION['my_skin'] = $config['skin'];
// โฟลเดอร์ของ template
define('SKIN', "skin/$config[skin]/");
