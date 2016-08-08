<?php
/*
 * @filesource index/views/menu.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menu;

use \Index\Index\Model as Menu;

/**
 * เมนูหลัก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{

  /**
   * สร้างเมนู
   *
   * @return string
   */
  public static function render()
  {
    $mymenu = '';
    foreach (Menu::$menus['sections'] AS $section => $name) {
      $link = preg_match('/<a.*>.*<\/a>/', $name[1]) ? $name[1] : '<a accesskey='.$name[0].' class=menu-arrow><span>'.$name[1].'</span></a>';
      $mymenu .= '<li class="'.$section.'">'.$link;
      if (isset(Menu::$menus[$section]) && sizeof(Menu::$menus[$section]) > 0) {
        $mymenu .= '<ul>';
        foreach (Menu::$menus[$section] AS $key => $value) {
          if (is_array($value)) {
            $mymenu .= '<li class="'.$key.'"><a class=menu-arrow tabindex=0><span>{LNG_'.ucfirst($key).'}</span></a><ul>';
            foreach ($value AS $key2 => $value2) {
              $mymenu .= '<li class="'.$key2.'">'.$value2.'</li>';
            }
            $mymenu .= '</ul></li>';
          } else {
            $mymenu .= '<li class="'.$key.'">'.$value.'</li>';
          }
        }
        $mymenu .= '</ul>';
      }
      $mymenu .= '</li>';
    }
    return $mymenu;
  }
}