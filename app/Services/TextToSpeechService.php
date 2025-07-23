<?php

namespace App\Services;

use App\Enums\TypeStatus;
use App\Exceptions\ChromeDriverNotStartedException;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class TextToSpeechService
{


    private RemoteWebDriver $driver;
    private string $serverUrl;
    private Const BASE_URl = 'https://freetts.ru/';

    private Const DEFAULT_PORT = '9515';

    public function __construct(string $port = '9515')
    {
        $chromedriverPath = storage_path('app/bin/chromedriver.exe');
        $result = Process::start($chromedriverPath. " --port=".self::DEFAULT_PORT);
        if(!$result->running()) {
            throw new ChromeDriverNotStartedException();
        }
        $this->serverUrl = "http://localhost:$port";
    }


    /**
     * Получает ссылку на сгенерированный аудиофайл
     */
    public function getUrlForGeneratedAudio(string $text, string $lang, string $voiceId): object
    {
        $this->initializeDriver();
        try {
            $this->loadWebsite();
            $this->waitForPageElements();
            $previousCountGeneratedAudio = $this->getCountGeneratedAudio();
            $this->inputText($text);
            $this->selectLanguage($lang);
            $this->selectVoice($voiceId);
            $this->synthesizeText();
            if ($this->waitForProcessingCompletion() === false) {
                throw new Exception('Обработка текста завершилась неудачно');
            }
            $this->waitForAudioGeneration();
            $currentCountGeneratedAudio = $this->getCountGeneratedAudio();
            if($currentCountGeneratedAudio - $previousCountGeneratedAudio === 1) {
                $download_url = $this->getDownloadUrl();
                logger("DOWNLOAD URL: {$download_url}");
                return (object)['status' => TypeStatus::success->value, 'url_download' => $download_url];
            }
            return (object)['status'=>TypeStatus::error->value, 'message'=>"Количество новых сгенерированных аудиозаписей больше, чем 1"];
        }
        catch (Exception $e) {
            return (object)['status'=>TypeStatus::error->value, 'message'=>$e->getMessage()];
        } finally {
            $this->closeDriver();
        }
    }

    /**
     * Инициализация WebDriver
     */
    private function initializeDriver(): void
    {
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create($this->serverUrl, $capabilities);
    }

    /**
     * Загрузка веб-сайта
     */
    private function loadWebsite(): void
    {
        $this->driver->get(self::BASE_URl);
    }

    /**
     * Ожидание загрузки основных элементов страницы
     */
    private function waitForPageElements(): void
    {
        $wait = new WebDriverWait($this->driver, 10);

        // Ожидание основных элементов
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('area_container')
        ));

        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('area')
        ));

        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('div.selected[data-type="lang"]')
        ));

        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('div.selected[data-type="voice"]')
        ));

        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('btn_syn')
        ));
    }
    /**
     * Ввод текста в поле
     */
    private function inputText(string $text): void
    {
        $textArea = $this->driver->findElement(WebDriverBy::id('area'));
        $textArea->clear();
        $textArea->sendKeys($text);
    }

    /**
     * Выбор языка
     * @throws Exception
     */
    private function selectLanguage(string $lang): void
    {
        $this->openLanguageDropdown();
        $this->validateLanguageExists($lang);
        $this->selectLanguageOption($lang);
        $this->waitForDropdownClose();
    }

    /**
     * Открытие выпадающего списка языков
     */
    private function openLanguageDropdown(): void
    {
        $langSelector = $this->driver->findElement(WebDriverBy::cssSelector('div.selected[data-type="lang"]'));
        $langSelector->click();

        $wait = new WebDriverWait($this->driver, 10);
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('.options[style*="display: block"]')
        ));
    }

    /**
     * Проверка существования языка
     */
    private function validateLanguageExists(string $lang): void
    {
        try {
            $this->driver->findElement(WebDriverBy::cssSelector(".option[data-code=\"$lang\"]"));
        } catch (NoSuchElementException $e) {
            throw new Exception("Язык '$lang' не найден в списке доступных языков");
        }
    }

    /**
     * Выбор опции языка
     */
    private function selectLanguageOption(string $lang): void
    {
        $langOption = $this->driver->findElement(
            WebDriverBy::cssSelector(".option[data-type=\"lang\"][data-code=\"$lang\"]")
        );
        $langOption->click();
    }

    /**
     * Выбор голоса
     * @throws Exception
     */
    private function selectVoice(string $voiceId): void
    {
        $this->openVoiceDropdown();
        $this->validateVoiceExists($voiceId);
        $this->selectVoiceOption($voiceId);
        $this->waitForDropdownClose();
    }

    /**
     * Открытие выпадающего списка голосов
     */
    private function openVoiceDropdown(): void
    {
        $voiceSelector = $this->driver->findElement(WebDriverBy::cssSelector('div.selected[data-type="voice"]'));
        $voiceSelector->click();

        $wait = new WebDriverWait($this->driver, 10);
        $wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(
            WebDriverBy::cssSelector('.options[style*="display: block"]')
        ));
    }

    /**
     * Проверка существования голоса
     */
    private function validateVoiceExists(string $voiceId): void
    {
        $this->driver->findElement(
            WebDriverBy::cssSelector(".option[data-type=\"voice\"][data-id=\"{$voiceId}\"]")
        );
    }

    /**
     * Выбор опции голоса
     */
    private function selectVoiceOption(string $voiceId): void
    {
        $voiceOption = $this->driver->findElement(
            WebDriverBy::cssSelector(".option[data-type=\"voice\"][data-id=\"{$voiceId}\"]")
        );
        $voiceOption->click();
    }

    /**
     * Ожидание закрытия выпадающего списка
     */
    private function waitForDropdownClose(): void
    {
        $wait = new WebDriverWait($this->driver, 5);
        $wait->until(WebDriverExpectedCondition::invisibilityOfElementLocated(
            WebDriverBy::cssSelector('.options[style*="display: block"]')
        ));
    }

    /**
     * Запуск синтеза речи
     */
    private function synthesizeText(): void
    {
        $synthesizeButton = $this->driver->findElement(WebDriverBy::id('btn_syn'));
        $synthesizeButton->click();
    }

    /**
     * Ожидание завершения обработки
     * @throws Exception
     */
    private function waitForProcessingCompletion(): bool
    {
        while (true) {
            $statusMessage = $this->getStatusMessage();
            if ($statusMessage === 'Обработка: 100%') {
                return true;
            }
            if(str_starts_with($statusMessage, "Обработка:"))
            {
                continue;
            }
            if ($statusMessage === 'Текст не соответствует выбранному языку' || $statusMessage === '') {
                return false;
            }
            sleep(1);
        }
    }

    /**
     * Получение статусного сообщения
     */
    private function getStatusMessage(): string
    {
        $statusElement = $this->driver->findElement(WebDriverBy::id('info_text'));
        return $statusElement->getText();
    }

    /**
     * Получение URL для скачивания
     */
    private function getDownloadUrl(): string
    {
        $downloadButton = $this->driver->findElement(
            WebDriverBy::cssSelector('#wave_container .wave_block:first-child .btn_download')
        );
        return $downloadButton->getAttribute('href');
    }

    private function getCountGeneratedAudio()
    {
        try {
            $wait = new WebDriverWait($this->driver, 3);
            $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('wave_container')
            ));

            $waveContainer = $this->driver->findElement(WebDriverBy::id('wave_container'));
            $children = $waveContainer->findElements(WebDriverBy::xpath('./*'));
            $childrenCount = count($children);
        } catch (\Facebook\WebDriver\Exception\TimeoutException $e) {
            // Элемент не найден в течение времени ожидания
            $childrenCount = 0;
        } catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
            // Элемент не найден
            $childrenCount = 0;
        }
        return $childrenCount;
    }

    /**
     * Ожидание генерации аудио
     */
    private function waitForAudioGeneration(): void
    {
        $wait = new WebDriverWait($this->driver, 30);

        // Ожидание контейнера с записями
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::id('wave_container')
        ));

        // Ожидание первого блока с аудио
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('#wave_container .wave_block:first-child')
        ));

        // Ожидание кнопки скачивания
        $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
            WebDriverBy::cssSelector('#wave_container .wave_block:first-child .btn_download')
        ));
    }



    /**
     * Закрытие драйвера
     */
    private function closeDriver(): void
    {
        if (isset($this->driver)) {
            $this->driver->close();
        }
    }

}
