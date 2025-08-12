<?php

namespace App\Services;

use App\Enums\TypeStatus;
use App\Exceptions\ChromeDriverNotStartedException;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Illuminate\Support\Facades\Process;
use Log;

class TextToSpeechService
{
    private RemoteWebDriver $driver;
    private string $serverUrl;
    private const BASE_URl = 'https://freetts.ru/';

    private const DEFAULT_PORT = '9515';

    /**
     * @throws ChromeDriverNotStartedException
     * @throws Exception
     */
    public function __construct()
    {
        $chromedriverPath = storage_path('app/bin/chromedriver.exe');
        if (!file_exists($chromedriverPath)) {
            throw new ChromeDriverNotStartedException("ChromeDriver not found at: " . $chromedriverPath);
        }
        $result = Process::start([
            $chromedriverPath,
            '--port=' . self::DEFAULT_PORT,
        ]);
        if (!$result->running()) {
            $error = $result->errorOutput();
            throw new ChromeDriverNotStartedException("Failed to start ChromeDriver: " . $error);
        }
        $this->serverUrl = "http://127.0.0.1:" . self::DEFAULT_PORT;

    }

    private function killExistingChromeDriver(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            exec('taskkill /F /IM chromedriver.exe 2>NUL');
        } else {
            exec('pkill -f chromedriver');
        }
    }

    /**
     * Получает ссылку на сгенерированный аудиофайл
     */
    public function getUrlsForGeneratedAudio(array $data): array
    {
        $result = [];
        try {
            $this->initializeDriver();
            $this->loadWebsite();
            $this->waitForPageElements();
            foreach ($data as $item) {
                $previousCountGeneratedAudio = $this->getCountGeneratedAudio();
                $this->inputText($item->text);
                $this->selectLanguage($item->lang);
                $this->selectVoice($item->voiceId);
                $this->synthesizeText();
                if ($this->waitForProcessingCompletion() === false) {
                    $result[] = (object)['status' => TypeStatus::error->value, 'text' => $item->text, 'lang' => $item->lang, "voiceId" => $item->voiceId, 'destination' => $item->destination, 'voice_name' => $item->voice_name];
                    continue;
                }
                $this->waitForAudioGeneration();
                $countAttempts = 10;
                $currentAttempt = 1;
                while ($currentAttempt < $countAttempts) {
                    $currentCountGeneratedAudio = $this->getCountGeneratedAudio();
                    if ($currentCountGeneratedAudio - $previousCountGeneratedAudio === 1) {
                        $download_url = $this->getDownloadUrl();
                        $result[] = (object)['status' => TypeStatus::success->value, 'text' => $item->text, 'lang' => $item->lang, "voiceId" => $item->voiceId, 'url_download' => $download_url, 'destination' => $item->destination, 'voice_name' => $item->voice_name];
                        break;
                    }
                    $currentAttempt++;
                    sleep(1);
                }
                if($currentAttempt === $countAttempts) {
                    $result[] = (object)['status' => TypeStatus::error->value, 'text' => $item->text, 'lang' => $item->lang, "voiceId" => $item->voiceId, 'destination' => $item->destination, 'voice_name' => $item->voice_name];
                }
            }
            $this->closeDriver();
            return $result;
        } catch (Exception $e) {
            Log::error("Произошла ошибка при генерации озвучки: " . $e->getMessage());
            $this->closeDriver();
            return $result;
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
     * Закрытие драйвера
     */
    private function closeDriver(): void
    {
        if (isset($this->driver)) {
            $this->driver->close();
        }
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
     * @throws NoSuchElementException|TimeoutException
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
        if ($this->validateLanguageExists($lang)) {
            $this->selectLanguageOption($lang);
            $this->waitForDropdownClose();
        }
    }

    /**
     * Открытие выпадающего списка языков
     * @throws NoSuchElementException|TimeoutException
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
    private function validateLanguageExists(string $lang): bool
    {
        return $this->driver->findElement(WebDriverBy::cssSelector(".option[data-code=\"$lang\"]")) !== null;
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
     * @throws NoSuchElementException|TimeoutException
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
            WebDriverBy::cssSelector(".option[data-type=\"voice\"][data-id=\"$voiceId\"]")
        );
    }

    /**
     * Выбор опции голоса
     */
    private function selectVoiceOption(string $voiceId): void
    {
        $voiceOption = $this->driver->findElement(
            WebDriverBy::cssSelector(".option[data-type=\"voice\"][data-id=\"$voiceId\"]")
        );
        $voiceOption->click();
    }

    /**
     * Ожидание закрытия выпадающего списка
     * @throws NoSuchElementException|TimeoutException
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
            if (str_starts_with($statusMessage, "Обработка:")) {
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

    private function getCountGeneratedAudio(): int
    {
        try {
            $wait = new WebDriverWait($this->driver, 3);
            $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('wave_container')
            ));

            $waveContainer = $this->driver->findElement(WebDriverBy::id('wave_container'));
            $children = $waveContainer->findElements(WebDriverBy::xpath('./*'));
            $childrenCount = count($children);
        } catch (TimeoutException|NoSuchElementException|Exception) {
            // Элемент не найден в течение времени ожидания
            $childrenCount = 0;
        }
        return $childrenCount;
    }

    /**
     * Ожидание генерации аудио
     * @throws NoSuchElementException|TimeoutException
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


}
