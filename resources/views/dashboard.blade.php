@extends('layouts.app')

@section('content')
    <div class="crm-dashboard">
        <div class="crm-dashboard-hero mb-4">
            <div>
                <h1 class="crm-dashboard-hero__title">Benvenuto nel CRM Welfare Nest</h1>
                <p class="crm-dashboard-hero__text">
                    Il tuo centro di controllo per gestire organizzazioni, persone, relazioni e recapiti.
                </p>
            </div>
        </div>

        <div class="row g-4">
            @foreach($stats as $stat)
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="crm-stat-card crm-stat-card--{{ $stat['tone'] }}">
                        <div class="crm-stat-card__icon">
                            <x-icon :group="$stat['icon_group']" :name="$stat['icon_name']" />
                        </div>

                        <div>
                            <div class="crm-stat-card__label">{{ $stat['label'] }}</div>
                            <div
                                class="crm-stat-card__value js-counter"
                                data-target="{{ $stat['value'] }}"
                            >
                                0
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const counters = document.querySelectorAll('.js-counter');

    counters.forEach(counter => {

        const target = parseInt(counter.dataset.target);
        const duration = 1200;

        let start = 0;
        const increment = target / (duration / 16);

        function updateCounter() {

            start += increment;

            if (start >= target) {
                counter.textContent = target;
                return;
            }

            counter.textContent = Math.floor(start);

            requestAnimationFrame(updateCounter);
        }

        updateCounter();
    });

});
</script>
@endpush