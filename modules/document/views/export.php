<?php
/*
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
    // ค่าที่ส่งมา
    $id = $request->get('id')->toInt();
    // อ่านรายการที่เลือก
    $story = \Document\View\Model::get((int)$index->module_id, $id, '');
    if ($story) {
      // login
      $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
      // แสดงความคิดเห็นได้
      $canReply = !empty($story->can_reply);
      // สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
      $canView = Gcms::canConfig($login, $index, 'can_view');
      // dir ของรูปภาพอัปโหลด
      $imagedir = ROOT_PATH.DATA_FOLDER.'document/';
      $imageurl = WEB_URL.DATA_FOLDER.'document/';
      // รูปภาพ
      if (!empty($story->picture) && is_file($imagedir.$story->picture)) {
        $story->image_src = $imageurl.$story->picture;
      }
      if ($canView || $index->viewing == 1) {
        if ($canReply) {
          // รายการแสดงความคิดเห็น
          $listitem = Grid::create($index->owner, $index->module, 'printcommentitem');
          foreach (\Document\Comment\Model::get($story) as $no => $item) {
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
        $detail = Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), $story->detail), $canView, true, true);
        $replace = array(
          '/{COMMENTLIST}/' => isset($listitem) ? $listitem->render() : '',
          '/{TOPIC}/' => $story->topic,
          '/<IMAGE>(.*)<\/IMAGE>/s' => empty($story->image_src) ? '' : '$1',
          '/{IMG}/' => empty($story->image_src) ? '' : $story->image_src,
          '/{DETAIL}/' => $detail,
          '/{DATE}/' => Date::format($story->create_date),
          '/{URL}/' => \Document\Index\Controller::url($index->module, $story->alias, $story->id, false),
          '/{DISPLAYNAME}/' => empty($story->displayname) ? $story->email : $story->displayname
        );
        return Template::create($index->owner, $index->module, 'print')->add($replace)->render();
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
    // ค่าที่ส่งมา
    $id = $request->get('id')->toInt();
    // อ่านรายการที่เลือก
    $story = \Document\View\Model::get((int)$index->module_id, $id, '');
    if ($story) {
      // login
      $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
      // แสดงความคิดเห็นได้
      $canReply = !empty($story->can_reply);
      // สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
      $canView = Gcms::canConfig($login, $index, 'can_view');
      // dir ของรูปภาพอัปโหลด
      $imagedir = ROOT_PATH.DATA_FOLDER.'document/';
      $imageurl = WEB_URL.DATA_FOLDER.'document/';
      // รูปภาพ
      if (!empty($story->picture) && is_file($imagedir.$story->picture)) {
        $story->image_src = $imageurl.$story->picture;
      }
      if ($canView || $index->viewing == 1) {
        if ($canReply) {
          // รายการแสดงความคิดเห็น
          $listitem = Grid::create($index->owner, $index->module, 'printcommentitem');
          foreach (\Document\Comment\Model::get($story) as $no => $item) {
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
        $detail = Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), $story->detail), $canView, true, true);
        $replace = array(
          '/{COMMENTLIST}/' => isset($listitem) ? $listitem->render() : '',
          '/{TOPIC}/' => $story->topic,
          '/<IMAGE>(.*)<\/IMAGE>/s' => empty($story->image_src) ? '' : '$1',
          '/{IMG}/' => empty($story->image_src) ? '' : $story->image_src,
          '/{DETAIL}/' => $detail,
          '/{DATE}/' => Date::format($story->create_date),
          '/{URL}/' => \Document\Index\Controller::url($index->module, $story->alias, $story->id, false),
          '/{DISPLAYNAME}/' => empty($story->displayname) ? $story->email : $story->displayname,
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