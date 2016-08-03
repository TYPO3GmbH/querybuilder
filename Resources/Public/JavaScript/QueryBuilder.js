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
define(['jquery', 'query-builder'], function($) {
    'use strict';

    /**
     *
     * @type {{selectorTable: string, selectorBuilder: string, template: string, instance: QueryBuilder, plugins: string[], filters: *[], basicRules: {condition: string, rules: Array}, buttons: *[]}}
     * @exports TYPO3/CMS/Querybuilder/QueryBuilder
     */
    var QueryBuilder = {
        selectorTable: '.panel.recordlist',
        selectorBuilder: '.t3js-querybuilder',
        template: '<div class="col-md-12 col-lg-10 col-lg-offset-1"><div class="t3js-querybuilder"></div><div class="btn-group"></div></div>',
        instance: null,
        plugins: ['bt-tooltip-errors'],
        filters: [{
            id: 'title',
            label: 'Title',
            type: 'string'
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
    QueryBuilder.initialize = function(rules) {
        console.log(rules);
        $(QueryBuilder.selectorTable).prepend(QueryBuilder.template);
        var $queryBuilderContainer = $(QueryBuilder.selectorBuilder);
        if (QueryBuilder.buttons.length > 0) {
            var $buttonGroup = $queryBuilderContainer.parent().find('.btn-group');
            for (var i=0; i<QueryBuilder.buttons.length; i++) {
                var button = QueryBuilder.buttons[i];
                var $button = $('<button type="button" class="btn btn-default" data-action="' + button.action + '">' + button.title + '</button>');
                $button.appendTo($buttonGroup);
            }
        }
        QueryBuilder.instance = $queryBuilderContainer.queryBuilder({
            allow_empty: true,
            plugins: QueryBuilder.plugins,
            filters: QueryBuilder.filters,
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
