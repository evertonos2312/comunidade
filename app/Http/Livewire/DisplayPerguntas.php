<?php

namespace App\Http\Livewire;

use App\Models\Perguntas;
use App\Models\User;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as IlluminateViewView;
use Livewire\Component;

class DisplayPerguntas extends Component
{

    public $perPage = 10;
    public int $quantity = 20;

    public $nextCursor;

    public $hasMorePages;



    public function render()
    {

        $perguntas = $this->data();
        return view('livewire.display-perguntas', ['perguntas' => $perguntas->toArray()]);
    }

    private function data()
    {
        return Perguntas::take($this->quantity)->get();
    }


    public function load()
    {
        $this->quantity += 10;
    }


}
