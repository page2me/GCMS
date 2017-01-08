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
    if (Login::isMember()) {
      // ตรวจสอบโมดูลและอ่านข้อมูลโมดูล
      $index = \Edocument\Write\Model::get($request->request('id')->toInt(), $index);
      if ($index) {
        // กลุ่มผู้รับ
        $reciever = array();
        foreach (ArrayTool::merge(array(-1 => '{LNG_Guest}'), self::$cfg->member_status) as $key => $value) {
          $sel = in_array($key, $index->reciever) ? ' checked' : '';
          $sel .= $key == -1 ? ' id=reciever' : '';
          $reciever[] = '<label><input type=checkbox value='.$key.$sel.' name=reciever[]>&nbsp;'.$value.'</label>';
        }
        $modules = array();
        foreach ($index->modules as $module_id => $topic) {
          $sel = $module_id == $index->module_id ? ' selected' : '';
          $modules[] = '<option value='.$module_id.$sel.'>'.$topic.'</option>';
        }
        // title
        $title = $index->id == 0 ? '{LNG_Add New}' : '{LNG_Edit}';
        // /edocument/write.html
        $template = Template::create('edocument', $index->module->module, 'write');
        $template->add(array(
          '/{TITLE}/' => $title,
          '/{NO}/' => $index->document_no,
          '/{TOPIC}/' => isset($index->topic) ? $index->topic : '',
          '/{DETAIL}/' => isset($index->detail) ? $index->detail : '',
          '/{TOKEN}/' => $request->createToken(),
          '/{ACCEPT}/' => Mime::getEccept($index->module->file_typies),
          '/{GROUPS}/' => implode('', $reciever),
          '/{ID}/' => $index->id,
          '/{MODULES}/' => implode('', $modules),
          '/{SENDMAIL}/' => $index->id == 0 && $index->module->send_mail ? 'checked' : ''
        ));
        Gcms::$view->setContentsAfter(array(
          '/:type/' => implode(', ', $index->module->file_typies),
          '/:size/' => Text::formatFileSize($index->module->upload_size)
        ));
        // คืนค่า
        $index->topic = $index->module->topic.' - '.$title;
        $index->detail = $template->render();
        return $index;
      }
    }
    return null;
  }
}