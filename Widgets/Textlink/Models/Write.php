<?php
/*
 * @filesource Widgets/Textlink/Models/Write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Textlink\Models;

use \Kotchasan\Http\Request;
use \Kotchasan\Date;
use \Kotchasan\Login;
use \Kotchasan\File;
use \Kotchasan\Language;
use \Kotchasan\Image;

/**
 * Controller สำหรับจัดการการตั้งค่าเริ่มต้น
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Write extends \Kotchasan\Model
{

  public function save(Request $request)
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isAdmin()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // ค่าที่ส่งมา
        $save = array(
          'name' => $request->post('name')->topic(),
          'description' => $request->post('description')->topic(),
          'type' => $request->post('type')->topic(),
          'text' => $request->post('text')->topic(),
          'url' => $request->post('url')->quote(),
          'target' => $request->post('target')->topic(),
          'publish_start' => Date::sqlDateTimeToMktime($request->post('publish_start')->date()),
          'publish_end' => Date::sqlDateTimeToMktime($request->post('publish_end')->date().' 23:59:59')
        );
        if ($request->post('dateless')->toBoolean()) {
          $save['publish_end'] = 0;
        }
        $template = $request->post('template')->toString();
        if ($template && $save['type'] == 'custom') {
          $save['template'] = preg_replace('/<\?(.*?)\?>/', '', trim($template));
        }
        $id = $request->post('id')->toInt();
        // ตรวจสอบรายการที่เลือก
        $query = $this->db()->createQuery()->from('textlink');
        if ($id > 0) {
          // แก้ไข
          $textlink = $query->where($id)->first('id', 'logo');
        } else {
          // ใหม่
          $textlink = $query->first(array(
            $this->buildNext('link_order', 'textlink', null, 'link_order'),
            $this->buildNext('id', 'textlink')
          ));
        }
        // ตรวจสอบค่าที่ส่งมา
        $ret = array();
        if (!$textlink) {
          $ret['alert'] = Language::get('Sorry, Item not found It&#39;s may be deleted');
        } elseif (!preg_match('/^[a-z0-9]{1,}$/u', $save['name'])) {
          // ภาษาอังกฤษและตัวเลข
          $ret['ret_name'] = 'this';
          $ret['input'] = 'name';
        } else {
          // อัปโหลดไฟล์
          foreach (self::$request->getUploadedFiles() as $item => $file) {
            /* @var $file UploadedFile */
            if ($file->hasUploadFile()) {
              if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'image/')) {
                // ไดเรคทอรี่ไม่สามารถสร้างได้
                $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'image/');
                $ret['input'] = $item;
              } elseif (!$file->validFileExt(array('jpg', 'gif', 'png'))) {
                $ret['ret_'.$item] = Language::get('The type of file is invalid');
                $ret['input'] = $item;
              } else {
                // อัปโหลด
                $save[$item] = 'textlink-'.$textlink->id.'.'.$file->getClientFileExt();
                try {
                  $file->moveTo(ROOT_PATH.DATA_FOLDER.'image/'.$save[$item]);
                  $info = Image::info(ROOT_PATH.DATA_FOLDER.'image/'.$save[$item]);
                  if ($info === false) {
                    @unlink(ROOT_PATH.DATA_FOLDER.'image/'.$save[$item]);
                    $ret['ret_'.$item] = Language::get('The type of file is invalid');
                    $ret['input'] = $item;
                  } else {
                    $save['width'] = $info['width'];
                    $save['height'] = $info['height'];
                    if ($id > 0 && $textlink->$item != $save[$item]) {
                      // ลบรูปภาพเก่า
                      @unlink(ROOT_PATH.DATA_FOLDER.'image/'.$textlink->$item);
                    }
                  }
                } catch (\Exception $exc) {
                  // ไม่สามารถอัปโหลดได้
                  $ret['ret_'.$item] = Language::get($exc->getMessage());
                  $ret['input'] = $item;
                }
              }
            }
          }
          if (empty($ret)) {
            // save
            $save['text'] = preg_replace('/(&lt;br[\s\/]+&gt;)/iu', '<br>', $save['text']);
            if ($id == 0) {
              // ใหม่
              $save['link_order'] = $textlink->link_order;
              $save['published'] = 1;
              $id = $this->db()->insert($this->getFullTableName('textlink'), $save);
            } else {
              // แก้ไข
              $this->db()->update($this->getFullTableName('textlink'), $textlink->id, $save);
            }
            // ส่งค่ากลับ
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = self::$request->getUri()->postBack('index.php', array('name' => $save['name'], 'module' => 'Textlink-settings'));
          }
        }
      }
    } else {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}