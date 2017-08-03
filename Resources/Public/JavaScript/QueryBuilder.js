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
	 * @type {{selectorBuilderPosition: string, selectorBuilderContainer: string, selectorBuilder: string, template: string, table: (*), querySelector: null, instance: null, plugins: {bt-tooltip-errors: {delay: number}, invert: {}, filter-description: {icon: string}}, icon: string, filters: [*], basicRules: {condition: string, rules: Array}, buttons: [*]}}
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
		querySelector: null,
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
				title: TYPO3.lang['button.apply'] || 'Apply',
				action: 'apply'
			},
			{
				title: TYPO3.lang['button.reset'] || 'Reset',
				action: 'reset'
			},
			{
				title: TYPO3.lang['button.save'] || 'Save query',
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
		QueryBuilder.querySelector = $('#t3js-querybuilder-recent-queries');
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
		var $queryHeader = $( '<h3>' + TYPO3.lang['recent.header'] + '</h3>' || '<h3>Saved queries</h3>');
		$queryHeader.prependTo($queryContainer);
		QueryBuilder.initializeRecentQueries(QueryBuilder.querySelector);
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
		var queryName = $('option[value=' + QueryBuilder.querySelector.val() + ']', QueryBuilder.querySelector).text() || '';
		$list.append(
			$('<dt />').text(TYPO3.lang['modal.headline'] || "Save your Query"),
			$('<dd />').append(
				$('<label />', {for: 'queryname'}).text(TYPO3.lang['modal.queryname'] || 'Name: '),
				$('<input />', {name: 'queryname', class: 'form-control', value: queryName})
			),
			$('<dt />').text(TYPO3.lang['recent.header'] || 'Saved queries'),
			$('<dd />').append(
				$('#t3js-querybuilder-recent-queries').clone()
					.attr('id', null)
			)
		);
		var queryBuilderAjaxUrl = TYPO3.settings.ajaxUrls.querybuilder_save_query;
		Modal.show(
			TYPO3.lang['modal.title'] || "Querybuilder - Save query",
			$list,
			Severity.info,
			[{
				text: TYPO3.lang['modal.cancel'] || 'Cancel',
				active: true,
				btnClass: 'btn-default',
				name: 'ok',
				trigger: function () {
					Modal.currentModal.trigger('modal-dismiss');
				}
			},
			{
				text: TYPO3.lang['modal.save'] || 'Save',
				active: true,
				btnClass: 'btn-info',
				name: 'ok',
				trigger: function () {
					if ($('input[name=queryname]', Modal.currentModal).val() === '') {
						$('input[name=queryname]', Modal.currentModal).parent().addClass('has-error');
						return;
					}
					var $modalSelect = $('select[name=recent-queries]', Modal.currentModal);
					var queryName = $('input[name=queryname]', Modal.currentModal).val();
					var uid = $modalSelect.val();
					var override = uid !== -1 ? 1 : 0;
					var query = JSON.stringify(QueryBuilder.instance.queryBuilder('getRules'), null, 2);
					$.ajax({
						url: queryBuilderAjaxUrl,
						cache: false,
						data: {
							table: QueryBuilder.table,
							query: query,
							queryName: queryName,
							uid: uid,
							override: override
						},
						success: function(data) {
							if (data.status === 'ok') {
								Modal.currentModal.trigger('modal-dismiss');
								Notification.success(TYPO3.lang['modal.success.headline'] || 'Query saved', TYPO3.lang['modal.success.text'] || 'Your query has been saved');
								if (override) {
									$('option[value="' + uid + '"]', QueryBuilder.querySelector).text(queryName);
								} else {
									var $query = $('<option />', {value: data.uid, 'data-query': query}).text(queryName);
									QueryBuilder.querySelector.append($query);
									QueryBuilder.querySelector.val('' + data.uid);
								}
							}
						}
					});
				}
			}]
		)
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
