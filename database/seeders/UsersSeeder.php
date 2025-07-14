<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Ahmed Benali',
                'email' => 'ahmed.benali@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Ahmed',
                    'last_name' => 'Benali',
                    'bio' => 'Passionné de généalogie et de l\'histoire familiale. J\'aime découvrir mes racines et connecter avec ma famille élargie.',
                    'address' => 'Casablanca, Maroc',
                    'birth_date' => '1985-03-15',
                    'gender' => 'male',
                    'phone' => '+212 6 12 34 56 78',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.zahra@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Fatima',
                    'last_name' => 'Zahra',
                    'bio' => 'Mère de famille dévouée, j\'adore organiser des réunions familiales et maintenir les liens entre nos proches.',
                    'address' => 'Rabat, Maroc',
                    'birth_date' => '1988-07-22',
                    'gender' => 'female',
                    'phone' => '+212 6 98 76 54 32',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Mohammed Alami',
                'email' => 'mohammed.alami@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Mohammed',
                    'last_name' => 'Alami',
                    'bio' => 'Ingénieur passionné par la technologie et l\'innovation. Toujours à la recherche de nouvelles façons de connecter les gens.',
                    'address' => 'Marrakech, Maroc',
                    'birth_date' => '1982-11-08',
                    'gender' => 'male',
                    'phone' => '+212 6 45 67 89 01',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Amina Tazi',
                'email' => 'amina.tazi@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Amina',
                    'last_name' => 'Tazi',
                    'bio' => 'Étudiante en médecine, je suis fascinée par l\'histoire de ma famille et les liens qui nous unissent.',
                    'address' => 'Fès, Maroc',
                    'birth_date' => '1995-04-12',
                    'gender' => 'female',
                    'phone' => '+212 6 23 45 67 89',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Youssef Bennani',
                'email' => 'youssef.bennani@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Youssef',
                    'last_name' => 'Bennani',
                    'bio' => 'Entrepreneur dans le domaine du commerce. J\'aime voyager et découvrir de nouvelles cultures.',
                    'address' => 'Tanger, Maroc',
                    'birth_date' => '1980-09-30',
                    'gender' => 'male',
                    'phone' => '+212 6 78 90 12 34',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Leila Mansouri',
                'email' => 'leila.mansouri@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Leila',
                    'last_name' => 'Mansouri',
                    'bio' => 'Architecte créative, j\'aime concevoir des espaces qui rassemblent les familles et créent des moments de bonheur.',
                    'address' => 'Agadir, Maroc',
                    'birth_date' => '1987-12-05',
                    'gender' => 'female',
                    'phone' => '+212 6 56 78 90 12',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Karim El Fassi',
                'email' => 'karim.elfassi@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Karim',
                    'last_name' => 'El Fassi',
                    'bio' => 'Professeur d\'histoire, passionné par la généalogie et la préservation de notre patrimoine familial.',
                    'address' => 'Meknès, Maroc',
                    'birth_date' => '1975-06-18',
                    'gender' => 'male',
                    'phone' => '+212 6 34 56 78 90',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Nadia Berrada',
                'email' => 'nadia.berrada@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Nadia',
                    'last_name' => 'Berrada',
                    'bio' => 'Médecin pédiatre, je consacre ma vie à soigner les enfants et à maintenir les liens familiaux forts.',
                    'address' => 'Oujda, Maroc',
                    'birth_date' => '1983-01-25',
                    'gender' => 'female',
                    'phone' => '+212 6 90 12 34 56',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Hassan Idrissi',
                'email' => 'hassan.idrissi@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Hassan',
                    'last_name' => 'Idrissi',
                    'bio' => 'Chef cuisinier traditionnel, j\'aime préparer des repas qui rassemblent toute la famille autour d\'une table.',
                    'address' => 'Tétouan, Maroc',
                    'birth_date' => '1978-08-14',
                    'gender' => 'male',
                    'phone' => '+212 6 67 89 01 23',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Sara Benjelloun',
                'email' => 'sara.benjelloun@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Sara',
                    'last_name' => 'Benjelloun',
                    'bio' => 'Artiste peintre, j\'exprime ma passion pour la famille à travers mes œuvres et mes créations.',
                    'address' => 'Essaouira, Maroc',
                    'birth_date' => '1990-02-28',
                    'gender' => 'female',
                    'phone' => '+212 6 89 01 23 45',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Omar Cherkaoui',
                'email' => 'omar.cherkaoui@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Omar',
                    'last_name' => 'Cherkaoui',
                    'bio' => 'Avocat spécialisé en droit familial, je défends les valeurs familiales et les liens qui nous unissent.',
                    'address' => 'Kénitra, Maroc',
                    'birth_date' => '1981-05-10',
                    'gender' => 'male',
                    'phone' => '+212 6 12 34 56 78',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Zineb El Khayat',
                'email' => 'zineb.elkhayat@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Zineb',
                    'last_name' => 'El Khayat',
                    'bio' => 'Écrivaine et journaliste, je raconte les histoires de familles et les traditions qui nous enrichissent.',
                    'address' => 'Safi, Maroc',
                    'birth_date' => '1986-10-03',
                    'gender' => 'female',
                    'phone' => '+212 6 45 67 89 01',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Adil Benslimane',
                'email' => 'adil.benslimane@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Adil',
                    'last_name' => 'Benslimane',
                    'bio' => 'Photographe professionnel, je capture les moments précieux de la vie familiale et les souvenirs inoubliables.',
                    'address' => 'El Jadida, Maroc',
                    'birth_date' => '1984-07-17',
                    'gender' => 'male',
                    'phone' => '+212 6 78 90 12 34',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Hanae Mernissi',
                'email' => 'hanae.mernissi@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Hanae',
                    'last_name' => 'Mernissi',
                    'bio' => 'Psychologue spécialisée en thérapie familiale, j\'aide les familles à renforcer leurs liens et à mieux communiquer.',
                    'address' => 'Béni Mellal, Maroc',
                    'birth_date' => '1989-12-20',
                    'gender' => 'female',
                    'phone' => '+212 6 23 45 67 89',
                    'avatar' => null,
                ]
            ],
            [
                'name' => 'Rachid Alaoui',
                'email' => 'rachid.alaoui@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'profile' => [
                    'first_name' => 'Rachid',
                    'last_name' => 'Alaoui',
                    'bio' => 'Imam et guide spirituel, je prône les valeurs familiales et l\'importance de maintenir des liens forts avec nos proches.',
                    'address' => 'Taza, Maroc',
                    'birth_date' => '1976-03-08',
                    'gender' => 'male',
                    'phone' => '+212 6 56 78 90 12',
                    'avatar' => null,
                ]
            ]
        ];

        foreach ($users as $userData) {
            $profileData = $userData['profile'];
            unset($userData['profile']);

            $user = User::create($userData);

            $user->profile()->create($profileData);
        }
    }
}
