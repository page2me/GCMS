<?php
/*
 * @filesource index/views/dashboard.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Dashboard;

use \Kotchasan\Date;
use \Gcms\Gcms;

/**
 * Database
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * module=dashboard
   *
   * @return string
   */
  public function render()
  {
    $dashboard = new \Index\Dashboard\Model;
    // colors
    $colors = array('#7E57C2', '#FF5722', '#E91E63', '#259B24', '#607D8B', '#2CB6D5', '#FD971F', '#26A694', '#FF5722', '#00BCD4', '#8BC34A', '#616161', '#FFD54F', '#03A9F4', '#795548');
    // ข้อมูล counter
    $counter = $dashboard->counter();
    if (!$counter) {
      $counter = array('counter' => 0, 'visited' => 0, 'members' => 0, 'activate' => 0, 'ban' => 0, 'useronline' => 0);
    }
    $content = array();
    $content[] = '<div class="infobox clear"><ul>';
    Gcms::$dashboard_menus[] = array('clock', '{LNG_Visitors today}', 'index.php?module=report', number_format($counter['visited']), 'visited');
    Gcms::$dashboard_menus[] = array('users', '{LNG_People online}', '', number_format($counter['useronline']), 'useronline');
    $l = sizeof($colors);
    foreach (Gcms::$dashboard_menus as $i => $items) {
      $z = $i % $l;
      $row = '<li class="table" style="border-color:'.$colors[$z].'">';
      $row .= '<span class="td icon-'.$items[0].'" style="background-color:'.$colors[$z].'"></span>';
      $d = !empty($items[4]) ? ' id="'.$items[4].'"' : '';
      $t = $items[3] == '' ? '' : '<span class=c-'.$i.$d.'>'.$items[3].'</span>';
      if ($items[2] == '') {
        $row .= '<span class="detail td">'.$t.$items[1].'</span>';
      } else {
        $row .= '<a class="detail td" href="'.$items[2].'">'.$t.$items[1].'</a>';
      }
      $row .= '</li>';
      $content[] = $row;
    }
    $content[] = '</ul></div>';
    $content[] = '<div class="ggrid collapse dashboard">';
    $content[] = '<div class="block4 float-left">';
    // site report
    $content[] = '<section class=section>';
    $content[] = '<header><h1 class=icon-summary>{LNG_Overview report of the system}</h1></header>';
    $content[] = '<table class="summary fullwidth">';
    $content[] = '<caption>{LNG_Overview and summary of this site}</caption>';
    $content[] = '<tbody>';
    $content[] = '<tr><th scope=row><a href="'.WEB_URL.'admin/index.php?module=member&amp;sort=id%20desc">{LNG_Total Members}</a></th><td class=right>'.number_format($counter['members']).' {LNG_people}</td></tr>';
    $content[] = '<tr class=bg2><th scope=row><a href="'.WEB_URL.'admin/index.php?module=member&amp;sort=activatecode%20desc,id%20desc">{LNG_Members who have not confirmed the email}</a></th><td class=right>'.number_format($counter['activate']).' {LNG_people}</td></tr>';
    $content[] = '<tr><th scope=row><a href="'.WEB_URL.'admin/index.php?module=member&amp;sort=ban%20desc,id%20desc">{LNG_Members were suspended}</a></th><td class=right>'.number_format($counter['ban']).' {LNG_people}</td></tr>';
    $content[] = '<tr class=bg2><th scope=row>{LNG_Visitors total}</th><td class=right>'.number_format($counter['counter']).' {LNG_people}</td></tr>';
    $content[] = '<tr><th scope=row>{LNG_People online}</th><td class=right>'.number_format($counter['useronline']).' {LNG_people}</td></tr>';
    $content[] = '<tr class=bg2><th scope=row><a href="'.WEB_URL.'admin/index.php?module=report">{LNG_Visitors today}</a></th><td class=right>'.number_format($counter['visited']).' {LNG_people}</td></tr>';
    if (is_file(ROOT_PATH.DATA_FOLDER.'index.php')) {
      $date = file_get_contents(ROOT_PATH.DATA_FOLDER.'index.php');
      if (preg_match('/([0-9]+){0,2}-([0-9]+){0,2}-([0-9]+){0,4}\s([0-9]+){0,2}:([0-9]+){0,2}:([0-9]+){0,2}/', $date, $match)) {
        $cron_time = Date::format(mktime($match[4], $match[5], $match[6], $match[2], $match[1], $match[3]));
      } else {
        $cron_time = '-';
      }
    } else {
      $cron_time = '-';
    }
    $content[] = '<tr><th scope=row>{LNG_Cron last running at}</th><td class=right>'.$cron_time.'</td></tr>';
    $content[] = '</tbody>';
    $content[] = '<tfoot>';
    $content[] = '<tr><td colspan=2 class=right>{LNG_You are currently using GCMS version} <em>{VERSION}</em></td></tr>';
    $content[] = '</tfoot>';
    $content[] = '</table>';
    $content[] = '</section>';
    // news
    $content[] = '<section class=section>';
    $content[] = '<header><h1 class=icon-rss>{LNG_News}</h1></header>';
    $content[] = '<ol id=news_div></ol>';
    $content[] = '<div class="bottom right padding-top-right">';
    $content[] = '<a class=icon-next href="http://gcms.in.th/news.html" target=_blank>{LNG_all items}</a>';
    $content[] = '</div>';
    $content[] = '</section>';
    $content[] = '</div>';
    $content[] = '<div class="block8 float-right">';
    // page view
    $pageviews = $dashboard->pageviews();
    $y = (int)date('Y');
    $pages_view = 0;
    $pageview = array();
    $visited = array();
    $thead = array();
    $l = sizeof($pageviews);
    foreach ($pageviews AS $i => $item) {
      $c = $i > $l - 8 ? $i > $l - 4 ? '' : 'mobile' : 'tablet';
      $thead[] = '<td class="'.$c.'"><a href="'.WEB_URL.'admin/index.php?module=pagesview&amp;date='.$item['year'].'-'.$item['month'].'">'.Date::monthName($item['month']).'</a></td>';
      $pageview[] = '<td class="'.$c.'">'.number_format($item['pages_view']).'</td>';
      $visited[] = '<td class="'.$c.'">'.number_format($item['visited']).'</td>';
    }
    $content[] = '<section class=section>';
    $content[] = '<header><h1 class=icon-stats>{LNG_People visit the site}</h1></header>';
    $content[] = '<div id=pageview_graph class=ggraphs>';
    $content[] = '<canvas></canvas>';
    $content[] = '<table class="data fullwidth border">';
    $content[] = '<thead><tr><th>{LNG_monthly}</th>'.implode('', $thead).'</tr></thead>';
    $content[] = '<tbody>';
    $content[] = '<tr><th scope=row>{LNG_Visitors total}</th>'.implode('', $visited).'</tr>';
    $content[] = '<tr class=bg2><th scope=row>{LNG_Pages view}</th>'.implode('', $pageview).'</tr>';
    $content[] = '</tbody>';
    $content[] = '</table>';
    $content[] = '</div>';
    $content[] = '</section>';
    // popular page
    $thead = array();
    $visited = array();
    foreach ($dashboard->popularpage() AS $item) {
      $thead[] = '<td>'.$item['topic'].'</td>';
      $visited[] = '<td>'.$item['visited_today'].'</td>';
    }
    $content[] = '<section class=section>';
    $content[] = '<header><h1 class=icon-pie>{LNG_Popular daily} ({LNG_Module} Document)</h1></header>';
    $content[] = '<div id=visited_graph class=ggraphs>';
    $content[] = '<canvas></canvas>';
    $content[] = '<table class=hidden>';
    $content[] = '<thead><tr><th>&nbsp;</th>'.implode('', $thead).'</tr></thead>';
    $content[] = '<tbody>';
    $content[] = '<tr><th>{LNG_Visited}</th>'.implode('', $visited).'</tr>';
    $content[] = '</tbody>';
    $content[] = '</table>';
    $content[] = '</div>';
    $content[] = '</section>';
    $content[] = '</div>';
    $content[] = '</section>';
    $content[] = '</div>';
    $content[] = '</div>';
    $content[] = '<script>';
    $content[] = '$G(window).Ready(function(){';
    // สี สำหรับส่งให้ graphs
    $color = "['".implode("', '", $colors)."']";
    $content[] = 'new GGraphs("pageview_graph", {type:"line",colors:'.$color.'});';
    $content[] = 'new GGraphs("visited_graph", {type:"pie",colors:'.$color.',centerX:30+Math.round($G("visited_graph").getHeight()/2),labelOffset:35,pieMargin:30,strokeColor:null});';
    $content[] = "getNews('news_div');";
    $content[] = "getUpdate('".self::$cfg->version."');";
    $content[] = '});';
    $content[] = '</script>';
    return implode('', $content);
  }
}