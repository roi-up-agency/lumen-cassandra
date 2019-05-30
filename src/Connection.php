<?php

namespace RoiupAgency\LumenCassandra;

use Illuminate\Database\Connection as BaseConnection;
use Illuminate\Database\ConnectionResolverInterface as ConnectionResolverInterface;
use Cassandra;
use RoiupAgency\LumenCassandra\Helpers\Helper;

class Connection extends BaseConnection implements ConnectionResolverInterface
{

    /**
     * The Cassandra connection handler.
     *
     * @var \Cassandra\DefaultSession
     */
    protected $connection;

    /**
     * Create a new database connection instance.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        // Create the connection
        $this->db = $config['keyspace'];
        $this->connection = $this->createConnection($config);
        $this->useDefaultPostProcessor();
    }

    /**
     * Begin a fluent query against a database collection.
     *
     * @param string $collection
     * @return Query\Builder
     */
    public function collection($collection)
    {
        $query = new Query\Builder($this);
        return $query->from($collection);
    }

    /**
     * Begin a fluent query against a database collection.
     *
     * @param string $table
     * @return Query\Builder
     */
    public function table($table)
    {
        return $this->collection($table);
    }

    /**
     * @inheritdoc
     */
    public function getSchemaBuilder()
    {
        return new Schema\Builder($this);
    }

    /**
     * [getSchemaGrammar returns the connection grammer]
     * @return [Schema\Grammar] [description]
     */
    public function getSchemaGrammar()
    {
        return new Schema\Grammar;
    }

    /**
     * return Cassandra object.
     *
     * @return \Cassandra\DefaultSession
     */
    public function getCassandraConnection()
    {
        return $this->connection;
    }

    /**
     * Create a new Cassandra connection.
     *
     * @param array $config
     * @return \Cassandra\DefaultSession
     */
    protected function createConnection(array $config)
    {
        $cluster = Cassandra::cluster()
            ->withContactPoints($config['host'])
            ->withPort($config['port'])
            ->build();
        $keyspace = $config['keyspace'];

        $connection = $cluster->connect($keyspace);

        return $connection;
    }

    /**
     * @inheritdoc
     */
    public function disconnect()
    {
        unset($this->connection);
    }

    /**
     * @inheritdoc
     */
    public function getElapsedTime($start)
    {
        return parent::getElapsedTime($start);
    }

    /**
     * @inheritdoc
     */
    public function getDriverName()
    {
        return 'Cassandra';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultPostProcessor()
    {
        return new Query\Processor();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultQueryGrammar()
    {
        return new Query\Grammar();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultSchemaGrammar()
    {
        return new Schema\Grammar();
    }


    /**
     * Execute an CQL statement and return the boolean result.
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {


        foreach ($bindings as $binding) {

            if (is_bool($binding)) {
                $value = $binding ? "true" : "false";
            } elseif (is_array($binding)) {
                $value = $this->elem2UDT($binding);
            } else {
                $value = Helper::isAvoidingQuotes($binding)  ? $binding : "'" . $binding . "'";
            }

            $query = preg_replace('/\?/', $value, $query, 1);
        }
        $builder = new Query\Builder($this, $this->getPostProcessor());

        //print("\n\n{$query}\n\n");

        return $builder->executeCql($query);
    }



    /**
     * Run an CQL statement and get the number of rows affected.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        // For update or delete statements, we want to get the number of rows affected
        // by the statement and return that back to the developer. We'll first need
        // to execute the statement and then we'll use PDO to fetch the affected.

        foreach ($bindings as $binding) {
            $value = Helper::isAvoidingQuotes($binding)  ? $binding : "'" . $binding . "'";
            $query = preg_replace('/\?/', $value, $query, 1);
        }

        $builder = new Query\Builder($this, $this->getPostProcessor());

        return $builder->executeCql($query);
    }


    /**
     * Execute an CQL statement and return the boolean result.
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function raw($query)
    {
        $builder = new Query\Builder($this, $this->getPostProcessor());
        $result = $builder->executeCql($query);
        return $result;
    }

    /**
     * Dynamically pass methods to the connection.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->connection, $method], $parameters);
    }

    // Interface methods implementation (for Lumen 5.7.* compatibility)

    /**
     * Get a database connection instance.
     *
     * @param string $name
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function connection($name = null)
    {
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
    }

    /**
     * Set the default connection name.
     *
     * @param string $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
    }


    // PRIVATE METHODS


    /**
     * Transform element to UDT update/insert value format
     *
     * @param $elem
     * @return string
     */
    private function elem2UDT($elem)
    {
        $result = "";
        if (is_array($elem)) {
            $result .= '{';
            $each_result = "";
            foreach ($elem as $key => $value) {
                if (!is_array($value)) {
                    $each_result .= "{$key}: '$value',";
                } else {
                    $each_result .= $this->elem2UDT($value) . ",";
                }
            }
            if (substr($each_result, strlen($each_result) - 1) == ',') {
                $each_result = substr($each_result, 0, strlen($each_result) - 1);
            }
            $result .= $each_result;
            $result .= '}';
        }
        return $result;
    }

}
