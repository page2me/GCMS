<?php
/*
 * @filesource video/models/admin/write.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Video\Admin\Write;

use Kotchasan\Language;
use Gcms\Gcms;
use Kotchasan\Login;
use \Kotchasan\ArrayTool;
use \Kotchasan\File;

/**
 * อ่านข้อมูลโมดูล.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

  /**
   * อ่านข้อมูลรายการที่เลือก
   *
   * @param int $module_id ของโมดูล
   * @param int $id ID
   * @return object|null คืนค่าข้อมูล object ไม่พบคืนค่า null
   */
  public static function get($module_id, $id)
  {
    // model
    $model = new static();
    $query = $model->db()->createQuery();
    if (empty($id)) {
      // ใหม่ ตรวจสอบโมดูล
      $query->select('0 id', 'M.id module_id', 'M.owner', 'M.module', 'M.config')
        ->from('modules M')
        ->where(array(
          array('M.id', $module_id),
          array('M.owner', 'video'),
      ));
    } else {
      // แก้ไข ตรวจสอบรายการที่เลือก
      $query->select('A.*', 'M.owner', 'M.module', 'M.config')
        ->from('video A')
        ->join('modules M', 'INNER', array(array('M.id', 'A.module_id'), array('M.owner', 'video')))
        ->where(array('A.id', $id));
    }
    $result = $query->limit(1)->toArray()->execute();
    if (sizeof($result) == 1) {
      $result = ArrayTool::unserialize($result[0]['config'], $result[0], empty($id));
      unset($result['config']);
      return (object)$result;
    }
    return null;
  }

  /**
   * บันทึก
   */
  public function save()
  {
    $ret = array();
    // referer, session, member
    if (self::$request->initSession() && self::$request->isReferer() && $login = Login::isMember()) {
      if ($login['email'] == 'demo') {
        $ret['alert'] = Language::get('Unable to complete the transaction');
      } else {
        // ค่าที่ส่งมา
        $save = array(
          'youtube' => self::$request->post('write_youtube')->topic(),
          'topic' => self::$request->post('write_topic')->topic(),
          'description' => self::$request->post('write_description')->description()
        );
        $id = self::$request->post('write_id')->toInt();
        // ตรวจสอบรายการที่เลือก
        $index = self::get(self::$request->post('module_id')->toInt(), $id);
        if ($index && Gcms::canConfig($login, $index, 'can_write')) {
          if ($save['youtube'] == '') {
            $ret['ret_write_youtube'] = 'this';
          } else {
            // ตรวจสอบรายการซ้ำ
            $search = $this->db()->first($this->getTableName('video'), array(
              array('youtube', $save['youtube']),
              array('module_id', (int)$index->module_id)
            ));
            if ($search && ($id == 0 || $id != $search->id)) {
              $ret['ret_write_youtube'] = Language::replace('This :name already exist', array(':name' => Language::get('Video')));
            } else {
              $ret['ret_write_youtube'] = '';
              // get youtube data
              $url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics&id='.$save['youtube'].(empty($index->google_api_key) ? '' : '&key='.$index->google_api_key);
              if (function_exists('curl_init') && $ch = @curl_init()) {
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $feed = curl_exec($ch);
                curl_close($ch);
              } else {
                $feed = file_get_contents($url);
              }
              $datas = json_decode($feed);
              if (isset($datas->{'error'})) {
                $ret['alert'] = $datas->{'error'}->{'message'};
              } else {
                $items = $datas->{'items'};
                if (sizeof($items) == 0) {
                  $ret['ret_write_youtube'] = Language::get('Video not found');
                } else {
                  $error = false;
                  $item = $items[0]->{'snippet'};
                  $save['topic'] = trim($item->{'title'});
                  $save['description'] = trim($item->{'description'});
                  $save['views'] = (int)$items[0]->{'statistics'}->{'viewCount'};
                  // video thumbnail
                  if (isset($item->{'thumbnails'}->{'standard'})) {
                    $url = $item->{'thumbnails'}->{'standard'}->{'url'};
                  } else {
                    $url = $item->{'thumbnails'}->{'high'}->{'url'};
                  }
                  if (function_exists('curl_init') && $ch = @curl_init()) {
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $thumbnail = curl_exec($ch);
                    curl_close($ch);
                  } else {
                    $thumbnail = file_get_contents($url);
                  }
                  if (!File::makeDirectory(ROOT_PATH.DATA_FOLDER.'video/')) {
                    // ไดเรคทอรี่ไม่สามารถสร้างได้
                    $ret['alert'] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'video/');
                    $error = true;
                  } else {
                    $f = @fopen(ROOT_PATH.DATA_FOLDER.'video/'.$save['youtube'].'.jpg', 'w');
                    if (!$f) {
                      // ไดเร็คทอรี่ไม่สามารถเขียนได้
                      $ret['alert'] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'video/'.$save['youtube'].'.jpg');
                      $error = true;
                    } else {
                      fwrite($f, $thumbnail);
                      fclose($f);
                      $ret['imgIcon'] = WEB_URL.DATA_FOLDER.'video/'.$save['youtube'].'.jpg?'.time();
                    }
                  }
                  if (!$error) {
                    // save
                    $save['last_update'] = time();
                    if ($id == 0) {
                      // ใหม่
                      $save['module_id'] = $index->module_id;
                      $save['views'] = 0;
                      $id = $this->db()->insert($this->getTableName('video'), $save);
                    } else {
                      // แก้ไข
                      $this->db()->update($this->getTableName('video'), $id, $save);
                    }
                    // คืนค่า
                    $ret['alert'] = Language::get('Saved successfully');
                    $ret['location'] = self::$request->getUri()->postBack('index.php', array('module' => 'video-setup', 'mid' => $index->module_id));
                  }
                }
              }
            }
          }
        } else {
          $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
        }
      }
    } else {
      $ret['alert'] = Language::get('Can not be performed this request. Because they do not find the information you need or you are not allowed');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}