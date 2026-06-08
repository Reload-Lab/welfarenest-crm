@php
    $resolvedTitle = $crmPage['title'] ?? null;
    $resolvedBreadcrumbs = $crmPage['breadcrumbs'] ?? [];

    $topbarTitle = trim($__env->yieldContent('topbar_title', $resolvedTitle ?: 'Dashboard'));
    $topbarSubtitle = trim($__env->yieldContent('topbar_subtitle'));
@endphp

<header class="crm-topbar">
    <div class="crm-topbar-left">
        <button type="button" class="crm-topbar-menu-btn d-lg-none" id="sidebarToggleMobile" aria-label="Apri menu">
            <x-icon group="navigation" name="menu" />
        </button>

        <div class="crm-topbar-heading">
            <h1 class="crm-topbar-title">{{ $topbarTitle }}</h1>

            @if(! empty($resolvedBreadcrumbs))
                <nav class="crm-breadcrumb" aria-label="Breadcrumb">
                    <ol class="crm-breadcrumb__list">
                        @foreach($resolvedBreadcrumbs as $crumb)
                            <li class="crm-breadcrumb__item">
                                @if(! empty($crumb['url']) && ! $loop->last)
                                    <a href="{{ $crumb['url'] }}" class="crm-breadcrumb__link">
                                        @if(! empty($crumb['icon']))
                                            <x-icon
                                                :group="$crumb['icon']['group']"
                                                :name="$crumb['icon']['name']"
                                                class="icon-sm"
                                            />
                                        @endif

                                        <span>{{ $crumb['label'] }}</span>
                                    </a>
                                @else
                                    <span class="crm-breadcrumb__current">
                                        @if(! empty($crumb['icon']))
                                            <x-icon
                                                :group="$crumb['icon']['group']"
                                                :name="$crumb['icon']['name']"
                                                class="icon-sm"
                                            />
                                        @endif

                                        <span>{{ $crumb['label'] }}</span>
                                    </span>
                                @endif

                                @unless($loop->last)
                                    <span class="crm-breadcrumb__separator" aria-hidden="true">›</span>
                                @endunless
                            </li>
                        @endforeach
                    </ol>
                </nav>
            @elseif($topbarSubtitle !== '')
                <p class="crm-topbar-subtitle">{{ $topbarSubtitle }}</p>
            @endif
        </div>
    </div>

    <div class="crm-topbar-right">
        <div class="dropdown">
            <button
                class="crm-topbar-user dropdown-toggle"
                type="button"
                id="topbarUserMenu"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <span class="crm-topbar-user-avatar">
                    <x-icon group="entities" name="person" />
                </span>

                <span class="crm-topbar-user-text">
                    {{ auth()->user()->name ?? 'Amministratore' }}
                </span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end crm-topbar-dropdown shadow-sm border-0" aria-labelledby="topbarUserMenu">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                        <x-icon group="entities" name="person" class="icon-sm" />
                        <span>Profilo</span>
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                            <x-icon group="actions" name="logout" class="icon-sm" />
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>