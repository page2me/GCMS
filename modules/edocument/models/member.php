<?php
/**
 * @filesource edocument/models/member.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Member;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 * โมเดลสำหรับแสดงรายการเอกสารของสมาชิก (member.php)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Orm\Field
{
  /**
   * ชื่อตาราง
   *
   * @var string
   */
  protected $table = 'edocument A';

  public function getConfig()
  {
    return array(
      'join' => array(
        array(
          'INNER',
          'Index\Modules\Model',
          array(
            array('M.id', 'A.module_id')
          )
        )
      ),
      'order' => array(
        'A.last_update'
      )
    );
  }

  /**
   * รับค่าจาก action ของ table
   */
  public static function action()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isMember()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        if (self::$request->post('action')->toString() == 'delete') {
          // Model
          $model = new \Kotchasan\Model;
          $search = $model->db()->createQuery()
            ->from('edocument')
            ->where(array(
              array('id', self::$request->post('id')->toInt()),
              array('sender_id', $login['id'])
            ))
            ->toArray()
            ->first('id', 'file');
          if ($search) {
            // ลบไฟล์
            @unlink(ROOT_PATH.$search['file']);
            // ลบข้อมูล
            $model->db()->delete($model->getTableName('edocument'), $search['id']);
            $model->db()->delete($model->getTableName('edocument_download'), array('document_id', $search['id']), 0);
            // ลบแถวตาราง
            $ret['remove'] = 'datatable_'.$search['id'];
          }
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    if (!empty($ret)) {
      // คืนค่าเป็น JSON
      echo json_encode($ret);
    }
  }
}
