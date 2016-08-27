<?php
/*
 * @filesource Widgets/Textlink/ModelsAction.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Textlink\Models;

use \Kotchasan\Http\Request;
use \Kotchasan\Login;
use \Kotchasan\Language;

/**
 * Textlink Action
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Action extends \Kotchasan\Model
{

  public function get(Request $request)
  {
    if ($request->initSession() && $request->isReferer() && Login::isAdmin()) {
      // ค่าที่ส่งมา
      $action = $request->post('action')->toString();
      $id = $request->post('id')->filter('0-9,');
      $value = $request->post('val')->toString();
      if ($action == 'delete') {
        // ลบ
        $query = $this->db()->createQuery()
          ->select('id', 'logo')
          ->from('textlink')
          ->where(array('id', explode(',', $id)));
        $ids = array();
        foreach ($query->execute() AS $item) {
          $ids[] = $item->id;
          if ($item->logo != '' && is_file(ROOT_PATH.DATA_FOLDER.'image/'.$item->logo)) {
            unlink(ROOT_PATH.DATA_FOLDER.'image/'.$item->logo);
          }
        }
        $this->db()->createQuery()->delete('textlink', array('id', $ids))->execute();
        // คืนค่า JSON
        echo json_encode(array('location' => 'reload'));
      } elseif ($action == 'move') {
        // sort link
        $max = 1;
        $query = $this->db()->createQuery()->update('textlink');
        foreach (explode(',', $request->post('data')->filter('0-9,')) As $i) {
          $query->set(array('link_order' => $max))->where((int)$i)->execute();
          $max++;
        }
      } elseif ($action == 'styles') {
        // เลือกรูปแบบ
        $styles = include (ROOT_PATH.'Widgets/Textlink/styles.php');
        // template
        if ($value == 'custom') {
          $textlink = $this->db()->createQuery()->from('textlink')->where((int)$id)->first('template');
          if ($textlink) {
            echo $textlink->template;
          }
        } elseif (isset($styles[$value])) {
          echo $styles[$value];
        }
      } elseif ($action == 'published') {
        // เผยแพร่
        $query = $this->db()->createQuery()->where((int)$id);
        $textlink = $query->from('textlink')->first('id', 'published');
        if ($textlink) {
          $published = $textlink->published == 0 ? 1 : 0;
          $query->update('textlink')->set(array('published' => $published))->execute();
          // คืนค่า
          $lng = Language::get('PUBLISHEDS');
          // คืนค่าเป็น JSON
          echo json_encode(array(
            'title' => $lng[$published],
            'class' => 'icon-published'.$published,
            'elem' => 'published_'.$textlink->id
          ));
        }
      } elseif (preg_match('/^published_([0-1])$/', $action, $match)) {
        // เผยแพร่ (multi)
        $this->db()->createQuery()
          ->update('textlink')
          ->set(array('published' => (int)$match[1]))
          ->where(array('id', explode(',', $id)))
          ->execute();
      }
    }
  }
}