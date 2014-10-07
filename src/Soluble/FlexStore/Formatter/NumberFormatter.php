<?php

namespace Soluble\FlexStore\Formatter;

use Soluble\FlexStore\Exception;
use Soluble\FlexStore\I18n\LocalizableInterface;
use ArrayObject;
use Locale;
use \NumberFormatter as IntlNumberFormatter;

class NumberFormatter implements FormatterInterface, LocalizableInterface, FormatterNumberInterface
{

    /**
     * Formatter instances
     *
     * @var array
     */
    protected $formatters = array();

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
        'decimals' => 2,
        'locale' => null,
    );

    /**
     * @param array $params
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present
     */
    public function __construct(array $params = array())
    {
        if (!extension_loaded('intl')) {
            throw new Exception\ExtensionNotLoadedException(sprintf(
                    '%s component requires the intl PHP extension', __NAMESPACE__
            ));
        }
        $this->default_params['locale'] = Locale::getDefault();

        $this->setParams($params);
    }

    /**
     * 
     * @param array $params
     */
    protected function setParams($params)
    {
        $this->params = $this->default_params;
        foreach($params as $name => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($name))));
            $this->$method($value);
        }
        
    }

    /**
     * Format a number
     *
     * @param  float  $number
     * @return string
     */
    public function format($number, ArrayObject $row = null)
    {
        $locale = $this->params['locale'];

        //$formatterId = md5($locale);
        $formatterId = $locale;

        if (!array_key_exists($formatterId, $this->formatters)) {
            $this->formatters[$formatterId] = new IntlNumberFormatter(
                    $locale, IntlNumberFormatter::DECIMAL
            );
            $this->formatters[$formatterId]->setAttribute(IntlNumberFormatter::FRACTION_DIGITS, $this->params['decimals']);
            if ($this->params['pattern'] !== null) {
                $this->formatters[$formatterId]->setPattern($this->params['pattern']);
            }
        }

        return $this->formatters[$formatterId]->format($number);
    }




    /**
     * Set locale to use instead of the default
     *
     * @param  string $locale
     * @return NumberFormatter
     */
    public function setLocale($locale)
    {
        $this->params['locale'] = (string) $locale;
        return $this;
    }

    /**
     * Get the locale to use
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->params['locale'];
    }
    
    /**
     * Set decimals
     * 
     * @param int $decimals
     * @return NumberFormatter
     */
    public function setDecimals($decimals)
    {
        $this->params['decimals'] = (int) $decimals;
        return $this;
    }
    
    /**
     * 
     * @return int
     */
    public function getDecimals()
    {
        return $this->params['decimals'];
    }

}
