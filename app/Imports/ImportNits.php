<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\NitsImport;
use App\Models\Sistema\TipoDocumentos;

class ImportNits implements ToCollection, WithHeadingRow, WithProgressBar
{
    use Importable;

    protected $razon_social = null;
    protected $actualizar_valores = null;

    public function __construct(string $razon_social, string $actualizar_valores)
    {
        $this->razon_social = $razon_social;
        $this->actualizar_valores = $actualizar_valores;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $estado = 0;
            $observacionMala = '';

            // Omitir filas completamente vacías (sin documento, tipo y email)
            if (!$row['numero_documento'] && !$row['tipo_documento'] && !$row['email']) {
                continue;
            }

            // ============================================================
            // Si numero_documento viene vacío, se omite según lógica original
            // ============================================================
            if (!$row['numero_documento']) {
                continue;
            }

            // ============================================================
            // CONSTRUCCIÓN DEL EMAIL (solo si no viene email y NO es actualización)
            // ============================================================
            $nuevoMail = $row['email'];
            if (!$row['email'] && !$this->actualizar_valores) {
                $nuevoMail = $row['numero_documento'] . '_' . rand(1, 5000);
                $razon_social = explode(" ", $this->razon_social);
                $nuevoMail .= '@' . $razon_social[0] . '.com';
            }

            // ============================================================
            // VALIDACIONES SEGÚN MODO (ACTUALIZAR vs CREAR)
            // ============================================================
            if ($this->actualizar_valores) {
                // ---------- MODO ACTUALIZACIÓN ----------
                $nit = Nits::where('numero_documento', $row['numero_documento'])->first();
                if (!$nit) {
                    $estado = 1;
                    $observacionMala .= 'El numero de documento: ' . $row['numero_documento'] . ', no fue encontrado!<br>';
                } else {
                    // Validar email duplicado omitiendo el mismo documento
                    if ($row['email']) {
                        $nitsConMismoCorreo = Nits::where('numero_documento', '!=', $row['numero_documento'])
                            ->where('email', $row['email'])
                            ->first();

                        if ($nitsConMismoCorreo) {
                            $estado = 1;
                            $observacionMala .= 'El email: ' . $row['email'] . ', ya esta en uso por el documento: ' . $nitsConMismoCorreo->numero_documento . '!<br>';
                        }
                    }
                }
            } else {
                // ---------- MODO CREACIÓN ----------
                // Validar tipo de documento (requerido y existente)
                if ($row['tipo_documento']) {
                    $existeTipo = TipoDocumentos::where('codigo', $row['tipo_documento'])->count();
                    if (!$existeTipo) {
                        $estado = 1;
                        $observacionMala .= 'El código tipo de documento no existe <br/>';
                    }
                } else {
                    $estado = 1;
                    $observacionMala .= 'El tipo de documento es requerido <br/>';
                }

                // Validar número de documento (requerido)
                if ($row['numero_documento']) {
                    $existeNit = Nits::where('numero_documento', $row['numero_documento'])->count();
                    if ($existeNit) {
                        $estado = 1;
                        $observacionMala .= 'El numero de documento: ' . $row['numero_documento'] . ' debe ser unico <br/>';
                    }
                } else {
                    $estado = 1;
                    $observacionMala .= 'El numero de documento es requerido <br/>';
                }

                // Validar email (si viene, debe ser único)
                if ($nuevoMail) {
                    $existeEmail = Nits::where('email', $nuevoMail)->count();
                    if ($existeEmail) {
                        $estado = 1;
                        $observacionMala .= 'El email: ' . $nuevoMail . ' debe ser unico <br/>';
                    }
                }
                // NOTA: el email no es requerido según getErrores (validación comentada)

                // Validar nombres (razón social o primer nombre) - según getErrores está comentado
                if (!$row['razon_social'] && !$row['primer_nombre']) {
                    $estado = 1;
                    $observacionMala .= 'La razon social o el primer nombre son requeridos <br/>';
                }
            }

            // ============================================================
            // CREAR REGISTRO EN NitsImport
            // ============================================================
            NitsImport::create([
                'tipo_documento'    => $row['tipo_documento'],
                'numero_documento'  => $row['numero_documento'],
                'digito_verificacion' => $row['digito_verificacion'],
                'primer_nombre'     => $row['primer_nombre'],
                'otros_nombres'     => $row['otros_nombres'],
                'primer_apellido'   => $row['primer_apellido'],
                'segundo_apellido'  => $row['segundo_apellido'],
                'razon_social'      => $row['razon_social'],
                'direccion'         => $row['direccion'],
                'email'             => $nuevoMail,
                'telefono_1'        => $row['telefono_1'],
                'plazo'             => $row['plazo'],
                'cupo'              => $row['cupo'],
                'observaciones'     => $row['observaciones'],
                'email_1'           => $row['email_1'],
                'email_2'           => $row['email_2'],
                'estado'            => $this->actualizar_valores == true ? 3 : $estado,
                'observacion'       => $observacionMala,
            ]);
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function customValidationAttributes()
    {
        return [
            '0'  => 'tipo_documento',
            '1'  => 'numero_documento',
            '2'  => 'digito_verificacion',
            '3'  => 'primer_nombre',
            '4'  => 'otros_nombres',
            '5'  => 'primer_apellido',
            '6'  => 'segundo_apellido',
            '7'  => 'razon_social',
            '8'  => 'direccion',
            '9'  => 'email',
            '10' => 'telefono_1',
            '11' => 'plazo',
            '12' => 'cupo',
            '13' => 'observaciones',
            '14' => 'email_1',
            '15' => 'email_2',
        ];
    }
}