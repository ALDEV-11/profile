<?php

namespace App\Http\Controllers;

use App\Models\Education;
use App\Models\Gallery;
use App\Models\Hobby;
use App\Models\Profile;
use App\Models\SocialMedia;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public responsive homepage.
     *
     * Fetches profile details, ordered social media links, hobbies, theme settings,
     * education records, and gallery items from the database.
     */
    public function index(): View
    {
        $profile = Profile::first();
        $socialMedias = SocialMedia::ordered()->get();
        $hobbies = Hobby::ordered()->get();
        $educations = Education::ordered()->get();
        $galleries = Gallery::ordered()->get();
        $featuredGalleries = $galleries->take(6);

        return view('welcome', compact(
            'profile',
            'socialMedias',
            'hobbies',
            'educations',
            'galleries',
            'featuredGalleries'
        ));
    }
}
