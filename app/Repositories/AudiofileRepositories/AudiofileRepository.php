<?php

namespace App\Repositories\AudiofileRepositories;

use App\Models\Audiofile;

class AudiofileRepository implements AudiofileRepositoryInterface
{
    protected Audiofile $model;

    public function __construct(Audiofile $model)
    {
        $this->model = $model;
    }

    public function saveNewAudiofile(string $path, string $destination, int $cardId): void
    {
        $newAudiofile = new Audiofile();
        $newAudiofile->path = $path;
        $newAudiofile->destination = $destination;
        $newAudiofile->card_id = $cardId;
        $newAudiofile->save();
    }
}
