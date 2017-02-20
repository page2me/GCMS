<?php
/*
 * @filesource index/views/pagesview.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Pagesview;

/**
 * ฟอร์ม forgot
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\Adminview
{

  public function render($date)
  {
    $total = 0;
    $thead = array();
    $tbody = array();
    $list = \Index\Pagesview\Model::get($date);
    $l = sizeof($list);
    foreach ($list as $i => $item) {
      list($y, $m, $d) = explode('-', $item['date']);
      $d = (int)$d;
      if (is_file(ROOT_PATH.DATA_FOLDER.'counter/'.(int)$y.'/'.(int)$m.'/'.$d.'.dat')) {
        $d = '<a href="index.php?module=report&amp;date='.$item['date'].'">'.$d.'</a>';
      }
      $c = $i > $l - 13 ? $i > $l - 7 ? '' : 'mobile' : 'tablet';
      $thead[] = '<th class='.$c.'>'.$d.'</th>';
      $tbody[] = '<td class='.$c.'>'.number_format($item['pages_view']).'</td>';
      $total = $total + $item['pages_view'];
    }
    $content = '<section class="section margin-top">';
    $content .= '<div id=pageview_graph class=ggraphs>';
    $content .= '<canvas></canvas>';
    $content .= '<table class="data fullwidth">';
    $content .= '<thead><tr><th>{LNG_date}</th>'.implode('', $thead).'</tr></thead>';
    $content .= '<tbody><tr><th>{LNG_Pages View}</th>'.implode('', $tbody).'</tr></tbody>';
    $content .= '</table>';
    $content .= '</div>';
    $content .= '</section>';
    $content .= '<script>new GGraphs("pageview_graph", {type:"line"});</script>';
    return $content;
  }
}