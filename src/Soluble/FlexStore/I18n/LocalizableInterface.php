<?php

namespace Soluble\FlexStore\I18n;

interface LocalizableInterface
{

    /**
     * Get locale
     *
     * @return string $locale
     */
    public function getLocale();

    /**
     * Set locale
     *
     * @param string $locale
     */
    public function setLocale($locale);
}
