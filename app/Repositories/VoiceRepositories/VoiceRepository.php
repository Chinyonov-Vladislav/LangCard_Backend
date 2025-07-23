<?php

namespace App\Repositories\VoiceRepositories;

use App\Http\Filters\FiltersForModels\VoiceFilter;
use App\Models\Voice;
use App\Services\PaginatorService;
use Illuminate\Database\Eloquent\Collection;

class VoiceRepository implements VoiceRepositoryInterface
{
    protected Voice $model;

    public function __construct(Voice $model)
    {
        $this->model = $model;
    }

    public function saveNewVoice(string $voiceId, string $voiceName, string $sex,bool $status, int $languageId): Voice
    {
         $newVoice = new Voice();
         $newVoice->voice_id = $voiceId;
         $newVoice->voice_name = $voiceName;
         $newVoice->sex = $sex;
         $newVoice->is_active = $status;
         $newVoice->language_id = $languageId;
         $newVoice->save();
         return $newVoice;
    }

    public function getVoiceByVoiceId(int $voiceId): ?Voice
    {
        return $this->model->where('voice_id', '=', $voiceId)->first();
    }

    public function getVoicesWithPaginationAndFilters(?PaginatorService $paginator = null, ?VoiceFilter $voiceFilter = null, int $countOnPage = 0, int $numberCurrentPage=0): Collection|array
    {
        $query = $this->model->with(['language'])->where('is_active', '=', true);
        if($voiceFilter !== null) {
            $query->filter($voiceFilter);
        }
        if($countOnPage === 0 && $numberCurrentPage === 0) {
            return $query->get();
        }
        $data = $paginator->paginate($query, $countOnPage, $numberCurrentPage);
        $metadataPagination = $paginator->getMetadataForPagination($data);
        return ['items' => collect($data->items()), "pagination" => $metadataPagination];
    }

    public function updateStatusActive(string $voiceId, bool $status): void
    {
        $this->model->where('voice_id','=',$voiceId)->update(['is_active' => $status]);
    }

    public function isExistVoice(string $voiceId): bool
    {
        return $this->model->where('voice_id','=',$voiceId)->exists();
    }

    public function getVoicesByVoiceId(array $voiceIds): Collection
    {
        return $this->model->with(['language'])->whereIn('voice_id', $voiceIds)->get();
    }
}
