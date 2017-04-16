<?php
/**
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
    $db->query("CREATE TABLE `$_SESSION[prefix]_language` (`id` int(11) unsigned NOT NULL auto_increment,`key` text collate utf8_unicode_ci NOT NULL,`th` text collate utf8_unicode_ci NULL DEFAULT NULL,`en` text collate utf8_unicode_ci NULL DEFAULT NULL,`owner` varchar(20) collate utf8_unicode_ci NOT NULL,`type` varchar(5) collate utf8_unicode_ci NOT NULL,`js` tinyint(1) NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
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
    $table = $_SESSION['prefix'].'_useronline';
    if (self::fieldExists($db, $table, 'id')) {
      $f = $db->query("ALTER TABLE `$table` DROP `id`");
    }
    if (self::fieldExists($db, $table, 'icon')) {
      $f = $db->query("ALTER TABLE `$table` DROP `icon`");
    }
    $content[] = '<li class="correct">Update database <b>'.$table.'</b> complete...</li>';
    // update database index
    $table = $_SESSION['prefix'].'_index';
    $db->query("ALTER TABLE `$table` CHANGE `language` `language` VARCHAR( 2 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';");
    $db->query("ALTER TABLE `$table` CHANGE `visited` `visited` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0;");
    $db->query("ALTER TABLE `$table` CHANGE `visited_today` `visited_today` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0;");
    $db->query("ALTER TABLE `$table` CHANGE `comments` `comments` SMALLINT( 3 ) UNSIGNED NOT NULL DEFAULT 0;");
    $db->query("ALTER TABLE `$table` CHANGE `comment_id` `comment_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0;");
    $db->query("ALTER TABLE `$table` CHANGE `commentator_id` `commentator_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT 0;");
    $content[] = '<li class="correct">Updated database <b>'.$table.'</b> complete...</li>';
    // add FULLTEXT
    $db->query('ALTER TABLE `'.$_SESSION['prefix'].'_index_detail` ADD FULLTEXT (`topic`);');
    $db->query('ALTER TABLE `'.$_SESSION['prefix'].'_index_detail` ADD FULLTEXT (`detail`);');
    $db->query('ALTER TABLE `'.$_SESSION['prefix'].'_comment` ADD FULLTEXT (`detail`);');
    $db->query('ALTER TABLE `'.$_SESSION['prefix'].'_board_q` ADD FULLTEXT (`topic`);');
    $db->query('ALTER TABLE `'.$_SESSION['prefix'].'_board_q` ADD FULLTEXT (`detail`);');
    $db->query('ALTER TABLE `'.$_SESSION['prefix'].'_board_r` ADD FULLTEXT (`detail`);');
    $content[] = '<li class="correct">Updated database for <b>FULLTEXT SEARCH</b> complete...</li>';
    if (\Index\Upgrade\Model::tableExists($db, $_SESSION['prefix'].'_eventcalendar')) {
      $db->query("ALTER TABLE `$_SESSION[prefix]_eventcalendar` CHANGE `color` `color` VARCHAR( 11 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
      $content[] = '<li class="correct">Updated database <b>'.$_SESSION['prefix'].'_eventcalendar</b> complete...</li>';
    }
    // update database download
    $table = $_SESSION['prefix'].'_download';
    if (\Index\Upgrade\Model::tableExists($db, $table) && !self::fieldExists($db, $table, 'reciever')) {
      $db->query("ALTER TABLE `$table` ADD `reciever` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;");
      $content[] = '<li class="correct">Updated database <b>'.$table.'</b> complete...</li>';
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