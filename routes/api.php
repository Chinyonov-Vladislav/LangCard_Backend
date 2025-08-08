<?php


use App\Http\Controllers\Api\V1\AnswerController;
use App\Http\Controllers\Api\V1\AuthControllers\AuthController;
use App\Http\Controllers\Api\V1\AuthControllers\ForgotPasswordController;
use App\Http\Controllers\Api\V1\AuthControllers\RegistrationController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\ColumnsController;
use App\Http\Controllers\Api\V1\DailyRewardController;
use App\Http\Controllers\Api\V1\DeckController;
use App\Http\Controllers\Api\V1\EmailVerificationController;
use App\Http\Controllers\Api\V1\FilterDataController;
use App\Http\Controllers\Api\V1\HistoryAttemptsTestController;
use App\Http\Controllers\Api\V1\HistoryPurchaseController;
use App\Http\Controllers\Api\V1\InviteController;
use App\Http\Controllers\Api\V1\LanguageController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\PromocodeController;
use App\Http\Controllers\Api\V1\SpellingController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Api\V1\TariffController;
use App\Http\Controllers\Api\V1\TopicController;
use App\Http\Controllers\Api\V1\TwoFactorAuthorizationController;
use App\Http\Controllers\Api\V1\UploadController;
use App\Http\Controllers\Api\V1\UserTestResultController;
use App\Http\Controllers\Api\V1\TimezoneController;
use App\Http\Controllers\Api\V1\VoiceController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(callback: function () {
    Route::middleware(['setApiLocale'])->group(callback: function () {
        Route::post('registration', [RegistrationController::class, 'registration'])->name('registration');
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::prefix('auth')->group(function () {
            Route::get('{provider}/redirect', [AuthController::class, 'redirect'])->name('redirect');
            Route::get('{provider}/callback', [AuthController::class, 'handleCallback'])->name('handleCallback');
        });
        Route::prefix('password')->group(function () {
            Route::post('sendResetLink', [ForgotPasswordController::class, 'sendResetLink'])->name('sendResetLink');
            Route::post('reset', [ForgotPasswordController::class, 'resetPassword'])->name('resetPassword');
        });
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::prefix('twoFactorVerification')->group(function () {
            Route::post('', [TwoFactorAuthorizationController::class, 'enableDisableTwoFactorAuthorization'])->name('enableDisableTwoFactorAuthorization')->middleware('auth:sanctum');
            Route::post('sendEmailWithCode', [TwoFactorAuthorizationController::class, 'sendEmailWithCode'])->name('sendEmailWithCode');
            Route::post('confirmEmailCode', [TwoFactorAuthorizationController::class, 'confirmCode'])->name('confirmCode');
            Route::post('verifyCodeGoogle2fa', [TwoFactorAuthorizationController::class, 'verifyCodeGoogle2fa'])->name('verifyCodeGoogle2fa');
            Route::post('useRecoveryCode', [TwoFactorAuthorizationController::class, 'useRecoveryCode'])->name('useRecoveryCode');
            Route::post('refreshRecoveryCodes', [TwoFactorAuthorizationController::class, 'refreshRecoveryCodes'])->name('refreshRecoveryCodes')->middleware('auth:sanctum');
        });
        Route::middleware('auth:sanctum')->group(callback: function () {
            Route::get('test', [TestController::class, 'test'])->name('test');
            Route::post('updatePassword', [ForgotPasswordController::class, 'updatePassword'])->name('updatePassword');
            Route::post('sendVerificationCodeEmail', [EmailVerificationController::class, 'sendVerificationCodeEmail'])->name('sendVerificationCodeEmail');
            Route::post('verificationEmailAddress', [EmailVerificationController::class, 'verificationEmailAddress'])->name('verificationEmailAddress');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('setInviter',[InviteController::class, 'setInviter'])->name('setInviter');
            Route::middleware('verifiedEmail')->group(callback: function () {
                Route::get('timezones', [TimezoneController::class, 'getTimezones'])->name('getTimezones');
                Route::get('columns/{nameTable}', [ColumnsController::class, 'getColumns'])->name('getColumns');
                Route::get('filtersData/{nameTable}', [FilterDataController::class, 'getFilterData'])->name('getFilterData');
                Route::prefix('profile')->group(function () {
                    Route::get('/', [ProfileController::class, 'getProfileAuthUser'])->name('getProfileAuthUser');
                    Route::get('/{id}', [ProfileController::class, 'getProfile'])->where('id', '[0-9]+')->name('getProfile');
                });
                Route::prefix('dailyRewards')->group(function () {
                    Route::get('', [DailyRewardController::class, 'getDailyRewardsForAuthUser'])->name('getDailyRewardsForAuthUser');
                    Route::post('', [DailyRewardController::class, 'takeDailyReward'])->name('takeDailyReward');
                });
                Route::prefix('tests')->group(function () {
                    Route::post('/start', [UserTestResultController::class, 'start'])->name('startTest');
                    Route::get('/questionsForTest/{attemptId}', [UserTestResultController::class, 'questionsForTest'])->name('questionsForTest');
                    Route::post('/end', [UserTestResultController::class, 'end'])->name('endTest');
                });
                Route::prefix('decks')->group(function () {
                    Route::get('/', [DeckController::class, 'getDecks'])->name('getDecks');
                    Route::get('/{id}', [DeckController::class, 'getDeck'])->where('id', '[0-9]+')->name('getDeck');
                    Route::post('/', [DeckController::class, 'createDeck'])->name('createDeck');
                    Route::post('/{id}/topics',[DeckController::class, 'addTopicsToDeck'] )->where('id', '[0-9]+')->name('addTopicsToDeck');
                    Route::delete('/{id}', [DeckController::class, 'deleteDeck'])->where('id', '[0-9]+')->name('deleteDeck');
                });
                Route::prefix('topics')->group(function () {
                    Route::get('/', [TopicController::class, 'getTopics'])->name('getTopics');
                    Route::post('/', [TopicController::class, 'createTopic'])->name('createTopic')->middleware('isAdmin');
                    Route::put('/', [TopicController::class, 'updateTopic'])->name('updateTopic')->middleware('isAdmin');
                    Route::delete('/{id}', [TopicController::class, 'deleteTopic'])->where('id', '[0-9]+')->name('deleteTopic')->middleware('isAdmin');
                });
                Route::prefix('historyAttempts')->group(function () {
                    Route::get('/', [HistoryAttemptsTestController::class, 'getAttemptsTests'])->name('getAttemptsTests');
                });
                Route::prefix('answers')->group(function () {
                    Route::get('/{attemptId}', [AnswerController::class, 'getAnswersInAttempt'])
                        ->where('id', '[0-9]+')->name('getAnswersInAttempt');
                });
                Route::prefix('historyPurchases')->group(function () {
                    Route::get('/', [HistoryPurchaseController::class, 'getHistoryPurchasesOfAuthUser'])->name('getHistoryPurchasesOfAuthUser');
                });
                Route::prefix('tariffs')->group(function () {
                    Route::get('/', [TariffController::class, 'getTariffs'])->name('getTariffs');
                    Route::post('/', [TariffController::class, 'addTariff'])->name('addTariff')->middleware('isAdmin');
                    Route::patch('/{id}', [TariffController::class, 'changeTariffStatus'])->where('id', '[0-9]+')
                        ->name('changeTariffStatus')->middleware('isAdmin');
                });
                Route::prefix('languages')->group(function () {
                    Route::get('/', [LanguageController::class, 'getLanguages'])->name('getLanguages');
                    Route::post('/', [LanguageController::class, 'addLanguage'])->name('addLanguage')->middleware('isAdmin');
                });
                Route::prefix('stats')->group(function () {
                    Route::get('/countUsersByMonths', [StatsController::class, 'getCountUsersByMonths'])->name('getCountUsersByMonths')->middleware('isAdmin');
                    Route::get('/countDecksByTopic', [StatsController::class, 'getTopicsWithCountDecksAndPercentage'])->name('getTopicsWithCountDecksAndPercentage');
                });
                Route::prefix('promocodes')->group(function () {
                    Route::post('/', [PromocodeController::class, 'createPromocodes'])->name('createPromocodes')->middleware('isAdmin');
                    Route::post('/activate', [PromocodeController::class, 'activatePromocode'])->name('activatePromocode');
                    Route::get('/download/{type}/{tariff_id?}', [PromocodeController::class, 'downloadPromocodes'])
                        ->whereIn('type', ['table', 'card'])->whereNumber('tariff_id')->name('downloadPromocodes');
                });
                Route::prefix('cards')->group(function () {
                    Route::post('/', [CardController::class, 'createCardForDeck'])->name('createCardForDeck');
                });
                Route::prefix('voices')->group(function () {
                    Route::get('/', [VoiceController::class, 'getVoices'])->name('getVoices');
                    Route::post('/', [VoiceController::class, 'createVoice'])->name('createVoice')->middleware('isAdmin');
                    Route::patch('/', [VoiceController::class, 'updateStatusOfVoices'])->name('updateStatusOfVoices')->middleware('isAdmin');
                });
                Route::post('/upload', [UploadController::class, 'uploadFile'])->name('uploadFile');
                Route::post('checkSpelling', [SpellingController::class, 'checkSpelling'])->name('checkSpelling');
            });
        });
    });
});
