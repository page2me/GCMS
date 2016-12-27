<?php
	// modules/doc/admin_write.php
	if (MAIN_INIT == 'admin' && $isMember) {
		// id ที่เลือก
		$id = gcms::getVars($_GET, 'qid', 0);
		unset($_GET['qid']);
		// id ของโมดูลที่เลือก (module_id)
		$module_id = gcms::getVars($_GET, 'id', 0);
		unset($_GET['id']);
		// หมวดที่เลือก
		$cat = gcms::getVars($_GET, 'cat', 0);
		// tab ที่เลือก
		$tab = gcms::getVars($_GET, 'tab', '');
		$tab = $tab == '' ? 'detail_'.$config['languages'][0] : $tab;
		if ($id > 0) {
			// แก้ไข ตรวจสอบรายการที่เลือก
			$sql = "SELECT D.*,M.`owner`,M.`module`,M.`config`";
			$sql .= " FROM `".DB_INDEX."` AS D";
			$sql .= " INNER JOIN `".DB_MODULES."` AS M ON M.`id`=$module_id";
			$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`module_id`=$module_id AND I.`index`='1' AND I.`language` IN ('".LANGUAGE."','')";
			$sql .= " WHERE D.`id`=$id AND D.`module_id`=$module_id AND D.`index`='0'";
			$sql .= " LIMIT 1";
		} else {
			// ใหม่ ตรวจสอบโมดูล
			$sql = "SELECT M.`id` AS `module_id`,M.`module`,M.`owner`,M.`config`";
			$sql .= " FROM `".DB_MODULES."` AS M";
			$sql .= " INNER JOIN `".DB_INDEX."` AS I ON I.`module_id`=$module_id AND I.`index`='1' AND I.`language` IN ('".LANGUAGE."','')";
			$sql .= " WHERE M.`id`=$module_id AND M.`owner`='doc'";
			$sql .= " LIMIT 1";
		}
		$index = $db->customQuery($sql);
		$index = sizeof($index) == 1 ? $index[0] : false;
		if ($index) {
			// config
			gcms::r2config($index['config'], $index, $id == 0);
		}
		if (!$index) {
			// ไมพบบทความหรือโมดูล
			$title = $lng['PAGE_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		} elseif (!gcms::canConfig($index, 'can_write')) {
			// ไม่สามารถเขียนหรือแก้ไขได้
			$title = $lng['LNG_DATA_NOT_FOUND'];
			$content[] = '<aside class=error>'.$title.'</aside>';
		} else {
			// title
			$m = ucwords($index['module']);
			$a = array();
			$a[] = '<span class=icon-help>'.ucwords($index['owner']).'</span>';
			$a[] = '<a href="{URLQUERY?module=doc-config&id='.$index['module_id'].'}">'.$m.'</a>';
			$a[] = '<a href="{URLQUERY?module=doc-setup&id='.$index['module_id'].'}" title="{LNG_CONTENTS}">{LNG_CONTENTS}</a>';
			if ($id > 0) {
				// โหลดข้อมูลอื่นๆที่แก้ไข
				$sql = "SELECT `language`,`topic`,`keywords`,`relate`,`description`,`detail` FROM `".DB_INDEX_DETAIL."`";
				$sql .= " WHERE `id`='$index[id]' AND `module_id`='$index[module_id]'";
				foreach ($db->customQuery($sql) AS $i => $item) {
					$item['language'] = ($i == 0 && $item['language'] == '') ? $config['languages'][0] : $item['language'];
					$datas[$item['language']] = $item;
				}
				$a[] = '{LNG_EDIT}';
				$title = "$lng[LNG_EDIT] $lng[LNG_CONTENTS] $m";
			} else {
				$a[] = '{LNG_ADD}';
				$title = "$lng[LNG_ADD] $lng[LNG_CONTENTS] $m";
				$index['id'] = 0;
				$index['alias'] = '';
				$index['published'] = 1;
				$index['category_id'] = $cat;
			}
			// แสดงผล
			$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
			$content[] = '<section>';
			$content[] = '<header><h1 class=icon-write>'.$title.'</h1>';
			$content[] = '<div class=inline><div class=writetab>';
			// menu
			$content[] = '<ul id=accordient_menu>';
			foreach ($config['languages'] AS $item) {
				$content[] = '<li><a id=tab_detail_'.$item.' href="{URLQUERY?module=doc-write&qid='.$index['id'].'&tab=detail_'.$item.'}">{LNG_DETAIL}&nbsp;<img src='.DATA_URL.'language/'.$item.'.gif alt='.$item.'></a></li>';
			}
			$content[] = '<li><a id=tab_options href="{URLQUERY?module=doc-write&qid='.$index['id'].'&tab=options}">{LNG_OTHER_DETAILS}</a></li>';
			$content[] = '</ul>';
			$content[] = '</div></div>';
			$content[] = '</header>';
			// ฟอร์มเขียน-แก้ไข
			$content[] = '<form id=setup_frm class="setup_frm accordion" method=post action=index.php>';
			// menu
			foreach ($config['languages'] AS $language) {
				$item = isset($datas[$language]) ? $datas[$language] : array('topic' => '', 'keywords' => '', 'description' => '', 'detail' => '', 'relate' => '');
				$content[] = '<fieldset id=detail_'.$language.'>';
				$content[] = '<legend><span>{LNG_DETAIL_IN}&nbsp;&nbsp;<img src="'.DATA_URL.'language/'.$language.'.gif" alt='.$language.'></span></legend>';
				// topic
				$content[] = '<div class=item>';
				$content[] = '<label for=write_topic_'.$language.'>{LNG_TOPIC}</label>';
				$content[] = '<span class="g-input icon-edit"><input type=text id=write_topic_'.$language.' name=write_topic_'.$language.' value="'.$item['topic'].'" maxlength=109 title="{LNG_TOPIC_COMMENT}"></span>';
				$content[] = '<div class=comment id=result_write_topic_'.$language.'>{LNG_TOPIC_COMMENT}</div>';
				$content[] = '</div>';
				// sdetail
				$content[] = '<div class=item>';
				$content[] = '<label for=write_description_'.$language.'>{LNG_DESCRIPTION}</label>';
				$content[] = '<span class="g-input icon-file"><textarea id=write_description_'.$language.' name=write_description_'.$language.' rows=3 maxlength=149 title="{LNG_DESCRIPTION_COMMENT}">'.gcms::detail2TXT($item, 'description').'</textarea></span>';
				$content[] = '<div class=comment id=result_write_description_'.$language.'>{LNG_DESCRIPTION_COMMENT}</div>';
				$content[] = '</div>';
				// detail
				$content[] = '<div class=item>';
				$content[] = '<label for=write_detail_'.$language.'>{LNG_DETAIL}</label>';
				$content[] = '<div><textarea name=write_detail_'.$language.' id=write_detail_'.$language.'>'.gcms::detail2TXT($item, 'detail').'</textarea></div>';
				$content[] = '</div>';
				$content[] = '</fieldset>';
			}
			$content[] = '<fieldset id=options>';
			$content[] = '<legend><span>{LNG_DETAIL_OPTIONS}</span></legend>';
			// alias
			$content[] = '<div class=item>';
			$content[] = '<label for=write_alias>{LNG_ALIAS}</label>';
			$content[] = '<span class="g-input icon-world"><input type=text id=write_alias name=write_alias value="'.$index['alias'].'" maxlength=64 title="{LNG_ALIAS_COMMENT}"></span>';
			$content[] = '<div class=comment id=result_write_alias>{LNG_ALIAS_COMMENT}</div>';
			$content[] = '</div>';
			// category
			$content[] = '<div class=item>';
			$content[] = '<label for=write_category>{LNG_CATEGORY}</label>';
			$content[] = '<span class="g-input icon-category"><select id=write_category name=write_category title="{LNG_CATEGORY_SELECT}">';
			$content[] = '<option value=0>{LNG_PLEASE_SELECT}</option>';
			$sql = "SELECT `category_id`,`topic` FROM `".DB_CATEGORY."` WHERE `module_id`='$index[module_id]' ORDER BY `category_id`";
			foreach ($db->customQuery($sql) AS $item) {
				$sel = $index['category_id'] == $item['category_id'] ? ' selected' : '';
				$content[] = '<option value='.$item['category_id'].$sel.'>'.gcms::ser2Str($item, 'topic').'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment id=result_write_category>{LNG_CATEGORY_SELECT}</div>';
			$content[] = '</div>';
			// published
			$content[] = '<div class=item>';
			$content[] = '<label for=write_published>{LNG_PUBLISHED}</label>';
			$content[] = '<span class="g-input icon-published1"><select id=write_published name=write_published title="{LNG_PUBLISHED_SETTING}">';
			foreach ($lng['LNG_PUBLISHEDS'] AS $i => $item) {
				$sel = $index['published'] == $i ? ' selected' : '';
				$content[] = '<option value='.$i.$sel.'>'.$item.'</option>';
			}
			$content[] = '</select></span>';
			$content[] = '<div class=comment>{LNG_PUBLISHED_SETTING}</div>';
			$content[] = '</div>';
			$content[] = '</fieldset>';
			// submit
			$content[] = '<fieldset class=submit>';
			$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
			$content[] = '<input type=button id=write_open class="button large preview" value="{LNG_PREVIEW}">';
			$content[] = gcms::get2Input($_GET);
			$content[] = '<input type=hidden id=write_id name=write_id value='.(int)$index['id'].'>';
			$content[] = '<input type=hidden name=module_id value='.(int)$index['module_id'].'>';
			$content[] = '<input type=hidden id=write_tab name=write_tab>';
			$content[] = '</fieldset>';
			$lastupdate = empty($index['last_update']) ? '-' : gcms::mktime2date($index['last_update']);
			$content[] = '<div class=lastupdate><span class=comment>{LNG_WRITE_COMMENT}</span>{LNG_LAST_UPDATE}<span id=lastupdate>'.$lastupdate.'</span></div>';
			$content[] = '</form>';
			$content[] = '</section>';
			$content[] = '<script>';
			$_SESSION['CKEDITOR'] = $_SESSION['login']['id'];
			foreach ($config['languages'] AS $item) {
				$content[] = 'CKEDITOR.replace("write_detail_'.$item.'", {';
				$content[] = 'toolbar:"Document",';
				$content[] = 'language:"'.LANGUAGE.'",';
				$content[] = 'height:300,';
				if (is_dir(ROOT_PATH.'ckfinder')) {
					$content[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html",';
					$content[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html?Type=Images",';
					$content[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'/ckfinder/ckfinder.html?Type=Flash",';
					$content[] = 'filebrowserUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files",';
					$content[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images",';
					$content[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash"';
				} else {
					$connector = urlencode(WEB_URL.'/ckeditor/filemanager/connectors/php/connector.php');
					$content[] = 'filebrowserBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Connector='.$connector.'",';
					$content[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector='.$connector.'",';
					$content[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'/ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector='.$connector.'",';
					$content[] = 'filebrowserUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php",';
					$content[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.php?Type=Image",';
					$content[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'/ckeditor/filemanager/connectors/php/upload.phpType=Flash"';
				}
				$content[] = '});';
			}
			$content[] = '$G(window).Ready(function(){';
			$content[] = 'new GForm("setup_frm","'.WEB_URL.'/modules/doc/admin_write_save.php").onsubmit(doFormSubmit);';
			$content[] = 'checkSaved("write_open", "'.WEB_URL.'/index.php?module='.$index['module'].'", "write_id");';
			$content[] = 'new GValidator("write_alias", "keyup,change", checkAlias, "'.WEB_URL.'/modules/document/checkalias.php", null, "setup_frm");';
			$content[] = 'inintWriteTab("accordient_menu", "'.$tab.'");';
			$content[] = '});';
			$content[] = '</script>';
			// หน้านี้
			$url_query['module'] = 'doc-write';
		}
	} else {
		$title = $lng['LNG_DATA_NOT_FOUND'];
		$content[] = '<aside class=error>'.$title.'</aside>';
	}
