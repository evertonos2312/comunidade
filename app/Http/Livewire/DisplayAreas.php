<?php

namespace App\Http\Livewire;

use App\Services\AreasService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Softonic\GraphQL\ClientBuilder;

class DisplayAreas extends Component
{
    protected $areaService;

    public function mount(AreasService $areasService)
    {
        $this->areaService = $areasService;
    }

    public function render()
    {
        $areas = $this->data();
        return view('livewire.display-areas', ['areas' => $areas]);
    }

    private function data()
    {
        return $this->areaService->getAreasFromCommunity();
    }


}
