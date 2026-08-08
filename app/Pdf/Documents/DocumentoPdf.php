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
use App\Pdf\Blocks\QrBlock;
use App\Pdf\Blocks\FooterBlock;
use App\Pdf\Mappers\DocumentoPdfMapper;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacDocumentos;

class DocumentoPdf
{
    public static function build(FacDocumentos $factura, Empresa $empresa, string $claveUrl): Document
    {
        $data = DocumentoPdfMapper::map($factura, $empresa, $claveUrl);

        $document = new Document();
        $document->setConfig([
            'orientation' => $factura->comprobante->tipo_impresion == 1 ? 'landscape' : 'portrait',
            'paper' => 'A4',
            'filename' => 'documento_' . uniqid() . '.pdf',
            'empresa' => $empresa,
        ]);

        $document
            ->addBlock(new HeaderBlock([
                'titulo' => $data['titulo'],
                'consecutivo' => $data['consecutivo'],
                'fecha' => $data['fecha_manual']
            ]))
            ->addBlock(new CompanyBlock(['empresa' => $data['empresa']]));

        // Solo agregar cliente si existe
        if ($data['cliente']) {
            $document->addBlock(new ClientBlock(['cliente' => $data['cliente']]));
        }

        $document
            ->addBlock(new InfoBlock(['info' => $data['info_data']]))
            ->addBlock(new TableBlock(['tabla' => $data['tabla']]))
            ->addBlock(new SummaryBlock([
                'resumen' => $data['resumen'],
                'monto_letras' => null,
                'observacion' => $data['observacion'],
            ]))
            ->addBlock(new NotesBlock([
                'monto_letras' => null,
                'observacion' => $data['observacion'],
            ]))
            ->addBlock(new QrBlock(['qr_code' => $data['qr_code']]))
            ->addBlock(new FooterBlock(['fecha_pdf' => $data['fecha_pdf']]));

        return $document;
    }
}