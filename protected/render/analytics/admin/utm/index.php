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
                    <div class="row">
                        <div class="col-xs-3">
                            <div class="card">
                                <div class="card-header">
                                    <h2>UTM-метки</h2>
                                </div>
                                <div class="card-body card-padding">
                                    <div id="utm-list"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-9">
                            <div class="card">
                                <div class="card-header">
                                    <h2>Пользователи</h2>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_users_all"><?= number_format($data['users']['all'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Всего</p>
                                                    <p>100%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_users_active"><?= number_format($data['users']['active'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Активные</p>
                                                    <p id="value_users_active_pct" class="dyn_val"><?= round($data['users']['active'] / ($data['users']['all'] / 100), 2)  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_users_inactive"><?= number_format($data['users']['inactive'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Ожидают активации</p>
                                                    <p id="value_users_inactive_pct" class="dyn_val"><?= round($data['users']['inactive'] / ($data['users']['all'] / 100), 2)  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_users_pause"><?= number_format($data['users']['pause'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Временно отписанные</p>
                                                    <p id="value_users_pause_pct" class="dyn_val"><?= round($data['users']['pause'] / ($data['users']['all'] / 100), 2)  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_users_unsubscribe"><?= number_format($data['users']['unsubscribe'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Отписанные</p>
                                                    <p id="value_users_unsubscribe_pct" class="dyn_val"><?= round($data['users']['unsubscribe'] / ($data['users']['all'] / 100), 2)  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_users_blacklist"><?= number_format($data['users']['blacklist'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">В блэк-листе</p>
                                                    <p id="value_users_blacklist_pct" class="dyn_val"><?= round($data['users']['blacklist'] / ($data['users']['all'] / 100), 2)  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_users_dropped"><?= number_format($data['users']['dropped'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Дропнутые</p>
                                                    <p id="value_users_dropped_pct" class="dyn_val"><?= round($data['users']['dropped'] / ($data['users']['all'] / 100), 2)  ?>%</p>
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
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_letters_all"><?= number_format($data['letters']['all'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Всего</p>
                                                    <p>100%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_letters_open"><?= number_format($data['letters']['open']['value'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Открыли</p>
                                                    <p id="value_letters_open_pct" class="dyn_val"><?= $data['letters']['open']['pct']  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_letters_click"><?= number_format($data['letters']['click']['value'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Кликнули</p>
                                                    <p id="value_letters_click_pct" class="dyn_val"><?= $data['letters']['click']['pct']  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_letters_bounce"><?= number_format($data['letters']['bounce']['value'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Bounce</p>
                                                    <p id="value_letters_bounce_pct" class="dyn_val"><?= $data['letters']['bounce']['pct']  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_letters_unsubscribe"><?= number_format($data['letters']['unsubscribe']['value'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Отписались</p>
                                                    <p id="value_letters_unsubscribe_pct" class="dyn_val"><?= $data['letters']['unsubscribe']['pct']  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value ">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_letters_spamreport"><?= number_format($data['letters']['spamreport']['value'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">СПАМ</p>
                                                    <p id="value_letters_spamreport_pct" class="dyn_val"><?= $data['letters']['spamreport']['pct']  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h2>Счета</h2>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <div class="value link" data-state="total">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_invoices_all"><?= number_format($data['invoices']['all'], 0, ' ', ' ') ?></span>
                                                    <p class="m-0">Всего</p>
                                                    <p>100%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value link" data-state="new">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_invoices_new"><?= $data['invoices']['new']['value'] ?></span>
                                                    <p class="m-0">Новые</p>
                                                    <p id="value_invoices_new_pct" class="dyn_val"><?= $data['invoices']['new']['pct']  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value link" data-state="processed">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_invoices_processed"><?= $data['invoices']['processed']['value'] ?></span>
                                                    <p class="m-0">В обработке</p>
                                                    <p id="value_invoices_processed_pct" class="dyn_val"><?= $data['invoices']['processed']['pct']  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value link" data-state="success">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_invoices_success"><?= $data['invoices']['success']['value'] ?></span>
                                                    <p class="m-0">Оплаченные</p>
                                                    <p id="value_invoices_success_pct" class="dyn_val"><?= $data['invoices']['success']['pct']  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-3">
                                            <div class="value link" data-state="revoked">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_invoices_revoked"><?= $data['invoices']['revoked']['value'] ?></span>
                                                    <p class="m-0">Аннулированные</p>
                                                    <p id="value_invoices_revoked_pct" class="dyn_val"><?= $data['invoices']['revoked']['pct']  ?>%</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_invoices_total"><?= $data['invoices']['total'] ?></span>
                                                    <p class="m-0">руб.</p>
                                                    <p>Оплачено</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="value">
                                                <div class="pad-all text-center">
                                                    <span class="f-20 dyn_val" id="value_invoices_avg"><?= $data['invoices']['avg'] ?></span>
                                                    <p class="m-0">руб.</p>
                                                    <p>Средний чек</p>
                                                </div>
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
                $('.dyn_val').html('<div class="preloader pl-xs"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div>');
                
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
                    success: function(data) {
                        $('#value_users_all').html(data.users.all);
                        $('#value_users_active').html(data.users.active);
                        $('#value_users_active_pct').html(parseFloat(data.users.active / (data.users.all / 100)).toFixed(2) + '%');
                        $('#value_users_inactive').html(data.users.inactive);
                        $('#value_users_inactive_pct').html(parseFloat(data.users.inactive / (data.users.all / 100)).toFixed(2) + '%');
                        $('#value_users_pause').html(data.users.pause);
                        $('#value_users_pause_pct').html(parseFloat(data.users.pause / (data.users.all / 100)).toFixed(2) + '%');
                        $('#value_users_unsubscribe').html(data.users.unsubscribe);
                        $('#value_users_unsubscribe_pct').html(parseFloat(data.users.unsubscribe / (data.users.all / 100)).toFixed(2) + '%');
                        $('#value_users_blacklist').html(data.users.blacklist);
                        $('#value_users_blacklist_pct').html(parseFloat(data.users.blacklist / (data.users.all / 100)).toFixed(2) + '%');
                        $('#value_users_dropped').html(data.users.dropped);
                        $('#value_users_dropped_pct').html(parseFloat(data.users.dropped / (data.users.all / 100)).toFixed(2) + '%');

                        $('#value_letters_all').html(data.letters.all);
                        $('#value_letters_open').html(data.letters.open.value);
                        $('#value_letters_open_pct').html(data.letters.open.pct);
                        $('#value_letters_click').html(data.letters.click.value);
                        $('#value_letters_click_pct').html(data.letters.click.pct);
                        $('#value_letters_bounce').html(data.letters.bounce.value);
                        $('#value_letters_bounce_pct').html(data.letters.bounce.pct);
                        $('#value_letters_unsubscribe').html(data.letters.unsubscribe.value);
                        $('#value_letters_unsubscribe_pct').html(data.letters.unsubscribe.pct);
                        $('#value_letters_spamreport').html(data.letters.spamreport.value);
                        $('#value_letters_spamreport_pct').html(data.letters.spamreport.pct);

                        $('#value_invoices_all').html(data.invoices.all);
                        $('#value_invoices_new').html(data.invoices.new.value);
                        $('#value_invoices_new_pct').html(data.invoices.new.pct);
                        $('#value_invoices_processed').html(data.invoices.processed.value);
                        $('#value_invoices_processed_pct').html(data.invoices.processed.pct);
                        $('#value_invoices_success').html(data.invoices.success.value);
                        $('#value_invoices_success_pct').html(data.invoices.success.pct);
                        $('#value_invoices_revoked').html(data.invoices.revoked.value);
                        $('#value_invoices_revoked_pct').html(data.invoices.revoked.pct);
                        $('#value_invoices_total').html(data.invoices.total);
                        $('#value_invoices_avg').html(data.invoices.avg);
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