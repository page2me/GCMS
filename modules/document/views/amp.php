<?php
/*
 * @filesource document/views/amp.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Amp;

use \Kotchasan\Template;
use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Document\Index\Controller;

/**
 * แสดงบทความ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
  private $jsonld;

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
      // ผู้ดูแล
      $moderator = Gcms::canConfig($login, $index, 'moderator');
      // สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
      $canView = Gcms::canConfig($login, $index, 'can_view');
      if ($canView || $index->viewing == 1) {
        // URL ของหน้า
        $canonical = Controller::url($index->module, $index->alias, $index->id);
        $create_date = date(DATE_ISO8601, $index->create_date);
        $this->jsonld = array(
          '@context' => 'http://schema.org',
          '@type' => 'TechArticle',
          'mainEntityOfPage' => array(
            '@type' => 'WebPage',
            '@id' => Gcms::createUrl($index->module)
          ),
          'headline' => $index->topic,
          'datePublished' => $create_date,
          'dateModified' => $create_date,
          'author' => array(
            '@type' => 'Person',
            'name' => empty($index->displayname) ? $index->email : $index->displayname
          ),
          'publisher' => array(
            '@type' => 'Organization',
            'name' => strip_tags(self::$cfg->web_title),
            'logo' => array(
              '@type' => 'ImageObject',
              'url' => WEB_URL.'skin/'.self::$cfg->skin.'/img/logo.png',
              'width' => 80,
              'height' => 60
            )
          ),
          'description' => $index->description
        );
        // img
        $detail = preg_replace_callback('/<img[^>]+src=["\']([^"\']+)[^>]+/is', function($matchs) {
          $size = getimagesize($matchs[1]);
          if (empty($this->jsonld['image'])) {
            $this->jsonld['image'] = array(
              '@type' => 'ImageObject',
              'url' => $matchs[1],
              'height' => $size[1],
              'width' => $size[0]
            );
          }
          return '<amp-img src="'.$matchs[1].'" '.$size[3].'></amp-img';
        }, $index->detail);
        // เนื้อหา
        $detail = Gcms::showDetail(str_replace(array('&#x007B;', '&#x007D;'), array('{', '}'), $detail), $canView, true, true);
        $replace = array(
          '/{TOPIC}/' => $index->topic,
          '/{DETAIL}/' => $detail,
          '/{URL}/' => $canonical,
          '/{JSONLD}/' => json_encode($this->jsonld)
        );
      } else {
        // not login
        $replace = array(
          '/{TOPIC}/' => $index->topic,
          '/{DETAIL}/' => '<div class=error>{LNG_Members Only}</div>'
        );
      }
      // คืนค่า
      return (object)array(
          'canonical' => $canonical,
          'topic' => $index->topic,
          'detail' => Template::create('document', $index->module, 'amp')->add($replace)->render()
      );
    }
    // 404
    return createClass('Index\PageNotFound\Controller')->init('document');
  }
}