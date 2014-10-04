<?php
namespace Soluble\Db\Metadata\Column\Definition;

class StringColumn extends AbstractColumnDefinition implements TextColumnInterface
{
    /**
     *
     * @var int
     */
    protected $characterMaximumLength = null;



    /**
     * @return int|null the $characterMaximumLength
     */
    public function getCharacterMaximumLength()
    {
        return $this->characterMaximumLength;
    }

    /**
     * @param int $characterMaximumLength the $characterMaximumLength to set
     * @return \Soluble\Db\Metadata\Column\StringColumn
     */
    public function setCharacterMaximumLength($characterMaximumLength)
    {
        $this->characterMaximumLength = $characterMaximumLength;
        return $this;
    }
}
