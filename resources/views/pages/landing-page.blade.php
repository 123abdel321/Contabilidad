<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Portafolio ERP | Software Contable y Facturación Electrónica DIAN para Colombia</title>
    
    <meta name="description" content="Software ERP colombiano con Contabilidad DIAN, Facturación Electrónica, Nómina y POS. Todo en una plataforma 100% web. Cumple con la normativa fiscal.">
    
    <!-- Schema Markup para Software -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "Portafolio ERP",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "offers": {
        "@type": "Offer",
        "price": "99000",
        "priceCurrency": "COP"
      },
      "description": "Software ERP colombiano con Contabilidad DIAN, Facturación Electrónica, Nómina y POS integrados",
      "featureList": "Contabilidad, Facturación DIAN, Nómina, Punto de Venta, Reportes",
      "softwareVersion": "2.0"
    }
    </script>
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --secondary: #1e40af;
            --dark: #1e293b;
            --darker: #0f172a;
            --light: #ffffff;
            --gray: #64748b;
            --gray-light: #f1f5f9;
            --gray-dark: #334155;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        [data-theme="dark"] {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #60a5fa;
            --secondary: #93c5fd;
            --dark: #f8fafc;
            --darker: #f1f5f9;
            --light: #0f172a;
            --gray: #94a3b8;
            --gray-light: #1e293b;
            --gray-dark: #cbd5e1;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            transition: var(--transition);
            overflow-x: hidden;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header & Navigation */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            background: var(--light);
            border-bottom: 1px solid var(--gray-light);
            z-index: 1000;
            transition: var(--transition);
            padding: 1rem 0;
        }
        
        .header.scrolled {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
        }
        
        [data-theme="dark"] .header {
            background: rgba(15, 23, 42, 0.95);
            border-bottom: 1px solid #1e293b;
        }

        [data-theme="dark"] .header.scrolled {
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(10px);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-weight: 700;
        }
        
        .logo img {
            height: 40px;
            width: auto;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-link {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: var(--transition);
            padding: 0.5rem 0;
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: var(--primary);
        }
        
        [data-theme="dark"] .nav-link {
            color: #e2e8f0;
        }

        [data-theme="dark"] .nav-link:hover {
            color: var(--primary-light);
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            border: 2px solid transparent;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        
        [data-theme="dark"] .btn-outline {
            color: var(--primary-light);
            border-color: var(--primary-light);
        }

        [data-theme="dark"] .btn-outline:hover {
            background: var(--primary-light);
            color: #0f172a;
        }
        
        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }
        
        /* Hero Section */
        .hero {
            padding: 180px 0 120px;
            background: linear-gradient(135deg, var(--gray-light) 0%, var(--light) 100%);
            position: relative;
            overflow: hidden;
        }
        
        [data-theme="dark"] .hero {
            background: linear-gradient(135deg, #1a202c 0%, #0f172a 100%);
        }
        
        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }
        
        .hero-content {
            max-width: 600px;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        
        [data-theme="dark"] .hero-badge {
            background: rgba(59, 130, 246, 0.2);
            color: var(--primary-light);
        }
        
        .hero-title {
            font-size: 3.5rem;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        
        .hero-title .highlight {
            color: var(--primary);
            position: relative;
        }
        
        .hero-title .highlight::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 8px;
            background: rgba(37, 99, 235, 0.2);
            z-index: -1;
        }
        
        .hero-description {
            font-size: 1.125rem;
            color: var(--gray);
            margin-bottom: 2rem;
        }
        
        [data-theme="dark"] .hero-description {
            color: #cbd5e1;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
        }
        
        .hero-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            color: var(--gray);
        }
        
        [data-theme="dark"] .feature-item {
            color: #cbd5e1;
        }
        
        .feature-item svg {
            color: var(--success);
            flex-shrink: 0;
        }
        
        .hero-visual {
            position: relative;
        }
        
        .dashboard-preview {
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-light);
            transform: perspective(1000px) rotateY(-10deg);
            transition: var(--transition);
        }
        
        .dashboard-preview:hover {
            transform: perspective(1000px) rotateY(0deg);
        }
        
        [data-theme="dark"] .dashboard-preview {
            background: #1e293b;
            border: 1px solid #334155;
            color: #e2e8f0;
        }
        
        /* Section Styles */
        .section {
            padding: 100px 0;
        }
        
        .section-header {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 4rem;
        }
        
        .section-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .section-subtitle {
            font-size: 1.125rem;
            color: var(--gray);
        }
        
        [data-theme="dark"] .section-subtitle {
            color: #94a3b8;
        }
        
        /* Problems Section */
        .problems {
            background: var(--gray-light);
            padding: 100px 0;
        }
        
        [data-theme="dark"] .problems {
            background: #111827;
        }
        
        .problems-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }
        
        .problem-card {
            background: var(--light);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .problem-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        [data-theme="dark"] .problem-card {
            background: #1e293b;
            border: 1px solid #334155;
            color: #e2e8f0;
        }
        
        .problem-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
        }
        
        .problem-icon-1 {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .problem-icon-2 {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .problem-icon-3 {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        
        /* Solutions Section */
        .solutions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }
        
        .solution-card {
            background: var(--light);
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            transition: var(--transition);
        }
        
        .solution-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        [data-theme="dark"] .solution-card {
            background: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }
        
        .solution-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            background: var(--primary);
            color: white;
        }
        
        /* Pricing Section */
        .pricing {
            background: var(--gray-light);
            padding: 100px 0;
        }
        
        [data-theme="dark"] .pricing {
            background: #111827;
        }
        
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }
        
        .pricing-card {
            background: var(--light);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }
        
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }
        
        .pricing-card.featured {
            border: 2px solid var(--primary);
            transform: scale(1.05);
        }
        
        [data-theme="dark"] .pricing-card {
            background: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }
        
        [data-theme="dark"] .pricing-card.featured {
            border-color: var(--primary-light);
            background: linear-gradient(135deg, #1e293b 0%, #2d3748 100%);
        }
        
        .featured-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary);
            color: white;
            padding: 0.25rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        [data-theme="dark"] .featured-badge {
            background: var(--primary-light);
        }
        
        .pricing-header {
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .pricing-price {
            margin: 1.5rem 0;
        }
        
        .price-amount {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }
        
        [data-theme="dark"] .price-amount {
            color: var(--primary-light);
        }
        
        .price-period {
            color: var(--gray);
            font-size: 0.875rem;
        }
        
        .pricing-features {
            padding: 2rem;
            list-style: none;
        }
        
        .pricing-features li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .pricing-features li svg {
            color: var(--success);
            flex-shrink: 0;
        }
        
        .pricing-footer {
            padding: 0 2rem 2rem;
        }
        
        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        [data-theme="dark"] .cta {
            background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
        }
        
        .cta-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .cta-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .cta-description {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        /* Botones CTA */
        .btn-white {
            background: white;
            color: var(--primary);
        }

        .btn-white:hover {
            background: #f1f5f9;
            color: var(--primary-dark);
        }

        .btn-outline-white {
            background: transparent;
            color: white;
            border-color: white;
        }

        .btn-outline-white:hover {
            background: white;
            color: var(--primary);
        }
        
        /* Footer */
        .footer {
            background: var(--darker);
            color: var(--gray);
            padding: 80px 0 40px;
        }
        
        [data-theme="dark"] .footer {
            background: #111827;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .footer-brand p {
            margin-top: 1rem;
            color: var(--gray);
        }
        
        .footer-column h4 {
            color: var(--light);
            font-size: 1.125rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column li {
            margin-bottom: 0.75rem;
        }
        
        .footer-column a {
            color: var(--gray);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-column a:hover {
            color: var(--primary-light);
        }
        
        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid var(--gray-dark);
            text-align: center;
            font-size: 0.875rem;
        }
        
        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
        }
        
        .hamburger-line {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--dark);
            margin: 4px 0;
            transition: var(--transition);
        }
        
        [data-theme="dark"] .hamburger-line {
            background: var(--light);
        }
        
        /* Mobile Menu Active State - AQUÍ ESTÁ LA SOLUCIÓN */
        .nav.active {
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 80px;
            left: 0;
            width: 100%;
            background: var(--light);
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideDown 0.3s ease;
        }
        
        [data-theme="dark"] .nav.active {
            background: #0f172a;
            border: 1px solid #334155;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .nav.active .nav-link {
            padding: 0.75rem 0;
            font-size: 1.1rem;
        }

        .nav.active .btn {
            margin-top: 1rem;
            width: 100%;
            justify-content: center;
        }

        /* Hamburger Animation */
        .mobile-menu-toggle.active .hamburger-line:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }

        .mobile-menu-toggle.active .hamburger-line:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-toggle.active .hamburger-line:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }
        
        /* Theme Toggle - CORREGIDO */
        .theme-toggle {
            background: none;
            border: none;
            color: var(--dark);
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            width: 44px;
            height: 44px;
            position: relative;
            background: var(--gray-light);
            border: 1px solid var(--gray-light);
        }
        
        .theme-toggle:hover {
            background: var(--primary);
            color: white;
            transform: rotate(15deg);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        [data-theme="dark"] .theme-toggle {
            background: #334155;
            color: var(--light);
            border-color: #475569;
        }
        
        [data-theme="dark"] .theme-toggle:hover {
            background: var(--primary-light);
            color: white;
        }
        
        .sun-icon, .moon-icon {
            width: 20px;
            height: 20px;
            transition: all 0.5s ease;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .sun-icon { 
            opacity: 0;
            color: yellow;
            transform: translate(-50%, -50%) rotate(90deg);
        }
        
        .moon-icon { 
            opacity: 1;
            color: black;
            transform: translate(-50%, -50%) rotate(0deg);
        }
        
        [data-theme="dark"] .sun-icon { 
            opacity: 1;
            transform: translate(-50%, -50%) rotate(0deg);
        }
        
        [data-theme="dark"] .moon-icon { 
            opacity: 0;
            transform: translate(-50%, -50%) rotate(-90deg);
        }
        
        /* Indicador visual del tema actual */
        .theme-toggle::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--primary);
            opacity: 0;
            transition: var(--transition);
        }

        [data-theme="dark"] .theme-toggle::after {
            background: var(--primary-light);
            opacity: 1;
        }
        
        /* Scroll to Top */
        .scroll-top-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: translateY(20px);
            transition: var(--transition);
            z-index: 999;
            box-shadow: var(--shadow-lg);
        }
        
        .scroll-top-btn.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .scroll-top-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-5px);
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .hero-grid,
            .solutions-grid,
            .pricing-grid {
                grid-template-columns: 1fr;
            }
            
            .hero-features,
            .problems-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .nav {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
            
            .hero-features,
            .problems-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
            }
            
            /* Botón del tema en móvil */
            .nav:not(.active) .theme-toggle {
                display: none;
            }
            
            .nav.active .theme-toggle {
                align-self: center;
                margin-top: 1.5rem;
                width: 50px;
                height: 50px;
            }
        }
    </style>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="container header-content">
            <a href="/" class="logo">
                <img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="Portafolio ERP">
                <span class="logo-text">PORTAFOLIO ERP</span>
            </a>
            
            <nav class="nav">
                <a href="#inicio" class="nav-link">Inicio</a>
                <a href="#soluciones" class="nav-link">Soluciones</a>
                <a href="#modulos" class="nav-link">Módulos</a>
                <a href="#precios" class="nav-link">Precios</a>
                <a href="#contacto" class="nav-link">Contacto</a>
                <a href="https://app.portafolioerp.com/login" class="btn btn-outline">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </a>
                <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema" title="Cambiar tema claro/oscuro">
                    <i class="fas fa-sun sun-icon"></i>
                    <i class="fas fa-moon moon-icon"></i>
                </button>
            </nav>
            
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>

    <!-- Scroll to Top Button -->
    <button class="scroll-top-btn" id="scrollTopBtn">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Hero Section -->
    <section id="inicio" class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <i class="fas fa-shield-alt"></i>
                        Certificado DIAN - 100% Colombiano
                    </div>
                    
                    <h1 class="hero-title">
                        Software ERP que <span class="highlight">elimina las sanciones de la DIAN</span> y automatiza tu empresa
                    </h1>
                    
                    <p class="hero-description">
                        Contabilidad, facturación electrónica, nómina y POS integrados en una sola plataforma 100% web. 
                        Diseñado específicamente para el mercado colombiano. Sin instalaciones, sin complicaciones.
                    </p>
                    
                    <div class="hero-buttons">
                        <a href="https://wa.me/573207141104?text=Hola,%20quiero%20una%20demo%20de%20Portafolio%20ERP" 
                           target="_blank" 
                           class="btn btn-primary btn-lg">
                            <i class="fab fa-whatsapp"></i> Solicitar Demo Gratis
                        </a>
                        
                        <a href="#precios" class="btn btn-outline btn-lg">
                            <i class="fas fa-eye"></i> Ver Planes
                        </a>
                    </div>
                    
                    <div class="hero-features">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Facturación Electrónica DIAN</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>100% Web - Sin instalación</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Soporte 24/7 Colombia</span>
                        </div>
                    </div>
                </div>
                
                <div class="hero-visual">
                    <div class="dashboard-preview">
                        <div class="dashboard-header">
                            <h3><i class="fas fa-chart-line"></i> Dashboard Ejecutivo</h3>
                            <div style="display: flex; gap: 1rem; margin: 1.5rem 0;">
                                <div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">$8.4M</div>
                                    <div style="font-size: 0.875rem; color: var(--gray);">Ingresos mensuales</div>
                                </div>
                                <div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">1,247</div>
                                    <div style="font-size: 0.875rem; color: var(--gray);">Facturas DIAN</div>
                                </div>
                            </div>
                        </div>
                        <div style="background: linear-gradient(135deg, var(--gray-light) 0%, var(--light) 100%); height: 180px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--gray);">
                            <i class="fas fa-chart-bar" style="font-size: 2rem; margin-right: 1rem;"></i>
                            Dashboard interactivo en tiempo real
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problems Section -->
    <section id="soluciones" class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">¿Problemas con tu contabilidad actual?</h2>
                <p class="section-subtitle">Estos son los principales problemas que resolvemos para empresas colombianas</p>
            </div>
            
            <div class="problems-grid">
                <div class="problem-card">
                    <div class="problem-icon problem-icon-1">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Sanciones DIAN por errores</h3>
                    <p>Facturas electrónicas rechazadas, reportes incorrectos y multas que afectan tu flujo de caja y reputación fiscal.</p>
                </div>
                
                <div class="problem-card">
                    <div class="problem-icon problem-icon-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Procesos manuales lentos</h3>
                    <p>Hojas de cálculo interminables, datos duplicados y horas perdidas en tareas repetitivas que podrían automatizarse.</p>
                </div>
                
                <div class="problem-card">
                    <div class="problem-icon problem-icon-3">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Falta de visibilidad real</h3>
                    <p>Decisiones a ciegas porque no tienes reportes actualizados ni dashboard en tiempo real de tu negocio.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Solutions Section -->
    <section id="modulos" class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">La solución completa para tu empresa</h2>
                <p class="section-subtitle">Todo integrado en una sola plataforma poderosa y simple de usar</p>
            </div>
            
            <div class="solutions-grid">
                <div class="solution-card">
                    <div class="solution-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h3>Facturación Electrónica DIAN</h3>
                    <p>Emisión automática de facturas electrónicas, notas crédito/débito, documentos equivalentes y validación en tiempo real con la DIAN.</p>
                    <ul style="margin-top: 1rem; list-style: none;">
                        <li><i class="fas fa-check text-success"></i> Validación automática DIAN</li>
                        <li><i class="fas fa-check text-success"></i> Resoluciones automáticas</li>
                        <li><i class="fas fa-check text-success"></i> Envío automático al cliente</li>
                    </ul>
                </div>
                
                <div class="solution-card">
                    <div class="solution-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h3>Contabilidad Automatizada</h3>
                    <p>Sistema contable completo con Plan de Cuentas PUC, estados financieros automáticos, conciliación bancaria y medios magnéticos.</p>
                    <ul style="margin-top: 1rem; list-style: none;">
                        <li><i class="fas fa-check text-success"></i> Plan de Cuentas PUC actualizado</li>
                        <li><i class="fas fa-check text-success"></i> Estados financieros automáticos</li>
                        <li><i class="fas fa-check text-success"></i> Conciliación bancaria</li>
                    </ul>
                </div>
                
                <div class="solution-card">
                    <div class="solution-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Nómina Electrónica</h3>
                    <p>Gestión completa de nómina electrónica, prestaciones sociales, reportes PILA y control de talento humano totalmente integrado.</p>
                    <ul style="margin-top: 1rem; list-style: none;">
                        <li><i class="fas fa-check text-success"></i> Nómina electrónica DIAN</li>
                        <li><i class="fas fa-check text-success"></i> Prestaciones automáticas</li>
                        <li><i class="fas fa-check text-success"></i> Reportes ministeriales</li>
                    </ul>
                </div>
                
                <div class="solution-card">
                    <div class="solution-icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h3>Punto de Venta (POS)</h3>
                    <p>Sistema de ventas integrado con inventario, caja y facturación en tiempo real. Perfecto para retail y servicios.</p>
                    <ul style="margin-top: 1rem; list-style: none;">
                        <li><i class="fas fa-check text-success"></i> Control de inventario</li>
                        <li><i class="fas fa-check text-success"></i> Múltiples formas de pago</li>
                        <li><i class="fas fa-check text-success"></i> Integración contable</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="precios" class="pricing">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Planes diseñados para cada negocio</h2>
                <p class="section-subtitle">Elige el plan perfecto. Todos incluyen facturación electrónica DIAN</p>
            </div>
            
            <div class="pricing-grid">
                <!-- Plan Básico -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Básico</h3>
                        <p>Para pequeñas empresas</p>
                        <div class="pricing-price">
                            <div class="price-amount">$50.000</div>
                            <div class="price-period">/mes + IVA</div>
                        </div>
                    </div>

                    <ul class="pricing-features">
                        <li><i class="fas fa-check" style="color: var(--success);"></i><b>Facturación Electrónica incluida</b></li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Hasta 30 facturas electrónicas mensuales</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Hasta 500 facturas POS mensuales</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Módulo POS integrado</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Hasta 1 puntos de venta</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Hasta 1 bodegas de inventario</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Nómina para 1 a 5 empleados</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Manejo de AIU</li>
                    </ul>
                    
                    <div class="pricing-footer">
                        <a href="https://wa.me/573207141104?text=Hola,%20quiero%20el%20plan%20Básico" 
                           target="_blank" 
                           class="btn btn-outline btn-block">
                            <i class="fab fa-whatsapp"></i> Solicitar
                        </a>
                    </div>
                </div>
                
                <!-- Plan Profesional (Featured) -->
                <div class="pricing-card featured">
                    <div class="featured-badge">MÁS POPULAR</div>
                    <div class="pricing-header">
                        <h3>Profesional</h3>
                        <p>Para empresas en crecimiento</p>
                        <div class="pricing-price">
                            <div class="price-amount">$100.000</div>
                            <div class="price-period">/mes + IVA</div>
                        </div>
                    </div>
                    
                    <ul class="pricing-features">
                        <li><i class="fas fa-check" style="color: var(--success);"></i><b>Facturación Electrónica incluida</b></li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Hasta 300 facturas electrónicas mensuales</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Hasta 2.000 facturas POS mensuales</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Módulo POS integrado</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Hasta 2 puntos de venta</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Hasta 2 bodegas de inventario</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Nómina para 5 a 15 empleados</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Manejo de AIU</li>
                    </ul>
                    
                    <div class="pricing-footer">
                        <a href="https://wa.me/573207141104?text=Hola,%20quiero%20el%20plan%20Profesional" 
                           target="_blank" 
                           class="btn btn-primary btn-block">
                            <i class="fab fa-whatsapp"></i> Comenzar Prueba
                        </a>
                    </div>
                </div>
                
                <!-- Plan Empresarial -->
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h3>Empresarial</h3>
                        <p>Para empresas consolidadas</p>
                        <div class="pricing-price">
                            <div class="price-amount">$300.000</div>
                            <div class="price-period">/mes + IVA</div>
                        </div>
                    </div>

                    <ul class="pricing-features">
                        <li><i class="fas fa-check" style="color: var(--success);"></i><b>Facturación Electrónica incluida</b></li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Facturas electrónicas Ilimitadas</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Facturas POS Ilimitadas</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Módulo POS integrado</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Hasta 3 puntos de venta</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Hasta 3 bodegas de inventario</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Nómina Ilimitadas</li>
                        <li><i class="fas fa-check" style="color: var(--success);"></i>Manejo de AIU</li>
                    </ul>
                    
                    <div class="pricing-footer">
                        <a href="https://wa.me/573207141104?text=Hola,%20quiero%20el%20plan%20Empresarial" 
                           target="_blank" 
                           class="btn btn-outline btn-block">
                            <i class="fas fa-phone"></i> Contactar Ventas
                        </a>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 3rem; color: var(--gray);">
                <p><i class="fas fa-info-circle"></i> Todos los planes incluyen certificado digital DIAN y actualizaciones automáticas.</p>
                <p>¿Necesitas un plan personalizado? <a href="https://wa.me/573207141104" target="_blank" style="color: var(--primary);">Contáctanos</a></p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="contacto" class="section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">¿Listo para transformar tu empresa?</h2>
                <p class="cta-description">
                    Únete a las empresas colombianas que ya automatizaron sus procesos 
                    y cumplen sin estrés con la DIAN
                </p>
                
                <div style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 2rem;">
                    <a href="https://wa.me/573207141104?text=Hola,%20quiero%20comenzar%20con%20Portafolio%20ERP" 
                       target="_blank" 
                       class="btn btn-white btn-lg">
                        <i class="fab fa-whatsapp"></i> Comenzar Gratis 15 Días
                    </a>
                    
                    <a href="#contacto" class="btn btn-outline-white btn-lg">
                        <i class="fas fa-calendar-alt"></i> Agendar Demo
                    </a>
                </div>
                
                <div style="opacity: 0.8; font-size: 0.9rem;">
                    <p><i class="fas fa-check"></i> Sin tarjeta de crédito • Sin compromisos • Implementación en 48h</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="contacto" class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="logo">
                        <img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="Portafolio ERP">
                        <span class="logo-text">PORTAFOLIO ERP</span>
                    </a>
                    <p>El software ERP diseñado específicamente para empresas colombianas. Cumplimiento DIAN garantizado.</p>
                    
                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                        <a href="https://facebook.com" target="_blank" style="color: var(--gray);">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://linkedin.com" target="_blank" style="color: var(--gray);">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" style="color: var(--gray);">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://youtube.com" target="_blank" style="color: var(--gray);">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h4>Producto</h4>
                    <ul>
                        <li><a href="#soluciones">Facturación Electrónica</a></li>
                        <li><a href="#soluciones">Contabilidad</a></li>
                        <li><a href="#soluciones">Nómina</a></li>
                        <li><a href="#soluciones">Punto de Venta</a></li>
                        <li><a href="https://pos.portafolioerp.com" target="_blank">Módulo POS</a></li>
                        <li><a href="https://maximoph.co" target="_blank">Maximoph PH</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Empresa</h4>
                    <ul>
                        <li><a href="#precios">Precios</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                        <li><a href="#">Soporte</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Términos</a></li>
                        <li><a href="#">Privacidad</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Contacto</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> +57 320 714 1104</li>
                        <li><i class="fas fa-envelope"></i> portafolioerp@gmail.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Medellín, Colombia</li>
                        <li><i class="fab fa-whatsapp"></i> <a href="https://wa.me/573207141104" target="_blank">WhatsApp Business</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Portafolio ERP. Todos los derechos reservados.</p>
                <p style="margin-top: 0.5rem; font-size: 0.875rem;">
                    Software ERP colombiano - Certificado DIAN - Cumplimiento normativo 100%
                </p>
            </div>
        </div>
    </section>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        function initTheme() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = detectSystemTheme();
            
            if (savedTheme) {
                applyTheme(savedTheme);
            } else {
                applyTheme(systemTheme);
            }
        }

        function detectSystemTheme() {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                return 'dark';
            }
            return 'light';
        }

        // Aplicar tema con transición suave
        function applyTheme(theme) {
            // Deshabilitar transiciones momentáneamente para evitar parpadeo
            html.style.transition = 'none';
            html.setAttribute('data-theme', theme);
            
            // Forzar reflow
            html.offsetHeight;
            
            // Rehabilitar transiciones
            html.style.transition = '';
            
            // Guardar en localStorage
            localStorage.setItem('theme', theme);
            
            // Actualizar metatag para tema (opcional pero recomendado)
            updateThemeMeta(theme);
        }
                
        function toggleTheme() {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            // Agregar clase para animación
            html.classList.add('theme-changing');
            
            // Cambiar tema
            applyTheme(newTheme);
            
            // Efecto visual en el botón
            themeToggle.style.transform = 'scale(0.9)';
            setTimeout(() => {
                themeToggle.style.transform = '';
            }, 150);
            
            // Quitar clase después de la animación
            setTimeout(() => {
                html.classList.remove('theme-changing');
            }, 500);
        }

        function updateThemeMeta(theme) {
            const themeColor = theme === 'dark' ? '#0f172a' : '#ffffff';
            const themeMeta = document.querySelector('meta[name="theme-color"]');
            
            if (themeMeta) {
                themeMeta.content = themeColor;
            } else {
                const meta = document.createElement('meta');
                meta.name = 'theme-color';
                meta.content = themeColor;
                document.head.appendChild(meta);
            }
        }

        if (window.matchMedia) {
            const colorSchemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
            colorSchemeQuery.addEventListener('change', (e) => {
                // Solo cambiar si el usuario no ha guardado una preferencia
                if (!localStorage.getItem('theme')) {
                    const newTheme = e.matches ? 'dark' : 'light';
                    applyTheme(newTheme);
                }
            });
        }
        
        // Header Scroll Effect
        const header = document.getElementById('header');
        
        function handleScroll() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
        
        // Mobile Menu Toggle - CORREGIDO
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const nav = document.querySelector('.nav');
        
        function toggleMobileMenu() {
            nav.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
            
            // Prevenir scroll del body cuando el menú está abierto
            if (nav.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        // Cerrar menú al hacer clic en un enlace
        function closeMobileMenu() {
            nav.classList.remove('active');
            mobileMenuToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Scroll to Top Button
        const scrollTopBtn = document.getElementById('scrollTopBtn');
        
        function handleScrollTop() {
            if (window.scrollY > 300) {
                scrollTopBtn.classList.add('visible');
            } else {
                scrollTopBtn.classList.remove('visible');
            }
        }
        
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        // Smooth Scroll for Anchor Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                // Solo prevenir default si es un enlace interno
                if (targetId.startsWith('#')) {
                    e.preventDefault();
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        const headerHeight = header.offsetHeight;
                        const targetPosition = targetElement.offsetTop - headerHeight;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                        
                        // Cerrar menú móvil
                        closeMobileMenu();
                    }
                }
            });
        });
        
        // Active Navigation on Scroll
        function updateActiveNav() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-link');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                const sectionHeight = section.clientHeight;
                if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        }
        
        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', (e) => {
            const isClickInsideNav = nav.contains(e.target);
            const isClickOnToggle = mobileMenuToggle.contains(e.target);
            
            if (!isClickInsideNav && !isClickOnToggle && nav.classList.contains('active')) {
                closeMobileMenu();
            }
        });
        
        // Cerrar menú al redimensionar ventana (si se hace más grande)
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768 && nav.classList.contains('active')) {
                closeMobileMenu();
            }
        });
        
        // Initialize Everything
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar tema primero
            initTheme();
            
            // Agregar transiciones suaves para elementos específicos
            const style = document.createElement('style');
            style.textContent = `
                .theme-changing * {
                    transition: background-color 0.5s ease, 
                                border-color 0.5s ease, 
                                color 0.5s ease,
                                transform 0.5s ease !important;
                }
            `;
            document.head.appendChild(style);
            
            // Evento para el botón del tema
            themeToggle.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevenir que se cierre el menú móvil
                toggleTheme();
            });
            
            // ¡¡¡FALTA ESTO!!! Evento para el botón hamburguesa
            mobileMenuToggle.addEventListener('click', (e) => {
                e.stopPropagation(); // Importante para que no se cierre inmediatamente
                toggleMobileMenu();
            });
            
            // Resto de tu código de inicialización...
            handleScroll();
            handleScrollTop();
            updateActiveNav();
            
            // Escuchar scroll
            window.addEventListener('scroll', () => {
                handleScroll();
                handleScrollTop();
                updateActiveNav();
            });
        });
    </script>
</body>
</html>