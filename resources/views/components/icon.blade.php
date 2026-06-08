@props([
    'group',
    'name',
])

@php
    $iconComponent = config("icons.{$group}.{$name}");
@endphp

{{-- Esempi:
<x-icon group="actions" name="edit" />
<x-icon group="entities" name="organization" class="me-1 text-primary" />
<x-icon group="actions" name="search" class="text-muted" />
--}}

@if($iconComponent)
    <span {{ $attributes->class('icon') }}>
        <x-dynamic-component :component="$iconComponent" />
    </span>
@endif
