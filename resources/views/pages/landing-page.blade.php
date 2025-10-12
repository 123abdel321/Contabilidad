<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>PORTAFOLIOERP: Plataforma Integral de ERP, POS y Administración PH | Colombia</title>
    
    <meta name="description" content="PORTAFOLIOERP es la plataforma integral líder en Colombia. Unifica tu ERP (Contabilidad DIAN, Nómina, Facturación Electrónica), Módulo POS (Ventas) y Gestión de Propiedad Horizontal (Maximoph.co).">
    
    <meta name="keywords" content="ERP Colombia, facturación electrónica DIAN, software contable, nómina Colombia, PORTAFOLIOERP, sistema contable, POS Colombia, software POS, Administración PH, Maximoph, software propiedad horizontal, gestión empresarial">
    
    <meta name="author" content="PORTAFOLIOERP">
    <meta name="robots" content="index, follow">
    
    <link rel="canonical" href="https://portafolioerp.com/">
    
    <link rel="related" href="https://pos.portafolioerp.com/" title="Módulo POS de Portafolio ERP">
    <link rel="related" href="https://maximoph.co/" title="Administración PH - Maximoph">

    <meta property="og:type" content="website">
    <meta property="og:url" content="https://portafolioerp.com/">
    <meta property="og:title" content="PORTAFOLIOERP: Un solo sistema para ERP, POS y Propiedad Horizontal">
    <meta property="og:description" content="Centraliza tu Contabilidad DIAN, Ventas POS y Gestión PH. Tres soluciones, un solo ecosistema robusto.">
    <meta property="og:image" content="https://PORTAFOLIOERP.com/og-image.jpg">
    
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://PORTAFOLIOERP.com/">
    <meta property="twitter:title" content="PORTAFOLIOERP: El ERP + POS + Maximoph para tu empresa.">
    <meta property="twitter:description" content="Centraliza tu Contabilidad DIAN, Ventas POS y Gestión PH. Tres soluciones, un solo ecosistema.">
    <meta property="twitter:image" content="https://PORTAFOLIOERP.com/og-image.jpg">
    
    <link rel="icon" type="image/png" href="/img/logo_contabilidad.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="mask-icon" href="https://maximoph.co/img/logo_contabilidad.png" color="#000000">
    <link rel="alternate icon" class="js-site-favicon" type="image/png" href="/img/logo_contabilidad.png">
    <link rel="icon" class="js-site-favicon" type="image/png" href="/img/logo_contabilidad.png">
    
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "url": "https://portafolioerp.com/",
      "name": "PORTAFOLIOERP | Plataforma de Software Empresarial",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://portafolioerp.com/buscar?q={search_term_string}",
        "query-input": "required name=search_term_string"
      },
      "hasPart": [
        {
          "@type": "WebPage",
          "name": "Facturación Electrónica DIAN",
          "url": "https://portafolioerp.com/#facturacion"
        },
        {
          "@type": "WebPage",
          "name": "Software Contable y Nómina",
          "url": "https://portafolioerp.com/#contabilidad-nomina"
        },
        {
          "@type": "WebPage",
          "name": "Módulo POS (Punto de Venta)",
          "url": "https://pos.portafolioerp.com/"
        },
        {
          "@type": "WebPage",
          "name": "Maximoph - Administración PH",
          "url": "https://maximoph.co/"
        },
        {
          "@type": "WebPage",
          "name": "Ingresar",
          "url": "https://app.portafolioerp.com/login" 
        }
      ]
    }
    </script>

    <link id="pagestyle" href="{{ asset('assets/css/landing-page.css') }}?v={{ config('app.version') }} " rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700&family=Open+Sans:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="page-container">
        <!-- Header -->
        <header class="header" id="header">
            <div class="container header-content">
                <div class="logo">
                    <img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="PORTAFOLIOERP Logo" class="logo-image">
                    <span class="logo-text">PORTAFOLIO ERP</span>
                </div>
                
                <!-- Agregando toggle de modo oscuro -->
                <div class="header-controls">
                    <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
                        <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                        <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </button>
                    
                    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Abrir menú">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>
                </div>
                
                <nav class="nav mobile-menu" id="mobileMenu">
                    <a href="#modulos" class="nav-link" data-section="modulos">Módulos</a>
                    <a href="#portafolio-integrado" class="nav-link" data-section="portafolio-integrado">Portafolio Integrado</a>
                    <a href="#beneficios" class="nav-link" data-section="beneficios">Beneficios</a>
                    <a href="#clientes" class="nav-link" data-section="clientes">Clientes</a>
                    <a href="#contacto" class="nav-link" data-section="contacto">Contacto</a>
                    <a href="/login" class="btn btn-outline">Iniciar Sesión</a>
                </nav>
            </div>
        </header>

        <!-- Agregando botón scroll to top -->
        <button class="scroll-to-top" id="scrollToTop" aria-label="Volver arriba">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="19" x2="12" y2="5"></line>
                <polyline points="5,12 12,5 19,12"></polyline>
            </svg>
        </button>

        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-content animate-fade-in">
                        <div class="hero-text">
                            <div class="badge animate-slide-up">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                                Integración Oficial DIAN
                            </div>
                            <h1 class="hero-title animate-slide-up">
                                El ERP colombiano que integra 
                                <span class="text-primary">contabilidad, nómina y facturación DIAN</span> 
                                en una sola plataforma
                            </h1>
                            <p class="hero-description animate-slide-up">
                                Cumple con la normativa fiscal, optimiza tus procesos empresariales y toma decisiones basadas en datos
                                reales. Todo desde una interfaz intuitiva diseñada para empresas colombianas.
                            </p>
                        </div>
                        <div class="hero-buttons animate-slide-up">
                            <a class="btn btn-primary btn-lg" href="/login">
                                Iniciar Sesión
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12,5 19,12 12,19"></polyline>
                                </svg>
                            </a>
                        </div>
                        <div class="hero-features animate-fade-in">
                            <div class="feature-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22,4 12,14.01 9,11.01"></polyline>
                                </svg>
                                <span>Sin instalación</span>
                            </div>
                            <div class="feature-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22,4 12,14.01 9,11.01"></polyline>
                                </svg>
                                <span>Soporte 24/7</span>
                            </div>
                            <div class="feature-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22,4 12,14.01 9,11.01"></polyline>
                                </svg>
                                <span>Datos seguros</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-dashboard animate-float">
                        <div class="dashboard-card">
                            <div class="dashboard-header">
                                <h3>Dashboard Ejecutivo</h3>
                                <span class="status-badge animate-pulse">En tiempo real</span>
                            </div>
                            <div class="dashboard-metrics">
                                <div class="metric-card metric-blue animate-scale">
                                    <div class="metric-value">$2.4M</div>
                                    <div class="metric-label">Ingresos mes</div>
                                </div>
                                <div class="metric-card metric-green animate-scale">
                                    <div class="metric-value">847</div>
                                    <div class="metric-label">Facturas DIAN</div>
                                </div>
                            </div>
                            <div class="dashboard-chart">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="20" x2="18" y2="10"></line>
                                    <line x1="12" y1="20" x2="12" y2="4"></line>
                                    <line x1="6" y1="20" x2="6" y2="14"></line>
                                </svg>
                                <div class="chart-label">Gráficos interactivos</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Problems Section -->
        <section class="problems" id="problemas">
            <div class="container">
                <div class="section-header animate-on-scroll">
                    <h2>Problemas que resolvemos cada día</h2>
                    <p>Empresas colombianas enfrentan desafíos únicos. PORTAFOLIOERP está diseñado específicamente para resolverlos.</p>
                </div>
                <div class="problems-grid">
                    <div class="problem-card animate-on-scroll">
                        <div class="problem-icon problem-red">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <h3>Sanciones DIAN</h3>
                        <p>Evita multas por errores en facturación electrónica. Nuestro sistema garantiza cumplimiento normativo automático.</p>
                    </div>
                    <div class="problem-card animate-on-scroll">
                        <div class="problem-icon problem-orange">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14,2 14,8 20,8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10,9 9,9 8,9"></polyline>
                            </svg>
                        </div>
                        <h3>Procesos Manuales</h3>
                        <p>Centraliza contabilidad y nómina sin hojas de cálculo. Automatiza tareas repetitivas y reduce errores humanos.</p>
                    </div>
                    <div class="problem-card animate-on-scroll">
                        <div class="problem-icon problem-blue">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                        </div>
                        <h3>Falta de Visibilidad</h3>
                        <p>Toma decisiones con datos reales en tiempo real. Reportes gerenciales que impulsan el crecimiento.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modules Section -->
        <section id="modulos" class="modules">
            <div class="container">
                <div class="section-header animate-on-scroll">
                    <h2>Módulos integrados para tu empresa</h2>
                    <p>Cada módulo trabaja en perfecta sincronía, compartiendo información en tiempo real.</p>
                </div>
                <div class="modules-grid">
                    <div class="module-card animate-on-scroll">
                        <div class="module-icon module-blue">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                        </div>
                        <h3>Contabilidad</h3>
                        <p>Plan de cuentas PUC, estados financieros automáticos, conciliación bancaria.</p>
                    </div>
                    <div class="module-card animate-on-scroll">
                        <div class="module-icon module-green">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14,2 14,8 20,8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10,9 9,9 8,9"></polyline>
                            </svg>
                        </div>
                        <h3>Facturación</h3>
                        <p>Facturación tradicional y electrónica, cotizaciones, control de inventarios.</p>
                    </div>
                    <div class="module-card animate-on-scroll">
                        <div class="module-icon module-purple">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3>Nómina</h3>
                        <p>Liquidación automática, prestaciones sociales, reportes ministeriales.</p>
                    </div>
                    <div class="module-card animate-on-scroll">
                        <div class="module-icon module-sky">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <h3>DIAN</h3>
                        <p>Facturación electrónica, reportes exógenas, medios magnéticos automáticos.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pos Section -->
        <section id="portafolio-integrado" class="integrated-portfolio">
            <div class="container">
                <div class="section-header animate-on-scroll">
                    <h2>Expande tu gestión con el Ecosistema PORTAFOLIOERP</h2>
                    <p>Una plataforma que crece con tu negocio: Módulo POS integrado y Gestión de Propiedad Horizontal.</p>
                </div>
                
                <div class="integrated-grid">
                    <div class="integrated-card card-pos animate-on-scroll">
                        <div class="integrated-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 3h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path>
                                <path d="M12 17v-4"></path>
                                <path d="M8 21h8"></path>
                                <path d="M5 13l-1 8h16l-1-8"></path>
                            </svg>
                        </div>
                        <h3>Módulo POS: Punto de Venta Integrado</h3>
                        <p>Convierte ventas minoristas rápidas directamente en facturas DIAN y actualiza el inventario en tiempo real con tu ERP. Ideal para tiendas, restaurantes y comercio.</p>
                        <ul class="features-list">
                            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Inventario automático</li>
                            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Facturación en segundos</li>
                            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Compatible con tótems y cajón</li>
                        </ul>
                        <a href="https://pos.portafolioerp.com/" target="_blank" class="btn btn-secondary mt-4">
                            Ver Módulo POS
                        </a>
                    </div>

                    <div class="integrated-card card-maximoph animate-on-scroll">
                        <div class="integrated-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                <path d="M2 17l10 5 10-5"></path>
                                <path d="M2 12l10 5 10-5"></path>
                            </svg>
                        </div>
                        <h3>Maximoph: Gestión de Propiedad Horizontal</h3>
                        <p>Software especializado en la administración de edificios y conjuntos residenciales. Conciliación bancaria, reportes contables y gestión de cuotas de administración.</p>
                        <ul class="features-list">
                            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Conciliación bancaria PH</li>
                            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Gestión de asambleas</li>
                            <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Portal para residentes</li>
                        </ul>
                        <a href="https://maximoph.co/" target="_blank" class="btn btn-secondary mt-4">
                            Ver Maximoph
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section id="beneficios" class="benefits">
            <div class="container">
                <div class="section-header animate-on-scroll">
                    <h2>¿Por qué elegir PORTAFOLIOERP?</h2>
                    <p>Más que un software, somos tu socio tecnológico para el crecimiento empresarial.</p>
                </div>
                <div class="benefits-grid">
                    <div class="benefit-item animate-on-scroll">
                        <div class="benefit-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                            </svg>
                        </div>
                        <h3>Implementación Rápida</h3>
                        <p>Tu empresa operando en menos de 48 horas con migración de datos incluida.</p>
                    </div>
                    <div class="benefit-item animate-on-scroll">
                        <div class="benefit-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <h3>Seguridad Garantizada</h3>
                        <p>Certificación ISO 27001, backups automáticos y encriptación de extremo a extremo.</p>
                    </div>
                    <div class="benefit-item animate-on-scroll">
                        <div class="benefit-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3>Soporte Especializado</h3>
                        <p>Equipo de contadores y desarrolladores disponibles 24/7 para tu tranquilidad.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="clientes" class="testimonials">
            <div class="container">
                <div class="section-header animate-on-scroll">
                    <h2>Empresas que confían en nosotros</h2>
                    <p>Más de 30 empresas colombianas han transformado sus procesos con PORTAFOLIO ERP.</p>
                </div>
                <div class="testimonials-grid">
                    <div class="testimonial-card animate-on-scroll">
                        <div class="stars">
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                        </div>
                        <blockquote>
                            "Desde que usamos PORTAFOLIOERP, cerramos mes en horas, no en días. La integración con la DIAN es
                            perfecta y nunca más hemos tenido problemas fiscales."
                        </blockquote>
                        <div class="testimonial-author">
                            <div class="author-avatar">MC</div>
                            <div class="author-info">
                                <div class="author-name">María Contreras</div>
                                <div class="author-title">Directora Financiera, Grupo Empresarial</div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-card animate-on-scroll">
                        <div class="stars">
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                        </div>
                        <blockquote>
                            "La nómina que antes nos tomaba una semana, ahora la procesamos en minutos. El soporte técnico es
                            excepcional, siempre disponibles cuando los necesitamos."
                        </blockquote>
                        <div class="testimonial-author">
                            <div class="author-avatar">JR</div>
                            <div class="author-info">
                                <div class="author-name">Jorge Ramírez</div>
                                <div class="author-title">Gerente General, Constructora del Valle</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta">
            <div class="container">
                <div class="cta-content animate-on-scroll">
                    <h2>Transforma tu empresa hoy mismo</h2>
                    <p>Únete a las empresas que ya optimizaron sus procesos y cumplen sin estrés con la normativa fiscal colombiana.</p>
                    <div class="cta-buttons">
                        <a href="https://wa.me/573207141104?text=Hola%2C%20estoy%20interesado%20en%20obtener%20m%C3%A1s%20informaci%C3%B3n%20sobre%20el%20sistema%20Portafolio%20ERP.%20%C2%BFPodr%C3%ADan%20brindarme%20detalles%3F" 
                        target="_blank" 
                        class="btn btn-outline-white btn-lg">
                            Hablar con un Experto
                        </a>
                    </div>
                    <div class="cta-features">
                        <p>✓ Demo personalizada de 30 minutos</p>
                        <p>✓ Análisis gratuito de tus procesos actuales</p>
                        <p>✓ Sin compromiso ni costos ocultos</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <section id="contacto">
            <footer class="footer">
                <div class="container">
                    <div class="footer-grid">
                        <div class="footer-brand">
                            <div class="logo">
                                
                                <div class="logo-icon">
                                    <img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="PORTAFOLIOERP Logo" class="logo-image">
                                </div>
                                <span class="logo-text">PORTAFOLIO ERP</span>
                            </div>
                            <p>El ERP colombiano diseñado para empresas que buscan crecer con tecnología confiable.</p>
                        </div>
                        <div class="footer-column">
                            <h4>Producto</h4>
                            <ul>
                                <li><a href="#">Contabilidad</a></li>
                                <li><a href="#">Facturación</a></li>
                                <li><a href="#">Nómina</a></li>
                                <li><a href="#">DIAN</a></li>
                            </ul>
                        </div>
                        <div class="footer-column">
                            <h4>Empresa</h4>
                            <ul>
                                <li><a href="#">Nosotros</a></li>
                                <li><a href="#">Clientes</a></li>
                                <li><a href="#">Soporte</a></li>
                                <li><a href="#">Blog</a></li>
                            </ul>
                        </div>
                        <div class="footer-column">
                            <h4>Contacto</h4>
                            <ul>
                                <li>+57 3207141104</li>
                                <li>portafolioerp@gmail.com</li>
                                <li>Medellín, Colombia</li>
                            </ul>
                        </div>
                    </div>
                    <div class="footer-bottom">
                        <p>&copy; 2025 PORTAFOLIO ERP. Todos los derechos reservados.</p>
                    </div>
                </div>
            </footer>
        </section>
    </div>

    <script>
        // Header scroll effect
        const header = document.getElementById('header');
        const navLinks = document.querySelectorAll('.nav-link');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const themeToggle = document.getElementById('themeToggle');
        const scrollToTop = document.getElementById('scrollToTop');
        
        function initTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        }
        
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }
        
        function updateThemeIcon(theme) {
            const sunIcon = themeToggle.querySelector('.sun-icon');
            const moonIcon = themeToggle.querySelector('.moon-icon');
            
            if (theme === 'dark') {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            } else {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
        }
        
        function handleScrollToTop() {
            if (window.scrollY > 300) {
                scrollToTop.classList.add('visible');
            } else {
                scrollToTop.classList.remove('visible');
            }
        }
        
        function scrollToTopAction() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        function handleScrollAnimations() {
            const animateElements = document.querySelectorAll('.animate-on-scroll');
            
            animateElements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < window.innerHeight - elementVisible) {
                    element.classList.add('animate-visible');
                }
            });
        }
        
        function toggleMobileMenu() {
            mobileMenu.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        }
        
        function closeMobileMenu() {
            mobileMenu.classList.remove('active');
            mobileMenuToggle.classList.remove('active');
            document.body.classList.remove('menu-open');
        }
        
        function handleHeaderScroll() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
        
        function handleActiveSection() {
            const sections = document.querySelectorAll('section[id]');
            const scrollPos = window.scrollY + 150;
            
            navLinks.forEach(link => link.classList.remove('active'));
            
            if (window.scrollY < 100) {
                return;
            }
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute('id');
                
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    const activeLink = document.querySelector(`[data-section="${sectionId}"]`);
                    if (activeLink) {
                        activeLink.classList.add('active');
                    }
                }
            });
        }
        
        // Event listeners
        window.addEventListener('scroll', () => {
            handleHeaderScroll();
            handleActiveSection();
            handleScrollToTop();
            handleScrollAnimations();
        });
        
        mobileMenuToggle.addEventListener('click', toggleMobileMenu);
        themeToggle.addEventListener('click', toggleTheme);
        scrollToTop.addEventListener('click', scrollToTopAction);
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                
                if (targetSection) {
                    const headerHeight = header.offsetHeight;
                    const targetPosition = targetSection.offsetTop - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                    
                    closeMobileMenu();
                }
            });
        });
        
        document.addEventListener('click', (e) => {
            if (!header.contains(e.target) && mobileMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        });
        
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
        });
        
        // Inicializar en carga de página
        document.addEventListener('DOMContentLoaded', () => {
            initTheme();
            handleHeaderScroll();
            handleActiveSection();
            handleScrollToTop();
            handleScrollAnimations();
        });
    </script>
</body>
</html>
