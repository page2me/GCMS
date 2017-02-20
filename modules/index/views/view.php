<?php
/*
 * @filesource index/views/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\View;

use \Kotchasan\Http\Request;
use \Kotchasan\Template;
use \Kotchasan\Language;
use \Gcms\Gcms;
use \Kotchasan\Date;
use \Kotchasan\Login;

/**
 * module=view
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * แสดงข้อมูลสมาชิก
   *
   * @param Request $request
   * @return object
   */
  public function render(Request $request)
  {
    $topic = Language::get('Personal information').' '.self::$cfg->web_title;
    if (Login::isMember()) {
      $user = \Index\User\Model::getUserById($request->request('id')->toInt());
      if ($user) {
        // /member/view.html
        $template = Template::create('member', 'member', 'view');
        $template->add(array(
          '/{ID}/' => $user->id,
          '/{EMAIL}/' => $user->email,
          '/{FNAME}/' => $user->fname,
          '/{LNAME}/' => $user->lname,
          '/{SEX}/' => $user->sex === 'f' || $user->sex === 'm' ? $user->sex : 'u',
          '/{DATE}/' => $user->create_date,
          '/{WEBSITE}/' => $user->website,
          '/{VISITED}/' => $user->visited,
          '/{LASTVISITED}/' => $user->lastvisited,
          '/{POST}/' => number_format($user->post),
          '/{REPLY}/' => number_format($user->reply),
          '/{STATUS}/' => isset(self::$cfg->member_status[$user->status]) ? self::$cfg->member_status[$user->status] : 'Unknow',
          '/{COLOR}/' => $user->status,
          '/{SOCIAL}/' => $user->fb == 1 ? 'icon-facebook' : '',
          '/{TOPIC}/' => $topic
        ));
        // breadcrumbs
        $canonical = WEB_URL.'index.php?module=member&amp;id='.$user->id;
        Gcms::$view->addBreadcrumb($canonical, $topic);
        // คืนค่า
        return (object)array(
            'detail' => $template->render(),
            'keywords' => self::$cfg->web_title,
            'description' => self::$cfg->web_description,
            'topic' => $topic,
            'canonical' => $canonical,
            'menu' => 'member'
        );
      }
      // ไม่พบสมาชิก
      return createClass('Index\PageNotFound\Controller')->init('index');
    } else {
      // ไม่ได้ login
      return createClass('Index\PageNotFound\Controller')->init('index', 'Members Only');
    }
  }
}