<script>
    $(document).on('click', '#tab-nav-<?= $data['hash'] ?> > a', function () {
        GetAllUsers();
    });
    
    function GetAllUsers() {
        $('#all-users-list').html('<div class="text-center"><div class="preloader pl-xxl"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20" /></svg></div></div>');
        
        $.ajax({
            url: '<?= APP::Module('Routing')->root ?>admin/users/api/dashboard/all.json',
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                $('#all-users-list').html([
                    '<div class="table-responsive ь">',
                        '<table id="roles-users-list-table" class="table table-hover">',
                            '<thead>',
                                '<tr>',
                                    '<th width="35%">Роль</th>',
                                    '<th width="65%">Пользователи</th>',
                                '</tr>',
                            '</thead>',
                            '<tbody>',
                                '<tr>',
                                    '<td>Подписчики</td>',
                                    '<td><a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= APP::Module('Crypt')->Encode('{"logic":"merge","rules":[{"method":"role","settings":{"logic":"=","value":"new"}},{"method":"role","settings":{"logic":"=","value":"user"}}]}') ?>">' + (parseInt(data.roles.new) + parseInt(data.roles.user)) + '</a></td>',
                                '</tr>',
                                '<tr>',
                                    '<td>Администраторы</td>',
                                    '<td><a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= APP::Module('Crypt')->Encode('{"logic":"merge","rules":[{"method":"role","settings":{"logic":"=","value":"admin"}}]}') ?>">' + data.roles.admin + '</a></td>',
                                '</tr>',
                                '<tr>',
                                    '<td>Тех. администраторы</td>',
                                    '<td><a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= APP::Module('Crypt')->Encode('{"logic":"merge","rules":[{"method":"role","settings":{"logic":"=","value":"tech-admin"}}]}') ?>">' + data.roles['tech-admin'] + '</a></td>',
                                '</tr>',
                            '</tbody>',
                        '</table>',
                        '<table id="states-users-list-table" class="table table-hover">',
                            '<thead>',
                                '<tr>',
                                    '<th width="35%">Состояние</th>',
                                    '<th width="65%">Пользователи</th>',
                                '</tr>',
                            '</thead>',
                            '<tbody>',
                                '<tr>',
                                    '<td>Активные</td>',
                                    '<td><a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"state","settings":{"logic":"=","value":"active"}}]}') ?>">' + data.states.active + '</a></td>',
                                '</tr>',
                                '<tr>',
                                    '<td>Ожидают активации</td>',
                                    '<td><a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"state","settings":{"logic":"=","value":"inactive"}}]}') ?>">' + data.states.inactive + '</a></td>',
                                '</tr>',
                                '<tr>',
                                    '<td>Временно отписанные</td>',
                                    '<td><a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"state","settings":{"logic":"=","value":"pause"}}]}') ?>">' + data.states.pause + '</a></td>',
                                '</tr>',
                                '<tr>',
                                    '<td>Отписанные</td>',
                                    '<td><a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"state","settings":{"logic":"=","value":"unsubscribe"}}]}') ?>">' + data.states.unsubscribe + '</a></td>',
                                '</tr>',
                                '<tr>',
                                    '<td>В черном списке</td>',
                                    '<td><a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"state","settings":{"logic":"=","value":"blacklist"}}]}') ?>">' + data.states.blacklist + '</a></td>',
                                '</tr>',
                                '<tr>',
                                    '<td>Невозможно доставить почту</td>',
                                    '<td><a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users?filters=<?= APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"state","settings":{"logic":"=","value":"dropped"}}]}') ?>">' + data.states.dropped + '</a></td>',
                                '</tr>',
                            '</tbody>',
                        '</table>',
                    '</div>'
                ].join(''));
            }
        });
    }
</script>