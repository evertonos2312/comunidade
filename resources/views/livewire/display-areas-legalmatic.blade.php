<div class="m-2 p-2 bg-gray-100 rounded d-block">
    <div class="text-center">
        <h2>Filtro banco de dados Legalmatic</h2>
    </div>
    <div class="btn-group d-flex" role="group" aria-label="Basic radio toggle button group">
        @forelse ($areas as $area)
            <input wire:click="updateArea({{$area->id}})" type="radio" class="btn-check" name="btnradio" id="{{ $area->id}}" autocomplete="off" @if($checkedArea == $area->id)checked @endif>
            <label class="btn btn-outline-primary" for="{{ $area->id}}">{{ $area->titulo}}</label>
        @empty
            <p>empty</p>
        @endforelse
    </div>
</div>
