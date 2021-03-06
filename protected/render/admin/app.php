<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PHP-shell - Application</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">        
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <? 
        APP::Render('admin/widgets/header', 'include', [
            'Application' => 'admin/app'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Application</h2>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tbody>
                                    <?
                                    foreach ([
                                        'Base URL'      => '<a href="' . APP::$conf['location'][0] . '://' . APP::$conf['location'][1] . APP::$conf['location'][2] . '" target="_blank" class="btn palette-Teal bg btn-xs waves-effect"><i class="zmdi zmdi-open-in-new"></i> ' . APP::$conf['location'][0] . '://' . APP::$conf['location'][1] . APP::$conf['location'][2] . '</a>',
                                        'Encoding'      => APP::$conf['encoding'],
                                        'Locale'        => APP::$conf['locale'],
                                        'Timezone'      => APP::$conf['timezone'],
                                        'Memory limit'  => APP::$conf['memory_limit'],
                                        'Debug mode'    => APP::$conf['debug'] ? 'On': 'Off',
                                        'Logs'          => APP::$conf['logs'],
                                        'Date'          => date('r')
                                    ] as $key => $value) {
                                        ?>
                                        <tr>
                                            <td width="20%"><?= $key ?></td>
                                            <td width="80%"><?= $value ?></td>
                                        </tr>
                                        <?
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

        <? APP::Render('core/widgets/js') ?>
    </body>
  </html>