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
     *
     * Only plain attribute arrays go into the cache. Caching Eloquent model
     * instances breaks on unserialize when the container runs an optimized,
     * classmap-authoritative autoloader, so models are rebuilt on read.
     */
    public function index(): View
    {
        $data = Cache::remember('home.page.data', now()->addMinutes(10), function () {
            return [
                'profile' => Profile::query()->first()?->getAttributes(),
                'socialMedias' => $this->attributes(SocialMedia::ordered()->get()),
                'hobbies' => $this->attributes(Hobby::ordered()->get()),
                'educations' => $this->attributes(Education::ordered()->get()),
                'galleries' => $this->attributes(Gallery::ordered()->get()),
            ];
        });

        $galleries = Gallery::hydrate($data['galleries']);

        return view('welcome', [
            'profile' => $data['profile'] !== null
                ? (new Profile)->newFromBuilder($data['profile'])
                : null,
            'socialMedias' => SocialMedia::hydrate($data['socialMedias']),
            'hobbies' => Hobby::hydrate($data['hobbies']),
            'educations' => Education::hydrate($data['educations']),
            'galleries' => $galleries,
            'featuredGalleries' => $galleries->take(6),
        ]);
    }

    /**
     * Strip a collection of models down to the raw database attributes.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>  $models
     * @return array<int, array<string, mixed>>
     */
    private function attributes($models): array
    {
        return $models
            ->map(fn ($model) => $model->getAttributes())
            ->all();
    }
}
