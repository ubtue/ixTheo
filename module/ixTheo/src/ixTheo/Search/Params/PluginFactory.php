<?php

namespace ixTheo\Search\Params;

class PluginFactory extends \VuFind\Search\Params\PluginFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->defaultNamespace = 'ixTheo\Search';
        $this->classSuffix = '\Params';
    }
}
