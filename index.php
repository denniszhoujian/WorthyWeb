<?php
/**
 * Created by PhpStorm.
 * User: Dennis
 * Date: 12/6/15
 * Time: 00:28
 */

include "config.php";

$category_id = $CONST_DEFAULT_CATALOG_ID;
if (isset($_REQUEST['category_id'])) {
    try {
        $category_id = $_REQUEST['category_id'];
    }
    catch (Exception $e) {}
}

$category_name = $CONST_DEFAULT_CATALOG_NAME;
if (isset($_REQUEST['category_name'])) {
    try {
        $category_name = $_REQUEST['category_name'];
    }
    catch (Exception $e) {}
}

$query = "";
if (isset($_REQUEST['query'])) {
    try {
        $query = $_REQUEST['query'];
    }
    catch (Exception $e) {}
}

?>


<!doctype html>
<html>
<?php include "header.php"; ?>

<script language="JavaScript">

    /**
     * SETTING CONSTS & GLOBAL VARS
     * */
    var g_category_id = "<?php echo $category_id; ?>";
    var g_start_pos = 1;
    var g_frames = 0;
    var g_old_time = 0;
    var g_new_id = "sku-list-div";
    var g_no_more_data = 0;
    var g_loading = 0;
    var image_url_prefix_xl = 'http://img11.360buyimg.com/n1/';
    var image_url_prefix_xs = 'http://img11.360buyimg.com/n9/';

    $(document).ready(function() {
        $("#j_search_submit").on('click',function(){
            $("#j_search_form").submit();
        });
        window.scrollTo(0,0);
        fillCategories();
        loadFrame(g_category_id,0);
        register_scroll_bottom();
    });

    function fillCategories() {
        $.ajax({
            url:'<?php echo $URL_JSONP?>/category/list',
            dataType: 'jsonp',
            type:'get',
            data: {'level': 0},
            async : true,
            timeout: 100000,
            error:function(){
                console.log('error');
//                $.hideLoading();
            },
            success:function(data){
                try {
                    showCategories(data)
                } finally {
//                    $.hideLoading();
                }
            },
            cache: true
        });
    }

    function expandCategories(data) {
        var newdata = new Array();
        newdata.push({'category_id':'_ALL_', 'category_name': '全部折扣'});
        newdata.push({'category_id':'_EXPENSIVE_', 'category_name': '超值折扣'});
        for (var i=0;i<data.length;i++) {
            newdata.push(data[i]);
        }
        return newdata;
    }

    function showCategories(data) {
        var markup = "";
        var newdata = expandCategories(data);
        for (var i=0;i<data.length;i++) {
//            markup += '<span class="w-category-item" onclick="switchCategory("'+data[i].category_id+'")">'+data[i].category_name+'</span>';
            markup += '<li><a href="index.php?category_name='+newdata[i].category_name+'&category_id='+newdata[i].category_id+'" abc="switchCategory("'+newdata[i].category_id+'")">'+newdata[i].category_name+'</a></li>';
        }
        $("#j_category_ul").html(markup);
    }

    function switchCategory(cat_id) {
        g_category_id = cat_id;
        g_start_pos = 1;
        loadFrame(cat_id,0);
    }

    function getScrollHeight(){
        var scrollHeight = 0, bodyScrollHeight = 0, documentScrollHeight = 0;
        if(document.body){
            bodyScrollHeight = document.body.scrollHeight;
        }
        if(document.documentElement){
            documentScrollHeight = document.documentElement.scrollHeight;
        }
        scrollHeight = (bodyScrollHeight - documentScrollHeight > 0) ? bodyScrollHeight : documentScrollHeight;
        return scrollHeight;
    }

    function register_scroll_bottom() {
        $(window).scroll(function() {

            if(g_no_more_data) return;

            var $this = $(this),
                viewH = $(this).height(),
                contentH = getScrollHeight(),
                scrollTop = $(this).scrollTop();
            if (contentH - viewH - scrollTop <= 100) {
                //console.log('here');
                var timestamp = Date.parse(new Date());
                if ( (timestamp - g_old_time) > 500 && g_frames*30>=(g_start_pos-1) ) {
                    g_old_time = timestamp;
                    loadFrame(g_category_id, g_start_pos);
                }
            }
        });
    }

    function getSkuThumbHtmlWithId(sku_id) {
        var html = $("#mark-div").html().replace(/\n/g,' ');
        var rpc = html.replace(/_rotate/g,'_rotate_' + sku_id);
        return rpc;
    }

    function loadFrame(category_id,start_pos) {
        if (g_loading > 0) return;
        g_loading = 1;
        $.showLoading();
        $.ajax({
            url:'<?php echo $URL_JSONP?>/sku/list',
            dataType: 'jsonp',
            type:'get',
            data: {'category_id': category_id, 'startpos':start_pos, 'query':'<?php echo $query; ?>'},
            async : true,
            timeout: 100000,
            error:function(){
                console.log('error');
                $.hideLoading();
                g_loading = 0;
            },
            success:function(data){
                try {
                    processData(data);
                    fillNavTitle();
                } finally {
                    $.hideLoading();
                    g_loading = 0;
                }
            },
            cache: true
        });
    }

    function noMoreData() {
        g_no_more_data = 1;
        $("#j_pull_refresh").html("没有更多折扣商品了");
    }

    function processData(data) {
        var markup = "";
        if (data.length<30) noMoreData();
        if (data.length == 0) return -1;

        g_frames ++;

        for (i=0;i<data.length;++i) {
            var sku = data[i];
            var sku_id = sku.sku_id;
            var plain_html = getSkuThumbHtmlWithId(sku_id);
            markup += plain_html;
        }

        $("#" + g_new_id).html(markup);

        for (i=0;i<data.length;++i) {
            var sku = data[i];
            var sku_id = sku.sku_id;
            if (sku_id == null || sku_id == 'undefined') continue;
            fillNavTitle();
            fillThumb(sku_id,sku);
            fillDiscounts(sku_id,sku.deducts);
        }

        g_start_pos += data.length;

        addConnector();
        if (data.length<30) noMoreData();
    }

    function fillNavTitle() {
        var str = "";

        <?php if ($query == "") { ?>

        if (g_category_id == '_EXPENSIVE_') str = "超值折扣";
        else if (g_category_id == '_ALL_') str = "全部折扣";
        else str = "<?php echo $category_name; ?>";

        <?php } else echo "str = '$query';\n"; ?>

        $("#query").attr('placeholder',str);
    }

    function fillThumb(sku_id, thumb) {
        var sku_id_idstr = '_rotate_' + sku_id;
        $("#j_main_image" + sku_id_idstr).attr('src',thumb.thumbnail_url);
        $("#j_title" + sku_id_idstr).html(thumb.title);
        $("#j_title" + sku_id_idstr).attr('href','<?php echo $URL_HOST ?>/skuAnalytics.php?from=web&sku_id=' + sku_id);
        if (thumb.rating_score_diff != null)
            $("#j_rating_diff" + sku_id_idstr).html('高于'+disposeNumber(thumb.rating_score_diff*100,0)+'\%的'+thumb.category_name+'类商品');
        else $("#j_ratings" + sku_id_idstr).css('display','none');
        $("#j_final_price" + sku_id_idstr).html(thumb.final_price);
        $("#j_mobile_price" + sku_id_idstr).html(thumb.current_price);
        var base_price = thumb.median_price;
        if (thumb.current_price > base_price) base_price = current_price;
        $("#j_base_price" + sku_id_idstr).html(base_price);
        var max_price = base_price;
        if (thumb.current_price>base_price) max_price = thumb.current_price;
        $("#j_final_discount" + sku_id_idstr).html(disposeNumber(thumb.final_price/max_price*10,1));
        $("#j_base_discount" + sku_id_idstr).html('('+disposeNumber(thumb.current_price/base_price*100,0)+'\%)');
        $("#j_min_price" + sku_id_idstr).html(disposeNumber(thumb.min_price,0));
        if (thumb.min_price_reached>1) $("#j_min_price_reached" + sku_id_idstr).html("历史最低");
        if (thumb.gift_name!=null) {
            $("#j_gift_title" + sku_id_idstr).html(thumb.gift_name);
            $("#j_gift_num" + sku_id_idstr).html(thumb.gift_num);
            $("#j_gift_price" + sku_id_idstr).html(disposeNumber(thumb.gift_price,0));
            $("#j_gift_image" + sku_id_idstr).attr('src',image_url_prefix_xs + thumb.gift_image);
            $("#gift_link" + sku_id_idstr).attr('href','<?php echo $URL_HOST; ?>/skuAnalytics.php?sku_id='+thumb.gift_sku_id);
            if (thumb.gift_price==null) $("#gift_link" + sku_id_idstr).attr('href','http://item.m.jd.com/product/'+thumb.gift_sku_id +'.html');
        } else {
            $("#j_gift_div" + sku_id_idstr).css('display', 'none');
            $("#j_gift_div2" + sku_id_idstr).css('display', 'none');
        }
    }

    function gridOnClick() {
        alert("tapped: sku_id=" + 0);
    }

    function fillDiscounts(sku_id, disc) {
        var sku_id_idstr = '_rotate_' + sku_id;
        var markup = '';
        //if (disc.length > 0) $("#j_discount_div" + sku_id_idstr).css('display','block'); else $("#j_discount_div" + sku_id_idstr).css('display','none');
        for (var i=0;i<disc.length;i++) {
            markup +=
                '                            <div class="col-xs-3 col-sm-3 no-padding">' +
                '                                <div class="w-list">' +
                '                                    <span class="w-badge-label">' + disc[i].name + '</span>' +
                '                                </div>' +
                '                            </div>' +
                '                            <div class="col-xs-9 col-sm-9 no-padding">' +
                '                                <div class="w-list">' +
                '                                    <span class="w-label w-color-discounts">' + disc[i].content + '</span>' +
                '                                </div>' +
                '                        </div>';
        }
        $("#j_discount_div" + sku_id_idstr).html(markup);
    }

    function addConnector() {
        var html = $("#sku_table_view").html();
        var timestamp = Date.parse(new Date());
        var newid = "sku-list-div-" + timestamp;
        var markup = '<div class="container-fluid"><div class="row w-container" id="'+newid+'"><div class="c w-vertical-padding" id="j_pull_refresh"><li class="fa fa-circle-o-notch fa-spin"></li>&nbsp;正在努力加载中…</div></div></div>';
        html += markup;
        $("#sku_table_view").html(html);
        g_new_id = newid;
        return newid;
    }


</script>

<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav class="navbar  navbar-worthy navbar-fixed-top" role="navigation">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#" id="j_nav_title"><img src="resources/jj_icon.gif" width="20" height="20"></a>
                    <p class="navbar-text navbar-right">
                        <form class="navbar-left" id="j_search_form">
                            <span class="search-form">
                                <input type="text" width="300" heigh="40" class="searchbox" placeholder="输入搜索词…" id="query" name="query">
                                &nbsp;<a href="#" id="j_search_submit""><li class="fa fa-search"></li></a>
                            </span>
                        </form>
                    </p>
                </div>
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav" id="j_category_ul">
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
            <div class="w-space-6">&nbsp;</div>
            <div class="w-space-6">&nbsp;</div>
            <div class="w-space-6">&nbsp;</div>
        </div>
    </div>
</div>

<div id="sku_table_view">
    <div class="container-fluid">
        <div class="row w-container" id="sku-list-div"></div>
    </div>
</div>

<div class="container-fluid w-hidden" id="mark-div">
    <div class="col-xs-12 col-sm-12 col-lg-4 col-md-6 no-padding">
        <div class="w-tbl-grid" id="j_tbl_grid_rotate">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-2 col-sm-2 no-padding">

                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 no-padding">
                                    <div class="w-list c" id="j_image_div_rotate">
                                        <img id="j_main_image_rotate" src="resources/placeholder_large.png" width="70">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-xs-10 col-sm-10">

                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 no-padding">
                                    <div class="w-list">
                                        <span class="w-label w-color-main b" id="j_min_price_reached_rotate"></span>
                                        <span class="w-label-l"><a href="#" id="j_title_rotate"></a>&nbsp;</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-3 col-sm-3 no-padding">
                                    <div class="w-list">
                                        <span class="w-badge-label">JD自营</span>
                                    </div>
                                </div>
                                <div class="col-xs-9 col-sm-9 no-padding">
                                    <div class="w-list" id="j_ratings_rotate">
                                        <span class="w-label w-gray">评价</span>
                                        <span class="w-label" id="j_rating_diff_rotate"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-4 col-sm-4 no-padding">
                                    <div class="w-list">
                                        <span class="w-label-s w-color-main">到手价</span>
                                        <span class="w-value w-color-main" id="j_final_price_rotate"></span>
                                    </div>
                                </div>
                                <div class="col-xs-5 col-sm-5 no-padding">
                                    <div class="w-list">
                                        <span class="w-label-s w-grey">历史最低价</span>
                                        <span class="w-label" id="j_min_price_rotate"></span>
                                    </div>
                                </div>
                                <div class="col-xs-3 col-sm-3 no-padding">
                                    <div class="w-list r">
                                        <span class="w-label-l" id="j_final_discount_rotate"></span>
                                        <span class="w-label-xs">折</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-4 col-sm-4 no-padding">
                                    <div class="w-list">
                                        <span class="w-label-s w-grey">当前价</span>
                                        <span class="w-label" id="j_mobile_price_rotate"></span>
                                    </div>
                                </div>
                                <div class="col-xs-8 col-sm-8 no-padding">
                                    <div class="w-list">
                                        <span class="w-label-s w-grey">历史常规价</span>
                                        <span class="w-label" id="j_base_price_rotate"></span>
                                        <span class="w-label-xs w-grey" id="j_base_discount_rotate"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="j_discount_div_rotate"></div>


                            <div class="row" id="j_gift_div_rotate">
                                <div class="col-xs-2 col-sm-2 no-padding">
                                    <div class="w-list">
                                        <span class="w-badge-label w-color-main">赠品</span>
                                    </div>
                                </div>
                                <div class="col-xs-10 col-sm-10 no-padding">
                                    <div class="w-list">
                                        <span class="w-label" id="j_gift_title_rotate"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="j_gift_div2_rotate">
                                <div class="col-xs-2 col-sm-2 no-padding">
                                    <div class="w-list">
                                        <span class="w-space-6"></span>
                                    </div>
                                </div>
                                <div class="col-xs-10 col-sm-10 no-padding">
                                    <div class="w-list">
                                        <img src="resources/placeholder.png" width="32" height="32" id="j_gift_image_rotate"/>
                                        <span class="w-label">x<span id="j_gift_num_rotate"></span></span>
                                        <span class="w-space-6"></span>
                                        <span class="w-label">￥<span id="j_gift_price_rotate">&nbsp;</span></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <span></span>
                                </div>
                            </div>


                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<?php include "footer.php"; ?>

</body>

<?php include "scripts.php"; ?>

</html>


