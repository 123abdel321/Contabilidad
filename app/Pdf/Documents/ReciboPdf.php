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
use App\Pdf\Mappers\ReciboPdfMapper;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConRecibos;

class ReciboPdf
{
    public static function build(ConRecibos $recibo, Empresa $empresa, string $claveUrl): Document
    {
        $data = ReciboPdfMapper::map($recibo, $empresa, $claveUrl);

        $document = new Document();
        $document->setConfig([
            'orientation' => $recibo->comprobante->tipo_impresion == 1 ? 'landscape' : 'portrait',
            'paper' => 'A4',
            'filename' => 'recibo_' . uniqid() . '.pdf',
            'empresa' => $empresa,
        ]);

        $document
            ->addBlock(new HeaderBlock([
                'titulo' => $data['titulo'],
                'consecutivo' => $data['consecutivo'],
                'fecha' => $data['fecha_manual']
            ]))
            ->addBlock(new CompanyBlock(['empresa' => $data['empresa']]))
            ->addBlock(new ClientBlock(['cliente' => $data['cliente']]))
            ->addBlock(new InfoBlock(['info' => $data['info_data']]))
            ->addBlock(new TableBlock(['tabla' => $data['tabla']]))
            ->addBlock(new SummaryBlock([
                'resumen' => $data['resumen'],
                'monto_letras' => $data['monto_letras'],
                'observacion' => $data['observacion'],
            ]))
            ->addBlock(new NotesBlock([
                'monto_letras' => $data['monto_letras'],
                'observacion' => $data['observacion'],
            ]))
            ->addBlock(new PaymentsBlock(['pagos' => $data['pagos']]))
            ->addBlock(new QrBlock(['qr_erp' => $data['qr_code']]))
            ->addBlock(new FooterBlock(['fecha_pdf' => $data['fecha_pdf']]));

        return $document;
    }
}