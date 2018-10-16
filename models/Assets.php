<?php

namespace app\common\models;

class Assets extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var integer
     */
    public $type;

    /**
     *
     * @var integer
     */
    public $status;

    /**
     *
     * @var string
     */
    public $url;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("long_term_project");
        $this->setSource("assets");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'assets';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Assets[]|Assets|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Assets|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
