<?php

namespace App\Http\Livewire;

use App\Models\PerguntasArea;
use Livewire\Component;

class DisplayAreasLegalmatic extends Component
{
    public $checkedArea = 1;
    public function render()
    {
        $areas = $this->data();
        return view('livewire.display-areas-legalmatic', ['areas' => $areas, 'checkedArea' => $this->checkedArea]);
    }

    private function data()
    {
        return PerguntasArea::orderBy('titulo')->get();
    }

    public function updateArea($area)
    {
        $this->checkedArea = $area;
        $this->emit('area', $area);
    }
}
