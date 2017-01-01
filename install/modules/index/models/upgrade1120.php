<?php
/*
 * @filesource index/views/upgrade1120.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Upgrade1120;

use \Kotchasan\Language;

/**
 * อัปเกรด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Index\Upgrade\Model
{

  /**
   * อัปเกรดจากเวอร์ชั่น 11.0.0
   *
   * @return string
   */
  public static function upgrade($db)
  {
    $content = array();
    // install database language
    $db->query("DROP TABLE IF EXISTS `$_SESSION[prefix]_language`;");
    $db->query("CREATE TABLE `$_SESSION[prefix]_language` (`id` int(11) unsigned NOT NULL auto_increment,`key` text collate utf8_unicode_ci NOT NULL,`th` text collate utf8_unicode_ci NOT NULL,`en` text collate utf8_unicode_ci NOT NULL,`owner` varchar(20) collate utf8_unicode_ci NOT NULL,`type` varchar(5) collate utf8_unicode_ci NOT NULL,`js` tinyint(1) NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    // import language
    foreach (array('php', 'js') as $lng) {
      foreach (Language::installed($lng) as $item) {
        if (!empty($item['array'])) {
          $item['type'] = 'array';
          if (isset($item['th']) && is_array($item['th'])) {
            $item['th'] = serialize($item['th']);
          }
          if (isset($item['en']) && is_array($item['en'])) {
            $item['en'] = serialize($item['en']);
          }
        } elseif (is_int($item['th'])) {
          $item['type'] = 'int';
        } else {
          $item['type'] = 'text';
        }
        unset($item['id']);
        unset($item['array']);
        $item['js'] = $lng == 'js' ? 1 : 0;
        $item['owner'] = 'index';
        $db->insert($_SESSION['prefix'].'_language', $item);
      }
    }
    $content[] = '<li class="correct">Created and Imported database <b>'.$_SESSION['prefix'].'_language</b> complete...</li>';
    // อัปเกรด useronline
    $f = $db->query('ALTER TABLE `'.$_SESSION['prefix'].'_useronline` DROP `id`');
    $f = $db->query('ALTER TABLE `'.$_SESSION['prefix'].'_useronline` DROP `icon`');
    $content[] = '<li class="'.($f ? 'correct' : 'incorrect').'">Update database <b>'.$_SESSION['prefix'].'_useronline</b> complete...</li>';
    // update database index
    $db->query("ALTER TABLE `$_SESSION[prefix]_index` CHANGE `language` `language` VARCHAR( 2 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';");
    $db->query("ALTER TABLE `$_SESSION[prefix]_index` CHANGE `visited` `visited` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0;");
    $db->query("ALTER TABLE `$_SESSION[prefix]_index` CHANGE `visited_today` `visited_today` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0;");
    $db->query("ALTER TABLE `$_SESSION[prefix]_index` CHANGE `comments` `comments` SMALLINT( 3 ) UNSIGNED NOT NULL DEFAULT 0;");
    $db->query("ALTER TABLE `$_SESSION[prefix]_index` CHANGE `comment_id` `comment_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0;");
    $db->query("ALTER TABLE `$_SESSION[prefix]_index` CHANGE `commentator_id` `commentator_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0;");
    $content[] = '<li class="correct">Updated database <b>'.$_SESSION['prefix'].'_index</b> complete...</li>';
    if (\Index\Upgrade\Model::tableExists($db, $_SESSION['prefix'].'_eventcalendar')) {
      $db->query("ALTER TABLE `$_SESSION[prefix]_eventcalendar` CHANGE `color` `color` VARCHAR( 11 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
      $content[] = '<li class="correct">Updated database <b>'.$_SESSION['prefix'].'_eventcalendar</b> complete...</li>';
    }
    // update database download
    if (\Index\Upgrade\Model::tableExists($db, $_SESSION['prefix'].'_download')) {
      $db->query("ALTER TABLE `$_SESSION[prefix]_download` ADD `reciever` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
      $content[] = '<li class="correct">Updated database <b>'.$_SESSION['prefix'].'_download</b> complete...</li>';
    }
    // update database.php
    $f = \Index\Upgrade\Model::updateTables(array('language' => 'language'));
    $content[] = '<li class="'.($f ? 'correct' : 'incorrect').'">Update file <b>database.php</b> ...</li>';
    $content[] = '<li class="correct">Upgrade to Version <b>11.1.0</b> complete.</li>';
    return (object)array(
        'content' => implode('', $content),
        'version' => '11.2.0'
    );
  }
}