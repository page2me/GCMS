<?php
/*
 * @filesource document/views/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\View;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Gcms\Gcms;
use \Kotchasan\Login;
use \Document\Index\Controller;
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
    $index->id = $request->request('id')->toInt();
    $index->alias = $request->request('alias')->text();
    $index->q = preg_replace('/[+\s]+/u', ' ', $request->request('q')->text());
    // อ่านรายการที่เลือก
    $index = \Document\View\Model::get($request, $index);
    if ($index && ($index->published || Login::isAdmin())) {
      // URL ของหน้า
      $index->canonical = Controller::url($index->module, $index->alias, $index->id, false);
      // login
      $login = $request->session('login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''))->all();
      // สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
      $canView = Gcms::canConfig($login, $index, 'can_view');
      if ($canView || $index->viewing == 1) {
        // สมาชิก true
        $isMember = $login['status'] > -1;
        // ผู้ดูแล
        $moderator = Gcms::canConfig($login, $index, 'moderator');
        // รูปภาพ
        $dir = DATA_FOLDER.'document/';
        $imagedir = ROOT_PATH.$dir;
        if (!empty($index->picture) && is_file($imagedir.$index->picture)) {
          $size = @getimagesize($imagedir.$index->picture);
          if ($size) {
            $index->image = array(
              '@type' => 'ImageObject',
              'url' => WEB_URL.$dir.$index->picture,
              'width' => $size[0],
              'height' => $size[1],
            );
          }
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
        Gcms::$view->addBreadcrumb($index->canonical, $index->topic, $index->description);
        // AMP
        if (!empty(self::$cfg->amp)) {
          Gcms::$view->metas['amphtml'] = '<link rel="amphtml" href="'.WEB_URL.'amp.php?module='.$index->module.'&amp;id='.$index->id.'">';
        }
        // แสดงความคิดเห็นได้ จากการตั้งค่าโมดูล
        $canReply = !empty($index->can_reply);
        if ($canReply) {
          // query รายการแสดงความคิดเห็น
          $index->comment_items = \Index\Comment\Model::get($index);
          // /document/commentitem.html
          $listitem = Grid::create('document', $index->module, 'commentitem');
          // รายการแสดงความคิดเห็น
          foreach ($index->comment_items as $no => $item) {
            // moderator และ เจ้าของ สามารถแก้ไขความคิดเห็นได้
            $canEdit = $moderator || ($isMember && $login['id'] == $item->member_id);
            $listitem->add(array(
              '/(edit-{QID}-{RID}-{NO}-{MODULE})/' => $canEdit ? '\\1' : 'hidden',
              '/(delete-{QID}-{RID}-{NO}-{MODULE})/' => $moderator ? '\\1' : 'hidden',
              '/{DETAIL}/' => Gcms::highlightSearch(Gcms::showDetail(nl2br($item->detail), $canView, true, true), $index->q),
              '/{UID}/' => $item->member_id,
              '/{DISPLAYNAME}/' => $item->displayname,
              '/{STATUS}/' => $item->status,
              '/{DATE}/' => $item->last_update,
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
        $index->detail = Gcms::showDetail(str_replace(array('&#x007B;', '&#x007D;'), array('{', '}'), $index->detail), $canView, true, true);
        // แสดงความคิดเห็นได้ จากการตั้งค่าโมดูล และ จากบทความ
        $canReply = $canReply && $index->canReply == 1;
        $replace = array(
          '/(quote-{QID}-0-0-{MODULE})/' => $canReply ? '\\1' : 'hidden',
          '/{COMMENTLIST}/' => isset($listitem) ? $listitem->render() : '',
          '/{REPLYFORM}/' => $canReply ? Template::load('document', $index->module, 'reply') : '',
          '/<MEMBER>(.*)<\/MEMBER>/s' => $isMember ? '' : '$1',
          '/{TOPIC}/' => $index->topic,
          '/<IMAGE>(.*)<\/IMAGE>/s' => isset($index->image) ? '$1' : '',
          '/{IMG}/' => isset($index->image) ? $index->image['url'] : '',
          '/{DETAIL}/' => Gcms::HighlightSearch($index->detail, $index->q),
          '/{DATE}/' => $index->create_date,
          '/{COMMENTS}/' => number_format($index->comments),
          '/{VISITED}/' => number_format($index->visited),
          '/{DISPLAYNAME}/' => $index->displayname,
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
          '/{TAGS}/' => implode('', $tags),
          '/{CATID}/' => $index->category_id,
          '/{XURL}/' => rawurlencode($index->canonical)
        );
        // /document/view.html
        $detail = Template::create('document', $index->module, 'view')->add($replace);
        // JSON-LD
        Gcms::$view->setJsonLd(\Document\Jsonld\View::generate($index));
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
          'image_src' => $index->picture == '' ? '' : WEB_URL.$index->picture,
          'canonical' => $index->canonical,
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