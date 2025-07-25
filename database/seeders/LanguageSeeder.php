<?php

namespace Database\Seeders;

use App\Enums\TypeFolderForFiles;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Services\FileServices\DownloadFileService;
use App\Services\FileServices\SaveFileService;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LanguageSeeder extends Seeder
{
    protected LanguageRepositoryInterface $languageRepository;
    protected DownloadFileService $downloadFileService;
    protected SaveFileService $saveFileService;

    public function __construct(LanguageRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
        $this->downloadFileService = new DownloadFileService();
        $this->saveFileService = new SaveFileService();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = resource_path('json/languages.json');
        // Получаем содержимое файла
        try {
            $json = File::get($path);
            // Преобразуем JSON в массив объектов
            $data = json_decode($json); // вернёт массив stdClass объектов
            foreach ($data as $language) {
                if (!$this->languageRepository->isExistLanguageByLocale($language->name)) {
                    try {
                        $imageFile = $this->downloadFileService->downloadFile($language->flag_url);
                        if ($imageFile === null) {
                            $pathToImageOnServer = null;
                        } else {
                            $pathToImageOnServer = $this->saveFileService->saveFile($imageFile);
                        }
                        $this->languageRepository->saveLanguage($language->name, $language->native_name, $language->code, $language->locale, $pathToImageOnServer === null ? $language->flag_url : $pathToImageOnServer);
                    } catch (Exception $e) {
                        logger("Произошла ошибка при скачивании флага для языка $language->name по ссылке $language->flag_url. Текст ошибки: {$e->getMessage()}");
                    }

                }
            }
        } catch (FileNotFoundException) {
            logger("Файл по пути $path отсутствует");
        }
    }
}
