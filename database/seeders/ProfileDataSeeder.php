<?php

namespace Database\Seeders;

use App\Models\Education;
use App\Models\Gallery;
use App\Models\Hobby;
use App\Models\Profile;
use App\Models\SocialMedia;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            $this->call(AdminUserSeeder::class);
            $user = User::first();
        }

        // Profile
        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => 'Alex Devlin',
                'tagline' => 'Full-Stack Engineer & Creative Architect',
                'about_me' => 'Passionate developer crafting modern, high-performance web applications with Laravel, Vue, and modern CSS architectures. I focus on creating seamless user experiences and robust backend systems.',
                'email' => 'alex.devlin@example.com',
                'phone' => '+1 (555) 234-5678',
                'address' => 'San Francisco, CA',
                'profile_image' => 'images/profile-avatar.png',
            ]
        );

        // Social Media Links
        $socials = [
            ['platform' => 'GitHub', 'url' => 'https://github.com', 'icon' => 'bx bxl-github', 'order' => 1],
            ['platform' => 'LinkedIn', 'url' => 'https://linkedin.com', 'icon' => 'bx bxl-linkedin', 'order' => 2],
            ['platform' => 'Twitter', 'url' => 'https://twitter.com', 'icon' => 'bx bxl-twitter', 'order' => 3],
            ['platform' => 'Instagram', 'url' => 'https://instagram.com', 'icon' => 'bx bxl-instagram', 'order' => 4],
            ['platform' => 'Dribbble', 'url' => 'https://dribbble.com', 'icon' => 'bx bxl-dribbble', 'order' => 5],
        ];

        foreach ($socials as $social) {
            SocialMedia::updateOrCreate(
                ['user_id' => $user->id, 'platform' => $social['platform']],
                [
                    'url' => $social['url'],
                    'icon' => $social['icon'],
                    'order' => $social['order'],
                ]
            );
        }

        // Hobbies & Interests
        $hobbies = [
            ['name' => 'Coding & Open Source', 'percentage' => 95, 'icon' => 'bx bx-code-alt', 'order' => 1],
            ['name' => 'Gaming & Esport', 'percentage' => 88, 'icon' => 'bx bx-game', 'order' => 2],
            ['name' => 'Photography & Drone', 'percentage' => 82, 'icon' => 'bx bx-camera', 'order' => 3],
            ['name' => 'Music & Guitar', 'percentage' => 75, 'icon' => 'bx bx-music', 'order' => 4],
        ];

        foreach ($hobbies as $hobby) {
            Hobby::updateOrCreate(
                ['user_id' => $user->id, 'name' => $hobby['name']],
                $hobby
            );
        }

        // Education Records
        $educations = [
            [
                'institution' => 'Stanford University',
                'degree' => 'Master of Science (M.S.)',
                'field_of_study' => 'Computer Science & Software Engineering',
                'start_date' => '2020-09-01',
                'end_date' => '2022-06-15',
                'is_currently_studying' => false,
                'order' => 1,
            ],
            [
                'institution' => 'University of California, Berkeley',
                'degree' => 'Bachelor of Science (B.S.)',
                'field_of_study' => 'Computer Science',
                'start_date' => '2016-09-01',
                'end_date' => '2020-05-30',
                'is_currently_studying' => false,
                'order' => 2,
            ],
        ];

        foreach ($educations as $edu) {
            Education::updateOrCreate(
                ['user_id' => $user->id, 'institution' => $edu['institution']],
                $edu
            );
        }

        // Gallery Showcase Items
        $galleries = [
            [
                'caption' => 'Enterprise Cloud Dashboard Architecture',
                'category' => 'project',
                'image_path' => 'galleries/cloud-dashboard-large.jpg',
                'order' => 1,
            ],
            [
                'caption' => 'Real-time Analytics Engine',
                'category' => 'project',
                'image_path' => 'galleries/analytics-engine-large.jpg',
                'order' => 2,
            ],
            [
                'caption' => 'Tech Keynote & Developer Conference',
                'category' => 'event',
                'image_path' => 'galleries/tech-keynote-large.jpg',
                'order' => 3,
            ],
            [
                'caption' => 'Hands-on DevCon Workshop',
                'category' => 'event',
                'image_path' => 'galleries/devcon-workshop-large.jpg',
                'order' => 4,
            ],
            [
                'caption' => 'City Sunset from the Rooftop',
                'category' => 'random_photo',
                'image_path' => 'galleries/city-sunset-large.jpg',
                'order' => 5,
            ],
            [
                'caption' => 'Mountain Trail on a Clear Morning',
                'category' => 'random_photo',
                'image_path' => 'galleries/mountain-trail-large.jpg',
                'order' => 6,
            ],
        ];

        foreach ($galleries as $gal) {
            Gallery::updateOrCreate(
                ['user_id' => $user->id, 'caption' => $gal['caption']],
                $gal
            );
        }
    }
}
