<?php

function locale_route($name, $parameters = [], $absolute = true)
{
    // team() returns a team object
    $parameters = array_merge(['locale' => user_locale()], $parameters);
    return route($name, $parameters, $absolute);
}

function user_locale() : string
{
    $request = \request();
    if(($locale = session('locale')) !== null) {
        return $locale;
    }
    foreach($request->getLanguages() as $language) {
        if(in_array($language, config('app.locales'))) {
            return $language;
        }
    }

    return config('app.fallback_locale');
}