<?
$nav = [];
$nav_cnt = 0;

foreach ($data['path'] as $key => $value) {
    ++ $nav_cnt;
    $title = $key ? $value : 'Письма';
    
    if (count($data['path']) !== $nav_cnt) {
        $nav[$title] = 'admin/mail/letters/' . APP::Module('Crypt')->Encode($key);
    } else {
        $nav[$title] = mb_substr(APP::Module('Routing')->RequestURI(), 1);
    }
}
?>
<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Управление письмами</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">        
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet"> 

        <? APP::Render('core/widgets/css') ?>
        
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
            #utm-list .item > .control > label {
                display: inline-block;
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
            
            .letter-name {
                font-size: 16px;
                white-space: nowrap; /* Отменяем перенос текста */
                overflow: hidden; /* Обрезаем содержимое */
                position: relative; /* Относительное позиционирование */
            }
            .letter-name::after {
                content: ''; /* Выводим элемент */
                position: absolute; /* Абсолютное позиционирование */
                right: 0; top: 0; /* Положение элемента */
                width: 40px; /* Ширина градиента*/
                height: 100%; /* Высота родителя */
                /* Градиент */
                background: -moz-linear-gradient(left, rgba(255,204,0, 0.2), #fc0 100%);
                background: -webkit-linear-gradient(left, rgba(255, 255, 255, 0.2), #fff 100%);
                background: -o-linear-gradient(left, rgba(255,204,0, 0.2), #fc0 100%);
                background: -ms-linear-gradient(left, rgba(255,204,0, 0.2), #fc0 100%);
                background: linear-gradient(to right, rgba(255, 255, 255, 0.2), #fff 100%);
            }
        </style>
    </head>
    <body data-ma-header="teal">
        <? APP::Render('admin/widgets/header', 'include', $nav) ?>

        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Управление письмами</h2>
                            <ul class="actions">
                                <li class="dropdown">
                                    <a href="javascript:void(0)" data-toggle="dropdown">
                                        <i class="zmdi zmdi-more-vert"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/mail/letters/<?= $data['group_sub_id'] ? APP::Module('Crypt')->Encode($data['group_sub_id']) : 0 ?>/add">Добавить письмо</a></li>
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/mail/letters/<?= $data['group_sub_id'] ? APP::Module('Crypt')->Encode($data['group_sub_id']) : 0 ?>/groups/add">Добавить группу</a></li>
                                        <li><a href="#" data-target="#letters-stat-modal" data-toggle="modal">Фильтры статистики</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <?
                            if (count($data['list'])) {
                                ?>
                                <table class="table table-vmiddle" style="table-layout:fixed; width:100%;">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th style="width: 130px">Отправлено</th>
                                            <th style="width: 130px">Открыто</th>
                                            <th style="width: 130px">Кликнули</th>
                                            <th style="width: 130px">Отписались</th>
                                            <th style="width: 130px">СПАМ</th>
                                            <th style="width: 130px">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?
                                        foreach ($data['list'] as $item) {
                                            switch ($item[0]) {
                                                case 'group':
                                                    ?>
                                                    <tr>
                                                        <td style="font-size: 16px" colspan="6"><span style="display: inline-block" class="avatar-char palette-Teal bg m-r-5"><i class="zmdi zmdi-folder"></i></span> <a style="color: #4C4C4C" href="<?= APP::Module('Routing')->root ?>admin/mail/letters/<?= APP::Module('Crypt')->Encode($item[1]) ?>"><?= $item[2] ?></a></td>
                                                        <td>
                                                            <a href="<?= APP::Module('Routing')->root ?>admin/mail/letters/<?= $data['group_sub_id'] ? APP::Module('Crypt')->Encode($data['group_sub_id']) : 0 ?>/groups/<?= APP::Module('Crypt')->Encode($item[1]) ?>/edit" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-edit"></span></a>
                                                            <a href="javascript:void(0)" data-letter-group-id="<?= $item[1] ?>" class="btn btn-sm btn-default btn-icon waves-effect waves-circle remove-letter-group"><span class="zmdi zmdi-delete"></span></a>
                                                        </td>
                                                    </tr>
                                                    <?
                                                    break;
                                                case 'letter':
                                                    ?>
                                                    <tr>
                                                        <td class="letter-name"><span style="display: inline-block" class="avatar-char palette-Orange-400 bg m-r-5"><i class="zmdi zmdi-email"></i></span> <a style="color: #4C4C4C" href="<?= APP::Module('Routing')->root ?>admin/mail/letters/preview/<?= APP::Module('Crypt')->Encode($item[1]) ?>" target="_blank"><?= $item[2] ?></a></td>
                                                        <td><?= $data['stat']['letters'][$item[1]]['delivered'] ?></td>
                                                        <td><?= $data['stat']['letters'][$item[1]]['delivered'] ? round($data['stat']['letters'][$item[1]]['open'] / ($data['stat']['letters'][$item[1]]['delivered'] / 100), 2) : 0 ?>%</td>
                                                        <td><?= $data['stat']['letters'][$item[1]]['delivered'] ? round($data['stat']['letters'][$item[1]]['click'] / ($data['stat']['letters'][$item[1]]['delivered'] / 100), 2) : 0 ?>%</td>
                                                        <td><?= $data['stat']['letters'][$item[1]]['delivered'] ? round($data['stat']['letters'][$item[1]]['unsubscribe'] / ($data['stat']['letters'][$item[1]]['delivered'] / 100), 2) : 0 ?>%</td>
                                                        <td><?= $data['stat']['letters'][$item[1]]['delivered'] ? round($data['stat']['letters'][$item[1]]['spamreport'] / ($data['stat']['letters'][$item[1]]['delivered'] / 100), 2) : 0 ?>%</td>
                                                        <td>
                                                            <a href="<?= APP::Module('Routing')->root ?>admin/mail/letters/<?= $data['group_sub_id'] ? APP::Module('Crypt')->Encode($data['group_sub_id']) : 0 ?>/edit/<?= APP::Module('Crypt')->Encode($item[1]) ?>" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-edit"></span></a>
                                                            <a href="javascript:void(0)" data-letter-id="<?= $item[1] ?>" class="btn btn-sm btn-default btn-icon waves-effect waves-circle remove-letter"><span class="zmdi zmdi-delete"></span></a>
                                                        </td>
                                                    </tr>
                                                    <?
                                                    break;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <?
                            } else {
                                ?>
                                <div class="card-body card-padding">
                                    <div class="alert alert-warning" role="alert"><i class="zmdi zmdi-close-circle"></i> Письма не найдены</div>
                                </div>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </section>

            <? APP::Render('admin/widgets/footer') ?>
        </section>
        
        <div id="letters-stat-modal" role="dialog" class="modal fade bootbox" tabindex="-1">
            <div class="modal-dialog" style="z-index:999;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Фильтры статистики писем</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-horizontal form-padding">
                            <div class="form-group">
                                <label class="col-md-3 control-label">Дата</label>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <input id="utm-labeld-date-from" name="utm-labeld-date-from" type="text" class="form-control">
                                        </div>
                                        <div class="col-xs-6">
                                            <input id="utm-labeld-date-to" name="utm-labeld-date-to" type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label">UTM-метки</label>
                                <div class="col-md-9">
                                    <div id="utm-list"></div>
                                    <input id="utm-label-source" name="utm-label[source]" type="hidden">
                                    <input id="utm-label-medium" name="utm-label[medium]" type="hidden">
                                    <input id="utm-label-campaign" name="utm-label[campaign]" type="hidden">
                                    <input id="utm-label-term" name="utm-label[term]" type="hidden">
                                    <input id="utm-label-content" name="utm-label[content]" type="hidden">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link waves-effect save">Применить</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Отмена</button>
                    </div>
                </div>
            </div>
        </div>

        <? APP::Render('core/widgets/page_loader') ?>
        <? APP::Render('core/widgets/ie_warning') ?>

        <!-- Javascript Libraries -->
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/jquery/dist/jquery.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/Waves/dist/waves.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.updated.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/moment/min/moment.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>

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
                        rules: '{"logic":"intersect","rules":[{"method":"email","settings":{"logic":"LIKE","value":"%"}}]}'
                    },
                    success: function(res) {
                        switch(label) {
                            case 'root':
                                $('#utm-list').append('<div class="utm-source"></div>');
                                
                                $.each(res, function(source_index, source_value) {
                                    var utm_value = source_value ? source_value : '<Не определено>';
                                    $('#utm-list > .utm-source').append('<div class="item source" data-state="inactive" id="' + source_index + '"><div class="control radio"><i class="zmdi zmdi-plus-square"></i> <label><input type="radio" name="utm-label"><i class="input-helper"></i><span data-value="' + source_value + '">' + utm_value + '</span></label></div></div>');
                                });
                                break;
                            case 'source':
                                $('#' + item + ' > .utm-medium').empty();
                                
                                $.each(res, function(medium_index, medium_value) {
                                    var utm_value = medium_value ? medium_value : '<Не определено>';
                                    $('#' + item + ' > .utm-medium').append('<div class="item medium" data-state="inactive" id="' + medium_index + '"><div class="control radio"><i class="zmdi zmdi-plus-square"></i> <label><input type="radio" name="utm-label"><i class="input-helper"></i><span data-value="' + medium_value + '">' + utm_value + '</span></label></div></div>');
                                });
                                break;
                            case 'medium':
                                $('#' + item + ' > .utm-campaign').empty();
                                
                                $.each(res, function(campaign_index, campaign_value) {
                                    var utm_value = campaign_value ? campaign_value : '<Не определено>';
                                    $('#' + item + ' > .utm-campaign').append('<div class="item campaign" data-state="inactive" id="' + campaign_index + '"><div class="control radio"><i class="zmdi zmdi-plus-square"></i> <label><input type="radio" name="utm-label"><i class="input-helper"></i><span data-value="' + campaign_value + '">' + utm_value + '</span></label></div></div>');
                                });
                                break;
                            case 'campaign':
                                $('#' + item + ' > .utm-term').empty();
                                
                                $.each(res, function(term_index, term_value) {
                                    var utm_value = term_value ? term_value : '<Не определено>';
                                    $('#' + item + ' > .utm-term').append('<div class="item term" data-state="inactive" id="' + term_index + '"><div class="control radio"><i class="zmdi zmdi-plus-square"></i> <label><input type="radio" name="utm-label"><i class="input-helper"></i><span data-value="' + term_value + '">' + utm_value + '</span></label></div></div>');
                                });
                                break;
                            case 'term':
                                $('#' + item + ' > .utm-content').empty();
                                
                                $.each(res, function(content_index, content_value) {
                                    var utm_value = content_value ? content_value : '<Не определено>';
                                    $('#' + item + ' > .utm-content').append('<div class="item content" data-state="inactive" id="' + content_index + '"><div class="control radio"><i class="zmdi fa-angle-right"></i> <label><input type="radio" name="utm-label"><i class="input-helper"></i><span data-value="' + content_value + '">' + utm_value + '</span></label></div></div>');
                                });
                                break;
                        }

                        //$('#page-content').niftyOverlay('hide');
                    }
                });
            }

            $('.save').on('click', function(e) {
                document.location.href = '<?= APP::Module('Routing')->root ?>admin/mail/letters/<?= $data['group_sub_id'] ? APP::Module('Crypt')->Encode($data['group_sub_id']) : 0 ?>?statfilter[date][from]=' + $('#utm-labeld-date-from').val() + '&statfilter[date][to]=' + $('#utm-labeld-date-to').val() + '&statfilter[utm][source]=' + $('#utm-label-source').val() + '&statfilter[utm][medium]=' + $('#utm-label-medium').val() + '&statfilter[utm][campaign]=' + $('#utm-label-campaign').val() + '&statfilter[utm][term]=' + $('#utm-label-term').val() + '&statfilter[utm][content]=' + $('#utm-label-content').val() + '';
                
                $('#letters-stat-modal .modal-footer').remove();
                $('#letters-stat-modal .modal-body').html('<center><div class="preloader pl-xxl"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20" /></svg></div></center>');
            });

            $(document).ready(function() {
                $('#utm-labeld-date-from').datetimepicker({
                    format: 'YYYY-MM-DD',
                    defaultDate: new Date()
                });
                
                $('#utm-labeld-date-to').datetimepicker({
                    format: 'YYYY-MM-DD',
                    defaultDate: new Date()
                });
            
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
                            var source_value = $('.control > label > span', item).data('value');
                            
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
                            var medium_value = $('.control > label > span', item_medium).data('value');
                            var source_value = $('.control > label > span', item_source).data('value');
                            
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
                            var campaign_value = $('.control > label > span', item_campaign).data('value');
                            var medium_value = $('.control > label > span', item_medium).data('value');
                            var source_value = $('.control > label > span', item_source).data('value');
                            
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
                            var term_value = $('.control > label > span', item_term).data('value');
                            var campaign_value = $('.control > label > span', item_campaign).data('value');
                            var medium_value = $('.control > label > span', item_medium).data('value');
                            var source_value = $('.control > label > span', item_source).data('value');
                            
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

                
                $(document).on('click', '#utm-list > .utm-source > .item  > .control > label', function () {
                    var source_item = $(this).closest('.item.source');
                    
                    var source_value = $('.control > label > span', source_item).data('value');
                    
                    $('#utm-label-source').val(source_value);
                    $('#utm-label-medium').val('');
                    $('#utm-label-campaign').val('');
                    $('#utm-label-term').val('');
                    $('#utm-label-content').val('');
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .control > label', function () {
                    var source_item = $(this).closest('.item.source');
                    var medium_item = $(this).closest('.item.medium');
                    
                    var source_value = $('.control > label > span', source_item).data('value');
                    var medium_value = $('.control > label > span', medium_item).data('value');
                    
                    $('#utm-label-source').val(source_value);
                    $('#utm-label-medium').val(medium_value);
                    $('#utm-label-campaign').val('');
                    $('#utm-label-term').val('');
                    $('#utm-label-content').val('');
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .utm-campaign > .item > .control > label', function () {
                    var source_item = $(this).closest('.item.source');
                    var medium_item = $(this).closest('.item.medium');
                    var campaign_item = $(this).closest('.item.campaign');
                    
                    var source_value = $('.control > label > span', source_item).data('value');
                    var medium_value = $('.control > label > span', medium_item).data('value');
                    var campaign_value = $('.control > label > span', campaign_item).data('value');
                    
                    $('#utm-label-source').val(source_value);
                    $('#utm-label-medium').val(medium_value);
                    $('#utm-label-campaign').val(campaign_value);
                    $('#utm-label-term').val('');
                    $('#utm-label-content').val('');
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .utm-campaign > .item > .utm-term > .item > .control > label', function () {
                    var source_item = $(this).closest('.item.source');
                    var medium_item = $(this).closest('.item.medium');
                    var campaign_item = $(this).closest('.item.campaign');
                    var term_item = $(this).closest('.item.term');
                    
                    var source_value = $('.control > label > span', source_item).data('value');
                    var medium_value = $('.control > label > span', medium_item).data('value');
                    var campaign_value = $('.control > label > span', campaign_item).data('value');
                    var term_value = $('.control > label > span', term_item).data('value');
                    
                    $('#utm-label-source').val(source_value);
                    $('#utm-label-medium').val(medium_value);
                    $('#utm-label-campaign').val(campaign_value);
                    $('#utm-label-term').val(term_value);
                    $('#utm-label-content').val('');
                });
                
                $(document).on('click', '#utm-list > .utm-source > .item  > .utm-medium > .item > .utm-campaign > .item > .utm-term > .item > .utm-content > .item > .control > label', function () {
                    var source_item = $(this).closest('.item.source');
                    var medium_item = $(this).closest('.item.medium');
                    var campaign_item = $(this).closest('.item.campaign');
                    var term_item = $(this).closest('.item.term');
                    var content_item = $(this).closest('.item.content');
                    
                    var source_value = $('.control > label > span', source_item).data('value');
                    var medium_value = $('.control > label > span', medium_item).data('value');
                    var campaign_value = $('.control > label > span', campaign_item).data('value');
                    var term_value = $('.control > label > span', term_item).data('value');
                    var content_value = $('.control > label > span', content_item).data('value');
                    
                    $('#utm-label-source').val(source_value);
                    $('#utm-label-medium').val(medium_value);
                    $('#utm-label-campaign').val(campaign_value);
                    $('#utm-label-term').val(term_value);
                    $('#utm-label-content').val(content_value);
                });
                
                
                $('body').on('click', '.remove-letter', function() {
                    var letter_id = $(this).data('letter-id');

                    swal({
                        title: 'Are you sure?',
                        text: 'You will not be able to recover this letter',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DD6B55',
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No',
                        closeOnConfirm: false,
                        closeOnCancel: true
                    }, function(isConfirm){
                        if (isConfirm) {
                            $.post('<?= APP::Module('Routing')->root ?>admin/mail/api/letters/remove.json', {
                                id: letter_id
                            }, function() { 
                                swal({
                                    title: 'Done!',
                                    text: 'Letter #' + letter_id + ' has been removed',
                                    type: 'success',
                                    showCancelButton: false,
                                    confirmButtonText: 'Ok',
                                    closeOnConfirm: false
                                }, function(){
                                    window.location.href = '<?= APP::Module('Routing')->root ?>admin/mail/letters/<?= $data['group_sub_id'] ? APP::Module('Crypt')->Encode($data['group_sub_id']) : 0 ?>';
                                });
                            });
                        }
                    });
                });

                $('body').on('click', '.remove-letter-group', function() {
                    var letter_group_id = $(this).data('letter-group-id');

                    swal({
                        title: 'Are you sure?',
                        text: 'You will not be able to recover this group',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DD6B55',
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No',
                        closeOnConfirm: false,
                        closeOnCancel: true
                    }, function(isConfirm){
                        if (isConfirm) {
                            $.post('<?= APP::Module('Routing')->root ?>admin/mail/api/letters/groups/remove.json', {
                                id: letter_group_id
                            }, function() { 
                                swal({
                                    title: 'Done!',
                                    text: 'Group #' + letter_group_id + ' has been removed',
                                    type: 'success',
                                    showCancelButton: false,
                                    confirmButtonText: 'Ok',
                                    closeOnConfirm: false
                                }, function(){
                                    window.location.href = '<?= APP::Module('Routing')->root ?>admin/mail/letters/<?= $data['group_sub_id'] ? APP::Module('Crypt')->Encode($data['group_sub_id']) : 0 ?>'; 
                                });
                            });
                        }
                    });
                });
            });
        </script>
    </body>
</html>