<?php
// widgets/textlink/admin_write.php
if (MAIN_INIT == 'admin' && $isAdmin && defined('DB_TEXTLINK')) {
	// รายการที่แก้ไข
	$id = gcms::getVars($_GET, 'id', 0);
	$name = $db->sql_trim_str($_GET, 'name');
	unset($_GET['id']);
	unset($_GET['name']);
	// รายการที่เลือก
	if ($id > 0) {
		$textlink = $db->getRec(DB_TEXTLINK, $id);
	} else {
		$textlink = array('id' => 0, 'name' => $name, 'type' => '', 'description' => '', 'text' => '', 'url' => '', 'target' => '', 'logo' => '', 'publish_start' => $mmktime, 'publish_end' => $mmktime);
	}
	// title
	$title = $lng['LNG_TEXTLINK_TITLE'];
	$a = array();
	$a[] = '<span class=icon-widgets>{LNG_WIDGETS}</span>';
	$a[] = '<a href="{URLQUERY?module=textlink-setup}">{LNG_TEXTLINK}</a>';
	$a[] = $id == 0 ? '{LNG_ADD}' : '{LNG_EDIT}';
	// แสดงผล
	$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
	$content[] = '<section>';
	$content[] = '<header><h1 class=icon-ads>'.$title.'</h1></header>';
	$content[] = '<form id=setup_frm class=setup_frm method=post action=index.php>';
	$content[] = '<fieldset>';
	$content[] = '<legend><span>{LNG_CONFIG}</span></legend>';
	// name
	$content[] = '<div class=item>';
	$content[] = '<label for=textlink_name>{LNG_NAME}</label>';
	$content[] = '<div class=input-groups-table>';
	$content[] = '<span class="width g-input icon-edit"><input type=text id=textlink_name name=textlink_name maxlength=11 value="'.$textlink['name'].'" title="{LNG_TEXTLINK_NAME_COMMENT}" autofocus></span>';
	$content[] = '<em class=width id=textlink_demo>{WIDGET_TEXTLINK}</em>';
	$content[] = '</div>';
	$content[] = '<div class=comment id=result_textlink_name>{LNG_TEXTLINK_NAME_COMMENT}</div>';
	$content[] = '</div>';
	// description
	$content[] = '<div class=item>';
	$content[] = '<label for=textlink_description>{LNG_DESCRIPTION}</label>';
	$content[] = '<span class="g-input icon-file"><input type=text id=textlink_description name=textlink_description maxlength=49 value="'.$textlink['description'].'" title="{LNG_TEXTLINK_DESCRIPTION_COMMENT}"></span>';
	$content[] = '<div class=comment id=result_textlink_description>{LNG_TEXTLINK_DESCRIPTION_COMMENT}</div>';
	$content[] = '</div>';
	// โหลด styles
	include (ROOT_PATH.'widgets/textlink/styles.php');
	// type
	$content[] = '<div class=item>';
	$content[] = '<label for=textlink_type>{LNG_TYPE}</label>';
	$content[] = '<span class="g-input icon-category"><select name=textlink_type id=textlink_type title="{LNG_TEXTLINK_TYPE_COMMENT}">';
	foreach ($textlink_typies AS $key => $values) {
		$sel = $textlink['type'] == $key ? ' selected' : '';
		$content[] = '<option value='.$key.$sel.'>'.$lng['TEXTLINK_TYPIES'][$key].'</option>';
	}
	$content[] = '</select></span>';
	$content[] = '<div class=comment id=result_textlink_description>{LNG_TEXTLINK_TYPE_COMMENT}</div>';
	$content[] = '</div>';
	// template
	$content[] = '<div class=item>';
	$content[] = '<label for=textlink_template>{LNG_TEMPLATE}</label>';
	$content[] = '<span class="g-input icon-file"><textarea name=textlink_template id=textlink_template rows=5 placeholder="&lt;HTML&gt;" title="{LNG_TEXTLINK_TEMPLATE_COMMENT}"></textarea></span>';
	$content[] = '<div class=comment id=result_textlink_template>{LNG_TEXTLINK_TEMPLATE_COMMENT}</div>';
	$content[] = '</div>';
	$content[] = '</fieldset>';
	$content[] = '<fieldset>';
	$content[] = '<legend><span>{LNG_DETAIL}</span></legend>';
	// text
	$content[] = '<div class=item>';
	$content[] = '<label for=textlink_text>{LNG_TEXT}</label>';
	$content[] = '<span class="g-input icon-edit"><input type=text id=textlink_text name=textlink_text value="'.$textlink['text'].'" title="{LNG_TEXTLINK_TEXT_COMMENT}"></span>';
	$content[] = '<div class=comment id=result_textlink_text>{LNG_TEXTLINK_TEXT_COMMENT}</div>';
	$content[] = '</div>';
	// url
	$content[] = '<div class=item>';
	$content[] = '<label for=textlink_url>{LNG_URL}</label>';
	$content[] = '<span class="g-input icon-world"><input type=text id=textlink_url name=textlink_url value="'.$textlink['url'].'" title="{LNG_WIDGET_URL_COMMENT}"></span>';
	$content[] = '<div class=comment id=result_textlink_url>{LNG_WIDGET_URL_COMMENT}</div>';
	$content[] = '</div>';
	// target
	$content[] = '<div class=item>';
	$content[] = '<label for=textlink_target>{LNG_MENU_TARGET}</label>';
	$content[] = '<span class="g-input icon-forward"><select name=textlink_target id=textlink_target title="{LNG_MENU_TARGET_COMMENT}">';
	foreach ($lng['MENU_TARGET'] AS $key => $value) {
		$sel = $key == $textlink['target'] ? ' selected' : '';
		$content[] = '<option value="'.$key.'"'.$sel.'>'.$value.'</option>';
	}
	$content[] = '</select></span>';
	$content[] = '<div class=comment>{LNG_MENU_TARGET_COMMENT}</div>';
	$content[] = '</div>';
	// logo
	$content[] = '<div class=item>';
	$logo = DATA_PATH.'image/'.$textlink['logo'];
	$logo = is_file($logo) ? DATA_URL.'image/'.$textlink['logo'] : '../skin/img/blank.gif';
	$content[] = '<div class=usericon><span><img id=textlink_logo src="'.$logo.'" alt=logo></span></div>';
	$content[] = '<label for=textlink_file>{LNG_IMAGE} {LNG_UPLOAD}</label>';
	$content[] = '<span class="g-input icon-upload"><input type=file class=g-file name=textlink_file id=textlink_file title="{LNG_TEXTLINK_LOGO_COMMENT}" accept="'.gcms::getEccept(array('jpg', 'png', 'gif')).'" data-preview=textlink_logo></span>';
	$content[] = '<div class=comment id=result_textlink_file>{LNG_TEXTLINK_LOGO_COMMENT}</div>';
	$content[] = '</div>';
	$content[] = '</fieldset>';
	$content[] = '<fieldset>';
	$content[] = '<legend><span>{LNG_TEXTLINK_PUBLISHED}</span></legend>';
	// start,end
	$content[] = '<div class=item>';
	$content[] = '<label for=textlink_publish_start>{LNG_PUBLISHED_START}</label>';
	$content[] = '<span class="table g-input icon-calendar"><input type=date id=textlink_publish_start name=textlink_publish_start value="'.date('Y-m-d', $textlink['publish_start']).'" title="{LNG_PUBLISHED_START}"></span>';
	$content[] = '</div>';
	$content[] = '<div class=item>';
	$content[] = '<label for=textlink_publish_end>{LNG_PUBLISHED_END}</label>';
	$content[] = '<div class="table collapse">';
	$content[] = '<div class=td><span class="g-input icon-calendar"><input type=date id=textlink_publish_end name=textlink_publish_end value="'.date('Y-m-d', $textlink['publish_end']).'" title="{LNG_PUBLISHED_END}"'.($textlink['publish_end'] == 0 ? ' disabled' : '').'></span></div>';
	$content[] = '<label class=td>&nbsp;{LNG_DATELESS}&nbsp;<input type=checkbox id=textlink_dateless name=textlink_dateless value=1'.($textlink['publish_end'] == 0 ? ' checked' : '').'></label>';
	$content[] = '</div>';
	$content[] = '<div class=comment id=result_textlink_publish_end>{LNG_PUBLISHED_START_END_COMMENT}</div>';
	$content[] = '</div>';
	$content[] = '</fieldset>';
	$content[] = '<fieldset class=submit>';
	$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
	$content[] = gcms::get2Input($_GET);
	$content[] = '<input type=hidden name=textlink_id id=textlink_id value='.(int)$textlink['id'].'>';
	$content[] = '</fieldset>';
	$content[] = '</form>';
	$content[] = '</section>';
	$content[] = '<script>';
	$content[] = 'inintTextlinkWrite();';
	$content[] = '</script>';
	// หน้านี้
	$url_query['module'] = 'textlink-write';
	$url_query['name'] = $name;
} else {
	$title = $lng['LNG_DATA_NOT_FOUND'];
	$content[] = '<aside class=error>'.$title.'</aside>';
}
