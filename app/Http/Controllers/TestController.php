<?php

namespace App\Http\Controllers;

use App\Enums\TypeStatus;
use App\Http\Responses\ApiResponse;
use App\Services\TextToSpeechService;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Browser\SpeechSynthesis;
use function PHPUnit\Framework\objectEquals;

class TestController extends Controller
{
    private array $headers;

    public function __construct()
    {

    }

    private function requestGetHistory()
    {
        return Http::withHeaders($this->headers)->get('https://freetts.ru/api/history');
    }

    private function requestSynthesis(string $text, string $voiceid, string $ext = 'mp3')
    {
        $body = [
            'ext' => $ext,
            'text' => $text,
            'voiceid' => $voiceid
        ];
        return Http::withHeaders($this->headers)->post('https://freetts.ru/api/synthesis', $body);
    }

    public function func()
    {

    }

    public function testTextToSpeech()
    {
        $textToSpeechService = new TextToSpeechService();
        $text = 'Hello';
        $lang = 'en';
        $voiceId = 'ytvJwPBbkqFc';
        $info = $textToSpeechService->getUrlForGeneratedAudio($text, $lang, $voiceId);
        if($info->status === TypeStatus::success->value)
        {
            return ApiResponse::success('Успех', (object)['url' => $info->url_download]);
        }
        return ApiResponse::error($info->message);
    }

    public function test()
    {
        $bool = true;
        if (!$bool) {
            logger('$requestHistory');
            $requestHistory = Http::get('https://freetts.ru/api/history');
            preg_match('/(uid=[^;]+)/', $requestHistory->headers()['Set-Cookie'][0], $matches);
            $uidString = $matches[1] ?? null;

            $this->headers = [
                'Accept' => '*/*',
                'Accept-Encoding' => 'gzip, deflate, br, zstd',
                'Accept-Language' => 'ru,en;q=0.9',
                'Connection' => 'keep-alive',
                'Cookie' => $uidString,
                'Host' => 'freetts.ru',
                'Referer' => 'https://freetts.ru/',
                'Sec-Fetch-Dest' => 'empty',
                'Sec-Fetch-Mode' => 'cors',
                'Sec-Fetch-Site' => 'same-origin',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 YaBrowser/25.6.0.0 Safari/537.36',
                'sec-ch-ua' => '"Chromium";v="136", "YaBrowser";v="25.6", "Not.A/Brand";v="99", "Yowser";v="2.5"',
                'sec-ch-ua-mobile' => '?0',
                'sec-ch-ua-platform' => '"Windows"'
            ];
            logger($uidString);
            logger('$requestSynthesis');
            $requestSynthesis = $this->requestSynthesis('Сука', 'NG6FIoMMe4L1');
            logger($requestSynthesis->json());
            while (true) {
                $requestGetHistory = $this->requestGetHistory();
                logger($requestGetHistory->json());
            }
            return;
        }
        $text = 'Fuck';
        $lang = 'en';
        $voiceId = 'ytvJwPBbkqFc';
        $serverUrl = 'http://localhost:57838';
        $capabilities = DesiredCapabilities::chrome();
        $driver = RemoteWebDriver::create($serverUrl, $capabilities);
        $url = 'https://freetts.ru/';
        $driver->get($url);
        $wait = new WebDriverWait($driver, 10); // 10 секунд таймаут

        // Ожидание элемента #area_container (аналог waitFor('#area_container', 10))
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('area_container')
        ));

        // Ожидание элемента #area (аналог waitFor('#area', 5))
        $wait5 = new WebDriverWait($driver, 5); // 5 секунд для второго элемента
        $wait5->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('area')
        ));

        // Ждем поле выбора языка - waitFor('div.selected[data-type="lang"]', 10)
        $wait10 = new WebDriverWait($driver, 10);
        $wait10->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('div.selected[data-type="lang"]')
        ));

        // Ждем поле выбора голоса - waitFor('div.selected[data-type="voice"]', 10)
        $wait10->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('div.selected[data-type="voice"]')
        ));

        // Ждем кнопку синтеза - waitFor('#btn_syn', 15)
        $wait15 = new WebDriverWait($driver, 15);
        $wait15->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('btn_syn')
        ));
        try {
            $waveContainer = $driver->findElement(WebDriverBy::id('wave_container'));
            if ($waveContainer) {
                // Подсчитываем количество блоков с записями
                $waveBlocks = $driver->findElements(WebDriverBy::cssSelector('#wave_container .wave_block'));
                $recordsCount = count($waveBlocks);
            }
        } catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
            // Элемент не найден, recordsCount остается 0
        }

        // Вводим текст в поле (аналог type())
        $textArea = $driver->findElement(WebDriverBy::id('area'));
        $textArea->clear(); // Очищаем поле перед вводом
        $textArea->sendKeys($text);

        // Кликаем по селектору языка
        $langSelector = $driver->findElement(WebDriverBy::cssSelector('div.selected[data-type="lang"]'));
        $langSelector->click();

        // Ждем появления контейнера с опциями
        $wait10 = new WebDriverWait($driver, 10);
        $wait10->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.options[style*="display: block"]')
        ));

        // Проверяем существование нужного языка
        $existsLang = false;
        try {
            $langOption = $driver->findElement(WebDriverBy::cssSelector(".option[data-code=\"$lang\"]"));
            $existsLang = $langOption !== null;
        } catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
            $existsLang = false;
        }

        if (!$existsLang) {
            // Обработка случая, когда язык не найден
            throw new \Exception("Язык '$lang' не найден в списке доступных языков");
        }

        // Выбираем нужный язык по коду
        $langOption = $driver->findElement(WebDriverBy::cssSelector(".option[data-type=\"lang\"][data-code=\"$lang\"]"));
        $langOption->click();

        // Ждем закрытия выпадающего списка (аналог waitUntilMissing)
        $wait5 = new WebDriverWait($driver, 5);
        $wait5->until(WebDriverExpectedCondition::invisibilityOfElementLocated(
            WebDriverBy::cssSelector('.options[style*="display: block"]')
        ));
// Клик по селектору голоса для открытия списка
        $voiceSelector = $driver->findElement(WebDriverBy::cssSelector('div.selected[data-type="voice"]'));
        $voiceSelector->click();

// Ждем появления выпадающего списка с голосами
        $wait10 = new WebDriverWait($driver, 10);
        $wait10->until(WebDriverExpectedCondition::visibilityOfElementLocated(
            WebDriverBy::cssSelector('.options[style*="display: block"]')
        ));

// Проверяем существование нужного голоса
        $optionVoiceExists = false;
        try {
            $voiceOption = $driver->findElement(WebDriverBy::cssSelector(".option[data-type=\"voice\"][data-id=\"{$voiceId}\"]"));
            $optionVoiceExists = $voiceOption !== null;
        } catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
            $optionVoiceExists = false;
        }

        if (!$optionVoiceExists) {
            throw new \Exception("Голос с ID '$voiceId' не найден в списке доступных голосов");
        }

// Кликаем по нужному голосу
        $voiceOption = $driver->findElement(WebDriverBy::cssSelector(".option[data-type=\"voice\"][data-id=\"{$voiceId}\"]"));
        $voiceOption->click();

// Ждем закрытия выпадающего списка
        $wait5 = new WebDriverWait($driver, 5);
        $wait5->until(WebDriverExpectedCondition::invisibilityOfElementLocated(
            WebDriverBy::cssSelector('.options[style*="display: block"]')
        ));

// Кликаем кнопку синтеза
        $synthesizeButton = $driver->findElement(WebDriverBy::id('btn_syn'));
        $synthesizeButton->click();
        $is_success = false;
// Цикл ожидания завершения обработки
        while (true) {

            try {
                $statusElement = $driver->findElement(WebDriverBy::id('info_text'));
                $statusMessage = $statusElement->getText();
            } catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
                $statusMessage = '';
            }

// Проверяем финальные сообщения
            if ($statusMessage === 'Обработка: 100%') {
                $is_success = true;
                break;
            }
            if ($statusMessage === 'Текст не соответствует выбранному языку') {
                break;
            }
        }
        if ($is_success) {
            // Ждем контейнер с записями
            $wait1 = new WebDriverWait($driver, 1);
            $wait1->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('wave_container')
            ));

            // Ждем появления первого блока с аудиозаписью
            $wait30 = new WebDriverWait($driver, 30);
            $wait30->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#wave_container .wave_block:first-child')
            ));

            // Ждем кнопку скачивания в первом блоке
            $wait15 = new WebDriverWait($driver, 15);
            $wait15->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#wave_container .wave_block:first-child .btn_download')
            ));

            // Кликаем на кнопку скачивания
            $downloadButton = $driver->findElement(WebDriverBy::cssSelector('#wave_container .wave_block:first-child .btn_download'));
            $hrefToDownload = $downloadButton->getAttribute('href');
            $driver->close();
            try {
                // Отправляем GET запрос для скачивания файла
                $response = Http::get($hrefToDownload);

                if ($response->failed()) {
                    throw new \Exception('Не удалось скачать файл. HTTP код: ' . $response->status());
                }

                // Генерируем имя файла, если не указано
                $filename = 'audio_' . time() . '.mp3';

                // Путь для сохранения в storage
                $storagePath = 'audio/' . $filename;

                // Сохраняем файл в storage/app/public/audio/
                Storage::disk('public')->put($storagePath, $response->body());

                return $storagePath;

            } catch (\Exception $e) {
                throw new \Exception('Ошибка скачивания файла: ' . $e->getMessage());
            }
            //return ApiResponse::success('Ссылка для скачивания', (object)['href'=>$hrefToDownload]);
            //$downloadButton->click();
        }

        /*$test = new SpeechSynthesis('testTextToSpeech', 'ru', 'NG6FIoMMe4L1');
        try {
            $test->textToSpeech();
            $filePath = $test->getDownloadedFilePath();

            if ($filePath && file_exists($filePath)) {
                echo "✅ Тест выполнен успешно!";
                echo "Файл скачан: {$filePath}";
                echo "Размер файла: " . filesize($filePath) . " байт";
                return ApiResponse::success("Удалось скачать файл", (object)['filePath' => $filePath]);
            } else {
                echo "❌ Файл не был скачан";
                return ApiResponse::error("не удалось скачать файл");
            }

        } catch (\Exception $e) {
            echo "❌ Ошибка выполнения теста: " . $e->getMessage();
            return ApiResponse::error("Ошибка при скачивании файла");
        }*/
    }
}
