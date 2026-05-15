<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\AddressType;
use App\Models\ContactPoint;
use App\Models\ContactType;
use App\Models\ContactUsage;
use App\Models\Department;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Person;
use App\Models\PersonOrganizationRelation;
use App\Models\Qualification;
use App\Models\OrganizationRole;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DemoCrmSeeder extends Seeder
{
    public function run(): void
    {
        $orgTypes = OrganizationType::pluck('id', 'code');
        $contactTypes = ContactType::pluck('id', 'code');
        $contactUsages = ContactUsage::pluck('id', 'code');
        $addressTypes = AddressType::pluck('id', 'code');
        $qualifications = Qualification::pluck('id', 'code');
        $departments = Department::pluck('id', 'code');

        $alpha = Organization::updateOrCreate(
            ['tax_code' => 'DEMOALPHA001'],
            [
                'name' => 'Fondo Sanitario Alpha',
                'legal_name' => 'Fondo Sanitario Alpha',
                'organization_type_id' => $orgTypes['fondo_sanitario'] ?? null,
                'vat_number' => '12345678901',
                'sdi_code' => 'ABC1234',
                'is_split_payment' => true,
                'is_active' => true,
            ]
        );

        $beta = Organization::updateOrCreate(
            ['tax_code' => 'DEMOBETA001'],
            [
                'name' => 'Società di Consulenza Beta con denominazione molto lunga per test interfaccia',
                'legal_name' => 'Beta Consulting Società a Responsabilità Limitata',
                'organization_type_id' => $orgTypes['societa_di_consulenza'] ?? null,
                'vat_number' => '98765432109',
                'sdi_code' => 'XYZ7890',
                'is_split_payment' => false,
                'is_active' => true,
            ]
        );


        $roles = OrganizationRole::pluck('id', 'code');

        DB::table('organization_role_assignments')->updateOrInsert(
            [
                'organization_id' => $alpha->id,
                'organization_role_id' => $roles['client'] ?? null,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('organization_role_assignments')->updateOrInsert(
            [
                'organization_id' => $beta->id,
                'organization_role_id' => $roles['supplier'] ?? null,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
                

        $mario = Person::updateOrCreate(
            ['first_name' => 'Mario', 'last_name' => 'Rossi'],
            []
        );

        $giulia = Person::updateOrCreate(
            ['first_name' => 'Giulia', 'last_name' => 'Bianchi'],
            []
        );

        PersonOrganizationRelation::updateOrCreate(
            [
                'person_id' => $mario->id,
                'organization_id' => $alpha->id,
            ],
            [
                'qualification_id' => $qualifications['direttore'] ?? null,
                'department_id' => $departments['direzione'] ?? null,
                'start_date' => '2023-01-01',
                'end_date' => null,
                'is_active' => true,
            ]
        );

        PersonOrganizationRelation::updateOrCreate(
            [
                'person_id' => $giulia->id,
                'organization_id' => $beta->id,
            ],
            [
                'qualification_id' => $qualifications['consulente'] ?? null,
                'department_id' => $departments['area_commerciale'] ?? null,
                'start_date' => '2022-06-01',
                'end_date' => null,
                'is_active' => true,
            ]
        );

        ContactPoint::updateOrCreate(
            [
                'owner_type' => 'organization',
                'owner_id' => $alpha->id,
                'contact_type_id' => $contactTypes['email'] ?? null,
                'value' => 'info@fondosanitarioalpha.it',
            ],
            [
                'contact_usage_id' => $contactUsages['administrative'] ?? null,
                'label' => 'Email amministrativa',
                'is_primary' => true,
                'is_active' => true,
            ]
        );

        ContactPoint::updateOrCreate(
            [
                'owner_type' => 'organization',
                'owner_id' => $alpha->id,
                'contact_type_id' => $contactTypes['pec'] ?? null,
                'value' => 'fondosanitarioalpha@pec.it',
            ],
            [
                'contact_usage_id' => $contactUsages['administrative'] ?? null,
                'label' => 'PEC',
                'is_primary' => true,
                'is_active' => true,
            ]
        );

        ContactPoint::updateOrCreate(
            [
                'owner_type' => 'person',
                'owner_id' => $mario->id,
                'contact_type_id' => $contactTypes['email'] ?? null,
                'value' => 'mario.rossi.referente.con.email.molto.lunga@fondosanitarioalpha.it',
            ],
            [
                'contact_usage_id' => $contactUsages['work'] ?? null,
                'label' => 'Email lavoro',
                'is_primary' => true,
                'is_active' => true,
            ]
        );

        Address::updateOrCreate(
            [
                'owner_type' => 'organization',
                'owner_id' => $alpha->id,
                'address_type_id' => $addressTypes['legal'] ?? null,
            ],
            [
                'label' => 'Sede legale',
                'street' => 'Via Roma',
                'street_number' => '10',
                'postal_code' => '00100',
                'city' => 'Roma',
                'province' => 'RM',
                'region' => 'Lazio',
                'country' => 'Italia',
                'is_primary' => true,
            ]
        );

        // DATI DEMO MASSIVI
        $demoOrganizations = Organization::factory()
            ->count(20)
            ->create();

        $demoPeople = Person::factory()
            ->count(50)
            ->create();

        $allOrganizations = Organization::all();

        foreach ($demoOrganizations as $organization) {
            $roleId = fake()->randomElement([
                $roles['client'] ?? null,
                $roles['supplier'] ?? null,
                $roles['partner'] ?? null,
            ]);

            if ($roleId) {
                DB::table('organization_role_assignments')->updateOrInsert(
                    [
                        'organization_id' => $organization->id,
                        'organization_role_id' => $roleId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            ContactPoint::updateOrCreate(
                [
                    'owner_type' => 'organization',
                    'owner_id' => $organization->id,
                    'contact_type_id' => $contactTypes['email'] ?? null,
                    'value' => fake()->companyEmail(),
                ],
                [
                    'contact_usage_id' => $contactUsages['administrative'] ?? null,
                    'label' => 'Email aziendale',
                    'is_primary' => true,
                    'is_active' => true,
                ]
            );

            Address::updateOrCreate(
                [
                    'owner_type' => 'organization',
                    'owner_id' => $organization->id,
                    'address_type_id' => $addressTypes['legal'] ?? null,
                ],
                [
                    'label' => 'Sede legale',
                    'street' => fake()->streetName(),
                    'street_number' => fake()->buildingNumber(),
                    'postal_code' => fake()->postcode(),
                    'city' => fake()->city(),
                    'province' => strtoupper(fake()->lexify('??')),
                    'region' => fake()->randomElement(['Lazio', 'Lombardia', 'Veneto', 'Toscana', 'Emilia-Romagna']),
                    'country' => 'Italia',
                    'is_primary' => true,
                ]
            );
        }

        foreach ($demoPeople as $person) {
            $personOrganizations = $allOrganizations->random(rand(1, 3));

            foreach ($personOrganizations as $organization) {
                PersonOrganizationRelation::updateOrCreate(
                    [
                        'person_id' => $person->id,
                        'organization_id' => $organization->id,
                    ],
                    [
                        'qualification_id' => $qualifications->random(),
                        'department_id' => $departments->random(),
                        'start_date' => fake()->dateTimeBetween('-5 years', '-1 month')->format('Y-m-d'),
                        'end_date' => fake()->boolean(15)
                            ? fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d')
                            : null,
                        'is_active' => fake()->boolean(85),
                    ]
                );
            }

            ContactPoint::updateOrCreate(
                [
                    'owner_type' => 'person',
                    'owner_id' => $person->id,
                    'contact_type_id' => $contactTypes['email'] ?? null,
                    'value' => fake()->safeEmail(),
                ],
                [
                    'contact_usage_id' => $contactUsages['work'] ?? null,
                    'label' => 'Email lavoro',
                    'is_primary' => true,
                    'is_active' => true,
                ]
            );

            ContactPoint::updateOrCreate(
                [
                    'owner_type' => 'person',
                    'owner_id' => $person->id,
                    'contact_type_id' => $contactTypes['mobile'] ?? null,
                    'value' => fake()->phoneNumber(),
                ],
                [
                    'contact_usage_id' => $contactUsages['direct'] ?? null,
                    'label' => 'Cellulare',
                    'is_primary' => true,
                    'is_active' => true,
                ]
            );
        }



    }
}


