<?php
if (INSTALL_INIT == 'install') {
    $error = false;
    // ftp datas
    $ftp_host = trim(gcms::getVars($_POST, 'ftp_host', ''));
    $_SESSION['ftp_username'] = trim(gcms::getVars($_POST, 'ftp_username', ''));
    $_SESSION['ftp_password'] = trim(gcms::getVars($_POST, 'ftp_password', ''));
    $ftp_port = gcms::getVars($_POST, 'ftp_port', 0);
    $ftp_root = trim(gcms::getVars($_POST, 'ftp_root', ''));
    $_SESSION['ftp_host'] = $ftp_host == '' ? $_SERVER['REMOTE_ADDR'] : $ftp_host;
    $_SESSION['ftp_port'] = $ftp_port == 0 ? 21 : $ftp_port;
    $_SESSION['ftp_root'] = $ftp_root == '' ? 'public_html' : $ftp_root;
    // connect ftp
    $ftp = new ftp($_SESSION['ftp_host'], $_SESSION['ftp_username'], $_SESSION['ftp_password'], $_SESSION['ftp_root'], $_SESSION['ftp_port']);
    // ตรวจสอบไฟ์ลและโฟลเดอร์
    echo '<h2>ตรวจสอบองค์ประกอบต่างๆ ที่จำเป็นสำหรับการติดตั้ง</h2>';
    echo '<ol>';
    if (!empty($_SESSION['ftp_username']) && !empty($_SESSION['ftp_password'])) {
        if ($ftp->connect()) {
            if ($ftp->is_dir($_SESSION['ftp_root'])) {
                echo '<li class=correct><strong>FTP</strong> <i>เชื่อมต่อสำเร็จ</i></li>';
            } else {
                echo '<li class=incorrect><strong>FTP Root</strong> <em>ไม่ถูกต้อง</em> กรุณาตรวจสอบจากโฮสต์ของคุณ</li>';
            }
        } else {
            echo '<li class=incorrect><strong>FTP</strong> <em>ไม่สามารถใช้งานได้</em> แต่สามารถติดตั้งต่อไปได้</li>';
        }
    }
    if (is_dir(DATA_PATH)) {
        $datas_dir = ROOT_PATH."$mmktime/";
        if (@rename(DATA_PATH, $datas_dir)) {
            copyDir($datas_dir, DATA_PATH);
        }
        gcms::rm_dir($datas_dir);
        echo '<li class=correct>นำเข้าโฟลเดอร์ <strong>'.DATA_FOLDER.'</strong> <i>เรียบร้อยแล้ว</i></li>';
    }
    gcms::saveConfig(ROOT_PATH.'bin/config.php', $config);
    $files = array();
    $files[] = ROOT_PATH.".htaccess";
    $files[] = ROOT_PATH."robots.txt";
    $files[] = ROOT_PATH."bin/config.php";
    $files[] = ROOT_PATH."bin/vars.php";
    foreach ($files AS $file) {
        if (!is_file($file)) {
            $ftp->fwrite($file, 'w', '');
        }
        if ($ftp->is_writeable($file)) {
            echo '<li class=correct>ไฟล์ <strong>'.str_replace(ROOT_PATH, '', $file).'</strong> <i>สามารถใช้งานได้</i></li>';
        } else {
            $error = true;
            echo '<li class=incorrect>ไฟล์ <strong>'.str_replace(ROOT_PATH, '', $file).'</strong> <em>ไม่สามารถเขียนหรือสร้างได้</em> กรุณาสร้างไฟล์นี้และปรับ chmod ให้เป็น 757 ด้วยตัวเอง</li>';
        }
    }
    $folders = array();
    $folders[0] = DATA_PATH;
    $dir = ROOT_PATH."modules/";
    $f = opendir($dir);
    while (false !== ($text = readdir($f))) {
        if ($text != '.' && $text != '..' && $text != 'index') {
            if (is_dir($dir.$text)) {
                $folders[] = $folders[0].$text.'/';
            }
        }
    }
    closedir($f);
    $folders[] = $folders[0].'counter/';
    $folders[] = $folders[0].'language/';
    $folders[] = $folders[0].'file/';
    $folders[] = $folders[0].'image/';
    $folders[] = $folders[0].'cache/';
    foreach ($folders AS $folder) {
        if ($ftp->mkdir($folder, 0755)) {
            echo '<li class=correct>โฟลเดอร์ <strong>'.str_replace(ROOT_PATH, '', $folder).'</strong> <i>สามารถใช้งานได้</i></li>';
        } else {
            $error = true;
            echo '<li class=incorrect>โฟลเดอร์ <strong>'.str_replace(ROOT_PATH, '', $folder).'</strong> <em>ไม่สามารถเขียนหรือสร้างได้</em> กรุณาสร้างโฟลเดอร์นี้และปรับ chmod ให้เป็น 757 ด้วยตัวเอง</li>';
        }
    }
    echo '</ol>';
    if ($error) {
        echo '<p><em>คุณต้องดำเนินการให้รายการทั้งหมดด้านบนถูกต้องก่อนดำเนินการต่อ</em>&nbsp;<a href="http://gcms.in.th/index.php?module=howto&id=24#setup1" target="_setup"><img src="'.WEB_URL.'/admin/install/img/help.png" alt=help></a>';
    }
    echo '<p>หากคุณไม่สามารถดำเนินการติดตั้งให้แล้วเสร็จได้ ให้ลองสร้างไฟล์ <em>bin/config.php</em> และ <em>bin/vars.php</em> ด้วยตัวเอง โดยทำการสำเนาไฟล์ <em>admin/install/default.config.php</em> และ <em>admin/install/default.vars.php</em> และเปลี่ยนชื่อให้เป็น <em>bin/config.php</em> และ <em>bin/vars.php</em> ตามลำดับ พร้อมทั้งแก้ไขข้อมูลในไฟล์ให้ถูกต้อง</p>';
    echo '<p><a href="index.php?step=2" class=button>ตรวจสอบใหม่</a>&nbsp;<a href="index.php?step=3" class=button>ดำเนินการต่อ</a></p>';
}
