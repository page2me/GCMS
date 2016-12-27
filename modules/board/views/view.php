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
    $index->id = $request->get('wbid', $request->get('id')->toInt())->toInt();
    $index->q = preg_replace('/[+\s]+/u', ' ', $request->get('q')->text());
    // อ่านรายการที่เลือก
    $index = \Board\View\Model::get($index);
    if ($index) {
      // login
      $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
      // สมาชิก true
      $isMember = $login['status'] > -1;
      // ผู้ดูแล
      $moderator = Gcms::canConfig($login, $index, 'moderator');
      // สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
      $canView = Gcms::canConfig($login, $index, 'can_view');
      // รูปภาพ
      $dir = DATA_FOLDER.'board/';
      $imagedir = ROOT_PATH.$dir;
      if (!empty($index->picture) && is_file($imagedir.$index->picture)) {
        $index->picture = $dir.$index->picture;
      } else {
        $index->picture = '';
      }
      // breadcrumb ของโมดูล
      if (!Gcms::$menu->isHome($index->index_id)) {
        $menu = Gcms::$menu->findTopLevelMenu($index->index_id);
        if ($menu) {
          Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $menu->menu_text, $menu->menu_tooltip);
        }
      }
      // breadcrumb ของหมวดหมู่
      if (!empty($index->category)) {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', $index->category_id), Gcms::ser2Str($index->category), Gcms::ser2Str($index->cat_tooltip));
      }
      // URL ของหน้า
      $index->canonical = Controller::url($index->module, $index->id);
      // breadcrumb ของหน้า
      Gcms::$view->addBreadcrumb($index->canonical, $index->topic);
      if ($canView || $index->viewing == 1) {
        // แสดงความคิดเห็นได้ จากการตั้งค่าโมดูล
        $canReply = !empty($index->can_reply);
        if ($canReply) {
          // query รายการแสดงความคิดเห็น
          $index->comment_items = \Index\Comment\Model::get($index, 'board_r');
          // /board/commentitem.html
          $listitem = Grid::create('board', $index->module, 'commentitem');
          // รายการแสดงความคิดเห็น
          foreach ($index->comment_items as $no => $item) {
            // moderator และ เจ้าของ สามารถแก้ไขความคิดเห็นได้
            $canEdit = $moderator || ($isMember && $login['id'] == $item->member_id);
            // รูปภาพของความคิดเห็น
            $picture = $item->picture != '' && is_file($imagedir.$item->picture) ? '<div><figure><img src="'.WEB_URL.$dir.$item->picture.'" alt="'.$index->topic.'"></figure></div>' : '';
            $listitem->add(array(
              '/(edit-{QID}-{RID}-{NO}-{MODULE})/' => $canEdit ? '\\1' : 'hidden',
              '/(delete-{QID}-{RID}-{NO}-{MODULE})/' => $moderator ? '\\1' : 'hidden',
              '/{DETAIL}/' => $picture.Gcms::highlightSearch(Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), nl2br($item->detail)), $canView, true, true), $index->q),
              '/{UID}/' => $item->member_id,
              '/{DISPLAYNAME}/' => $item->displayname,
              '/{STATUS}/' => $item->status,
              '/{DATE}/' => Date::format($item->last_update),
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
        $canEdit = $moderator || ($isMember && $login['id'] == $index->member_id);
        // รูปภาพในกระทู้
        $picture = empty($index->picture) ? '' : '<div><figure><img src="'.WEB_URL.$index->picture.'" alt="'.$index->topic.'"></figure></div>';
        // เนื้อหา
        $index->detail = Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), nl2br($index->detail)), $canView, true, true);
        // description
        $index->description = Gcms::html2txt($index->detail);
        $replace = array(
          '/(edit-{QID}-0-0-{MODULE})/' => $canEdit ? '\\1' : 'hidden',
          '/(delete-{QID}-0-0-{MODULE})/' => $moderator ? '\\1' : 'hidden',
          '/(quote-{QID}-([0-9]+)-([0-9]+)-{MODULE})/' => !$canReply || $index->locked == 1 ? 'hidden' : '\\1',
          '/(pin-{QID}-0-0-{MODULE})/' => $moderator ? '\\1' : 'hidden',
          '/(lock-{QID}-0-0-{MODULE})/' => $moderator ? '\\1' : 'hidden',
          '/{COMMENTLIST}/' => isset($listitem) ? $listitem->render() : '',
          '/{REPLYFORM}/' => $canReply && $index->locked == 0 ? Template::load($index->owner, $index->module, 'reply') : '',
          '/<MEMBER>(.*)<\/MEMBER>/s' => $isMember ? '' : '$1',
          '/<UPLOAD>(.*)<\/UPLOAD>/s' => empty($index->img_upload_type) ? '' : '$1',
          '/{TOPIC}/' => $index->topic,
          '/{DETAIL}/' => $picture.Gcms::HighlightSearch($index->detail, $index->q),
          '/{DATE}/' => Date::format($index->create_date),
          '/{COMMENTS}/' => number_format($index->comments),
          '/{VISITED}/' => number_format($index->visited),
          '/{DISPLAYNAME}/' => $index->name,
          '/{STATUS}/' => $index->status,
          '/{UID}/' => (int)$index->member_id,
          '/{LOGIN_PASSWORD}/' => $login['password'],
          '/{LOGIN_EMAIL}/' => $login['email'],
          '/{QID}/' => $index->id,
          '/{URL}/' => $index->canonical,
          '/{MODULE}/' => $index->module,
          '/{MODULEID}/' => $index->module_id,
          '/{TOKEN}/' => $request->createToken(),
          '/{DELETE}/' => $moderator ? '{LNG_Delete}' : '{LNG_Removal request}',
          '/{PIN}/' => $index->pin == 0 ? 'un' : '',
          '/{LOCK}/' => $index->locked == 0 ? 'un' : '',
          '/{PIN_TITLE}/' => '{LNG_click to} '.($index->pin == 1 ? '{LNG_Unpin}' : '{LNG_Pin}'),
          '/{LOCK_TITLE}/' => '{LNG_click to} '.($index->locked == 1 ? '{LNG_Unlock}' : '{LNG_Lock}')
        );
        // /board/view.html
        $detail = Template::create('board', $index->module, 'view')->add($replace);
        // JSON-LD
        Gcms::$view->setJsonLd(\Board\Jsonld\View::generate($index));
      } else {
        // not login
        $replace = array(
          '/{TOPIC}/' => $index->topic,
          '/{DETAIL}/' => '<div class=error>{LNG_Members Only}</div>'
        );
        // /board/error.html
        $detail = Template::create('board', $index->module, 'error')->add($replace);
      }
      // คืนค่า
      return (object)array(
          'image_src' => $index->picture == '' ? '' : WEB_URL.$index->picture,
          'canonical' => $index->canonical,
          'module' => $index->module,
          'topic' => $index->topic,
          'description' => $index->description,
          'keywords' => $index->topic,
          'detail' => $detail->render()
      );
    }
    // 404
    return createClass('Index\PageNotFound\Controller')->init('board');
  }
}