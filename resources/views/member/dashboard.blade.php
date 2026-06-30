@extends('layouts.member-portal')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="mb-1">Welcome back, {{ $user->name }} 👋</h5>
            <p class="text-muted mb-3">Use the menu to access your reports.</p>
            <a href="{{ route('member.reports.index') }}" class="btn btn-primary">
                <i class="icon-base ti tabler-chart-bar me-1"></i> View reports
            </a>
        </div>
    </div>
@endsection
