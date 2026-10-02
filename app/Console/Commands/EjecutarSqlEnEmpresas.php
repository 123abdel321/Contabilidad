<?php

namespace App\Console\Commands;

use App\Models\Empresas\Empresa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class EjecutarSqlEnEmpresas extends Command
{
    /**
     * Firma del comando.
     * Uso: php artisan empresas:ejecutar-sql "UPDATE tabla SET col='x' WHERE id=1"
     *      php artisan empresas:ejecutar-sql "ALTER TABLE foo ADD col INT" --id=40 --dry-run
     */
    protected $signature = 'empresas:ejecutar-sql
                            {sql : La sentencia SQL a ejecutar en cada base de datos}
                            {--id=* : IDs específicos de empresas (opcional, si no se pasa se ejecutan todas)}
                            {--estado=1 : Filtrar por estado (1=Activo). Usa --estado=* para todos}
                            {--dry-run : Solo mostrar qué se ejecutaría sin ejecutar nada}
                            {--force : No pedir confirmación}';

    protected $description = 'Ejecuta una sentencia SQL en todas las bases de datos (token_db) de las empresas';

    public function handle()
    {
        $sql = $this->argument('sql');
        $ids = $this->option('id');
        $estado = $this->option('estado');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // 1. Obtener empresas
        $query = Empresa::query();

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } elseif ($estado !== '*') {
            $query->where('estado', (int) $estado);
        }

        $empresas = $query->get(['id', 'nombre', 'razon_social', 'token_db', 'estado', 'servidor']);

        if ($empresas->isEmpty()) {
            $this->error('❌ No se encontraron empresas con los filtros indicados.');
            return self::FAILURE;
        }

        $this->info("📋 Empresas a procesar: {$empresas->count()}");
        $this->newLine();

        // 2. Confirmación
        if (!$force && !$dryRun) {
            $this->warn("SQL a ejecutar en TODAS las bases de datos:");
            $this->line("   <fg=yellow>{$sql}</>");
            $this->newLine();
            if (!$this->confirm('¿Deseas continuar?', false)) {
                $this->info('Cancelado.');
                return self::SUCCESS;
            }
        }

        $ok = 0;
        $fail = 0;
        $resultados = [];

        $bar = $this->output->createProgressBar($empresas->count());
        $bar->start();

        foreach ($empresas as $empresa) {
            $dbName = $empresa->token_db;
            $nombreEmpresa = $empresa->razon_social ?: $empresa->nombre ?: "ID {$empresa->id}";

            if (empty($dbName)) {
                $fail++;
                $resultados[] = [
                    'id' => $empresa->id,
                    'empresa' => $nombreEmpresa,
                    'db' => '(vacío)',
                    'status' => '❌ SKIP',
                    'msg' => 'token_db vacío',
                ];
                $bar->advance();
                continue;
            }

            if ($dryRun) {
                $resultados[] = [
                    'id' => $empresa->id,
                    'empresa' => $nombreEmpresa,
                    'db' => $dbName,
                    'status' => '🧪 DRY',
                    'msg' => 'No ejecutado (dry-run)',
                ];
                $bar->advance();
                continue;
            }

            try {
                $this->configurarConexion($dbName);

                // Ejecutar SQL (soporta múltiples sentencias separadas por ;)
                $sentencias = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($sentencias as $sentencia) {
                    if ($sentencia === '') continue;
                    DB::connection('empresa_dinamica')->statement($sentencia);
                }

                $ok++;
                $resultados[] = [
                    'id' => $empresa->id,
                    'empresa' => $nombreEmpresa,
                    'db' => $dbName,
                    'status' => '✅ OK',
                    'msg' => 'Ejecutado',
                ];
            } catch (\Throwable $e) {
                $fail++;
                $resultados[] = [
                    'id' => $empresa->id,
                    'empresa' => $nombreEmpresa,
                    'db' => $dbName,
                    'status' => '❌ ERROR',
                    'msg' => $e->getMessage(),
                ];
            } finally {
                // Limpiar conexión para liberar recursos
                DB::purge('empresa_dinamica');
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // 3. Reporte final
        $this->table(
            ['ID', 'Empresa', 'DB', 'Estado', 'Mensaje'],
            array_map(fn($r) => [
                $r['id'],
                \Illuminate\Support\Str::limit($r['empresa'], 35),
                $r['db'],
                $r['status'],
                \Illuminate\Support\Str::limit($r['msg'], 60),
            ], $resultados)
        );

        $this->newLine();
        $this->info("✅ Éxitos: {$ok}   ❌ Fallos: {$fail}   Total: {$empresas->count()}");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Configura dinámicamente la conexión 'empresa_dinamica' apuntando
     * a la base de datos indicada, usando como plantilla la conexión 'sam'.
     */
    protected function configurarConexion(string $dbName): void
    {
        $base = config('database.connections.sam');

        // Clonar la configuración de 'sam' cambiando solo la base de datos
        $config = array_merge($base, ['database' => $dbName]);

        Config::set('database.connections.empresa_dinamica', $config);

        // Importante: purgar para que Laravel no reutilice conexión previa
        DB::purge('empresa_dinamica');
    }
}