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


Routing configuration for pagination
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Copy the following routing enhancer config to your site configuration, to get beautified page links.

.. code-block:: yaml

	routeEnhancers:
	  PwTeaser:
		type: Extbase
		extension: PwTeaser
		plugin: Pi1
		routes:
		  - routePath: '/'
			_controller: 'Teaser::index'
		  - routePath: '/{label-page}-{page}'
			_controller: 'Teaser::index'
			_arguments:
			  page: 'currentPage'
		defaultController: 'Teaser::index'
		defaults:
		  page: '0'
		requirements:
		  page: '\d+'
		aspects:
		  page:
			type: StaticRangeMapper
			start: '1'
			end: '999'
		  label-page:
			type: LocaleModifier
			default: 'page'
			localeMap:
			  -   locale: 'de_.*'
				  value: 'seite'


Events
------

Previous versions of pw_teaser provided a Signal to programmatically modify the pages result array.
Since version 6 those Signals has been replaced with `Events <events>`.
