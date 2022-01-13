<?php

namespace TickTackk\SignatureOnce\Install\Data;

use XF\Db\Schema\Create as DbCreateSchema;

/**
 * @since 2.0.0 Alpha 1
 */
class MySql
{
    public function getTables() : array
    {
        $tables = [];

        $tables['xf_tck_signature_once_container_first_user_content'] = function (DbCreateSchema $table)
        {
            $table->addColumn('record_id', 'int')->autoIncrement()->primaryKey();
            $table->addColumn('user_id', 'int');
            $table->addColumn('container_type', 'varbinary', 25);
            $table->addColumn('container_id', 'int');
            $table->addColumn('content_type', 'varbinary', 25);
            $table->addColumn('content_id', 'int');
            $table->addColumn('content_date', 'int');

            $table->addUniqueKey([
                'user_id',
                'container_type',
                'container_id',
                'content_type'
            ]);
            $table->addKey(['container_type', 'container_id']);
        };

        return $tables;
    }

    public function getInstallAlters() : array
    {
        $tables = [];

        return $tables;
    }

    public function getUninstallAlters() : array
    {
        $tables = [];

        return $tables;
    }
}