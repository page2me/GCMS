<?php
/*
 * @filesource index/controllers/member.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Member;

use \Kotchasan\Http\Request;

/**
 * Controller หลัก สำหรับแสดง frontend ของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * แสดงผลฟอร์ม ที่เรียกมาจาก GModal
   *
   * @param Request $request
   */
  public function modal(Request $request)
  {
    $action = $request->post('action')->toString();
    if ($action === 'register') {
      $page = createClass('Index\Register\View')->render($request, true);
    } elseif ($action === 'forgot') {
      $page = createClass('Index\Forgot\View')->render($request, true);
    } else {
      // 404
      $page = createClass('Index\PageNotFound\Controller')->init($request, 'index');
    }
    echo json_encode($page);
  }

  public function editprofile(Request $request)
  {
    return createClass('Index\Editprofile\View')->render($request);
  }

  public function sendmail(Request $request)
  {
    return createClass('Index\Sendmail\View')->render($request);
  }

  public function register(Request $request)
  {
    return createClass('Index\Register\View')->render($request, false);
  }

  public function forgot(Request $request)
  {
    return createClass('Index\Forgot\View')->render($request);
  }

  public function dologin(Request $request)
  {
    return createClass('Index\Dologin\View')->render($request);
  }

  public function member(Request $request)
  {
    return createClass('Index\View\View')->render($request);
  }

  public function activate(Request $request)
  {
    return createClass('Index\Activate\View')->render($request);
  }
}