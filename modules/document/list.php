<?php
// modules/document/list.php
if (defined('MAIN_INIT')) {
  // ค่าที่ส่งมา
  $cat = array();
  foreach (explode(',', gcms::getVars($_REQUEST, 'cat', '')) AS $c) {
    $c = (int)$c;
    if ($c > 0) {
      $cat[] = $c;
    }
  }
  $cat_count = sizeof($cat);
  $cat = implode(',', $cat);
  $page = gcms::getVars($_REQUEST, 'page', 0);
  $module_id = gcms::getVars($_REQUEST, 'mid', 0);
  $sqls = array();
  // breadcrumbs
  $breadcrumb = gcms::loadtemplate('document', '', 'breadcrumb');
  $breadcrumbs = array();
  // หน้าหลัก
  $breadcrumbs['HOME'] = gcms::breadcrumb('icon-home', $canonical, $install_modules[$module_list[0]]['menu_tooltip'], $install_modules[$module_list[0]]['menu_text'], $breadcrumb);
  if (isset($ds) || !empty($tag)) {
    // tag หรือ calendar
    if (!isset($default)) {
      include ROOT_PATH.'modules/document/default.config.php';
    }
    $index = $default['document'];
    $index['categories'] = 0;
    if (isset($ds)) {
      // เรียกจากวันที่
      $selday = mktime(0, 0, 0, $ds[2], $ds[1], (int)$ds[3] - $lng['YEAR_OFFSET']);
      $nextday = $selday + 86400;
      $sqls[] = "I.`create_date` >= $selday";
      $sqls[] = "I.`create_date` < $nextday";
      $index['topic'] = $lng['LNG_DOCUMENT_DATE'].' '.gcms::mktime2date($selday, 'd F Y');
      // breadcrumb ของวันที่ที่เรียก
      $breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL('calendar', "$ds[1]-$ds[2]-$ds[3]"), $index['topic'], $index['topic'], $breadcrumb);
    } elseif ($tag != '') {
      // เรียกตาม tags
      $index['topic'] = $lng['LNG_TAGS'].' '.$tag;
      // breadcrumb ของ tag ที่เรียก
      $breadcrumbs['MODULE'] = gcms::breadcrumb('', gcms::getURL('tag', $tag), $index['topic'], $index['topic'], $breadcrumb);
    }
    $index['keywords'] = "$index[topic],$keywords";
    $index['description'] = "$index[topic],$description";
    $index['menu_text'] = $index['topic'];
    $index['module'] = 'document';
    include (ROOT_PATH.'modules/document/default.config.php');
    $index = array_merge($default['document'], $index);
  } else {
    // ตรวจสอบโมดูลที่เลือก และ จำนวนหมวดในโมดูล
    $sql = "SELECT M.`id`,M.`module`,D.`topic` AS `title`,D.`detail`,D.`keywords`";
    $sql .= ",(SELECT COUNT(*) FROM `".DB_CATEGORY."` WHERE `module_id`=M.`id`) AS `categories`";
    if ($cat_count == 1) {
      // มีการเลือกหมวด เพียงหมวดเดียว
      $sql .= ",CASE WHEN ISNULL(C.`config`) THEN M.`config` ELSE CONCAT(M.`config` ,'\n' ,C.`config`) END AS `config`";
      $sql .= ",C.`category_id`,C.`topic`,C.`detail` AS `description`,C.`icon`";
      $sql .= " FROM `".DB_INDEX_DETAIL."` AS D";
      $sql .= " INNER JOIN `".DB_CATEGORY."` AS C ON C.`category_id` IN ($cat) AND C.`module_id`=D.`module_id`";
    } else {
      // ไม่ได้เลือกหมวดมา หรือมีการเลือกหลายหมวด
      $sql .= ",D.`topic`,D.`description`,M.`config`";
      $sql .= " FROM `".DB_INDEX_DETAIL."` AS D";
    }
    $sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`id`=D.`id` AND I.`index`='1' AND I.`module_id`=D.`module_id` AND I.`language`=D.`language`";
    if ($module_id > 0) {
      $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=$module_id AND M.`module`='$module' AND M.`owner`='document'";
    } else {
      $sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=D.`module_id` AND M.`module`='$module' AND M.`owner`='document'";
    }
    $sql .= " WHERE D.`language` IN ('".LANGUAGE."', '') LIMIT 1";
    $index = $cache->get($sql);
    if (!$index) {
      $index = $db->customQuery($sql);
      if (sizeof($index) == 1) {
        $index = $index[0];
        $cache->save($sql, $index);
      } else {
        $index = false;
      }
    }
    if ($index) {
      // โมดูล
      if ($index['module'] != $module_list[0]) {
        if (isset($install_modules[$index['module']]['menu_text'])) {
          $m = $install_modules[$index['module']]['menu_text'];
          $t = $install_modules[$index['module']]['menu_tooltip'];
        } else {
          $m = $index['title'];
          $t = $index['title'];
        }
        $canonical = gcms::getURL($index['module']);
        $breadcrumbs['MODULE'] = gcms::breadcrumb('', $canonical, $t, $m, $breadcrumb);
      }
      // อ่าน config
      gcms::r2config($index['config'], $index);
    }
  }
  if (!$index) {
    $title = $lng['LNG_DOCUMENT_NOT_FOUND'];
    $content = '<div class=error>'.$title.'</div>';
  } else {
    if ($cat_count == 1) {
      $index['topic'] = gcms::ser2Str($index, 'topic');
      $index['description'] = gcms::ser2Str($index, 'description');
      $index['icon'] = gcms::ser2Str($index, 'icon');
    }
    // category
    if ($cat_count == 1 && $index['topic'] != '') {
      $breadcrumbs['CATEGORY'] = gcms::breadcrumb('', gcms::getURL($index['module'], '', (int)$index['category_id']), $index['description'], $index['topic'], $breadcrumb);
    }
    $splitpage = '';
    $list = array();
    if ($cat_count > 0 || $index['categories'] == 0 || $index['category_display'] == 0) {
      // เลือกหมวดมา หรือไม่มีหมวด หรือปิดการแสดงผลหมวดหมู่ แสดงรายการเรื่อง
      include (ROOT_PATH.'modules/document/stories.php');
      $template = 'list';
    } else {
      // ลิสต์รายชื่อหมวด
      include (ROOT_PATH.'modules/document/categories.php');
      $template = 'category';
    }
    // แสดงผลหน้าเว็บ
    $patt = array('/{BREADCRUMS}/', '/{LIST}/', '/{NEWTOPIC}/', '/{CATEGORY}/', '/{TOPIC}/',
      '/{DETAIL}/', '/{SPLITPAGE}/', '/{LANGUAGE}/', '/{MODULE}/');
    $replace = array();
    $replace[] = implode("\n", $breadcrumbs);
    $replace[] = sizeof($list) > 0 ? '<div class="row iconview">'.implode("\n", $list).'</div>' : '';
    $replace[] = is_file(ROOT_PATH.'modules/document/write.php') && gcms::canConfig($index, 'can_write') ? '' : 'hidden';
    $replace[] = $cat;
    $replace[] = $index['topic'];
    $replace[] = gcms::getVars($index, 'detail', '');
    $replace[] = $splitpage;
    $replace[] = LANGUAGE;
    $replace[] = $index['module'];
    if (sizeof($list) > 0) {
      $content = preg_replace($patt, $replace, gcms::loadtemplate($index['module'], 'document', $template));
    } else {
      $content = preg_replace($patt, $replace, gcms::loadtemplate($index['module'], 'document', 'empty'));
    }
    // title,keywords,description
    $title = $index['topic'];
    $keywords = $index['keywords'];
    $description = $index['description'];
    // เลือกเมนู
    $menu = empty($install_modules[$index['module']]['alias']) ? $index['module'] : $install_modules[$index['module']]['alias'];
  }
}
