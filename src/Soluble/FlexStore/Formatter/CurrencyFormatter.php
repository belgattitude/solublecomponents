<?php

namespace Soluble\FlexStore\Formatter;

use Soluble\FlexStore\Exception;
use Soluble\FlexStore\I18n\LocalizableInterface;
use Soluble\FlexStore\Formatter\RowColumn;
use ArrayObject;
use Locale;
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
        $formatterId = $locale;

        if (!array_key_exists($formatterId, $this->formatters)) {
            $this->formatters[$formatterId] = new IntlNumberFormatter(
                    $locale, IntlNumberFormatter::CURRENCY
            );
            $this->formatters[$formatterId]->setAttribute(IntlNumberFormatter::FRACTION_DIGITS, $this->params['decimals']);
            if ($this->params['pattern'] !== null) {
                $this->formatters[$formatterId]->setPattern($this->params['pattern']);
            }
        }
        
        if ($this->currency_column !== null) {
            $this->params['currency_code'] = $row[$this->currency_column];
            if (!isset($row[$this->currency_column])) {
                throw new Exception\RuntimeException(__METHOD__ . " Cannot determine currency code based on column '{$this->currency_column}'.");
            }
            return $this->formatters[$formatterId]->formatCurrency(
                            $number, $row[$this->currency_column]
                   );
        } 
        if ($this->params['currency_code'] == '') {
            throw new Exception\RuntimeException(__METHOD__ . " Currency code must be set prior to use the currency formatter");
        }

        return $this->formatters[$formatterId]->formatCurrency(
                        $number, $this->params['currency_code']
               );
            

    }

    /**
     * The 3-letter ISO 4217 currency code indicating the currency to use
     *
     * @throws Exception\InvalidArgumentException
     * @param  string|RowColumn $currencyCode
     * @return CurrencyFormat
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

}
