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
class View extends \Kotchasan\View
{
  /**
   * ลิสต์รายการ breadcrumb.
   *
   * @var array
   */
  private $breadcrumbs = array();
  /**
   * ลิสต์รายการ breadcrumb สำหรับ JSON-LD
   *
   * @var array
   */
  private $breadcrumbs_jsonld = array();
  /**
   * ลิสต์รายการ JSON-LD
   *
   * @var array
   */
  private $jsonld = array();

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
   * กำหนดค่า JSON-LD
   *
   * @param array $datas
   */
  public function setJsonLd($datas)
  {
    $this->jsonld[] = $datas;
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
      /* ภาษา ที่ใช้งานอยู่ */
      '/{LANGUAGE}/' => \Kotchasan\Language::name()
    ));
    // BreadcrumbList
    if (sizeof($this->breadcrumbs_jsonld) > 1) {
      $elements = array();
      foreach ($this->breadcrumbs_jsonld as $i => $items) {
        $elements[] = array(
          '@type' => 'ListItem',
          'position' => $i + 1,
          'item' => $items
        );
      }
      $this->jsonld[] = array(
        '@context' => 'http://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $elements
      );
    }
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