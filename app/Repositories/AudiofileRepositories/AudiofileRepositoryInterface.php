<?php

namespace App\Repositories\AudiofileRepositories;

use App\Models\Audiofile;

interface AudiofileRepositoryInterface
{
    public function saveNewAudiofile(string $path, string $destination, int $cardId);
}
