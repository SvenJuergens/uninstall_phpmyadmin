<?php
namespace SvenJuergens\UninstallPhpmyadmin\Command;

/**
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

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;

class UninstallCommandController extends CommandController
{
    public function uninstallCommand()
    {
        $this->uninstallAndRemove();
    }

    /**
     * @param string $extensionKey
     * @return void
     */
    public function uninstallAndRemove($extensionKey = 'phpmyadmin')
    {
        /** @var $service InstallUtility */
        $service = $this->objectManager->get(InstallUtility::class);

        if ($service->isLoaded($extensionKey)) {
            $service->uninstall($extensionKey);
        }

        if ($service->isAvailable($extensionKey)) {
            $service->removeExtension($extensionKey);
        }
    }
}
