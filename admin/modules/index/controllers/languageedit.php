<?php
/*
 * @filesource index/controllers/languageedit.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Languageedit;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Html;

/**
 * ฟอร์มเขียน/แก้ไข ภาษา
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{
  public $type;
  public $id;
  public $language;
  public $languages;

  /**
   * แสดงผล
   */
  public function render()
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // ชนิดของภาษาที่เลือก php,js
      $this->type = self::$request->get('type')->toString();
      $this->type = $this->type == 'js' ? 'js' : 'php';
      // รายการที่แก้ไข (id)
      $this->id = self::$request->get('id', -1)->toInt();
      // โหลดไฟล์ภาษา ที่ติดตั้ง
      $languages = Language::installed($this->type);
      $installed_languages = Language::installedLanguage();
      $this->languages = array();
      if ($this->id > -1) {
        $this->language = $languages[$this->id];
        foreach ($installed_languages as $item) {
          if (isset($this->language['array'])) {
            if (isset($this->language[$item])) {
              foreach ($this->language[$item] as $k => $v) {
                if (!isset($this->languages[$k]['key'])) {
                  $this->languages[$k]['key'] = $k;
                  foreach ($installed_languages as $a) {
                    $this->languages[$k][$a] = '';
                  }
                }
                $this->languages[$k][$item] = $v;
              }
            }
          } else {
            if (!isset($this->languages[0]['key'])) {
              $this->languages[0]['key'] = '';
              foreach ($installed_languages as $a) {
                $this->languages[0][$a] = '';
              }
            }
            if (isset($this->language[$item])) {
              $this->languages[0][$item] = $this->language[$item];
            }
          }
        }
      } else {
        // ใหม่
        $this->language = array('key' => '');
        $this->languages[0]['key'] = '';
        foreach ($installed_languages as $item) {
          $this->languages[0][$item] = '';
        }
      }
      // แสดงผล
      $section = Html::create('section');
      // breadcrumbs
      $breadcrumbs = $section->add('div', array(
        'class' => 'breadcrumbs'
      ));
      $ul = $breadcrumbs->add('ul');
      $ul->appendChild('<li><span class="icon-tools">{LNG_Tools}</span></li>');
      $ul->appendChild('<li><a href="{BACKURL?module=language}">{LNG_Language}</a></li>');
      $ul->appendChild('<li><span>{LNG_'.($this->id > -1 ? 'Edit' : 'Create').'}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-language">'.$this->title().'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Languageedit\View')->render($this));
      return $section->render();
    } else {
      // 404.html
      return \Index\Error\Controller::page404();
    }
  }

  /**
   * title bar
   */
  public function title()
  {
    return '{LNG_Add and manage the display language of the site}';
  }
}