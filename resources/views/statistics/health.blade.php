@extends('layouts.app')

@section('title', __('messages.health.title'))
@section('header', __('messages.health.title'))

@section('content')
<div class="p-8 max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">{{ __('messages.health.analysis') }}</h1>
    <form method="get" class="mb-6 flex items-center space-x-4">
        <label for="store_id" class="font-medium">{{ __('messages.health.select_store') }}：</label>
        <select name="store_id" id="store_id" class="border rounded px-3 py-2" onchange="this.form.submit()">
            @foreach($stores as $store)
                <option value="{{ $store->id }}" @if($currentStore && $store->id == $currentStore->id) selected @endif>{{ $store->name }}</option>
            @endforeach
        </select>
    </form>
    @if($currentStore)
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-2">{{ $currentStore->name }} {{ __('messages.health.health_score') }}：<span class="text-green-600 text-2xl font-bold">{{ $totalScore }}</span></h2>
            <div class="grid grid-cols-2 gap-4 mb-6">
                @foreach($indicators as $name => $score)
                    <div class="bg-gray-50 rounded-lg p-4 flex items-center justify-between">
                        <span class="font-medium">{{ $name }}</span>
                        <span class="text-lg font-bold text-blue-600">{{ $score }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mb-6">
                <canvas id="healthRadar" width="400" height="300"></canvas>
            </div>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <span class="font-semibold text-green-700">{{ __('messages.health.suggestions') }}：</span>
                <span>{{ __('messages.health.suggestion_text') }}</span>
            </div>
        </div>
    @else
        <div class="text-gray-500">{{ __('messages.health.no_store_data') }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('healthRadar');
    if (ctx) {
        var radar = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: {!! json_encode(array_keys($indicators)) !!},
                datasets: [{
                    label: '健康度指标',
                    data: {!! json_encode(array_values($indicators)) !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                }]
            },
            options: {
                responsive: true,
                scale: {
                    ticks: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }
});
</script>
@endpush 