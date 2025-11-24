<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Trafico;
use App\Models\Revisione;
use App\Models\Anexo;
use App\Models\Historial;

class BorrarTraficoCompleto extends Command
{
    protected $signature = 'traficos:borrar-todo {--force}';
    protected $description = 'Borra TODOS los tráficos, anexos, revisiones, historiales y carpetas físicas.';

    public function handle()
    {
        if (!$this->option('force')) {
            $this->error('Debes pasar la opción --force para confirmar.');
            return;
        }

        $this->info('BORRANDO todo...');

        DB::transaction(function () {

            // 1) Borrar las carpetas físicas
            $this->info('Borrando carpetas...');

            Storage::disk('local')->deleteDirectory('public/Facturas');
            Storage::disk('local')->deleteDirectory('public/Anexos');

            // 2) Borrar relaciones en BD
            $this->info('Borrando datos de tablas relacionadas...');

            Historial::truncate();
            Revisione::truncate();
            Anexo::truncate();

            // Tabla pivot trafico_anexo si existe
            if (Schema::hasTable('anexo_trafico')) {
                DB::table('anexo_trafico')->truncate();
            }

            // 3) Borrar tráficos
            $this->info('Borrando traficos...');
            Trafico::truncate();
        });

        $this->info('¡Listo! Se borró TODO correctamente.');
    }
}
