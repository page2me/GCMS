<?php
/*
 * @filesource Widgets/Textlink/styles.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
/**
 * @return array template สำหรับ Text Links
 */
return array(
  'custom' => '',
  'text' => '<a title="{TITLE}"{URL}{TARGET}>{TITLE}</a>',
  'menu' => '<li><a title="{TITLE}"{URL}{TARGET}><span>{TITLE}</span></a></li>',
  'image' => '<a title="{TITLE}"{URL}{TARGET}><img class="nozoom" alt="{TITLE}" src="{LOGO}"></a>',
  'banner' => '<a title="{TITLE}"{URL}{TARGET}><img class="nozoom" alt="{TITLE}" src="{LOGO}"></a>',
  'slideshow' => ''
);
