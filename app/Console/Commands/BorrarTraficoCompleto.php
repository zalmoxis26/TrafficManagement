<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BorrarTraficoCompleto extends Command
{
    protected $signature = 'traficos:borrar-todo {--force : Confirmar acción destructiva}';
    protected $description = 'Borra todos los traficos, sus relaciones, pedimentos, jobs y carpetas físicas';

    public function handle()
    {
        if (!$this->option('force')) {
            $this->error('Debes usar --force para ejecutar este comando. Ej: php artisan traficos:borrar-todo --force');
            return 1;
        }

        $this->warn('*** ATENCIÓN: se borrará TODO el módulo de Tráfico (BD + carpetas) ***');

        DB::beginTransaction();

        try {
            // 1) IDs de anexos relacionados a tráficos (para borrar solo esos)
            $anexoIds = collect();
            if (Schema::hasTable('trafico_anexo')) {
                $anexoIds = DB::table('trafico_anexo')->pluck('anexo_id')->unique();
            }

            // 2) Pivots primero
            if (Schema::hasTable('trafico_embarque')) {
                DB::table('trafico_embarque')->delete();
            }

            if (Schema::hasTable('trafico_anexo')) {
                DB::table('trafico_anexo')->delete();
            }

            // 3) Tablas hijas (dependen de traficos)
            if (Schema::hasTable('comments')) {
                DB::table('comments')->delete();
            }

            if (Schema::hasTable('historials')) {
                DB::table('historials')->delete();
            }

            // 4) Tráficos (padre de revisiones y pedimento por FK)
            if (Schema::hasTable('traficos')) {
                DB::table('traficos')->delete();
            }

            // 5) Revisiones (ya sin traficos que apunten)
            if (Schema::hasTable('revisiones')) {
                DB::table('revisiones')->delete();
            }

            // 6) Anexos SOLO los relacionados a traficos
            if ($anexoIds->isNotEmpty() && Schema::hasTable('anexos')) {
                DB::table('anexos')->whereIn('id', $anexoIds)->delete();
            }

            // 7) Embarques (si quieres limpiar todo el módulo)
            if (Schema::hasTable('embarque')) {
                DB::table('embarque')->delete();
            }

            // 8) Pedimentos
            if (Schema::hasTable('pedimento')) {
                DB::table('pedimento')->delete();
            }

            // 9) Limpiar colas de jobs
            if (Schema::hasTable('jobs')) {
                DB::table('jobs')->delete();
            }
            if (Schema::hasTable('failed_jobs')) {
                DB::table('failed_jobs')->delete();
            }
            if (Schema::hasTable('job_batches')) {
                DB::table('job_batches')->delete();
            }

            DB::commit();
            $this->info('✔ Tablas de tráfico y relacionadas limpiadas.');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('❌ ERROR en la BD: ' . $e->getMessage());
            return 1;
        }

        // 10) Carpetas físicas
        $this->warn('Borrando carpetas de Facturas y Anexos en storage/app/public...');
            Storage::disk('local')->deleteDirectory('public/Facturas');
            Storage::disk('local')->deleteDirectory('public/Anexos');
            Storage::disk('local')->deleteDirectory('public/Historial');
            Storage::disk('local')->deleteDirectory('public/Revisiones');
            Storage::disk('local')->deleteDirectory('public/Pedimentos');

        $this->info('✔ Carpetas físicas borradas.');
        $this->info('✅ TODO el módulo de Tráfico quedó limpio.');
        return 0;
    }
}
