<?php

namespace App\Services\ApiServices;


use App\Exceptions\HunspellNotInstallException;
use App\Exceptions\ProcessHunspellCheckException;
use App\Exceptions\UnsupportedDictionaryLanguageException;
use Symfony\Component\Process\Process;

class SpellCheckerService
{
    private array $dictionaries = [];
    /**
     * @throws HunspellNotInstallException
     */
    public function __construct()
    {
        $command = "wsl command -v hunspell 2>&1"; // `2>&1` перенаправляет stderr в stdout
        $output = shell_exec($command);
        if($output === null)
        {
            throw new HunspellNotInstallException();
        }
        $command = "wsl hunspell -D 2>&1"; // `2>&1` перенаправляет stderr в stdout
        $output = shell_exec($command);

        preg_match_all('/(\/.+(?:\.aff|\.dic))/', $output, $matches);

        $dictionaries = [];
        foreach ($matches[0] as $path) {
            $lang = basename($path, '.aff');  // Удаляем расширение
            $lang = basename($lang, '.dic');  // На случай, если это .dic
            $dictionaries[] = $lang;
        }
        $this->dictionaries = array_unique($dictionaries);
    }

    public function getAcceptedLanguages(): array
    {
        return $this->dictionaries;
    }

    /**
     * @throws UnsupportedDictionaryLanguageException
     * @throws ProcessHunspellCheckException
     */
    public function checkSentence(string $language, string $sentence): array
    {
        if(!in_array($language, $this->dictionaries))
        {
            throw new UnsupportedDictionaryLanguageException($language);
        }
        $process = new Process(['wsl', 'hunspell', '-d', $language]);
        $process->setInput("$sentence"); // слово для проверки
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessHunspellCheckException();
        }
        $lines = preg_split('/\r\n|\r|\n/', $process->getOutput());
        $result = [];

        foreach ($lines as $line) {
            if (str_starts_with($line, '&')) {
                if (preg_match('/^&\s+(\S+).*?:\s+(.*)$/', $line, $matches)) {
                    $wrongWord = $matches[1];
                    $suggestionsRaw = $matches[2];
                    $suggestions = array_map('trim', explode(',', $suggestionsRaw));
                    $result[] = (object)[
                        'original_word' => $wrongWord,
                        'suggestion' => $suggestions,
                    ];
                }
            }
        }
        return $result;
    }
}
