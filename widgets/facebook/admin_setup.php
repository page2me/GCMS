<?php
// widgets/facebook/admin_setup.php
if (MAIN_INIT == 'admin' && $isAdmin) {
	// ตรวจสอบค่า default
	$config['facebook_width'] = gcms::getVars($config, 'facebook_width', 500);
	$config['facebook_height'] = gcms::getVars($config, 'facebook_height', 0);
	$config['facebook_user'] = gcms::getVars($config, 'facebook_user', 'gcmscms');
	$config['facebook_show_facepile'] = gcms::getVars($config, 'facebook_show_facepile', 1);
	$config['facebook_show_posts'] = gcms::getVars($config, 'facebook_show_posts', 0);
	$config['facebook_hide_cover'] = gcms::getVars($config, 'facebook_hide_cover', 0);
	// title
	$title = $lng['LNG_FACEBOOK_SETTINGS'];
	$a = array();
	$a[] = '<span class=icon-widgets>{LNG_WIDGETS}</span>';
	$a[] = '{LNG_FACEBOOK_LIKE_BOX}';
	// แสดงผล
	$content[] = '<div class=breadcrumbs><ul><li>'.implode('</li><li>', $a).'</li></ul></div>';
	$content[] = '<section>';
	$content[] = '<header><h1 class=icon-facebook>'.$title.'</h1></header>';
	$content[] = '<div class=setup_frm>';
	$content[] = '<form id=setup_frm class=paper method=post action=index.php>';
	$content[] = '<fieldset>';
	$content[] = '<legend><span>{LNG_FACEBOOK_LIKE_BOX}</span></legend>';
	// width, height
	$content[] = '<div class=item>';
	$content[] = '<div class=input-groups>';
	$content[] = '<div class=width50>';
	$content[] = '<label for=facebook_width>{LNG_WIDTH}</label>';
	$content[] = '<span class="g-input icon-width"><input type=number name=facebook_width id=facebook_width value="'.$config['facebook_width'].'" title="{LNG_FACEBOOK_SIZE_COMMENT}"></span>';
	$content[] = '</div>';
	$content[] = '<div class=width50>';
	$content[] = '<label for=facebook_height>{LNG_HEIGHT}</label>';
	$content[] = '<span class="g-input icon-height"><input type=number name=facebook_height id=facebook_height value="'.$config['facebook_height'].'" title="{LNG_FACEBOOK_SIZE_COMMENT}"></span>';
	$content[] = '</div>';
	$content[] = '</div>';
	$content[] = '<div class=comment>{LNG_FACEBOOK_SIZE_COMMENT}</div>';
	$content[] = '</div>';
	// facebook_user
	$content[] = '<div class=item>';
	$content[] = '<label for=facebook_user>{LNG_USERNAME}</label>';
	$content[] = '<div class="table collapse">';
	$content[] = '<span class="td mobile">http://www.facebook.com/&nbsp;</span>';
	$content[] = '<div class=td><span class="g-input icon-facebook"><input type=text id=facebook_user name=facebook_user value="'.$config['facebook_user'].'" title="{LNG_WIDGETS_FACEBOOK_USER_COMMENT}"></span></div>';
	$content[] = '</div>';
	$content[] = '<div class=comment id=result_facebook_user>{LNG_FACEBOOK_USER_COMMENT}</div>';
	$content[] = '</div>';
	// facebook_show_facepile
	$content[] = '<div class=item>';
	$content[] = '<label for=facebook_show_facepile>{LNG_FACEBOOK_SHOW_FACES}</label>';
	$content[] = '<span class="g-input icon-config"><select id=facebook_show_facepile name=facebook_show_facepile title="{LNG_PLEASE_SELECT}">';
	foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
		$sel = $i == $config['facebook_show_facepile'] ? ' selected' : '';
		$content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
	}
	$content[] = '</select></span>';
	$content[] = '</div>';
	// facebook_show_posts
	$content[] = '<div class=item>';
	$content[] = '<label for=facebook_show_posts>{LNG_FACEBOOK_SHOW_STREAM}</label>';
	$content[] = '<span class="g-input icon-config"><select id=facebook_show_posts name=facebook_show_posts title="{LNG_PLEASE_SELECT}">';
	foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
		$sel = $i == $config['facebook_show_posts'] ? ' selected' : '';
		$content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
	}
	$content[] = '</select></span>';
	$content[] = '</div>';
	// facebook_hide_cover
	$content[] = '<div class=item>';
	$content[] = '<label for=facebook_hide_cover>{LNG_FACEBOOK_SHOW_HEADER}</label>';
	$content[] = '<span class="g-input icon-config"><select id=facebook_hide_cover name=facebook_hide_cover title="{LNG_PLEASE_SELECT}">';
	foreach ($lng['OPEN_CLOSE'] AS $i => $value) {
		$sel = $i == $config['facebook_hide_cover'] ? ' selected' : '';
		$content[] = '<option value='.$i.$sel.'>'.$value.'</option>';
	}
	$content[] = '</select></span>';
	$content[] = '</div>';
	$content[] = '</fieldset>';
	// submit
	$content[] = '<fieldset class=submit>';
	$content[] = '<input type=submit class="button large save" value="{LNG_SAVE}">';
	$content[] = '</fieldset>';
	$content[] = '</form>';
	$content[] = '<div class=center><iframe style="height:'.(max(340, $config['facebook_height']) + 20).'px;width:100%" src="'.WEB_URL.'/widgets/facebook/facebook.php"></iframe></div>';
	$content[] = '</div>';
	$content[] = '</section>';
	$content[] = '<script>';
	$content[] = '$G(window).Ready(function(){';
	$content[] = 'new GForm("setup_frm","'.WEB_URL.'/widgets/facebook/admin_setup_save.php").onsubmit(doFormSubmit);';
	$content[] = '});';
	$content[] = '</script>';
	// หน้านี้
	$url_query['module'] = 'facebook-setup';
} else {
	$title = $lng['LNG_DATA_NOT_FOUND'];
	$content[] = '<aside class=error>'.$title.'</aside>';
}
