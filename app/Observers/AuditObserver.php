<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Support\AuditContext;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_RESTORED = 'restored';

    public function created(Model $model): void
    {
        $this->write($model, self::EVENT_CREATED, null, $this->attributesOf($model));
    }

    public function updated(Model $model): void
    {
        $new = $this->filter($model, $model->getChanges());

        if ($new === []) {
            return;
        }

        $old = [];

        foreach (array_keys($new) as $attribute) {
            $old[$attribute] = $this->value($model->getOriginal($attribute));
        }

        $this->write($model, self::EVENT_UPDATED, $old, $new);
    }

    public function deleted(Model $model): void
    {
        $this->write($model, self::EVENT_DELETED, $this->attributesOf($model), null);
    }

    public function restored(Model $model): void
    {
        $this->write($model, self::EVENT_RESTORED, null, $this->attributesOf($model));
    }

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    protected function write(Model $model, string $event, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => AuditContext::actorId(),
            'event_type' => $event,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'old_values_json' => $old,
            'new_values_json' => $new,
            'context_json' => AuditContext::current(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function attributesOf(Model $model): array
    {
        return $this->filter($model, $model->getAttributes());
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function filter(Model $model, array $attributes): array
    {
        $excluded = method_exists($model, 'auditExcludedAttributes')
            ? $model->auditExcludedAttributes()
            : [];

        $filtered = [];

        foreach ($attributes as $attribute => $value) {
            if (in_array($attribute, $excluded, true)) {
                continue;
            }

            $filtered[$attribute] = $this->value($value);
        }

        return $filtered;
    }

    /**
     * Normalizza il valore in qualcosa che sopravviva al giro in JSON.
     */
    protected function value(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if (is_object($value)) {
            return (string) $value;
        }

        return $value;
    }
}
