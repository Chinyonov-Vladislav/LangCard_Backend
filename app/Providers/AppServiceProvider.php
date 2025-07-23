<?php

namespace App\Providers;

use App\Models\QuestionAnswer;
use App\Repositories\ApiLimitRepositories\ApiLimitRepository;
use App\Repositories\ApiLimitRepositories\ApiLimitRepositoryInterface;
use App\Repositories\AudiofileRepositories\AudiofileRepository;
use App\Repositories\AudiofileRepositories\AudiofileRepositoryInterface;
use App\Repositories\CardRepositories\CardRepository;
use App\Repositories\CardRepositories\CardRepositoryInterface;
use App\Repositories\CostRepositories\CostRepository;
use App\Repositories\CostRepositories\CostRepositoryInterface;
use App\Repositories\CurrencyRepositories\CurrencyRepository;
use App\Repositories\CurrencyRepositories\CurrencyRepositoryInterface;
use App\Repositories\DeckRepositories\DeckRepository;
use App\Repositories\DeckRepositories\DeckRepositoryInterface;
use App\Repositories\DeckTopicRepositories\DeckTopicRepository;
use App\Repositories\DeckTopicRepositories\DeckTopicRepositoryInterface;
use App\Repositories\ExampleRepositories\ExampleRepository;
use App\Repositories\ExampleRepositories\ExampleRepositoryInterface;
use App\Repositories\HistoryPurchasesRepository\HistoryPurchaseRepository;
use App\Repositories\HistoryPurchasesRepository\HistoryPurchaseRepositoryInterface;
use App\Repositories\LanguageRepositories\LanguageRepository;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Repositories\LoginRepositories\LoginRepository;
use App\Repositories\LoginRepositories\LoginRepositoryInterface;
use App\Repositories\ForgotPasswordRepositories\ForgotForgotPasswordRepository;
use App\Repositories\ForgotPasswordRepositories\ForgotPasswordRepositoryInterface;
use App\Repositories\PromocodeRepositories\PromocodeRepository;
use App\Repositories\PromocodeRepositories\PromocodeRepositoryInterface;
use App\Repositories\QuestionAnswerRepository\QuestionAnswerRepository;
use App\Repositories\QuestionAnswerRepository\QuestionAnswerRepositoryInterface;
use App\Repositories\QuestionRepositories\QuestionRepository;
use App\Repositories\QuestionRepositories\QuestionRepositoryInterface;
use App\Repositories\RegistrationRepositories\RegistrationRepository;
use App\Repositories\RegistrationRepositories\RegistrationRepositoryInterface;
use App\Repositories\StatsRepositories\StatsRepository;
use App\Repositories\StatsRepositories\StatsRepositoryInterface;
use App\Repositories\TariffRepositories\TariffRepository;
use App\Repositories\TariffRepositories\TariffRepositoryInterface;
use App\Repositories\TestRepositories\TestRepository;
use App\Repositories\TestRepositories\TestRepositoryInterface;
use App\Repositories\TimezoneRepositories\TimezoneRepository;
use App\Repositories\TimezoneRepositories\TimezoneRepositoryInterface;
use App\Repositories\TopicRepositories\TopicRepository;
use App\Repositories\TopicRepositories\TopicRepositoryInterface;
use App\Repositories\UserRepositories\UserRepository;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use App\Repositories\UserTestAnswerRepositories\UserTestAnswerRepository;
use App\Repositories\UserTestAnswerRepositories\UserTestAnswerRepositoryInterface;
use App\Repositories\UserTestResultRepositories\UserTestResultRepository;
use App\Repositories\UserTestResultRepositories\UserTestResultRepositoryInterface;
use App\Repositories\VisitedDeckRepositories\VisitedDeckRepository;
use App\Repositories\VisitedDeckRepositories\VisitedDeckRepositoryInterface;
use App\Repositories\VoiceRepositories\VoiceRepository;
use App\Repositories\VoiceRepositories\VoiceRepositoryInterface;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Type\EnumCaseType;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $repositories = [
            RegistrationRepositoryInterface::class => RegistrationRepository::class,
            TimezoneRepositoryInterface::class => TimezoneRepository::class,
            LoginRepositoryInterface::class => LoginRepository::class,
            CurrencyRepositoryInterface::class => CurrencyRepository::class,
            ApiLimitRepositoryInterface::class => ApiLimitRepository::class,
            UserRepositoryInterface::class => UserRepository::class,
            ForgotPasswordRepositoryInterface::class => ForgotForgotPasswordRepository::class,
            LanguageRepositoryInterface::class => LanguageRepository::class,
            DeckRepositoryInterface::class => DeckRepository::class,
            TariffRepositoryInterface::class => TariffRepository::class,
            CostRepositoryInterface::class => CostRepository::class,
            HistoryPurchaseRepositoryInterface::class => HistoryPurchaseRepository::class,
            TopicRepositoryInterface::class => TopicRepository::class,
            DeckTopicRepositoryInterface::class => DeckTopicRepository::class,
            VisitedDeckRepositoryInterface::class => VisitedDeckRepository::class,
            CardRepositoryInterface::class => CardRepository::class,
            ExampleRepositoryInterface::class => ExampleRepository::class,
            TestRepositoryInterface::class => TestRepository::class,
            QuestionRepositoryInterface::class => QuestionRepository::class,
            QuestionAnswerRepositoryInterface::class => QuestionAnswerRepository::class,
            UserTestResultRepositoryInterface::class => UserTestResultRepository::class,
            UserTestAnswerRepositoryInterface::class => UserTestAnswerRepository::class,
            StatsRepositoryInterface::class => StatsRepository::class,
            PromocodeRepositoryInterface::class => PromocodeRepository::class,
            VoiceRepositoryInterface::class => VoiceRepository::class,
            AudiofileRepositoryInterface::class => AudiofileRepository::class,
        ];
        foreach ($repositories as $interface => $model) {
            $this->app->bind($interface, $model);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });
        Scramble::configure()
            ->withOperationTransformers(function (Operation $operation, RouteInfo $routeInfo) {
                $routeMiddleware = $routeInfo->route->gatherMiddleware();

                $hasAuthMiddleware = collect($routeMiddleware)->contains(
                    fn($m) => Str::startsWith($m, 'auth:')
                );

                if (!$hasAuthMiddleware) {
                    $operation->security = [];
                }
            });
        Scramble::configure()
            ->withOperationTransformers(function (Operation $operation){
                $acceptHeaderParameter = Parameter::make('Accept', 'header')
                    ->setSchema(
                        Schema::fromType(new StringType)
                    )
                    ->required(true)
                    ->example("application/json");
                $acceptLanguageHeaderParameter = Parameter::make('Accept-Language', 'header')
                    ->description('Предпочитаемый язык для ответа API (поддерживаемые языки: ar, de,en,es,fr,ja,pt,ru,ul,zh). Если язык не установлен, то русский выбирается автоматически')
                    ->setSchema(Schema::fromType(new StringType))
                    ->required(false)
                    ->example("ru");
                $operation->parameters[] = $acceptLanguageHeaderParameter;
            });
    }
}
