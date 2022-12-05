<?php

namespace App\Http\Livewire;

use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as IlluminateViewView;
use Livewire\Component;

class DisplayPerguntas extends Component
{

    public $perPage = 10;
    public int $quantity = 20;
    public $area = 1;
    public $migrado_em = null;
    public $selectedItem;

    protected $listeners = ['area' => 'updateAreaId'];

    public function updateAreaId($area)
    {
        $this->area = $area;
    }


    public function render()
    {
        $perguntas = $this->data();
        return view('livewire.display-perguntas', [
            'perguntas' => $perguntas->toArray(),
            'area' => $this->area,
            'migrado' => $this->migrado_em
        ]);
    }

    private function data()
    {
        $query = DB::table('perguntas')
                ->whereNotNull('resposta')
                ->whereRaw('resposta <> ""')
                ->whereNot(function ($query) {
                    $query->where('resposta', 'like', "%table%");
                })
                ->when($this->area, function ($query, $area){
                    $query->where('area', $area);
                });
        if($this->migrado_em){
            $query->whereNotNull('migrado_em');
        } else {
            $query->where('migrado_em', NULL);
        }
        $query->take($this->quantity)
            ->orderByDesc('datapergunta');

        return $query->get();

    }

    public function load()
    {
        $this->quantity += 10;
    }

    public function updateMigrado()
    {
        $this->migrado_em = $this->migrado_em ? null : true;
    }

    public function selectedItem($modelId)
    {
        $this->selectedItem = $modelId;
        $this->emit('selectedItem', $this->selectedItem);  //emit to the form component to load the model
    }


}
