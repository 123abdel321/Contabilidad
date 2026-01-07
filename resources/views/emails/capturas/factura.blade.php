<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Factura {{ $factura->documento_referencia_fe }}</title>
    <style type="text/css" rel="stylesheet" media="all">
      /* Base ------------------------------ */
      @import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,600,700&display=swap");
      
      body {
        width: 100% !important;
        height: 100%;
        margin: 0;
        -webkit-text-size-adjust: none;
      }

      a {
        color: #3869D4;
        text-decoration: none;
      }

      a img {
        border: none;
      }

      td {
        word-break: break-word;
      }

      .preheader {
        display: none !important;
        visibility: hidden;
        mso-hide: all;
        font-size: 1px;
        line-height: 1px;
        max-height: 0;
        max-width: 0;
        opacity: 0;
        overflow: hidden;
      }
      
      /* Type ------------------------------ */
      body,
      td,
      th {
        font-family: "Nunito Sans", Helvetica, Arial, sans-serif;
      }

      h1 {
        margin-top: 0;
        color: #2D3748;
        font-size: 20px;
        font-weight: 700;
        text-align: left;
        margin-bottom: 16px;
      }

      h2 {
        margin-top: 0;
        color: #2D3748;
        font-size: 16px;
        font-weight: 600;
        text-align: left;
        margin-bottom: 12px;
      }

      h3 {
        margin-top: 0;
        color: #2D3748;
        font-size: 14px;
        font-weight: 600;
        text-align: left;
        margin-bottom: 8px;
      }

      td,
      th {
        font-size: 14px;
      }

      p,
      ul,
      ol,
      blockquote {
        margin: .4em 0 1em;
        font-size: 14px;
        line-height: 1.5;
        color: #4A5568;
      }

      p.sub {
        font-size: 12px;
        color: #718096;
      }
      
      /* Utilities ------------------------------ */
      .align-right {
        text-align: right;
      }

      .align-left {
        text-align: left;
      }

      .align-center {
        text-align: center;
      }
      
      /* Invoice Container ------------------------------ */
      .invoice-container {
        background: #FFFFFF;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        padding: 20px;
        margin: 20px 0;
      }
      
      .invoice-header {
        border-bottom: 2px solid #4299E1;
        padding-bottom: 12px;
        margin-bottom: 16px;
      }
      
      .invoice-number {
        font-size: 16px;
        font-weight: 700;
        color: #2D3748;
      }
      
      .invoice-date {
        font-size: 13px;
        color: #718096;
      }
      
      /* Invoice Details ------------------------------ */
      .detail-row {
        padding: 8px 0;
        border-bottom: 1px solid #EDF2F7;
      }
      
      .detail-row:last-child {
        border-bottom: none;
      }
      
      .detail-label {
        display: inline-block;
        width: 140px;
        font-weight: 600;
        color: #4A5568;
        font-size: 13px;
      }
      
      .detail-value {
        color: #2D3748;
      }
      
      .total-row {
        background: #F7FAFC;
        padding: 12px;
        border-radius: 6px;
        margin-top: 12px;
        font-weight: 700;
        color: #2D3748;
      }
      
      /* Status Badge ------------------------------ */
      .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-left: 10px;
      }
      
      .status-pending {
        background: #FEF3C7;
        color: #92400E;
      }
      
      .status-paid {
        background: #D1FAE5;
        color: #065F46;
      }
      
      /* Divider ------------------------------ */
      .divider {
        height: 1px;
        background: #E2E8F0;
        margin: 20px 0;
      }
      
      /* Email Structure ------------------------------ */
      body {
        background-color: #F7FAFC;
        color: #4A5568;
      }

      .email-wrapper {
        width: 100%;
        margin: 0;
        padding: 0;
        background-color: #F7FAFC;
      }

      .email-content {
        width: 100%;
        margin: 0;
        padding: 0;
      }
      
      /* Masthead ----------------------- */
      .email-masthead {
        padding: 20px 0;
        text-align: center;
        background: #FFFFFF;
        border-bottom: 1px solid #E2E8F0;
      }

      .email-masthead_logo {
        width: 94px;
      }

      .email-masthead_name {
        font-size: 14px;
        font-weight: 600;
        color: #4A5568;
        text-decoration: none;
      }
      
      /* Body ------------------------------ */
      .email-body {
        width: 100%;
        margin: 0;
        padding: 0;
        background-color: #FFFFFF;
      }

      .email-body_inner {
        width: 570px;
        margin: 0 auto;
        padding: 0;
        background-color: #FFFFFF;
      }

      .email-footer {
        width: 570px;
        margin: 0 auto;
        padding: 0;
        text-align: center;
        background: #F7FAFC;
      }

      .email-footer p {
        color: #718096;
        font-size: 12px;
      }

      .content-cell {
        padding: 30px;
      }
      
      /* Simple Footer ------------------------------ */
      .simple-footer {
        padding: 20px;
        text-align: center;
        font-size: 12px;
        color: #718096;
        background: #F7FAFC;
      }
      
      /*Media Queries ------------------------------ */
      @media only screen and (max-width: 600px) {
        .email-body_inner,
        .email-footer {
          width: 100% !important;
        }
        
        .content-cell {
          padding: 20px !important;
        }
        
        .detail-label {
          width: 120px;
        }
      }

      @media (prefers-color-scheme: dark) {
        body,
        .email-body,
        .email-body_inner,
        .email-content,
        .email-wrapper,
        .email-masthead {
          background-color: #1A202C !important;
          color: #E2E8F0 !important;
        }
        
        p,
        ul,
        ol,
        blockquote,
        h1,
        h2,
        h3 {
          color: #E2E8F0 !important;
        }
        
        .invoice-container {
          background: #2D3748 !important;
          border: 1px solid #4A5568 !important;
        }
        
        .detail-row {
          border-bottom: 1px solid #4A5568 !important;
        }
        
        .total-row {
          background: #4A5568 !important;
        }
        
        .email-footer {
          background: #1A202C !important;
        }
        
        .simple-footer {
          background: #1A202C !important;
        }
        
        .detail-label {
          color: #CBD5E0 !important;
        }
        
        .detail-value {
          color: #E2E8F0 !important;
        }
      }
    </style>
  </head>
  <body>
    <span class="preheader">Factura {{ $factura->documento_referencia_fe }} - Valor: {{ number_format($factura->total_factura) }}</span>
    
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
      <tr>
        <td align="center">
          <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
              <td class="email-masthead align-center">
                <a href="#" class="f-fallback email-masthead_name align-center">
                    @if ($empresa->logo)
                    <img style='max-height:60px;max-width:200px;' src="{{ $empresa->logo }}" alt="{{ $empresa->nombre }}">
                    @else
                    <img style='max-height:60px;max-width:200px;' src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="{{ $empresa->nombre }}">
                  @endif
                  <br/>
                  <h2 class="align-center">{{ $empresa->nombre }}</h2>
                </a>
              </td>
            </tr>
            
            <!-- Email Body -->
            <tr>
              <td class="email-body" width="100%" cellpadding="0" cellspacing="0">
                <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                  <tr>
                    <td class="content-cell">
                      <!-- Saludo -->
                      <h1>Hola, {{ $cliente->nombre_completo }}</h1>
                      <p>Te enviamos los detalles de tu factura para tu revisión.</p>
                      
                      <!-- Contenedor de Factura -->
                      <div class="invoice-container">
                        
                        <div class="detail-row">
                          <span class="detail-label">Documento:</span>
                          <span class="detail-value">{{ $factura->documento_referencia_fe }}</span>
                        </div>
                        
                        <div class="detail-row">
                          <span class="detail-label">Fecha emisión:</span>
                          <span class="detail-value">{{ $factura->fecha_validacion }}</span>
                        </div>
                        
                        <div class="detail-row">
                          <span class="detail-label">Fecha vencimiento:</span>
                          <span class="detail-value">{{ $factura->fecha_vencimiento }}</span>
                        </div>
                        
                        <div class="detail-row">
                          <span class="detail-label">Días restantes:</span>
                          <span class="detail-value">
                            {{ \Carbon\Carbon::parse($factura->fecha_vencimiento)->diffInDays(now()) }} días
                          </span>
                        </div>
                        
                        <div class="total-row">
                          <span class="detail-label">Valor factura:</span>
                          <span class="detail-value">${{ number_format($factura->total_factura, 2, ',', '.') }}</span>
                        </div>
                      </div>
                      
                      <div class="divider"></div>
                      
                      <!-- Información de contacto (basada en tus includes) -->
                      @include('emails.texts.contact')
                      
                      <div style="margin-top: 20px;">
                        @include('emails.texts.emittedBy')
                      </div>
                      
                      <div style="margin-top: 15px; font-size: 12px; color: #718096;">
                        @include('emails.texts.att')
                      </div>
                      
                      <div style="margin-top: 20px; font-size: 11px; color: #A0AEC0;">
                        @include('emails.texts.unsubscribe')
                      </div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            
            <!-- Footer Simple -->
            <tr>
              <td>
                <table class="email-footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                  <tr>
                    <td class="content-cell">
                      <div class="simple-footer">
                        <p>{{ $empresa->nombre ?? 'Portafolio ERP' }}</p>
                        <p class="sub">
                          Este correo fue enviado a {{ $cliente->email }}<br>
                          © {{ date('Y') }} - Todos los derechos reservados
                        </p>
                      </div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>