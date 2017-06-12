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
define(['jquery',
		'moment',
		'TYPO3/CMS/Backend/Severity',
		'TYPO3/CMS/Backend/Storage',
		'TYPO3/CMS/Backend/Modal',
		'TYPO3/CMS/Backend/Notification',
		'twbs/bootstrap-datetimepicker',
		'query-builder'
		], function ($, moment, Severity, Storage, Modal, Notification) {
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
		template: '<div class="t3js-querybuilder">' +
					'<div class="t3js-querybuilder-builder"></div>' +
					'<div class="btn-group"></div>' +
					'<div class="t3js-querybuilder-queries">' +
						'<select name="recent-queries" class="form-control" id="t3js-querybuilder-recent-queries">' +
							'<option class="first-opt" value="-1"></option>' +
						'</select>' +
					'</div>' +
				   '</div>',
		table: $('table[data-table]').data('table'),
		instance: null,
		plugins: {
			'bt-tooltip-errors': {delay: 100},
			//'sortable': { icon: 'fa fa-sort' },
			'invert': {},
			'filter-description': {icon: 'fa fa-info'}
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
			rules: []
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
	QueryBuilder.initialize = function (rules, filter) {
		// Add moment to the global windows space, because query-builder checks again window.moment
		window.moment = moment;
		QueryBuilder.table = $('table[data-table]').data('table') || QueryBuilder.getUrlVars()['table'];
		$(QueryBuilder.template).insertAfter(QueryBuilder.selectorBuilderPosition);
		var $queryBuilderContainer = $(QueryBuilder.selectorBuilderContainer);
		if (QueryBuilder.buttons.length > 0) {
			var $buttonGroup = $queryBuilderContainer.find('.btn-group');
			for (var i = 0; i < QueryBuilder.buttons.length; i++) {
				var button = QueryBuilder.buttons[i];
				var $button = $('<button type="button" class="btn btn-default" data-action="' + button.action + '">' + button.title + '</button>');
				$button.appendTo($buttonGroup);
			}
		}

		//QueryBuilder.initializeQueries();
		var $queryContainer = $queryBuilderContainer.find('.t3js-querybuilder-queries');
		var $queryHeader = $('<h3> Saved queries</h3>');
		$queryHeader.prependTo($queryContainer);
		var $querySelector = $('#t3js-querybuilder-recent-queries');
		QueryBuilder.initializeRecentQueries($querySelector);
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
				//QueryBuilder.applyFilter();
			} catch (err) {
				console.log(err.message);
			}
		}
	};

	QueryBuilder.getUrlVars = function() {
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++)
		{
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	};

	/**
	 *
	 */
	QueryBuilder.initializeEvents = function () {
		var $builderElement = $(QueryBuilder.selectorBuilder);
		$builderElement.on('afterCreateRuleInput.queryBuilder', function (e, rule) {
			if (rule.filter.plugin === 'datetimepicker') {
				var $input = rule.$el.find('.rule-value-container [name*=_value_]');
				$input.on('dp.change', function () {
					$input.trigger('change');
				});
			}
		});
		$builderElement.parent().find('.btn-group button').click(function () {
			var $button = $(this);
			var action = $button.data('action');
			var url = self.location.href;
			switch (action) {
				case 'apply':
					var configuration = JSON.stringify(QueryBuilder.instance.queryBuilder('getRules'), null, 2);
					QueryBuilder.setStoredQuery(configuration);
					QueryBuilder.applyFilter(configuration);
					break;
				case 'reset':
					if (!QueryBuilder.instance.queryBuilder('validate')) {
						break;
					}
					if (url.indexOf('&query=') !== -1) {
						url = url.substring(0, url.indexOf('&query='));
					}
					QueryBuilder.setStoredQuery(null);
					self.location.href = url;
					break;
				case 'save':
					QueryBuilder.showSaveModal();
					break;
			}
		});
		$builderElement.on('keyup', '.rule-value-container input, .rule-operator-container select', function (e) {
			if (e.which === 13) {
				var configuration = JSON.stringify(QueryBuilder.instance.queryBuilder('getRules'), null, 2);
				QueryBuilder.setStoredQuery(configuration);
				QueryBuilder.applyFilter(configuration);
			}
		});
	};

	QueryBuilder.initializeRecentQueries = function($querySelector) {
		$.ajax({
			url: TYPO3.settings.ajaxUrls.querybuilder_get_recent_queries,
			cache: false,
			data: {
				table: QueryBuilder.table
			},
			success: function(data) {
				for (var j = 0; j < data.length; j++) {
					var $query = $('<option />', {value: data[j].uid, 'data-query': data[j].where_parts}).text(data[j].queryname);
					$querySelector.append($query);
				}
				$querySelector.on('change', function() {
					var $option = $(this.options[this.selectedIndex]);
					try {
						QueryBuilder.instance.queryBuilder('setRules', $option.data('query'));
					} catch (err) {
						console.log(err.message);
					}
				});
			}
		});
	};

	QueryBuilder.showSaveModal = function() {
		var $list = $('<dl></dl>');
		// get current query name as default value for the modal
		var queryName = $('#t3js-querybuilder-recent-queries option[value=' + $('#t3js-querybuilder-recent-queries').val() + ']').text() || '';
		$list.append(
			$('<dt />').text("Save your Query"),
			$('<dd />').append(
				$('<label />', {for: 'queryname'}).text('Name: '),
				$('<input />', {name: 'queryname', class: 'form-control', value: queryName})
			),
			$('<dd />').append(
				$('<div />', {class: 'checkbox'}).append(
					$('<label />').append(
						$('<input />', {name: 'override', type: 'checkbox'}),
						$('<span />').text('Override saved query?')
					)
				)
			)
		);
		var queryBuilderAjaxUrl = TYPO3.settings.ajaxUrls.querybuilder_save_query;
		Modal.show(
			"Querybuilder - Save query",
			$list,
			Severity.info,
			[{
				text: 'Cancel',
				active: true,
				btnClass: 'btn-default',
				name: 'ok',
				trigger: function () {
					Modal.currentModal.trigger('modal-dismiss');
				}
			},
			{
				text: 'Save',
				active: true,
				btnClass: 'btn-info',
				name: 'ok',
				trigger: function () {
					if ($('input[name=queryname]', Modal.currentModal).val() === '') {
						$('input[name=queryname]', Modal.currentModal).parent().addClass('has-error');
						return;
					}
					//if ($('#t3js-querybuilder-recent-queries').val() < 1) {
					//	$('input[name=override]', Modal.currentModal).style.visibility = "hidden";
					//}
					$.ajax({
						url: queryBuilderAjaxUrl,
						cache: false,
						data: {
							table: QueryBuilder.table,
							query: JSON.stringify(QueryBuilder.instance.queryBuilder('getRules'), null, 2),
							queryName: $('input[name=queryname]', Modal.currentModal).val(),
							uid: $('#t3js-querybuilder-recent-queries').val(),
							override: $('input[name=override]', Modal.currentModal).is(':checked') ? $('input[name=override]', Modal.currentModal).val() : 0
						},
						success: function(data) {
							if (data.status === 'ok') {
								Modal.currentModal.trigger('modal-dismiss');
								Notification.success('Query saved', 'Your query was saved');
								var savedquery = JSON.stringify(QueryBuilder.instance.queryBuilder('getRules'), null, 2);
								QueryBuilder.setStoredQuery(savedquery);
								$( "option" ).remove( "[data-query]" );
								var $querySelector = $('#t3js-querybuilder-recent-queries');
								QueryBuilder.initializeRecentQueries($querySelector);
							} else {
								Modal.currentModal.trigger('modal-dismiss');
								Notification.error('Query not saved', 'Sorry, your query can\'t be saved');
							}
						}
					});
				}
			}]
		);
	};

	QueryBuilder.applyFilter = function(configuration) {
		if (!QueryBuilder.instance.queryBuilder('validate')) {
			return;
		}
		var url = self.location.href;
		if (url.indexOf('&query=') !== -1) {
			url = url.substring(0, url.indexOf('&query='));
		}
		self.location.href = url + '&query=' + configuration;
	};

	QueryBuilder.getStoredQuery = function () {
		return Storage.Client.get('querybuilder-query-' + QueryBuilder.table);
	};

	QueryBuilder.setStoredQuery = function (data) {
		Storage.Client.set('querybuilder-query-' + QueryBuilder.table, data);
	};

	return QueryBuilder;
});
