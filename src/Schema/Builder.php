<?php 

namespace RoiupAgency\LumenCassandra\Schema;

use Closure;
use RoiupAgency\LumenCassandra\Connection;

class Builder extends \Illuminate\Database\Schema\Builder
{
    /**
     * @inheritdoc
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->grammar = $connection->getSchemaGrammar();
    }


  
    /**
     * @inheritdoc
     */
    protected function createBlueprint($table, Closure $callback = null)
    {
        return new Blueprint($this->connection, $table);
    }
}
