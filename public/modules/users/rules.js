/**
 * @author Selivanov Max <max@evildevel.com>
 * 
 * Dependencies:
 * jquery.json (https://github.com/krinkle/jquery-json)
 * 
 */

(function($) {
    var settings;
    var objects = {};
    var methods = {
        init: function(options) { 
            $target_rules = $(this);

            settings = $.extend( {
                'url': 'http://pult2.glamurnenko.ru/',
                'rules': $.evalJSON($(this).val()),
                'debug': false
            }, options);

            $('<div/>', {
                'id': 'trigger_rules_editor',
            }).insertAfter(this);

            $('<div/>', {
                'class': 'trigger_children',
            }).appendTo($('#trigger_rules_editor'));

            methods.render_rules(settings.rules, $('.trigger_children'));
            methods.checkblock();
            return this;
        },
        render_value: function(trigger_rule) { // .trigger_rule
            var value = {
                'logic': $('> table > tbody > tr > .trigger_logic_holder > .trigger_logic > select', trigger_rule).val()
            };

            var trigger_rule_item = $('> table > tbody > tr > .trigger_holder > .trigger_rules > .trigger_rule_item', trigger_rule);
            var trigger_children_rule = $('> table > tbody > tr > .trigger_holder > .trigger_children > .trigger_rule', trigger_rule);

            if (trigger_rule_item.length) {
                value.rules = [];

                trigger_rule_item.each(function() {
                    var method = $(this).data('method');
                    var settings = new Object();

                    $('.trigger_settings input, .trigger_settings select', this).each(function() {
                        id = $(this).data('id');
                        param_value = $(this).val();

                        if (param_value) {
                            switch(method) {
                                case 'email': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'role': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'id': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'source': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'about': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        case 'item': 
                                            if (settings.item === undefined) settings.item = new Object();
                                            settings.item = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'state': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'firstname': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'lastname': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'city': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'country': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'tel': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'reg_date': 
                                    switch(id) {
                                        case 'date_from': 
                                            if (settings.date_from === undefined) settings.date_from = new Object();
                                            settings.date_from = Math.round(objects['reg_date_from'].date._d.setHours(0,0,0,0) / 1000); 
                                            break;
                                        case 'date_to': 
                                            if (settings.date_to === undefined) settings.date_to = new Object();
                                            settings.date_to = Math.round(objects['reg_date_to'].date._d.setHours(0,0,0,0) / 1000 - 1); 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'social_id': 
                                    switch(id) {
                                        case 'service': 
                                            if (settings.service === undefined) settings.service = new Object();
                                            settings.service = param_value; 
                                            break;
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'group': 
                                    switch(id) {
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'utm': 
                                    switch(id) {
                                        case 'item': 
                                            if (settings.item === undefined) settings.item = new Object();
                                            settings.item = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        case 'num': 
                                            if (settings.num === undefined) settings.num = new Object();
                                            settings.num = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'tags': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        case 'date_from': 
                                            if (settings.date_from === undefined) settings.date_from = new Object();
                                            settings.date_from = param_value; 
                                            break;
                                        case 'date_to': 
                                            if (settings.date_to === undefined) settings.date_to = new Object();
                                            settings.date_to = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'tunnels': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'tunnels_type': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'tunnels_tags': 
                                    switch(id) {
                                        case 'token': 
                                            if (settings.token === undefined) settings.token = new Object();
                                            settings.token = param_value; 
                                            break;
                                        case 'label': 
                                            if (settings.label === undefined) settings.label = new Object();
                                            settings.label = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'tunnels_queue': 
                                    switch(id) {
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'tunnels_object': 
                                    switch(id) {
                                        case 'object': 
                                            if (settings.object === undefined) settings.object = new Object();
                                            settings.object = param_value; 
                                            break;
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'tunnels_label': 
                                    switch(id) {
                                        case 'label_id': 
                                            if (settings.label_id === undefined) settings.label_id = new Object();
                                            settings.label_id = param_value; 
                                            break;
                                        case 'token': 
                                            if (settings.token === undefined) settings.token = new Object();
                                            settings.token = param_value; 
                                            break;
                                        case 'cr_date_mode': 
                                            if (settings.cr_date_mode === undefined) settings.cr_date_mode = new Object();
                                            settings.cr_date_mode = param_value; 
                                            break;
                                        case 'cr_date_value': 
                                            if (settings.cr_date_value === undefined) settings.cr_date_value = new Object();
                                            settings.cr_date_value = param_value; 
                                            break;
                                        case 'process_id': 
                                            if (settings.process_id === undefined) settings.process_id = new Object();
                                            settings.process_id = param_value; 
                                            break;
                                        case 'mode': 
                                            if (settings.mode === undefined) settings.mode = new Object();
                                            settings.mode = param_value; 
                                            break;
                                        case 'from': 
                                            if (settings.from === undefined) settings.from = new Object();
                                            settings.from = param_value; 
                                            break;
                                        case 'to': 
                                            if (settings.to === undefined) settings.to = new Object();
                                            settings.to = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'mail_count': 
                                    switch(id) {
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'mail_events': 
                                    switch(id) {
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        case 'logic': 
                                            if (settings.logic === undefined) settings.logic = new Object();
                                            settings.logic = param_value; 
                                            break;
                                        case 'date_from': 
                                            if (settings.date_from === undefined) settings.date_from = new Object();
                                            settings.date_from = Math.round(objects['mail_events_date_from'].date._d.setHours(0,0,0,0) / 1000); 
                                            break;
                                        case 'date_to': 
                                            if (settings.date_to === undefined) settings.date_to = new Object();
                                            settings.date_to = Math.round(objects['mail_events_date_to'].date._d.setHours(0,0,0,0) / 1000 - 1); 
                                            break;
                                        case 'letter': 
                                            if (settings.letter === undefined) settings.letter = new Object();
                                            settings.letter = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'mail_user_inactive': 
                                    switch(id) {
                                        case 'date_from': 
                                            if (settings.date_from === undefined) settings.date_from = new Object();
                                            settings.date_from = param_value; 
                                            break;
                                        case 'date_to': 
                                            if (settings.date_to === undefined) settings.date_to = new Object();
                                            settings.date_to = param_value; 
                                            break;
                                        case 'count': 
                                            if (settings.count === undefined) settings.count = new Object();
                                            settings.count = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'mail_log': 
                                    switch(id) {
                                        case 'date_from': 
                                            if (settings.date_from === undefined) settings.date_from = new Object();
                                            settings.date_from = param_value; 
                                            break;
                                        case 'date_to': 
                                            if (settings.date_to === undefined) settings.date_to = new Object();
                                            settings.date_to = param_value; 
                                            break;
                                        case 'letter': 
                                            if (settings.letter === undefined) settings.letter = new Object();
                                            settings.letter = param_value; 
                                            break;
                                        case 'result': 
                                            if (settings.result === undefined) settings.result = new Object();
                                            settings.result = param_value; 
                                            break;
                                        case 'state': 
                                            if (settings.state === undefined) settings.state = new Object();
                                            settings.state = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'mail_open_pct': 
                                    switch(id) {
                                        case 'from': 
                                            if (settings.from === undefined) settings.from = new Object();
                                            settings.from = param_value; 
                                            break;
                                        case 'to': 
                                            if (settings.to === undefined) settings.to = new Object();
                                            settings.to = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'mail_open_time': 
                                    switch(id) {
                                        case 'value': 
                                            if (settings.value === undefined) settings.value = new Object();
                                            settings.value = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'mail_open_pct30': 
                                    switch(id) {
                                        case 'from': 
                                            if (settings.from === undefined) settings.from = new Object();
                                            settings.from = param_value; 
                                            break;
                                        case 'to': 
                                            if (settings.to === undefined) settings.to = new Object();
                                            settings.to = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'product_buy': 
                                    switch(id) {
                                        case 'product': 
                                            if (settings.product === undefined) settings.product = new Object();
                                            settings.product= param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'product_order': 
                                    switch(id) {
                                        case 'product': 
                                            if (settings.product === undefined) settings.product = new Object();
                                            settings.product= param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'product_availability': 
                                    switch(id) {
                                        case 'product': 
                                            if (settings.product === undefined) settings.product = new Object();
                                            settings.product= param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'product_order_sum': 
                                    switch(id) {
                                        case 'mode': 
                                            if (settings.mode === undefined) settings.mode = new Object();
                                            settings.mode = param_value; 
                                            break;
                                        case 'sum': 
                                            if (settings.sum === undefined) settings.sum = new Object();
                                            settings.sum = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'rfm_billing': 
                                    switch(id) {
                                        case 'dates_from': 
                                            if (settings.dates_from === undefined) settings.dates_from = new Object();
                                            settings.dates_from = param_value; 
                                            break;
                                        case 'dates_to': 
                                            if (settings.dates_to === undefined) settings.dates_to = new Object();
                                            settings.dates_to = param_value; 
                                            break;
                                        case 'units_from': 
                                            if (settings.units_from === undefined) settings.units_from = new Object();
                                            settings.units_from = param_value; 
                                            break;
                                        case 'units_to': 
                                            if (settings.units_to === undefined) settings.units_to = new Object();
                                            settings.units_to = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                case 'rfm_mail': 
                                    switch(id) {
                                        case 'dates_from': 
                                            if (settings.dates_from === undefined) settings.dates_from = new Object();
                                            settings.dates_from = param_value; 
                                            break;
                                        case 'dates_to': 
                                            if (settings.dates_to === undefined) settings.dates_to = new Object();
                                            settings.dates_to = param_value; 
                                            break;
                                        case 'units_from': 
                                            if (settings.units_from === undefined) settings.units_from = new Object();
                                            settings.units_from = param_value; 
                                            break;
                                        case 'units_to': 
                                            if (settings.units_to === undefined) settings.units_to = new Object();
                                            settings.units_to = param_value; 
                                            break;
                                        case 'event': 
                                            if (settings.event === undefined) settings.event = new Object();
                                            settings.event = param_value; 
                                            break;
                                        default: settings[id] = param_value;
                                    }
                                    break;
                                default: settings[id] = param_value;
                            }
                        }
                    });

                    value.rules.push({
                        'method': method,
                        'settings': settings
                    });
                });
            }

            if (trigger_children_rule.length) {
                value.children = methods.render_value(trigger_children_rule);
            }

            return value;
        },
        add_trigger_rule: function() {
            var trigger_rule = $(this).closest('.trigger_rule');
            var trigger_logic = $(this).data('logic');
            var trigger_method = $(this).data('method');

            if (trigger_logic === $('> table > tbody > tr > .trigger_logic_holder > .trigger_logic > select', trigger_rule).val()) {
                methods.render_rule({
                    'method': trigger_method,
                    'settings': new Object()
                }, $('> table > tbody > tr > .trigger_holder > .trigger_rules', trigger_rule));
            } else {
                var trigger_children = $(trigger_rule).closest('.trigger_children');

                trigger_rule.remove();

                methods.render_rules({
                    'logic': trigger_logic,
                    'rules': [{
                        method: trigger_method,
                        'settings': new Object()
                    }],
                    'children': methods.render_value(trigger_rule)
                }, trigger_children);
            }   

            $('select', trigger_rule).selectpicker(); 
            $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
            methods.checkblock();
            methods.check_editor();
        },
        remove_trigger_rule: function() {
            $(this).parent().parent().remove();
            $(this).siblings('.trigger_rule_item').remove();
            $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
            methods.checkblock();
            methods.check_editor();
        },
        checkblock: function() {
            var value = $target_rules.val();
            var obj = JSON.parse(value);

            if (obj.rules === undefined || obj.rules.length == 1) {
                $('.trigger_logic_holder').css('width', '0');
                $('.trigger_logic_holder', $trigger_rule).hide();
            } else if (obj.rules && obj.rules.length > 1) {
                $('.trigger_logic_holder').css('width', '100px');
                $('.trigger_logic_holder', $trigger_rule).show();
            }
        },
        getRulesListByLogic: function(logic) {
            return [
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="id" href="javascript:void(0)">ID</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="email" href="javascript:void(0)">E-Mail</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="role" href="javascript:void(0)">Роль</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="state" href="javascript:void(0)">Состояние</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="reg_date" href="javascript:void(0)">Дата регистрации</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="tags" href="javascript:void(0)">Метка</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="group" href="javascript:void(0)">Группа</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="source" href="javascript:void(0)">Источник</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="about" href="javascript:void(0)">Доп. информация</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="utm" href="javascript:void(0)">UTM-метка</a></li>',
                '<li class="divider"></li>',
                /*
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="firstname" href="javascript:void(0)">Имя</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="lastname" href="javascript:void(0)">Фамилия</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="tel" href="javascript:void(0)">Телефон</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="city" href="javascript:void(0)">Город</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="country" href="javascript:void(0)">Страна</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="social_id" href="javascript:void(0)">Социальная сеть</a></li>',
                '<li class="divider"></li>',
                */
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="tunnels" href="javascript:void(0)">Туннель</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="tunnels_type" href="javascript:void(0)">Тип туннеля</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="tunnels_tags" href="javascript:void(0)">Метка туннеля</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="tunnels_queue" href="javascript:void(0)">Очередь туннелей</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="tunnels_object" href="javascript:void(0)">Объект туннеля</a></li>',
                '<li class="divider"></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="mail_log" href="javascript:void(0)">Отправка письма</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="mail_count" href="javascript:void(0)">Кол-во писем</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="mail_events" href="javascript:void(0)">Событие в письме</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="mail_open_pct" href="javascript:void(0)">Процент открытия писем</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="mail_open_pct30" href="javascript:void(0)">Процент открытия писем за 30 дней</a></li>',
                /*'<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="mail_user_inactive" href="javascript:void(0)">User inactive</a></li>',
                '<li class="divider"></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="product_buy" href="javascript:void(0)">Product buy</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="product_availability" href="javascript:void(0)">Product_availability</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="product_order" href="javascript:void(0)">Product order</a></li>',
                */
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="product_order_sum" href="javascript:void(0)">Сумма выручки</a></li>',
                '<li class="divider"></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="rfm_billing" href="javascript:void(0)">RFM анализ</a></li>',
                '<li><a class="add_trigger_rule" data-logic="' + logic + '" data-method="rfm_mail" href="javascript:void(0)">RFM анализ (письма)</a></li>',                
            ].join('');
        },
        render_rules: function(rules, holder) {
            var self = this;

            $trigger_rule = $('<div/>', {
                'class': 'trigger_rule'
            }).appendTo(holder); 

            $trigger_rule.append([
                '<a style="display:none;" href="javascript:void(0)" class="remove_trigger_rule btn btn-default btn-icon waves-effect waves-circle waves-float"><i class="zmdi zmdi-close"></i></a>',
                '<table>',
                    '<tr>',
                        '<td class="trigger_logic_holder" style="width: 100px; vertical-align: middle;">',
                            '<div class="trigger_logic">',
                                '<select class="selectpicker" data-width="85px">',
                                    '<option value="intersect">И</option>',
                                    '<option value="merge">ИЛИ</option>',
                                '</select>',
                            '</div>',
                        '</td>', 
                        '<td class="trigger_holder">',
                            '<div class="trigger_rules"></div>',
                            '<div class="trigger_children"></div>',
                            '<div class="btn-group trigger_holder_controls_several">',
                                '<div class="btn-group">',
                                    '<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">И</button>',
                                    '<ul class="dropdown-menu scrollable-menu scrollbar" role="menu">',                                  
                                        self.getRulesListByLogic('intersect'),
                                    '</ul>',
                                '</div>',
                                '<div class="btn-group">',
                                    '<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">ИЛИ</button>',
                                    '<ul class="dropdown-menu scrollable-menu scrollbar" role="menu">',
                                        self.getRulesListByLogic('merge'),
                                    '</ul>',
                                '</div>',
                            '</div>',
                            '<div class="btn-group trigger_holder_controls_single">',
                                '<div class="btn-group">',
                                    '<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">Добавить условие</button>',
                                    '<ul class="dropdown-menu scrollable-menu scrollbar" role="menu">',                                  
                                        self.getRulesListByLogic('intersect'),
                                    '</ul>',
                                '</div>',
                            '</div>',
                        '</td>',
                    '<tr>',
                '</table>'
            ].join(''));
            $('.trigger_holder .remove_trigger_rule').show();

            $('.add_trigger_rule', $trigger_rule).on('click', methods.add_trigger_rule);

            $('.trigger_logic select', $trigger_rule).val(rules.logic).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
            $('.remove_trigger_rule').on('click', methods.remove_trigger_rule);
        
            if (rules.rules !== undefined) {
                $.each(rules.rules, function(index, rule) {
                    methods.render_rule(rule, $('.trigger_rules', $trigger_rule));
                });
            }

            if (rules.children !== undefined) {
                methods.render_rules(rules.children, $('.trigger_children', $trigger_rule));
            }
            
            $('.selectpicker').selectpicker(); 

            methods.check_editor();
        },
        check_editor: function() {
            $('.trigger_children').each(function (i, item) {
                var child_numbers = 0;
                $item = $(item);
                child_numbers += $item.find('.trigger_holder').first().find('.trigger_rules > div').length;
                child_numbers += $item.find('.trigger_holder').first().find('.trigger_children > div').length;
                if (child_numbers < 2) {
                    $(item).find('.trigger_logic').hide();
                    $(item).find('.trigger_logic_holder').hide();
                    $(item).find('.trigger_holder_controls_single').show();
                    $(item).find('.trigger_holder_controls_several').hide();
                } else {
                    $(item).find('.trigger_logic').show();
                    $(item).find('.trigger_logic_holder').show();
                    $(item).find('.trigger_holder_controls_single').hide();
                    $(item).find('.trigger_holder_controls_several').show(); 
                }
            })
        },
        render_rule: function(rule, holder) {
            var elid = Math.floor(Math.random( ) * (999999+1));
            
            $trigger_rule_item = $('<div/>', {
                'class': 'trigger_rule_item',
                'data-method': rule.method
            }).appendTo(holder);

            $trigger_rule_item.append([
                '<div class="trigger_settings">',
                    '<a href="javascript:void(0)" class="remove_rule_item btn btn-default btn-icon waves-effect waves-circle waves-float"><i class="zmdi zmdi-close"></i></a>',
                '</div>'
            ].join(''));

            $('.remove_rule_item').on('click', methods.remove_trigger_rule);

            switch(rule.method) {
                case 'email':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>E-Mail</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker" data-id="logic">',
                                        '<option value="=">=</option>',
                                        '<option value="!=">!=</option>',
                                        '<option value="IN">один из</option>',
                                        '<optgroup label="по маске">',
                                            '<option value="LIKE">=</option>',
                                            '<option value="NOT LIKE">!=</option>',
                                        '</optgroup>',
                                        '<optgroup label="регулярное выражение">',
                                            '<option value="REGEXP">=</option>',
                                            '<option value="NOT REGEXP">!=</option>',
                                        '</optgroup>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'role':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Роль</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<input data-id="logic" class="form-control m-l-5" type="hidden" value="=">',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings input[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings input[data-id="logic"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'id':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>ID</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<input data-id="logic" class="form-control m-l-5" type="hidden" value="=">',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings input[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings input[data-id="logic"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'about':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Доп. информация</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="form-control m-t-10" data-id="item"></select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="logic" class="form-control m-l-5" type="hidden" value="=">',
                                    '<input data-id="value" class="form-control m-l-5" type="text" placeholder="Значение">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));
                    
                    $.post(settings.url+'admin/users/api/about/item/list.json', function(res){
                        $.each(res, function(j, i){
                            $('.trigger_settings select[data-id="item"]', $trigger_rule_item).append('<option value="'+i+'">'+i+'</option>');
                        });
                    });
                    
                    $('.trigger_settings select[data-id="item"]', $trigger_rule_item).selectpicker('refresh');

                    if (rule.settings.logic !== undefined) $('.trigger_settings input[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings input[data-id="logic"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    if (rule.settings.item !== undefined) $('.trigger_settings select[data-id="item"]', $trigger_rule_item).val(rule.settings.item);
                    $('.trigger_settings select[data-id="item"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'source':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Источник</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker form-control" data-id="logic">',
                                        '<option value="=">=</option>',
                                        '<option value="!=">!=</option>',
                                        '<option value="IN">один из</option>',
                                    '</select>',
                                '</td>',
                                '<td>',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'state':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Состояние</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker form-control" data-id="logic">',
                                        '<option value="=">=</option>',
                                        '<option value="!=">!=</option>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<select data-id="value" class="form-control selectpicker">',
                                        '<option value="inactive">ожидает активации</option>',
                                        '<option value="active">активный</option>',
                                        '<option value="pause">временно отписан</option>',
                                        '<option value="unsubscribe">отписан</option>',
                                        '<option value="blacklist">в блэк-листе (пожаловался на спам)</option>',
                                        '<option value="dropped">невозможно доставить почту</option>',
                                    '</select>',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings select[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings select[data-id="value"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'firstname':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Firstname</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker" data-id="logic">',
                                        '<option value="=">equal</option>',
                                        '<option value="!=">not equal</option>',
                                        '<option value="IN">in</option>',
                                        '<optgroup label="mask">',
                                            '<option value="LIKE">equal</option>',
                                            '<option value="NOT LIKE">not equal</option>',
                                        '</optgroup>',
                                        '<optgroup label="regexp">',
                                            '<option value="REGEXP">equal</option>',
                                            '<option value="NOT REGEXP">not equal</option>',
                                        '</optgroup>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'lastname':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Lastname</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker" data-id="logic">',
                                        '<option value="=">equal</option>',
                                        '<option value="!=">not equal</option>',
                                        '<option value="IN">in</option>',
                                        '<optgroup label="mask">',
                                            '<option value="LIKE">equal</option>',
                                            '<option value="NOT LIKE">not equal</option>',
                                        '</optgroup>',
                                        '<optgroup label="regexp">',
                                            '<option value="REGEXP">equal</option>',
                                            '<option value="NOT REGEXP">not equal</option>',
                                        '</optgroup>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'tel':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Telephone</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker" data-id="logic">',
                                        '<option value="=">equal</option>',
                                        '<option value="!=">not equal</option>',
                                        '<option value="IN">in</option>',
                                        '<optgroup label="mask">',
                                            '<option value="LIKE">equal</option>',
                                            '<option value="NOT LIKE">not equal</option>',
                                        '</optgroup>',
                                        '<optgroup label="regexp">',
                                            '<option value="REGEXP">equal</option>',
                                            '<option value="NOT REGEXP">not equal</option>',
                                        '</optgroup>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'city':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>City</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker" data-id="logic">',
                                        '<option value="=">equal</option>',
                                        '<option value="!=">not equal</option>',
                                        '<option value="IN">in</option>',
                                        '<optgroup label="mask">',
                                            '<option value="LIKE">equal</option>',
                                            '<option value="NOT LIKE">not equal</option>',
                                        '</optgroup>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'country':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Country</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker" data-id="logic">',
                                        '<option value="=">equal</option>',
                                        '<option value="!=">not equal</option>',
                                        '<option value="IN">in</option>',
                                        '<optgroup label="mask">',
                                            '<option value="LIKE">equal</option>',
                                            '<option value="NOT LIKE">not equal</option>',
                                        '</optgroup>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'reg_date':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Дата регистрации</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<input data-id="date_from" class="form-control m-l-5 date-picker-from" type="text" placeholder="Начало">',
                                    '<input data-id="date_to" class="form-control m-l-5 date-picker-to" type="text" placeholder="Конец">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));
                    
                    $('.date-picker-from', $trigger_rule_item).datetimepicker({
                        format: 'YYYY-MM-DD',
                        defaultDate: 0
                    }).on('dp.change', function(e) {
                        objects['reg_date_from'] = e;
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });
                    
                    $('.date-picker-to', $trigger_rule_item).datetimepicker({
                        format: 'YYYY-MM-DD',
                        defaultDate: 0
                    }).on('dp.change', function(e) {
                        objects['reg_date_to'] = e;
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });
                    break;
                case 'social_id':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>VK ID</tr>',
                            '<tr>',
                                '<td style="width: 220px">',
                                    '<select data-id="service">',
                                        '<option value="vk">vk</option>',
                                        '<option value="fb">facebook</option>',
                                        '<option value="ya">yandex</option>',
                                        '<option value="google">google</option>',
                                    '</select>',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td style="width: 220px">',
                                    '<input data-id="logic" class="form-control m-l-5" type="hidden" value="=">',
                                    '<input data-id="value" class="form-control m-l-5" type="text" placeholder="ID">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));
                    
                    if (rule.settings.service !== undefined) $('.trigger_settings select[data-id="service"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="service"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.logic !== undefined) $('.trigger_settings input[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings input[data-id="logic"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'utm':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>',
                                '<td style="width: 140px; padding-right: 10px;">',
                                    'UTM-метка<br>',
                                    '<select data-id="num">',
                                        '<option value="1">Первичная</option>',
                                        '<option value="0">Все</option>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 140px; padding-right: 10px;">',
                                    '<br>',
                                    '<select data-id="item">',
                                        '<option value="source">source</option>',
                                        '<option value="medium">medium</option>',
                                        '<option value="campaign">campaign</option>',
                                        '<option value="term">term</option>',
                                        '<option value="content">content</option>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 200px; padding-right: 10px;">',
                                    '<br><input data-id="value" class="form-control" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));
                    
                    if (rule.settings.num !== undefined) $('.trigger_settings select[data-id="num"]', $trigger_rule_item).val(rule.settings.num);
                    $('body').on('change', $('.trigger_settings select[data-id="num"]', $trigger_rule_item), function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    if (rule.settings.item !== undefined) $('.trigger_settings select[data-id="item"]', $trigger_rule_item).val(rule.settings.item);
                    $('body').on('change', $('.trigger_settings select[data-id="item"]', $trigger_rule_item), function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    break;
                case 'tags':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Метка</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker" data-id="logic">',
                                        '<option value="exist">есть</option>',
                                        '<option value="not_exist">нет</option>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text" placeholder="tag">',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<input data-id="date_from" class="form-control m-l-5 date-picker" type="text" placeholder="Начало">',
                                '</td>',
                                '<td style="width: 100px">',
                                    '<input data-id="date_to" class="form-control m-l-5 date-picker" type="text" placeholder="Конец">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));
                    
                    $('.date-picker', $trigger_rule_item).datetimepicker({
                        format: 'YYYY-MM-DD',
                        defaultDate: new Date()
                    }).on('dp.change', function(e){
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });
                    
                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    break;
                case 'tunnels':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Tunnels</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select data-id="logic">',
                                        '<option value="exist">Подписка на туннель</option>',
                                        '<option value="not_exist">Нет подписки на процесс</option>',
                                        '<option value="active">Туннель активный</option>',
                                        '<option value="complete">Туннель закончен</option>',
                                        '<option value="pause">Туннель на паузе</option>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 200px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));
                    
                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $(document).on('change', $('.trigger_settings select[data-id="value"]', $trigger_rule_item), function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).TunnelSelector({'url':settings.url});

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'tunnels_type':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Tunnels type</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker" data-id="logic">',
                                        '<option value="IN">in</option>',
                                        '<option value="NOT IN">not in</option>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker" data-id="value">',
                                        '<option value="static">статический</option>',
                                        '<option value="dynamic">динамический</option>',
                                    '</select>',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings select[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings select[data-id="value"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    break;
                case 'tunnels_tags':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Tunnels tags</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<input data-id="token" class="form-control m-l-5" type="text" placeholder="token">',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="label" class="form-control m-l-5" type="text" placeholder="label">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.token !== undefined) $('.trigger_settings input[data-id="token"]', $trigger_rule_item).val(rule.settings.token);
                    $('.trigger_settings input[data-id="token"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    if (rule.settings.label !== undefined) $('.trigger_settings input[data-id="label"]', $trigger_rule_item).val(rule.settings.label);
                    $('.trigger_settings input[data-id="label"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'tunnels_queue':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Tunnels queue</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select class="selectpicker" data-id="logic">',
                                        '<option value="IN">in</option>',
                                        '<option value="NOT IN">not in</option>',
                                    '</select>',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    break;
                case 'tunnels_object':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Tunnels object</tr>',
                            '<tr>',
                                '<td style="width: 200px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text" placeholder="tunnel_id">',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="object" class="form-control m-l-5" type="text" placeholder="object">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $(document).on('change', $('.trigger_settings select[data-id="value"]', $trigger_rule_item), function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).TunnelSelector({'url':settings.url});
                    
                    if (rule.settings.object !== undefined) $('.trigger_settings input[data-id="object"]', $trigger_rule_item).val(rule.settings.object);
                    $('.trigger_settings input[data-id="object"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'tunnel_label': 
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>',
                                '<td style="width: 160px; padding-right: 10px;">',
                                    '<select data-id="mode" class="form-control">',
                                        '<option value="exist">есть метка</option>',
                                        '<option value="not_exist">нет метки</option>',
                                    '</select>',
                                '</td>',
                                '<td>',
                                    '<input data-id="tunnel_id" type="text">',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td style="padding-right: 10px;">',
                                    '<br><input data-id="label_id" type="text" class="form-control">',
                                '</td>',
                                '<td>',
                                    '<br><input data-id="label_data" type="text" class="form-control">',
                                '</td>',
                            '</tr>',
                        '</table>',
                        '<div class="panel-group accordion" id="accordion' + elid + '">',
                            '<div class="panel">',
                                '<div class="panel-heading">',
                                    '<h4 class="panel-title">',
                                        '<a data-parent="#accordion' + elid + '" data-toggle="collapse" href="#add_settings' + elid + '">дополнительные условия</a>',
                                    '</h4>',
                                '</div>',
                                '<div class="panel-collapse collapse" id="add_settings' + elid + '">',
                                    '<div class="panel-body">',
                                        'Таймаут',
                                        '<input data-id="timeout-value" value="1" type="text" class="form-control" style="width: 80px; display: inline-block;">',
                                        '<select data-id="timeout-mode" class="form-control">',
                                            '<option value="min">мин.</option>',
                                            '<option value="hours">час.</option>',
                                            '<option value="days">дн.</option>',
                                        '</select>',
                                    '</div>',
                                '</div>',
                            '</div>',
                        '</div>'
                    ].join(''));
                    
                    
                            
                    if (rule.settings.mode !== undefined) $('.trigger_settings select[data-id="mode"]', $trigger_rule_item).val(rule.settings.mode);
                    $('.trigger_settings select[data-id="mode"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.tunnel_id !== undefined) $('.trigger_settings input[data-id="tunnel_id"]', $trigger_rule_item).val(rule.settings.tunnel_id);
                    $(document).on('change', $('.trigger_settings select[data-id="tunnel_id"]', $trigger_rule_item), function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    $('.trigger_settings input[data-id="tunnel_id"]', $trigger_rule_item).TunnelSelector({'url':settings.url});
                    
                    if (rule.settings.label_id !== undefined) $('.trigger_settings input[data-id="label_id"]', $trigger_rule_item).val(rule.settings.label_id);
                    $('.trigger_settings input[data-id="label_id"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    if (rule.settings.label_data !== undefined) $('.trigger_settings input[data-id="label_data"]', $trigger_rule_item).val(rule.settings.label_data);
                    $('.trigger_settings input[data-id="label_data"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    if (rule.settings.timeout !== undefined) { 
                        if (rule.settings.timeout.value !== undefined) $('.trigger_settings input[data-id="timeout-value"]', $trigger_rule_item).val(rule.settings.timeout.value);
                        if (rule.settings.timeout.mode !== undefined) $('.trigger_settings select[data-id="timeout-mode"]', $trigger_rule_item).val(rule.settings.timeout.mode);
                    }
                    
                    $('.trigger_settings input[data-id="timeout-value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    $('.trigger_settings select[data-id="timeout-mode"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'mail_count':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Letter count</tr>',
                            '<tr>',
                                '<td style="width: 220px">',
                                    '<select data-id="logic" class="form-control">',
                                        '<option value="=">=</option>',
                                        '<option value="!=">!=</option>',
                                        '<option value=">">></option>',
                                        '<option value="<"><</option>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 125px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.logic);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'mail_events':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>События в письмах</tr>',
                            '<tr>',
                                '<td style="width: 300px">',
                                    '<div class="col-sm-6">',
                                        '<div class="form-group fg-line">',
                                            '<input data-id="date_from" class="form-control date-picker-from" type="text" placeholder="дата (начало)">',
                                        '</div>',
                                    '</div>',
                                    '<div class="col-sm-6">',
                                        '<div class="form-group fg-line">',
                                            '<input data-id="date_to" class="form-control date-picker-to" type="text" placeholder="дата (конец)">',
                                        '</div>',
                                    '</div>',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<div class="col-sm-4">',
                                        '<div class="form-group fg-line">',
                                            '<select class="selectpicker form-control" data-id="logic" >',
                                                '<option value="=">=</option>',
                                                '<option value="!=">!=</option>',
                                            '</select>',
                                        '</div>',
                                    '</div>',
                                    '<div class="col-sm-8">',
                                        '<div class="form-group fg-line">',
                                            '<select class="selectpicker form-control" data-id="value">',
                                                '<option value="open">открытие</option>',
                                                '<option value="click">клик</option>',
                                                '<option value="delivered">доставка</option>',
                                                '<option value="processed">отправка</option>',
                                                '<option value="unsubscribe">отписка</option>',
                                                '<option value="dropped">dropped</option>',
                                                '<option value="deferred">deferred</option>',
                                                '<option value="bounce">bounce</option>',
                                                '<option value="spamreport">spamreport</option>',
                                            '</select>',
                                        '</div>',
                                    '</div>',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td>',
                                    '<input type="hidden" data-id="letter">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));


                    $('.date-picker-from', $trigger_rule_item).datetimepicker({
                        format: 'YYYY-MM-DD',
                        defaultDate: 0
                    }).on('dp.change', function(e) {
                        objects['mail_events_date_from'] = e;
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });
                    
                    $('.date-picker-to', $trigger_rule_item).datetimepicker({
                        format: 'YYYY-MM-DD',
                        defaultDate: 0
                    }).on('dp.change', function(e) {
                        objects['mail_events_date_to'] = e;
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });

                    if (rule.settings.value !== undefined) $('.trigger_settings select[data-id="value"]', $trigger_rule_item).val(rule.settings.action);
                    $('.trigger_settings select[data-id="value"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.logic !== undefined) $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).val(rule.settings.mode);
                    $('.trigger_settings select[data-id="logic"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    $('.trigger_settings input[data-id="letter"]', $trigger_rule_item).MailingLetterSelector({'url':settings.url});
                    
                    if (rule.settings.letter !== undefined) $('.trigger_settings select[data-id="letter"]', $trigger_rule_item).val(rule.settings.letter);
                    $('.trigger_settings input[data-id="letter"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'mail_user_inactive':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>User inactive</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<input data-id="date_from" class="form-control m-l-5 date-picker" type="text" placeholder="date from">',
                                '</td>',
                                '<td style="width: 100px">',
                                    '<input data-id="date_to" class="form-control m-l-5 date-picker" type="text" placeholder="date to">',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<input data-id="count" class="form-control m-l-5" type="text" placeholder="count">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));
                    
                    $('.date-picker', $trigger_rule_item).datetimepicker({
                        format: 'YYYY-MM-DD',
                        defaultDate: new Date()
                    }).on('dp.change', function(e){
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });
                    
                    if (rule.settings.count !== undefined) $('.trigger_settings input[data-id="count"]', $trigger_rule_item).val(rule.settings.count);
                    $('.trigger_settings input[data-id="count"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    break;
                case 'mail_log':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Mail log</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<select data-id="state" class="form-control m-l-5">',
                                        '<option value="success">success</option>',
                                        '<option value="error">error</option>',
                                    '</select>',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td style="width: 125px">',
                                    '<div class="form-group fg-line">',
                                        '<input data-id="result" class="form-control m-l-5" type="text" placeholder="Result">',
                                    '</div>',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td>',
                                    '<div class="form-group fg-line">',
                                        '<input type="hidden" data-id="letter">',
                                    '</div>',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td style="width: 300px">',
                                    '<div class="col-sm-6">',
                                        '<div class="form-group fg-line">',
                                            '<input data-id="date_from" class="form-control date-picker" type="text" placeholder="Date from">',
                                        '</div>',
                                    '</div>',
                                    '<div class="col-sm-6">',
                                        '<div class="form-group fg-line">',
                                            '<input data-id="date_to" class="form-control date-picker" type="text" placeholder="Date to">',
                                        '</div>',
                                    '</div>',
                                '</td>',
                            '</tr>',
                            
                        '</table>'
                    ].join(''));
                    
                    $('.date-picker', $trigger_rule_item).datetimepicker({
                        format: 'YYYY-MM-DD',
                        defaultDate: new Date()
                    }).on('dp.change', function(e){
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });
                    
                    $('.trigger_settings input[data-id="letter"]', $trigger_rule_item).MailingLetterSelector({'url':settings.url});
                    
                    if (rule.settings.letter !== undefined) $('.trigger_settings input[data-id="letter"]', $trigger_rule_item).val(rule.settings.letter);
                    $('.trigger_settings input[data-id="letter"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    if (rule.settings.result !== undefined) $('.trigger_settings input[data-id="result"]', $trigger_rule_item).val(rule.settings.result);
                    $('.trigger_settings input[data-id="result"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    if (rule.settings.state !== undefined) $('.trigger_settings input[data-id="state"]', $trigger_rule_item).val(rule.settings.state);
                    $('.trigger_settings select[data-id="state"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'mail_open_pct':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Letter open pct</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<input data-id="from" class="form-control m-l-5" type="text" placeholder="0">',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<input data-id="to" class="form-control m-l-5" type="text" placeholder="95">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.from !== undefined) $('.trigger_settings input[data-id="from"]', $trigger_rule_item).val(rule.settings.from);
                    $('.trigger_settings input[data-id="from"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.to !== undefined) $('.trigger_settings input[data-id="to"]', $trigger_rule_item).val(rule.settings.to);
                    $('.trigger_settings input[data-id="to"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'mail_open_pct30':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Letter open pct 30</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<input data-id="from" class="form-control m-l-5" type="text" placeholder="0">',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<input data-id="to" class="form-control m-l-5" type="text" placeholder="95">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.from !== undefined) $('.trigger_settings input[data-id="from"]', $trigger_rule_item).val(rule.settings.from);
                    $('.trigger_settings input[data-id="from"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.to !== undefined) $('.trigger_settings input[data-id="to"]', $trigger_rule_item).val(rule.settings.to);
                    $('.trigger_settings input[data-id="to"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    break;
                case 'mail_open_time':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Letter open time</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text" placeholder="0">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    break;
                case 'product_buy':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Product buy</tr>',
                            '<tr>',
                                '<td style="width: 200px">',
                                    '<input data-id="product" class="form-control m-l-5" type="text" placeholder="product id">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.product !== undefined) $('.trigger_settings select[data-id="product"]', $trigger_rule_item).val(rule.settings.product);
                    $('.trigger_settings input[data-id="product"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    break;
                case 'product_order':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Product order</tr>',
                            '<tr>',
                                '<td style="width: 200px">',
                                    '<input data-id="product" class="form-control m-l-5" type="text" placeholder="product id">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.product !== undefined) $('.trigger_settings select[data-id="product"]', $trigger_rule_item).val(rule.settings.product);
                    $('.trigger_settings input[data-id="product"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    break;
                case 'product_availability':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Product availability</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<input data-id="product" class="form-control m-l-5" type="text" placeholder="product id">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.product !== undefined) $('.trigger_settings select[data-id="product"]', $trigger_rule_item).val(rule.settings.product);
                    $('.trigger_settings input[data-id="product"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    break;
                case 'product_order_sum':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Сумма выручки</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<select class="form-control" data-id="mode" >',
                                        '<option value="=">=</option>',
                                        '<option value=">">></option>',
                                        '<option value="<"><</option>',
                                        '<option value=">=">=</option>',
                                        '<option value="<="><=</option>',
                                    '</select>',
                                '</td>',
                                '<td style="width: 100px">',
                                    '<input data-id="sum" class="form-control m-l-5" type="text" value="0" placeholder="Sum">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));
                    
                    if (rule.settings.mode !== undefined) $('.trigger_settings input[data-id="mode"]', $trigger_rule_item).val(rule.settings.mode);
                    $('.trigger_settings select[data-id="mode"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.sum !== undefined) $('.trigger_settings select[data-id="sum"]', $trigger_rule_item).val(rule.settings.sum);
                    $('.trigger_settings input[data-id="sum"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    break;
                case 'rfm_billing':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>RFM анализ</tr>',
                            '<tr>',
                                '<td>',
                                    '<div class="col-sm-6">',
                                        '<div class="input-group mar-btm">',
                                            '<input id="dates_from" data-id="dates_from" class="form-control dt_picker" type="text">',
                                        '</div>',
                                    '</div>',
                                     '<div class="col-sm-6">',
                                        '<div class="input-group mar-btm">',
                                            '<input id="dates_to" data-id="dates_to" class="form-control dt_picker" type="text" >',
                                        '</div>',
                                    '</div>',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td>',
                                    '<div class="col-sm-6">',
                                        '<div class="input-group mar-btm">',
                                            '<input id="units_from" data-id="units_from" class="form-control" type="text" placeholder="Количество от">',
                                        '</div>',
                                    '</div>',
                                    '<div class="col-sm-6">',
                                        '<div class="input-group mar-btm">',
                                            '<input id="units_to" data-id="units_to" class="form-control" type="text" placeholder="Количество до">',
                                        '</div>',
                                    '</div>',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));
                    
                    if (rule.settings.dates_from !== undefined) $('.trigger_settings input[data-id="dates_from"]', $trigger_rule_item).val(rule.settings.dates_from);
                    $('#dates_from', $trigger_rule_item).datetimepicker({
                        format: 'DD-MM-YYYY',
                    }).on('dp.change', function(e){
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });
                    
                    if (rule.settings.dates_to !== undefined) $('.trigger_settings input[data-id="dates_to"]', $trigger_rule_item).val(rule.settings.dates_to);
                    $('#dates_to', $trigger_rule_item).datetimepicker({
                        format: 'DD-MM-YYYY',
                        defaultDate: new Date()
                    }).on('dp.change', function(e){
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });

                    if (rule.settings.units_from !== undefined) $('.trigger_settings input[data-id="units_from"]', $trigger_rule_item).val(rule.settings.units_from);
                    $('.trigger_settings input[data-id="units_from"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.units_to !== undefined) $('.trigger_settings input[data-id="units_to"]', $trigger_rule_item).val(rule.settings.units_to);
                    $('.trigger_settings input[data-id="units_to"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    break;
                case 'rfm_mail':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>RFM анализ (письма)</tr>',
                            '<tr>',
                                '<td style="width: 100px">',
                                    '<div class="col-sm-12">',
                                        '<select class="form-control" data-id="event" >',
                                            '<option value="open">Open</option>',
                                            '<option value="click">Click</option>',
                                        '</select>',
                                    '</div>',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td>',
                                    '<div class="col-sm-6">',
                                        '<div class="input-group mar-btm">',
                                            '<input id="dates_from" data-id="dates_from" class="form-control dt_picker" type="text">',
                                        '</div>',
                                    '</div>',
                                     '<div class="col-sm-6">',
                                        '<div class="input-group mar-btm">',
                                            '<input id="dates_to" data-id="dates_to" class="form-control dt_picker" type="text" >',
                                        '</div>',
                                    '</div>',
                                '</td>',
                            '</tr>',
                            '<tr>',
                                '<td>',
                                    '<div class="col-sm-6">',
                                        '<div class="input-group mar-btm">',
                                            '<input id="units_from" data-id="units_from" class="form-control" type="text" placeholder="Количество от">',
                                        '</div>',
                                    '</div>',
                                    '<div class="col-sm-6">',
                                        '<div class="input-group mar-btm">',
                                            '<input id="units_to" data-id="units_to" class="form-control" type="text" placeholder="Количество до">',
                                        '</div>',
                                    '</div>',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));
                    
                    if (rule.settings.dates_from !== undefined) $('.trigger_settings input[data-id="dates_from"]', $trigger_rule_item).val(rule.settings.dates_from);
                    $('input[data-id="dates_from"]', $trigger_rule_item).datetimepicker({
                        format: 'DD-MM-YYYY',
                    }).on('dp.change', function(e){
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });
                    
                    if (rule.settings.dates_to !== undefined) $('.trigger_settings input[data-id="dates_to"]', $trigger_rule_item).val(rule.settings.dates_to);
                    $('input[data-id="dates_to"]', $trigger_rule_item).datetimepicker({
                        format: 'DD-MM-YYYY',
                        defaultDate: new Date()
                    }).on('dp.change', function(e){
                        $target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))));
                    });
                    
                    if (rule.settings.event !== undefined) $('.trigger_settings select[data-id="event"]', $trigger_rule_item).val(rule.settings.event);
                    $('.trigger_settings select[data-id="event"]', $trigger_rule_item).on('change', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.units_from !== undefined) $('.trigger_settings input[data-id="units_from"]', $trigger_rule_item).val(rule.settings.units_from);
                    $('.trigger_settings input[data-id="units_from"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    if (rule.settings.units_to !== undefined) $('.trigger_settings input[data-id="units_to"]', $trigger_rule_item).val(rule.settings.units_to);
                    $('.trigger_settings input[data-id="units_to"]', $trigger_rule_item).on('input propertychange paste', function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});

                    break;
                case 'group':
                    $('.trigger_settings', $trigger_rule_item).append([
                        '<table>',
                            '<tr>Группы</tr>',
                            '<tr>',
                                '<td style="width: 200px">',
                                    '<input data-id="value" class="form-control m-l-5" type="text" placeholder="group_id">',
                                '</td>',
                            '</tr>',
                        '</table>'
                    ].join(''));

                    if (rule.settings.value !== undefined) $('.trigger_settings input[data-id="value"]', $trigger_rule_item).val(rule.settings.value);
                    $(document).on('change', $('.trigger_settings select[data-id="value"]', $trigger_rule_item), function(){$target_rules.val($.toJSON(methods.render_value($('#trigger_rules_editor > .trigger_children > .trigger_rule'))))});
                    
                    $('.trigger_settings input[data-id="value"]', $trigger_rule_item).GroupSelector({'url':settings.url});
                    break;
            }
        }
    };

    $.fn.RefRulesEditor = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('RefRulesEditor: Unknown method: ' +  method);
        }
    };
})(jQuery);
