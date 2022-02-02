<?php

namespace TickTackk\SignatureOnce;

use TickTackk\SignatureOnce\Install\Data\MySql as MySqlInstallData;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Job\Manager as JobManager;

/**
 * @since 2.0.0 Alpha 1
 */
class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1() : void
    {
        $sm = $this->schemaManager();

        foreach ($this->getMySqlInstallData()->getTables() AS $tableName => $toApply)
        {
            $sm->createTable($tableName, $toApply);
        }
    }

    public function installStep2() : void
    {
        $sm = $this->schemaManager();

        foreach ($this->getMySqlInstallData()->getInstallAlters() AS $tableName => $toApply)
        {
            $sm->alterTable($tableName, $toApply);
        }
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param array $stateChanges
     *
     * @return void
     */
    public function postInstall(array &$stateChanges) : void
    {
        $jobList = [
            'TickTackk\SignatureOnce:ThreadFirstUserPost',
            'TickTackk\SignatureOnce:ConversationFirstUserMessage'
        ];

        $this->jobManager()->enqueueUnique('tckSigOnceInstall', 'XF:Atomic', [
            'execute' => $jobList
        ]);
    }

    /**
     * @since 2.0.0 Alpha 1
     *
     * @param int|null $previousVersion
     * @param array $stateChanges
     *
     * @return void
     */
    public function postUpgrade($previousVersion, array &$stateChanges) : void
    {
        if ($previousVersion)
        {
            if ($previousVersion < 2000011)
            {
                $jobList = [
                    'TickTackk\SignatureOnce:ThreadFirstUserPost',
                    'TickTackk\SignatureOnce:ConversationFirstUserMessage'
                ];

                $this->jobManager()->enqueueUnique('tckSigOnce2000011', 'XF:Atomic', [
                    'execute' => $jobList
                ]);
            }
        }
    }

    public function uninstallStep1() : void
    {
        $sm = $this->schemaManager();

        foreach ($this->getMySqlInstallData()->getTables() AS $tableName => $null)
        {
            $sm->dropTable($tableName);
        }
    }

    public function uninstallStep2() : void
    {
        $sm = $this->schemaManager();

        foreach ($this->getMySqlInstallData()->getUninstallAlters() AS $tableName => $toApply)
        {
            $sm->alterTable($tableName, $toApply);
        }
    }

    protected function getMySqlInstallData() : MySqlInstallData
    {
        return new MySqlInstallData;
    }

    /**
     * @return JobManager
     */
    protected function jobManager() : JobManager
    {
        return $this->app()->jobManager();
    }
}