<?php
/*
 * @filesource edocument/models/admin/report.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Admin\Report;

/**
 * โมเดลสำหรับแสดงรายละเอียดการดาวน์โหลด (report.php)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Orm\Field
{
  /**
   * ชื่อตาราง
   *
   * @var string
   */
  protected $table = 'edocument_download D';

  public function getConfig()
  {
    return array(
      'select' => array('D.member_id id', 'U.fname', 'U.lname', 'U.email', 'U.status', 'D.last_update', 'D.downloads'),
      'join' => array(
        array(
          'LEFT',
          'Index\Member\Model',
          array(
            array('U.id', 'D.member_id')
          )
        )
      ),
      'order' => array(
        'D.last_update DESC'
      )
    );
  }
}