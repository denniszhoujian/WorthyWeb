<?php
/**
 * Created by PhpStorm.
 * User: Dennis
 * Date: 11/29/15
 * Time: 12:32
 */
include "config.php";

$sku_id = -9999;
if (isset($_REQUEST['sku_id'])) {
    try {
        $sku_id = intval($_REQUEST['sku_id'], 10);
    }
    catch (Exception $e) {
        $sku_id = -9999;
        die;
    }
}

?>

<!doctype html>
<html>
<?php include "header.php"; ?>

<script language="JavaScript">

    var g_sku_id = <?php echo $sku_id; ?>;
    var image_url_prefix_xl = 'http://img12.360buyimg.com/n1/';
    var image_url_prefix_xs = 'http://img12.360buyimg.com/n9/';


    $(document).ready(function() {

        $.showLoading();

        require.config({
            paths: {
                echarts: "js/echarts-2.2.7"
            }
        });

        $("#j_main_image").attr('width',window.innerWidth-24);
        $(".w-flip").attr('width',window.innerWidth-24);

        loadServerData(g_sku_id);
    });

    function loadServerData(sku_id) {
        $.ajax({
            url:'<?php echo $URL_JSONP?>/sku/info',
            dataType: 'jsonp',
            type:'get',
            data: {'sku_id': sku_id},
            async : true,
            timeout: 100000,
            error:function(){
                console.log('error');
                $.hideLoading();
            },
            success:function(data){
                $.hideLoading();
                fillThumb(data.worthy);
                fillDiscounts(data.discount_list);
                loadCharts(data.price_chart);
                fillImageFlips(data.worthy,data.images);
            },
            cache: true
        });
    }

    function fillImageFlips(thumb,images) {
        var markup = '<div class="carousel slide" id="carousel-239807">' +
            '                    <ol class="carousel-indicators">' +
            '                        <li class="active" data-slide-to="0" data-target="#carousel-239807">' +
            '                        </li>';

        for (var i=0;i<images.length;i++) {
            markup += '<li data-slide-to="' + (i+1) + '" data-target="#carousel-239807"></li>';
        }

        markup +=    '                    </ol>' +
            '                    <div class="carousel-inner">';
        var p1 = thumb.thumbnail_url.replace('/n7/','/n1/');

        markup += '<div class="item active"><img class="w-flip" src="'+ p1 +'" /></div><div class="carousel-caption"><h4></h4><p></p></div>';
        for (var i=0;i<images.length;i++) {
            markup += '<div class="item"><img class="w-flip" src="'+ image_url_prefix_xl + images[i].image_url +'" /></div><div class="carousel-caption"><h4></h4><p></p></div>';
        }
        markup += '                    </div> <a class="left carousel-control" href="#carousel-239807" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a> <a class="right carousel-control" href="#carousel-239807" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>' +
            '                </div>';
        $("#j_image_div").html(markup);

        $('#carousel-239807').hammer().on('swipeleft', function(){
            $(this).carousel('next');
        });

        $('#carousel-239807').hammer().on('swiperight', function(){
            $(this).carousel('prev');
        });
    }

    function fillThumb(thumb) {
        $("#j_title").html(thumb.title);
        if (thumb.rating_score_diff != null)
            $("#j_rating_diff").html('高于'+disposeNumber(thumb.rating_score_diff*100,0)+'\%的'+thumb.category_name+'类商品');
        else $("#j_ratings").css('display','none');
        $("#j_final_price").html(thumb.final_price);
        $("#j_mobile_price").html(thumb.current_price);
        var base_price = thumb.median_price;
        if (thumb.current_price > base_price) base_price = current_price;
        $("#j_base_price").html(base_price);
        $("#j_final_discount").html(disposeNumber(thumb.final_price/base_price*10,1));
        $("#j_base_discount").html('('+disposeNumber(thumb.current_price/base_price*100,0)+'\%)');
        $("#j_min_price").html(disposeNumber(thumb.min_price,0));
        if (thumb.min_price_reached) $("#j_min_price_reached").html("历史最低价");
        if (thumb.gift_name!=null) {
            $("#j_gift_title").html(thumb.gift_name);
            $("#j_gift_num").html(thumb.gift_num);
            $("#j_gift_price").html(disposeNumber(thumb.gift_price,0));
            $("#j_gift_image").attr('src',image_url_prefix_xs + thumb.gift_image);
        } else
            $("#j_gift_div").css('display','none');
    }

    function fillDiscounts(disc) {
        var markup = '';
        for (var i=0;i<disc.length;i++) {
            markup += '	<div class="row">' +
                '        <div class="col-xs-2 col-sm-2">' +
                '            <div class="w-grid">' +
                '                <span class="w-badge-label w-color-main">' + disc[i].name + '</span>' +
                '            </div>' +
                '        </div>' +
                '        <div class="col-xs-10 col-sm-10">' +
                '            <div class="w-grid">' +
                '                <span class="w-label w-color-main">' + disc[i].content + '</span>' +
                '            </div>' +
                '        </div>' +
                '    </div>';
        }
        $("#j_discount_div").html(markup);
    }

    function loadCharts(chart_data) {
        var timestamp = Date.parse(new Date());
        var chartid = 'price_chart_' + timestamp;
        var chartdiv = '<div class="w-chart" style="height:400px" id="' + chartid + '"></div>';
        $("#price-chart").html(chartdiv);

        require(
            [
                'echarts',
                'echarts/chart/line'
            ],
            function (ec) {
                var myChart = ec.init(document.getElementById(chartid),'macarons');
                option = {
                    title : {
                        text: ''
                    },
                    tooltip : {
                        trigger: 'axis'
                    },
//                    legend: {
//                        data:['价格']
//                    },
                    toolbox: {
                        show : false
                    },
                    calculable : true,
//                    dataZoom: {
//                        show: true,
//                        realtime: true,
//                        start: 0,
//                        end: 100
//                    },
                    xAxis : [
                        {
                            type : 'category',
                            boundaryGap : true,
                            data : chart_data.dates
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value',
                            axisLabel : {
                                formatter: '{value}元'
                            }
                        }
                    ],
                    series : [
                        {
                            name:'价格',
                            type:'line',
                            data:chart_data.prices,
                            markPoint : {
                                data : [
                                    {type : 'max', name: '最高价'},
                                    {type : 'min', name: '最低价'}
                                ]
                            }
//                            ,
//                            markLine : {
//                                data : [
//                                    {type : 'min', name: '最低价'}
//                                ]
//                            }
                        }
                    ]
                };

                myChart.setOption(option);
                window.onresize = function () {
                    myChart.resize();
                }
            }
        );
    }



</script>

<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="w-grid c" id="j_image_div">
                <img id="j_main_image" src="resources/placeholder_large.png">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <div class="w-grid">
                <span id="j_title" class="w-label-xl"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-2 col-sm-2">
            <div class="w-grid">
                <span class="w-badge-label">JD自营</span>
            </div>
        </div>
        <div class="col-xs-7 col-sm-7">
            <div class="w-grid" id="j_ratings">
                <span class="w-label w-gray">评价</span>
                <span class="w-label" id="j_rating_diff"></span>
            </div>
        </div>
        <div class="col-xs-3 col-sm-3">
            <div class="w-grid">
                <span class="w-label w-color-main b" id="j_min_price_reached"></span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-4 col-sm-4">
            <div class="w-grid">
                <span class="w-label w-color-main">到手价</span>
                <span class="w-space-6"></span>
                <span class="w-value-xl w-color-main b" id="j_final_price"></span>
            </div>
        </div>
        <div class="col-xs-5 col-sm-5">
            <div class="w-grid">
                <span class="w-label w-grey">历史最低价</span>
                <span class="w-space-6"></span>
                <span class="w-label-xl" id="j_min_price"></span>
            </div>
        </div>
        <div class="col-xs-3 col-sm-3">
            <div class="w-grid r">
                <span class="w-label-xxl" id="j_final_discount"></span>
                <span class="w-label-xs">折</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-4 col-sm-4">
            <div class="w-grid">
                <span class="w-label w-grey">当前价</span>
                <span class="w-space-6"></span>
                <span class="w-label-xl" id="j_mobile_price"></span>
            </div>
        </div>
        <div class="col-xs-8 col-sm-8">
            <div class="w-grid">
                <span class="w-label w-grey">历史常规价</span>
                <span class="w-space-6"></span>
                <span class="w-label-xl" id="j_base_price"></span>
                <span class="w-space-6"></span>
                <span class="w-label-s w-grey" id="j_base_discount"></span>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid" id="j_discount_div"></div>

<div class="container-fluid" id="j_gift_div">
    <div class="row">
        <div class="col-xs-2 col-sm-2">
            <div class="w-grid">
                <span class="w-badge-label w-color-main">赠品</span>
            </div>
        </div>
        <div class="col-xs-10 col-sm-10">
            <div class="w-grid">
                <span class="w-label" id="j_gift_title"></span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-2 col-sm-2">
            <div class="w-grid">
                <span class="w-space-6"></span>
            </div>
        </div>
        <div class="col-xs-10 col-sm-10">
            <div class="w-grid">
                <img src="resources/placeholder.png" width="32" height="32" id="j_gift_image"/>
                <span class="w-label">x<span id="j_gift_num"></span></span>
                <span class="w-space-6"></span>
                <span class="w-label">￥<span id="j_gift_price">28</span></span>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <span class="w-space-6"></span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12">
            <span class="w-label-xl b">历史价格</span>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12">
                <div class="w-chart" id="price-chart">
                    CHART HERE
                </div>
        </div>
    </div>

</div>


</body>

<?php include "scripts.php"; ?>

</html>
