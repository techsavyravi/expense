<?php

namespace Pop\Db\Sql;


class Update extends AbstractSql
{

    /**
     * WHERE predicate object
     * @var \Pop\Db\Sql\Predicate
     */
    protected $where = null;

    /**
     * Set the WHERE clause
     *
     * @return \Pop\Db\Sql\Predicate
     */
    public function where()
    {
        if (null === $this->where) {
            $this->where = new Predicate($this->sql);
        }

        return $this->where;
    }

    /**
     * Render the UPDATE statement
     *
     * @return string
     */
    public function render()
    {
        // Start building the UPDATE statement
        $sql = 'UPDATE ' . $this->sql->quoteId($this->sql->getTable()) . ' SET ';
        $set = array();

        $paramCount = 1;
        $dbType = $this->sql->getDbType();

        foreach ($this->columns as $column => $value) {
            $colValue = (strpos($column, '.') !== false) ?
                substr($column, (strpos($column, '.') + 1)) : $column;

            // Check for named parameters
            if ((':' . $colValue == substr($value, 0, strlen(':' . $colValue))) &&
                ($dbType !== \Pop\Db\Sql::SQLITE) &&
                ($dbType !== \Pop\Db\Sql::ORACLE)) {
                if (($dbType == \Pop\Db\Sql::MYSQL) || ($dbType == \Pop\Db\Sql::SQLSRV)) {
                    $value = '?';
                } else if ($dbType == \Pop\Db\Sql::PGSQL) {
                    $value = '$' . $paramCount;
                    $paramCount++;
                }
            }
            $val = (null === $value) ? 'NULL' : $this->sql->quote($value);
            $set[] = $this->sql->quoteId($column) .' = ' . $val;
        }

        $sql .= implode(', ', $set);

        // Build any WHERE clauses
        if (null !== $this->where) {
            $sql .= ' WHERE ' . $this->where->render($paramCount);
        }

        // Build any ORDER BY clause
        if (null !== $this->orderBy) {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }

        // Build any LIMIT clause
        if (null !== $this->limit) {
            $sql .= ' LIMIT ' . (int)$this->limit;
        }

        return $sql;
    }

}
