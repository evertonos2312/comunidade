<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as IlluminateViewView;
use Livewire\Component;

class DisplayUsers extends Component
{
    
    public $perPage = 10;
    public int $quantity = 20;

    public $nextCursor;

    public $hasMorePages;



    public function render()
    {

        $users = $this->data();
        // echo '<pre>';
        // print_r($users->toArray());
        // echo '</pre>';
        // die();
        return view('livewire.display-users', ['users' => $users->toArray()]);
    }

    private function data()
    {
        return User::take($this->quantity)->get();
    }


    public function load()
    {
        $this->quantity += 10;
    }


}
