<?php
/*
 * @filesource Widgets/Share/Controllers/Index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Share\Controllers;

use \Kotchasan\Text;

/**
 * Controller หลัก สำหรับแสดงผล Widget
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Index extends \Kotchasan\Controller
{

  /**
   * แสดงผล Widget
   *
   * @param array $query_string ข้อมูลที่ส่งมาจากการเรียก Widget
   * @return string
   */
  public function get($query_string)
  {
    $id = Text::rndname(10);
    // share on tweeter & facebook
    $widget = '<div id="'.$id.'" class="widget_share'.(empty($query_string['module']) ? '' : '_'.$query_string['module']).'">';
    if (!empty($query_string['module'])) {
      $widget .= '<span><b id="fb_share_count">0</b>SHARE</span>';
      $widget .= '<a class="fb_share icon-facebook" title="Facebook Share">Facebook</a>';
      $widget .= '<a class="twitter_share icon-twitter" title="Twitter">Twitter</a>';
    } else {
      $widget .= '<a class="fb_share icon-facebook" title="Facebook Share"></a>';
      $widget .= '<a class="twitter_share icon-twitter" title="Twitter"></a>';
      $widget .= '<a class="gplus_share icon-googleplus" title="Google Plus"></a>';
      if (!empty(self::$cfg->google_profile)) {
        $widget .= '<a rel=nofollow href="http://plus.google.com/'.self::$cfg->google_profile.'" class="google_profile icon-google" target=_blank title="Google Profile"></a>';
      }
      $widget .= '<a class="line_share icon-comments" title="LINE it!"></a>';
    }
    $widget .= '<script>';
    $widget .= '$G(window).Ready(function(){';
    $widget .= 'initShareButton("'.$id.'");';
    $widget .= '});';
    $widget .= '</script>';
    $widget .= '</div>';
    return $widget;
  }
}