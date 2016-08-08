<?php
/*
 * @filesource index/views/other.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Other;

use \Kotchasan\Html;

/**
 * ตั้งค่าอื่นๆ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * module=other
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
        'action' => 'index.php/index/model/other/save',
        'onsubmit' => 'doFormSubmit',
        'ajax' => true
    ));
    $fieldset = $form->add('fieldset', array(
      'title' => '{LNG_General}'
    ));
    // member_reservir
    if (empty($config->member_reserv)) {
      $config->member_reserv = array(
        'website',
        'webmaster',
        'cms',
        'gcms',
        'module',
        'website',
        'member',
        'members',
        'register',
        'edit',
        'forgot'
      );
    }
    $fieldset->add('textarea', array(
      'id' => 'member_reserv',
      'labelClass' => 'g-input icon-file',
      'itemClass' => 'item',
      'label' => '{LNG_Member reserve}',
      'comment' => '{LNG_Do not use these names as a member (one per line)}',
      'rows' => 6,
      'value' => implode("\n", $config->member_reserv)
    ));
    // wordrude
    if (empty($config->wordrude)) {
      $config->wordrude = array(
        'ashole',
        'a s h o l e',
        'a.s.h.o.l.e',
        'bitch',
        'b i t c h',
        'b.i.t.c.h',
        'shit',
        's h i t',
        's.h.i.t',
        'fuck',
        'dick',
        'f u c k',
        'd i c k',
        'f.u.c.k',
        'd.i.c.k',
        'มึง',
        'มึ ง',
        'ม ึ ง',
        'ม ึง',
        'มงึ',
        'มึ.ง',
        'มึ_ง',
        'มึ-ง',
        'มึ+ง',
        'กู',
        'ควย',
        'ค ว ย',
        'ค.ว.ย',
        'คอ วอ ยอ',
        'คอ-วอ-ยอ',
        'ปี้',
        'เหี้ย',
        'ไอ้เหี้ย',
        'เฮี้ย',
        'ชาติหมา',
        'ชาดหมา',
        'ช า ด ห ม า',
        'ช.า.ด.ห.ม.า',
        'ช า ติ ห ม า',
        'ช.า.ติ.ห.ม.า',
        'สัดหมา',
        'สัด',
        'เย็ด',
        'หี',
        'สันดาน',
        'แม่ง',
        'ระยำ',
        'ส้นตีน',
        'แตด',
      );
    }
    $fieldset->add('textarea', array(
      'id' => 'wordrude',
      'labelClass' => 'g-input icon-file',
      'itemClass' => 'item',
      'label' => '{LNG_Bad words}',
      'comment' => '{LNG_List of bad words (one per line)}',
      'rows' => 6,
      'value' => implode("\n", $config->wordrude)
    ));
    // wordrude_replace
    $fieldset->add('text', array(
      'id' => 'wordrude_replace',
      'labelClass' => 'g-input icon-edit',
      'itemClass' => 'item',
      'label' => '{LNG_Replace}',
      'comment' => '{LNG_Bad words will be replaced with this message}',
      'value' => isset($config->wordrude_replace) ? $config->wordrude_replace : 'xxx'
    ));
    // counter_digit
    $fieldset->add('text', array(
      'id' => 'counter_digit',
      'labelClass' => 'g-input icon-edit',
      'itemClass' => 'item',
      'label' => '{LNG_Digits of the counter}',
      'comment' => '{LNG_Principal amount of the counter for preview}',
      'value' => isset($config->counter_digit) ? $config->counter_digit : self::$cfg->counter_digit
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