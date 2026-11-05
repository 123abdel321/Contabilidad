<?php

namespace App\Helpers\FacturaElectronica;

class AcuseReciboElectronicaSender extends AbstractFESender
{
    /**
     * Endpoint del API de FE para acuse de recibo.
     */
    private $endpoint = '/send-event-data';

    /**
     * CUFE del documento al que se le hace el acuse.
     * Se inyecta desde el controlador.
     */
    protected $cufe;

    /**
     * ID del evento (1 = Acuse de recibo de factura electrónica).
     */
    protected $eventId = '1';

    public function __construct($factura, string $cufe, string $eventId = '1')
    {
        parent::__construct($factura);
        $this->cufe    = $cufe;
        $this->eventId = $eventId;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * El acuse de recibo SOLO lleva el event_id y el cufe.
     * No lleva payment_form, customer, invoice_lines ni totals.
     */
    public function getExtraParams(): array
    {
        return [
            'event_id' => $this->eventId,
            'document_reference' => [
                'cufe' => $this->cufe,
            ],
        ];
    }

    /**
     * No necesita relaciones especiales: solo el CUFE.
     * Devolvemos array vacío para que `loadMissing([])` no haga nada.
     */
    public function getRelationShips(): array
    {
        return [];
    }
}