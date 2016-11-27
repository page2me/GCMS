<?php
/*
 * @filesource index/models/mailserver.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Mailserver;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Validator;
use \Kotchasan\Config;

/**
 * บันทึกการตั้งค่าระบบอีเมล์
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * form submit
   */
  public function save()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // โหลด config
        $config = Config::load(ROOT_PATH.'settings/config.php');
        // รับค่าจากการ POST
        $save = array(
          'noreply_email' => self::$request->post('noreply_email')->url(),
          'email_charset' => self::$request->post('email_charset')->text(),
          'email_use_phpMailer' => self::$request->post('email_use_phpMailer')->toBoolean(),
          'email_Host' => self::$request->post('email_Host')->text(),
          'email_Port' => self::$request->post('email_Port')->toInt(),
          'email_SMTPAuth' => self::$request->post('email_SMTPAuth')->toBoolean(),
          'email_SMTPSecure' => self::$request->post('email_SMTPSecure')->text(),
          'email_Username' => self::$request->post('email_Username')->quote(),
          'email_Password' => self::$request->post('email_Password')->quote()
        );
        // อีเมล์
        if (empty($save['noreply_email'])) {
          $ret['ret_noreply_email'] = Language::get('Please fill in').' '.Language::get('Email');
        } elseif (!Validator::email($save['noreply_email'])) {
          $ret['ret_noreply_email'] = str_replace(':name', Language::get('Email'), Language::get('Invalid :name'));
        } else {
          $config->noreply_email = $save['noreply_email'];
        }
        $config->email_charset = empty($save['email_charset']) ? 'utf-8' : strtolower($save['email_charset']);
        if (empty($save['email_Host'])) {
          $config->email_Host = 'localhost';
          $config->email_Port = 25;
          $config->email_SMTPSecure = '';
          $config->email_Username = '';
          $config->email_Password = '';
        } else {
          $config->email_Host = $save['email_Host'];
          $config->email_Port = empty($save['email_Port']) ? 25 : $save['email_Port'];
          $config->email_SMTPSecure = isset($save['email_SMTPSecure']) ? $save['email_SMTPSecure'] : '';
          $config->email_Username = isset($save['email_Username']) ? $save['email_Username'] : '';
          if (!empty($save['email_Password'])) {
            $config->email_Password = $save['email_Password'];
          }
        }
        $config->email_use_phpMailer = $save['email_use_phpMailer'];
        $config->email_SMTPAuth = $save['email_SMTPAuth'];
        if (empty($ret)) {
          // save config
          if (Config::save($config, ROOT_PATH.'settings/config.php')) {
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = 'reload';
          } else {
            $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
          }
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}