<?php
/*
 * @filesource Gcms/View.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gcms;

use Kotchasan\Template;

/**
 * View base class สำหรับ GCMS.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{
  /**
   * ลิสต์รายการ breadcrumb.
   *
   * @var array
   */
  protected $breadcrumbs = array();
  /**
   * template ของ breadcrumb.
   *
   * @var type
   */
  protected $breadcrumb_template;

  /**
   * Class constructor.
   */
  public function __construct()
  {
    try {
      $this->breadcrumb_template = Template::load('', '', 'breadcrumb');
    } catch (\Exception $exc) {
      $this->breadcrumb_template = '';
    }
  }

  /**
   * เพิ่ม breadcrumb.
   *
   * @param string $url ลิงค์
   * @param string $menu ข้อความแสดงใน breadcrumb
   * @param string $tooltip (option) ทูลทิป
   * @param string $class (option) คลาสสำหรับลิงค์นี้
   */
  public function addBreadcrumb($url, $menu, $tooltip = '', $class = '')
  {
    $menu = htmlspecialchars_decode($menu);
    $tooltip = $tooltip == '' ? $menu : $tooltip;
    if ($url) {
      $this->breadcrumbs[] = '<li><a class="'.$class.'" href="'.$url.'" title="'.$tooltip.'"><span>'.$menu.'</span></a></li>';
    } else {
      $this->breadcrumbs[] = '<li><span class="'.$class.'" title="'.$tooltip.'">'.$menu.'</span></li>';
    }
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
      '/{LOGIN}/' => method_exists('Index\Login\Controller', 'init') ? \Index\Login\Controller::init(Login::isMember()) : '',
      // widgets
      '/{WIDGET_([A-Z]+)(([_\s]+)([^}]+))?}/e' => '\Gcms\View::getWidgets(array(1=>"$1",3=>"$3",4=>"$4"))',
      // breadcrumbs
      '/{BREADCRUMBS}/' => implode('', $this->breadcrumbs),
      // ขนาดตัวอักษร
      '/{FONTSIZE}/' => '<a class="font_size small" title="{LNG_change font small}">A<sup>-</sup></a><a class="font_size normal" title="{LNG_change font normal}">A</a><a class="font_size large" title="{LNG_change font large}">A<sup>+</sup></a>',
      // เวอร์ชั่นของ GCMS
      '/{VERSION}/' => self::$cfg->version,
      // เวลาประมวลผล
      '/{ELAPSED}/' => round(microtime(true) - REQUEST_TIME, 4),
      // จำนวน Query
      '/{QURIES}/' => \Kotchasan\Database\Driver::queryCount()
    ));
    return parent::renderHTML($template);
  }

  /**
   * แสดงผล Widget.
   *
   * @param array $matches
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
  }
}