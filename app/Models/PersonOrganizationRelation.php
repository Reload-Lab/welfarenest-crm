<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use App\Models\ContactPoint;

class PersonOrganizationRelation extends Model
{
    use HasFactory;

    protected $table = 'person_organization_relations';

    protected $fillable = [
        'person_id',
        'organization_id',
        'qualification_id',
        'department_id',
        'start_date',
        'end_date',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function qualification()
    {
        return $this->belongsTo(Qualification::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function contactPoints()
    {
        return $this->hasMany(ContactPoint::class, 'owner_id')
            ->where('owner_type', 'person_organization_relation');
    }


}
