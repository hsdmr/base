<?php

namespace Hasdemir\Base;

use Hasdemir\Base\Exception\NotFoundException;
use Hasdemir\Base\Exception\StoragePdoException;
use PDO;

abstract class PdoModel extends HandyClass
{
  private string $primary_key = 'id';
  private PDO $db;
  protected string $table;
  protected array $fields = [];
  protected array $unique = [];
  protected array $hidden = [];
  protected array $protected = [];
  protected bool $soft_delete = false;
  private string $relation;
  private string $relation_table;
  private bool $with_hidden = false;
  private bool $with_deleted = false;
  private bool $only_deleted = false;

  private array $select = [];
  private array $special_select = [];
  private string $where_sql = '';
  private array $where_params = [];
  private $where_key;
  private string $order = '';
  private string $limit = '';
  private array $with = [];
  private array $collection = [];

  public function __construct()
  {
    Codes::currentJob(slugify($this->table, '_') . '-model');
    $this->db = System::getPdo();
    $this->createFields();
  }

  /**
   * It detects whether to edit or add a new row in the table and performs the recording.
   *
   * @return object model
   *
   */
  public function save()
  {
    $params = [];
    foreach ($this->fields as $field) {
      $params[$field] = $this->{$field};
    }
    if (isset($this->where_key)) {
      return $this->update($params);
    }
    return $this->create($params);
  }

  /**
   * Adds a new row to the table.
   *
   * @param  array  $params
   * @return object model
   *
   */
  public function create($params)
  {
    $this->timestamps($params);
    $this->checkHasUniqueItem($params);
    $fields = [];
    foreach ($this->fields as $key) {
      $fields[$key] = $params[$key] ?? null;
    }
    $obj_fields = $this->fields;
    array_shift($fields);
    array_shift($obj_fields);
    $binds = array_map(fn ($attr) => ":$attr", array_keys($fields));
    $sql = "INSERT INTO `$this->table` (`" . implode("`, `", $obj_fields) . "`) VALUES (" . implode(", ", $binds) . ")";
    $statement = $this->db->prepare($sql);
    $binds = [];
    foreach ($fields as $key => $value) {
      $statement->bindValue(":$key", $value);
      $binds[$key] = $value;
    }
    $GLOBALS[Codes::SQL_QUERIES][] = [
      Codes::QUERY => $sql,
      Codes::BINDS => $binds
    ];
    if ($statement->execute()) {
      $fields[$this->primary_key] = $this->db->lastInsertId();
      return $this->prepareFields($fields);
    }

    throw new StoragePdoException(
      "An unknown error has occurred while '" . explode('\\', getModelFromTable($this->table))[2] . "' model create"
    );
  }

  /**
   * Updates the relevant row in the table.
   *
   * @param  array  $params
   * @return object model
   *
   */
  public function update($params = [])
  {
    $this->timestamps($params, 'update');
    $this->checkHasUniqueItem($params);
    $binds = [];
    foreach ($params as $key => $value) {
      $binds[] = '`' . $key . '` = :' . $key;
    }
    $sql = "UPDATE `$this->table` SET " . implode(', ', $binds) . " WHERE " . $this->primary_key . " = :" . $this->primary_key;
    $statement = $this->db->prepare($sql);
    $binds = [];
    foreach ($params as $key => $value) {
      $statement->bindValue(":$key", $value);
      $binds[$key] = $value;
    }
    $statement->bindValue(":" . $this->primary_key, $this->where_key);
    $binds[$this->primary_key] = $this->where_key;
    $GLOBALS[Codes::SQL_QUERIES][] = [
      Codes::QUERY => $sql,
      Codes::BINDS => $binds
    ];

    if ($statement->execute()) {
      $params[$this->primary_key] = $this->where_key;
      return $this->prepareFields($params);
    }

    throw new StoragePdoException(
      "An unknown error has occurred while '" . explode('\\', getModelFromTable($this->table))[2] . "' model update"
    );
  }

  /**
   * Returns the first row in the search query.
   *
   * @return object model
   *
   */
  public function first()
  {
    if (!empty($this->special_select)) {
      return $this->get();
    }

    if (!empty($get_result = $this->get())) {
      return $this->prepareFields($get_result[0]->container);
    }
  }

  /**
   * Returns the search query.
   *
   * If custom selection is not requested, the collection will be prepared as object
   * If custom selection is requested such as sum, count, avg, the collection will be prepared as array
   * @return array object collection
   * @return object item
   *
   */
  public function get(): mixed
  {
    $sql = "SELECT " . $this->createValidSelect() . " FROM `$this->table` $this->where_sql";

    if ($this->soft_delete && !$this->with_deleted && !$this->only_deleted) {
      $sql .= ($this->where_sql === '' ? "WHERE" : " AND") . " `deleted_at` IS NULL";
    }

    if ($this->soft_delete && $this->only_deleted && !$this->with_deleted) {
      $sql .= ($this->where_sql === '' ? "WHERE" : " AND") . " `deleted_at` IS NOT NULL";
    }

    $sql .= $this->order . $this->limit;
    $statement = $this->db->prepare($sql);
    $binds = [];
    foreach ($this->where_params as $item) {
      $statement->bindValue(":" . $item[0], $item[2]);
      $binds[$item[0]] = $item[2];
    }
    $GLOBALS[Codes::SQL_QUERIES][] = [
      Codes::QUERY => $sql,
      Codes::BINDS => $binds
    ];

    if ($statement->execute()) {
      $items = $statement->fetchAll(PDO::FETCH_ASSOC);
      $this->collection = [];
      foreach ($items as $item) {

        if (count(array_diff(array_keys($item), $this->fields)) === 0) {
          $object = $this->newModel($this->table, $item, $this->with_deleted, $this->only_deleted, $this->with_hidden, $this->select);
          foreach ($this->with as $with) {
            $object->{$with} = $object->{$with}()->{$with}->toArray();
          }
        }

        if (!empty($this->special_select)) {
          return $this->prepareFields($item);
        }

        $this->collection[] = $object ?? $item;
      }
      $this->select = [];
      $this->special_select = [];
      return $this->collection;
    }

    throw new StoragePdoException(
      "An unknown error has occurred while '" . explode('\\', getModelFromTable($this->table))[2] . "' model get"
    );
  }

  /**
   * Searches with primary key.
   *
   * @param  array  $primary_key
   * @return object model
   *
   * @throws NotFoundException
   */
  public function findByPrimaryKey(int $primary_key)
  {
    $this->where_key = $primary_key;
    $sql = "SELECT " . $this->createValidSelect() . " FROM `$this->table` WHERE `" . $this->primary_key . "` = :" . $this->primary_key;
    $statement = $this->db->prepare($sql);
    $binds = [];
    $statement->bindValue(":" . $this->primary_key, $this->where_key);
    $binds[$this->primary_key] = $this->where_key;
    $GLOBALS[Codes::SQL_QUERIES][] = [
      Codes::QUERY => $sql,
      Codes::BINDS => $binds
    ];

    if ($statement->execute()) {
      $item = $statement->fetch(PDO::FETCH_ASSOC);
      return $this->prepareFields($item);
    }

    throw new NotFoundException(
      explode('\\', getModelFromTable($this->table))[2] . " not found"
    );
  }

  /**
   * Returns all data in the table.
   *
   * @return object collection
   *
   */
  public function all()
  {
    return $this->get();
  }

  /**
   * Performs deletion according to soft delete status on the table.
   *
   * @return bool
   *
   */
  public function delete(): bool
  {
    if ($this->soft_delete) {
      $sql = "UPDATE `$this->table` SET `deleted_at` = :deleted_at WHERE `" . $this->primary_key . "` = :" . $this->primary_key;
      $statement = $this->db->prepare($sql);
      $binds = [];
      $statement->bindValue(":$this->primary_key", $this->where_key);
      $binds[$this->primary_key] = $this->where_key;
      $statement->bindValue(":deleted_at", time());
      $binds['deleted_at'] = time();
      $GLOBALS[Codes::SQL_QUERIES][] = [
        Codes::QUERY => $sql,
        Codes::BINDS => $binds
      ];
      if ($statement->execute()) {
        return true;
      };
      return false;
    }
    return $this->forceDelete();
  }

  /**
   * Completely deletes the relevant row in the table.
   *
   * @return bool
   *
   */
  public function forceDelete(): bool
  {
    $sql = "DELETE FROM `$this->table` WHERE `" . $this->primary_key . "` = :" . $this->primary_key;
    $statement = $this->db->prepare($sql);
    $binds = [];
    $statement->bindValue(":$this->primary_key", $this->where_key);
    $binds[$this->primary_key] = $this->where_key;
    $GLOBALS[Codes::SQL_QUERIES][] = [
      Codes::QUERY => $sql,
      Codes::BINDS => $binds
    ];
    if ($statement->execute()) {
      return true;
    };
    return false;
  }

  /**
   * Returns value with deleted data in table.
   *
   * @return object model
   *
   */
  public function withDeleted(): object
  {
    $this->with_deleted = true;
    return $this;
  }

  /**
   * Returns values with hidden columns in the table.
   *
   * @return object model
   *
   */
  public function withHidden(): object
  {
    $this->with_hidden = true;
    return $this;
  }

  /**
   * Returns only deleted data.
   *
   * @return object $this
   *
   */
  public function with(): object
  {
    $this->with = func_get_args() ?? [];
    return $this;
  }

  /**
   * Returns only deleted data.
   *
   * @param  array  $params
   * @return object $this
   *
   */
  public function onlyDeleted(): object
  {
    $this->only_deleted = true;
    return $this;
  }

  /**
   * Determines which columns in the table are returned.
   *
   * @param  array  $params
   * @return object $this
   *
   */
  public function select(): object
  {
    $this->select = func_get_args() ?? [];
    for ($i = 0; $i < count($this->select); $i++) {
      if (str_contains(strtolower($this->select[$i]), '(')) {
        $this->special_select[] = strtolower($this->select[$i]);
        unset($this->select[$i]);
      }
    }
    return $this;
  }

  private function whereConditions($key): void
  {
    if ($this->where_sql === '') {
      $this->where_sql .= ' WHERE';
    }

    if (substr($this->where_sql, -1) !== '(' && substr($this->where_sql, -5) !== 'WHERE') {
      $this->where_sql .= " $key";
    }
  }
  /**
   * Adds where query with and.
   *
   * @return object $this
   *
   */
  public function where(): object
  {
    $this->whereConditions('AND');

    $where = func_get_args();
    if (func_num_args() === 2) {
      $key = $where[0];
      $operator = '=';
      $value = $where[1];
    } else {
      $key = $where[0];
      $operator = $where[1];
      $value = $where[2];
    }

    $this->where_params[] = [$key, $operator, $value];
    $this->where_sql = trim($this->where_sql .= " `$key` $operator :$key");

    return $this;
  }

  /**
   * Adds where query with or.
   *
   * @return object $this
   *
   */
  public function orWhere(): object
  {
    $this->whereConditions('OR');

    $where = func_get_args();

    if (func_num_args() == 2) {
      $key = $where[0];
      $operator = '=';
      $value = $where[1];
    } else {
      $key = $where[0];
      $operator = $where[1];
      $value = $where[2];
    }

    $this->where_params[] = [$key, $operator, $value];
    $this->where_sql = trim($this->where_sql .= " `$key` $operator :$key");

    return $this;
  }

  /**
   * Adds WHERE IS NOT NULL to query.
   *
   * @return object $this
   *
   */
  public function WhereNotNull($key, $conjunction = 'AND'): object
  {
    if ($conjunction === 'AND') {
      $this->where($key, 'IS', 'NOT NULL');
    } else {
      $this->orWhere($key, 'IS', 'NOT NULL');
    }
    return $this;
  }

  /**
   * Adds WHERE IN () to query.
   *
   * @return object $this
   *
   */
  public function WhereIn($key, $keys = []): object
  {
    $this->where($key, 'IN', "('" . implode("', '", $keys) . "')");
    return $this;
  }

  /**
   * Adds WHERE IS NULL to query.
   *
   * @return object $this
   *
   */
  public function WhereNull($key, $conjunction = 'AND'): object
  {
    if ($conjunction === 'AND') {
      $this->where($key, 'IS', 'NULL');
    } else {
      $this->orWhere($key, 'IS', 'NULL');
    }
    return $this;
  }

  /**
   * Adds open parenthesis to query with or.
   *
   * @return object $this
   *
   */
  public function openPharanthesis($key = ''): object
  {
    if ($this->where_sql === '') {
      $this->where_sql .= ' WHERE (';
    } else {
      $this->where_sql = trim($this->where_sql .= " $key (");
    }


    return $this;
  }

  /**
   * Adds close parenthesis to query with and.
   *
   * @return object $this
   *
   */
  public function closePharanthesis(): object
  {
    $this->where_sql = trim($this->where_sql .= " )");

    return $this;
  }

  /**
   * Determines the order of the data to return in the query.
   *
   * @param  array  $order
   * @return object $this
   *
   */
  public function order(string $order, string $by = 'ASC'): object
  {
    $this->order = " ORDER BY `" . $order . '` ' . strtoupper($by);
    return $this;
  }

  /**
   * Determines how many rows to return in the query.
   *
   * @param  int  $limit
   * @return object $this
   *
   */
  public function limit(int $limit, int $offset = 0): object
  {
    $this->limit = " LIMIT " . $offset . ", " . $limit;
    return $this;
  }

  /**
   * For many to many queries.
   *
   * @param  string  $table
   * @return $collection
   *
   */
  public function belongsToMany(string $table)
  {
    $method = getCallingMethodName();
    $this->relation = $table;
    foreach ([$table . '_' . $this->table, $this->table . '_' . $table] as $item) {
      $statement = $this->db->prepare("SHOW TABLES LIKE '$item';");
      $statement->execute();
      $row = $statement->fetch();
      if ($row) {
        $this->relation_table = $row[0];
      }
    }
    $sql = "SELECT * FROM `" . $this->relation . "` INNER JOIN `" .
      $this->relation_table . "` ON " . $this->relation_table . "." . $this->relation . "_id=" . $this->relation . ".id WHERE " .
      $this->relation_table . "." . $this->table . "_id=:" . $this->table;
    $statement = $this->db->prepare($sql);
    $binds = [];
    $statement->bindValue(":" . $this->table, $this->where_key);
    $binds[$this->table . "_id"] = $this->where_key;
    $GLOBALS[Codes::SQL_QUERIES][] = [
      Codes::QUERY => $sql,
      Codes::BINDS => $binds
    ];
    if ($statement->execute()) {
      $items = $statement->fetchAll(PDO::FETCH_ASSOC);
      $this->{$method} = [];
      foreach ($items as $item) {
        $object = $this->newModel($table, $item);
        $this->{$method}[] = $object;
      }
      $this->offsetSet($method, $this->{$method});
      return $this;
    }

    throw new StoragePdoException(
      "An unknown error has occurred while '" . explode('\\', getModelFromTable($this->table))[2] . "' model call belongsToMany '" . explode('\\', getModelFromTable($table))[2] . "'"
    );
  }

  /**
   *  For detach to queries.
   *
   * @return object
   *
   */
  public function detach(): object
  {
    $sql = "DELETE FROM `" . $this->relation_table . "` WHERE `" . $this->table . "_id` = :id";
    $statement = $this->db->prepare($sql);
    $binds = [];
    $statement->bindValue(":id", $this->where_key);
    $binds[$this->primary_key] = $this->where_key;
    $GLOBALS[Codes::SQL_QUERIES][] = [
      Codes::QUERY => $sql,
      Codes::BINDS => $binds
    ];

    if ($statement->execute()) {
      $this->offsetUnset($this->relation);
      return $this;
    }

    throw new StoragePdoException(
      "An unknown error has occurred while '" . explode('\\', getModelFromTable($this->table))[2] . "' model call detach '" . explode('\\', getModelFromTable($this->relation))[2] . "'"
    );
  }

  /**
   *  For attach to queries.
   *
   * @return object
   *
   */
  public function attach($id): object
  {
    $sql = "INSERT INTO `" . $this->relation_table . "` (`" . $this->table . "_id`, `" . $this->relation . "_id`) VALUES (:id, :" . $this->relation . "_id)";
    $statement = $this->db->prepare($sql);
    $binds = [];
    $statement->bindValue(":" . $this->relation . "_id", $id);
    $statement->bindValue(":id", $this->where_key);
    $binds[$this->primary_key] = $this->where_key;
    $binds[$this->relation . "_id"] = $id;
    $GLOBALS[Codes::SQL_QUERIES][] = [
      Codes::QUERY => $sql,
      Codes::BINDS => $binds
    ];

    if ($statement->execute()) {
      return $this;
    }

    throw new StoragePdoException(
      "An unknown error has occurred while '" . explode('\\', getModelFromTable($this->table))[2] . "' model call detach '" . explode('\\', getModelFromTable($this->relation))[2] . "'"
    );
  }

  /**
   *  For belong to queries.
   *
   * @param  string  $table
   * @return object
   *
   */
  public function belongsTo(string $table): object
  {
    $method = getCallingMethodName();
    $sql = "SELECT * FROM `" . $table . "` WHERE `id` = :id LIMIT 1";
    $statement = $this->db->prepare($sql);
    $binds = [];
    $statement->bindValue(":id", $this->{$table . '_id'});
    $binds[$this->primary_key] = $this->{$table . '_id'};
    $GLOBALS[Codes::SQL_QUERIES][] = [
      Codes::QUERY => $sql,
      Codes::BINDS => $binds
    ];

    if ($statement->execute()) {
      $item = $statement->fetch(PDO::FETCH_ASSOC);
      $this->{$method} = $this->newModel($table, $item);
      $this->offsetSet($method, $this->{$method});
      return $this;
    }

    throw new StoragePdoException(
      "An unknown error has occurred while '" . explode('\\', getModelFromTable($this->table))[2] . "' model call belongsTo '" . explode('\\', getModelFromTable($table))[2] . "'"
    );
  }

  /**
   *  For has many queries.
   *
   * @param  string  $table
   * @return object
   *
   */
  public function hasMany(string $table): object
  {
    $method = getCallingMethodName();
    $sql = "SELECT * FROM `" . $table . "` WHERE `" . $this->table . "_id` = :id";
    $statement = $this->db->prepare($sql);
    $binds = [];
    $statement->bindValue(":id", $this->where_key);
    $binds[$this->table . "_id"] = $this->where_key;
    $GLOBALS[Codes::SQL_QUERIES][] = [
      Codes::QUERY => $sql,
      Codes::BINDS => $binds
    ];
    if ($statement->execute()) {
      $items = $statement->fetchAll(PDO::FETCH_ASSOC);
      $this->{$method} = [];
      foreach ($items as $item) {
        $object = $this->newModel($table, $item);
        $this->{$method}[] = $object;
      }
      $this->offsetSet($method, $this->{$method});
      return $this;
    }

    throw new StoragePdoException(
      "An unknown error has occurred while '" . explode('\\', getModelFromTable($this->table))[2] . "' model call hasMany '" . explode('\\', getModelFromTable($table))[2] . "'"
    );
  }

  /**
   * Checks for unique element in table.
   *
   * @param  array  $params
   * @return void
   *
   * @throws StoragePdoException
   */
  private function checkHasUniqueItem($params): void
  {
    foreach ($this->unique as $key) {
      $result = $this->select("COUNT(id) as count", $key, $this->primary_key)->where($key, $params[$key])->get();
      if ($this->where_key != '') {
        if ($result['count'] && $result[$this->primary_key] != $this->where_key) {
          throw new StoragePdoException("'$key' has already been registered");
        }
      } else if ($result['count']) {
        throw new StoragePdoException("'$key' has already been registered");
      }
    }
  }

  /**
   * Creates timestamps.
   *
   * @param  array  $params
   * @return void
   *
   */
  private function timestamps(&$params, $type = 'create'): void
  {
    if ($type === 'create') {
      $params['updated_at'] = time();
      $params['created_at'] = time();
      if ($this->soft_delete) {
        $params['deleted_at'] = null;
      }
    }

    if ($type === 'update') {
      $params['updated_at'] = time();
      if ($this->soft_delete) {
        $params['deleted_at'] = null;
      }
    }
  }

  /**
   * Creates the columns in the table.
   *
   * @param  array  $options
   * @return void
   *
   */
  private function createFields(): void
  {
    if (empty($this->fields)) {
      $sql = "DESCRIBE $this->table";
      $statement = $this->db->prepare($sql);
      $statement->execute();

      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $this->fields[] = $item['Field'];
        $this->{$item['Field']} = '';
      }
    }
  }

  /**
   * Creates valid select.
   *
   * @return string
   *
   */
  private function createValidSelect(): string
  {
    if ($this->select == [] && $this->special_select == []) {
      return '`' . trim(implode('`, `', array_diff($this->fields, $this->protected))) . '`';
    }
    if ($this->special_select == []) {
      return '`' . trim(implode('`, `', array_diff($this->select, $this->protected))) . '`';
    }
    if ($this->select == []) {
      return trim(implode(', ', $this->special_select));
    }
    return trim(implode(', ', $this->special_select)) . ', `' . trim(implode('`, `', array_diff($this->select, $this->protected))) . '`';
  }

  /**
   * Returns data in array format.
   *
   * @return array
   *
   */
  public function toArray(): array
  {
    return $this->container;
  }

  public function newModel(string $table, array $attributes, $with_deleted = null, $only_deleted = null, $with_hidden = null, $select = null): object
  {
    $model_name = getModelFromTable($table);
    $model = new $model_name;
    if ($with_deleted) {
      $model->withDeleted();
    }
    if ($only_deleted) {
      $model->onlyDeleted();
    }
    if ($with_hidden) {
      $model->withHidden();
    }
    if ($select) {
      $model->select = $select;
    }
    return $model->prepareFields($attributes);
  }

  private function prepareFields($item): object
  {

    if (!empty($this->special_select)) {
      foreach ($item as $key => $value) {
        $this->{$key} = $value ?? '';
      }
    } else {
      foreach ($this->fields as $field) {
        $item[$field] = $item[$field] ?? '';
        $this->{$field} = $item[$field];
      }

      if (isset($item[$this->primary_key])) {
        $this->where_key = $item[$this->primary_key];
      }

      if (!$this->with_hidden) {
        foreach ($this->hidden as $hide) {
          unset($this->{$hide});
          unset($item[$hide]);
        }
      }

      foreach ($this->protected as $protect) {
        unset($this->{$protect});
        unset($item[$protect]);
      }

      foreach ($this->fields as $field) {
        if ($this->select != [] && !in_array($field, $this->select)) {
          unset($this->{$field});
          unset($item[$field]);
        }
      }
    }

    return $this->setContainer($item);
  }

  public function __destruct()
  {
    Codes::endJob();
  }
}
