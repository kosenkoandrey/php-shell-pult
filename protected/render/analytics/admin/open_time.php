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
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/morris-js/morris.min.css" rel="stylesheet">

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

        </style>
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <?
        APP::Render('admin/widgets/header', 'include', [
            'Letter Open Time' => 'admin/analytics/open/letter/time'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Анализ по времени открытия</h2>
                        </div>
                        
                        <div class="card-body card-padding">
                            <table class="table table-striped" style="margin-bottom:30px;">
                                <tbody>
                                    <tr>
                                        <td class="time" data-sort="<?php echo $data['sort_time']; ?>">Время <i class="fa fa-sort-amount-<?php echo $data['sort_time']; ?>" style="width:16px;height:16px;" aria-hidden="true"></i></td>
                                        <td>Количество</td>
                                    </tr>
                                    <?php foreach ($data['data'] as $value) { ?>
                                        <tr>
                                            <td width="10%"><?= $value['time'] ?></td>
                                            <td><a class="alink" target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= $value['filter'] ?>" ><?= $value['count'] ?></a></td>

                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <div id="demo-flot-donut" style="height:800px;"></div>
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
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.updated.min.js"></script>
        
        <script src="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/morris-js/morris.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/morris-js/raphael-js/raphael.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/sparkline/jquery.sparkline.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/flot-charts/jquery.flot.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/flot-charts/jquery.flot.resize.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/flot-charts/jquery.flot.pie.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/gauge-js/gauge.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/easy-pie-chart/jquery.easypiechart.min.js"></script>
        
        <? APP::Render('core/widgets/js') ?>
                
        <!-- OPTIONAL -->
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

                $.plot('#demo-flot-donut', dataSet, {
                    series: {
                        pie: {
                            show: true,
                            label: {
                                show: true,
                                radius: 1
                            },
                            innerRadius: 0.3
                        }
                    },
                    legend: {
                        show: true,
                        labelFormatter: legendFormatter
                    },
                    grid: {
                        hoverable: true
                    }
                });

                $(document).on('mouseover', '.legend .legendLabel span', function(e){
                    var id = $(this).data('id');
                    $('.pieLabel div').hide();
                    $('.pieLabel').find('span[data-id="'+id+'"]').parent().show();       
                });

                $(document).on('mouseout', '.legend .legendLabel span', function(e){
                    $('.pieLabel div').show();
                });

                function legendFormatter(label, series) {
                    // series is the series object for the label
                    return '<span data-id="#pieLabel'+parseInt(label)+'" style="margin-left:5px;font-size:14px;">' + label + '</span>';
                }
            });
        </script>
    </body>
</html>