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
    $index->id = $request->get('id')->toInt();
    $index->alias = $request->get('alias')->text();
    $index->q = preg_replace('/[+\s]+/u', ' ', $request->get('q')->text());
    // อ่านรายการที่เลือก
    $index = \Document\View\Model::get($index);
    if ($index && $index->published) {
      // login
      $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
      // สมาชิก true
      $isMember = $login['status'] > -1;
      // ผู้ดูแล
      $moderator = Gcms::canConfig($login, $index, 'moderator');
      // สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
      $canView = Gcms::canConfig($login, $index, 'can_view');
      // dir ของรูปภาพอัปโหลด
      $imagedir = ROOT_PATH.DATA_FOLDER.'document/';
      $imageurl = WEB_URL.DATA_FOLDER.'document/';
      // รูปภาพ
      if (!empty($index->picture) && is_file($imagedir.$index->picture)) {
        $image_src = $imageurl.$index->picture;
      } else {
        $image_src = '';
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
      // breadcrumb ของหน้า
      $canonical = Controller::url($index->module, $index->alias, $index->id);
      Gcms::$view->addBreadcrumb($canonical, $index->topic, $index->description);
      if ($canView || $index->viewing == 1) {
        // แสดงความคิดเห็นได้ จากการตั้งค่าโมดูล
        $canReply = !empty($index->can_reply);
        if ($canReply) {
          // antispam
          $antispam = new Antispam();
          // /document/commentitem.html
          $listitem = Grid::create('document', $index->module, 'commentitem');
          // รายการแสดงความคิดเห็น
          foreach (\Index\Comment\Model::get($index) as $no => $item) {
            // moderator และ เจ้าของ สามารถแก้ไขความคิดเห็นได้
            $canEdit = $moderator || ($isMember && $login['id'] == $item->member_id);
            $listitem->add(array(
              '/(edit-{QID}-{RID}-{NO}-{MODULE})/' => $canEdit ? '\\1' : 'hidden',
              '/(delete-{QID}-{RID}-{NO}-{MODULE})/' => $moderator ? '\\1' : 'hidden',
              '/{DETAIL}/' => Gcms::highlightSearch(Gcms::showDetail(str_replace(array('{', '}'), array('&#x007B;', '&#x007D;'), nl2br($item->detail)), $canView, true, true), $index->q),
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
        foreach (explode(',', $index->relate) as $tag) {
          $tags[] = '<a href="'.Gcms::createUrl('tag', $tag).'">'.$tag.'</a>';
        }
        // เนื้อหา
        $detail = Gcms::showDetail(str_replace(array('&#x007B;', '&#x007D;'), array('{', '}'), $index->detail), $canView, true, true);
        // แสดงความคิดเห็นได้ จากการตั้งค่าโมดูล และ จากบทความ
        $canReply = $canReply && $index->can_reply == 1;
        $replace = array(
          '/(quote-{QID}-0-0-{MODULE})/' => $canReply ? '\\1' : 'hidden',
          '/{COMMENTLIST}/' => isset($listitem) ? $listitem->render() : '',
          '/{REPLYFORM}/' => $canReply ? Template::load('document', $index->module, 'reply') : '',
          '/<MEMBER>(.*)<\/MEMBER>/s' => $isMember ? '' : '$1',
          '/{TOPIC}/' => $index->topic,
          '/<IMAGE>(.*)<\/IMAGE>/s' => empty($image_src) ? '' : '$1',
          '/{IMG}/' => $image_src,
          '/{DETAIL}/' => Gcms::HighlightSearch($detail, $index->q),
          '/{DATE}/' => Date::format($index->create_date),
          '/{DATEISO}/' => date(DATE_ISO8601, $index->create_date),
          '/{COMMENTS}/' => number_format($index->comments),
          '/{VISITED}/' => number_format($index->visited),
          '/{DISPLAYNAME}/' => empty($index->displayname) ? $index->email : $index->displayname,
          '/{STATUS}/' => $index->status,
          '/{UID}/' => (int)$index->member_id,
          '/{LOGIN_PASSWORD}/' => $login['password'],
          '/{LOGIN_EMAIL}/' => $login['email'],
          '/{QID}/' => $index->id,
          '/{CATID}/' => $index->category_id,
          '/{MODULE}/' => $index->module,
          '/{MODULEID}/' => $index->module_id,
          '/{ANTISPAM}/' => isset($antispam) ? $antispam->getId() : '',
          '/{ANTISPAMVAL}/' => isset($antispam) && Login::isAdmin() ? $antispam->getValue() : '',
          '/{DELETE}/' => $moderator ? '{LNG_Delete}' : '{LNG_Removal request}',
          '/{TAGS}/' => implode(', ', $tags),
          '/{URL}/' => $canonical,
          '/{XURL}/' => rawurlencode($canonical)
        );
        // /document/view.html
        $detail = Template::create('document', $index->module, 'view')->add($replace);
      } else {
        // not login
        $replace = array(
          '/{TOPIC}/' => $index->topic,
          '/{DETAIL}/' => '<div class=error>{LNG_Members Only}</div>'
        );
        // /document/error.html
        $detail = Template::create('document', $index->module, 'error')->add($replace);
      }
      // คืนค่า
      return (object)array(
          'image_src' => $image_src,
          'canonical' => $canonical,
          'module' => $index->module,
          'topic' => $index->topic,
          'description' => $index->description,
          'keywords' => $index->keywords,
          'detail' => $detail->render()
      );
    }
    // 404
    return createClass('Index\PageNotFound\Controller')->init('document');
  }
}