.. include:: ../Includes.txt

.. _events:


Events
======

ModifyPagesEvent
----------------

pw_teaser provides an event to modify the pages array result, before reaching to view.

For this, you need to provide an EventListener in your extension. For example:


.. code-block:: php
	<?php
	namespace VendorName\YourExtension\EventListener;

	use PwTeaserTeam\PwTeaser\Event\ModifyPagesEvent;

	class YourListener
	{
		public function modifyPages(ModifyPagesEvent $event): void
		{
			$event->setPages(array_reverse($event->getPages()));
		}
	}

Also, you need to register ``YourListener`` in EventDispatcher. You can do this in the ``Configuration/Services.yaml`` file:

.. code-block:: yaml
	services:
	  VendorName\YourExtension\EventListener\YourListener:
		tags:
		  - name: event.listener
			identifier: 'yourlistener-modifypages'
			method: 'modifyPages'
			event: PwTeaserTeam\PwTeaser\Event\ModifyPagesEvent
