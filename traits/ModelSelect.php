<?php

namespace app\common\traits;

use app\common\libs\Application;

trait ModelSelect
{
    /**
     * @param $filter array
     * @param string $fileds
     * @param int $limit
     * @param string $connection
     * @return bool|\Phalcon\Db\ResultInterface
     */
    public function select($filter, $fileds = '*', $limit = 1, $connection = 'db')
    {
        $table = $this->getSource();
        return self::modelSelect($table, $filter, $fileds, $limit, $connection);
    }

    /**
     * @param $table string
     * @param $filter array
     * @param string $fileds
     * @param int $limit
     * @param string $connection
     * @return bool|\Phalcon\Db\ResultInterface
     */
    public static function modelSelect($table, $filter, $fileds = '*', $limit = 1, $connection = 'db')
    {
        $sql = 'select ' . $fileds . ' from ' . $table;
        $i = 0;
        $values = [];
        foreach ($filter as $key => $value) {
            if ($i == 0) {
                $sql .= ' where ' . $key . ' =? ';

            } else {
                $sql .= ' and ' . $key . '=?';
            }
            $values[$i] = $value;
            ++$i;
        }
        $sql .= ' limit ' . $limit;
        $connection = Application::getApp()->getPdoConnection($connection);
        return $connection->query($sql, $values);
    }

    public function setOperationMade($made)
    {
        $this->_operationMade = $made;
    }

    /**
     * @param $filter
     * @param string $fileds
     * @param string $connection
     * @return static
     */
    public static function modelFind($filter, $fileds = '*', $connection = 'db')
    {
        $model = new static();
        //fetch 从查询中获取一条数据，fetchAll  获取查询的所有数据集
        $row = $model->select($filter, $fileds, 1, $connection)->fetch();
        if (!$row) {
            return null;
        }
        foreach ($row as $key => $value) {
            $model->$key = $value;
        }
        $model->setOperationMade(self::OP_UPDATE);
        return $model;
    }

    /**
     * @param $table
     * @param $filter
     * @param int $limit
     * @param string $connection
     * @return bool
     */
    public static function modelDelete($table, $filter, $limit = 1, $connection = 'db')
    {
        $sql = 'delete ' . $table;
        $i = 0;
        $values = [];
        foreach ($filter as $key => $value) {
            if ($i == 0) {
                $sql .= ' where ' . $key . ' =? ';

            } else {
                $sql .= ' and ' . $key . '=?';
            }
            $values[$i] = $value;
            ++$i;
        }
        $sql .= ' limit ' . $limit;
        $connection = Application::$app->getPdoConnection($connection);
        return $connection->execute($sql, $values);
    }

    public static function modelUpdate($table, $filter, $data, $limit = 1, $connection = 'db')
    {
        $sql = 'update ' . $table;
        $i = 0;
        $values = [];
        foreach ($data as $key=>$value) {
            if ($i == 0) {
                $sql .= ' set ' . $key . '=?';
            } else {
                $sql .= ' ,' . $key . '=?';
            }
            $values[$i] = $value;
            ++$i;
        }
        $j = $i;
        foreach ($filter as $key => $value) {
            if ($j == $i) {
                $sql .= ' where ' . $key . ' =? ';

            } else {
                $sql .= ' and ' . $key . '=?';
            }
            $values[$j] = $value;
            ++$j;
        }
        $sql .= ' limit ' . $limit;
        $connection = Application::$app->getPdoConnection($connection);
        return $connection->execute($sql, $values);
    }

    /**
     * @param string $connection
     * @return array
     */
    public static function getErrorInfo($connection = 'db')
    {
        return Application::$app->getPdoConnection($connection)->getErrorInfo();
    }
}