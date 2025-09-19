<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Under Maintenance | IMARA LIMS</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Design Icons -->
    <link href="https://cdn.materialdesignicons.com/7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #b52d3c 0%, #8b1f2c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .particle:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 120px; height: 120px; left: 20%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 60px; height: 60px; left: 35%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 100px; height: 100px; left: 50%; animation-delay: 1s; }
        .particle:nth-child(5) { width: 70px; height: 70px; left: 70%; animation-delay: 3s; }
        .particle:nth-child(6) { width: 90px; height: 90px; left: 85%; animation-delay: 5s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.3; }
            50% { transform: translateY(-100px) rotate(180deg); opacity: 0.8; }
        }
        
        .maintenance-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 90%;
            position: relative;
            z-index: 2;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .logo-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 15px;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: #b52d3c;
            margin: 0;
            letter-spacing: 1px;
        }

        .logo-subtitle {
            font-size: 0.9rem;
            color: #666;
            margin: 5px 0 0 0;
            font-weight: 500;
        }
        
        .maintenance-icon {
            font-size: 120px;
            color: #b52d3c;
            margin-bottom: 30px;
            animation: pulse 2s ease-in-out infinite;
            position: relative;
        }

        .animated-cogs {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            pointer-events: none;
        }

        .cog {
            position: absolute;
            color: #7cb342;
            opacity: 0.4;
            filter: drop-shadow(0 0 8px rgba(124, 179, 66, 0.3));
            transition: all 0.3s ease;
        }

        .cog:hover {
            opacity: 0.7;
            transform: scale(1.1);
        }

        .cog-1 {
            font-size: 40px;
            top: 20px;
            left: 20px;
            animation: rotate-clockwise 3s linear infinite;
        }

        .cog-2 {
            font-size: 30px;
            top: 60px;
            right: 30px;
            animation: rotate-counter-clockwise 2.5s linear infinite;
        }

        .cog-3 {
            font-size: 25px;
            bottom: 40px;
            left: 50px;
            animation: rotate-clockwise 2s linear infinite;
        }

        .cog-4 {
            font-size: 35px;
            bottom: 20px;
            right: 20px;
            animation: rotate-counter-clockwise 3.5s linear infinite;
        }

        @keyframes rotate-clockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes rotate-counter-clockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(-360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .maintenance-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .maintenance-subtitle {
            font-size: 1.2rem;
            color: #b52d3c;
            font-weight: 500;
            margin-bottom: 30px;
        }
        
        .maintenance-message {
            font-size: 1.1rem;
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .progress-container {
            background: #e2e8f0;
            border-radius: 25px;
            height: 8px;
            margin: 30px 0;
            overflow: hidden;
            position: relative;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #b52d3c, #8b1f2c);
            height: 100%;
            border-radius: 25px;
            width: 0%;
            animation: progress 4s ease-in-out infinite;
            position: relative;
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            width: 20px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4));
            animation: shimmer 1.5s ease-in-out infinite;
        }
        
        @keyframes progress {
            0% { width: 0%; }
            50% { width: 75%; }
            100% { width: 100%; }
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .status-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f7fafc;
            padding: 20px;
            border-radius: 12px;
            margin-top: 30px;
            border-left: 4px solid #b52d3c;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-icon {
            font-size: 20px;
            color: #b52d3c;
        }
        
        .status-text {
            font-size: 0.9rem;
            color: #4a5568;
            font-weight: 500;
        }
        
        .contact-info {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
        }
        
        .contact-text {
            color: #718096;
            font-size: 0.95rem;
            margin-bottom: 15px;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #b52d3c, #8b1f2c);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-size: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(181, 45, 60, 0.3);
        }
        
        .social-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(181, 45, 60, 0.4);
        }
        
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .maintenance-container {
                padding: 40px 20px;
                margin: 20px;
            }
            
            .maintenance-title {
                font-size: 2rem;
            }
            
            .maintenance-icon {
                font-size: 80px;
            }
            
            .status-info {
                flex-direction: column;
                gap: 15px;
                text-align: left;
            }
            
            .social-links {
                gap: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .maintenance-title {
                font-size: 1.8rem;
            }
            
            .maintenance-subtitle {
                font-size: 1.1rem;
            }
            
            .maintenance-message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animated background particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="maintenance-container">
        <div class="logo-section">
            <img src="{{ asset('images/imara-sys.png') }}" alt="IMARA LIMS" class="logo">
            <p class="logo-subtitle">Laboratory Information Management System</p>
        </div>
        
        <div class="maintenance-icon">
            <i class="mdi mdi-flask" style="color: #7cb342;"></i>
            <div class="animated-cogs">
                <i class="mdi mdi-cog cog cog-1"></i>
                <i class="mdi mdi-cog cog cog-2"></i>
                <i class="mdi mdi-cog cog cog-3"></i>
                <i class="mdi mdi-cog cog cog-4"></i>
            </div>
        </div>
        
        <h1 class="maintenance-title">System Under Maintenance</h1>
        <p class="maintenance-subtitle">We're Making Things Better</p>
        
        <p class="maintenance-message">
            We are currently performing scheduled maintenance to improve your experience. Our team is working hard to get everything back online as quickly as possible.
        </p>
        
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        
        <div class="status-info">
            <div class="status-item">
                <i class="mdi mdi-shield-check status-icon"></i>
                <span class="status-text">Data is Safe</span>
            </div>
            <div class="status-item">
                <i class="mdi mdi-update status-icon"></i>
                <span class="status-text">System Upgrade</span>
            </div>
            <div class="status-item">
                <i class="mdi mdi-speedometer status-icon"></i>
                <span class="status-text">Performance Boost</span>
            </div>
        </div>
        
        <div class="contact-info">
            <p class="contact-text">
                Need immediate assistance? Contact our support team
            </p>
            <div class="social-links">
                <a href="mailto:support@imaralims.com" class="social-link" title="Email Support">
                    <i class="mdi mdi-email"></i>
                </a>
                <a href="tel:+1234567890" class="social-link" title="Call Support">
                    <i class="mdi mdi-phone"></i>
                </a>
                <a href="#" class="social-link" title="Live Chat">
                    <i class="mdi mdi-message-text"></i>
                </a>
                <a href="#" class="social-link" title="Status Page">
                    <i class="mdi mdi-web"></i>
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh the page every 2 minutes to check if maintenance is over
        setTimeout(function() {
            window.location.reload();
        }, 120000);
        
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate the progress bar on load
            const progressBar = document.querySelector('.progress-bar');
            setTimeout(() => {
                progressBar.style.animationPlayState = 'running';
            }, 500);
            
            // Add hover effect to the main container
            const container = document.querySelector('.maintenance-container');
            container.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 35px 60px rgba(0, 0, 0, 0.3)';
            });
            
            container.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 25px 50px rgba(0, 0, 0, 0.2)';
            });
        });
    </script>
</body>
</html>
