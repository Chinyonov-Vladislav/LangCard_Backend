<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;

trait HasTableColumns
{
    /**
     * Получить наименования всех столбцов таблицы, связанной с моделью.
     *
     * @return array
     */
    public function getTableColumns(): array
    {
        return Schema::getColumnListing($this->getTable());
    }
}
