@props([
    'name' => '',
    'image' => null,
    'type' => 'person', // person | organization
    'size' => 'md',     // xs | sm | md | lg | xl
])

@php
    $cleanName = trim($name ?? '');

    $stopWords = ['fondo', 'pensione', 'sanitario', 'srl', 'spa', 'di', 'del', 'della', 'dei', 'degli', 'delle', 'e', 'ed', 'per'];
    $words = preg_split('/\s+/', $cleanName, -1, PREG_SPLIT_NO_EMPTY) ?: [];

    if ($type === 'organization') {
        $filtered = array_values(array_filter($words, function ($word) use ($stopWords) {
            return !in_array(mb_strtolower($word), $stopWords, true);
        }));

        $sourceWords = count($filtered) > 0 ? $filtered : $words;

        $initials = '';

        if (count($sourceWords) >= 3) {
            $initials =
                mb_substr($sourceWords[0], 0, 1) .
                mb_substr($sourceWords[1], 0, 1) .
                mb_substr($sourceWords[2], 0, 1);
        } elseif (count($sourceWords) === 2) {
            $initials =
                mb_substr($sourceWords[0], 0, 1) .
                mb_substr($sourceWords[1], 0, 1) .
                mb_substr($sourceWords[1], 1, 1);
        } elseif (count($sourceWords) === 1) {
            $initials = mb_substr($sourceWords[0], 0, 3);
        } else {
            $initials = 'ORG';
        }
    } else {
        $initials = '';

        if (count($words) >= 2) {
            $initials =
                mb_substr($words[0], 0, 1) .
                mb_substr($words[1], 0, 1);
        } elseif (count($words) === 1) {
            $initials = mb_substr($words[0], 0, 2);
        } else {
            $initials = 'NA';
        }
    }

    $initials = mb_strtoupper($initials);

    $personPalette = [
        'avatar-person-1',
        'avatar-person-2',
        'avatar-person-3',
        'avatar-person-4',
        'avatar-person-5',
    ];

    $organizationPalette = [
        'avatar-org-1',
        'avatar-org-2',
        'avatar-org-3',
        'avatar-org-4',
        'avatar-org-5',
    ];

    $palette = $type === 'organization' ? $organizationPalette : $personPalette;
    $hash = abs(crc32($cleanName ?: 'default'));
    $colorClass = $palette[$hash % count($palette)];

    $sizeClass = match($size) {
        'xs' => 'crm-avatar-xs',
        'sm' => 'crm-avatar-sm',
        'lg' => 'crm-avatar-lg',
        'xl' => 'crm-avatar-xl',
        default => 'crm-avatar-md',
    };

    $shapeClass = $type === 'organization'
        ? 'crm-avatar-organization'
        : 'crm-avatar-person';
@endphp

@if($image)
    <img
        src="{{ $image }}"
        alt="{{ $cleanName }}"
        class="crm-avatar {{ $sizeClass }} {{ $shapeClass }}"
    >
@else
    <span
        class="crm-avatar {{ $sizeClass }} {{ $shapeClass }} {{ $colorClass }}"
        title="{{ $cleanName }}"
        aria-label="{{ $cleanName }}"
    >
        {{ $initials }}
    </span>
@endif