<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Анализ по времени открытия</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">

        <style>
            .alink:hover {
                color: #4089CE;
                text-decoration: underline;
            }
            .alink {
                color: #044582;
                text-decoration: underline;
            }

            .legend tr{
                cursor: pointer;
            }
            
            .time{
                cursor: pointer;
                width: 150px;
            }
            
            .time i{
                font-size: 18px;
                float: left;
                margin-right: 10px;
                vertical-align: bottom;
            }

        </style>
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <?
        APP::Render('admin/widgets/header', 'include', [
            'Анализ по времени открытия' => 'admin/analytics/open/letter/time'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h2>Анализ по времени открытия</h2>
                            </div>

                            <div class="card-body">
                                <table class="table" style="margin-bottom:30px;">
                                    <thead>
                                        <tr>
                                            <td class="time" data-sort="<?php echo $data['sort_time']; ?>"><i class="zmdi zmdi-sort-amount-<?php echo $data['sort_time']; ?>" aria-hidden="true"></i><div style="width:120px;"> Время</div></td>
                                            <td>Количество</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['data'] as $value) { ?>
                                            <tr>
                                                <td width="10%"><?= $value['time'] ?></td>
                                                <td><a class="alink" target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= $value['filter'] ?>" ><?= $value['count'] ?></a></td>

                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                        <div class="card-header">
                            <h2>График</h2>
                        </div>
                        <div class="card-body card-padding">
                            <div id="pie-chart" class="flot-chart-pie" ></div>
                            <div class="flc-pie hidden-xs"></div>
                            <form id="form-sort" method="post">
                                <input name="rules" value='<?php echo $data['rules']; ?>' type="hidden" />
                                <input name="sort_time" value="<?php echo $data['sort_time']; ?>" type="hidden" />
                            </form>
                        </div>
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
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootstrap-growl/bootstrap-growl.min.js"></script>
 
        
        <? APP::Render('core/widgets/js') ?>
                       
        <?
        APP::$insert['js_flot_pie'] = ['js', 'file', 'before', '</body>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/flot/jquery.flot.pie.js'];
        APP::$insert['js_flot_tooltip'] = ['js', 'file', 'before', '</body>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/flot.tooltip/js/jquery.flot.tooltip.min.js'];
        APP::$insert['js_flot_resize'] = ['js', 'file', 'before', '</body>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/flot/jquery.flot.resize.js'];

        ob_start();
        ?>
         <script type="text/javascript">
            $(function(){
                $(document).on('click', '.time', function(e){
                    var sort = $(this).data('sort');
                    if(sort == 'asc'){
                        $('input[name="sort_time"]', $('#form-sort')).val('desc');
                    }else{
                        $('input[name="sort_time"]', $('#form-sort')).val('asc');
                    }

                    $('#form-sort').submit();
                });

                var dataSet = <?php echo $data['chart']; ?>;
                
                if($('#pie-chart')[0]){
                    $.plot('#pie-chart', dataSet, {
                        series: {
                            pie: {
                                show: true,
                                stroke: { 
                                    width: 1,
                                },
                            },
                        },
                        legend: {
                            container: '.flc-pie',
                            backgroundOpacity: 0.5,
                            noColumns: 0,
                            backgroundColor: "white",
                            lineWidth: 0
                        },
                        grid: {
                            hoverable: true,
                            clickable: true
                        },
                        tooltip: true,
                        tooltipOpts: {
                            content: "%p.0%, %s", // show percentages, rounding to 2 decimal places
                            shifts: {
                                x: 20,
                                y: 0
                            },
                            defaultTheme: false,
                            cssClass: 'flot-tooltip'
                        }

                    });
                }
            });
        </script>
        <?
        APP::$insert['js_opentime'] = ['js', 'code', 'before', '</body>', ob_get_contents()];
        ob_end_clean(); ?>
    </body>
</html>