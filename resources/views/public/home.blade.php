@extends('layouts.public')

@section('title', 'The platform for modern teams')
@section('meta-description', 'Run projects, tasks and your whole team on one multi-tenant platform built for modern businesses.')

@php
    $features = [
        [
            'title' => 'Multi-tenant by design',
            'body' => 'Each organization gets an isolated, secure workspace. Your data never leaks across tenants.',
        ],
        [
            'title' => 'Projects & tasks',
            'body' => 'Plan work, assign owners and track status from a single, fast dashboard your team will actually use.',
        ],
        [
            'title' => 'Roles that fit',
            'body' => 'Admins, managers and members each get exactly the access they need — nothing more.',
        ],
        [
            'title' => 'Built-in reporting',
            'body' => 'Understand throughput and progress with reports that update as your team does the work.',
        ],
        [
            'title' => 'Fast and lightweight',
            'body' => 'No bloated bundles. Pages load instantly so your team spends time working, not waiting.',
        ],
        [
            'title' => 'Ready when you are',
            'body' => 'Sign up in seconds, or request a guided demo and we will walk you through it.',
        ],
    ];
@endphp

@section('content')
    <section class="landing-hero">
        <div class="landing-container">
            <h1>Run your whole team on one platform</h1>
            <p>Projects, tasks and people — organized in a secure, multi-tenant workspace built for modern businesses.</p>
            <div>
                <a href="{{ route('register') }}" class="landing-btn landing-btn--primary">Get started</a>
                <a href="#" class="landing-btn landing-btn--ghost" data-open-demo>Request a demo</a>
            </div>
        </div>
    </section>

    <section class="landing-section landing-section--alt">
        <div class="landing-container">
            <div class="landing-grid">
                @foreach ($features as $feature)
                    <div class="landing-card">
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="landing-section">
        <div class="landing-container" style="text-align:center;">
            <h2>Ready to get your team organized?</h2>
            <p style="color:var(--landing-muted); max-width:560px; margin:0 auto 1.75rem;">
                Start free in minutes, or let us show you around with a personalized demo.
            </p>
            <div>
                <a href="{{ route('register') }}" class="landing-btn landing-btn--primary">Get started</a>
                <a href="#" class="landing-btn landing-btn--ghost" data-open-demo>Request a demo</a>
            </div>
        </div>
    </section>

    {{-- Request a Demo modal — posts to leads.store via axios (JSON). --}}
    <div class="landing-modal" id="demo-modal" aria-hidden="true">
        <div class="landing-modal__panel" role="dialog" aria-modal="true" aria-labelledby="demo-modal-title">
            <h3 id="demo-modal-title" style="margin-top:0;">Request a demo</h3>

            <div class="landing-alert landing-alert--success" id="demo-success" style="display:none;">
                Thanks — we will be in touch.
            </div>
            <div class="landing-alert landing-alert--error" id="demo-error" style="display:none;"></div>

            <form id="demo-form">
                <input class="landing-field" type="text" name="name" placeholder="Your name" autocomplete="name">
                <input class="landing-field" type="email" name="email" placeholder="Work email" autocomplete="email">
                <input class="landing-field" type="text" name="company" placeholder="Company (optional)" autocomplete="organization">
                <textarea class="landing-field" name="message" rows="3" placeholder="What would you like to see? (optional)"></textarea>

                <div style="display:flex; gap:.75rem; justify-content:flex-end;">
                    <button type="button" class="landing-btn landing-btn--ghost" data-close-demo>Cancel</button>
                    <button type="submit" class="landing-btn landing-btn--primary" id="demo-submit">Send request</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const storeUrl = @json(route('leads.store'));
    const modal = document.getElementById('demo-modal');
    const form = document.getElementById('demo-form');
    const successEl = document.getElementById('demo-success');
    const errorEl = document.getElementById('demo-error');
    const submitBtn = document.getElementById('demo-submit');

    function resetFeedback() {
        successEl.style.display = 'none';
        errorEl.style.display = 'none';
        errorEl.textContent = '';
    }

    function openModal() {
        resetFeedback();
        form.reset();
        form.style.display = '';
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    document.querySelectorAll('[data-open-demo]').forEach(function (el) {
        el.addEventListener('click', function (e) { e.preventDefault(); openModal(); });
    });
    document.querySelectorAll('[data-close-demo]').forEach(function (el) {
        el.addEventListener('click', function (e) { e.preventDefault(); closeModal(); });
    });
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        resetFeedback();
        submitBtn.disabled = true;

        const payload = Object.fromEntries(new FormData(form));

        axios.post(storeUrl, payload, { headers: { 'Accept': 'application/json' } })
            .then(function ({ data }) {
                form.style.display = 'none';
                successEl.textContent = data.message || 'Thanks — we will be in touch.';
                successEl.style.display = '';
                setTimeout(closeModal, 1800);
            })
            .catch(function (err) {
                if (err.response && err.response.status === 422) {
                    const errors = err.response.data.errors || {};
                    const first = Object.keys(errors).map(function (k) { return errors[k][0]; });
                    errorEl.textContent = first.join(' ') || 'Please check the form and try again.';
                } else {
                    errorEl.textContent = 'Something went wrong. Please try again.';
                }
                errorEl.style.display = '';
            })
            .finally(function () { submitBtn.disabled = false; });
    });
})();
</script>
@endpush
