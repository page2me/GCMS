<?php
/*
 * @filesource document/views/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\View;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Kotchasan\Antispam;
use \Document\Index\Controller;
use \Kotchasan\Date;
use \Kotchasan\Grid;

/**
 * แสดงบทความ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แสดงบทความ
   *
   * @param Request $request
   * @param object $index ข้อมูลโมดูล
   * @return object
   */
  public function index(Request $request, $index)
  {
    // ค่าที่ส่งมา
    $id = $request->get('id')->toInt();
    $alias = $request->get('alias')->text();
    $search = preg_replace('/[+\s]+/u', ' ', $request->get('q')->text());
    // อ่านรายการที่เลือก
    $story = \Document\View\Model::get((int)$index->module_id, $id, $alias);
    if (empty($story)) {
      // 404
      return createClass('Index\PageNotFound\Controller')->init($request, 'document');
    } else {
      // login
      $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
      // สมาชิก true
      $isMember = $login['status'] > -1;
      // แสดงความคิดเห็นได้
      $canReply = !empty($story->can_reply);
      // ผู้ดูแล
      $moderator = Gcms::canConfig($login, $index, 'moderator');
      // สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
      $canView = Gcms::canConfig($login, $index, 'can_view');
      // dir ของรูปภาพอัปโหลด
      $imagedir = ROOT_PATH.DATA_FOLDER.'document/';
      $imageurl = WEB_URL.DATA_FOLDER.'document/';
      // รูปภาพ
      if (!empty($story->picture) && is_file($imagedir.$story->picture)) {
        $story->image_src = $imageurl.$story->picture;
      } else {
        $story->image_src = null;
      }
      if ($canView || $index->viewing == 1) {
        if ($canReply) {
          // antispam
          $antispam = new Antispam();
          // รายการแสดงความคิดเห็น
          $listitem = Grid::create($index->owner, $index->module, 'commentitem');
          foreach (\Document\Comment\Model::get($story) as $no => $item) {
            // moderator และ เจ้าของ สามารถแก้ไขความคิดเห็นได้
            $canEdit = $moderator || ($isMember && $login['id'] == $item->member_id);
            $listitem->add(array(
              '/(edit-{QID}-{RID}-{NO}-{MODULE})/' => $canEdit ? '\\1' : 'hidden',
              '/(delete-{QID}-{RID}-{NO}-{MODULE})/' => $moderator ? '\\1' : 'hidden',
              '/{DETAIL}/' => Gcms::highlightSearch(Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), nl2br($item->detail)), $canView, true, true), $search),
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
        }
        // tags
        $tags = array();
        foreach (explode(',', $story->relate) as $tag) {
          $tags[] = '<a href="'.Gcms::createUrl('tag', $tag).'">'.$tag.'</a>';
        }
        // เนื้อหา
        $detail = Gcms::showDetail(str_replace(array('&#x007B;', '&#x007D;'), array('{', '}'), $story->detail), $canView, true, true);
        $replace = array(
          '/(quote-{QID}-0-0-{MODULE})/' => $canReply ? '\\1' : 'hidden',
          '/{COMMENTLIST}/' => isset($listitem) ? $listitem->render() : '',
          '/{REPLYFORM}/' => $canReply ? Template::load($index->owner, $index->module, 'reply') : '',
          '/<MEMBER>(.*)<\/MEMBER>/s' => $isMember ? '' : '$1',
          '/{TOPIC}/' => $story->topic,
          '/<IMAGE>(.*)<\/IMAGE>/s' => empty($story->image_src) ? '' : '$1',
          '/{IMG}/' => $story->image_src,
          '/{DETAIL}/' => Gcms::HighlightSearch($detail, $search),
          '/{DATE}/' => Date::format($story->create_date),
          '/{DATEISO}/' => date(DATE_ISO8601, $story->create_date),
          '/{COMMENTS}/' => number_format($story->comments),
          '/{VISITED}/' => number_format($story->visited),
          '/{DISPLAYNAME}/' => empty($story->displayname) ? $story->email : $story->displayname,
          '/{STATUS}/' => $story->status,
          '/{UID}/' => (int)$story->member_id,
          '/{LOGIN_PASSWORD}/' => $login['password'],
          '/{LOGIN_EMAIL}/' => $login['email'],
          '/{QID}/' => $story->id,
          '/{MODULE}/' => $index->module,
          '/{MODULEID}/' => $story->module_id,
          '/{ANTISPAM}/' => isset($antispam) ? $antispam->getId() : '',
          '/{ANTISPAMVAL}/' => isset($antispam) && Login::isAdmin() ? $antispam->getValue() : '',
          '/{DELETE}/' => $moderator ? '{LNG_Delete}' : '{LNG_Removal request}',
          '/{TAGS}/' => implode(', ', $tags)
        );
        $story->detail = Template::create($index->owner, $index->module, 'view')->add($replace)->render();
      } else {
        // not login
        $replace = array(
          '/{TOPIC}/' => $story->topic,
          '/{DETAIL}/' => '<div class=error>{LNG_Members Only}</div>'
        );
        $story->detail = Template::create($index->owner, $index->module, 'error')->add($replace)->render();
      }
      // breadcrumb ของโมดูล
      if (!Gcms::isHome($index->module)) {
        $menu = Gcms::$menu->moduleMenu($index->module);
        if ($menu) {
          Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module), $menu->menu_text, $menu->menu_tooltip);
        }
      }
      // breadcrumb ของหมวดหมู่
      if (!empty($story->category)) {
        Gcms::$view->addBreadcrumb(Gcms::createUrl($index->module, '', $story->category_id), Gcms::ser2Str($story->category), Gcms::ser2Str($story->cat_tooltip));
      }
      // breadcrumb ของหน้า
      $story->canonical = Controller::url($index->module, $story->alias, $story->id);
      Gcms::$view->addBreadcrumb($story->canonical, $story->topic);
      // คืนค่า
      return (object)array(
          'image_src' => $story->image_src,
          'canonical' => $story->canonical,
          'module' => $index->module,
          'topic' => $story->topic,
          'description' => $story->description,
          'keywords' => $story->keywords,
          'detail' => $story->detail
      );
    }
  }
}