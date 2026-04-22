<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Route;

class CrmPage
{
    public static function resolve(): array
    {
        $route = Route::current();
        $routeName = $route?->getName();

        $pages = config('crm_pages', []);
        $config = $routeName ? ($pages[$routeName] ?? []) : [];

        $params = $route?->parameters() ?? [];

        return [
            'title' => static::resolveValue($config['title'] ?? null, $params),
            'breadcrumbs' => static::resolveBreadcrumbs($config['breadcrumbs'] ?? [], $params),
        ];
    }

    protected static function resolveBreadcrumbs(array $items, array $params): array
    {
        return collect($items)
            ->map(function (array $item) use ($params) {
                $label = static::resolveValue($item['label'] ?? '', $params);
                $routeNameOrUrl = $item['route'] ?? null;

                $url = null;

                if ($routeNameOrUrl instanceof Closure) {
                    $url = $routeNameOrUrl($params);
                } elseif (is_string($routeNameOrUrl) && Route::has($routeNameOrUrl)) {
                    $url = route($routeNameOrUrl);
                } elseif (is_string($routeNameOrUrl) && str_starts_with($routeNameOrUrl, '/')) {
                    $url = $routeNameOrUrl;
                }

                return [
                    'label' => $label,
                    'url' => $url,
                    'icon' => $item['icon'] ?? null,
                ];
            })
            ->filter(fn (array $item) => filled($item['label']))
            ->values()
            ->all();
    }

    protected static function resolveValue(mixed $value, array $params): mixed
    {
        if ($value instanceof Closure) {
            return $value($params);
        }

        return $value;
    }
}