<?php
/**
 * @filesource product/models/admin/setup.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Product\Admin\Setup;

use \Kotchasan\Login;
use \Kotchasan\Language;
use \Gcms\Gcms;

/**
 * ตาราง product
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
  protected $table = 'product P';

  /**
   * query หน้าเพจ เรียงลำดับตาม module,language
   *
   * @return array
   */
  public function getConfig()
  {
    return array(
      'select' => array(
        'P.id',
        'P.product_no',
        'D.topic',
        'P.published',
        'P.last_update',
        'P.visited'
      ),
      'join' => array(
        array(
          'LEFT',
          'Product\Admin\Detail\Model',
          array(
            array('D.id', 'P.id'),
            array('D.language', array(Language::name(), '')),
          ),
        ),
      ),
    );
  }

  /**
   * รับค่าจาก action ของ table
   */
  public static function action()
  {
    $ret = array();
    // referer, session, admin
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isMember()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // รับค่าจากการ POST
        $id = self::$request->post('id')->toString();
        $action = self::$request->post('action')->toString();
        $index = \Index\Adminmodule\Model::get('product', self::$request->post('mid')->toInt());
        if ($index && Gcms::canConfig($login, $index, 'can_write')) {
          // Model
          $model = new \Kotchasan\Model;
          if ($action === 'published') {
            // สถานะการเผยแพร่
            $table = $model->getTableName('product');
            $search = $model->db()->first($table, array(array('id', (int)$id), array('module_id', (int)$index->module_id)));
            if ($search) {
              $published = $search->published == 1 ? 0 : 1;
              $model->db()->update($table, $search->id, array('published' => $published));
              // คืนค่า
              $ret['elem'] = 'published_'.$search->id;
              $lng = Language::get('PUBLISHEDS');
              $ret['title'] = $lng[$published];
              $ret['class'] = 'icon-published'.$published;
            }
          } elseif ($action === 'delete' && preg_match('/^[0-9,]+$/', $id)) {
            $id = explode(',', $id);
            // ตรวจสอบรายการ เพื่อลบรูปภาพ
            $query = $model->db()->createQuery()->select('id', 'picture')->from('product')->where(array(array('id', $id), array('module_id', (int)$index->module_id)))->toArray();
            foreach ($query->execute() as $item) {
              // ลบรูปภาพ
              @unlink(ROOT_PATH.DATA_FOLDER.'product/thumb_'.$item['picture']);
              @unlink(ROOT_PATH.DATA_FOLDER.'product/'.$item['picture']);
            }
            // ลบฐานข้อมูล
            $model->db()->createQuery()->delete('product', array(array('id', $id), array('module_id', (int)$index->module_id)))->execute();
            $model->db()->createQuery()->delete('product_detail', array('id', $id))->execute();
            $model->db()->createQuery()->delete('product_price', array('id', $id))->execute();
            // คืนค่า
            $ret['alert'] = Language::get('Deleted successfully');
            $ret['location'] = 'reload';
          }
        } else {
          $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
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