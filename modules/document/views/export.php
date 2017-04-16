<?php
/**
 * @filesource document/views/export.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Export;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Date;
use \Kotchasan\Grid;

/**
 * แสดงหน้าสำหรับพิมพ์
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แสดงหน้าสำหรับพิมพ์
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function printer(Request $request, $index)
  {
    // อ่านรายการที่เลือก
    $index = \Document\View\Model::get($request, (object)array('id' => $request->get('id')->toInt()));
    if ($index && $index->published) {
      // login
      $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
      // แสดงความคิดเห็นได้
      $canReply = !empty($index->can_reply);
      // สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
      $canView = Gcms::canConfig($login, $index, 'can_view');
      if ($canView || $index->viewing == 1) {
        // รูปภาพ
        $dir = DATA_FOLDER.'document/';
        $imagedir = ROOT_PATH.$dir;
        if (!empty($index->picture) && is_file($imagedir.$index->picture)) {
          $size = @getimagesize($imagedir.$index->picture);
          if ($size) {
            $index->picture = WEB_URL.$dir.$index->picture;
            $index->pictureWidth = $size[0];
            $index->pictureHeight = $size[1];
          } else {
            $index->picture = '';
          }
        } else {
          $index->picture = '';
        }
        // URL ของหน้า
        $index->canonical = \Document\Index\Controller::url($index->module, $index->alias, $index->id, false);
        // แสดงความคิดเห็นได้ จากการตั้งค่าโมดูล
        $canReply = !empty($index->can_reply);
        if ($canReply) {
          // query รายการแสดงความคิดเห็น
          $index->comment_items = \Index\Comment\Model::get($index);
          // /document/printcommentitem.html
          $listitem = Grid::create('document', $index->module, 'printcommentitem');
          // รายการแสดงความคิดเห็น
          foreach ($index->comment_items as $no => $item) {
            $item->detail = Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), nl2br($item->detail)), $canView, true, true);
            $listitem->add(array(
              '/{DETAIL}/' => $item->detail,
              '/{DISPLAYNAME}/' => $item->displayname,
              '/{DATE}/' => Date::format($item->last_update),
              '/{IP}/' => Gcms::showip($item->ip),
              '/{NO}/' => $no + 1
            ));
          }
        }
        // เนื้อหา
        $index->detail = Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), $index->detail), $canView, true, true);
        $replace = array(
          '/{COMMENTLIST}/' => isset($listitem) ? $listitem->render() : '',
          '/{TOPIC}/' => $index->topic,
          '/{DETAIL}/' => $index->detail,
          '/{DATE}/' => Date::format($index->create_date),
          '/<IMAGE>(.*)<\/IMAGE>/s' => empty($index->picture) ? '' : '$1',
          '/{IMG}/' => $index->picture,
          '/{DISPLAYNAME}/' => $index->displayname,
          '/{URL}/' => $index->canonical,
        );
        // /document/print.html
        return Template::create('document', $index->module, 'print')->add($replace)->render();
      }
    }
    return false;
  }

  /**
   * ส่งออกเป็นไฟล์ PDF
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function pdf(Request $request, $index)
  {
    // อ่านรายการที่เลือก
    $index = \Document\View\Model::get($request, (object)array('id' => $request->get('id')->toInt()));
    if ($index) {
      // login
      $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
      // แสดงความคิดเห็นได้
      $canReply = !empty($index->can_reply);
      // สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
      $canView = Gcms::canConfig($login, $index, 'can_view');
      // dir ของรูปภาพอัปโหลด
      $imagedir = ROOT_PATH.DATA_FOLDER.'document/';
      $imageurl = WEB_URL.DATA_FOLDER.'document/';
      // รูปภาพ
      if (!empty($index->picture) && is_file($imagedir.$index->picture)) {
        $index->image_src = $imageurl.$index->picture;
      }
      if ($canView || $index->viewing == 1) {
        if ($canReply) {
          // รายการแสดงความคิดเห็น
          $listitem = Grid::create($index->owner, $index->module, 'printcommentitem');
          foreach (\Document\Comment\Model::get($index) as $no => $item) {
            $listitem->add(array(
              '/{DETAIL}/' => Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), nl2br($item->detail)), $canView, true, true),
              '/{DISPLAYNAME}/' => $item->name,
              '/{DATE}/' => Date::format($item->last_update),
              '/{IP}/' => Gcms::showip($item->ip),
              '/{NO}/' => $no + 1
            ));
          }
        }
        // เนื้อหา
        $detail = Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), $index->detail), $canView, true, true);
        $replace = array(
          '/{COMMENTLIST}/' => isset($listitem) ? $listitem->render() : '',
          '/{TOPIC}/' => $index->topic,
          '/<IMAGE>(.*)<\/IMAGE>/s' => empty($index->image_src) ? '' : '$1',
          '/{IMG}/' => empty($index->image_src) ? '' : $index->image_src,
          '/{DETAIL}/' => $detail,
          '/{DATE}/' => Date::format($index->create_date),
          '/{URL}/' => \Document\Index\Controller::url($index->module, $index->alias, $index->id, false),
          '/{DISPLAYNAME}/' => empty($index->displayname) ? $index->email : $index->displayname,
          '/{LNG_([\w\s\.\-\'\(\),%\/:&\#;]+)}/e' => '\Kotchasan\Language::get(array(1=>"$1"))'
        );
        $pdf = new \Kotchasan\Pdf();
        $pdf->AddPage();
        $pdf->WriteHTML(Template::create($index->owner, $index->module, 'print')->add($replace)->render());
        $pdf->Output();
        exit;
      }
    }
  }
}