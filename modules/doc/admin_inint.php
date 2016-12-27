<?php
	// modules/doc/admin_inint.php
	$config['doc']['description'] = $lng['DOC_DESCRIPTION'];
	// เมนูเขียนเรื่อง
	if (isset($install_owners['doc'])) {
		foreach ($install_owners['doc'] AS $items) {
			$admin_menus['modules'][$items['module']]['write'] = '<a href="index.php?module=doc-write&amp;id='.$items['id'].'" title="{LNG_DOCUMENT_WRITE}"><span>{LNG_DOCUMENT_WRITE}</span></a>';
		}
	}