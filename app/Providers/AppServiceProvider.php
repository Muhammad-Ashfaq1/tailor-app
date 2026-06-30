<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Interface\LeadRepositoryInterface;
use App\Repositories\LeadRepository;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Repository bindings: interface => concrete. Controllers type-hint the
     * interface; the container resolves the implementation.
     *
     * @var array<class-string, class-string>
     */
    private array $repositories = [
        LeadRepositoryInterface::class => LeadRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->repositories as $interface => $concrete) {
            $this->app->bind($interface, $concrete);
        }
    }

    public function boot(): void
    {
        // @money($amount) -> per-organization currency formatting.
        Blade::directive('money', static fn (string $expression): string => "<?php echo \App\Support\Currency::format($expression); ?>");
    }
}
