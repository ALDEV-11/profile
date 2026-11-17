<?php

namespace App\Http\Controllers;

use App\Models\Education;
use App\Models\Gallery;
use App\Models\Hobby;
use App\Models\Profile;
use App\Models\SocialMedia;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public responsive homepage.
     *
     * The whole payload is cached because the homepage only changes when
     * admin content changes. The content models clear this key on save and
     * delete, so edits appear immediately without paying for five queries
     * on every visit.
     */
    public function index(): View
    {
        $data = Cache::remember('home.page.data', now()->addMinutes(10), function () {
            $profile = Profile::first();
            $socialMedias = SocialMedia::ordered()->get();
            $hobbies = Hobby::ordered()->get();
            $educations = Education::ordered()->get();
            $galleries = Gallery::ordered()->get();

            return [
                'profile' => $profile,
                'socialMedias' => $socialMedias,
                'hobbies' => $hobbies,
                'educations' => $educations,
                'galleries' => $galleries,
                'featuredGalleries' => $galleries->take(6),
            ];
        });

        return view('welcome', $data);
    }
}
