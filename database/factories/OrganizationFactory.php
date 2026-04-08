<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OrganizationType;

class OrganizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'legal_name' => $this->faker->company() . ' S.r.l.',
            'vat_number' => $this->faker->numerify('###########'),
            'tax_code' => strtoupper($this->faker->bothify('???????????????')),
            'sdi_code' => strtoupper($this->faker->bothify('???????')),
            'is_split_payment' => $this->faker->boolean(20),
            'is_active' => $this->faker->boolean(90),
            'organization_type_id' => OrganizationType::inRandomOrder()->value('id'),
        ];
    }
}
