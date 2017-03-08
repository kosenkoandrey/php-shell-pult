<? 
function getUrl($method, $filter, $date_from, $date_to, $units_from, $units_to){
    $filter['rules'][] = [
        'method' => $method,
        'settings' => [
            'dates_from'=>  $date_from,
            'dates_to'  =>  $date_to,
            'units_from'=>  $units_from,
            'units_to'  =>  $units_to 
        ]
    ];
    return APP::Module('Routing')->root.'admin/users?filters='.APP::Module('Crypt')->Encode(json_encode($filter));
}
?>
<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>RFM-анализ</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
        <style>
            .clients{
                cursor: pointer;
                color: #044582;
                text-decoration: underline;
            }
        </style>
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <?
        APP::Render('admin/widgets/header', 'include', [
            'RFM Billing' => 'admin/analytics/rfm/billing'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="col-lg-9">
                            <div class="card">
                                <div class="card-header">
                                    <h2>RFM-анализ <?php if(isset($data['title'])){ echo '('.$data['title'].')'; }  ?> <?php echo date('d-m-Y', strtotime($data['dates_from'])); ?></h2>
                                </div>

                                <div class="card-body">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 12%;">Frequency (units)</th>
                                                <? foreach ($data['table1'] as $date_id => $date_range) echo '<th style="width: 12%;">' . $date_id . '</th>'; ?>
                                                <th style="width: 12%;">Totals</th>
                                                <th style="width: 12%;">Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <? foreach (APP::Module('Analytics')->config['rfm']['units'] as $unit_id => $unit_range) { ?>
                                                <tr>
                                                    <td class="unit_id"><?= $unit_id ?></td>
                                                    <? foreach ($data['table1'] as $date_id => $date_range) {
                                                        if (isset($data['report'][$unit_id][$date_id])) {
                                                            $url = getUrl($data['method'], $data['filter'], date('d-m-Y H:i:s', $date_range[0]), date('d-m-Y H:i:s', $date_range[1]),$unit_range[0], $unit_range[1]);
                                                    ?>
                                                            <td class="details">
                                                                <a href="<?= $url; ?>" target="_blank"><span class="clients"><? echo (int) $data['report'][$unit_id][$date_id];?> </span></a>
                                                            </td>
                                                        <?php } else { ?>
                                                            <td class="text-muted">0</td>
                                                        <? } ?>
                                                    <? } ?>

                                                    <? $url = getUrl($data['method'], $data['filter'], 0, $data['dates_from'],$unit_range[0], $unit_range[1]); ?>
                                                    <td class="warning total"><a href="<?= $url; ?>" target="_blank"><span  class="clients" ><?= $data['totals']['units'][$unit_id]; ?></span></a></td>
                                                    <td><?= round((int) $data['totals']['units'][$unit_id] / ($data['totals']['summary'] / 100), 2) ?>%</td>
                                                </tr>
                                            <? } ?>

                                            <tr>
                                                <td class="total">Totals</td>

                                                <?php foreach ($data['table1'] as $date_id => $date_range){
                                                    $url = getUrl($data['method'], $data['filter'], date('d-m-Y H:i:s', $date_range[0]), date('d-m-Y H:i:s', $date_range[1]),1, 9999); 
                                                ?>
                                                    <td class="warning total">
                                                        <a href="<?= $url; ?>" target="_blank"><span  class="clients" ><?php echo (int) $data['totals']['dates'][$date_id]; ?></span></a>
                                                    </td>
                                                <?php } ?>
                                                <?
                                                    $url = getUrl($data['method'], $data['filter'], 0, $data['dates_from'],1, 9999); 
                                                ?>
                                                <td class="success total summary"><a href="<?= $url; ?>" target="_blank"><span  class="clients"><?= $data['totals']['summary'] ?></span></a></td>
                                                <td>100%</td>
                                            </tr>
                                            <tr>
                                                <td>Percentage</td>
                                                <? foreach ($data['table1'] as $date_id => $date_range) echo '<td>' . round((int) $data['totals']['dates'][$date_id] / ($data['totals']['summary'] / 100), 2) . '%</td>'; ?>
                                                <td>100%</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php if($data['report2']) { ?>
                            <div class="card">  
                                <div class="card-header">
                                    <h2>RFM-анализ <?php if(isset($data['title'])){ echo '('.$data['title'].')'; }  ?> <?php echo date('d-m-Y', strtotime($data['dates_two_from'])); ?></h2>
                                </div>
                                <div class="card-body card-padding">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 12%;">Frequency (units)</th>
                                                <? foreach ($data['table2'] as $date_id => $date_range) echo '<th style="width: 12%;">' . $date_id . '</th>'; ?>
                                                <th style="width: 12%;">Totals</th>
                                                <th style="width: 12%;">Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <? 
                                            foreach (APP::Module('Analytics')->config['rfm']['units'] as $unit_id => $unit_range) {
                                                ?>
                                                <tr>
                                                    <td class="unit_id"><?= $unit_id ?></td>
                                                    <? foreach ($data['table2'] as $date_id => $date_range) { 
                                                        if (isset($data['report2'][$unit_id][$date_id])) { 
                                                            $url = getUrl($data['method'], $data['filter'], date('d-m-Y H:i:s', $date_range[0]), date('d-m-Y H:i:s', $date_range[1]),$unit_range[0], $unit_range[1]); 
                                                    ?>
                                                            <td class="details">
                                                                <a href="<?= $url; ?>" target="_blank">
                                                                <span class="clients" ><?php echo $data['report2'][$unit_id][$date_id]; ?></span>
                                                                <sup style="margin-left: 5px;">
                                                                    <?php if($data['report2'][$unit_id][$date_id] - $data['report'][$unit_id][$date_id] > 0){ echo '<i class="zmdi zmdi-caret-up zmdi-hc-fw" style="font-size:14px;color: rgb(47, 151, 4)"></i> '; } elseif($data['report2'][$unit_id][$date_id] - $data['report'][$unit_id][$date_id] < 0) { echo '<i class="zmdi zmdi-caret-down zmdi-hc-fw" style="font-size:14px;color: rgb(151, 11, 4)"></i> '; } echo abs($data['report2'][$unit_id][$date_id] - $data['report'][$unit_id][$date_id]); ?>
                                                                </sup>
                                                                </a>
                                                            </td>
                                                        <? } else { ?>
                                                            <td class="text-muted">
                                                                <?php echo (int) $data['report2'][$unit_id][$date_id]; ?><sup style="margin-left: 5px;"><i class="zmdi zmdi-caret-down zmdi-hc-fw" style="font-size:14px;color: rgb(151, 11, 4)"></i> <?= abs($data['report2'][$unit_id][$date_id] - $data['report'][$unit_id][$date_id]);?></sup>
                                                            </td>
                                                        <? }
                                                    }
                                                    $url = getUrl($data['method'], $data['filter'], 0, $data['dates_two_from'],$unit_range[0], $unit_range[1]); ?>
                                                    <td class="warning total"><a href="<?= $url; ?>" target="_blank"><span class="clients"><?php echo (int) $data['totals2']['units'][$unit_id]; ?></span><sup style="margin-left: 5px;"><?php if($data['totals2']['units'][$unit_id] - $data['totals']['units'][$unit_id] > 0) { echo '<i class="zmdi zmdi-caret-up zmdi-hc-fw" style="font-size:14px;color: rgb(47, 151, 4)"></i> '; } elseif($data['totals2']['units'][$unit_id] - $data['totals']['units'][$unit_id] < 0) { echo '<i class="zmdi zmdi-caret-down zmdi-hc-fw" style="font-size:14px;color: rgb(151, 11, 4)"></i> '; } echo abs($data['totals2']['units'][$unit_id] - $data['totals']['units'][$unit_id]); ?></sup></a></td>
                                                    <td><?= round((int) $data['totals2']['units'][$unit_id] / ($data['totals2']['summary'] / 100), 2); ?>%<sup style="margin-left: 5px;"><?php if(round(round((int) $data['totals2']['units'][$unit_id] / ($data['totals2']['summary'] / 100), 2) - round((int) $data['totals']['units'][$unit_id] / ($data['totals']['summary'] / 100), 2),2) > 0){ echo '<i class="zmdi zmdi-caret-up zmdi-hc-fw" style="font-size:14px;color: rgb(47, 151, 4)"></i> '; } elseif(round(round((int) $data['totals2']['units'][$unit_id] / ($data['totals2']['summary'] / 100), 2) - round((int) $data['totals']['units'][$unit_id] / ($data['totals']['summary'] / 100), 2),2) < 0) { echo '<i class="zmdi zmdi-caret-down zmdi-hc-fw" style="font-size:14px;color: rgb(151, 11, 4)"></i> '; } echo abs(round(round((int) $data['totals2']['units'][$unit_id] / ($data['totals2']['summary'] / 100), 2) - round((int) $data['totals']['units'][$unit_id] / ($data['totals']['summary'] / 100), 2),2)); ?>%</sup></td>
                                                </tr>
                                                <?
                                            }
                                            ?>
                                            <tr>
                                                <td class="total">Totals</td>
                                                <? foreach ($data['table2'] as $date_id => $date_range){ 
                                                    $url = getUrl($data['method'], $data['filter'], date('d-m-Y H:i:s', $date_range[0]), date('d-m-Y H:i:s', $date_range[1]),1, 9999); 
                                                ?>
                                                    <td class="warning total">
                                                        <a href="<?= $url; ?>" ><span class="clients"><?php echo (int) $data['totals2']['dates'][$date_id]; ?></span><sup style="margin-left: 5px;"><?php if($data['totals2']['dates'][$date_id] - $data['totals']['dates'][$date_id] > 0){ echo '<i class="zmdi zmdi-caret-up zmdi-hc-fw" style="font-size:14px;color: rgb(47, 151, 4)"></i> '; } elseif($data['totals2']['dates'][$date_id] - $data['totals']['dates'][$date_id] < 0) { echo '<i class="zmdi zmdi-caret-down zmdi-hc-fw" style="font-size:14px;color: rgb(151, 11, 4)"></i> '; } echo abs($data['totals2']['dates'][$date_id] - $data['totals']['dates'][$date_id]); ?></sup></a>
                                                    </td>
                                                <?php } 
                                                    $url = getUrl($data['method'], $data['filter'], 0, $data['dates_two_from'],1, 9999); 
                                                ?>
                                                <td class="success total summary" ><a href="<?= $url; ?>" target="_blank"><span class="clients"><?php echo $data['totals2']['summary']; ?></span><sup style="margin-left: 5px;"><?php if($data['totals2']['summary'] - $data['totals']['summary'] > 0){ echo '<i class="zmdi zmdi-caret-up zmdi-hc-fw" style="font-size:14px;color: rgb(47, 151, 4)"></i> '; } elseif($data['totals2']['summary'] - $data['totals']['summary'] < 0) { echo '<i class="zmdi zmdi-caret-down zmdi-hc-fw" style="font-size:14px;color: rgb(151, 11, 4)"></i> '; } echo abs($data['totals2']['summary'] - $data['totals']['summary']); ?></sup></a></td>
                                                <td>100%</td>
                                            </tr>
                                            <tr>
                                                <td>Percentage</td>
                                                <? foreach ($data['table2'] as $date_id => $date_range){ ?>
                                                    <td>
                                                        <?php echo round((int) $data['totals2']['dates'][$date_id] / ($data['totals2']['summary'] / 100), 2) . '%'; ?>
                                                        <sup style="margin-left: 5px;">
                                                            <?php if(round(round((int) $data['totals2']['dates'][$date_id] / ($data['totals2']['summary'] / 100), 2) - round((int) $data['totals']['dates'][$date_id] / ($data['totals']['summary'] / 100), 2),2) > 0){ echo '<i class="zmdi zmdi-caret-up zmdi-hc-fw" style="font-size:14px;color: rgb(47, 151, 4)"></i> '; } elseif(round(round((int) $data['totals2']['dates'][$date_id] / ($data['totals2']['summary'] / 100), 2) - round((int) $data['totals']['dates'][$date_id] / ($data['totals']['summary'] / 100), 2),2) < 0) { echo '<i class="zmdi zmdi-caret-down zmdi-hc-fw" style="font-size:14px;color: rgb(151, 11, 4)"></i> '; } echo abs(round(round((int) $data['totals2']['dates'][$date_id] / ($data['totals2']['summary'] / 100), 2) - round((int) $data['totals']['dates'][$date_id] / ($data['totals']['summary'] / 100), 2),2)); ?>%
                                                        </sup>
                                                    </td>
                                                <?php } ?>
                                                <td>100%</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php } ?>
                    </div>
                    <div class="col-lg-3 filters">
                        <div class="card">
                            <div class="card-header">
                                <h2>Фильтр</h2>
                            </div>
                            <div class="card-body card-padding">
                                <form id="filters" method="post">
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <input name="dates_from" id="date_from" type="text" class="form-control dt_picker" value="<?php if($data['dates_from']) { echo date('Y-m-d', strtotime($data['dates_from'])); } ?>">
                                        </div>
                                        <div class="form-group">
                                            <input name="dates_two_from" id="date_two_from" type="text" class="form-control dt_picker" value="<?php if($data['dates_two_from']) { echo date('Y-m-d', strtotime($data['dates_two_from'])); } ?>">
                                        </div>
                                        <input type="hidden" value="<?php echo htmlspecialchars(json_encode($data["filter"])); ?>" name="rules" />
                                    </div>                      
                                    <div class="card-footer">
                                        <button type="submit" class="filter-click btn btn-primary btn-block btn-lg">Применить</button>
                                    </div>
                                </form>
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
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/moment/min/moment.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>    
       
        <? APP::Render('core/widgets/js') ?>
        <!-- OPTIONAL -->
        <script>
            
                
        $(function(){
            $(document).on('click', '.filter-click', function(e){
                var date_from = $('#date_from').val();
                var date_to = $('#date_to').val();
                var units_from = $('#units_from').val();
                var units_to = $('#units_to').val();
                $('#filters').submit();
            });
            
            $('.dt_picker').datetimepicker({
                format: 'YYYY-MM-DD',
                defaultDate: new Date()
            });
            
        });
        </script>
    </body>
</html>