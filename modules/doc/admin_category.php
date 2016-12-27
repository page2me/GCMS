<?php
	// modules/doc/admin_category.php
	if (MAIN_INIT == 'admin' && $isMember) {
		// ตรวจสอบโมดูลที่เรียก
		$sql = "SELECT `id`,`module`,`config` FROM `".DB_MODULES."` WHERE `id`=".(int)$_GET['id']." AND `owner`='doc' LIMIT 1";
		$index = $db->customQuery($sql);
		if (sizeof($index) == 1) {
			$index = $index[0];
			// อ่าน config ของโมดูล
			gcms::r2config($index['config'], $index);
			// ตรวจสอบสถานะที่สามารถเข้าหน้านี้ได้
			if (!gcms::canConfig($index, 'can_write')) {
				$index = false;
			}
		} else {
			$index = false;
		}
		if ($index) {
			// title
			$m = ucwords($index['module']);
			$title = "$lng[LNG_CREATE]-$lng[LNG_EDIT] $lng[LNG_CATEGORY]";
			$a = array();
			$a[] = '<span class=icon-help>{LNG_MODULES}</span>';
			$a[] = '<a href="{URLQUERY?module=doc-config&id='.$index['id'].'}">'.$m.'</a>';
			$a[] = '{LNG_CATEGORY}';
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-category>'.$title.'</h1></header>';
			// form
			$content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
			$content[] = '<fieldset>';
			$content[] = '<legend><span>{LNG_DOC_CATEGORY}</span></legend>';
			$content[] = '<div class=item>';
			$content[] = '<table class="responsive-v border fullwidth" id=languageedit>';
			$content[] = '<thead><tr><th>{LNG_ID}</th><th colspan='.(sizeof($config['languages']) + 1).'>{LNG_CATEGORY}</th></tr></thead>';
			$content[] = '<tbody>';
			// อ่านข้อมูลจาก db
			$list = array();
			$sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`=$index[id] ORDER BY `category_id`";
			$list = $db->customQuery($sql);
			if (sizeof($list) == 0) {
				$list[] = array('category_id' => 1, 'topic' => '');
			}
			foreach ($list AS $i => $item) {
				$topic = gcms::ser2Array($item, 'topic');
				$row = '<tr id=M_'.$i.'>';
				$row .= '<td data-text="{LNG_ID}"><label class=g-input><input type=text class=number value="'.$item['category_id'].'" name=category_id[] size=5 title="{LNG_ID}"></label></td>';
				foreach ($config['languages'] AS $k) {
					$row .= '<td data-text='.$k.'><label class=g-input><input type=text name=topic_'.$k.'[] value="'.gcms::getVars($topic, $k, '').'" style="background-image:url(../datas/language/'.$k.'.gif)" title="{LNG_TOPIC} '.$k.'"></label></td>';
				}
				$row .= '<td class=icons><div><a class=icon-plus title="{LNG_ADD}"></a><a class=icon-minus title="{LNG_DELETE}"></a></div></td>';
				$row .= '</tr>';
				$content[] = $row;
			}
			$content[] = '</tbody>';
			$content[] = '</table>';
			$content[] = '<div class=comment>{LNG_SELECT_COMMENT}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// submit
			$content[] = '<fieldset class=submit>';
			$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
			$content[] = '<input type=hidden name=module_id value='.$index['id'].'>';
			$content[] = '</fieldset>';
			$content[] = '</form>';
			$content[] = '</section>';
			$content[] = '<script>';
			$content[] = '$G(window).Ready(function(){';
			$content[] = 'new GForm("setup_frm", "'.WEB_URL.'/modules/doc/admin_category_save.php").onsubmit(doFormSubmit);';
			$content[] = 'inintPMTable("setup_frm");';
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'doc-category';
		} else {
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
