<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\NitsImport;
use App\Models\Sistema\TipoDocumentos;
use App\Models\Empresas\Ciudades;

class ImportNits implements ToCollection, WithHeadingRow, WithProgressBar
{
    use Importable;

    protected $razon_social = null;
    protected $actualizar_valores = false;
    protected $dominio_email = 'empresa.com';

    public function __construct(string $razon_social, bool $actualizar_valores = false)
    {
        $this->razon_social       = $razon_social;
        $this->actualizar_valores = $actualizar_valores;

        $partes = explode(' ', trim($razon_social ?: ''));
        $slug   = Str::slug($partes[0] ?? 'empresa');
        $this->dominio_email = ($slug !== '' ? $slug : 'empresa') . '.com';
    }

    public function collection(Collection $rows)
    {
        // ── Precarga para evitar N+1 ──
        $tiposDocumento       = TipoDocumentos::pluck('id', 'codigo')->toArray();
        $documentosExistentes = Nits::pluck('numero_documento')->flip()->toArray();
        $emailsExistentes     = Nits::whereNotNull('email')->pluck('numero_documento', 'email')->toArray();

        $ciudades = Ciudades::select('id', 'nombre', 'nombre_completo')->get();

        $documentosVistos = [];
        $emailsVistos     = [];
        
        foreach ($rows as $row) {

            $errores = '';

            // ⚠️ AHORA las keys son las slugificadas del Excel (headingRow = 2)
            $tipoDocumento      = $this->limpiarEntero($row['cod_tipo_documento'] ?? null);
            $numeroDocumento    = $this->limpiarEntero($row['numero_documento'] ?? null);
            $digitoVerificacion = $this->limpiar($row['dv'] ?? null);
            $primerNombre       = $this->limpiar($row['primer_nombre'] ?? null);
            $otrosNombres       = $this->limpiar($row['otros_nombres'] ?? null);
            $primerApellido     = $this->limpiar($row['primer_apellido'] ?? null);
            $segundoApellido    = $this->limpiar($row['segundo_apellido'] ?? null);
            $razonSocial        = $this->limpiar($row['razon_social'] ?? null);
            $direccion          = $this->limpiar($row['direccion'] ?? null);
            $ciudadTexto        = $this->limpiar($row['ciudad'] ?? null);
            $email              = $this->limpiar($row['email'] ?? null);
            $telefono1          = $this->limpiarEntero($row['telefono'] ?? null);
            $plazo              = $this->limpiarDecimal($row['plazo'] ?? null);
            $cupo               = $this->limpiarDecimal($row['cupo'] ?? null);
            $observaciones      = $this->limpiar($row['observacion'] ?? null);
            $email2             = $this->limpiar($row['email_2'] ?? null);
            $email3             = $this->limpiar($row['email_3'] ?? null);

            // ── Omitir filas completamente vacías ──
            if (!$numeroDocumento && !$tipoDocumento && !$email) {
                continue;
            }

            if (!$numeroDocumento) {
                continue;
            }

            // ── Email auto-generado solo en modo crear ──
            $nuevoMail = $email;
            if (!$email && !$this->actualizar_valores) {
                $nuevoMail = $numeroDocumento . '_' . Str::lower(Str::random(6)) . '@' . $this->dominio_email;
            }

            // ── Resolver CIUDAD (LIKE por nombre_completo o nombre) ──
            $idCiudad = null;
            if ($ciudadTexto) {
                $busqueda = Str::lower(Str::ascii($ciudadTexto));
                $coincidencias = $ciudades->filter(function ($c) use ($busqueda) {
                    return Str::contains(Str::lower(Str::ascii($c->nombre_completo)), $busqueda)
                        || Str::lower(Str::ascii($c->nombre)) === $busqueda;
                });

                if ($coincidencias->count() === 0) {
                    $errores .= 'La ciudad "' . $ciudadTexto . '" no existe en el sistema.<br/>';
                } else if ($coincidencias->count() > 1) {
                    $exacta = $coincidencias->first(function ($c) use ($busqueda) {
                        return Str::lower(Str::ascii($c->nombre_completo)) === $busqueda;
                    });
                    if ($exacta) {
                        $idCiudad = $exacta->id;
                    } else {
                        $errores .= 'La ciudad "' . $ciudadTexto . '" es ambigua (' . $coincidencias->count() . ' coincidencias). Usa formato "COLOMBIA - DEPTO - CIUDAD".<br/>';
                    }
                } else {
                    $idCiudad = $coincidencias->first()->id;
                }
            }

            // ════════════════════════════════════════════════════
            // MODO ACTUALIZAR → estado 2 (OK) / 3 (error)
            // ════════════════════════════════════════════════════
            if ($this->actualizar_valores) {
                $estado = 2;

                if (!isset($documentosExistentes[$numeroDocumento])) {
                    $estado = 3;
                    $errores .= 'El número de documento: ' . $numeroDocumento . ', no fue encontrado para actualizar.<br/>';
                }

                if ($email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $estado = 3;
                        $errores .= 'El email "' . $email . '" no tiene un formato válido.<br/>';
                    } else if (
                        (isset($emailsExistentes[$email]) && $emailsExistentes[$email] !== $numeroDocumento)
                        || (isset($emailsVistos[$email]) && $emailsVistos[$email] !== $numeroDocumento)
                    ) {
                        $dueno = $emailsExistentes[$email] ?? $emailsVistos[$email];
                        $estado = 3;
                        $errores .= 'El email: ' . $email . ', ya está en uso por el documento: ' . $dueno . '.<br/>';
                    }
                }

                if ($errores) $estado = 3;
            }
            // ════════════════════════════════════════════════════
            // MODO CREAR → estado 0 (OK) / 1 (error)
            // ════════════════════════════════════════════════════
            else {
                $estado = 0;

                if (!$tipoDocumento) {
                    $estado = 1;
                    $errores .= 'El tipo de documento es requerido.<br/>';
                } else if (!isset($tiposDocumento[$tipoDocumento])) {
                    $estado = 1;
                    $errores .= 'El código de tipo de documento "' . $tipoDocumento . '" no existe.<br/>';
                }

                if (isset($documentosExistentes[$numeroDocumento])) {
                    $estado = 1;
                    $errores .= 'El número de documento: ' . $numeroDocumento . ' ya existe en el sistema.<br/>';
                } else if (isset($documentosVistos[$numeroDocumento])) {
                    $estado = 1;
                    $errores .= 'El número de documento: ' . $numeroDocumento . ' está duplicado dentro del archivo.<br/>';
                }

                if ($nuevoMail) {
                    if (!filter_var($nuevoMail, FILTER_VALIDATE_EMAIL)) {
                        $estado = 1;
                        $errores .= 'El email "' . $nuevoMail . '" no tiene un formato válido.<br/>';
                    } else if (isset($emailsExistentes[$nuevoMail])) {
                        $estado = 1;
                        $errores .= 'El email: ' . $nuevoMail . ' ya está en uso en el sistema.<br/>';
                    } else if (isset($emailsVistos[$nuevoMail])) {
                        $estado = 1;
                        $errores .= 'El email: ' . $nuevoMail . ' está duplicado dentro del archivo.<br/>';
                    }
                }

                if (!$razonSocial && !$primerNombre) {
                    $estado = 1;
                    $errores .= 'La razón social o el primer nombre son requeridos.<br/>';
                }

                if ($errores) $estado = 1;
            }

            if ($numeroDocumento) $documentosVistos[$numeroDocumento] = true;
            if ($email)          $emailsVistos[$email]                 = $numeroDocumento;

            NitsImport::create([
                'tipo_documento'      => $tipoDocumento,
                'numero_documento'    => $numeroDocumento,
                'digito_verificacion' => $digitoVerificacion,
                'primer_nombre'       => $primerNombre,
                'otros_nombres'       => $otrosNombres,
                'primer_apellido'     => $primerApellido,
                'segundo_apellido'    => $segundoApellido,
                'razon_social'        => $razonSocial,
                'direccion'           => $direccion,
                'ciudad'              => $ciudadTexto,
                'id_ciudad'           => $idCiudad,
                'email'               => $nuevoMail,
                'telefono_1'          => $telefono1,
                'plazo'               => $plazo,
                'cupo'                => $cupo,
                'observaciones'       => $observaciones,
                'email_1'             => $email2,
                'email_2'             => $email3,
                'estado'              => $estado,
                'observacion'         => $errores,
            ]);
        }
    }

    /* ============================================================
       Helpers de limpieza
       ============================================================ */

    /** null → null; string → trim; float "13.0" → "13"; int → string */
    private function limpiar($valor)
    {
        if ($valor === null) return null;
        if (is_string($valor)) {
            $valor = trim($valor);
            return $valor === '' ? null : $valor;
        }
        if (is_float($valor) && floor($valor) == $valor) {
            return (string) (int) $valor;   // 13.0 → "13"
        }
        return $valor;
    }

    /** Devuelve string de dígitos o null. Ideal para documento, teléfono, tipo doc. */
    private function limpiarEntero($valor)
    {
        if ($valor === null || $valor === '') return null;
        if (is_numeric($valor)) {
            return (string) (int) round((float) $valor);
        }
        $valor = trim((string) $valor);
        return $valor === '' ? null : $valor;
    }

    /** Devuelve decimal numérico o null. */
    private function limpiarDecimal($valor)
    {
        if ($valor === null || $valor === '') return null;
        if (is_numeric($valor)) return (float) $valor;

        // Por si viene como string con separadores CO: "1.500,50" → 1500.50
        $valor = trim((string) $valor);
        if ($valor === '') return null;
        $valor = str_replace('.', '', $valor);   // quitar separador de miles
        $valor = str_replace(',', '.', $valor);  // coma → punto decimal
        return is_numeric($valor) ? (float) $valor : null;
    }

    public function headingRow(): int
    {
        return 2;   // 👈 fila 2 = headers reales
    }
}