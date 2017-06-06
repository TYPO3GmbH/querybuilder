/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: TYPO3/CMS/Querybuilder/QueryBuilder
 * Javascript functions regarding the permissions module
 */
define(['jquery', 'moment','TYPO3/CMS/Backend/Severity','TYPO3/CMS/Backend/Storage','TYPO3/CMS/Backend/Modal','twbs/bootstrap-datetimepicker','query-builder'], function($, moment, Severity, Storage, Modal) {
    'use strict';

    /**
     *
     * @type {{selectorBuilderPosition: string, selectorBuilderContainer: string, selectorBuilder: string, template: string, instance: null, plugins: string[], filters: *[], basicRules: {condition: string, rules: Array}, buttons: *[]}}
     * @exports TYPO3/CMS/Querybuilder/QueryBuilder
     */
    var QueryBuilder = {
        selectorBuilderPosition: '.t3js-module-body h1',
        selectorBuilderContainer: '.t3js-querybuilder',
        selectorBuilder: '.t3js-querybuilder-builder',
        template: '<div class="t3js-querybuilder"><div class="t3js-querybuilder-builder"></div><div class="btn-group"></div></div>',
        table: $('table[data-table]').data('table'),
		instance: null,
        plugins: {
            'bt-tooltip-errors': { delay: 100 },
            //'sortable': { icon: 'fa fa-sort' },
            'invert': {},
            'filter-description': { icon: 'fa fa-info' }
        },
        icon: 'fa fa-sort',
        // Filter:Types: string, integer, double, date, time, datetime and boolean.
        // Filter:Required: id, type, values*
        filters: [{
            id: 'title',
            label: 'Title',
            type: 'string',
            description: 'foo'
        }],
        basicRules: {
            condition: 'AND',
            rules:[]
        },
        buttons: [
            {
                title: 'Apply',
                action: 'apply'
            },
			{
				title: 'Reset',
				action: 'reset'
			},
			{
				title: 'Save query',
				action: 'save'
			}
        ]
    };

    /**
     * Initialize method
     */
    QueryBuilder.initialize = function(rules, filter) {
        // Add moment to the global windows space, because query-builder checks again window.moment
        window.moment = moment;
        $(QueryBuilder.template).insertAfter(QueryBuilder.selectorBuilderPosition);
        var $queryBuilderContainer = $(QueryBuilder.selectorBuilderContainer);
        if (QueryBuilder.buttons.length > 0) {
            var $buttonGroup = $queryBuilderContainer.find('.btn-group');
            for (var i=0; i<QueryBuilder.buttons.length; i++) {
                var button = QueryBuilder.buttons[i];
                var $button = $('<button type="button" class="btn btn-default" data-action="' + button.action + '">' + button.title + '</button>');
                $button.appendTo($buttonGroup);
            }
        }
		QueryBuilder.initializeEvents();
        QueryBuilder.instance = $queryBuilderContainer.find(QueryBuilder.selectorBuilder).queryBuilder({
            allow_empty: true,
            icons: {
                'add_group': 'fa fa-plus-circle',
                'add_rule': 'fa fa-plus',
                'remove_group': 'fa fa-minus-circle',
                'remove_rule': 'fa fa-minus-circle',
                'error': 'fa fa-warning'
            },
            plugins: QueryBuilder.plugins,
            filters: filter.length ? filter : QueryBuilder.filters,
            rules: rules || QueryBuilder.basicRules
        });
		var lastQuery = QueryBuilder.getStoredQuery();
		if (lastQuery !== null) {
			try {
				lastQuery = JSON.parse(lastQuery);
				QueryBuilder.instance.queryBuilder('setRules', lastQuery);
 			} catch (e) {
				console.log(e);
			}
		}
	};

    /**
     *
     */
    QueryBuilder.initializeEvents = function() {
        var $builderElement = $(QueryBuilder.selectorBuilder);
        $builderElement.on('afterCreateRuleInput.queryBuilder', function(e, rule) {
            if (rule.filter.plugin === 'datetimepicker') {
                var $input = rule.$el.find('.rule-value-container [name*=_value_]');
                $input.on('dp.change', function() {
                    $input.trigger('change');
                });
            }
        });
        $builderElement.parent().find('.btn-group button').click(function() {
            var $button = $(this);
            var action = $button.data('action');
			var url = self.location.href;
            switch (action) {
                case 'apply':
                    if (!QueryBuilder.instance.queryBuilder('validate')) {
                        break;
                    }
                    if (url.indexOf('&query=') !== -1) {
                        url = url.substring(0, url.indexOf('&query='));
                    }
					var storage = Storage.Client;
					var configuration = JSON.stringify(QueryBuilder.instance.queryBuilder('getRules'), null, 2);
					//storage.set('extkey-query-' + QueryBuilder.table, configuration);
                    self.location.href = url + '&query=' + configuration;
                    break;
				case 'reset':
					if (!QueryBuilder.instance.queryBuilder('validate')) {
						break;
					}
					if (url.indexOf('&query=') !== -1) {
						url = url.substring(0, url.indexOf('&query='));
					}
					self.location.href = url;
					break;
				case 'save':
					var $list = $('<dl></dl>');
					$list.append(
						$('<dt />').text("Save your Query"),
						$('<dd />').append(
							$('<label />', {for:'queryname'}).text('Name: '),
							$('<input />', {name:'queryname'})
						)
					);
					Modal.show(
						"test",
						$list,
						Severity.info,
						[{
							text: 'Cancel',
							active: true,
							btnClass: 'btn-default',
							name: 'ok',
							trigger: function() {
								Modal.currentModal.trigger('modal-dismiss');
							}
						},
						{
							text: 'Save',
							active: true,
							btnClass: 'btn-info',
							name: 'ok',
							trigger: function() {
								$.ajax({
									url: TYPO3.settings.ajaxUrls['querybuilder_save_query'],
									cache: false,
									data: {
										table: QueryBuilder.table,
										query: JSON.stringify(QueryBuilder.instance.queryBuilder('getRules'), null, 2)
										//queryname: $('input[name=queryname]').data('value')
									}
								});
							}
						}],
						['modal-inner-scroll']
					);
					break;
            }
        });
		$builderElement.on('keyup', '.rule-value-container input, .rule-operator-container select', function(e) {
			if (e.which === 13) {
				var url = self.location.href;
				if (QueryBuilder.instance.queryBuilder('validate')) {
					if (url.indexOf('&query=') !== -1) {
						url = url.substring(0, url.indexOf('&query='));
					}
					self.location.href = url + '&query=' + JSON.stringify(QueryBuilder.instance.queryBuilder('getRules'), null, 2);
				}
			}
		});
    };

	QueryBuilder.getStoredQuery = function() {
		return Storage.Client.get('extkey-query-' + QueryBuilder.table);
	};

    return QueryBuilder;
});
