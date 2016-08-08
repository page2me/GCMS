<?php
/*
 * @filesource index/models/mailtemplate.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Mailtemplate;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Kotchasan\Orm\Recordset;
use \Kotchasan\Orm\Field;

/**
 * ตาราง emailtemplate
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends Field
{
  /**
   * ชื่อตาราง
   *
   * @var string
   */
  protected $table = 'emailtemplate E';

  public function getConfig()
  {
    return array(
      'select' => array(
        'id',
        'email_id',
        'name',
        'language',
        'module',
        'subject'
      ),
      'order' => array(
        'module',
        'email_id',
        'language'
      )
    );
  }

  /**
   * action
   */
  public static function action()
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        if (self::$request->post('action')->toString() === 'delete') {
          $id = self::$request->post('action')->toInt();
          $rs = Recordset::create(get_called_class());
          $index = $rs->find($id);
          if ($index) {
            $index->delete();
          }
          // คืนค่า
          $ret['delete_id'] = self::$request->post('src')->toString().'_'.$id;
          $ret['alert'] = Language::get('Deleted successfully');
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}