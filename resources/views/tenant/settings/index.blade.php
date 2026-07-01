@extends('layouts.app')

@section('title', __('settings.title'))
@section('page-title', __('settings.title'))

@php
    // Settings tabs (label + icon) — defined here in the view, not the controller.
    $tabs = [
        'profile' => ['label' => __('settings.tabs.profile'), 'icon' => 'tabler-building-store'],
        'regional' => ['label' => __('settings.tabs.regional'), 'icon' => 'tabler-world'],
        'operations' => ['label' => __('settings.tabs.operations'), 'icon' => 'tabler-settings'],
        'notifications' => ['label' => __('settings.tabs.notifications'), 'icon' => 'tabler-bell'],
        'invoice' => ['label' => __('settings.tabs.invoice'), 'icon' => 'tabler-receipt'],
        'roles' => ['label' => __('settings.tabs.roles'), 'icon' => 'tabler-shield-lock'],
    ];
@endphp

@section('content')
<div class="row g-4">
    {{-- Settings tabs. --}}
    <div class="col-lg-3 col-md-4">
        <div class="card">
            <div class="list-group list-group-flush">
                @foreach ($tabs as $key => $tab)
                    <a href="{{ route('tenant.settings.index', $key) }}"
                       class="list-group-item list-group-item-action d-flex align-items-center {{ $page === $key ? 'active' : '' }}">
                        <i class="icon-base ti {{ $tab['icon'] }} me-2"></i>
                        <span>{{ $tab['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Active tab. --}}
    <div class="col-lg-9 col-md-8">
        @include('tenant.settings.partials.'.$page)
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('organization/js/settings/settings.js') }}"></script>
@endpush
