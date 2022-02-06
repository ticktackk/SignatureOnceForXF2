<?php

namespace TickTackk\SignatureOnce;

use TickTackk\SignatureOnce\Install\Data\MySql as MySqlInstallData;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Entity\Option as OptionEntity;
use XF\Job\Manager as JobManager;
use XF\Mvc\Entity\Manager as EntityManager;

/**
 * @since 2.0.0
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
     * @since 2.0.0
     *
     * @return void
     */
    public function upgrade2000070Step1() : void
    {
        $this->installStep1();
    }

    /**
     * @since 2.0.0
     *
     * @return void
     */
    public function upgrade2000070Step2() : void
    {
        $this->installStep2();
    }

    /**
     * @since 2.0.0
     *
     * @return void
     */
    public function upgrade2000070Step3() : void
    {
        $optionRenameMap = [
            'showSignatureOncePerThread' => 'tckSignatureOnceShowSignatureOncePerThread',
            'showSignatureOncePerConversation' => 'tckSignatureOnceShowSignatureOncePerConversation'
        ];

        foreach ($optionRenameMap AS $oldOptionId => $newOptionId)
        {
            /** @var OptionEntity $oldOption */
            $oldOption = $this->em()->find('XF:Option', $oldOptionId);
            /** @var OptionEntity $newOption */
            $newOption = $this->em()->find('XF:Option', $newOptionId);

            if ($oldOption && !$newOption)
            {
                $oldOption->option_id = $newOptionId;
                if ($oldOption->hasBehavior('XF:DevOutputWritable'))
                {
                    $oldOption->getBehavior('XF:DevOutputWritable')->setOption('write_dev_output', false);
                }
                $oldOption->saveIfChanged();
            }
            else if ($oldOption && $newOption)
            {
                $newOption->option_value = $oldOption->option_value;
                if ($oldOption->hasBehavior('XF:DevOutputWritable'))
                {
                    $oldOption->getBehavior('XF:DevOutputWritable')->setOption('write_dev_output', false);
                }
                $newOption->saveIfChanged();
            }
        }
    }

    /**
     * @since 2.0.0
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
     * @since 2.0.0
     * @version 2.0.1
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
            if ($previousVersion < 2000170)
            {
                $this->jobManager()->cancelUniqueJob('tckSigOnceInstall');
                $this->jobManager()->cancelUniqueJob('tckSigOnce2000070');
            }

            if ($previousVersion < 2000170)
            {
                $jobList = [
                    'TickTackk\SignatureOnce:ThreadFirstUserPost',
                    'TickTackk\SignatureOnce:ConversationFirstUserMessage'
                ];

                $this->jobManager()->enqueueUnique('tckSigOnce2000170', 'XF:Atomic', [
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
     * @since 2.0.0
     *
     * @return EntityManager
     */
    protected function em() : EntityManager
    {
        return $this->app()->em();
    }

    /**
     * @since 2.0.0
     *
     * @return JobManager
     */
    protected function jobManager() : JobManager
    {
        return $this->app()->jobManager();
    }
}