## **Eloquent Cassandra for Lumen 5.7.x**

Cassandra support for Lumen 5.7.x database query builder

## **Table of contents**

* Prerequisites

* Installation

* Query Builder

* Schema

* Extensions

* Examples

## **Prerequisites**

* Datastax PHP Driver for Apache Cassandra (https://github.com/datastax/php-driver)
* PHP 7.*

## **Installation**

Using composer:

    composer require roi-up-agency/lumen-cassandra

Add the line below in the "Register Service Provider" of bootstrap/app.php file:

	$app->register(RoiupAgency\LumenCassandra\CassandraServiceProvider::class);

Include to .env file the filled data below:

	DB_CONNECTION=cassandra
	DB_HOST=
	DB_PORT=9042
	DB_KEYSPACE=mykeyspace
	DB_USERNAME=
	DB_PASSWORD=
	DB_AUTH_TYPE= empty or userCredentials

### **Auth**

You can use Laravel's native Auth functionality for cassandra, make sure your config/auth.php looks like 

        'providers' => [
        // 'users' => [
        //     'driver' => 'eloquent',
        //     'model' => App\User::class,
        // ],
        'users' => [
            'driver' => 'database',
            'table' => 'users',
        ],
            ],


# **CQL data types supported**

text('a')

bigint('b')

blob('c')

boolean('d')

counter('e')

decimal('f')

double('g')

float('h')

frozen('i')

inet('j')

nt('k')

listCollection('l', 'text')

mapCollection('m', 'timestamp', 'text')

setCollection('n', 'int')

timestamp('o')

timeuuid('p')

tuple('q', 'int', 'text', 'timestamp')

uuid('r')

varchar('s')

varint('t')

ascii('u')

**Primary Key**

primary(['a', 'b'])

**Query Builder**

The database driver plugs right into the original query builder. When using cassandra connections, you will be able to build fluent queries to perform database operations.

    $emp = DB::table('emp')->get();

    $emp = DB::table('emp')->where('emp_name', 'Christy')->first();

If you did not change your default database connection, you will need to specify it when querying.

    $emp = DB::connection('cassandra')->table('emp')->get();

**Examples**

### **Basic Usage**

**Retrieving All Records**

    $emp = DB::table('emp')->all();

**Indexing columns**

CREATE INDEX creates a new index on the given table for the named column.

    DB::table('users')->index(['name']);

**Selecting columns**

    $emp = DB::table('emp')->where('emp_no', '>', 50)->select('emp_name', 'emp_no')->get();

    $emp = DB::table('emp')->where('emp_no', '>', 50)->get(['emp_name', 'emp_no']);

**Wheres**

The WHERE clause specifies which rows to query. In the WHERE clause, refer to a column using the actual name, not an alias. Columns in the WHERE clause need to meet one of these requirements:

* The partition key definition includes the column.	

* A column that is indexed using CREATE INDEX.

        $emp = DB::table('emp')->where('emp_no', '>', 50)->take(10)->get();

**And Statements**

    $emp = DB::table('emp')->where('emp_no', '>', 50)->where('emp_name', '=', 'Christy')->get();

**Using Where In With An Array**

    $emp = DB::table('emp')->whereIn('emp_no', [12, 17, 21])->get();

**Order By**

ORDER BY clauses can select a single column only. Ordering can be done in ascending or descending order, default ascending, and specified with the ASC or DESC keywords. In the ORDER BY clause, refer to a column using the actual name, not the aliases.

    $emp = DB::table('emp')->where('emp_name','Christy')->orderBy('emp_no', 'desc')->get();

**Limit**

We can use limit() and take() for limiting the query.

    $emp = DB::table('emp')->where('emp_no', '>', 50)->take(10)->get();

    $emp = DB::table('emp')->where('emp_no', '>', 50)->limit(10)->get();

**Distinct**

Distinct requires a field for which to return the distinct values.

    $emp = DB::table('emp')->distinct()->get(['emp_id']);

Distinct can be combined with **where**:

    $emp = DB::table('emp')->where('emp_sal', 45000)->distinct()->get(['emp_name']);

**Count**

    $number = DB::table('emp')->count();

Count can be combined with **where**:

    $sal = DB::table('emp')->where('emp_sal', 45000)->count();

**Truncate**

    $sal = DB::table('emp')->truncate();

### **Filtering a collection set, list, or map**

You can index the collection column, and then use the CONTAINS condition in the WHERE clause to filter the data for a particular value in the collection.

    $emp = DB::table('emp')->where('emp_name','contains', 'Christy')->get();

After [indexing the collection keys](https://docs.datastax.com/en/cql/3.1/cql/cql_reference/create_index_r.html#reference_ds_eqm_nmd_xj__CreatIdxCollKey) in the venues map, you can filter on map keys.

    $emp = DB::table(emp')->where('todo','contains key', '2014-10-02 06:30:00+0000')->get();

**Raw Query**

The CQL expressions can be injected directly into the query.

    $emp = DB::raw('select * from emp');

**Inserts, updates and deletes**

Inserting, updating and deleting records works just like the original QB.

**Insert**

    DB::table('emp')->insert(['emp_id' => 11, 'emp_city' => '{"kochi", "tvm", "kollam"}', 'emp_name' => 'Christy', 'emp_phone' => 12345676890, 'emp_sal' => 500]);

**Updating**

To update a model, you may retrieve it, change an attribute, and use the update method.

    DB::table('emp')->where('emp_id', 11)->update(['emp_city' => 'kochi', 'emp_name' => 'Christy jos', 'emp_phone' =>  1234567890]);

### **Updating a collection set, list, and map**

Update collections in a row. The method will be like

    updateCollection(collection_type, column_name, operator, value);

Collection_type is any of set, list or map.

Column_name is the name of column to be updated.

Operator is + or -, + for adding the values to collection and - to remove the value from collection.

Value can be associative array for map type and array of string/number for list and set types.

    DB::table('users')->where('id', 1)->updateCollection('set', 'phn', '+', [123, 1234,12345])->update();

    DB::table('users')->where('id', 1)->updateCollection('set', 'phn', '-', [123])->update();

    DB::table('users')->where('id', 1)->updateCollection('list', 'hobbies', '+', ['reading', 'cooking', 'cycling'])->update();

    DB::table('users')->where('id', 1)->updateCollection('list', 'hobbies', '-', ['cooking'])->update();

    DB::table('users')->where('id', 1)->updateCollection('map', 'friends', '+', ['John' => 'Male', 'Rex' => 'Male'])->update();

    DB::table('users')->where('id', 1)->updateCollection('map', 'friends', '-', ['John'])->update();

**Deleting**

To delete a model, simply call the delete method on the instance. We can delete the rows in a table by using deleteRow method:

    $emp = DB::table('emp')->where('emp_city', 'Kochi')->deleteRow();

We can also perform delete by the column in a table using deleteColumn method:

    $emp = DB::table('emp')->where('emp_id', 3)->deleteColumn();
