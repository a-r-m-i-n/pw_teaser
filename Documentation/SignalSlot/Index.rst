.. include:: ../Includes.txt

.. _signal-slot:


Signal/Slot
===========

pw_teaser provides a signal, that may be used by your extensions **to modify the pages array** (or containing pages)
right **before assigning to view**.

To connect your slot to the signal just put this to your ``ext_localconf.php``:

::

    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $signalSlotDispatcher->connect(
        \PwTeaserTeam\PwTeaser\Controller\TeaserController::class,
        'indexActionModifyPages',
        \Vendor\ExtensionName\Signals\PwTeaserSignal::class,
        'modifyPages'
    );

The class you have referenced should look like this:

::

    <?php
    namespace Vendor\ExtensionName\Signals;

    class PwTeaserSignal
    {
        /**
         * @param array<\PwTeaserTeam\PwTeaser\Domain\Model\Page> $pages Referenced array of pages
         * @param \PwTeaserTeam\PwTeaser\Controller\TeaserController $teaserController
         * @return void
         */
        public function modifyPages(array &$pages, PwTeaserTeam\PwTeaser\Controller\TeaserController $teaserController) {
            // Do whatever you want with referenced array in $pages
        }
    }

Because of the referenced array, all changes you do in the array will also take effect in pw_teaser extension.
You don't need to return anything.
