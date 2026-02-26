<?php

namespace App\Providers;

use App\Contracts\Repositories\PlayerNoteRepositoryInterface;
use App\Repositories\EloquentPlayerNoteRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings for the application.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        PlayerNoteRepositoryInterface::class => EloquentPlayerNoteRepository::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
