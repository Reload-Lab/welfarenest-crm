<?php

namespace App\Support;

use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Rende leggibile il contenuto tecnico delle tabelle di log, usando il
 * vocabolario di config/audit_labels.php.
 *
 * Le chiavi esterne vengono risolte nel loro nome (organization_type_id 3
 * diventa "Fondo pensione") con una cache di processo, cosi una pagina di
 * elenco non ripete la stessa query per ogni riga.
 */
class AuditPresenter
{
    /** @var array<string, array<int, string|null>> */
    protected static array $lookupCache = [];

    // --- Entita --------------------------------------------------------------

    public static function entityLabel(?string $type): string
    {
        if (blank($type)) {
            return '—';
        }

        return config("audit_labels.entities.{$type}.label", Str::headline($type));
    }

    /**
     * @return array{group: string, name: string}|null
     */
    public static function entityIcon(?string $type): ?array
    {
        return $type ? config("audit_labels.entities.{$type}.icon") : null;
    }

    public static function entityUrl(?string $type, int|string|null $id): ?string
    {
        if (blank($type) || blank($id)) {
            return null;
        }

        $route = config("audit_labels.entities.{$type}.route");

        if (! $route || ! app('router')->has($route)) {
            return null;
        }

        return route($route, $id);
    }

    // --- Eventi e origini ----------------------------------------------------

    public static function eventLabel(string $event): string
    {
        return config("audit_labels.events.{$event}.label", Str::headline($event));
    }

    public static function eventVariant(string $event): string
    {
        return config("audit_labels.events.{$event}.variant", 'muted');
    }

    public static function originLabel(?string $origin): ?string
    {
        if (blank($origin)) {
            return null;
        }

        return config("audit_labels.origins.{$origin}", Str::headline($origin));
    }

    public static function activityLabel(string $activityType): string
    {
        return config("audit_labels.activities.{$activityType}", Str::headline($activityType));
    }

    public static function accessEventLabel(string $event): string
    {
        return config("audit_labels.access_events.{$event}.label", Str::headline($event));
    }

    public static function accessEventVariant(string $event): string
    {
        return config("audit_labels.access_events.{$event}.variant", 'muted');
    }

    // --- Campi ---------------------------------------------------------------

    public static function fieldLabel(?string $entityType, string $field): string
    {
        $specific = config("audit_labels.fields.{$entityType}.{$field}.label");

        if ($specific) {
            return $specific;
        }

        return config("audit_labels.fields.*.{$field}.label", Str::headline($field));
    }

    /**
     * @return array<string, mixed>
     */
    protected static function fieldDefinition(?string $entityType, string $field): array
    {
        return config("audit_labels.fields.{$entityType}.{$field}")
            ?? config("audit_labels.fields.*.{$field}")
            ?? [];
    }

    public static function formatValue(?string $entityType, string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $definition = static::fieldDefinition($entityType, $field);

        return match ($definition['type'] ?? null) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Sì' : 'No',
            'date' => static::formatDate($value, 'd/m/Y'),
            'datetime' => static::formatDate($value, 'd/m/Y H:i'),
            'lookup' => static::lookup($definition['model'], $definition['attribute'] ?? 'name', $value),
            default => is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE),
        };
    }

    protected static function formatDate(mixed $value, string $format): string
    {
        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected static function lookup(string $modelClass, string $attribute, mixed $id): string
    {
        if (! is_numeric($id)) {
            return (string) $id;
        }

        $id = (int) $id;
        $cacheKey = $modelClass.'|'.$attribute;

        if (! array_key_exists($id, static::$lookupCache[$cacheKey] ?? [])) {
            $model = $modelClass::find($id);
            static::$lookupCache[$cacheKey][$id] = $model?->{$attribute};
        }

        $name = static::$lookupCache[$cacheKey][$id];

        // Il record puo essere stato cancellato dopo la registrazione del log:
        // in quel caso resta l'id, che e comunque un'informazione.
        return $name ?? "#{$id} (non più presente)";
    }

    // --- Righe di modifica ---------------------------------------------------

    /**
     * Elenco leggibile delle modifiche registrate da una riga di audit.
     *
     * @return array<int, array{field: string, label: string, old: string, new: string}>
     */
    public static function changes(AuditLog $log): array
    {
        $old = $log->old_values_json ?? [];
        $new = $log->new_values_json ?? [];

        $fields = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));

        return array_map(fn (string $field) => [
            'field' => $field,
            'label' => static::fieldLabel($log->auditable_type, $field),
            'old' => array_key_exists($field, $old)
                ? static::formatValue($log->auditable_type, $field, $old[$field])
                : '—',
            'new' => array_key_exists($field, $new)
                ? static::formatValue($log->auditable_type, $field, $new[$field])
                : '—',
        ], $fields);
    }

    /**
     * Chi ha eseguito l'operazione, in forma leggibile: il nome dell'utente
     * CRM quando c'e, altrimenti l'origine dell'operazione automatica.
     */
    public static function actorLabel(AuditLog|ActivityLog|AccessLog $log): string
    {
        if ($log->user) {
            return $log->user->name;
        }

        $context = $log instanceof ActivityLog ? $log->properties_json : $log->context_json;
        $origin = static::originLabel($context['origin'] ?? null);

        return $origin ? $origin : 'Automatico';
    }
}
