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
        </style>
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <?
        APP::Render('admin/widgets/header', 'include', [
            'Letter Pct' => 'admin/analytics/open/letter/pct'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Анализ по % открытия <div class="pull-right mar-rgt">средняя температура по больнице &mdash; <?= $data['avg'] ?>%</div>Анализ по % открытия</h2>
                        </div>
                        
                        <div class="card-body card-padding">
                            <div id="demo-morris-bar" style="height:350px"></div>
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
        
        <? APP::Render('core/widgets/js') ?>
                
        <!-- OPTIONAL -->
        <script>
            $(document).ready(function() {
                Morris.Bar({
                    element: 'demo-morris-bar',
                    data: [<? foreach ($data['pct'] as $key => $value) { ?>{ y: '<?= $key ?>%', a: <?= $value ?>},<? } ?>],
                    xkey: 'y',
                    ykeys: 'a',
                    labels: ['Кол-во пользователей'],
                    gridEnabled: false,
                    gridLineColor: 'transparent',
                    barColors: ['#177bbb'],
                    resize: true,
                    hideHover: 'auto'
                });
            });
        </script>
    </body>
</html>