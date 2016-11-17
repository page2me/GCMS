<?php
/*
 * @filesource edocument/controllers/init.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Init;

use \Gcms\Gcms;

/**
 * เริ่มต้นใช้งานโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

  /**
   * Init Module
   */
  public function init()
  {
    if (!empty(Gcms::$install_owners['edocument'])) {
      Gcms::$member_tabs['edocument'] = array('E-Document', 'Edocument\Member\View');
      Gcms::$member_tabs['edocumentwrite'] = array(null, 'Edocument\Write\View');
      Gcms::$member_tabs['edocumentreport'] = array(null, 'Edocument\Report\View');
    }
  }
}
