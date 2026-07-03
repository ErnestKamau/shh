@include('layouts.partials.login-theme')
<style>
    html, body {
        background: linear-gradient(135deg, var(--color-primary-tint) 0%, var(--color-bg-app) 45%, #ffffff 100%);
        color: #111827;
        font-family: 'Raleway', sans-serif;
        font-weight: 100;
        height: 100vh;
        margin: 0;
    }

    .full-height { height: 100vh; }
    .flex-center { align-items: center; display: flex; justify-content: center; }
    .position-ref { position: relative; }
    .content { text-align: center; }
    .w3-whiter { background-color: rgba(199, 199, 199, 0.8); color: #232323; }

    .w3-border-brown { border-color: var(--color-primary) !important; }
    .btn-primary {
        background-color: var(--color-primary) !important;
        border-color: var(--color-primary) !important;
        color: #ffffff !important;
        font-weight: 600;
    }
    .btn-primary:hover,
    .btn-primary:focus {
        background-color: var(--color-primary-hover) !important;
        border-color: var(--color-primary-hover) !important;
        color: #ffffff !important;
    }
    .btn-link { color: var(--color-primary) !important; font-weight: 500; }
    .btn-link:hover,
    .btn-link:focus { color: var(--color-primary-hover) !important; }
</style>
