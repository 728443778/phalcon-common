<?php

namespace app\common\services;

use app\core\traits\SingleInstance;
use Phalcon\Di\Injectable;

class AssetsManager extends Injectable
{
    use SingleInstance;

    /**
     * @var \Phalcon\Assets\Manager
     */
    protected $assets;

    public function init()
    {
        $this->assets = $this->getDI()->getShared('assets');
        $this->initAssets();
    }

    public function initAssets()
    {

    }

    public function addCss($path, $local=null, $filter=null, $attributes=null)
    {
        $this->assets->addCss($path, $local, $filter, $attributes);
    }

    public function addJs($path, $local=null, $filter=null, $attributes=null)
    {
        $this->assets->addJs($path, $local, $filter, $attributes);
    }

    public function getCss()
    {
        return $this->assets->getCss();
    }

    public function getJs()
    {
        return $this->assets->getJs();
    }

    public function outputCss($collectionName = null)
    {
        return $this->assets->outputCss($collectionName);
    }

    public function outputJs($collectionName = null)
    {
        return $this->assets->outputJs($collectionName);
    }

    /**
     * Adds a raw resource to the manager
     *
     * <code>
     * $assets->addResource(
     *     new Phalcon\Assets\Resource("css", "css/style.css")
     * );
     * </code>
     *
     * @param \Phalcon\Assets\Resource $resource
     * @return \Phalcon\Assets\Manager
     */
    public function addResource($resource)
    {
        return $this->assets->addResource($resource);
    }

    public function getAssets()
    {
        return $this->assets;
    }
}