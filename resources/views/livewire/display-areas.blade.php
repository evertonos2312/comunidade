<div class="m-2 p-2 bg-gray-100 rounded d-block">
    <label>
        Escolha para qual espaço deseja migrar essa pergunta
        <select name="area">
            @foreach ($areas as $area)
                <option value="{{ $area['id'] }}" @selected(old('area') == $area['id'])>
                    {{ $area['name'] }}
                </option>
            @endforeach
        </select>
        @error('area')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </label>
</div>
