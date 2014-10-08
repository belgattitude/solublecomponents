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
class UnitFormatter extends NumberFormatter
{

    /**
     *
     * @var string|null
     */
    protected $unit_column;

    /**
     *
     * @var array
     */
    protected $default_params = array(
        'decimals' => 2,
        'locale' => null,
        'pattern' => null,
        'unit' => null
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
                $locale, IntlNumberFormatter::DECIMAL
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

        if ($this->unit_column !== null) {
            if (!isset($row[$this->unit_column])) {
                throw new Exception\RuntimeException(__METHOD__ . " Cannot determine unit code based on column '{$this->unit_column}'.");
            }
            return $this->formatters[$formatterId]->format($number) . ' ' . $row[$this->unit_column];
        } else if ($this->params['unit'] != '') {
            $value = $this->formatters[$formatterId]->format($number) . ' ' . $this->params['unit'];
        } else {
            throw new Exception\RuntimeException(__METHOD__ . " Unit code must be set prior to use the UnitFormatter");
        }

        if (intl_is_failure($this->formatters[$formatterId]->getErrorCode())) {
            $this->throwNumberFormatterException($this->formatters[$formatterId], $number);
        }

        return $value;
    }

    /**
     * 
     *
     * @throws Exception\InvalidArgumentException
     * @param  string|RowColumn $unit
     * @return UnitFormat
     */
    public function setUnit($unit)
    {
        if ($unit instanceof RowColumn) {
            $this->unit_column = $currencyCode->getColumnName();
        } elseif (!is_string($unit) || trim($unit) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . " Unit must be an non empty string (or a RowColumn object)");
        }
        $this->params['unit'] = $unit;
        return $this;
    }

    /**
     * Get the 3-letter ISO 4217 currency code indicating the currency to use
     *
     * @return string
     */
    public function getUnit()
    {
        return $this->params['unit'];
    }

}
