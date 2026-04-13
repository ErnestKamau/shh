@extends('layouts.app')

@section('title')
<title>LIMS HOME - Professional Dashboard</title>
<link rel="stylesheet" href="{{ asset('css/default.css') }}">
<link rel="stylesheet" href="{{ asset('css/w3.css') }}">
<style>
    /* Professional Landing Page Styles */
    .landing-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background-image: url('/images/bg-il.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        overflow: hidden;
        z-index: 1000;
    }

    .landing-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.6) 0%, rgba(118, 75, 162, 0.6) 100%);
        z-index: 1;
    }

    .animated-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.08) 0%, transparent 50%);
        animation: backgroundShift 20s ease-in-out infinite;
        z-index: 2;
    }

    @keyframes backgroundShift {
        0%, 100% { transform: translateX(0) translateY(0) rotate(0deg); }
        33% { transform: translateX(-30px) translateY(-30px) rotate(1deg); }
        66% { transform: translateX(30px) translateY(30px) rotate(-1deg); }
    }

    .floating-particles {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 3;
    }

    .particle {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
        50% { transform: translateY(-20px) rotate(180deg); opacity: 1; }
    }

    .welcome-section {
        position: relative;
        z-index: 10;
        text-align: center;
        padding: 4rem 0 2rem 0;
        animation: fadeInUp 1s ease-out;
        margin-top: 60px; /* Account for navbar height */
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

    .company-logo {
        width: 120px;
        height: 120px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        animation: logoFloat 3s ease-in-out infinite;
        transition: all 0.3s ease;
    }

    .company-logo:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
    }

    @keyframes logoFloat {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .company-logo img {
        width: 80%;
        height: 80%;
        object-fit: contain;
        border-radius: 50%;
    }

    .welcome-text {
        color: white;
        font-size: 2.5rem;
        font-weight: 300;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        animation: slideInFromLeft 1s ease-out 0.5s both;
    }

    .welcome-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 1.2rem;
        font-weight: 300;
        animation: slideInFromRight 1s ease-out 0.7s both;
    }

    @keyframes slideInFromLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInFromRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .apps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem 2rem 2rem;
        animation: fadeInUp 1s ease-out 1s both;
        position: relative;
        z-index: 10;
    }

    .app-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        padding: 1.5rem;
        text-align: center;
        text-decoration: none;
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        z-index: 10;
    }

    .app-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.6s ease;
    }

    .app-card:hover::before {
        left: 100%;
    }

    .app-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.25);
    }

    .app-card:hover .app-title {
        color: white !important;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }

    .app-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 0.8rem;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .app-card:hover .app-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .app-icon::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }

    .app-card:hover .app-icon::after {
        transform: translateX(100%);
    }

    .app-title {
        font-size: 1rem;
        font-weight: 500;
        margin: 0;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    .user-menu {
        position: absolute;
        top: 1rem;
        right: 2rem;
        z-index: 20;
    }

    .user-button {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 50px;
        padding: 0.75rem 1.5rem;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .user-button:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.05);
        color: white;
        text-decoration: none;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('/images/bg-il.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        transition: opacity 0.5s ease;
    }

    .loading-overlay::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.6) 0%, rgba(118, 75, 162, 0.6) 100%);
        z-index: 1;
    }

    .loading-overlay .text-center {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .loading-spinner {
        width: 80px;
        height: 80px;
        border: 6px solid rgba(255, 255, 255, 0.3);
        border-top: 6px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1.5rem auto;
        display: block;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-text {
        color: white;
        margin: 0;
        font-size: 1.2rem;
        font-weight: 500;
        text-align: center;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 0.7; }
        50% { opacity: 1; }
    }

    /* Responsive Design */
    @media (max-width: 991px) and (min-width: 769px) {
        .apps-grid {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.2rem;
            padding: 0 1.5rem 1.5rem 1.5rem;
        }
        
        .app-card {
            padding: 1.2rem;
        }
        
        .app-icon {
            width: 50px;
            height: 50px;
            font-size: 1.8rem;
        }
        
        .app-title {
            font-size: 0.9rem;
        }
        
        .company-logo {
            width: 100px;
            height: 100px;
        }
        
        .welcome-text {
            font-size: 2.2rem;
        }
        
        .welcome-subtitle {
            font-size: 1.1rem;
        }
    }

    @media (max-width: 768px) {
        .apps-grid {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            padding: 0 1rem;
        }
        
        .app-card {
            padding: 1.2rem;
        }
        
        .app-icon {
            width: 45px;
            height: 45px;
            font-size: 1.6rem;
        }
        
        .app-title {
            font-size: 0.85rem;
        }
        
        .welcome-text {
            font-size: 1.8rem;
        }
        
        .company-logo {
            width: 80px;
            height: 80px;
        }
    }

    /* Color variations for different app types */
    .app-card.lab { background: linear-gradient(135deg, rgba(76, 175, 80, 0.2), rgba(76, 175, 80, 0.1)); }
    .app-card.inventory { background: linear-gradient(135deg, rgba(33, 150, 243, 0.2), rgba(33, 150, 243, 0.1)); }
    .app-card.equipment { background: linear-gradient(135deg, rgba(121, 85, 72, 0.2), rgba(121, 85, 72, 0.1)); }
    .app-card.crm { background: linear-gradient(135deg, rgba(0, 188, 212, 0.2), rgba(0, 188, 212, 0.1)); }
    .app-card.personnel { background: linear-gradient(135deg, rgba(244, 67, 54, 0.2), rgba(244, 67, 54, 0.1)); }
    .app-card.calendar { background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(255, 193, 7, 0.1)); }
    .app-card.matrix { background: linear-gradient(135deg, rgba(158, 158, 158, 0.2), rgba(158, 158, 158, 0.1)); }
    .app-card.ai { background: linear-gradient(135deg, rgba(255, 152, 0, 0.2), rgba(255, 152, 0, 0.1)); }
    .app-card.audit { background: linear-gradient(135deg, rgba(156, 39, 176, 0.2), rgba(156, 39, 176, 0.1)); }
    .app-card.risk { background: linear-gradient(135deg, rgba(244, 67, 54, 0.2), rgba(244, 67, 54, 0.1)); }
    .app-card.settings { background: linear-gradient(135deg, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.1)); }
    .app-card.dms { background: linear-gradient(135deg, rgba(103, 58, 183, 0.2), rgba(103, 58, 183, 0.1)); }
</style>
@endsection

@section('content')
<div class="loading-overlay" id="loadingOverlay">
    <div class="text-center">
        <div class="loading-spinner"></div>
        <div class="loading-text">Loading Dashboard...</div>
    </div>
</div>

<div class="landing-container" id="landingContainer" style="display: none;">
    <div class="animated-background"></div>
    <div class="floating-particles" id="particlesContainer"></div>
    
    <div class="user-menu">
        <div class="dropdown">
            <button type="button" class="user-button dropdown-toggle" data-toggle="dropdown">
                <i class="mdi mdi-account-circle"></i> {{ Auth::user()->name }}
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="width: 200px">
                <a class="dropdown-item" href="/system-user/{{ Auth::user()->id }}">
                    <i class="mdi mdi-account-details text-primary"></i> &nbsp;&nbsp;My Profile
                </a>
                <a class="dropdown-item" href="http://127.0.0.1:8000/logout"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="text-danger mdi mdi-power"></i> &nbsp;&nbsp;Sign-Out
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    <div class="welcome-section">
        <div class="company-logo">
            <?php $active = getActiveCompany()?>
            <img src="{{$active->logo}}" alt="Company Logo" />
        </div>
        <h1 class="welcome-text">Welcome to IMARA LIMS</h1>
        <p class="welcome-subtitle">Laboratory Information Management System</p>
    </div>

    <div class="apps-grid">
        @if(isSystemModuleVisible('laboratory'))
        <a class="app-card lab" href="/lab-dashboard" data-app="laboratory">
            <div class="app-icon" style="background: linear-gradient(135deg, #4CAF50, #45a049);">
                <i class="mdi mdi-flask"></i>
            </div>
            <h3 class="app-title">Laboratory</h3>
        </a>
        @endif

        @if(isSystemModuleVisible('inventory'))
        <a class="app-card inventory" href="/inventory-home" data-app="inventory">
            <div class="app-icon" style="background: linear-gradient(135deg, #2196F3, #1976D2);">
                <i class="mdi mdi-package-variant"></i>
            </div>
            <h3 class="app-title">Inventory</h3>
        </a>
        @endif

        @if(isSystemModuleVisible('equipment'))
        <a class="app-card equipment" href="{{ route('equipment-dashboard') }}" data-app="equipment">
            <div class="app-icon" style="background: linear-gradient(135deg, #795548, #5D4037);">
                <i class="mdi mdi-tools"></i>
            </div>
            <h3 class="app-title">Equipment</h3>
        </a>
        @endif

        @if(isSystemModuleVisible('crm'))
        <a class="app-card crm" href="{{ route('crm-dashboard') }}" data-app="crm">
            <div class="app-icon" style="background: linear-gradient(135deg, #00BCD4, #0097A7);">
                <i class="mdi mdi-account-multiple-outline"></i>
            </div>
            <h3 class="app-title">CRM</h3>
        </a>
        @endif

        @if((auth()->user()->is_support_staff || isset($user_personel_access->id)) && isSystemModuleVisible('personnel'))
        <a class="app-card personnel" href="/personnel-home" data-app="personnel">
            <div class="app-icon" style="background: linear-gradient(135deg, #F44336, #D32F2F);">
                <i class="mdi mdi-account-group"></i>
            </div>
            <h3 class="app-title">Personnel</h3>
        </a>
        @endif

        @if(isSystemModuleVisible('dms'))
        <a class="app-card dms" href="{{ route('dms.dashboard') }}" data-app="dms">
            <div class="app-icon" style="background: linear-gradient(135deg, #673AB7, #512DA8);">
                <i class="mdi mdi-file-document-multiple"></i>
            </div>
            <h3 class="app-title">Document Management</h3>
        </a>
        @endif

        @if(isSystemModuleVisible('calendar'))
        <a class="app-card calendar" href="/full-calendar/view" data-app="calendar">
            <div class="app-icon" style="background: linear-gradient(135deg, #FFC107, #FF8F00);">
                <i class="mdi mdi-calendar"></i>
            </div>
            <h3 class="app-title">System Planner</h3>
        </a>
        @endif

        @if(isSystemModuleVisible('matrix'))
        <a class="app-card matrix" href="{{route('matrix')}}" data-app="matrix">
            <div class="app-icon" style="background: linear-gradient(135deg, #9E9E9E, #616161);">
                <i class="mdi mdi-account-star-outline"></i>
            </div>
            <h3 class="app-title">Skills Matrix</h3>
        </a>
        @endif

        @if(isSystemModuleVisible('ai'))
        <a class="app-card ai" href="{{route('imara-ai-index')}}" data-app="ai">
            <div class="app-icon" style="background: linear-gradient(135deg, #FF9800, #F57C00);">
                <i class="mdi mdi-chip"></i>
            </div>
            <h3 class="app-title">Imara AI</h3>
        </a>
        @endif

        @if(isSystemModuleVisible('risk'))
        <a class="app-card risk" href="{{ route('risk.dashboard') }}" data-app="risk">
            <div class="app-icon" style="background: linear-gradient(135deg, #F44336, #D32F2F);">
                <i class="mdi mdi-alert-octagon-outline"></i>
            </div>
            <h3 class="app-title">Risk Management</h3>
        </a>
        @endif

        @if(isSystemModuleVisible('audit'))
        <a class="app-card audit" href="{{ route('audit.dashboard') }}" data-app="audit">
            <div class="app-icon" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2);">
                <i class="mdi mdi-clipboard-check-outline"></i>
            </div>
            <h3 class="app-title">Audit</h3>
        </a>
        @endif

        @if(isSystemModuleVisible('tickets'))
        <a class="app-card tickets" href="{{ route('tickets.dashboard') }}" data-app="tickets">
            <div class="app-icon" style="background: linear-gradient(135deg, #E91E63, #C2185B);">
                <i class="mdi mdi-ticket"></i>
            </div>
            <h3 class="app-title">Help Desk</h3>
        </a>
        @endif
        
        @if(auth()->user()->is_support_staff && isSystemModuleVisible('settings'))
        <a class="app-card settings" href="/system-settings" data-app="settings">
            <div class="app-icon" style="background: linear-gradient(135deg, #424242, #212121);">
                <i class="fas fa-cogs"></i>
            </div>
            <h3 class="app-title">System Settings</h3>
        </a>
        @endif
    </div>
</div>

<div class="modal fade" id="to-be-configured" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="alert alert-primary p-2 d-flex">
                    <i class="mdi mdi-alert-decagram-outline" style="font-size: 35px"></i>
                    <h5 class="p-2">This module will be enabled in <b>Phase 2</b></h5>
                </div>
            </div>
            <div class="modal-footer">
                <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the landing page
    initializeLandingPage();
    
    // Create floating particles
    createFloatingParticles();
    
    // Add app card interactions
    addAppCardInteractions();
    
    // Add smooth transitions
    addSmoothTransitions();
});

function initializeLandingPage() {
    // Hide loading overlay and show landing page
    setTimeout(() => {
        document.getElementById('loadingOverlay').style.opacity = '0';
        setTimeout(() => {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('landingContainer').style.display = 'block';
            
            // Trigger entrance animations
            triggerEntranceAnimations();
        }, 500);
    }, 2000);
}

function createFloatingParticles() {
    const particlesContainer = document.getElementById('particlesContainer');
    const particleCount = 50;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        // Random size between 2px and 6px
        const size = Math.random() * 4 + 2;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        
        // Random position
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        
        // Random animation delay
        particle.style.animationDelay = Math.random() * 6 + 's';
        particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
        
        particlesContainer.appendChild(particle);
    }
}

function addAppCardInteractions() {
    const appCards = document.querySelectorAll('.app-card');
    
    appCards.forEach((card, index) => {
        // Staggered entrance animation
        card.style.animationDelay = (index * 0.1) + 's';
        
        // Add hover sound effect (optional)
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
        
        // Add click animation
        card.addEventListener('click', function(e) {
            // Create ripple effect
            createRippleEffect(e, this);
        });
    });
}

function createRippleEffect(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.style.position = 'absolute';
    ripple.style.borderRadius = '50%';
    ripple.style.background = 'rgba(255, 255, 255, 0.3)';
    ripple.style.transform = 'scale(0)';
    ripple.style.animation = 'ripple 0.6s linear';
    ripple.style.pointerEvents = 'none';
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

function addSmoothTransitions() {
    // Add CSS for ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .app-card {
            animation: slideInUp 0.6s ease-out both;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
}

function triggerEntranceAnimations() {
    // Animate welcome section
    const welcomeSection = document.querySelector('.welcome-section');
    if (welcomeSection) {
        welcomeSection.style.animation = 'fadeInUp 1s ease-out';
    }
    
    // Animate app cards with stagger
    const appCards = document.querySelectorAll('.app-card');
    appCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Add keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Add any escape key functionality
        console.log('Escape pressed');
    }
});

// Add touch gestures for mobile
let touchStartY = 0;
let touchEndY = 0;

document.addEventListener('touchstart', function(e) {
    touchStartY = e.changedTouches[0].screenY;
});

document.addEventListener('touchend', function(e) {
    touchEndY = e.changedTouches[0].screenY;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartY - touchEndY;
    
    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            // Swipe up
            console.log('Swipe up detected');
        } else {
            // Swipe down
            console.log('Swipe down detected');
        }
    }
}

// Performance optimization: Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
        }
    });
}, observerOptions);

// Observe all app cards
document.querySelectorAll('.app-card').forEach(card => {
    observer.observe(card);
});
</script>
@endsection