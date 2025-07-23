<?php

namespace App\Services;

use App\Exceptions\FailedGenerationPromocodeException;
use App\Repositories\PromocodeRepositories\PromocodeRepositoryInterface;
use Exception;

class PromocodeGeneratorService
{
    private array $config;
    private PromocodeRepositoryInterface $promocodeRepository;

    public function __construct(PromocodeRepositoryInterface $promocodeRepository)
    {
        $this->promocodeRepository = $promocodeRepository;
        $this->config = [
            'mask' => '****-****-****-****',
            'characters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            'exclude_ambiguous' => true, // Исключить O, 0, I, 1
            'max_attempts' => 100
        ];
        if ($this->config['exclude_ambiguous']) {
            $this->config['characters'] = str_replace(['O', '0', 'I', '1'], '', $this->config['characters']);
        }
    }
    public function getConfig(): array
    {
        return $this->config;
    }
    public function setConfig(array $config = []): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @throws Exception
     */
    public function generateCertainCountCode(int $countCodes): array
    {
        $promocodes = [];
        $correctCountCodes = 0;
        if($countCodes <= 0)
        {
            return $promocodes;
        }
        while($correctCountCodes < $countCodes)
        {
            $promocodes[] = $this->generate();
            $correctCountCodes++;
        }
        return $promocodes;
    }

    /**
     * @throws FailedGenerationPromocodeException
     */
    private function generate(): string
    {
        $attempts = 0;
        do {
            $code = $this->generateFromMask();
            $attempts++;
            if ($attempts > $this->config['max_attempts']) {
                throw new FailedGenerationPromocodeException($this->config['max_attempts']);
            }
        }
        while ($this->promocodeRepository->isExistPromocode($code));
        return $code;
    }

    private function generateFromMask(): string
    {
        $mask = $this->config['mask'];
        $characters = $this->config['characters'];
        return preg_replace_callback('/\*/', function() use ($characters) {
            return $characters[random_int(0, strlen($characters) - 1)];
        }, $mask);
    }
}
