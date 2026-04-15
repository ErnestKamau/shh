@extends('layouts.ImaraAi.manager')

@section('kb-content')
<div class="manager-content">
    <div style="max-width: 1200px; margin: 0 auto;">
        {{-- Header Section --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div>
                <h1 style="font-size: 2.25rem; font-weight: 800; color: #111827; margin: 0 0 8px 0; letter-spacing: -0.025em;">Knowledge Base Manager</h1>
                <p style="color: #6b7280; font-size: 1.1rem; margin: 0;">Manage manual documents, SOPs, and system-managed knowledge for AI reasoning.</p>
            </div>
            <button onclick="openKnowledgeModal()" style="background: #a72b2a; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(167, 43, 42, 0.2);" onmouseover="this.style.background='#8e2423'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#a72b2a'; this.style.transform='translateY(0)'">
                <i class="mdi mdi-plus-circle" style="font-size: 1.25rem;"></i>
                Add Knowledge
            </button>
        </div>

        {{-- Stats Section --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px;">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <div style="color: #6b7280; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.025em; margin-bottom: 8px;">Total Chunks</div>
                <div id="statTotalItems" style="font-size: 1.875rem; font-weight: 800; color: #111827;">0</div>
                <div style="margin-top: 4px; color: #10b981; font-size: 0.875rem; display: flex; align-items: center; gap: 4px;">
                    <i class="mdi mdi-arrow-up-right"></i> Active in RAG
                </div>
            </div>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <div style="color: #6b7280; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.025em; margin-bottom: 8px;">Manual Entries</div>
                <div id="statManualDocs" style="font-size: 1.875rem; font-weight: 800; color: #111827;">0</div>
                <div style="margin-top: 4px; color: #6b7280; font-size: 0.875rem;">User-managed documents</div>
            </div>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <div style="color: #6b7280; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.025em; margin-bottom: 8px;">Last Synced</div>
                <div id="statLastUpdated" style="font-size: 1.25rem; font-weight: 700; color: #111827; margin-top: 8px;">Never</div>
                <div style="margin-top: 4px; color: #6b7280; font-size: 0.875rem;">Auto-refresh enabled</div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 24px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px; position: relative;">
                <i class="mdi mdi-magnify" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 1.25rem;"></i>
                <input type="text" id="kbSearch" placeholder="Search by identifier or collection..." style="width: 100%; padding: 12px 16px 12px 48px; border: 1px solid #e5e7eb; border-radius: 10px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#a72b2a'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
            
            <select id="kbFilterCollection" style="padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; background: white; outline: none; min-width: 150px;">
                <option value="">All Collections</option>
                <option value="General Ops">General Ops</option>
                <option value="Inventory">Inventory</option>
                <option value="SOPs">SOPs</option>
            </select>

            <select id="kbFilterType" style="padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; background: white; outline: none; min-width: 150px;">
                <option value="">All Types</option>
                <option value="System Managed">System Managed</option>
                <option value="Manual Entry">Manual Entry</option>
            </select>

            <select id="kbFilterPermission" style="padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 10px; background: white; outline: none; min-width: 160px;">
                <option value="">All Permissions</option>
                <option value="General.View">General Access</option>
                <option value="manual entry">Personal Docs</option>
            </select>

            <button onclick="clearFilters()" style="background: none; border: none; color: #6b7280; font-weight: 600; cursor: pointer; padding: 8px 12px; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">Clear</button>
        </div>

        {{-- Knowledge Table --}}
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                        <th style="padding: 12px 16px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Collection</th>
                        <th style="padding: 12px 16px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Source / Identifier</th>
                        <th style="padding: 12px 16px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Type</th>
                        <th style="padding: 12px 16px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Required Permission</th>
                        <th style="padding: 12px 16px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; text-align: center;">Chunks</th>
                        <th style="padding: 12px 16px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                        <th style="padding: 12px 16px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Last Indexed</th>
                        <th style="padding: 12px 16px; color: #4b5563; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="kbTableBody">
                    {{-- Rows injected via JS --}}
                </tbody>
            </table>
            
            {{-- Empty State --}}
            <div id="kbEmptyState" style="padding: 80px 24px; text-align: center; display: none;">
                <div style="width: 64px; height: 64px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <i class="mdi mdi-database-off" style="font-size: 2rem; color: #9ca3af;"></i>
                </div>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: #111827; margin: 0 0 8px 0;">No knowledge entries found</h3>
                <p style="color: #6b7280; font-size: 0.95rem; margin: 0 auto 24px; max-width: 400px;">Begin by adding manual documents or wait for the system to index operational data into the RAG collections.</p>
                <button onclick="openKnowledgeModal()" style="background: white; border: 1px solid #d1d5db; padding: 10px 20px; border-radius: 8px; font-weight: 600; color: #374151; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='white'">Add your first entry</button>
            </div>
        </div>
    </div>
</div>

{{-- Manual Knowledge Modal --}}
<div id="knowledgeModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 1000;">
    <div style="background: white; width: 100%; max-width: 700px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
        <div style="padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #fafafa;">
            <div>
                <h3 id="kbModalTitle" style="font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0;">Add Knowledge</h3>
                <p style="color: #6b7280; font-size: 0.875rem; margin: 4px 0 0 0;">Create a manual entry for the AI knowledge base.</p>
            </div>
            <button onclick="closeKnowledgeModal()" style="background: none; border: none; font-size: 1.5rem; color: #9ca3af; cursor: pointer; line-height: 1;">&times;</button>
        </div>
        
        <form id="kbForm" onsubmit="handleKbFormSubmit(event)" style="flex: 1; overflow-y: auto; padding: 24px;">
            <input type="hidden" id="manualDocId">
            <input type="hidden" id="kbCreatedBy">
            <input type="hidden" id="kbCreatedAt">

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Document Title</label>
                <input type="text" id="kbTitle" required placeholder="e.g. Standard Operating Procedure for Lab Safety" style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#a72b2a'">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Collection Name</label>
                    <select id="kbCollection" required style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; background: white; outline: none;">
                        <option value="General Ops">General Ops</option>
                        <option value="Inventory">Inventory</option>
                        <option value="SOPs">SOPs</option>
                        <option value="Product Catalog">Product Catalog</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Required Permission</label>
                    <select id="kbPermission" style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; background: white; outline: none;">
                        <option value="General.View">General Access</option>
                        <option value="Laboratory.Samples.View">Lab Staff</option>
                        <option value="Laboratory.Admin">Lab Admin</option>
                        <option value="Quality.Control.Manage">QC Team</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #374151;">Document Content</label>
                    <span style="font-size: 0.75rem; color: #6b7280;">Markdown supported</span>
                </div>
                <textarea id="kbContent" required rows="12" placeholder="Paste the content that the AI should know..." style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; font-family: inherit; font-size: 0.95rem; line-height: 1.5; transition: border-color 0.2s;" onfocus="this.style.borderColor='#a72b2a'"></textarea>
            </div>

            <div id="kbCreatorInfo" style="display: none; padding: 12px; background: #f9fafb; border-radius: 8px; margin-bottom: 12px; border: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 0.825rem;">
                    <i class="mdi mdi-information-outline"></i>
                    <span id="kbCreatorLabel">Created by User on Date</span>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6;">
                <button type="button" onclick="closeKnowledgeModal()" style="padding: 10px 20px; border: 1px solid #d1d5db; border-radius: 8px; color: #4b5563; font-weight: 600; background: white; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 24px; border: none; border-radius: 8px; color: white; font-weight: 600; background: #a72b2a; cursor: pointer; box-shadow: 0 2px 4px rgba(167, 43, 42, 0.2);">Save Document</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
@include('layouts.ImaraAi.partials._knowledge-base-scripts')
<script>
    // Constants from environment/blade
    const KB_URL = "{{ url('/api/ai/knowledge') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
    
    // UI Elements
    const kbTableBody = document.getElementById('kbTableBody');
    const kbEmptyState = document.getElementById('kbEmptyState');
    const kbSearch = document.getElementById('kbSearch');
    const kbFilterCollection = document.getElementById('kbFilterCollection');
    const kbFilterType = document.getElementById('kbFilterType');
    const kbFilterPermission = document.getElementById('kbFilterPermission');
    const knowledgeModal = document.getElementById('knowledgeModal');
    const kbForm = document.getElementById('kbForm');

    let allKnowledgeItems = [];

    // Initialize
    window.addEventListener('DOMContentLoaded', () => {
        fetchKnowledgeBase();
        
        // Setup filter listeners
        kbSearch.addEventListener('input', applyFilters);
        kbFilterCollection.addEventListener('change', applyFilters);
        kbFilterType.addEventListener('change', applyFilters);
        kbFilterPermission.addEventListener('change', applyFilters);
    });
</script>
@endpush
