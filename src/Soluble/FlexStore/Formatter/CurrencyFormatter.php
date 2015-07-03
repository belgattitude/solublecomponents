<?php

namespace Soluble\FlexStore\Formatter;

use Soluble\FlexStore\Exception;
use Soluble\FlexStore\Formatter\RowColumn;
use ArrayObject;
use \NumberFormatter as IntlNumberFormatter;

/**
 * columns
 *  - price:
 *    - formatter:
 *          - money
 *              - currency_code
 *              - locale
 *
 */
class CurrencyFormatter extends NumberFormatter
{
    /**
     *
     * @var string|null
     */
    protected $currency_column;

    /**
     *
     * @var array
     */
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
        parent::__construct($params);
    }

    /**
     *
     * @param string $formatterId
     */
    protected function loadFormatterId($formatterId)
    {
        $locale = $this->params['locale'];
        $this->formatters[$formatterId] = new IntlNumberFormatter(
            $locale,
            IntlNumberFormatter::CURRENCY
        );
        $this->formatters[$formatterId]->setAttribute(IntlNumberFormatter::FRACTION_DIGITS, $this->params['decimals']);
        if ($this->params['pattern'] !== null) {
            $this->formatters[$formatterId]->setPattern($this->params['pattern']);
        }
    }

    /**
     * Currency format a number
     *
     * @throws Exception\RuntimeException
     * @param  float|string|int  $number
     * @return string
     */
    public function format($number, ArrayObject $row = null)
    {
        $locale = $this->params['locale'];

        //$formatterId = md5($locale);
        $formatterId = $locale . (string) $this->params['pattern'];

        if (!array_key_exists($formatterId, $this->formatters)) {
            $this->loadFormatterId($formatterId);
        }

        if ($this->currency_column !== null) {
            if (!isset($row[$this->currency_column])) {
                throw new Exception\RuntimeException(__METHOD__ . " Cannot determine currency code based on column '{$this->currency_column}'.");
            }
            $value = $this->formatters[$formatterId]->formatCurrency(
                $number,
                $row[$this->currency_column]
            );
        } else {
            if ($this->params['currency_code'] == '') {
                throw new Exception\RuntimeException(__METHOD__ . " Currency code must be set prior to use the currency formatter");
            }

            $value = $this->formatters[$formatterId]->formatCurrency(
                $number,
                $this->params['currency_code']
            );
        }
        
        if (intl_is_failure($this->formatters[$formatterId]->getErrorCode())) {
            $this->throwNumberFormatterException($this->formatters[$formatterId], $number);
        }
        
        return $value;
    }

    /**
     * Parse a
     * @param string $value
     * @return array|null
     */
    public function parse($value)
    {
        $locale = $this->params['locale'];
        //$formatterId = md5($locale);
        $formatterId = $locale;
        if (!array_key_exists($formatterId, $this->formatters)) {
            $this->loadFormatterId($formatterId);
        }
        // Currency will be passed as reference
        // setting it to null prevent eventual warning
        $currency = null;
        $result = $this->formatters[$formatterId]->parseCurrency($value, $currency);
        
        if ($value === false) {
            return null;
        }
        return array('value' => $result, 'currency' => $currency);
    }

    /**
     * The 3-letter ISO 4217 currency code indicating the currency to use
     *
     * @throws Exception\InvalidArgumentException
     * @param  string|RowColumn $currencyCode
     * @return CurrencyFormatter
     */
    public function setCurrencyCode($currencyCode)
    {
        if ($currencyCode instanceof RowColumn) {
            $this->currency_column = $currencyCode->getColumnName();
        } elseif (!is_string($currencyCode) || trim($currencyCode) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Currency code must be an non empty string (or a RowColumn object)");
        }
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
}
