<?php
/**
 * Created by PhpStorm.
 * User: Dennis
 * Date: 12/7/15
 * Time: 02:32
 */

$sku_id = -9999;
$from_web = 0;
if (isset($_REQUEST['sku_id'])) {
    try {
        $sku_id = intval($_REQUEST['sku_id'], 10);
    }
    catch (Exception $e) {
        $sku_id = -9999;
        die;
    }
} else {
    echo "非法请求";
    die;
}

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
            <div class="w-space-6">&nbsp;</div>
            <div class="w-space-6">&nbsp;</div>
            <div class="w-space-6">&nbsp;</div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav class="navbar navbar-default navbar-inverse navbar-fixed-bottom" role="navigation">
                <div class="navbar-header">
                    <a class="navbar-brand c" href="http://item.m.jd.com/product/<?php echo $sku_id; ?>.html">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;到京东商城查看购买&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
                </div>

            </nav>
        </div>
    </div>
</div>

