<?php
namespace App\Models\Concerns;

use App\Support\Audit;

trait Auditable
{
    public static function bootAuditable(): void
    {
        foreach ((new static)->auditEvents() as $event) {
            static::$event(fn ($model) => Audit::fromModel($event, $model));
        }
    }

    /** الأحداث المسجَّلة (يمكن للموديل تغييرها) */
    public function auditEvents(): array
    {
        return ['created', 'updated', 'deleted'];
    }
}
