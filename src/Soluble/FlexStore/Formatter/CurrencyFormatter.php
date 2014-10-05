<?php

namespace Soluble\FlexStore\Formatter;

use Soluble\FlexStore\Exception;
use Soluble\FlexStore\I18n\LocalizableInterface;
use ArrayObject;
use Locale;
use NumberFormatter;

/**
 * columns
 *  - price:
 *    - formatter: 
 *          - money
 *              - currency_code
 *              - locale
 * 
 */
class CurrencyFormatter implements FormatterInterface, LocalizableInterface
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
    protected $default_params = array(
        'decimals' => 2,
        'locale' => null,
        'pattern' => null,
        'currency_code' => null
    );

    /**
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
        $this->params = array_merge($params, $this->default_params);
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
            $this->formatters[$formatterId] = new NumberFormatter(
                    $locale, NumberFormatter::CURRENCY
            );
            $this->formatters[$formatterId]->setAttribute(NumberFormatter::FRACTION_DIGITS, $this->params['decimals']);
            if ($this->params['pattern'] !== null) {
                $this->formatters[$formatterId]->setPattern($this->params['pattern']);
            }
        }

        return $this->formatters[$formatterId]->formatCurrency(
                        $number, $this->params['currency_code']
        );
    }

    /**
     * The 3-letter ISO 4217 currency code indicating the currency to use
     *
     * @param  string $currencyCode
     * @return CurrencyFormat
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->params['currency_code'] = $currencyCode;
        return $this;
    }

    /**
     * Get the 3-letter ISO 4217 currency code indicating the currency to use
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->params['currency_code'];
    }

    /**
     * Set the currency pattern
     *
     * @param  string $currencyPattern
     * @return CurrencyFormat
     */
    public function setCurrencyPattern($currencyPattern)
    {
        $this->params['pattern'] = $currencyPattern;
        return $this;
    }

    /**
     * Get the currency pattern
     *
     * @return string|null
     */
    public function getCurrencyPattern()
    {
        return $this->params['pattern'];
    }

    /**
     * Set locale to use instead of the default
     *
     * @param  string $locale
     * @return CurrencyFormatter
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

}
