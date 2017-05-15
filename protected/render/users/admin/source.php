<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Анализ источников</title>

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
            #table .country-btn{
                    cursor: pointer;
                    text-decoration: underline;
            }
            
            #groups-tree button{
                box-shadow: none;
            }
        </style>
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <?
        APP::Render('admin/widgets/header', 'include', [
            'Анализ источников' => 'admin/users/source'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Анализ источников</h2>
                        </div>
                        <div class="card-body">
                            <table data-target="#table" id="table" class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Источник</th>
                                        <th>Количество</th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<?php foreach($data['source'] as $item){ ?>
                                		<tr>
	                                        <th><?= $item['value']; ?></th>
	                                        <th><a href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= $item['filter']; ?>" target="_blank"><?= $item['count']; ?></a></th>
	                                    </tr>
                                	<?php } ?>
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
	    <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootstrap-growl/bootstrap-growl.min.js"></script>
	    <script src="https://api-maps.yandex.ru/2.1/?load=package.full&lang=ru-RU" type="text/javascript"></script>

	    <? APP::Render('core/widgets/js') ?>
	    <script>
	        
	    </script>
    </body>
</html>                       