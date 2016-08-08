<?php
/*
 * @filesource Gcms/Login.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Gcms;

/**
 * Router Class สำหรับ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Router extends \Kotchasan\Router
{
  /**
   * กฏของ Router สำหรับการแยกหน้าเว็บไซต์
   *
   * @var array
   */
  protected $rules = array(
    // index.php/Widgets/Textlink/Models/Write/save
    '/^[a-z0-9]+\.php\/(Widgets\/[a-z]+\/Models\/[a-z]+)\/([a-z]+)$/i' => array('_class', '_method'),
    // index.php/module/model/folder/_dir/_method
    '/^[a-z0-9]+\.php\/([a-z]+)\/(model)(\/([\/a-z0-9_]+)\/([a-z]+))?$/i' => array('module', '_mvc', '', '_dir', '_method'),
    // css, js
    '/(css|js)\/(view)\/(index)/i' => array('module', '_mvc', '_dir'),
    // antispam image
    '/(index)\/(view)\/(antispam)/i' => array('module', '_mvc', '_dir'),
    // install
    '/index\.php\/(index)\/(controller)\/(installing)/i' => array('module', '_mvc', '_dir'),
    // module/cat/id
    '/^([a-z]+)\/([0-9]+)\/([0-9]+)$/' => array('module', 'cat', 'id'),
    // module/cat module/alias, module/cat/alias
    '/^([a-z]+)(\/([0-9]+))?(\/(.*))?$/' => array('module', '', 'cat', '', 'alias'),
    // module, module.php
    '/^([a-z0-9_]+)(\.php)?$/' => array('module'),
    // alias
    '/^(.*)$/' => array('alias')
  );
}