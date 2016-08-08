<?php
/*
 * @filesource index/views/menu.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menu;

use \Gcms\Gcms;
use \Kotchasan\Login;

/**
 * สร้างเมนูหลักของ GCMS
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
   * @param array $items
   *
   * @return string
   */
  public function render($items, $select)
  {
    $mymenu = '';
    if (isset($items['toplevel'])) {
      foreach ($items['toplevel'] AS $level => $name) {
        if (isset($items[$level]) && sizeof($items[$level]) > 0) {
          $mymenu .= $this->getItem($name, $select, true).'<ul>';
          foreach ($items[$level] AS $level2 => $item2) {
            if ($item2->published != 0) {
              if (isset($items[$level2]) && sizeof($items[$level2]) > 0) {
                $mymenu .= $this->getItem($item2, true).'<ul>';
                foreach ($items[$level2] AS $item3) {
                  $mymenu .= $this->getItem($item3).'</li>';
                }
                $mymenu .= '</ul></li>';
              } else {
                $mymenu .= $this->getItem($item2).'</li>';
              }
            }
          }
          $mymenu .= '</ul></li>';
        } elseif ($name->published != 0) {
          $mymenu .= $this->getItem($name, $select).'</li>';
        }
      }
    }
    return $mymenu;
  }

  /**
   * ฟังก์ชั่น แปลงเป็นรายการเมนู
   *
   * @param array $item แอเรย์ข้อมูลเมนู
   * @param string $select (optional) เมนูที่ถูกเลือก
   * @param boolean $arrow (optional) true=แสดงลูกศรสำหรับเมนูที่มีเมนูย่อย (default false)
   * @return string คืนค่า HTML ของเมนู
   */
  public function getItem($item, $select = null, $arrow = false)
  {
    $c = array();
    if ($item->alias != '') {
      $c[] = $item->alias;
      if ($select === $item->alias) {
        $c[] = 'select';
      }
    } elseif ($item->module != '') {
      $c[] = $item->module;
      if ($select === $item->module) {
        $c[] = 'select';
      }
    }
    if (isset($item->published)) {
      if ($item->published != 1) {
        if (Login::isMember()) {
          if ($item->published == '3') {
            $c[] = 'hidden';
          }
        } else {
          if ($item->published == '2') {
            $c[] = 'hidden';
          }
        }
      }
    }
    $c = sizeof($c) == 0 ? '' : ' class="'.implode(' ', $c).'"';
    if ($item->index_id > 0 || $item->menu_url != '') {
      $a = $item->menu_target == '' ? '' : ' target='.$item->menu_target;
      $a .= $item->accesskey == '' ? '' : ' accesskey='.$item->accesskey;
      if ($item->index_id > 0) {
        $a .= ' href="'.Gcms::createUrl($item->module).'"';
      } elseif ($item->menu_url != '') {
        $a .= ' href="'.$item->menu_url.'"';
      } else {
        $a .= ' tabindex=0';
      }
    } else {
      $a = ' tabindex=0';
    }
    $menu_text = $item->menu_text;
    $b = $item->menu_tooltip == '' ? $menu_text : $item->menu_tooltip;
    if ($b != '') {
      $a .= ' title="'.$b.'"';
    }
    if ($arrow) {
      return '<li'.$c.'><a class=menu-arrow'.$a.'><span>'.(empty($menu_text) ? '&nbsp;' : htmlspecialchars_decode($menu_text)).'</span></a>';
    } else {
      return '<li'.$c.'><a'.$a.'><span>'.(empty($menu_text) ? '&nbsp;' : htmlspecialchars_decode($menu_text)).'</span></a>';
    }
  }
}