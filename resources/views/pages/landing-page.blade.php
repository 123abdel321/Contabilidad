<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PORTFOLIO ERP - ERP Colombiano con Integración DIAN | Software Contable y Facturación Electrónica</title>
    <meta name="description" content="PORTFOLIO ERP es el ERP colombiano líder con integración oficial DIAN. Contabilidad, nómina, facturación electrónica y reportes en tiempo real. Demo gratuita disponible.">
    <meta name="keywords" content="ERP Colombia, facturación electrónica DIAN, software contable, nómina Colombia, PORTFOLIO ERP, sistema contable colombiano">
    <meta name="author" content="PORTFOLIO ERP">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://portfolioerp.com/">
    <meta property="og:title" content="PORTFOLIO ERP - ERP Colombiano con Integración DIAN">
    <meta property="og:description" content="El ERP colombiano que integra contabilidad, nómina y facturación DIAN en una sola plataforma. Cumple con la normativa fiscal y optimiza tus procesos empresariales.">
    <meta property="og:image" content="https://portfolioerp.com/og-image.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://portfolioerp.com/">
    <meta property="twitter:title" content="PORTFOLIO ERP - ERP Colombiano con Integración DIAN">
    <meta property="twitter:description" content="El ERP colombiano que integra contabilidad, nómina y facturación DIAN en una sola plataforma.">
    <meta property="twitter:image" content="https://portfolioerp.com/og-image.jpg">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://portfolioerp.com/">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;700&family=Open+Sans:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* Reset and Base Styles */
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        }

        body {
        font-family: "Open Sans", sans-serif;
        line-height: 1.6;
        color: #334155;
        }

        /* Agregando clase para prevenir scroll cuando el menú móvil está abierto */
        body.menu-open {
        overflow: hidden;
        }

        .page-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 50%, #f0f9ff 100%);
        }

        .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
        }

        /* Typography */
        h1,
        h2,
        h3,
        h4 {
        font-family: "Work Sans", sans-serif;
        font-weight: 700;
        color: #1e293b;
        }

        h1 {
        font-size: 2.5rem;
        line-height: 1.2;
        }

        h2 {
        font-size: 2rem;
        line-height: 1.3;
        }

        h3 {
        font-size: 1.25rem;
        line-height: 1.4;
        }

        p {
        font-size: 1rem;
        line-height: 1.6;
        color: #64748b;
        }

        .text-primary {
        color: #510ee7;
        }

        /* Buttons */
        .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        font-family: "Open Sans", sans-serif;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        }

        .btn-primary {
        background-color: #510ee7;
        color: white;
        }

        .btn-primary:hover {
        background-color: #440bbe;
        }

        .btn-outline {
        background-color: transparent;
        color: #64748b;
        border: 1px solid #e2e8f0;
        }

        .btn-outline:hover {
        background-color: #f8fafc;
        color: #2563eb;
        }

        .btn-white {
        background-color: white;
        color: #2563eb;
        }

        .btn-white:hover {
        background-color: #eff6ff;
        }

        .btn-outline-white {
        background-color: transparent;
        color: white;
        border: 1px solid white;
        }

        .btn-outline-white:hover {
        background-color: white;
        color: #2563eb;
        }

        .btn-lg {
        padding: 1rem 2rem;
        font-size: 1.125rem;
        }

        /* Header */
        .header {
        /* Header completamente transparente por defecto para integrarse con hero */
        background-color: transparent;
        backdrop-filter: none;
        border-bottom: 1px solid transparent;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 50;
        transition: all 0.3s ease;
        }

        /* Estilos para header revelado al hacer scroll */
        .header.scrolled {
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        }

        .logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        }

        /* Agregando estilos para el logo real */
        .logo-image {
        width: 2rem;
        height: 2rem;
        object-fit: contain;
        }

        .logo-icon {
        width: 2rem;
        height: 2rem;
        background-color: #2563eb;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        }

        .logo-text {
        font-family: "Work Sans", sans-serif;
        font-weight: 700;
        font-size: 1.25rem;
        color: #1e293b;
        }

        /* Agregando estilos para el botón hamburguesa */
        .mobile-menu-toggle {
        display: none;
        flex-direction: column;
        justify-content: space-around;
        width: 2rem;
        height: 2rem;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0;
        z-index: 60;
        }

        .hamburger-line {
        width: 100%;
        height: 0.125rem;
        background-color: #1e293b;
        border-radius: 0.0625rem;
        transition: all 0.3s ease;
        transform-origin: 1px;
        }

        .mobile-menu-toggle.active .hamburger-line:nth-child(1) {
        transform: rotate(45deg);
        }

        .mobile-menu-toggle.active .hamburger-line:nth-child(2) {
        opacity: 0;
        }

        .mobile-menu-toggle.active .hamburger-line:nth-child(3) {
        transform: rotate(-45deg);
        }

        /* Estilos para el menú de navegación responsive */
        .nav {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        }

        .nav-link {
        color: #64748b;
        text-decoration: none;
        font-family: "Open Sans", sans-serif;
        transition: all 0.2s ease;
        /* Agregando padding y border-radius para el efecto activo */
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        position: relative;
        }

        .nav-link:hover {
        color: #2563eb;
        background-color: rgba(37, 99, 235, 0.05);
        }

        /* Estilos para link activo - azul cuando estás en esa sección */
        .nav-link.active {
        color: #2563eb;
        background-color: rgba(37, 99, 235, 0.1);
        font-weight: 600;
        }

        .nav-link.active::after {
        content: "";
        position: absolute;
        bottom: -0.25rem;
        left: 50%;
        transform: translateX(-50%);
        width: 1rem;
        height: 0.125rem;
        background-color: #2563eb;
        border-radius: 0.0625rem;
        }

        /* Media queries para responsive design del header */
        @media (max-width: 768px) {
        .mobile-menu-toggle {
            display: flex;
        }

        .nav {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 300px;
            height: 100vh;
            background-color: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            flex-direction: column;
            justify-content: flex-start;
            align-items: stretch;
            padding: 5rem 2rem 2rem;
            gap: 0;
            transition: right 0.3s ease;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
        }

        .nav.active {
            right: 0;
        }

        .nav-link {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            border-radius: 0;
            text-align: center;
        }

        .nav-link.active::after {
            display: none;
        }

        .nav .btn {
            margin-top: 1rem;
            justify-content: center;
        }

        .nav .btn-outline {
            margin-bottom: 0.5rem;
        }

        /* Ajuste del color de las líneas hamburguesa cuando el header tiene scroll */
        .header.scrolled .hamburger-line {
            background-color: #1e293b;
        }
        }

        /* Hero Section */
        .hero {
        /* Ajustando padding-top para que el hero se integre visualmente con el header transparente */
        padding: 6rem 1rem 5rem;
        }

        .hero-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 3rem;
        align-items: center;
        }

        @media (min-width: 1024px) {
        .hero-grid {
            grid-template-columns: 1fr 1fr;
        }
        }

        .hero-content {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        }

        .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background-color: #dbeafe;
        color: #1d4ed8;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        width: fit-content;
        }

        .hero-title {
        font-size: 2.5rem;
        line-height: 1.2;
        margin-bottom: 1rem;
        }

        @media (min-width: 1024px) {
        .hero-title {
            font-size: 3rem;
        }
        }

        .hero-description {
        font-size: 1.125rem;
        line-height: 1.6;
        color: #64748b;
        }

        .hero-buttons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        }

        @media (min-width: 640px) {
        .hero-buttons {
            flex-direction: row;
        }
        }

        .hero-features {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        font-size: 0.875rem;
        color: #64748b;
        }

        .feature-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        }

        .feature-item svg {
        color: #16a34a;
        }

        /* Dashboard */
        .hero-dashboard {
        position: relative;
        }

        .dashboard-card {
        background-color: white;
        border-radius: 1rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        padding: 1.5rem;
        animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
        0%,
        100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
        }

        .dashboard-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        }

        .dashboard-header h3 {
        font-family: "Work Sans", sans-serif;
        font-weight: 600;
        color: #1e293b;
        }

        .status-badge {
        background-color: #f3f4f6;
        color: #16a34a;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        }

        .dashboard-metrics {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
        }

        .metric-card {
        padding: 1rem;
        border-radius: 0.5rem;
        }

        .metric-blue {
        background-color: #eff6ff;
        }

        .metric-green {
        background-color: #f0fdf4;
        }

        .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        }

        .metric-blue .metric-value {
        color: #2563eb;
        }

        .metric-green .metric-value {
        color: #16a34a;
        }

        .metric-label {
        font-size: 0.875rem;
        color: #64748b;
        }

        .dashboard-chart {
        height: 8rem;
        background: linear-gradient(to right, #dbeafe, #e0f2fe);
        border-radius: 0.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        }

        .dashboard-chart svg {
        color: #2563eb;
        }

        .chart-label {
        font-size: 0.75rem;
        color: #64748b;
        }

        /* Section Headers */
        .section-header {
        text-align: center;
        margin-bottom: 3rem;
        }

        .section-header h2 {
        margin-bottom: 1rem;
        }

        .section-header p {
        font-size: 1.125rem;
        max-width: 32rem;
        margin: 0 auto;
        }

        /* Problems Section */
        .problems {
        padding: 4rem 1rem;
        background-color: #f8fafc;
        }

        .problems-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        }

        @media (min-width: 768px) {
        .problems-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        }

        .problem-card {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        transition: box-shadow 0.2s ease;
        }

        .problem-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .problem-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        }

        .problem-red {
        background-color: #fef2f2;
        color: #dc2626;
        }

        .problem-orange {
        background-color: #fff7ed;
        color: #ea580c;
        }

        .problem-blue {
        background-color: #dbeafe;
        color: #2563eb;
        }

        .problem-card h3 {
        margin-bottom: 1rem;
        }

        /* Modules Section */
        .modules {
        padding: 4rem 1rem;
        }

        .modules-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        }

        @media (min-width: 768px) {
        .modules-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        }

        @media (min-width: 1024px) {
        .modules-grid {
            grid-template-columns: repeat(4, 1fr);
        }
        }

        .module-card {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        text-align: center;
        transition: all 0.2s ease;
        }

        .module-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: translateY(-4px);
        }

        .module-icon {
        width: 4rem;
        height: 4rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        }

        .module-blue {
        background-color: #dbeafe;
        color: #2563eb;
        }

        .module-green {
        background-color: #dcfce7;
        color: #16a34a;
        }

        .module-purple {
        background-color: #f3e8ff;
        color: #9333ea;
        }

        .module-sky {
        background-color: #e0f2fe;
        color: #0284c7;
        }

        .module-card h3 {
        margin-bottom: 1rem;
        }

        /* Benefits Section */
        .benefits {
        padding: 4rem 1rem;
        background-color: #2563eb;
        color: white;
        }

        .benefits .section-header h2 {
        color: white;
        }

        .benefits .section-header p {
        color: #bfdbfe;
        }

        .benefits-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        }

        @media (min-width: 768px) {
        .benefits-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        }

        @media (min-width: 1024px) {
        .benefits-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        }

        .benefit-item {
        text-align: center;
        }

        .benefit-icon {
        width: 4rem;
        height: 4rem;
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: white;
        }

        .benefit-item h3 {
        color: white;
        margin-bottom: 0.5rem;
        }

        .benefit-item p {
        color: #bfdbfe;
        }

        /* Testimonials Section */
        .testimonials {
        padding: 4rem 1rem;
        }

        .testimonials-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        margin-bottom: 3rem;
        }

        @media (min-width: 768px) {
        .testimonials-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        }

        .testimonial-card {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        }

        .stars {
        display: flex;
        gap: 0.25rem;
        margin-bottom: 1rem;
        }

        .star {
        color: #fbbf24;
        font-size: 1.25rem;
        }

        .testimonial-card blockquote {
        font-style: italic;
        color: #64748b;
        margin-bottom: 1rem;
        line-height: 1.6;
        }

        .testimonial-author {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        }

        .author-avatar {
        width: 2.5rem;
        height: 2.5rem;
        background-color: #dbeafe;
        color: #2563eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: "Work Sans", sans-serif;
        font-weight: 600;
        }

        .author-name {
        font-family: "Work Sans", sans-serif;
        font-weight: 600;
        color: #1e293b;
        }

        .author-title {
        font-size: 0.875rem;
        color: #64748b;
        }

        /* CTA Section */
        .cta {
        padding: 5rem 1rem;
        background: linear-gradient(to right, #2563eb, #0284c7);
        color: white;
        }

        .cta-content {
        text-align: center;
        max-width: 64rem;
        margin: 0 auto;
        }

        .cta-content h2 {
        color: white;
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        }

        .cta-content > p {
        font-size: 1.25rem;
        color: #bfdbfe;
        margin-bottom: 2rem;
        max-width: 32rem;
        margin-left: auto;
        margin-right: auto;
        }

        .cta-buttons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        justify-content: center;
        margin-bottom: 2rem;
        }

        @media (min-width: 640px) {
        .cta-buttons {
            flex-direction: row;
        }
        }

        .cta-features {
        color: #bfdbfe;
        }

        .cta-features p {
        color: #bfdbfe;
        margin-bottom: 0.5rem;
        }

        /* Footer */
        .footer {
        background-color: #0f172a;
        color: white;
        padding: 3rem 1rem;
        }

        .footer-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        padding-top: 80px;
        padding-bottom: 80px;
        }

        @media (min-width: 768px) {
        .footer-grid {
            grid-template-columns: repeat(4, 1fr);
        }
        }

        .footer-brand .logo-text {
        color: white;
        }

        .footer-brand p {
        color: #94a3b8;
        margin-top: 1rem;
        }

        .footer-column h4 {
        color: white;
        font-family: "Work Sans", sans-serif;
        font-weight: 600;
        margin-bottom: 1rem;
        }

        .footer-column ul {
        list-style: none;
        }

        .footer-column li {
        margin-bottom: 0.5rem;
        }

        .footer-column a {
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.2s ease;
        }

        .footer-column a:hover {
        color: white;
        }

        .footer-bottom {
        border-top: 1px solid #334155;
        padding-top: 2rem;
        text-align: center;
        }

        .footer-bottom p {
        color: #94a3b8;
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .hero-title {
                font-size: 2rem;
            }

            .section-header h2 {
                font-size: 1.75rem;
            }

            .cta-content h2 {
                font-size: 2rem;
            }

            /* Ajuste para móviles con header transparente */
            .hero {
                padding: 5rem 1rem 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Header -->
        <header class="header" id="header">
            <div class="container header-content">
                <div class="logo">
                    <!-- Reemplazando el icono SVG con el logo real -->
                    <img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="PORTFOLIO ERP Logo" class="logo-image">
                    <span class="logo-text">PORTFOLIO ERP</span>
                </div>
                
                <!-- Agregando botón hamburguesa para móvil -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Abrir menú">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
                
                <!-- Agregando clase mobile-menu al nav -->
                <nav class="nav mobile-menu" id="mobileMenu">
                    <a href="#modulos" class="nav-link" data-section="modulos">Módulos</a>
                    <a href="#beneficios" class="nav-link" data-section="beneficios">Beneficios</a>
                    <a href="#clientes" class="nav-link" data-section="clientes">Clientes</a>
                    <a href="#contacto" class="nav-link" data-section="contacto">Contacto</a>
                    <button onclick="window.location.href='https://app.portafolioerp.com/login'" class="btn btn-primary">Iniciar Sesión</button>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <div class="hero-grid">
                    <div class="hero-content">
                        <div class="hero-text">
                            <div class="badge">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                                Integración Oficial DIAN
                            </div>
                            <h1 class="hero-title">
                                El ERP colombiano que integra 
                                <span class="text-primary">contabilidad, nómina y facturación DIAN</span> 
                                en una sola plataforma
                            </h1>
                            <p class="hero-description">
                                Cumple con la normativa fiscal, optimiza tus procesos empresariales y toma decisiones basadas en datos
                                reales. Todo desde una interfaz intuitiva diseñada para empresas colombianas.
                            </p>
                        </div>
                        <div class="hero-buttons">
                            <a href="#contacto" class="btn btn-primary btn-lg">
                                Solicita Demo Gratuita
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12,5 19,12 12,19"></polyline>
                                </svg>
                            </a>
                            <!-- <button class="btn btn-outline btn-lg">Ver Video Demo</button> -->
                        </div>
                        <div class="hero-features">
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
                    <div class="hero-dashboard">
                        <div class="dashboard-card">
                            <div class="dashboard-header">
                                <h3>Dashboard Ejecutivo</h3>
                                <span class="status-badge">En tiempo real</span>
                            </div>
                            <div class="dashboard-metrics">
                                <div class="metric-card metric-blue">
                                    <div class="metric-value">$2.4M</div>
                                    <div class="metric-label">Ingresos mes</div>
                                </div>
                                <div class="metric-card metric-green">
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
                <div class="section-header">
                    <h2>Problemas que resolvemos cada día</h2>
                    <p>Empresas colombianas enfrentan desafíos únicos. PORTFOLIO ERP está diseñado específicamente para resolverlos.</p>
                </div>
                <div class="problems-grid">
                    <div class="problem-card">
                        <div class="problem-icon problem-red">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <h3>Sanciones DIAN</h3>
                        <p>Evita multas por errores en facturación electrónica. Nuestro sistema garantiza cumplimiento normativo automático.</p>
                    </div>
                    <div class="problem-card">
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
                    <div class="problem-card">
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
                <div class="section-header">
                    <h2>Módulos integrados para tu empresa</h2>
                    <p>Cada módulo trabaja en perfecta sincronía, compartiendo información en tiempo real.</p>
                </div>
                <div class="modules-grid">
                    <div class="module-card">
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
                    <div class="module-card">
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
                    <div class="module-card">
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
                    <div class="module-card">
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

        <!-- Benefits Section -->
        <section id="beneficios" class="benefits">
            <div class="container">
                <div class="section-header">
                    <h2>¿Por qué elegir PORTFOLIO ERP?</h2>
                    <p>Más que un software, somos tu socio tecnológico para el crecimiento empresarial.</p>
                </div>
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                            </svg>
                        </div>
                        <h3>Implementación Rápida</h3>
                        <p>Tu empresa operando en menos de 48 horas con migración de datos incluida.</p>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <h3>Seguridad Garantizada</h3>
                        <p>Certificación ISO 27001, backups automáticos y encriptación de extremo a extremo.</p>
                    </div>
                    <div class="benefit-item">
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
                <div class="section-header">
                    <h2>Empresas que confían en nosotros</h2>
                    <p>Más de 500 empresas colombianas han transformado sus procesos con PORTFOLIO ERP.</p>
                </div>
                <div class="testimonials-grid">
                    <div class="testimonial-card">
                        <div class="stars">
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                            <span class="star">★</span>
                        </div>
                        <blockquote>
                            "Desde que usamos PORTFOLIO ERP, cerramos mes en horas, no en días. La integración con la DIAN es
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
                    <div class="testimonial-card">
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
                <div class="cta-content">
                    <h2>Transforma tu empresa hoy mismo</h2>
                    <p>Únete a las empresas que ya optimizaron sus procesos y cumplen sin estrés con la normativa fiscal colombiana.</p>
                    <div class="cta-buttons">
                        <button class="btn btn-white btn-lg">
                            Solicitar Demo Gratuita
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12,5 19,12 12,19"></polyline>
                            </svg>
                        </button>
                        <button class="btn btn-outline-white btn-lg">Hablar con un Experto</button>
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
                                <img src="https://app.portafolioerp.com/img/logo_contabilidad.png" style="width: 50px;" />
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
                                <li>+57 (1) 234-5678</li>
                                <li>info@portfolioerp.com</li>
                                <li>Bogotá, Colombia</li>
                            </ul>
                        </div>
                    </div>
                    <div class="footer-bottom">
                        <p>&copy; 2024 PORTFOLIO ERP. Todos los derechos reservados.</p>
                    </div>
                </div>
            </footer>
        </section>
    </div>

    <!-- Agregando JavaScript para efectos de scroll -->
    <script>
        // Header scroll effect
        const header = document.getElementById('header');
        const navLinks = document.querySelectorAll('.nav-link');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        
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
        
        // Función para manejar el scroll del header
        function handleHeaderScroll() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
        
        // Función para detectar la sección activa
        function handleActiveSection() {
            const sections = document.querySelectorAll('section[id]');
            const scrollPos = window.scrollY + 150;
            
            // Remover clase activa de todos los links primero
            navLinks.forEach(link => link.classList.remove('active'));
            
            // Si estamos en la parte superior (hero), no marcar ninguna sección
            if (window.scrollY < 100) {
                return;
            }
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute('id');
                
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    // Agregar clase activa al link correspondiente
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
        });
        
        mobileMenuToggle.addEventListener('click', toggleMobileMenu);
        
        // Smooth scroll para los links de navegación
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
            handleHeaderScroll();
            handleActiveSection();
        });
    </script>
</body>
</html>
