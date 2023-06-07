<?php

namespace IvanSotelo\Inventory\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use IvanSotelo\Inventory\Exceptions\NoUserLoggedInException;

/**
 * Trait UserIdentificationTrait.
 */
trait UserIdentificationTrait
{
    /**
     * Attempt to find the user id of the currently logged in user
     * Supports Cartalyst Sentry/Sentinel based authentication, as well as stock Auth.
     *
     * Thanks to https://github.com/VentureCraft/revisionable/blob/master/src/Venturecraft/Revisionable/RevisionableTrait.php
     *
     * @return int|string|null
     *
     * @throws NoUserLoggedInException
     */
    protected static function getCurrentUserId()
    {
        /*
         * Check if we're allowed to return no user ID to the model, if so we'll return NULL
         */
        if (Config::get('inventory::allow_no_user')) {
            return null;
        }

        if (auth()->check()) {
            return auth()->user()->getAuthIdentifier();
        }

        /*
         * Couldn't get the current logged in users ID, throw exception
         */
        $message = Lang::get('inventory::exceptions.NoUserLoggedInException');

        throw new NoUserLoggedInException($message);
    }
}
