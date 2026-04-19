@extends('layouts.ImaraAi.manager')

@section('kb-content')
<div class="manager-content">
    <div style="max-width: 1000px; margin: 0 auto;">
        
        <div style="margin-bottom: 32px;">
            <h1 style="font-size: 2.25rem; font-weight: 800; color: #111827; margin: 0 0 10px 0; letter-spacing: -0.025em;">Imarachat AI Settings</h1>
            <p style="color: #6b7280; font-size: 1.1rem; margin: 0;">Configure and manage your AI assistant's core capabilities and knowledge.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
            {{-- Knowledge Base Card --}}
            <a href="{{ route('ai.knowledge.manager') }}" style="text-decoration: none; display: block; group;">
                <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); height: 100%; box-shadow: 0 1px 2px rgba(0,0,0,0.05); position: relative; overflow: hidden;"
                     onmouseover="this.style.borderColor='#a72b2a'; this.style.boxShadow='0 10px 25px -5px rgba(167, 43, 42, 0.1), 0 8px 10px -6px rgba(167, 43, 42, 0.1)';"
                     onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                    
                    <div style="width: 48px; height: 48px; background: #fef2f2; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; transition: transform 0.3s ease;" class="card-icon">
                        <i class="mdi mdi-database-search" style="font-size: 1.5rem; color: #a72b2a;"></i>
                    </div>

                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0 0 8px 0;">Knowledge Base</h3>
                    <p style="color: #6b7280; font-size: 0.95rem; line-height: 1.5; margin: 0;">Manage manual documents, SOPs, and system-managed knowledge that Imarachat AI uses for grounding responses.</p>
                    
                    <div style="margin-top: 24px; display: flex; align-items: center; gap: 6px; color: #a72b2a; font-weight: 600; font-size: 0.9rem;">
                        Open Manager <i class="mdi mdi-arrow-right" style="font-size: 1.1rem;"></i>
                    </div>
                </div>
            </a>

        </div>
        </div>

        {{-- Help Section --}}
        <div style="margin-top: 48px; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 16px; padding: 32px; text-align: center;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: #fff; border: 1px solid #e5e7eb; border-radius: 50%; margin-bottom: 16px;">
                <i class="mdi mdi-help-circle-outline" style="font-size: 1.25rem; color: #6b7280;"></i>
            </div>
            <h4 style="font-size: 1.1rem; font-weight: 600; color: #374151; margin: 0 0 8px 0;">Need help configuring Imarachat AI?</h4>
            <p style="color: #6b7280; font-size: 0.95rem; margin: 0 auto; max-width: 500px;">Check out the documentation or contact your administrator for guidance on managing AI knowledge collections and permissions.</p>
        </div>

    </div>
</div>
@endsection
