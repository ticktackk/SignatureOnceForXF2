<?php

namespace TickTackk\SignatureOnce;

use TickTackk\SignatureOnce\Install\Data\MySql as MySqlInstallData;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

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
}