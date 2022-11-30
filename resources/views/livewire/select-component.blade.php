<div class="container p-4 mx-auto  bg-gray-200">
    {{-- Nothing in the world is as soft and yielding as water. --}}
    <h2 class="text-center">Migração em lote</h2>
    <span>Serão migradas as perguntas mais antigas da área em exibição.</span>
    <form  class="form-inline" method="post" action="{{route('migrar.pergunta.lote')}}">
        @csrf
        <input type="hidden" name="area" value="{{$area}}">
        <div class="flex items-center grid grid-cols-6 gap-2">
            <select name="number" class="form-select" id="number">
                @foreach ($availableNumbers as $number)
                    <option value="{{ $number }}" @selected(10 == $number)>
                        {{ $number }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Enviar</button>
        </div>
    </form>
</div>
