<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Анализ по % открытия</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">

        <style>
            .alink:hover {
                color: #4089CE;
                text-decoration: underline;
            }
            .alink {
                color: #044582;
                text-decoration: underline;
            }
        </style>
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <?
        APP::Render('admin/widgets/header', 'include', [
            'Анализ по % открытия' => 'admin/analytics/open/letter/pct'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Анализ по % открытия <div class="pull-right mar-rgt">средняя температура по больнице &mdash; <?= $data['avg'] ?>%</div></h2>
                        </div>
                        
                        <div class="card-body card-padding">
                            <div id="bar-chart" class="flot-chart" style="height:350px"></div>
                        </div>
                    </div>
                    <div class="card">
                        <table class="table table-striped">
                            <tbody>
                                <?

                                foreach ($data['pct'] as $key => $value) {
                                    if ($value) {
                                        ?>
                                        <tr>
                                            <td width="10%"><?= $key ?>%</td>
                                            <td><a class="alink" target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?php echo $data['url'][$key]; ?>" ><?= $value ?></a></td>
                                        </tr>
                                        <?
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            <? APP::Render('admin/widgets/footer') ?>
        </section>

        <? APP::Render('core/widgets/page_loader') ?>
        <? APP::Render('core/widgets/ie_warning') ?>

        <!-- Javascript Libraries -->
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/jquery/dist/jquery.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/Waves/dist/waves.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/json/dist/jquery.json.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.updated.min.js"></script>
                
        <? APP::Render('core/widgets/js') ?>
        
        <?
        APP::$insert['js_flot_tooltip'] = ['js', 'file', 'before', '</body>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/flot.tooltip/js/jquery.flot.tooltip.min.js'];
        APP::$insert['js_flot_resize'] = ['js', 'file', 'before', '</body>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/flot/jquery.flot.resize.js'];
        ob_start();
        ?>
         <script type="text/javascript">
            $(function(){
                var barData = new Array();
                <? foreach ($data['pct'] as $key => $value) { ?>
                      barData.push({
                        data : [[<?= $key ?>, <?= $value ?>]],
                        label: '<?= $key ?>%',
                        bars : {
                            show : true,
                            barWidth : 1,
                            order : 1,
                            lineWidth: 0,
                            fillColor: '#8BC34A'
                        }
                    });
                <? } ?>
                
                if ($('#bar-chart')[0]) {
                    $.plot($("#bar-chart"), barData, {
                        grid : {
                                borderWidth: 1,
                                borderColor: '#eee',
                                show : true,
                                hoverable : true,
                                clickable : true
                        },

                        yaxis: {
                            tickColor: '#eee',
                            tickDecimals: 0,
                            font :{
                                lineHeight: 13,
                                style: "normal",
                                color: "#9f9f9f",
                            },
                            shadowSize: 0
                        },

                        xaxis: {
                            tickColor: '#fff',
                            tickDecimals: 0,
                            font :{
                                lineHeight: 13,
                                style: "normal",
                                color: "#9f9f9f"
                            },
                            shadowSize: 0,
                        },

                        legend:{
                            container: '.flc-bar',
                            backgroundOpacity: 0.5,
                            noColumns: 0,
                            backgroundColor: "white",
                            lineWidth: 0
                        }
                    });
                }
                
                if ($(".flot-chart")[0]) {
                    $(".flot-chart").bind("plothover", function (event, pos, item) {
                        if (item) {
                            var x = item.datapoint[0],
                                y = item.datapoint[1];
                            $(".flot-tooltip").html(item.series.label + " от 100% = " + y).css({top: item.pageY+5, left: item.pageX+5}).show();
                        }
                        else {
                            $(".flot-tooltip").hide();
                        }
                    });

                    $("<div class='flot-tooltip' class='chart-tooltip'></div>").appendTo("body");
                }
            });
        </script>
        <?
        APP::$insert['js_opentime'] = ['js', 'code', 'before', '</body>', ob_get_contents()];
        ob_end_clean(); ?>
    </body>
</html>