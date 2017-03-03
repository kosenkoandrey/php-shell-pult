<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Geo анализ</title>

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
            'Geo анализ' => 'admin/analytics/geo'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Geo анализ</h2>
                        </div>
                        
                        <div class="card-body">
                            <div id="map" style="height: 400px"></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h2>По странам</h2>
                        </div>
                        <div class="card-body">
                            <table data-target="#table" id="table" class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Название</th>
                                        <th>Количество</th>
                                        <th>Выручка</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <input name="rules" id="rules" type="hidden" value='<?php echo $data['rules']; ?>'/>
                            <div class="btn-group btn-group-sm m-20" role="group" id="groups-tree">
                                <button type="button" class="btn btn-success waves-effect">/</button>
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
    <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootstrap-growl/bootstrap-growl.min.js"></script>
    <script src="https://api-maps.yandex.ru/2.1/?load=package.full&lang=ru-RU" type="text/javascript"></script>


    <? APP::Render('core/widgets/js') ?>
    
    <script>
        function getCountry(){           
            var data = {};
            $('#table tbody').html('<center><div class="preloader pl-xxl"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div></center>');
                
           
            if($('#rules').val()){
                data['rules'] = $('#rules').val();
            }

            $('.title').html('По странам');

            $.post('<?= APP::Module('Routing')->root ?>admin/analytics/api/geo/country.json', data, function(resp){
                var c = 0;
                $('#table tbody').html('');        
                $.each(resp.location, function(i, j){
                    $('#table tbody').append([
                        '<tr>',
                            '<td>'+c+'</td>',
                            '<td class="country-btn" data-name="'+i+'">'+i+'</td>',
                            '<td>'+(resp.url[i] ? '<a  target="_blank"  href="<?= APP::Module('Routing')->root ?>admin/users?filters='+resp.url[i]+'" >'+j+'</a>' : j)+'</td>',
                            '<td>'+parseInt(resp.amount[i]).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,')+' руб.</td>',
                        '</tr>'
                    ].join(''));
                    c++;
                });
            });
        }

        $(function(){
            getCountry();

            $(document).on('click', '.back', function(e){
                $('#groups-tree').empty();
                $('#groups-tree').append('<button type="button" class="btn btn-success waves-effect">/</button>');
                getCountry();
            });

            $(document).on('click', '.country-btn', function(e){
                $('#table tbody').html('<center><div class="preloader pl-xxl"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20"></circle></svg></div></center>');
                
                var data = {};

                if($('#rules').val()){
                    data['rules'] = $('#rules').val();
                }
                
                data['country_name_ru'] = $(this).data('name');
                    $('.title').html('По '+$(this).data('name'));
                    $('#groups-tree button').removeClass('btn-success').addClass('btn-default').addClass('back');
                    $('#groups-tree').append('<button type="button" class="btn btn-success waves-effect">' + $(this).data('name') + '</button>');
                   
                    $.post('<?= APP::Module('Routing')->root ?>admin/analytics/api/geo/city.json', data, function(resp){
                        $('#table tbody').html('');                
                        var c = 0;
                        $.each(resp.location, function(i, j){
                            $('#table tbody').append([
                                '<tr>',
                                    '<td>'+c+'</td>',
                                    '<td>'+i+'</td>',
                                    '<td>'+(resp.url[i] ? '<a  target="_blank"  href="<?= APP::Module('Routing')->root ?>admin/users?filters='+resp.url[i]+'" >'+j+'</a>' : j)+'</td>',
                                    '<td>'+parseInt(resp.amount[i]).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,')+' руб.</td>',
                                '</tr>'
                            ].join(''));
                            c++;
                        });
                    });
            });
        });

        ymaps.ready(function(){
            var coords = JSON.parse('<?php echo $data["maps"]; ?>');
 
            var myMap = new ymaps.Map("map", {
                center: [55.76, 37.64],
                zoom: 10,
                behaviors:["scrollZoom","default"]
            });
            
            var objectManager = new ymaps.ObjectManager({
                // Чтобы метки начали кластеризоваться, выставляем опцию.
                clusterize: true,
                // ObjectManager принимает те же опции, что и кластеризатор.
                gridSize: 32
            });
            	//myMap.controls.add('mapTools');
                
            var currentId = 0;
            var myGeoObjects = {"type": "FeatureCollection", "features":[]};
            $.each(coords, function(i, j){
                myGeoObjects["features"].push({
                    type: 'Feature',
                    id: currentId++,
                    geometry: {
                        type: "Point",
                        coordinates: [j[0], j[1]]
                    }
                });
            });
            myMap.geoObjects.add(objectManager);
            objectManager.add(myGeoObjects);  
        });
        </script>
     </body>
</html>                       