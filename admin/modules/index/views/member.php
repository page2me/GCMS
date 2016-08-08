<?php
/*
 * @filesource index/views/member.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Member;

use \Kotchasan\DataTable;
use \Kotchasan\Language;
use \Kotchasan\Date;

/**
 * module=member
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{
  private $sexes;

  /**
   * ตารางรายชื่อสมาชิก
   *
   * @return string
   */
  public function render()
  {
    $this->sexes = Language::get('SEXES');
    // สถานะสมาชิก
    $change_member_status = array();
    $member_status = array(-1 => '{LNG_all items}');
    foreach (self::$cfg->member_status as $key => $value) {
      $member_status[$key] = $value;
      $change_member_status[$key] = '{LNG_Change member status to} '.$value;
    }
    // ตารางสมาชิก
    $table = new DataTable(array(
      'model' => 'Index\Member\Model',
      'perPage' => self::$request->cookie('member_perPage', 30)->toInt(),
      'sort' => self::$request->cookie('member_sort', 'id desc')->toString(),
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('visited', 'status', 'admin_access', 'activatecode', 'website', 'fb'),
      /* คอลัมน์ที่สามารถค้นหาได้ */
      'searchColumns' => array('fname', 'lname', 'displayname', 'email'),
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/index/model/member/action',
      'actions' => array(
        array(
          'id' => 'action',
          'class' => 'ok',
          'text' => '{LNG_With selected}',
          'options' => array(
            'accept' => '{LNG_Accept membership}',
            'activate' => '{LNG_Send confirmation email}',
            'sendpassword' => '{LNG_Get new password}',
            'ban' => '{LNG_Suspended}',
            'unban' => '{LNG_Cancel suspension}',
            'delete' => '{LNG_Delete}'
          )
        ),
        array(
          'id' => 'status',
          'class' => 'ok',
          'text' => '{LNG_With selected}',
          'options' => $change_member_status
        )
      ),
      /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
      'filters' => array(
        'status' => array(
          'name' => 'status',
          'default' => -1,
          'text' => '{LNG_Member status}',
          'options' => $member_status,
          'value' => self::$request->get('status', -1)->toInt()
        )
      ),
      /* รายชื่อฟิลด์ที่ query (ถ้าแตกต่างจาก Model) */
      'fields' => array(
        'id',
        'ban',
        'email',
        'displayname',
        'CONCAT_WS(" ", `pname`,`fname`,`lname`) name',
        'phone1',
        'sex',
        'website',
        'create_date',
        'lastvisited',
        'visited',
        'status',
        'admin_access',
        'activatecode',
        'fb'
      ),
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'id' => array(
          'text' => '{LNG_ID}',
          'sort' => 'id',
        ),
        'ban' => array(
          'text' => ''
        ),
        'email' => array(
          'text' => '{LNG_Email}',
          'sort' => 'email'
        ),
        'displayname' => array(
          'text' => '{LNG_Displayname}',
          'sort' => 'displayname'
        ),
        'name' => array(
          'text' => '{LNG_Name} {LNG_Surname}',
          'sort' => 'name'
        ),
        'phone1' => array(
          'text' => '{LNG_Phone}'
        ),
        'sex' => array(
          'text' => '{LNG_Sex}',
          'class' => 'center'
        ),
        'website' => array(
          'text' => '{LNG_Website}'
        ),
        'create_date' => array(
          'text' => '{LNG_Created}',
          'class' => 'center'
        ),
        'lastvisited' => array(
          'text' => '{LNG_Last login} ({LNG_times})',
          'class' => 'center'
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'sex' => array(
          'class' => 'center'
        ),
        'ban' => array(
          'class' => 'center'
        ),
        'create_date' => array(
          'class' => 'center'
        ),
        'lastvisited' => array(
          'class' => 'center'
        )
      ),
      /* ปุ่มแสดงในแต่ละแถว */
      'buttons' => array(
        array(
          'class' => 'icon-edit button green',
          'href' => self::$request->getUri()->createBackUri(array('module' => 'editprofile', 'id' => ':id')),
          'text' => '{LNG_Edit}'
        )
      )
    ));
    // save cookie
    setcookie('member_perPage', $table->perPage, time() + 3600 * 24 * 365, '/');
    setcookie('member_sort', $table->sort, time() + 3600 * 24 * 365, '/');
    return $table->render();
  }

  /**
   * จัดรูปแบบการแสดงผลในแต่ละแถว
   *
   * @param array $item
   * @return array
   */
  public function onRow($item)
  {
    $item['email'] = '<a href="index.php?module=sendmail&to='.$item['email'].'" class="status'.$item['status'].'">'.$item['email'].'</a>';
    $item['create_date'] = Date::format($item['create_date'], 'd M Y');
    $item['lastvisited'] = Date::format($item['lastvisited'], 'd M Y H:i').' ('.number_format($item['visited']).')';
    $item['sex'] = '<span class=icon-sex'.(isset($this->sexes[$item['sex']]) ? '-'.$item['sex'] : '').'></span>';
    $item['ban'] = $item['ban'] == 1 ? '<span class="icon-ban ban" title="{LNG_Members were suspended}"></span>' : '<span class="icon-ban"></span>';
    $item['phone1'] = empty($item['phone1']) ? '' : '<a href="tel:'.$item['phone1'].'">'.$item['phone1'].'</a>';
    $class = $item['fb'] == 1 ? ' class=facebook' : '';
    $item['displayname'] = empty($item['website']) ? '<span'.$class.'>'.$item['displayname'].'</span>' : '<a href="http://'.$item['website'].'" target="_blank"'.$class.'>'.$item['displayname'].'</a>';
    return $item;
  }
}