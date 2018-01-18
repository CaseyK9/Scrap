<?php

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

use App\FileResolver;
use App\Upload;
use Carbon\Carbon;

Artisan::command('make:user {name} {username} {password}', function ($name, $username, $password) {
    $new_user = \App\User::create([
        "name" => $name,
        "username" => $username,
        "password" => bcrypt($password)
    ]);

    echo sprintf("Created: %s\n", $new_user);
})->describe('Create a user account');

Artisan::command('clean:files', function () {
    // Delete files older than the defined number of days
    $num_of_days = config('app.days_to_store', 30);
    $expiration_date = Carbon::now()->subDays($num_of_days);
    Upload::where('created_at', '>=', $expiration_date->toDateTimeString())->delete();

    //Iterate over each resolver and find where 0 uploads are.
    $resolvers = FileResolver::all();
    foreach ($resolvers as $resolver) if (Upload::where('resolver_id', $resolver->id)->get()->count() == 0) $resolver->delete();
})->describe('Deletes old files that are no longer being used.');

Artisan::command('test:upload {alias}', function ($alias) {
    $upload = Upload::getCached($alias);
    $resolver = FileResolver::getCachedFromUpload($upload);
    dd($upload, $resolver);
})->describe('Test upload data');
