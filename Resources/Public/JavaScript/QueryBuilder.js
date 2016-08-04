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
define(['jquery', 'twbs/bootstrap-datetimepicker', 'query-builder'], function($) {
    'use strict';

    /**
     *
     * @type {{selectorBuilderPoistion: string, selectorBuilderContainer: string, selectorBuilder: string, template: string, instance: null, plugins: string[], filters: *[], basicRules: {condition: string, rules: Array}, buttons: *[]}}
     * @exports TYPO3/CMS/Querybuilder/QueryBuilder
     */
    var QueryBuilder = {
        selectorBuilderPoistion: '.t3js-module-body h1',
        selectorBuilderContainer: '.t3js-querybuilder',
        selectorBuilder: '.t3js-querybuilder-builder',
        template: '<div class="t3js-querybuilder"><div class="t3js-querybuilder-builder"></div><div class="btn-group"></div></div>',
        instance: null,
        plugins: {
            'bt-tooltip-errors': { delay: 100 },
            'sortable': { icon: 'fa fa-sort' },
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
            }
        ]
    };

    /**
     * Initialize method
     */
    QueryBuilder.initialize = function(rules, filter) {
        $(QueryBuilder.template).insertAfter(QueryBuilder.selectorBuilderPoistion);
        var $queryBuilderContainer = $(QueryBuilder.selectorBuilderContainer);
        if (QueryBuilder.buttons.length > 0) {
            var $buttonGroup = $queryBuilderContainer.find('.btn-group');
            for (var i=0; i<QueryBuilder.buttons.length; i++) {
                var button = QueryBuilder.buttons[i];
                var $button = $('<button type="button" class="btn btn-default" data-action="' + button.action + '">' + button.title + '</button>');
                $button.appendTo($buttonGroup);
            }
        }
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
        QueryBuilder.initialzeEvents();
    };

    /**
     *
     */
    QueryBuilder.initialzeEvents = function() {
        $(QueryBuilder.selectorBuilder).parent().find('.btn-group button').click(function() {
            var $button = $(this);
            var action = $button.data('action');
            switch (action) {
                case 'apply':
                    var url = self.location.href;
                    if (url.indexOf('&query=') !== -1) {
                        url = url.substring(0, url.indexOf('&query='));
                    }
                    self.location.href = url + '&query=' + JSON.stringify(QueryBuilder.instance.queryBuilder('getRules'), null, 2);
                    break;
            }
        });
    };

    return QueryBuilder;
});
