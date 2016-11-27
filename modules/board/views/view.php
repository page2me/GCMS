<?php
/*
 * @filesource board/views/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\View;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\Antispam;
use \Board\Index\Controller;
use \Kotchasan\Date;
use \Kotchasan\Grid;

/**
 * แสดงกระทู้
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แสดงกระทู้
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function index(Request $request, $index)
  {
    // ค่าที่ส่งมา
    $id = $request->get('wbid', $request->get('id')->toInt())->toInt();
    $search = preg_replace('/[+\s]+/u', ' ', $request->get('q')->text());
    // อ่านรายการที่เลือก
    $story = \Board\View\Model::get($id);
    if ($story) {
      // login
      $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
      // สมาชิก true
      $isMember = $login['status'] > -1;
      // แสดงความคิดเห็นได้
      $canReply = !empty($index->can_reply);
      // ผู้ดูแล
      $moderator = Gcms::canConfig($login, $index, 'moderator');
      // สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
      $canView = Gcms::canConfig($login, $index, 'can_view');
      // dir ของรูปภาพอัปโหลด
      $imagedir = ROOT_PATH.DATA_FOLDER.'board/';
      $imageurl = WEB_URL.DATA_FOLDER.'board/';
      // รูปภาพ
      if (!empty($story->picture) && is_file($imagedir.$story->picture)) {
        $image_src = $imageurl.$story->picture;
      } else {
        $image_src = '';
      }
      if ($canView || $index->viewing == 1) {
        if ($canReply) {
          // antispam
          $antispam = new Antispam();
          // /board/commentitem.html
          $listitem = Grid::create('board', $index->module, 'commentitem');
          // รายการแสดงความคิดเห็น
          foreach (\Index\Comment\Model::get($story, 'board_r') as $no => $item) {
            // moderator และ เจ้าของ สามารถแก้ไขความคิดเห็นได้
            $canEdit = $moderator || ($isMember && $login['id'] == $item->member_id);
            // รูปภาพของความคิดเห็น
            $picture = $item->picture != '' && is_file($imagedir.$item->picture) ? '<div><figure><img src="'.$imageurl.$item->picture.'" alt="'.$index->topic.'"></figure></div>' : '';
            $listitem->add(array(
              '/(edit-{QID}-{RID}-{NO}-{MODULE})/' => $canEdit ? '\\1' : 'hidden',
              '/(delete-{QID}-{RID}-{NO}-{MODULE})/' => $moderator ? '\\1' : 'hidden',
              '/{DETAIL}/' => $picture.Gcms::highlightSearch(Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), nl2br($item->detail)), $canView, true, true), $search),
              '/{UID}/' => $item->member_id,
              '/{DISPLAYNAME}/' => $item->name,
              '/{STATUS}/' => $item->status,
              '/{DATE}/' => Date::format($item->last_update),
              '/{DATEISO}/' => date(DATE_ISO8601, $item->last_update),
              '/{IP}/' => Gcms::showip($item->ip),
              '/{NO}/' => $no + 1,
              '/{RID}/' => $item->id
            ));
          }
          Gcms::$view->setContents(array(
            '/:size/' => $index->img_upload_size,
            '/:type/' => implode(', ', $index->img_upload_type)
            ), false);
        }
        // แก้ไขกระทู้ (mod หรือ ตัวเอง)
        $canEdit = $moderator || ($isMember && $login['id'] == $story->member_id);
        // รูปภาพในกระทู้
        $picture = empty($image_src) ? '' : '<div><figure><img src="'.$image_src.'" alt="'.$story->topic.'"></figure></div>';
        // เนื้อหา
        $detail = Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), nl2br($story->detail)), $canView, true, true);
        // URL
        $canonical = Controller::url($index->module, $story->id);
        $replace = array(
          '/(edit-{QID}-0-0-{MODULE})/' => $canEdit ? '\\1' : 'hidden',
          '/(delete-{QID}-0-0-{MODULE})/' => $moderator ? '\\1' : 'hidden',
          '/(quote-{QID}-([0-9]+)-([0-9]+)-{MODULE})/' => !$canReply || $story->locked == 1 ? 'hidden' : '\\1',
          '/(pin-{QID}-0-0-{MODULE})/' => $moderator ? '\\1' : 'hidden',
          '/(lock-{QID}-0-0-{MODULE})/' => $moderator ? '\\1' : 'hidden',
          '/{COMMENTLIST}/' => isset($listitem) ? $listitem->render() : '',
          '/{REPLYFORM}/' => $canReply && $story->locked == 0 ? Template::load($index->owner, $index->module, 'reply') : '',
          '/<MEMBER>(.*)<\/MEMBER>/s' => $isMember ? '' : '$1',
          '/<UPLOAD>(.*)<\/UPLOAD>/s' => empty($index->img_upload_type) ? '' : '$1',
          '/{TOPIC}/' => $story->topic,
          '/{DETAIL}/' => $picture.Gcms::HighlightSearch($detail, $search),
          '/{DATE}/' => Date::format($story->create_date),
          '/{DATEISO}/' => date(DATE_ISO8601, $story->create_date),
          '/{COMMENTS}/' => number_format($story->comments),
          '/{VISITED}/' => number_format($story->visited),
          '/{DISPLAYNAME}/' => $story->name,
          '/{STATUS}/' => $story->status,
          '/{UID}/' => (int)$story->member_id,
          '/{LOGIN_PASSWORD}/' => $login['password'],
          '/{LOGIN_EMAIL}/' => $login['email'],
          '/{QID}/' => $story->id,
          '/{URL}/' => $canonical,
          '/{MODULE}/' => $index->module,
          '/{MODULEID}/' => $story->module_id,
          '/{ANTISPAM}/' => isset($antispam) ? $antispam->getId() : '',
          '/{ANTISPAMVAL}/' => isset($antispam) && Login::isAdmin() ? $antispam->getValue() : '',
          '/{DELETE}/' => $moderator ? '{LNG_Delete}' : '{LNG_Removal request}',
          '/{PIN}/' => $story->pin == 0 ? 'un' : '',
          '/{LOCK}/' => $story->locked == 0 ? 'un' : '',
          '/{PIN_TITLE}/' => '{LNG_click to} '.($story->pin == 1 ? '{LNG_Unpin}' : '{LNG_Pin}'),
          '/{LOCK_TITLE}/' => '{LNG_click to} '.($story->locked == 1 ? '{LNG_Unlock}' : '{LNG_Lock}')
        );
        // /board/view.html
        $detail = Template::create('board', $index->module, 'view')->add($replace)->render();
      } else {
        // not login
        $replace = array(
          '/{TOPIC}/' => $story->topic,
          '/{DETAIL}/' => '<div class=error>{LNG_Members Only}</div>'
        );
        $detail = Template::create($index->owner, $index->module, 'error')->add($replace)->render();
      }
      // breadcrumb ของโมดูล
      if (!Gcms::$menu->isHome($index->index_id)) {
        $menu = Gcms::$menu->findTopLevelMenu($index->index_id);
        if ($menu) {
          Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $menu->menu_text, $menu->menu_tooltip);
        } else {
          Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $index->topic, $index->description);
        }
      }
      // breadcrumb ของหมวดหมู่
      if (!empty($story->category)) {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', $story->category_id), Gcms::ser2Str($story->category), Gcms::ser2Str($story->cat_tooltip));
      }
      // breadcrumb ของหน้า
      Gcms::$view->addBreadcrumb($canonical, $story->topic);
      // คืนค่า
      return (object)array(
          'image_src' => $image_src,
          'canonical' => $canonical,
          'module' => $index->module,
          'topic' => $story->topic,
          'description' => Gcms::html2txt($detail),
          'keywords' => $story->topic,
          'detail' => $detail
      );
    }
    // 404
    return createClass('Index\PageNotFound\Controller')->init('board');
  }
}