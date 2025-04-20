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

            if (!$row['numero_documento'] && !$row['tipo_documento'] && !$row['email']) {
                continue;
            }

            if ($row['numero_documento']) {
                $nuevoMail = $row['email'];
                if (!$row['email'] && !$this->actualizar_valores) {
                    $nuevoMail = $row['numero_documento'].'_'.rand(1, 5000);
                    // if ($row['primer_nombre'] && $row['primer_apellido']) {
                    //     $nuevoMail= $row['primer_nombre'].'.'.$row['primer_apellido'].rand(1, 5000);
                    // } else if ($row['primer_nombre']) {
                    //     $nuevoMail= $row['primer_nombre'].'_'.rand(1, 5000);
                    // } else if ($row['primer_apellido']) {
                    //     $nuevoMail= $row['primer_apellido'].'_'.rand(1, 5000);
                    // } else if ($row['otros_nombres']) {
                    //     $nuevoMail= $row['otros_nombres'].'_'.rand(1, 5000);
                    // } else if ($row['segundo_apellido']) {
                    //     $nuevoMail= $row['segundo_apellido'].'_'.rand(1, 5000);
                    // }
                    $razon_social = explode(" ", $this->razon_social);
                    $nuevoMail.='@'.$razon_social[0].'.com';
                }

                if ($this->actualizar_valores) {
                    $nit = Nits::where('numero_documento', $row['numero_documento'])->first();
                    if (!$nit) {
                        $estado = 1;
                        $observacionMala.= 'El numero de documento: '.$row['numero_documento'].', no fue encontrado!<br>';
                    } else {
                        $nitsConMismoCorreo = Nits::where('numero_documento', '!=', $row['numero_documento'])
                            ->where('email', $row['email'])
                            ->first();

                        if ($row['email'] && $nitsConMismoCorreo) {
                            $estado = 1;
                            $observacionMala.= 'El email: '.$nitsConMismoCorreo->email.', ya esta en uso por el documento: '.$nitsConMismoCorreo->numero_documento.'!<br>';
                        }
                        
                    }
                }
            } else {
                $estado = 1;
                $observacionMala.= 'El numero de documento: '.$row['numero_documento'].', es obligatorio!<br>';
            }

            NitsImport::create([
                'tipo_documento' => $row['tipo_documento'],
                'numero_documento' => $row['numero_documento'],
                'digito_verificacion' => $row['digito_verificacion'],
                'primer_nombre' => $row['primer_nombre'],
                'otros_nombres' => $row['otros_nombres'],
                'primer_apellido' => $row['primer_apellido'],
                'segundo_apellido' => $row['segundo_apellido'],
                'razon_social' => $row['razon_social'],
                'direccion' => $row['direccion'],
                'email' => $nuevoMail,
                'telefono_1' => $row['telefono_1'],
                'plazo' => $row['plazo'],
                'cupo' => $row['cupo'],
                'observaciones' => $row['observaciones'],
                'email_1' => $row['email_1'],
                'email_2' => $row['email_2'],
                'estado' => $this->actualizar_valores ? 1 : 0,
                'observacion' => $observacionMala,
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
            '0' => 'tipo_documento',
            '1' => 'numero_documento',
            '2' => 'digito_verificacion',
            '3' => 'primer_nombre',
            '4' => 'otros_nombres',
            '5' => 'primer_apellido',
            '6' => 'segundo_apellido',
            '7' => 'razon_social',
            '8' => 'direccion',
            '9' => 'email',
            '10' => 'telefono_1',
            '11' => 'plazo',
            '12' => 'cupo',
            '13' => 'observaciones',
            '14' => 'email_1',
            '15' => 'email_2',
        ];
    }

}
