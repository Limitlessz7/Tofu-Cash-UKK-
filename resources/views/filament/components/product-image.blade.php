@if ($state)
    <img src="{{ asset('storage/' . $state) }}" width="80" class="rounded border">
@else
    <div class="text-gray-500 text-sm">Tidak ada foto</div>
@endif
