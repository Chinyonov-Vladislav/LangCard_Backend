<?php

namespace Database\Seeders;

use App\Repositories\TimezoneRepositories\TimezoneRepositoryInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TimezoneSeeder extends Seeder
{
    protected TimezoneRepositoryInterface $timezoneRepository;

    public function __construct(TimezoneRepositoryInterface $timezoneRepository)
    {
        $this->timezoneRepository = $timezoneRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = resource_path('json/timezones_with_utc.json');
        if(File::exists($path)) {
            // Получаем содержимое файла
            try {
                $json = File::get($path);
                // Преобразуем JSON в массив объектов
                $data = json_decode($json); // вернёт массив stdClass объектов
                foreach ($data as $timezone_json) {
                    if (!$this->timezoneRepository->isExistTimezoneByNameRegion($timezone_json->zone)) {
                        $this->timezoneRepository->saveNewTimezone($timezone_json->zone, $timezone_json->utc_offset);
                    }
                }
            } catch (FileNotFoundException) {
                logger("Файл по пути $path отсутствует");
            }
        }
    }
}
