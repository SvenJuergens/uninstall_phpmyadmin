<?php
namespace SvenJuergens\UninstallPhpmyadmin\Utility;

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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Scheduler\Task;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;

class ControlTask
{

    /**
     * @param string $extensionKey kex which is uninstalled
     * @param  InstallUtility $parentObj
     * @param $signalInformation
     */
    public function controlTask($extensionKey, $parentObj, $signalInformation)
    {
        if ($extensionKey == 'phpmyadmin' && ExtensionManagementUtility::isLoaded('scheduler')) {
            $task = $this->getTask('uninstall_phpmyadmin:uninstall:uninstall');
            if (!is_null($task)) {
                $task->setDisabled($this->enableOrDisableTask($signalInformation));
                $this->saveTask($task);
            }
        }
    }

    /**
     * @param $commandIdentifier string
     * @return Task
     */
    public function getTask($commandIdentifier)
    {
        $rows = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'uid, serialized_task_object',
            'tx_scheduler_task',
            '1=1'
        );

        foreach ($rows as $row) {
            /** @var Task $task */
            $task = unserialize($row['serialized_task_object']);
            if ($task instanceof Task) {
                if ($task->getCommandIdentifier() == $commandIdentifier) {
                    return $task;
                }
            }
        }
        return null;
    }

    /**
     * @param Task $task
     */
    public function saveTask(Task $task)
    {
        $fields = [
            'serialized_task_object' => serialize($task),
            'disable' => $task->isDisabled(),
        ];

        $this->getDatabaseConnection()->exec_UPDATEquery(
            'tx_scheduler_task',
            'uid = ' . (int)$task->getTaskUid(),
            $fields
        );
    }

    /**
     * If EXT:phpmyadmin will uninstalled, the task should disabled and if we install pphmyadmin
     * the Task should be enabled
     *
     * @param $signalInformation
     * @return bool
     */
    public function enableOrDisableTask($signalInformation)
    {
        $setDisable = false;
        $signal = GeneralUtility::trimExplode('::', $signalInformation, true);
        if ($signal[1] == 'afterExtensionUninstall') {
            $setDisable = true;
        }
        return $setDisable;
    }
    
    
    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
