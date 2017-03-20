<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Статистика по доменам получателей</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">        
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">

        <style>
            #domains-table-header .actionBar .actions > button {
                display: none;
            }
        </style>
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <? 
        APP::Render('admin/widgets/header', 'include', [
            'Почта' => 'admin/mail'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Статистика по доменам получателей</h2>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-vmiddle" id="domains-table">
                                <thead>
                                    <tr>
                                        <th data-column-id="domain" data-formatter="domain">Домен</th>
                                        <th data-column-id="inactive" data-formatter="inactive">Ожидают активации</th>
                                        <th data-column-id="active" data-formatter="active">Активные</th>
                                        <th data-column-id="pause" data-formatter="pause">Временно отписан</th>
                                        <th data-column-id="unsubscribe" data-formatter="unsubscribe">Отписанные</th>
                                        <th data-column-id="blacklist" data-formatter="blacklist">ЧС</th>
                                        <th data-column-id="dropped" data-formatter="dropped" data-order="desc">Дропнутые</th>
                                    </tr>
                                </thead>
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
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.updated.min.js"></script>

        <? APP::Render('core/widgets/js') ?>
        
        <script>
            $(document).ready(function() {
                $('#domains-table').bootgrid({
                    requestHandler: function (request) {
                        var model = {
                            search: $('.search-field').val(),
                            current: request.current,
                            rows: request.rowCount
                        };

                        for (var key in request.sort) {
                            model.sort_by = key;
                            model.sort_direction = request.sort[key];
                        }

                        return JSON.stringify(model);
                    },
                    ajaxSettings: {
                        method: 'POST',
                        cache: false,
                        contentType: 'application/json'
                    },
                    ajax: true,
                    url: '<?= APP::Module('Routing')->root ?>admin/mail/api/domains/list.json',
                    css: {
                        icon: 'zmdi icon',
                        iconColumns: 'zmdi-view-module',
                        iconDown: 'zmdi-chevron-down pull-left',
                        iconRefresh: 'zmdi-refresh',
                        iconUp: 'zmdi-chevron-up pull-left'
                    },
                    formatters: {
                        domain: function(column, row) {
                            return '<a href="<?= APP::Module('Routing')->root ?>admin/users?filters=' + row.domain[1] + '" target="_blank">' + row.domain[0] + '</a>';
                        },
                        inactive: function(column, row) {
                            return '<a href="<?= APP::Module('Routing')->root ?>admin/users?filters=' + row.inactive[1] + '" target="_blank">' + row.inactive[0] + '</a>';
                        },
                        active: function(column, row) {
                            return '<a href="<?= APP::Module('Routing')->root ?>admin/users?filters=' + row.active[1] + '" target="_blank">' + row.active[0] + '</a>';
                        },
                        pause: function(column, row) {
                            return '<a href="<?= APP::Module('Routing')->root ?>admin/users?filters=' + row.pause[1] + '" target="_blank">' + row.pause[0] + '</a>';
                        },
                        unsubscribe: function(column, row) {
                            return '<a href="<?= APP::Module('Routing')->root ?>admin/users?filters=' + row.unsubscribe[1] + '" target="_blank">' + row.unsubscribe[0] + '</a>';
                        },
                        blacklist: function(column, row) {
                            return '<a href="<?= APP::Module('Routing')->root ?>admin/users?filters=' + row.blacklist[1] + '" target="_blank">' + row.blacklist[0] + '</a>';
                        },
                        dropped: function(column, row) {
                            return '<a href="<?= APP::Module('Routing')->root ?>admin/users?filters=' + row.dropped[1] + '" target="_blank">' + row.dropped[0] + '</a>';
                        }
                    }
                });
            });
        </script>
    </body>
</html>