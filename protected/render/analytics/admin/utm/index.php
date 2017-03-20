<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>UTM-анализ</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">
        <style>
            #utm-list .item {
                font-size: 15px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            #utm-list .item > .control {
                margin-bottom: 10px;
            }
            #utm-list .item > .control > i {
                margin-right: 8px;
                cursor: pointer;
            }
            #utm-list .item > .control > i:hover {
                color: #2e6da4;
            }
            #utm-list .item > .control > span {
                cursor: pointer;
            }
            #utm-list .item > .control > span:hover {
                color: #2e6da4;
            }
            
            .utm-source {
                margin-left: 0px;
            }
            .utm-medium,
            .utm-campaign,
            .utm-term,
            .utm-content {
                margin-left: 20px;
            }
        </style>
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <?
        APP::Render('admin/widgets/header', 'include', [
            'UTM-анализ' => 'admin/analytics/utm'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h2>UTM-метки</h2>
                            </div>
                            <div class="card-body card-padding">
                                <div id="utm-list"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header">
                                <h2>Пользователи</h2>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_ref_all"><?= number_format($data['ref']['all'], 0, ' ', ' ') ?></span>
                                                <p>Всего</p>
                                                <p>100%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_ref_active"><?= number_format($data['ref']['active'], 0, ' ', ' ') ?></span>
                                                <p>Активированных</p>
                                                <p id="value_ref_active_pct"><?= round($data['ref']['active'] / ($data['ref']['all'] / 100), 2)  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_ref_wait"><?= number_format($data['ref']['wait'], 0, ' ', ' ') ?></span>
                                                <p>Ожидают акт.</p>
                                                <p id="value_ref_wait_pct"><?= round($data['ref']['wait'] / ($data['ref']['all'] / 100), 2)  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_ref_inactive"><?= number_format($data['ref']['inactive'], 0, ' ', ' ') ?></span>
                                                <p>Неактивных</p>
                                                <p id="value_ref_inactive_pct"><?= round($data['ref']['inactive'] / ($data['ref']['all'] / 100), 2)  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_ref_unsubscribe"><?= number_format($data['ref']['unsubscribe'], 0, ' ', ' ') ?></span>
                                                <p>Отписались</p>
                                                <p id="value_ref_unsubscribe_pct"><?= round($data['ref']['unsubscribe'] / ($data['ref']['all'] / 100), 2)  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h2>Письма</h2>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_letters_all"><?= number_format($data['letters']['all'], 0, ' ', ' ') ?></span>
                                                <p>Всего</p>
                                                <p>100%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_letters_open"><?= number_format($data['letters']['open']['value'], 0, ' ', ' ') ?></span>
                                                <p>Открыто</p>
                                                <p id="value_letters_open_pct"><?= $data['letters']['open']['pct']  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_letters_click"><?= number_format($data['letters']['click']['value'], 0, ' ', ' ') ?></span>
                                                <p>Клики</p>
                                                <p id="value_letters_click_pct"><?= $data['letters']['click']['pct']  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_letters_bounce"><?= number_format($data['letters']['bounce']['value'], 0, ' ', ' ') ?></span>
                                                <p>Bounce</p>
                                                <p id="value_letters_bounce_pct"><?= $data['letters']['bounce']['pct']  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_letters_spam"><?= number_format($data['letters']['spamreport']['value'], 0, ' ', ' ') ?></span>
                                                <p>СПАМ</p>
                                                <p id="value_letters_spam_pct"><?= $data['letters']['spamreport']['pct']  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value ">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_letters_unsubscribe"><?= number_format($data['letters']['unsubscribe']['value'], 0, ' ', ' ') ?></span>
                                                <p>Отписались</p>
                                                <p id="value_letters_unsubscribe_pct"><?= $data['letters']['unsubscribe']['pct']  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h2>Заказы</h2>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value link" data-state="total">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_orders_all"><?= number_format($data['orders']['all'], 0, ' ', ' ') ?></span>
                                                <p>Всего</p>
                                                <p>100%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value link" data-state="success">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_orders_success"><?= $data['orders']['success']['value'] ?></span>
                                                <p>Оплаченные</p>
                                                <p id="value_orders_success_pct"><?= $data['orders']['success']['pct']  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value link" data-state="processed">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_orders_processed"><?= $data['orders']['processed']['value'] ?></span>
                                                <p>В обработке</p>
                                                <p id="value_orders_processed_pct"><?= $data['orders']['processed']['pct']  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value link" data-state="new">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_orders_new"><?= $data['orders']['new']['value'] ?></span>
                                                <p>Неоплаченные</p>
                                                <p id="value_orders_new_pct"><?= $data['orders']['new']['pct']  ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_orders_total"><?= $data['orders']['total'] ?></span>
                                                <p>руб.</p>
                                                <p>Оплачено</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-md-3 col-lg-2">
                                        <div class="value">
                                            <div class="pad-all text-center">
                                                <span class="f-20" id="value_orders_avg"><?= $data['orders']['avg'] ?></span>
                                                <p>руб.</p>
                                                <p>Средний чек</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

        <? APP::Render('core/widgets/js') ?>
        <script>
            function GetLabels(label, value, item) {
                //$('#page-content').niftyOverlay('show');
                
                $.ajax({
                    type: 'post',
                    url: '<?= APP::Module('Routing')->root ?>admin/analytics/utm',
                    data: {
                        api: 'labels',
                        settings: {
                            label: label,
                            value: value
                        },
                        rules: '<?= json_encode($data['rules']) ?>'
                    },
                    success: function(res) {
                        switch(label) {
                            case 'root':
                                $('#utm-list').append('<div class="utm-source"></div>');
                                
                                $.each(res, function(source_index, source_value) {
                                    var utm_value = source_value ? source_value : '<Не определено>';
                                    $('#utm-list > .utm-source').append('<div class="item source" data-state="inactive" id="' + source_index + '"><div class="control"><i class="zmdi zmdi-plus-square"></i><span data-value="' + source_value + '">' + utm_value + '</span></div></div>');
                                });
                                break;
                            case 'source':
                                $('#' + item + ' > .utm-medium').empty();
                                
                                $.each(res, function(medium_index, medium_value) {
                                    var utm_value = medium_value ? medium_value : '<Не определено>';
                                    $('#' + item + ' > .utm-medium').append('<div class="item medium" data-state="inactive" id="' + medium_index + '"><div class="control"><i class="zmdi zmdi-plus-square"></i><span data-value="' + medium_value + '">' + utm_value + '</span></div></div>');
                                });
                                break;
                            case 'medium':
                                $('#' + item + ' > .utm-campaign').empty();
                                
                                $.each(res, function(campaign_index, campaign_value) {
                                    var utm_value = campaign_value ? campaign_value : '<Не определено>';
                                    $('#' + item + ' > .utm-campaign').append('<div class="item campaign" data-state="inactive" id="' + campaign_index + '"><div class="control"><i class="zmdi zmdi-plus-square"></i><span data-value="' + campaign_value + '">' + utm_value + '</span></div></div>');
                                });
                                break;
                            case 'campaign':
                                $('#' + item + ' > .utm-term').empty();
                                
                                $.each(res, function(term_index, term_value) {
                                    var utm_value = term_value ? term_value : '<Не определено>';
                                    $('#' + item + ' > .utm-term').append('<div class="item term" data-state="inactive" id="' + term_index + '"><div class="control"><i class="zmdi zmdi-plus-square"></i><span data-value="' + term_value + '">' + utm_value + '</span></div></div>');
                                });
                                break;
                            case 'term':
                                $('#' + item + ' > .utm-content').empty();
                                
                                $.each(res, function(content_index, content_value) {
                                    var utm_value = content_value ? content_value : '<Не определено>';
                                    $('#' + item + ' > .utm-content').append('<div class="item content" data-state="inactive" id="' + content_index + '"><div class="control"><i class="zmdi fa-angle-right"></i><span data-value="' + content_value + '">' + utm_value + '</span></div></div>');
                                });
                                break;
                        }

                        //$('#page-content').niftyOverlay('hide');
                    }
                });
            }
            
            function GetHealth(label, value) {
                //$('#page-content').niftyOverlay('show');
                
                $('#value_ref_all').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_ref_active').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_ref_active_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_ref_wait').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_ref_wait_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_ref_inactive').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_ref_inactive_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_ref_unsubscribe').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_ref_unsubscribe_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');

                $('#value_letters_all').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_open').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_open_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_click').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_click_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_bounce').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_bounce_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_spam').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_spam_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_unsubscribe').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_unsubscribe_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');

                $('#value_letters_all30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_open30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_open_pct30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_click30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_click_pct30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_bounce30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_bounce_pct30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_spam30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_spam_pct30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_unsubscribe30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_letters_unsubscribe_pct30').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');

                $('#value_orders_all').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_orders_success').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_orders_success_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_orders_processed').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_orders_processed_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_orders_new').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_orders_new_pct').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_orders_total').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                $('#value_orders_avg').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                
                $.ajax({
                    type: 'post',
                    url: '<?= APP::Module('Routing')->root ?>admin/analytics/utm',
                    data: {
                        api: 'health',
                        settings: {
                            label: label,
                            value: value
                        },
                        rules: '<?= json_encode($data["rules"]) ?>'
                    },
                    success: function(res) {
                        $('#value_ref_all').html(res.ref.all);
                        $('#value_ref_active').html(res.ref.active.value);
                        $('#value_ref_active_pct').html(res.ref.active.pct + '%');
                        $('#value_ref_wait').html(res.ref.wait.value);
                        $('#value_ref_wait_pct').html(res.ref.wait.pct + '%');
                        $('#value_ref_inactive').html(res.ref.inactive.value);
                        $('#value_ref_inactive_pct').html(res.ref.inactive.pct + '%');
                        $('#value_ref_unsubscribe').html(res.ref.unsubscribe.value);
                        $('#value_ref_unsubscribe_pct').html(res.ref.unsubscribe.pct + '%');
                        
                        $('#value_letters_all').html(res.letters.all);
                        $('#value_letters_open').html(res.letters.open.value);
                        $('#value_letters_open_pct').html(res.letters.open.pct + '%');
                        $('#value_letters_click').html(res.letters.click.value);
                        $('#value_letters_click_pct').html(res.letters.click.pct + '%');
                        $('#value_letters_bounce').html(res.letters.bounce.value);
                        $('#value_letters_bounce_pct').html(res.letters.bounce.pct + '%');
                        $('#value_letters_spam').html(res.letters.spamreport.value);
                        $('#value_letters_spam_pct').html(res.letters.spamreport.pct + '%');
                        $('#value_letters_unsubscribe').html(res.letters.unsubscribe.value);
                        $('#value_letters_unsubscribe_pct').html(res.letters.unsubscribe.pct + '%');

                        $('#value_letters_all30').html(res.letters.all30);
                        $('#value_letters_open30').html(res.letters.open30.value);
                        $('#value_letters_open_pct30').html(res.letters.open30.pct + '%');
                        $('#value_letters_click30').html(res.letters.click30.value);
                        $('#value_letters_click_pct30').html(res.letters.click30.pct + '%');
                        $('#value_letters_bounce30').html(res.letters.bounce30.value);
                        $('#value_letters_bounce_pct30').html(res.letters.bounce30.pct + '%');
                        $('#value_letters_spam30').html(res.letters.spamreport30.value);
                        $('#value_letters_spam_pct30').html(res.letters.spamreport30.pct + '%');
                        $('#value_letters_unsubscribe30').html(res.letters.unsubscribe30.value);
                        $('#value_letters_unsubscribe_pct30').html(res.letters.unsubscribe30.pct + '%');
                        
                        $('#value_orders_all').html(res.orders.all);
                        $('#value_orders_success').html(res.orders.success.value);
                        $('#value_orders_success_pct').html(res.orders.success.pct + '%');
                        $('#value_orders_processed').html(res.orders.processed.value);
                        $('#value_orders_processed_pct').html(res.orders.processed.pct + '%');
                        $('#value_orders_new').html(res.orders.new.value);
                        $('#value_orders_new_pct').html(res.orders.new.pct + '%');
                        $('#value_orders_total').html(res.orders.total);
                        $('#value_orders_avg').html(res.orders.avg);

                        //$('#page-content').niftyOverlay('hide');
                    }
                });
            } 
            
            $(document).ready(function() {
                GetLabels('root', null, null);

                $(document).on('click', '#utm-list > .utm-source > .item  > .control > .zmdi', function () {
                    var hide = $(this).hasClass('zmdi-plus-square');
                    
                    if (hide) {
                        $(this).removeClass('zmdi-plus-square');
                        $(this).addClass('zmdi-minus-square');
                    } else {
                        $(this).removeClass('zmdi-minus-square');
                        $(this).addClass('zmdi-plus-square');
                    }
                    
                    var item = $(this).closest('.item.source');

                    switch(item.data('state')) {
                        case 'inactive':
                            var source_value = $('.control > span', item).data('value');
                            
                            item.append('<div class="utm-medium"><div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div> Загрузка "medium" меток...</div>');
                            item.data('state','active');

                            GetLabels('source', source_value, item.attr('id'));
                            break;
                        case 'active':
                            $('.utm-medium', item).slideToggle(300);
                            break;
                    }
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .control > .zmdi', function () {
                    var hide = $(this).hasClass('zmdi-plus-square');
                    
                    if (hide) {
                        $(this).removeClass('zmdi-plus-square');
                        $(this).addClass('zmdi-minus-square');
                    } else {
                        $(this).removeClass('zmdi-minus-square');
                        $(this).addClass('zmdi-plus-square');
                    }
                    
                    var item_medium = $(this).closest('.item.medium');
                    var item_source = $(this).closest('.item.source');

                    switch(item_medium.data('state')) {
                        case 'inactive':
                            var medium_value = $('.control > span', item_medium).data('value');
                            var source_value = $('.control > span', item_source).data('value');
                            
                            item_medium.append('<div class="utm-campaign"><div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div> Загрузка "campaign" меток...</div>');
                            item_medium.data('state','active');

                            GetLabels(
                                'medium', 
                                {
                                    source: source_value,
                                    medium: medium_value
                                }, 
                                item_medium.attr('id')
                            );
                            break;
                        case 'active':
                            $('.utm-campaign', item_medium).slideToggle(300);
                            break;
                    }
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .utm-campaign > .item > .control > .zmdi', function () {
                    var hide = $(this).hasClass('zmdi-plus-square');
                    
                    if (hide) {
                        $(this).removeClass('zmdi-plus-square');
                        $(this).addClass('zmdi-minus-square');
                    } else {
                        $(this).removeClass('zmdi-minus-square');
                        $(this).addClass('zmdi-plus-square');
                    }
                    
                    var item_campaign = $(this).closest('.item.campaign');
                    var item_medium = $(this).closest('.item.medium');
                    var item_source = $(this).closest('.item.source');

                    switch(item_campaign.data('state')) {
                        case 'inactive':
                            var campaign_value = $('.control > span', item_campaign).data('value');
                            var medium_value = $('.control > span', item_medium).data('value');
                            var source_value = $('.control > span', item_source).data('value');
                            
                            item_campaign.append('<div class="utm-term"><div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div> Загрузка "term" меток...</div>');
                            item_campaign.data('state','active');

                            GetLabels(
                                'campaign', 
                                {
                                    source: source_value,
                                    medium: medium_value,
                                    campaign: campaign_value
                                }, 
                                item_campaign.attr('id')
                            );
                            break;
                        case 'active':
                            $('.utm-term', item_campaign).slideToggle(300);
                            break;
                    }
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .utm-campaign > .item > .utm-term > .item > .control > .zmdi', function () {
                    var hide = $(this).hasClass('zmdi-plus-square');
                    
                    if (hide) {
                        $(this).removeClass('zmdi-plus-square');
                        $(this).addClass('zmdi-minus-square');
                    } else {
                        $(this).removeClass('zmdi-minus-square');
                        $(this).addClass('zmdi-plus-square');
                    }
                    
                    var item_term = $(this).closest('.item.term');
                    var item_campaign = $(this).closest('.item.campaign');
                    var item_medium = $(this).closest('.item.medium');
                    var item_source = $(this).closest('.item.source');

                    switch(item_term.data('state')) {
                        case 'inactive':
                            var term_value = $('.control > span', item_term).data('value');
                            var campaign_value = $('.control > span', item_campaign).data('value');
                            var medium_value = $('.control > span', item_medium).data('value');
                            var source_value = $('.control > span', item_source).data('value');
                            
                            item_term.append('<div class="utm-content"><div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div> Загрузка "content" меток...</div>');
                            item_term.data('state','active');

                            GetLabels(
                                'term', 
                                {
                                    source: source_value,
                                    medium: medium_value,
                                    campaign: campaign_value,
                                    term: term_value
                                }, 
                                item_term.attr('id')
                            );
                            break;
                        case 'active':
                            $('.utm-content', item_term).slideToggle(300);
                            break;
                    }
                });

                
                $(document).on('click', '#utm-list > .utm-source > .item  > .control > span', function () {
                    var source_item = $(this).closest('.item.source');
                    var source_value = $('.control > span', source_item).data('value');
                    
                    GetHealth('source', source_value);
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .control > span', function () {
                    var source_item = $(this).closest('.item.source');
                    var medium_item = $(this).closest('.item.medium');
                    
                    var source_value = $('.control > span', source_item).data('value');
                    var medium_value = $('.control > span', medium_item).data('value');
                    
                    GetHealth(
                        'medium', 
                        {
                            source: source_value,
                            medium: medium_value
                        }
                    );
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .utm-campaign > .item > .control > span', function () {
                    var source_item = $(this).closest('.item.source');
                    var medium_item = $(this).closest('.item.medium');
                    var campaign_item = $(this).closest('.item.campaign');
                    
                    var source_value = $('.control > span', source_item).data('value');
                    var medium_value = $('.control > span', medium_item).data('value');
                    var campaign_value = $('.control > span', campaign_item).data('value');
                    
                    GetHealth(
                        'campaign', 
                        {
                            source: source_value,
                            medium: medium_value,
                            campaign: campaign_value
                        }
                    );
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .utm-campaign > .item > .utm-term > .item > .control > span', function () {
                    var source_item = $(this).closest('.item.source');
                    var medium_item = $(this).closest('.item.medium');
                    var campaign_item = $(this).closest('.item.campaign');
                    var term_item = $(this).closest('.item.term');
                    
                    var source_value = $('.control > span', source_item).data('value');
                    var medium_value = $('.control > span', medium_item).data('value');
                    var campaign_value = $('.control > span', campaign_item).data('value');
                    var term_value = $('.control > span', term_item).data('value');
                    
                    GetHealth(
                        'term', 
                        {
                            source: source_value,
                            medium: medium_value,
                            campaign: campaign_value,
                            term: term_value
                        }
                    );
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .utm-campaign > .item > .utm-term > .item > .utm-content > .item > .control > span', function () {
                    var source_item = $(this).closest('.item.source');
                    var medium_item = $(this).closest('.item.medium');
                    var campaign_item = $(this).closest('.item.campaign');
                    var term_item = $(this).closest('.item.term');
                    var content_item = $(this).closest('.item.content');
                    
                    var source_value = $('.control > span', source_item).data('value');
                    var medium_value = $('.control > span', medium_item).data('value');
                    var campaign_value = $('.control > span', campaign_item).data('value');
                    var term_value = $('.control > span', term_item).data('value');
                    var content_value = $('.control > span', content_item).data('value');
                    
                    GetHealth(
                        'content', 
                        {
                            source: source_value,
                            medium: medium_value,
                            campaign: campaign_value,
                            term: term_value,
                            content: content_value
                        }
                    );
                });
            });
        </script>
    </body>
</html>