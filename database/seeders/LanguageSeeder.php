<?php

namespace Database\Seeders;

use App\Helpers\Downloader;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LanguageSeeder extends Seeder
{
    protected Downloader $downloader;
    protected LanguageRepositoryInterface $languageRepository;

    public function __construct(LanguageRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
        $this->downloader = new Downloader();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = resource_path('json/languages.json');
        if(File::exists($path)) {
            // Получаем содержимое файла
            try {
                $json = File::get($path);
                // Преобразуем JSON в массив объектов
                $data = json_decode($json); // вернёт массив stdClass объектов
                foreach ($data as $language) {
                    if (!$this->languageRepository->isExistLanguageByLocale($language->name)) {
                        $pathToImageOnServer = $this->downloader->downloadImage($language->flag_url, $language->code);
                        $this->languageRepository->saveLanguage($language->name,$language->native_name, $language->code,$language->locale, $pathToImageOnServer === null ? $language->flag_url : $pathToImageOnServer);
                    }
                }
            } catch (FileNotFoundException $e) {
                logger("Файл по пути $path отсутствует");
            }
        }
    }
}
