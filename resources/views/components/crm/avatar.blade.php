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

    $hash = abs(crc32($cleanName ?: 'default'));

    if ($type === 'organization') {
        // Blu / teal / cyan / indigo: più varietà ma sempre istituzionale
        $orgHues = [195, 205, 215, 225, 235, 245, 185, 175];
        $hue = $orgHues[$hash % count($orgHues)];

        $saturation = 50 + ($hash % 22);  // 50–71
        $lightness = 26 + ($hash % 20);   // 26–45

        $background = "hsl({$hue}, {$saturation}%, {$lightness}%)";
        $textColor = '#ffffff';
    } else {
        // Oro / ambra / arancio / lime caldo: più distinguibile
        $personHues = [38, 42, 46, 50, 54, 58, 62, 32, 28, 68];
        $hue = $personHues[$hash % count($personHues)];

        $saturation = 62 + ($hash % 24);  // 62–85
        $lightness = 42 + ($hash % 20);   // 42–61

        $background = "hsl({$hue}, {$saturation}%, {$lightness}%)";

        // Se è troppo scuro uso testo bianco, se è chiaro uso testo scuro
        $textColor = $lightness < 48 ? '#ffffff' : '#1f2937';
    }

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
        class="crm-avatar {{ $sizeClass }} {{ $shapeClass }}"
        style="background: {{ $background }}; color: {{ $textColor }};"
        title="{{ $cleanName }}"
        aria-label="{{ $cleanName }}"
    >
        {{ $initials }}
    </span>
@endif