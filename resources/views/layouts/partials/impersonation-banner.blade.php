@if (!empty($isImpersonating) && $isImpersonating)
    <div class="bg-warning text-dark px-4 py-2 d-flex align-items-center justify-content-between">
        <span>
            <i class="icon-base ti tabler-eye"></i>
            You are impersonating <strong>{{ auth()->user()->name }}</strong>
            @isset($impersonator) (as {{ $impersonator->name }}) @endisset
        </span>
        <form method="POST" action="{{ route('impersonate.stop') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-dark">Stop impersonating</button>
        </form>
    </div>
@endif
