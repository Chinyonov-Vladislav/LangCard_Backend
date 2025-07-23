<?php

namespace App\Exceptions;

use Exception;

class UnsupportedDictionaryLanguageException extends Exception
{
    public function __construct(string $language)
    {
        parent::__construct(__('api.dictionary_lang_for_hunspell_not_found', ['language' => $language]));
    }
}
