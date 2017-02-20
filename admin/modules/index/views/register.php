<?php
/*
 * @filesource index/views/register.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Register;

use \Kotchasan\Html;

/**
 * module=register
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{

  /**
   * Register Form
   */
  public function render()
  {
    // register form
    $form = Html::create('form', array(
        'id' => 'setup_frm',
        'class' => 'setup_frm',
        'autocomplete' => 'off',
        'action' => 'index.php/index/model/updateprofile/save',
        'onsubmit' => 'doFormSubmit',
        'token' => true,
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_Register}'
    ));
    // email
    $fieldset->add('email', array(
      'id' => 'register_email',
      'itemClass' => 'item',
      'labelClass' => 'g-input icon-email',
      'label' => '{LNG_Email}',
      'comment' => '{LNG_The system will send the registration information to this e-mail. Please use real email address}',
      'maxlength' => 255,
      'validator' => array('keyup,change', 'checkEmail', 'index.php/index/model/checker/email')
    ));
    $groups = $fieldset->add('groups');
    // password
    $groups->add('password', array(
      'id' => 'register_password',
      'itemClass' => 'width50',
      'labelClass' => 'g-input icon-password',
      'label' => '{LNG_Password}',
      'comment' => '{LNG_Passwords must be at least four characters}',
      'maxlength' => 20,
      'validator' => array('keyup,change', 'checkPassword')
    ));
    // repassword
    $groups->add('password', array(
      'id' => 'register_repassword',
      'itemClass' => 'width50',
      'labelClass' => 'g-input icon-password',
      'label' => '{LNG_Repassword}',
      'comment' => '{LNG_Enter your password again}',
      'maxlength' => 20,
      'validator' => array('keyup,change', 'checkPassword')
    ));
    $fieldset->add('select', array(
      'id' => 'register_status',
      'itemClass' => 'item',
      'label' => '{LNG_Member status}',
      'labelClass' => 'g-input icon-star0',
      'options' => self::$cfg->member_status
    ));
    $fieldset->add('checkbox', array(
      'id' => 'register_admin_access',
      'itemClass' => 'item',
      'label' => '{LNG_Access to the system administrator.}',
      'value' => 1
    ));
    $fieldset = $form->add('fieldset', array(
      'class' => 'submit'
    ));
    // submit
    $fieldset->add('submit', array(
      'class' => 'button save large',
      'value' => '{LNG_Register}'
    ));
    $fieldset->add('hidden', array(
      'id' => 'register_id',
      'value' => 0
    ));
    return $form->render();
  }
}