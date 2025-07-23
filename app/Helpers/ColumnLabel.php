<?php

namespace App\Helpers;

class ColumnLabel
{
    public string $nameColumn;
    public string $name;

    public function __construct(string $nameColumn, string $name)
    {
        $this->nameColumn = $nameColumn;
        $this->name = $name;
    }
}
