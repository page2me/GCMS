<?php
/*
 * BingSiteAuth.php
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
// load Kotchasan
include 'load.php';
// config
$cfg = Gcms\Config::create();
if (empty($cfg->msvalidate)) {
  new \Kotchasan\Http\NotFound();
} else {
  $response = new Kotchasan\Http\Response();
  $content = '<'.'?xml version="1.0"?'.'>';
  $content .= "\n<users>";
  $content .= "\n\t<user>".$cfg->msvalidate."</user>";
  $content .= "\n</users>";
  $response->setContent($content)
    ->withHeader('Content-Type', 'application/xml; charset=UTF-8')
    ->send();
}
