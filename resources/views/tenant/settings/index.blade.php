@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@php
    // Settings tabs (label + icon) — defined here in the view, not the controller.
    $tabs = [
        'profile' => ['label' => 'Shop Profile', 'icon' => 'tabler-building-store'],
        'regional' => ['label' => 'Regional & Billing', 'icon' => 'tabler-world'],
        'operations' => ['label' => 'Operations', 'icon' => 'tabler-settings'],
        'notifications' => ['label' => 'Notifications & Loyalty', 'icon' => 'tabler-bell'],
        'invoice' => ['label' => 'Order & Invoice', 'icon' => 'tabler-receipt'],
        'roles' => ['label' => 'Roles & Permissions', 'icon' => 'tabler-shield-lock'],
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
