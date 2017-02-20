<?php
/*
 * @filesource index/views/member.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Member;

use \Kotchasan\Http\Request;
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
class View extends \Gcms\Adminview
{
  private $sexes;

  /**
   * ตารางรายชื่อสมาชิก
   *
   * @param Request $request
   * @return string
   */
  public function render(Request $request)
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
      /* Model */
      'model' => 'Index\Member\Model',
      /* แบ่งหน้า */
      'perPage' => $request->cookie('member_perPage', 30)->toInt(),
      /* เรียงลำดับ */
      'sort' => $request->cookie('member_sort', 'id desc')->toString(),
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('visited', 'status', 'activatecode', 'website'),
      /* คอลัมน์ที่สามารถค้นหาได้ */
      'searchColumns' => array('fname', 'lname', 'displayname', 'email', 'phone1'),
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
          'value' => $request->get('status', -1)->toInt()
        )
      ),
      /* รายชื่อฟิลด์ที่ query (ถ้าแตกต่างจาก Model) */
      'fields' => array(
        'id',
        'ban',
        'admin_access',
        'fb',
        'email',
        'CONCAT_WS(" ", `pname`,`fname`,`lname`) name',
        'displayname',
        'phone1',
        'sex',
        'status',
        'website',
        'create_date',
        'lastvisited',
        'visited',
        'activatecode',
      ),
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'id' => array(
          'text' => '{LNG_ID}',
          'sort' => 'id',
        ),
        'ban' => array(
          'text' => '',
          'colspan' => 3
        ),
        'email' => array(
          'text' => '{LNG_Email}',
          'sort' => 'email'
        ),
        'name' => array(
          'text' => '{LNG_Name} {LNG_Surname}',
          'sort' => 'name'
        ),
        'displayname' => array(
          'text' => '{LNG_Displayname}',
          'sort' => 'displayname'
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
          'class' => 'center',
          'sort' => 'lastvisited'
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'ban' => array(
          'class' => 'center'
        ),
        'fb' => array(
          'class' => 'center'
        ),
        'admin_access' => array(
          'class' => 'center'
        ),
        'sex' => array(
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
          'href' => $request->getUri()->createBackUri(array('module' => 'editprofile', 'id' => ':id')),
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
  public function onRow($item, $o, $prop)
  {
    $item['email'] = '<a href="index.php?module=sendmail&to='.$item['email'].'" class="status'.$item['status'].'">'.$item['email'].'</a>';
    $item['create_date'] = Date::format($item['create_date'], 'd M Y');
    $item['lastvisited'] = Date::format($item['lastvisited'], 'd M Y H:i').' ('.number_format($item['visited']).')';
    $item['sex'] = '<span class=icon-sex'.(isset($this->sexes[$item['sex']]) ? '-'.$item['sex'] : '').'></span>';
    $item['ban'] = $item['ban'] == 1 ? '<span class="icon-ban ban" title="{LNG_Members were suspended}"></span>' : '<span class="icon-ban"></span>';
    $item['fb'] = $item['fb'] == 1 ? '<span class="icon-facebook"></span>' : '';
    $item['admin_access'] = $item['admin_access'] == 1 ? '<span class="icon-valid access" title="{LNG_Access to the system administrator.}"></span>' : '<span class="icon-valid disabled"></span>';
    $item['phone1'] = empty($item['phone1']) ? '' : '<a href="tel:'.$item['phone1'].'">'.$item['phone1'].'</a>';
    $item['name'] = empty($item['website']) ? $item['name'] : '<a href="http://'.$item['website'].'" target="_blank">'.$item['name'].'</a>';
    return $item;
  }
}