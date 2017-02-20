<?php
/*
 * @filesource index/controllers/languageedit.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Languageedit;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Html;

/**
 * module=languageedit
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{

  /**
   * ฟอร์มเขียน/แก้ไข ภาษา
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
  {
    // แอดมิน
    if (Login::isAdmin()) {
      // ข้อความ title bar
      $this->title = '{LNG_Add and manage the display language of the site}';
      // เลือกเมนู
      $this->menu = 'tools';
      // ภาษาที่ติดตั้ง
      $languages = \Gcms\Gcms::installedLanguage();
      // รายการที่แก้ไข (id)
      $id = $request->get('id')->toInt();
      if ($id > 0) {
        // แก้ไข อ่านรายการที่เลือก
        $model = new \Kotchasan\Model();
        $language = $model->db()->first($model->getTableName('language'), $id);
        if ($language && $language->type == 'array') {
          foreach ($languages as $lng) {
            if ($language->$lng != '') {
              $ds = @unserialize($language->$lng);
              if (is_array($ds)) {
                foreach ($ds as $key => $value) {
                  $language->datas[$key]['key'] = $key;
                  $language->datas[$key][$lng] = $value;
                }
              }
            }
            unset($language->$lng);
          }
        } else {
          $language->datas[0]['key'] = '';
          foreach ($languages as $lng) {
            $language->datas[0][$lng] = $language->$lng;
            unset($language->$lng);
          }
        }
      } else {
        // ใหม่
        $language = array(
          'id' => 0,
          'key' => '',
          'js' => $request->get('type')->toBoolean(),
          'owner' => 'index',
          'type' => 'text'
        );
        $language['datas'][0]['key'] = '';
        foreach ($languages as $lng) {
          $language['datas'][0][$lng] = '';
        }
        $language = (object)$language;
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
      $ul->appendChild('<li><span>{LNG_'.($id > 0 ? 'Edit' : 'Create').'}</span></li>');
      $section->add('header', array(
        'innerHTML' => '<h1 class="icon-language">'.$this->title.'</h1>'
      ));
      // แสดงฟอร์ม
      $section->appendChild(createClass('Index\Languageedit\View')->render($request, $language));
      return $section->render();
    }
    // 404.html
    return \Index\Error\Controller::page404();
  }
}