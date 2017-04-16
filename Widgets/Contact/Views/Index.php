<?php
/**
 * @filesource Widgets/Contact/Views/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Contact\Views;

use \Kotchasan\Html;
use \Kotchasan\Login;

/**
 * ฟอร์มส่งอีเมล์ถึงแอดมิน
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Gcms\View
{

  /**
   * ฟอร์มส่งจดหมายหาแอดมิน
   *
   * @param array $emails รายชื่อผู้รับ
   * @return string
   */
  public static function render($emails)
  {
    // send email form
    $form = Html::create('form', array(
        'id' => 'write_frm',
        'class' => 'setup_frm',
        'action' => 'xhr.php/Widgets/Contact/Models/Index/send',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true,
        'token' => true
    ));
    $fieldset = $form->add('fieldset');
    // reciever
    $reciever = self::$request->get('to')->topic();
    $fieldset->add('select', array(
      'id' => 'mail_reciever',
      'itemClass' => 'item',
      'labelClass' => 'g-input icon-email-sent',
      'label' => '{LNG_Reciever}',
      'options' => $emails
    ));
    // sender
    $login = Login::isAdmin();
    $fieldset->add('text', array(
      'id' => 'mail_sender',
      'itemClass' => 'item',
      'labelClass' => 'g-input icon-email',
      'label' => '{LNG_Sender}',
      'value' => $login ? $login['email'] : '',
      'placeholder' => '{LNG_Please fill in} {LNG_Sender}'
    ));
    // subject
    $fieldset->add('text', array(
      'id' => 'mail_subject',
      'itemClass' => 'item',
      'labelClass' => 'g-input icon-edit',
      'label' => '{LNG_Subject}',
      'placeholder' => '{LNG_Please fill in} {LNG_Subject}'
    ));
    // detail
    $fieldset->add('textarea', array(
      'id' => 'mail_detail',
      'itemClass' => 'item',
      'labelClass' => 'g-input icon-file',
      'label' => '{LNG_Detail}',
      'rows' => 10,
      'placeholder' => '{LNG_Please fill in} {LNG_Detail}'
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // submit
    $fieldset->add('submit', array(
      'class' => 'button ok large',
      'value' => '{LNG_Send message}'
    ));
    return $form->render();
  }
}