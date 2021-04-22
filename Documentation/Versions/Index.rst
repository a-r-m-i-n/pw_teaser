.. include:: ../Includes.txt


.. _versions:

Versions
========

.. contents:: :local:

5.0.1
-----

- [BUGFIX] Fix wrong extension icon path
- [DEVOPS] Update DDEV install scripts (for TYPO3 environments)
- [BUGFIX] Do not show warning in ItemsProcFunc, if pw_teaser typoscript is missing
- [BUGFIX] Fix wrong Extbase mapping (for TYPO3 10+)
- [TASK] Update README


5.0.0
-----

- [FEATURE] Documentation
- [BUGFIX] Remove default value for setting "hideCurrentPage" in teaser flexform
- [TASK] Set templateType by default to "preset"
- [TASK] Remove "ENABLECACHE" extension setting
- [BUGFIX] Fix image viewhelper usage in Fluid template
- [FEATURE] Template presets
- [BUGFIX] Merge view settings from plugin with typoscript
- [TASK] Set label for default value in dropdowns
- [BUGFIX] Streamline "templateRootPaths" setting
- [TASK] Update copyrights
- [TASK] Code style fixing
- [BUGFIX] Custom fluid templates not working because of missing templateRootPath
- [TASK] Move ext_tables.php contents to TCA/Overrides
- [FEATURE] Add TYPO3 10 compatibility
- Dropped support for TYPO3 8.7


4.0.0
-----

- [BUGFIX] No not escape output of GetContent ViewHelper
- [CHANGE][!!!] Introduce {page.get}
- [TASK] Compatibility Fixes
- [TASK] Set version to 4.0.0-dev and update package name to "t3/pw_teaser"
- Dropped support for TYPO3 6.2 and 7.6

3.4.2
-----

- [TASK] Fix license attribute in composer.json
- [BUGFIX] Fix missing index attributes in numIndex nodes


3.4.1
-----

- [BUGFIX] Check for version 7.5 to use IconRegistry, fallback for TYPO3 6.2


3.4.0
-----

- Add support for TYPO3 7.6 to 8.x. Also added content element wizard entry.


3.3.0
-----

Add new option "pageMode". Normally pages are passed as flat array to fluid template.
If this option is set to "nested" you get a nested page object with (new attribute) childPages.


3.2.0
-----

- Add new option "recursionDepthFrom" to define a start for recursion.


3.1.0
-----

Page and content model in pw_teaser does not have properties for all available columns in the accordant tables.
With this patch, everytime you try to access such attribute the whole row of the page/content will be loaded (and cached)
and the values will be accessible.

Example in fluid template:

::

    <f:for each="{pages}" as="page">
        Page Layout: {page.layout}
    </f:for>

Layout is one of these attributes, existing in pages table but not in page model.
So this example will not work with previous versions of pw_teaser.
Furthermore now also attributes added by other extensions are accessible.


3.0.0
-----

- Complete TYPO3 6.2 support
- Massive refactoring of all classes (eg. namespaces)
- Add language support (behavior of teasers like menu items in TYPO3)
- Add categories attribute to Page and Content model
- $page->getMedia() and $content->getImage() works with FAL (To work in template use $page->getMediaFiles() or $content->getImageFiles())
- Add option to plugin to filter pages by categories
- Add option to plugin to define recursion depth
- Add getRootLine() and getRootLineDepth() to Page model
- New extension/plugin icon
- New Signal/Slot to modify found pages
- Many bugfixes
