Administration
==============

In general every query is saved on pid = 0. This means only admin-user can create, edit or delete without using
the querybuilder functions itself in the list module.

.. figure:: ../Images/Administration-Querybuilder.png

   query administration on pid = 0

Editing saved queries
---------------------

Be careful while editing given queries!
Wrong editing could cause the querybuilder to not filter properly.

Also adapting the user or the affected table could cause some trouble:

Changing the affected table of a query which is filtering the a field that does not exist in the new table throws an exception!

Changing the user does not directly influence the usability, but makes the query not usable for the actual owner (which is
of course not very polite).
