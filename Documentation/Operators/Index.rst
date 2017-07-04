Operators
=========

The are different operators for different types of data, enabling filtering as the editor wishes.
In general the TCA definition of the field defines the possible operators as e.g. `greater` is only possible for numbers.
The concrete list of possible inputs:

    equal:            'string', 'number', 'datetime', 'boolean'
    not_equal:        'string', 'number', 'datetime', 'boolean'
    in:               'string', 'number', 'datetime'
    not_in:           'string', 'number', 'datetime'
    less:             'number', 'datetime'
    less_or_equal:    'number', 'datetime'
    greater:          'number', 'datetime'
    greater_or_equal: 'number', 'datetime'
    between:          'number', 'datetime'
    not_between:      'number', 'datetime'
    begins_with:      'string'
    not_begins_with:  'string'
    contains:         'string'
    not_contains:     'string'
    ends_with:        'string'
    not_ends_with:    'string'
    is_empty:         'string'
    is_not_empty:     'string'
    is_null:          'string', 'number', 'datetime', 'boolean'
    is_not_null:      'string', 'number', 'datetime', 'boolean'
