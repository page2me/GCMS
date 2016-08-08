<?php
/*
 * @filesource index/views/report.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Report;

use \Kotchasan\DataTable;

/**
 * ฟอร์ม forgot
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  public function render($date)
  {
    $table = new DataTable(array(
      'datas' => \Index\Report\Model::get($date),
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'time' => array(
          'text' => '{LNG_Time}',
          'sort' => 'time'
        ),
        'ip' => array(
          'text' => '{LNG_IP}',
          'sort' => 'ip'
        ),
        'count' => array(
          'text' => '{LNG_Count}',
          'class' => 'center',
          'sort' => 'count'
        ),
        'referer' => array(
          'text' => '{LNG_Referer}',
          'sort' => 'referer'
        ),
        'agent' => array(
          'text' => '{LNG_User Agent}',
          'class' => 'tablet',
          'sort' => 'agent'
        ),
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'count' => array(
          'class' => 'center'
        ),
        'agent' => array(
          'class' => 'tablet'
        ),
      )
    ));
    return $table->render();
  }
}