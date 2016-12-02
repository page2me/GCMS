<?php
/*
 * @filesource index/views/meta.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Meta;

use \Kotchasan\Html;

/**
 * ตั้งค่า SEO & Social
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{

  /**
   * module=meta
   *
   * @param object $config
   * @return string
   */
  public function render($config)
  {
    // form
    $form = Html::create('form', array(
        'id' => 'setup_frm',
        'class' => 'setup_frm',
        'autocomplete' => 'off',
        'action' => 'index.php/index/model/meta/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'titleClass' => 'icon-google',
      'title' => '{LNG_Google}'
    ));
    // google_site_verification
    $fieldset->add('text', array(
      'id' => 'google_site_verification',
      'labelClass' => 'g-input icon-edit',
      'itemClass' => 'item',
      'label' => '{LNG_Site verification code}',
      'comment' => '{LNG_&lt;meta name="google-site-verification" content="<em>xxxxxxxxxx</em>" /&gt;}',
      'value' => isset($config->google_site_verification) ? $config->google_site_verification : ''
    ));
    // google_profile
    $fieldset->add('text', array(
      'id' => 'google_profile',
      'labelClass' => 'g-input icon-edit',
      'itemClass' => 'item',
      'label' => '{LNG_Google page ID}',
      'comment' => '{LNG_https://plus.google.com/<em>xxxxxxxxxx</em>/}',
      'value' => isset($config->google_profile) ? $config->google_profile : ''
    ));
    // amp
    $fieldset->add('select', array(
      'id' => 'amp',
      'labelClass' => 'g-input icon-amp',
      'itemClass' => 'item',
      'label' => '{LNG_Accelerated Mobile Pages}',
      'options' => \Kotchasan\Language::get('BOOLEANS'),
      'value' => isset($config->amp) ? $config->amp : 0
    ));
    $fieldset = $form->add('fieldset', array(
      'titleClass' => 'icon-bing',
      'title' => '{LNG_Bing}'
    ));
    // msvalidate
    $fieldset->add('text', array(
      'id' => 'msvalidate',
      'labelClass' => 'g-input icon-edit',
      'itemClass' => 'item',
      'label' => '{LNG_Site verification code}',
      'comment' => '{LNG_&lt;meta name="msvalidate.01" content="<em>xxxxxxxxxx</em>" /&gt;}',
      'value' => isset($config->msvalidate) ? $config->msvalidate : ''
    ));
    $fieldset = $form->add('fieldset', array(
      'titleClass' => 'icon-facebook',
      'title' => '{LNG_Facebook}'
    ));
    // facebook_appId
    $fieldset->add('text', array(
      'id' => 'facebook_appId',
      'labelClass' => 'g-input icon-edit',
      'itemClass' => 'item',
      'label' => '{LNG_App ID}',
      'value' => isset($config->facebook_appId) ? $config->facebook_appId : ''
    ));
    $fieldset = $form->add('fieldset', array(
      'titleClass' => 'icon-image',
      'title' => '{LNG_Image}'
    ));
    // site_logo
    $fieldset->add('file', array(
      'id' => 'site_logo',
      'labelClass' => 'g-input icon-upload',
      'itemClass' => 'item',
      'label' => '{LNG_Browse file}',
      'comment' => '{LNG_Select Image size 696 * 464 pixel jpg types only, used as the logo of the site. When sharing}',
      'dataPreview' => 'logoImage',
      'previewSrc' => is_file(ROOT_PATH.DATA_FOLDER.'image/site_logo.jpg') ? WEB_URL.DATA_FOLDER.'image/site_logo.jpg' : WEB_URL.'skin/img/blank.gif'
    ));
    // delete_site_logo
    $fieldset->add('checkbox', array(
      'id' => 'delete_site_logo',
      'itemClass' => 'subitem',
      'label' => '{LNG_remove this photo}',
      'value' => 1
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // submit
    $fieldset->add('submit', array(
      'class' => 'button ok large',
      'value' => '{LNG_Save}'
    ));
    return $form->render();
  }
}