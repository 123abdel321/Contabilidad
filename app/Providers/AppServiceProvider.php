<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Laravel\Horizon\Events\JobFailed;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Relations\Relation;
//NOTIFICACION
use App\Notifications\DiscordHorizonNotification;
//MODELS FAC
use App\Models\Sistema\FacMovimientoInventarios;
use App\Models\Sistema\FacDocumentos;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacCompras;
use App\Models\Sistema\FacVentas;
//MODELS CON
use App\Models\Sistema\ConPagos;
use App\Models\Sistema\ConGastos;
use App\Models\Sistema\ConRecibos;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Relation::morphMap([
            '1' => FacProductos::class,
			'2' => FacDocumentos::class,
			'3' => FacCompras::class,
			'4' => FacVentas::class,
			'5' => FacMovimientoInventarios::class,
            '6' => ConRecibos::class,
            '7' => ConGastos::class,
            '8' => ConPagos::class,
		]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Event::listen(JobFailed::class, function (JobFailed $event) {
            $message = "❌ Job failed: `{$event->job->resolveName()}`\n"
                    . "Exception: {$event->exception->getMessage()}";

            Notification::route('discord', env('LOG_DISCORD_WEBHOOK_URL'))
                ->notify(new DiscordHorizonNotification($message));
        });
    }
}
