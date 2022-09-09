.. include:: ../Includes.txt

.. _upgrading:


Upgrading from below version 6
==============================

Since version 6 of pw_teaser, which is available for TYPO3 10 and 11 only, some changes in Templates occured you need
to change, when upgrading to version 6.0 or higher.


Pagination
----------

pw_teaser used to use the paginate Fluid widget provided by TYPO3 CMS. Those widgets has been removed from core, instead
you can use the new Pagination API.

Here is a minimum example, which replaces the previous ``widget.paginate`` call:

.. code-block:: html

	<f:variable name="pages">{pages}</f:variable>
	<f:if condition="{settings.enablePagination}">
		<f:variable name="pages">{pagination.paginator.paginatedItems}</f:variable>
	</f:if>

	<f:for each="{pages}" as="page">
		<div>{page.title}</div>
	</f:for>

    <f:if condition="{settings.enablePagination}">
		<f:render partial="Pagination" arguments="{pagination: pagination.pagination, paginator: pagination.paginator}" />
	</f:if>


You can disable the pagination in the plugin settings.
By default it is enabled and the amount of ``settings.itemsPerPage`` is 10.
