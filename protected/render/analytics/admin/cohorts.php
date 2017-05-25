<?
$indicators = [
    'total_subscribers_active' => 'Активированные подписчики',
    'total_subscribers_unsubscribe' => 'Отписанные подписчики',
    'total_subscribers_dropped' => 'Дропутые подписчики',
    'total_clients' => 'Покупатели',
    'total_orders' => 'Заказы',
    'total_revenue' => 'Выручка',
    'ltv_client' => 'LTV покупателя',
    'cost' => 'Расходы',
    'subscriber_cost' => 'Расходы на подписчика',
    'client_cost' => 'Расходы на покупателя',
    'roi' => 'ROI',
];

$post_indicators = [
    'total_subscribers_active',
    'total_subscribers_unsubscribe',
    'total_subscribers_dropped',
    'total_clients',
    'total_orders',
    'total_revenue',
    'ltv_client',
    'cost',
    'subscriber_cost',
    'client_cost',
    'roi',
];

$post['indicators'] = isset($_POST['indicators']) ? $_POST['indicators'] : $post_indicators;

function to_bank_amount($val, $precision = 2) {
    $q = pow (10, $precision);
    $x = intval (abs ($val) * $q * 10);
    if ($val && (($x % 10) == 5)){
    $tmp = intval (abs ($val) * $q);
    if ($tmp % 2 != 0) $tmp += 1;
    if ($val < 0) $tmp = 0 - $tmp;
        $amount = $tmp / $q;
    }else{
        $amount = round($val,$precision);
    }
    return (float) $amount;
}

// roi period 
$tmp_roi_period = [];
$roi_period = [];
$roi_period_data = $data;

array_pop($roi_period_data);
foreach ($roi_period_data as $period) {
    foreach (array_values(array_reverse($period['indicators'])) as $index => $cohort) {
        $tmp_roi_period[$index]['cost'][] = $cohort['cost'];
        $tmp_roi_period[$index]['total_revenue'][] = $cohort['total_revenue'];
    }
}

foreach ($tmp_roi_period as $index => $values) {
    $roi_period[] = [
        'index' => 'ROI-' . ($index + 1) . ' (' . count($values['cost']) . ')',
        'roi' => to_bank_amount(((array_sum($values['total_revenue']) - array_sum($values['cost'])) / (array_sum($values['cost']) ? array_sum($values['cost']) : 1)) * 100)
    ];
}


?>
<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Когортный анализ</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">        
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet"> 
        <link href="<?= APP::Module('Routing')->root ?>public/modules/users/rules.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/modules/tunnels/scheme/letter-selector/style.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/morris-js/morris.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/chosen/chosen.min.css" rel="stylesheet">
        <!-- OPTIONAL -->
        <style>
            .dropdown-toggle.selectpicker {
                height: 35px;
            }
            .date-head {
                min-width: 240px;
            }
            #results-table {
                width: 100%;
                overflow-x: auto;
            }
            td.indicator-names:first-child{
                padding-left:0px!important;
            }
            .indicator-names {
                min-width: 240px;
            }
            .link {
                text-decoration: underline;
                color: #337ab7;
            }
            .cohorts-content {
                height: 500px;
            }
            .cohorts-left {
                height: 100%;
                overflow: hidden;
                position: relative;
                overflow-x: auto;
            }
            .cohorts-left::webkit-scrollbar {
                display: none;
            }
            .cohorts-left-header,
            .cohorts-right-header {
                position: absolute;
            }
            .cohorts-left-content,
            .cohorts-right-content {
                margin-top: 50px;
                display: inline-block;
                height: 460px;
                overflow-y: auto;
            }
            .cohorts-left-content td:last-of-type {
                min-width: 140px;
            }
            .cohorts-left-content{
                overflow-y: hidden;
            }
            .cohorts-right {
                height: 100%;
                overflow: hidden;
                position: relative;
            }
            .cohorts-right-content {
                width: 200px;
            }

            #roi-chart {
                height: 400px;
            }
            #roi-chart-legend > span {
                margin-right: 20px;
            }
        </style>
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <? 
        APP::Render('admin/widgets/header', 'include', [
            'Аналитика' => 'admin/analytics/cohorts'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            
                            <h2>Когортный анализ</h2>
                            <ul class="actions">
                                <li class="dropdown">
                                    <a href="javascript:void(0)" data-toggle="dropdown">
                                        <i class="zmdi zmdi-more-vert"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a id="cohorts-settings" href="javascript:void(0)"><i class="fa fa-cog"></i> Настройки</a></li>
                                        <li><a id="cohorts-text" href="javascript:void(0)"><i class="fa fa-cog"></i> Описание</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div>
                            <div class="col-lg-10 col-sm-8 cohorts-left">
                                <div class="cohorts-left-header">
                                    <table class="table">
                                        <thead>
                                            <tr valign="top">
                                                <th class="date-head"></th>
                                                <?
                                                foreach ($data as $index => $values) {
                                                    ?>
                                                    <th class="date-head"><i class="fa fa-calendar"></i> <?= $values['label'] ?></th>
                                                    <?
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="cohorts-left-content">
                                    <table class="table">
                                        <tbody>
                                            <?
                                            $total_glob_data = [];
                                            $total_revenue = [];

                                            foreach ($data as $index => $values) {
                                                $last_indicators = [];
                                                ?>
                                                <tr valign="top" style="height: <?= (25 * count($post['indicators'])) + 30 ?>px">
                                                    <? if ($index !== 0) { ?><td colspan="<?= $index ?>"></td><? } ?>
                                                    <td class="indicator-names">
                                                        <?
                                                        $indicators_names = [];

                                                        foreach ($post['indicators'] as $value) {
                                                            $indicators_names[] = $indicators[$value];
                                                        }
                                                        ?>
                                                        <b><?= implode('</b><br><b>', $indicators_names) ?></b>
                                                        <hr style="margin: 10px 0">
                                                        <i class="fa fa-user"></i> <?= $values['users'] ?>
                                                    </td>
                                                    <?
                                                    $total = [];

                                                    foreach ($data as $l_index => $l_values) {
                                                        if (isset($l_values['indicators'][$index])) {
                                                            foreach ($post['indicators'] as $key) {
                                                                switch ($key) {
                                                                    default:
                                                                        $total[$key] = $l_values['indicators'][$index][$key];
                                                                        break;
                                                                }
                                                            }

                                                            $total['orders'] = array_merge(isset($total['orders']) ? $total['orders'] : [], $l_values['indicators'][$index]['orders']);
                                                            ?>
                                                            <td>
                                                                <?
                                                                $indicators_values = [];

                                                                foreach ($post['indicators'] as $key) {
                                                                    $last_indicators_key = isset($last_indicators[$key]) ? $last_indicators[$key] : 0;
                                                                    $last_value = $total[$key] - $last_indicators_key;
                                                                    $sup_last_value = '';

                                                                    if (isset($last_indicators[$key]) && count($last_indicators)) {
                                                                        switch ($last_value >= 0 ? ($last_value == 0 ? 0 : 1) : -1 ) {
                                                                            case 1: $sup_last_value = '<span style="color: green">+' . to_bank_amount($last_value, 2) . '</span>'; break;
                                                                            case -1: $sup_last_value = '<span style="color: red">' . to_bank_amount($last_value, 2) . '</span>'; break;
                                                                        }
                                                                    }

                                                                    switch ($key) {
                                                                        case 'total_subscribers_active':
                                                                        case 'total_subscribers_unsubscribe':
                                                                        case 'total_subscribers_dropped':
                                                                        case 'total_clients':
                                                                            $indicators_values[] = (int) $total[$key] . ' <sup>' . $sup_last_value . '</sup>';
                                                                            break;
                                                                        case 'total_orders':
                                                                            $indicators_values[] = '<a class="link" target="_blank" href="' . APP::Module('Routing')->root . 'admin/billing/invoices?filters=' .  APP::Module('Crypt')->Encode(json_encode(['logic'=>'intersect','rules'=>[['method'=>'id','settings'=>['logic'=>'IN','value'=>$total['orders']]]]])) . '">' . $total[$key] . '</a> <sup>' . $sup_last_value . '</sup>';
                                                                            break;
                                                                        case 'total_revenue':
                                                                        case 'ltv_client':
                                                                        case 'cost':
                                                                        case 'subscriber_cost':
                                                                        case 'client_cost':
                                                                            $indicators_values[] = to_bank_amount($total[$key], 2) . ' <sup>' . $sup_last_value . '</sup>';
                                                                            break;
                                                                        case 'roi':
                                                                            $indicators_values[] = to_bank_amount($total[$key], 2) . '% <sup>' . $sup_last_value . '</sup>';
                                                                            break;
                                                                    }
                                                                }

                                                                echo implode('<br>', array_values($indicators_values));
                                                                ?>
                                                            </td>
                                                            <?
                                                            $last_indicators = $l_values['indicators'][$index];
                                                        }
                                                    }

                                                    $total_data = Array();

                                                    foreach ($total as $key => $values) {
                                                        $total_cohorts[$key][] = $values;

                                                        switch ($key) {
                                                            case 'total_subscribers_active':
                                                            case 'total_subscribers_unsubscribe':
                                                            case 'total_subscribers_dropped':
                                                            case 'total_clients':
                                                            case 'total_orders':
                                                                $total_data[] = $values;
                                                                break;
                                                            case 'total_revenue':
                                                            case 'ltv_client':
                                                            case 'cost':
                                                            case 'subscriber_cost':
                                                            case 'client_cost':
                                                                $total_data[] = to_bank_amount($values, 2) . ' руб.';
                                                                break;
                                                            case 'roi':
                                                                $total_data[] = to_bank_amount($values, 2) . ' %';
                                                                break;
                                                        }
                                                    }

                                                    $total_glob_data[] = implode('<br>', array_values($total_data));
                                                    ?>
                                                </tr>
                                                <?
                                            }
                                            ?>
                                            <tr style="height:30px;"><tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-4 cohorts-right">
                                <div class="cohorts-right-header">
                                    <table class="table">
                                        <thead>
                                            <tr valign="top">
                                                <th class="date-head">Итого</td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="cohorts-right-content">
                                    <table class="table">
                                        <tbody>
                                            <?
                                            foreach ($total_glob_data as $values) {
                                                ?>
                                                <tr style="height: <?= (25 * count($post['indicators'])) + 30 ?>px"><td style="font-weight: bold"><?= $values ?></td></tr>
                                                <?
                                            }
                                            ?>
                                            <tr style="height:30px;"><tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </div>
                            <hr style="margin-top: 10px">
                                    
                            <table class="table table-striped">
                                <?
                                foreach ($total_cohorts as $key => $values) {
                                    ?>
                                    <tr>
                                        <?
                                        switch ($key) {
                                            case 'client_cost':
                                                ?><td style="width: 30%"><?= $indicators[$key] ?></td><td><?= number_format(to_bank_amount(array_sum($total_cohorts['cost']) / array_sum($total_cohorts['total_clients']), 2), 2, '.', ' ') ?> руб.</td><?
                                                break;
                                            case 'subscriber_cost':
                                                ?><td style="width: 30%"><?= $indicators[$key] ?></td><td><?= number_format(to_bank_amount(array_sum($total_cohorts['cost']) / array_sum($total_cohorts['total_subscribers_active']), 2), 2, '.', ' ') ?> руб.</td><?
                                                break;
                                            
                                            
                                            case 'total_subscribers_active':
                                            case 'total_subscribers_unsubscribe':
                                            case 'total_subscribers_dropped':
                                            case 'total_clients':
                                            case 'total_orders':
                                                ?><td style="width: 30%"><?= $indicators[$key] ?></td><td><?= array_sum($values) ?></td><?
                                                break;
                                            case 'total_revenue':
                                            case 'cost':
                                                ?><td style="width: 30%"><?= $indicators[$key] ?></td><td><?= number_format(to_bank_amount(array_sum($values), 2), 2, '.', ' ') ?> руб.</td><?
                                                break;
                                            case 'ltv_client':
                                                ?><td style="width: 30%"><?= $indicators[$key] ?></td><td><?= number_format(to_bank_amount(array_sum($values) / count($values), 2), 2, '.', ' ') ?> руб.</td><?
                                                break;
                                            case 'roi':
                                                ?><td style="width: 30%"><?= $indicators[$key] ?></td><td><?= to_bank_amount(((int) array_sum($total_cohorts['total_revenue']) - (float) array_sum($total_cohorts['cost'])) / (array_sum($total_cohorts['cost']) ? (float) array_sum($total_cohorts['cost']) : 1), 2) * 100 ?> %</td><?
                                                break;
                                        }
                                        ?>
                                    </tr>    
                                    <?
                                }
                                ?>
                            </table>

                            <?
                            $overview = [];
                            $total_revenue = 0;
                            $cost = 0;
                            $subscriber_cost = 0;
                            $client_cost = 0;
                            
                            foreach ($data as $value) {
                                $total_revenue = 0;
                                $cost = 0;

                                foreach ($value['indicators'] as $indicator) {
                                    $total_revenue += $indicator['total_revenue'];
                                    $cost += $indicator['cost'];
                                    $subscriber_cost += $indicator['subscriber_cost'];
                                    $client_cost += $indicator['client_cost'];
                                }

                                $roi = (($total_revenue - $cost) / ($cost ? $cost : 1)) * 100;

                                $overview[] = Array(
                                    'date' => $value['label'],
                                    'total_revenue' => $total_revenue,
                                    'cost' => $cost,
                                    'roi' => $roi
                                );
                            }
                            ?>

                            <div id="roi-chart"></div>
                            <div id="roi-chart-legend"></div>


                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Общая выручка</th>
                                        <th>Расходы</th>
                                        <th>ROI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                    foreach ($overview as $value) {
                                        ?>
                                        <tr>
                                            <td style="width: 25%"><?= $value['date'] ?></td>
                                            <td style="width: 25%"><?= number_format(to_bank_amount($value['total_revenue'], 2), 2, '.', ' ') ?> <i class="fa fa-rub" aria-hidden="true"></i></td>
                                            <td style="width: 25%"><?= number_format(to_bank_amount($value['cost'], 2), 2, '.', ' ') ?> <i class="fa fa-rub" aria-hidden="true"></i></td>
                                            <td style="width: 25%"><?= to_bank_amount($value['roi'], 2) ?> %</td>
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
            
            <div id="cohorts-settings-modal" role="dialog" class="modal fade" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button class="close" data-dismiss="modal" type="button"><span>&times;</span><span class="sr-only">Закрыть</span></button>
                            <h4 class="modal-title">Настройки когортного анализа</h4>
                        </div>
                        <div class="modal-body">
                            <form method="post" class="form-horizontal bv-form">
                                <input type="hidden" name="rules" value="<?= isset($_POST['rules']) ? htmlspecialchars($_POST['rules']) : '' ?>">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Способ компоновки</label>
                                    <div class="col-md-10">
                                        <select name="group" class="selectpicker">
                                            <option value="day" <? if(isset($_POST['group']) && $_POST['group'] == 'day') { ?>selected<? } ?>>День</option>
                                            <option value="week" <? if(isset($_POST['group']) && $_POST['group'] == 'week') { ?>selected<? } ?>>Неделя</option>
                                            <option value="month" <? if(isset($_POST['group']) && $_POST['group'] == 'month') { ?>selected<? } ?>>Месяц</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Отображаемые данные</label>
                                    <div class="col-md-10">
                                        <select name="indicators[]" id="select-indicators" class="chosen" data-placeholder="Выберите показатели" multiple>
                                            <?
                                            foreach ($indicators as $key => $value) {
                                                ?><option value="<?= $key ?>" <? if(array_search($key, $post['indicators']) !== false) { ?>selected<? } ?>><?= $value ?></option><?
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link waves-effect save">Применить</button>
                            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Отмена</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="cohorts-text-modal" role="dialog" class="modal fade" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button class="close" data-dismiss="modal" type="button"><span>&times;</span><span class="sr-only">Закрыть</span></button>
                            <h4 class="modal-title">Описание</h4>
                        </div>
                        <div class="modal-body">
                            <p>Анализируя пользователей в когортном анализе делим их на когорты в зависимости от даты регистрации, начиная от самой ранней. Первая когорта это период самой раней регистрации.</p>
                            <p>Покупатели - это пользователи из когорты которые совершили покупку. В каждом периоде отслеживается этот показатель прироста или падения для когорты.</p>
                            <p>Выручка - это сумма всех заказов совершенных пользователями из когорыт за определенный период.</p>
                            <p>Расходы - в каждой когорте одинаковые и это сумма всех расходов на пользователей. </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">Отмена</button>
                        </div>
                    </div>
                </div>
            </div>
            
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
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/moment/min/moment.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/morris.js/morris.min.js"></script>
	<script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/raphael/raphael.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/chosen/chosen.jquery.min.js"></script>
        
        <script src="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/morris-js/morris.min.js"></script>
	<script src="<?= APP::Module('Routing')->root ?>public/nifty/ui/plugins/morris-js/raphael-js/raphael.min.js"></script>
            <? APP::Render('core/widgets/js') ?>
        
        <!-- OPTIONAL -->
        <script>
   
            $(document).ready(function() {               
                $('#cohorts-panel').html('<div class="text-center"><div class="preloader pl-xxl"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20" /></svg></div></div>');
            });
            
            var container = $('#results-table');
            var tblWidth = $('#results-table > table').width();
            var duration = 5000;

            $(document).on('keydown',function(e){
                if(e.which == 39) {
                    container.animate({
                        scrollLeft: tblWidth
                    }, duration, 'linear');
                } else if(e.which == 37) {
                    container.animate({
                        scrollLeft: 0
                    }, duration, 'linear');
                }
            });
            $(document).keyup(function(){
                container.stop(true);
            });
            
            $(document).on('click', '#cohorts-text', function(){
                $('#cohorts-text-modal').modal('show');
            });
            
            $(document).on('click', '#cohorts-settings', function(){
                $('#cohorts-settings-modal').modal('show');
            });
            
            $(document).on('click', '.save', function(){
                $('#cohorts-settings-modal').modal('hide');
                $('#cohorts-settings-modal form').submit();
            }); 
            $('.cohorts-left-content').on('scroll', function (e) {
                var top = $(e.target).scrollTop();
                $('.cohorts-right-content').scrollTop(top);
            });
            
            $('.cohorts-right-content').on('scroll', function (e) {
                var top = $(e.target).scrollTop();
                $('.cohorts-left-content').scrollTop(top);
            });
            
  
            window.roi = Morris.Bar({
                element: 'roi-chart',
                data: <?= json_encode(array_slice($roi_period, 0, 12)) ?>,
                xkey: 'index',
                ykeys: ['roi'],
                labels: ['ROI (%)'],
                resize: true,
                hideHover: 'auto'
            });

            window.roi.options.labels.forEach(function(label, i){
                var legendItem = $('<span></span>').text(label).css('color', window.roi.options.lineColors[i]);
                $('#roi-chart-legend').append(legendItem);
            });
        </script>
      </body>
  </html>