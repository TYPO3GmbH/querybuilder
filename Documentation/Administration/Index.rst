Administration
==============

In general every query is saved on pid = 0. This means only admin-user can create, edit or delete without using
the querybuilder functionality itself in the list module.

.. figure:: ../Images/Administration-Querybuilder.png

   query administration on pid = 0

Editing saved queries
---------------------

Be careful while editing given queries!
Wrong editing could cause the querybuilder to not filter properly.

Adapting the user, affected table or actual query itself could cause some trouble:

- **Table:** Changing the affected table of a query, which is filtering a field, that does not exist in the new table, won't work for the actual filter.

- **User:** Changing the user does not directly influence the functionality, but makes the query not usable for the actual owner (which is of course annoying).

- **Rules:** Last but not least changing the query rules/groups itself. In general you should not adapt the queries directly and use the given save/override implementation. If you still feel in need to change it, you should be careful and know what you are doing. Changing for example the field to a non-existent field in the table won't work for the filter.

.. tip::

   In general: You should only delete queries on pid = 0 and be careful, if you really have to edit them.
