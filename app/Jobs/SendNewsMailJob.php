<?php

namespace App\Jobs;

use App\Enums\JobStatuses;
use App\Mail\NewsPublishedMail;
use App\Repositories\NewsRepositories\NewsRepositoryInterface;
use App\Repositories\UserRepositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Mail;

class SendNewsMailJob extends BaseJob
{
    public int $newsId;
    /**
     * Create a new job instance.
     */
    public function __construct(?string $jobId, int $newsId)
    {
        parent::__construct($jobId);
        $this->newsId = $newsId;
    }

    /**
     * Execute the job.
     */
    public function handle(UserRepositoryInterface $userRepository, NewsRepositoryInterface $newsRepository): void
    {
        $this->updateJobStatus(JobStatuses::processing->value);
        $emailsForSending = [];
        $newsForMailing = $newsRepository->getNewsById($this->newsId);
        if($newsForMailing === null){
            $this->updateJobStatus(JobStatuses::failed->value, ['message'=>"Не найдена новость c id = $this->newsId для рассылки"]);
            return;
        }
        $usersForMailing = $userRepository->getAllUsersWithConfirmedEmailForMailingNews();
        foreach ($usersForMailing as $user) {
            $languageCodeForMessage = $user->language?->code ?? "ru";
            Mail::to($user->email)->send(new NewsPublishedMail($languageCodeForMessage,$newsForMailing));
            $emailsForSending[] = $user->email;
        }
        $this->updateJobStatus(JobStatuses::finished->value, ["message"=>"Сообщения успешно разослано по электронным адресам",
            'emails'=>$emailsForSending, 'totalCountEmails'=>count($emailsForSending)]);
    }
}
