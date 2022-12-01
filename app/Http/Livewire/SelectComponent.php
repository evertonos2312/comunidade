<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SelectComponent extends Component
{
    public $area = 1;
    protected $listeners = ['area' => 'updateAreaId'];

    public function updateAreaId($area)
    {
        $this->area = $area;
    }

    public function render()
    {
        $availableNumbers = [10, 100, 500, 1000];
        return view('livewire.select-component', [
            'availableNumbers' => $availableNumbers,
            'area' => $this->area,
        ]);
    }
}
