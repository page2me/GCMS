<?php
	// modules/doc/sitemap.php
	if (is_array($owners['doc'])) {
		$sql = "SELECT `id`,`module_id`,`alias`,`create_date`";
		$sql .= " FROM `".DB_INDEX."`";
		$sql .= " WHERE `module_id` IN(".implode(',', $owners['doc']).") AND `index`='0' AND `published`='1' AND `published_date`<='$cdate'";
		$datas = $cache->get($sql);
		if (!$datas) {
			$datas = $db->customQuery($sql);
			$cache->save($sql, $datas);
		}
		foreach ($datas AS $item) {
			if ($config['module_url'] == '1') {
				$link = gcms::getURL($modules[$item['module_id']], $item['alias']);
			} else {
				$link = gcms::getURL($modules[$item['module_id']], '', 0, $item['id']);
			}
			echo '<url>';
			echo '<loc>'.$link.'</loc>';
			echo '<lastmod>'.date('Y-m-d', $item['create_date']).'</lastmod>';
			echo '<changefreq>daily</changefreq>';
			echo '<priority>0.5</priority>';
			echo '</url>';
		}
		if ($db->tableExists(DB_TAGS)) {
			// keywords
			$sql = "SELECT `tag` FROM `".DB_TAGS."`";
			$datas = $cache->get($sql);
			if (!$datas) {
				$datas = $db->customQuery($sql);
				if (sizeof($datas) > 0) {
					$cache->save($sql, $datas);
				}
			}
			foreach ($datas AS $item) {
				echo '<url>';
				echo '<loc>'.gcms::getURL('tag', $item['tag']).'</loc>';
				echo '<lastmod>'.$cdate.'</lastmod>';
				echo '<changefreq>daily</changefreq>';
				echo '<priority>0.5</priority>';
				echo '</url>';
			}
		}
	}
