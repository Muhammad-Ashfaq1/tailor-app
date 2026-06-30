@extends($layout)

@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
    <div class="row g-4">
        @forelse ($reports as $key => $definition)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-label-primary rounded p-2 me-2"><i class="icon-base ti tabler-chart-bar"></i></span>
                            <h5 class="mb-0">{{ $definition->label() }}</h5>
                        </div>
                        <a href="{{ route("{$prefix}.reports.show", $key) }}" class="btn btn-outline-primary mt-auto">
                            <i class="icon-base ti tabler-eye me-1"></i> View report
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">No reports are available.</div>
            </div>
        @endforelse
    </div>
@endsection
