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
class View extends \Kotchasan\View
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
    // facebook_photo
    $img = is_file(ROOT_PATH.DATA_FOLDER.'image/facebook_photo.jpg') ? WEB_URL.DATA_FOLDER.'image/facebook_photo.jpg' : WEB_URL.'skin/img/blank.gif';
    $fieldset->add('file', array(
      'id' => 'facebook_photo',
      'labelClass' => 'g-input icon-upload',
      'itemClass' => 'item',
      'label' => '{LNG_Browse file}',
      'comment' => '{LNG_Select an image file size 200x200 pixel types jpg only, for posting to your Facebook wall. On a shared or a subscription through Facebook.}',
      'dataPreview' => 'fbImage',
      'previewSrc' => $img
    ));
    // delete_facebook_photo
    $fieldset->add('checkbox', array(
      'id' => 'delete_facebook_photo',
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