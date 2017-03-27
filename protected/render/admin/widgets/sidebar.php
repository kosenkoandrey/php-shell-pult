<?
APP::$insert['js_flot'] = ['js', 'file', 'before', '</body>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/flot/jquery.flot.js'];
APP::$insert['js_flot_tooltip'] = ['js', 'file', 'before', '</body>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/flot.tooltip/js/jquery.flot.tooltip.min.js'];
APP::$insert['js_flot_resize'] = ['js', 'file', 'before', '</body>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/flot/jquery.flot.resize.js'];

$system = APP::Module('Admin')->System();
$modules = [];
        
foreach (APP::$modules as $key => $value) {
    if (method_exists($value, 'Admin')) {
        $modules[$key] = $value->Admin();
    }
}
?>
<aside id="s-user-alerts" class="sidebar">
    <ul class="tab-nav tn-justified tn-icon m-t-10" data-tab-color="teal">
        <li><a class="system-cpu" href="#system-cpu" data-toggle="tab"><i class="zmdi zmdi-desktop-windows"></i></a></li>
        <li><a class="system-hdd" href="#system-hdd" data-toggle="tab"><i class="zmdi zmdi-dns"></i></a></li>
        <li><a class="system-memory" href="#system-memory" data-toggle="tab"><i class="zmdi zmdi-card-sd"></i></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade" id="system-cpu">
            <table class="table">
                <tbody>
                    <tr>
                        <td>Uptime</td>
                        <td id="uptime_value">-</td>
                    </tr>
                    <tr>
                        <td>Нагрузка</td>
                        <td id="la_values"><?= implode(' / ', $system[0]) ?></td>
                    </tr>
                </tbody>
            </table>
            <div style="margin: 0 10px;">
                <div id="dynamic-chart" class="flot-chart"></div>
                <div class="flc-dynamic"></div>
            </div>
        </div>
        <div class="tab-pane fade" id="system-hdd">
            <table class="table">
                <thead>
                    <tr>
                        <th>ФС</th>
                        <th>Размер</th>
                        <th>Исп.</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    foreach ($system[1] as $key => $value) {
                        if ($key === 0) continue;
                        ?>
                        <tr>
                            <td><?= $value[0] ?></td>
                            <td><?= $value[1] ?></td>
                            <td><?= $value[2] ?></td>
                        </tr>
                        <?
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="tab-pane fade" id="system-memory">
            <table class="table">
                <tbody>
                    <tr>
                        <td>Всего</td>
                        <td><?= APP::Module('Utils')->SizeConvert($system[2][1][1] * 1024) ?></td>
                    </tr>
                    <tr>
                        <td>Использовано</td>
                        <td><?= APP::Module('Utils')->SizeConvert($system[2][1][2] * 1024) ?></td>
                    </tr>
                    <tr>
                        <td>Свободно</td>
                        <td><?= APP::Module('Utils')->SizeConvert($system[2][1][3] * 1024) ?></td>
                    </tr>
                    <tr>
                        <td>Расшарено</td>
                        <td><?= APP::Module('Utils')->SizeConvert($system[2][1][4] * 1024) ?></td>
                    </tr>
                    <tr>
                        <td>Буффер</td>
                        <td><?= APP::Module('Utils')->SizeConvert($system[2][1][5] * 1024) ?></td>
                    </tr>
                    <tr>
                        <td>Кэшировано</td>
                        <td><?= APP::Module('Utils')->SizeConvert($system[2][1][6] * 1024) ?></td>
                    </tr>
                    <tr>
                        <td>Буффер/кэш</td>
                        <td><?= APP::Module('Utils')->SizeConvert($system[2][2][2] * 1024) ?> / <?= APP::Module('Utils')->SizeConvert($system[2][2][3] * 1024) ?></td>
                    </tr>
                    <tr>
                        <td>Своп</td>
                        <td><?= APP::Module('Utils')->SizeConvert($system[2][3][1] * 1024) ?> / <?= APP::Module('Utils')->SizeConvert($system[2][3][2] * 1024) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</aside>

<aside id="s-main-menu" class="sidebar">
    <div class="smm-header">
        <i class="zmdi zmdi-long-arrow-left" data-ma-action="sidebar-close"></i>
    </div>

    <ul class="smm-alerts">
        <li data-user-alert="system-cpu" data-ma-action="sidebar-open" data-ma-target="user-alerts">
            <i class="zmdi zmdi-desktop-windows"></i>
        </li>
        <li data-user-alert="system-hdd" data-ma-action="sidebar-open" data-ma-target="user-alerts">
            <i class="zmdi zmdi-dns"></i>
        </li>
        <li data-user-alert="system-memory" data-ma-action="sidebar-open" data-ma-target="user-alerts">
            <i class="zmdi zmdi-card-sd"></i>
        </li>
    </ul>

    <ul class="main-menu">
        <?
        switch (APP::Module('Users')->user['role']) {
            case 'admin': 
                ?>
                <li class="sub-menu">
                    <a href="" data-ma-action="submenu-toggle"><i class="zmdi zmdi-caret-right"></i> Биллинг</a>
                    <ul>
                        <li><a href="<?= APP::Module('Routing')->root ?>admin/billing/products">Продукты</a></li>
                        <li><a href="<?= APP::Module('Routing')->root ?>admin/billing/invoices">Счета</a></li>
                        <li><a href="<?= APP::Module('Routing')->root ?>admin/billing/payments">Платежи</a></li>
                    </ul>
                    </li>
                    <li class="sub-menu">
                        <a href="<?= APP::Module('Routing')->root ?>admin/costs"><i class="zmdi zmdi-caret-right"></i> Расходы</a>
                    </li>
                    <li class="sub-menu">
                        <a href="<?= APP::Module('Routing')->root ?>admin/groups"><i class="zmdi zmdi-caret-right"></i> Группы</a>
                    </li>
                    <li class="sub-menu">
                        <a href="<?= APP::Module('Routing')->root ?>admin/hotornot/people"><i class="zmdi zmdi-caret-right"></i> Hot or not</a>
                    </li>
                    <li class="sub-menu">
                        <a href="" data-ma-action="submenu-toggle"><i class="zmdi zmdi-caret-right"></i> Почта</a>
                        <ul>
                            <li><a href="<?= APP::Module('Routing')->root ?>admin/mail/letters/0">Письма</a></li>
                            <li><a href="<?= APP::Module('Routing')->root ?>admin/mail/senders/0">Отправители</a></li>
                            <li><a href="<?= APP::Module('Routing')->root ?>admin/mail/shortcodes">Шорт-коды</a></li>
                            <li><a href="<?= APP::Module('Routing')->root ?>admin/mail/log">Журнал</a></li>
                            <li><a href="<?= APP::Module('Routing')->root ?>admin/mail/queue">Очередь</a></li>
                            <li><a href="<?= APP::Module('Routing')->root ?>admin/mail/spam_lists">СПАМ-листы</a></li>
                            <li><a href="<?= APP::Module('Routing')->root ?>admin/mail/fbl">FBL-отчеты</a></li>
                            <li><a href="<?= APP::Module('Routing')->root ?>admin/mail/domains">Домены</a></li>
                        </ul>
                    </li>
                    <li class="sub-menu">
                        <a href="<?= APP::Module('Routing')->root ?>admin/members/pages/0"><i class="zmdi zmdi-caret-right"></i> Мемберка</a>
                    </li>
                    <li class="sub-menu">
                        <a href="<?= APP::Module('Routing')->root ?>admin/quiz/question"><i class="zmdi zmdi-caret-right"></i> Викторина</a>
                    </li>
                    <li class="sub-menu">
                        <a href="<?= APP::Module('Routing')->root ?>admin/tunnels"><i class="zmdi zmdi-caret-right"></i> Туннели</a>
                    </li>
                    <li class="sub-menu">
                        <a href="<?= APP::Module('Routing')->root ?>admin/users"><i class="zmdi zmdi-caret-right"></i> Пользователи</a>
                    </li>
                    <li class="sub-menu">
                        <a href="<?= APP::Module('Routing')->root ?>admin/taskmanager"><i class="zmdi zmdi-caret-right"></i> Менеджер задач</a>
                    </li>
                <?
                break;
            default:
                ?>
                <li class="sub-menu">
                    <a href="" data-ma-action="submenu-toggle"><i class="zmdi zmdi-caret-right"></i> Система</a>
                    <ul>
                        <li><a href="<?= APP::Module('Routing')->root ?>admin/app">Конфигурация</a></li>
                        <li><a href="<?= APP::Module('Routing')->root ?>admin/modules">Модули</a></li>
                    </ul>
                </li>
                <?
                foreach ($modules as $key => $value) {
                    ?>
                    <li class="sub-menu">
                        <a href="" data-ma-action="submenu-toggle"><i class="zmdi zmdi-caret-right"></i> <? 
                            switch ($key) {
                                case 'Analytics': echo 'Аналитика'; break;
                                case 'Billing': echo 'Биллинг'; break;
                                case 'Blog': echo 'Блог'; break;
                                case 'Cache': echo 'Кэш'; break;
                                case 'Comments': echo 'Комментарии'; break;
                                case 'Costs': echo 'Расходы'; break;
                                case 'Cron': echo 'Управление Cron'; break;
                                case 'Crypt': echo 'Шифрование'; break;
                                case 'Files': echo 'Файлы'; break;
                                case 'HotOrNot': echo 'Hot or not'; break;
                                case 'Likes': echo 'Оценки'; break;
                                case 'Logs': echo 'Журналы'; break;
                                case 'Mail': echo 'Почта'; break;
                                case 'Members': echo 'Мемберка'; break;
                                case 'Rating': echo 'Рейтинг'; break;
                                case 'Sessions': echo 'Сессии'; break;
                                case 'SocialNetworks': echo 'Социальные сети'; break;
                                case 'SSH': echo 'SSH соединения'; break;
                                case 'TaskManager': echo 'Менеджер задач'; break;
                                case 'Triggers': echo 'Триггеры'; break;
                                case 'Tunnels': echo 'Туннели'; break;
                                case 'Users': echo 'Пользователи'; break;
                                case 'Quiz': echo 'Викторина'; break;
                                case 'Groups': echo 'Группы'; break;
                                case 'Cloning': echo 'Клонирование'; break;
                                default: echo $key; break;
                            }
                        ?></a>
                        <ul>
                            <?= $value ?>
                        </ul>
                    </li>
                    <?
                }
                break;
        }
        ?>
    </ul>
</aside>
<?
ob_start();
?>
<script>
    $(document).ready(function(){
        var la_chart = [];
        var la_update_interval = 1000;

        function returnServerLA() {
            return la_chart;
        }

        function getServerLA() {
            tmp_la_chart = la_chart.slice(1);
            out_la_chart = [];

            for (var i = 0; i < tmp_la_chart.length; ++i) {
                out_la_chart.push([i - 1, tmp_la_chart[i][1]]);
            }

            $.ajax({
                url: '<?= APP::Module('Routing')->root ?>admin/api/server.json',
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    $('#la_values').html(data.la[0] + ' / ' + data.la[1] + ' / ' + data.la[2]);
                    $('#uptime_value').html(data.uptime + ' дней');
                    
                    out_la_chart.push([60, data.la[0]]);
                    la_chart = out_la_chart;
                }
            });
        }

        /* Create Chart */
        
        for (var i = 0; i <= 60; ++i) {
            la_chart.push([i, 0]);
        }

        var plot = $.plot("#dynamic-chart", [la_chart], {
            series: {
                label: "Средняя нагрузка",
                lines: {
                    show: true,
                    lineWidth: 0.2,
                    fill: 0.6
                },

                color: '#00BCD4',
                shadowSize: 0,
            },
            yaxis: {
                min: 0,
                max: 4,
                tickColor: '#eee',
                font :{
                    lineHeight: 13,
                    style: "normal",
                    color: "#9f9f9f",
                },
                shadowSize: 0,

            },
            xaxis: {
                tickColor: '#eee',
                show: true,
                font :{
                    lineHeight: 13,
                    style: "normal",
                    color: "#9f9f9f",
                },
                shadowSize: 0,
                min: 0,
                max: 60
            },
            grid: {
                borderWidth: 1,
                borderColor: '#eee',
                labelMargin:10,
                hoverable: true,
                clickable: true,
                mouseActiveRadius:6,
            },
            legend:{
                container: '.flc-dynamic',
                backgroundOpacity: 0.5,
                noColumns: 0,
                backgroundColor: "white",
                lineWidth: 0
            },
            tooltip: {
                show: true,
                content: "%s - %y"
            }
        });

        /* Update */    
        function updateLA() {
            getServerLA();
            plot.setData([returnServerLA()]);
            // Since the axes don't change, we don't need to call plot.setupGrid()

            plot.draw();
            setTimeout(updateLA, la_update_interval);
        }

        updateLA();
    });
</script>
<?
APP::$insert['js_la'] = ['js', 'code', 'before', '</body>', ob_get_contents()];
ob_end_clean();