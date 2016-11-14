<?php
/*
 * @filesource edocument/views/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Write;

use \Kotchasan\Http\Request;
use \Gcms\Gcms;
use \Kotchasan\Text;
use \Kotchasan\Antispam;
use \Kotchasan\Login;
use \Kotchasan\Mime;
use \Kotchasan\ArrayTool;
use \Kotchasan\Template;

/**
 * แสดงรายการบทความ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{

  /**
   * อัปโหลดเอกสาร
   *
   * @param Request $request
   * @param object $index
   * @return object
   */
  public function render(Request $request, $index)
  {
    // ตรวจสอบโมดูลและอ่านข้อมูลโมดูล
    $index = \Edocument\Write\Model::get($request->request('id')->toInt(), $index);
    // กลุ่มผู้รับ
    $reciever = array();
    foreach (ArrayTool::merge(array(-1 => '{LNG_Guest}'), self::$cfg->member_status) as $key => $value) {
      $sel = in_array($key, $index->reciever) ? ' checked' : '';
      $sel .= $key == -1 ? ' id=reciever' : '';
      $reciever[] = '<label><input type=checkbox value='.$key.$sel.' name=reciever[]>&nbsp;'.$value.'</label>';
    }
    $modules = array();
    foreach (\Edocument\Admin\Setup\Model::listModules('edocument', $index->modules) as $module_id => $module) {
      $sel = $module_id == $index->module_id ? ' selected' : '';
      $modules[] = '<option value='.$module_id.$sel.'>'.$module.'</option>';
    }
    // antispam
    $antispam = new Antispam();
    // template
    $template = Template::create($index->owner, $index->module, 'write');
    $template->add(array(
      '/{TITLE}/' => $index->id == 0 ? '{LNG_Add New}' : '{LNG_Edit}',
      '/{NO}/' => $index->document_no,
      '/{TOPIC}/' => isset($index->topic) ? $index->topic : '',
      '/{DETAIL}/' => isset($index->detail) ? $index->detail : '',
      '/{ANTISPAM}/' => $antispam->getId(),
      '/{ANTISPAMVAL}/' => Login::isAdmin() ? $antispam->getValue() : '',
      '/{ACCEPT}/' => Mime::getEccept($index->file_typies),
      '/{GROUPS}/' => implode('', $reciever),
      '/{ID}/' => $index->id,
      '/{MODULES}/' => implode('', $modules),
      '/{SENDMAIL}/' => $index->id == 0 && $index->send_mail ? 'checked' : ''
    ));
    Gcms::$view->setContents(array(
      '/:type/' => implode(', ', $index->file_typies),
      '/:size/' => Text::formatFileSize($index->upload_size)
      ), false);
    // คืนค่า
    $index->topic = $index->title;
    $index->detail = $template->render();
    return $index;
  }
}
