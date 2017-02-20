<?php
/*
 * @filesource Gcms/View.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gcms;

/**
 * View base class สำหรับ GCMS.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Baseview
{
  /**
   * ลิสต์รายการ breadcrumb.
   *
   * @var array
   */
  private $breadcrumbs = array();
  /**
   * ลิสต์คำสั่ง Javascript ที่จะใส่ไว้ท้ายเพจ
   *
   * @var array
   */
  private $script = array();

  /**
   * เพิ่ม breadcrumb.
   *
   * @param string|null $url ลิงค์ ถ้าเป็นค่า null จะแสดงข้อความเฉยๆ
   * @param string $menu ข้อความแสดงใน breadcrumb
   * @param string $tooltip (option) ทูลทิป
   * @param string $class (option) คลาสสำหรับลิงค์นี้
   */
  public function addBreadcrumb($url, $menu, $tooltip = '', $class = '')
  {
    $menu = htmlspecialchars_decode($menu);
    $tooltip = $tooltip == '' ? $menu : $tooltip;
    if ($url) {
      $this->breadcrumbs_jsonld[] = array('@id' => $url, 'name' => $menu);
      $this->breadcrumbs[] = '<li><a class="'.$class.'" href="'.$url.'" title="'.$tooltip.'"><span>'.$menu.'</span></a></li>';
    } else {
      $this->breadcrumbs_jsonld[] = array('name' => $menu);
      $this->breadcrumbs[] = '<li><span class="'.$class.'" title="'.$tooltip.'">'.$menu.'</span></li>';
    }
  }

  /**
   * เพิ่มคำสั่ง Javascript ที่จะใส่ตรงท้ายเพจ
   *
   * @param string $script
   */
  public function addScript($script)
  {
    $this->script[] = $script;
  }

  /**
   * ouput เป็น HTML.
   *
   * @param string|null $template HTML Template ถ้าไม่กำหนด (null) จะใช้ index.html
   * @return string
   */
  public function renderHTML($template = null)
  {
    // เนื้อหา
    parent::setContents(array(
      // กรอบ login
      '/{LOGIN}/' => \Index\Login\Controller::init(Login::isMember()),
      // widgets
      '/{WIDGET_([A-Z]+)(([_\s]+)([^}]+))?}/e' => '\Gcms\View::getWidgets(array(1=>"$1",3=>"$3",4=>"$4"))',
      // breadcrumbs
      '/{BREADCRUMBS}/' => implode('', $this->breadcrumbs),
      // ขนาดตัวอักษร
      '/{FONTSIZE}/' => '<a class="font_size small" title="{LNG_change font small}">A<sup>-</sup></a><a class="font_size normal" title="{LNG_change font normal}">A</a><a class="font_size large" title="{LNG_change font large}">A<sup>+</sup></a>',
      // เวอร์ชั่นของ GCMS
      '/{VERSION}/' => isset(self::$cfg->version) ? self::$cfg->version : '',
      // เวลาประมวลผล
      '/{ELAPSED}/' => round(microtime(true) - REQUEST_TIME, 4),
      // จำนวน Query
      '/{QURIES}/' => \Kotchasan\Database\Driver::queryCount(),
      /* ภาษา */
      '/{LNG_([^}]+)}/e' => '\Kotchasan\Language::get(array(1=>"$1"))',
      /* วันที่ */
      '/{DATE\s([0-9\-]+(\s[0-9:]+)?)?(\s([^}]+))?}/e' => '\Gcms\View::formatDate(array(1=>"$1",4=>"$4"))',
      /* ภาษา ที่ใช้งานอยู่ */
      '/{LANGUAGE}/' => \Kotchasan\Language::name(),
      /* Javascript ท้ายเพจ */
      '/(<body.*)(<\/body>)/isu' => '$1<script>'.implode("\n", $this->script).'</script>$2',
    ));
    // JSON-LD
    if (!empty($this->jsonld)) {
      $this->metas['JsonLd'] = '<script type="application/ld+json">'.json_encode($this->jsonld).'</script>';
    }
    return parent::renderHTML($template);
  }

  /**
   * แสดงผล Widget.
   *
   * @param array $matches
   * @return string
   */
  public static function getWidgets($matches)
  {
    $request = array(
      'owner' => strtolower($matches[1]),
    );
    if (isset($matches[4])) {
      $request['module'] = $matches[4];
    }
    if (!empty($request['module'])) {
      foreach (explode(';', $request['module']) as $item) {
        if (strpos($item, '=') !== false) {
          list($key, $value) = explode('=', $item);
          $request[$key] = $value;
        }
      }
    }
    $className = '\\Widgets\\'.ucfirst(strtolower($matches[1])).'\\Controllers\\Index';
    if (method_exists($className, 'get')) {
      return createClass($className)->get($request);
    }
    return '';
  }

  /**
   * แปลงวันที่ {DATE 0123456789 d M Y} หรือ {DATE 2016-01-01 12:00:00 d M Y H:i:s}
   * วันที่รูปแบบ mktime ตัวเลขเท่านั้น
   * วันที่รูปแบบ YYYY-mm-dd H:i:s จาก MySQL (จะมีเวลาหรือไม่ก็ได้)
   * ถ้าไม่ได้ระบุรูปแบบ จะใช้ตามรูปแบบของภาษา
   *
   * @param array $matches
   * @return string
   */
  public static function formatDate($matches)
  {
    if (!empty($matches[1])) {
      return \Kotchasan\Date::format($matches[1], isset($matches[4]) ? $matches[4] : null);
    }
    return '';
  }
}