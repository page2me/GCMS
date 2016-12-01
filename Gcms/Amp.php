<?php
/*
 * @filesource Gcms/Amp.php
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
class Amp extends \Gcms\Baseview
{

  /**
   * ouput เป็น HTML.
   *
   * @param string|null $template HTML Template ถ้าไม่กำหนด (null) จะใช้ index.html
   * @return string
   */
  public function renderHTML($template = NULL)
  {
    // เนื้อหา
    parent::setContents(array(
      /* AMP CSS */
      '/{CSS}/' => \Css\Index\View::compress(file_get_contents(ROOT_PATH.'skin/'.self::$cfg->skin.'/amp.css')),
      // widgets
      '/{WIDGET_([A-Z]+)(([_\s]+)([^}]+))?}/e' => '\Gcms\View::getWidgets(array(1=>"$1",3=>"$3",4=>"$4"))',
      /* ภาษา */
      '/{LNG_([^}]+)}/e' => '\Kotchasan\Language::get(array(1=>"$1"))',
      /* ภาษาที่ใช้งานอยู่ */
      '/{LANGUAGE}/' => \Kotchasan\Language::name()
    ));
    // JSON-LD
    if (!empty($this->jsonld)) {
      $this->metas['JsonLd'] = '<script type="application/ld+json">'.json_encode($this->jsonld).'</script>';
    }
    return preg_replace_callback('/<img[^>]+src=["\']([^"\']+)[^>]+/is', function($matchs) {
      $size = @getimagesize($matchs[1]);
      if ($size) {
        return '<amp-img src="'.$matchs[1].'" '.$size[3].'></amp-img';
      } else {
        return $matchs[0];
      }
    }, parent::renderHTML(\Kotchasan\Template::load('', '', 'amp')));
  }
}