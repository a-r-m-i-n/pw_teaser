.. include:: ../Includes.txt

.. _using-typoscript:


Using TypoScript
================

.. contents:: :local:


Predefine settings with TS
--------------------------

You may predefine some settings which are used in pw_teaser, unless it **will be overwritten by plugin settings**.

To do this, just write this in TypoScript setup:

::

    plugin.tx_pwteaser.settings.[setting] = value

    plugin.tx_pwteaser.view.[setting] = value


.. hint::
   See the configuration :ref:`configuration_reference`, which settings are available.


Add a whole page teaser with TS
-------------------------------

If you want to add a page teaser in areas on your page, which are not editable by users, you can simply use typoscript
to assign it to a marker or variable (i.e.) of your template. You could put it in a COA too, of course.

**Example:**

::

    10 = COA
    10 {
      10 = TEXT
      10.value = Latest News (based on pages)

      20 < lib.tx_pwteaser
      20 {
        settings {
          # see configuration reference
        }
        view {
          # see configuration reference
        }
      }
    }

.. important::
   The ``lib.tx_pwteaser`` is only available when you have **included the static template** to your TYPO3 template.


Using parsed TypoScript
-----------------------

You may use typoscript for aby pw_teaser setting.

**For example:**

::

    plugin.tx_pwteaser.settings {
      source = customPages
      customPages = CONTENT
      customPages {
        table = pages
        select {
          pidInList = 1
          recursive = 50
          where = whatever=1337
        }
        renderObj = COA
        renderObj.10 = TEXT
        renderObj.10.field = uid
        renderObj.10.wrap = | ,|*| ,| |*|
      }
    }

This example creates a comma-separated list for the setting "customPages". Unlikely to default Extbase behavior, the
defined settings are parsed by TypoScript parser, before used in Extbase controller.
