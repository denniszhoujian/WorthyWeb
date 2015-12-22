<?php
/**
 * Created by PhpStorm.
 * User: Dennis
 * Date: 11/29/15
 * Time: 12:54
 */


global $URL_JSONP;
global $URL_HOST;
global $CONST_DEFAULT_CATALOG_ID;
global $CONST_DEFAULT_CATALOG_NAME;


/**
 *  Configurations
 */

/** TEST ENV */
$URL_HOST = "http://192.168.31.106";
$URL_JSONP = "http://192.168.31.106:8080";

/** PROD ENV */
//$URL_HOST = "http://www.moshutao.com";
//$URL_JSONP = "http://www.moshutao.com:8090";

$CONST_DEFAULT_CATALOG_ID = "_ALL_";
$CONST_DEFAULT_CATALOG_NAME = "全部折扣";

?>