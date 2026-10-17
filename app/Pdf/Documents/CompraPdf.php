<?php

namespace App\Pdf\Documents;

use App\Pdf\Core\Document;
use App\Pdf\Blocks\HeaderBlock;
use App\Pdf\Blocks\CompanyBlock;
use App\Pdf\Blocks\ClientBlock;
use App\Pdf\Blocks\InfoBlock;
use App\Pdf\Blocks\TableBlock;
use App\Pdf\Blocks\SummaryBlock;
use App\Pdf\Blocks\NotesBlock;
use App\Pdf\Blocks\PaymentsBlock;
use App\Pdf\Blocks\QrBlock;
use App\Pdf\Blocks\FooterBlock;
use App\Pdf\Mappers\CompraPdfMapper;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacCompras;

class CompraPdf
{
    public static function build(FacCompras $compra, Empresa $empresa, string $claveUrl): Document
    {
        $data = CompraPdfMapper::map($compra, $empresa, $claveUrl);

        $document = new Document();
        $document->setConfig([
            'paper'    => 'A4',
            'filename' => 'compra_' . uniqid() . '.pdf',
            'empresa'  => $empresa,
        ]);

        $document
            ->addBlock(new HeaderBlock([
                'titulo'       => $data['titulo'],
                'consecutivo'  => $data['consecutivo'],
                'fecha'        => $data['fecha_manual'],
            ]))
            ->addBlock(new CompanyBlock(['empresa' => $data['empresa']]));

        // PROVEEDOR (reutiliza ClientBlock)
        if ($data['cliente']) {
            $document->addBlock(new ClientBlock(['cliente' => $data['cliente']]));
        }

        $document
            ->addBlock(new InfoBlock(['info' => $data['info_data']]))
            ->addBlock(new TableBlock(['tabla' => $data['tabla']]))
            ->addBlock(new SummaryBlock([
                'resumen'      => $data['resumen'],
                'monto_letras' => null,
                'observacion'  => null,
            ]))
            ->addBlock(new NotesBlock([
                'monto_letras' => null,
                'observacion'  => $data['observacion'],
            ]))
            ->addBlock(new PaymentsBlock(['pagos' => $data['pagos']]))
            ->addBlock(new QrBlock(['qr_erp' => $data['qr_erp']]))
            ->addBlock(new FooterBlock(['fecha_pdf' => $data['fecha_pdf']]));

        return $document;
    }
}