<?php
/**
 * @filesource Widgets/Map/Views/Map.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Widgets\Map\Views;

use \Kotchasan\Http\Request;
use \Kotchasan\Language;

/**
 * Description
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Map extends \Gcms\View
{

  public function render(Request $request)
  {
    // หน้าเว็บ Google Map
    $map[] = '<!DOCTYPE html>';
    $map[] = '<html lang='.Language::name().' dir=ltr>';
    $map[] = '<head>';
    $map[] = '<title>Google Map</title>';
    $map[] = '<style>';
    $map[] = 'html,body,#map_canvas{height:100%}';
    $map[] = 'body{margin:0 auto;padding:0;font-family:Tahoma;font-size:12px;text-align:center;line-height:1.5em}';
    $map[] = '</style>';
    $map[] = '<script src="//maps.google.com/maps/api/js?key='.(empty(self::$cfg->map_api_key) ? '' : self::$cfg->map_api_key).'&language='.Language::name().'"></script>';
    $map[] = '<meta charset=utf-8>';
    $map[] = '<script>';
    $map[] = 'function initialize() {';
    $map[] = 'var myLatlng = new google.maps.LatLng("'.$request->get('lat')->topic().'","'.$request->get('lant')->topic().'");';
    $map[] = 'var myOptions = {';
    $map[] = 'zoom:'.$request->get('zoom', 14)->toInt().',';
    $map[] = 'center:myLatlng,';
    $map[] = 'mapTypeId:google.maps.MapTypeId.ROADMAP';
    $map[] = '};';
    $map[] = 'var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);';
    $info = $request->get('info')->toString();
    if (!empty($info)) {
      $map[] = "var infowindow = new google.maps.InfoWindow({content:'".nl2br(str_replace(array('&lt;', '&gt;', '&#92;'), array('<', '>', '\\'), $info))."'});";
      $map[] = 'var info = new google.maps.LatLng("'.$request->get('info_lat')->topic().'","'.$request->get('info_lant')->topic().'");';
      $map[] = 'var marker = new google.maps.Marker({position:info,map:map});';
      $map[] = 'infowindow.open(map,marker);';
      $map[] = 'google.maps.event.addListener(marker,"click",function(){';
      $map[] = 'infowindow.open(map,marker);';
      $map[] = '});';
    }
    $map[] = '}';
    $map[] = '</script>';
    $map[] = '</head>';
    $map[] = '<body onload="initialize()">';
    $map[] = '<div id=map_canvas>Google Map</div>';
    $map[] = '</body>';
    $map[] = '</html>';
    return implode("\n", $map);
  }
}