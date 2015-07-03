<?php

namespace Soluble\FlexStore\Options;

class HydrationOptions
{
    /**
     *
     * @var array
     */
    protected $params = array();
    
    /**
     *
     * @var array
     */
    protected $default_params = array(
        'disable_formatters' => false,
        'disable_renderers' => false,
        'disable_column_exclusion' => false
    );

    /**
     *
     */
    public function __construct()
    {
        $this->params = $this->default_params;
    }

    /**
     * Disable formatters processing when getting data
     *
     * @return HydrationOptions
     */
    public function disableFormatters()
    {
        $this->params['disable_formatters'] = true;
        return $this;
    }

    /**
     * Enable formatters processing when getting data
     *
     * @return HydrationOptions
     */
    public function enableFormatters()
    {
        $this->params['disable_formatters'] = false;
        return $this;
    }

    /**
     * Test chether formatters should be called when getting data
     *
     * @return bool
     */
    public function isFormattersEnabled()
    {
        return ($this->params['disable_formatters'] == false);
    }

    /**
     * Disable renderers processing when getting data
     *
     * @return HydrationOptions
     */
    public function disableRenderers()
    {
        $this->params['disable_renderers'] = true;
        return $this;
    }

    /**
     * Enable renderers processing when getting data
     *
     * @return HydrationOptions
     */
    public function enableRenderers()
    {
        $this->params['disable_renderers'] = false;
        return $this;
    }

    /**
     * Test whether renderers should be called when getting data
     *
     * @return bool
     */
    public function isRenderersEnabled()
    {
        return ($this->params['disable_renderers'] == false);
    }


    /**
     * Disable column exclusion when getting data
     *
     * @return HydrationOptions
     */
    public function disableColumnExclusion()
    {
        $this->params['disable_column_exclusion'] = true;
        return $this;
    }

    /**
     * Enable column exclusion when getting data
     *
     * @return HydrationOptions
     */
    public function enableColumnExclusion()
    {
        $this->params['disable_column_exclusion'] = false;
        return $this;
    }

    /**
     * Test whether column model exclusions are enabled
     *
     * @return bool
     */
    public function isColumnExclusionEnabled()
    {
        return ($this->params['disable_column_exclusion'] == false);
    }
}
