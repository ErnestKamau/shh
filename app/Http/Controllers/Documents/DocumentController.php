<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentAttachment;
use App\Models\DocumentPublication;
use App\Models\DocumentFolder;
use App\Models\NotificationFrequency;
use App\InventoryDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class DocumentController extends Controller
{
    protected function getActiveRoles(): \Illuminate\Support\Collection
    {
        $roles = SpatieRole::query()
            ->where('active', true)
            ->orderBy('name');

        $company = getUserCompany();
        if ($company && isset($company->id)) {
            $roles->where('company_id', $company->id);
        }

        return $roles->get();
    }

    public function __construct()
    {
        $this->middleware(['auth', 'twofactor']);
    }

    public function dashboard()
    {
        $user = Auth::user();

        // Build the publish scope filter for the current user
        $publishScopeFilter = function($query) use ($user) {
            // Admin users can see all documents
            if ($user->hasRole('Admin')) {
                return; // No filtering for admin users
            }
            
            $query->where(function($q) use ($user) {
                $q->where('publish_scope', 'all_departments')
                  ->orWhere('publish_scope', 'all_roles')
                  ->orWhere(function($subQ) use ($user) {
                      // Department-specific documents
                      $subQ->where('publish_scope', 'department')
                           ->whereJsonContains('publish_targets', (string)$user->department_id);
                  })
                  ->orWhere(function($subQ) use ($user) {
                      // Role-specific documents
                      $userRoleIds = $user->roles->pluck('id')->toArray();
                      if (!empty($userRoleIds)) {
                          $subQ->where('publish_scope', 'role')
                               ->where(function($roleQ) use ($userRoleIds) {
                                   foreach ($userRoleIds as $roleId) {
                                       $roleQ->orWhereJsonContains('publish_targets', (string)$roleId);
                                   }
                               });
                      }
                  })
                  ->orWhere(function($subQ) use ($user) {
                      // Mixed scope documents
                      $userRoleIds = $user->roles->pluck('id')->toArray();
                      $subQ->where('publish_scope', 'mixed')
                           ->where(function($mixedQ) use ($user, $userRoleIds) {
                               $mixedQ->whereJsonContains('publish_targets->departments', (string)$user->department_id)
                                      ->orWhere(function($roleQ) use ($userRoleIds) {
                                          if (!empty($userRoleIds)) {
                                              foreach ($userRoleIds as $roleId) {
                                                  $roleQ->orWhereJsonContains('publish_targets->roles', (string)$roleId);
                                              }
                                          }
                                      });
                           });
                  });
            });
        };

        // Get statistics for published documents accessible to the user
        $totalDocuments = Document::currentVersion()->active()->published()->where($publishScopeFilter)->count();
        $departmentDocuments = Document::currentVersion()->active()->published()->where($publishScopeFilter)->count();
        $expiringDocuments = Document::currentVersion()->active()->published()->where($publishScopeFilter)->where('validity_period', '<=', now()->addDays(30))->count();
        $expiredDocuments = Document::currentVersion()->active()->published()->expired()->where($publishScopeFilter)->count();
        $unpublishedDocuments = Document::currentVersion()->active()->unpublished()->where($publishScopeFilter)->count();

        // Get published documents by type accessible to the user
        $documentsByType = Document::currentVersion()
            ->active()
            ->published()
            ->where($publishScopeFilter)
            ->with('documentType')
            ->get()
            ->groupBy('document_type_id');

        // Get only top 8 document types for dashboard display (count published documents only)
        $documentTypes = DocumentType::where('is_active', true)
            ->withCount(['documents' => function($query) use ($publishScopeFilter) {
                $query->published()->where($publishScopeFilter);
            }])
            ->orderBy('documents_count', 'desc')
            ->limit(8)
            ->get();
        
        // Get total count of document types for "View All" logic
        $totalDocumentTypes = DocumentType::where('is_active', true)->count();
        
        $departments = InventoryDepartment::where('active', true)->get();

        return view('documents.dashboard', compact(
            'totalDocuments',
            'departmentDocuments',
            'expiringDocuments',
            'expiredDocuments',
            'unpublishedDocuments',
            'documentsByType',
            'documentTypes',
            'totalDocumentTypes',
            'departments'
        ));
    }

    public function coas()
    {
        $user = Auth::user();
        
        // Ensure user has access to Document Management before allowing access.
        // We will just return the view which will render the Livewire components.
        return view('documents.coas');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $userDepartment = $user->department;

        // Since we're using Livewire, we don't need to pass data here
        // The Livewire component will handle showing only published documents
        return view('documents.index');
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $userDepartment = $user->department;
        
        // Check if user has permission to create documents
        if (!$user->canAddDocuments()) {
            return redirect()->back()->with('error', 'You do not have permission to create documents. This action requires the "documents.components.document management.add" permission.');
        }
        
        $documentTypes = DocumentType::where('is_active', true)->get();
        $departments = InventoryDepartment::where('active', true)->get();
        $roles = $this->getActiveRoles();
        $notificationFrequencies = NotificationFrequency::where('is_active', true)->get();

        // Check if creating a new version from parent document
        $parentDocument = null;
        if ($request->has('parent_id')) {
            $parentDocument = Document::findOrFail($request->parent_id);
            
            // Check if user has access to the parent document
            if ($parentDocument->department_id !== $userDepartment->id) {
                abort(403, 'You do not have access to this document.');
            }
        }

        $folders = DocumentFolder::where('department_id', $userDepartment->id)->orderBy('name')->get();

        $folderTree = [];
        foreach ($documentTypes as $type) {
            $folderTree[] = (object)[
                'id' => 'type_' . $type->id,
                'name' => '📄 ' . $type->name,
                'level' => 0,
                'is_type' => true,
                'document_type_id' => $type->id
            ];
            
            $buildTree = function($parentId, $level) use (&$buildTree, &$folderTree, $folders, $type) {
                $children = $folders->where('document_type_id', $type->id)->where('parent_id', $parentId);
                foreach ($children as $child) {
                    $folderTree[] = (object)[
                        'id' => 'folder_' . $child->id,
                        'name' => str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . '📁 ' . $child->name,
                        'level' => $level,
                        'is_type' => false,
                        'document_type_id' => $type->id
                    ];
                    $buildTree($child->id, $level + 1);
                }
            };
            
            $buildTree(null, 1);
        }

    return view('documents.create', compact('documentTypes', 'departments', 'userDepartment', 'roles', 'notificationFrequencies', 'parentDocument', 'folderTree'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userDepartment = $user->department;

        if ($request->filled('location')) {
            $locTypeId = null;
            $locFolderId = null;
            if (str_starts_with($request->location, 'type_')) {
                $locTypeId = substr($request->location, 5);
            } elseif (str_starts_with($request->location, 'folder_')) {
                $locFolderId = substr($request->location, 7);
                $folder = \App\Models\DocumentFolder::find($locFolderId);
                if ($folder) {
                    $locTypeId = $folder->document_type_id;
                }
            }
            $request->merge([
                'document_type_id' => $locTypeId,
                'document_folder_id' => $locFolderId
            ]);
        }

        // Check if user has permission to create documents
        if (!$user->canAddDocuments()) {
            return redirect()->back()->with('error', 'You do not have permission to create documents. This action requires the "documents.components.document management.add" permission.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    // Trim the values to ensure exact matching
                    $trimmedName = trim($value);
                    $trimmedVersion = trim($request->version);
                    
                    // Check if document name is unique within the same document type and version
                    $exists = Document::where('name', $trimmedName)
                        ->where('document_type_id', $request->document_type_id)
                        ->where('version', $trimmedVersion)
                        ->exists();
                    
                    if ($exists) {
                        $fail('A document with this name and version already exists for the selected document type.');
                    }
                }
            ],
            'description' => 'nullable|string',
            'document_type_id' => 'required|exists:document_types,id',
            'document_folder_id' => 'nullable|exists:document_folders,id',
            'version' => 'required|numeric|min:0.1',
            'validity_period' => 'nullable|date|after:today',
            'status' => 'required|in:active,draft,archived',
            'document_file' => 'required|file|max:10240', // 10MB max
            'attachments.*' => 'nullable|file|max:10240',
            'publish_scope' => 'nullable|in:role,department,all_departments,all_roles,mixed',
            'publish_targets' => 'nullable|array',
            'publish_targets.*' => 'nullable|integer',
            'publish_targets.departments' => 'required_if:publish_scope,mixed|array',
            'publish_targets.departments.*' => 'required_if:publish_scope,mixed|integer',
            'publish_targets.roles' => 'required_if:publish_scope,mixed|array',
            'publish_targets.roles.*' => 'required_if:publish_scope,mixed|integer',
            'publish_targets.*' => 'required_if:publish_scope,role,department|integer',
            'notification_frequency_id' => 'nullable|exists:notification_frequencies,id',
            'notification_days_before_expiry' => 'nullable|integer|min:1|max:365',
            'notifications_enabled' => 'nullable|boolean'
        ]);

        // Generate unique document number
        $documentNumber = 'DOC-' . date('Y') . '-' . str_pad(Document::count() + 1, 4, '0', STR_PAD_LEFT);

        // Check if this is a new version of an existing document
        $parentDocumentId = null;
        if ($request->filled('parent_document_id')) {
            $parentDocument = Document::findOrFail($request->parent_document_id);
            
            // Check if user has access to the parent document
            if ($parentDocument->department_id !== $userDepartment->id) {
                abort(403, 'You do not have access to this document.');
            }
            
            $parentDocumentId = $parentDocument->id;
        }

        // Handle main document file
        $file = $request->file('document_file');
        $filePath = $file->store('documents/' . date('Y/m'), 'public');

        // Determine if document should be published
        $isPublished = false;
        $publishScope = null;
        $publishTargets = null;
        $publishedBy = null;
        $publishedAt = null;

        if ($request->filled('publish_scope')) {
            $isPublished = true;
            $publishScope = $request->publish_scope;
            
            // Handle different publishing scopes
            if ($request->publish_scope === 'mixed') {
                $publishTargets = [
                    'departments' => $request->publish_targets['departments'] ?? [],
                    'roles' => $request->publish_targets['roles'] ?? []
                ];
            } elseif ($request->publish_scope === 'all_departments') {
                $publishTargets = ['all_departments' => true];
            } elseif ($request->publish_scope === 'all_roles') {
                $publishTargets = ['all_roles' => true];
            } else {
                $publishTargets = $request->publish_targets ?? [];
            }
            
            $publishedBy = Auth::id();
            $publishedAt = now();
        }

        $document = Document::create([
            'name' => trim($request->name),
            'description' => $request->description,
            'document_number' => $documentNumber,
            'document_type_id' => $request->document_type_id,
            'document_folder_id' => $request->document_folder_id,
            'department_id' => $userDepartment->id,
            'version' => (float)trim($request->version),
            'validity_period' => $request->validity_period,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => $request->status,
            'parent_document_id' => $parentDocumentId,
            'created_by' => Auth::id(),
            'is_published' => $isPublished,
            'publish_scope' => $publishScope,
            'publish_targets' => $publishTargets,
            'published_by' => $publishedBy,
            'published_at' => $publishedAt,
            'notification_frequency_id' => $request->notification_frequency_id,
            'notification_days_before_expiry' => $request->notification_days_before_expiry ?? 30,
            'notifications_enabled' => $request->has('notifications_enabled')
        ]);

        // Handle additional attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $attachmentPath = $attachment->store('documents/attachments/' . date('Y/m'), 'public');
                
                DocumentAttachment::create([
                    'document_id' => $document->id,
                    'file_path' => $attachmentPath,
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_type' => $attachment->getClientMimeType(),
                    'file_size' => $attachment->getSize(),
                    'attachment_type' => 'supporting',
                    'uploaded_by' => Auth::id()
                ]);
            }
        }

        // Create publication record if document is published
        if ($isPublished) {
            DocumentPublication::create([
                'document_id' => $document->id,
                'publish_scope' => $publishScope,
                'publish_targets' => $publishTargets,
                'published_by' => $publishedBy,
                'published_at' => $publishedAt,
                'is_active' => true
            ]);
        }

        $message = $isPublished ? 'Document created and published successfully.' : 'Document created successfully.';
        return redirect()->route('documents.show', $document->id)
            ->with('success', $message);
    }

    public function show($id)
    {
        $user = Auth::user();
        $userDepartment = $user->department;

        $document = Document::with([
            'documentType', 
            'department', 
            'creator', 
            'updater', 
            'approver',
            'notificationFrequency',
            'attachments.uploader',
            'versions.documentType',
            'versions.creator'
        ])->findOrFail($id);

        // Admin users and users with edit permissions can access any document
        if (!$user->hasRole('Admin') && !$user->canEditDocuments()) {
            // Check if user has access to this document based on publishing scope
            $hasAccess = false;
            
            // Check if document is published - only published documents are visible to regular users
            if ($document->is_published) {
                // Check publishing scope
                if ($document->publish_scope === 'all_departments' || $document->publish_scope === 'all_roles') {
                    $hasAccess = true;
                } elseif ($document->publish_scope === 'department') {
                    $hasAccess = in_array($userDepartment->id, $document->publish_targets ?? []);
                } elseif ($document->publish_scope === 'role') {
                    $userRoleIds = $user->roles->pluck('id')->toArray();
                    $hasAccess = !empty(array_intersect($userRoleIds, $document->publish_targets ?? []));
                } elseif ($document->publish_scope === 'mixed') {
                    $userRoleIds = $user->roles->pluck('id')->toArray();
                    $targets = $document->publish_targets ?? [];
                    $hasAccess = in_array($userDepartment->id, $targets['departments'] ?? []) || 
                                !empty(array_intersect($userRoleIds, $targets['roles'] ?? []));
                }
            }
            
            if (!$hasAccess) {
                abort(403, 'You do not have access to this document.');
            }
        }

        // Get version history - all documents with the same name and document type
        $versionHistory = Document::with(['creator', 'documentType'])
            ->sameDocument($document->name, $document->document_type_id)
            ->byDepartment($userDepartment->id)
            ->orderBy('version', 'desc')
            ->get();

        return view('documents.show', compact('document', 'versionHistory'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $userDepartment = $user->department;

        $document = Document::findOrFail($id);

        // Check if user has access to this document
        if ($document->department_id !== $userDepartment->id) {
            abort(403, 'You do not have access to this document.');
        }

        $documentTypes = DocumentType::where('is_active', true)->get();
        $departments = InventoryDepartment::where('active', true)->get();
        $roles = $this->getActiveRoles();
        $notificationFrequencies = NotificationFrequency::where('is_active', true)->get();

        $folders = DocumentFolder::where('department_id', $userDepartment->id)->orderBy('name')->get();

        $folderTree = [];
        foreach ($documentTypes as $type) {
            $folderTree[] = (object)[
                'id' => 'type_' . $type->id,
                'name' => '📄 ' . $type->name,
                'level' => 0,
                'is_type' => true,
                'document_type_id' => $type->id
            ];
            
            $buildTree = function($parentId, $level) use (&$buildTree, &$folderTree, $folders, $type) {
                $children = $folders->where('document_type_id', $type->id)->where('parent_id', $parentId);
                foreach ($children as $child) {
                    $folderTree[] = (object)[
                        'id' => 'folder_' . $child->id,
                        'name' => str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . '📁 ' . $child->name,
                        'level' => $level,
                        'is_type' => false,
                        'document_type_id' => $type->id
                    ];
                    $buildTree($child->id, $level + 1);
                }
            };
            
            $buildTree(null, 1);
        }

        $currentLocation = $document->document_folder_id ? 'folder_' . $document->document_folder_id : 'type_' . $document->document_type_id;

    return view('documents.edit', compact('document', 'documentTypes', 'departments', 'roles', 'notificationFrequencies', 'folderTree', 'currentLocation'));
    }

    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        if ($request->filled('location')) {
            $locTypeId = null;
            $locFolderId = null;
            if (str_starts_with($request->location, 'type_')) {
                $locTypeId = substr($request->location, 5);
            } elseif (str_starts_with($request->location, 'folder_')) {
                $locFolderId = substr($request->location, 7);
                $folder = \App\Models\DocumentFolder::find($locFolderId);
                if ($folder) {
                    $locTypeId = $folder->document_type_id;
                }
            }
            $request->merge([
                'document_type_id' => $locTypeId,
                'document_folder_id' => $locFolderId
            ]);
        }

        // Check if user has access to this document
        if ($document->department_id !== $user->department->id) {
            abort(403, 'You do not have access to this document.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $document) {
                    // Trim the values to ensure exact matching
                    $trimmedName = trim($value);
                    $trimmedVersion = trim($request->version);
                    
                    // Check if document name is unique within the same document type and version, excluding current document
                    $exists = Document::where('name', $trimmedName)
                        ->where('document_type_id', $request->document_type_id)
                        ->where('version', $trimmedVersion)
                        ->where('id', '!=', $document->id)
                        ->exists();
                    
                    if ($exists) {
                        $fail('A document with this name and version already exists for the selected document type.');
                    }
                }
            ],
            'description' => 'nullable|string',
            'document_type_id' => 'required|exists:document_types,id',
            'document_folder_id' => 'nullable|exists:document_folders,id',
            'version' => 'required|numeric|min:0.1',
            'validity_period' => 'nullable|date|after:today',
            'status' => 'required|in:active,draft,archived',
            'document_file' => 'nullable|file|max:10240',
            'publish_scope' => 'nullable|in:role,department,all_departments,all_roles,mixed',
            'publish_targets' => 'nullable|array',
            'publish_targets.*' => 'nullable|integer',
            'publish_targets.departments' => 'required_if:publish_scope,mixed|array',
            'publish_targets.departments.*' => 'required_if:publish_scope,mixed|integer',
            'publish_targets.roles' => 'required_if:publish_scope,mixed|array',
            'publish_targets.roles.*' => 'required_if:publish_scope,mixed|integer',
            'publish_targets.*' => 'required_if:publish_scope,role,department|integer',
            'notification_frequency_id' => 'nullable|exists:notification_frequencies,id',
            'notification_days_before_expiry' => 'nullable|integer|min:1|max:365',
            'notifications_enabled' => 'nullable|boolean'
        ]);

        // Handle publishing information
        $isPublished = false;
        $publishScope = null;
        $publishTargets = null;
        $publishedBy = null;
        $publishedAt = null;

        if ($request->filled('publish_scope')) {
            $isPublished = true;
            $publishScope = $request->publish_scope;
            
            // Handle different publishing scopes
            if ($request->publish_scope === 'mixed') {
                $publishTargets = [
                    'departments' => $request->publish_targets['departments'] ?? [],
                    'roles' => $request->publish_targets['roles'] ?? []
                ];
            } elseif ($request->publish_scope === 'all_departments') {
                $publishTargets = ['all_departments' => true];
            } elseif ($request->publish_scope === 'all_roles') {
                $publishTargets = ['all_roles' => true];
            } else {
                $publishTargets = $request->publish_targets ?? [];
            }
            
            $publishedBy = Auth::id();
            $publishedAt = now();
        }

        // Update existing document
        $updateData = [
            'name' => trim($request->name),
            'description' => $request->description,
            'document_type_id' => $request->document_type_id,
            'document_folder_id' => $request->document_folder_id,
            'version' => (float)trim($request->version),
            'validity_period' => $request->validity_period,
            'status' => $request->status,
            'is_published' => $isPublished,
            'publish_scope' => $publishScope,
            'publish_targets' => $publishTargets,
            'published_by' => $publishedBy,
            'published_at' => $publishedAt,
            'notification_frequency_id' => $request->notification_frequency_id,
            'notification_days_before_expiry' => $request->notification_days_before_expiry ?? 30,
            'notifications_enabled' => $request->has('notifications_enabled'),
            'updated_by' => Auth::id()
        ];

        // Handle file replacement
        if ($request->hasFile('document_file')) {
            // Delete old file
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('document_file');
            $filePath = $file->store('documents/' . date('Y/m'), 'public');

            $updateData = array_merge($updateData, [
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize()
            ]);
        }

        $document->update($updateData);

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'Document updated successfully.');
    }

    private function createNewVersion(Request $request, Document $originalDocument)
    {
        // Generate new document number for the version
        $documentNumber = $originalDocument->document_number . '-V' . $request->version;

        // Handle main document file
        $file = $request->file('document_file');
        $filePath = $file->store('documents/' . date('Y/m'), 'public');

        // Create new version
        $newDocument = Document::create([
            'name' => trim($request->name),
            'description' => $request->description,
            'document_number' => $documentNumber,
            'document_type_id' => $request->document_type_id,
            'department_id' => $request->department_id,
            'version' => (float)trim($request->version),
            'validity_period' => $request->validity_period,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => $request->status,
            'parent_document_id' => $originalDocument->id,
            'created_by' => Auth::id()
        ]);

        // Set original document as not current version
        $originalDocument->update(['is_current_version' => false]);

        // Copy attachments from original document
        foreach ($originalDocument->attachments as $attachment) {
            DocumentAttachment::create([
                'document_id' => $newDocument->id,
                'file_path' => $attachment->file_path,
                'file_name' => $attachment->file_name,
                'file_type' => $attachment->file_type,
                'file_size' => $attachment->file_size,
                'attachment_type' => $attachment->attachment_type,
                'description' => $attachment->description,
                'uploaded_by' => Auth::id()
            ]);
        }

        // Handle additional attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $attachmentPath = $attachment->store('documents/attachments/' . date('Y/m'), 'public');
                
                DocumentAttachment::create([
                    'document_id' => $newDocument->id,
                    'file_path' => $attachmentPath,
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_type' => $attachment->getClientMimeType(),
                    'file_size' => $attachment->getSize(),
                    'attachment_type' => 'supporting',
                    'uploaded_by' => Auth::id()
                ]);
            }
        }

        return redirect()->route('documents.show', $newDocument->id)
            ->with('success', 'New version created successfully.');
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        // Check if user has access to this document
        if ($document->department_id !== $user->department->id) {
            abort(403, 'You do not have access to this document.');
        }

        // Soft delete the document
        $document->update([
            'is_active' => false,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        // Check if user has access to this document
        if ($document->department_id !== $user->department->id) {
            abort(403, 'You do not have access to this document.');
        }

        // Check if document is published - only published documents can be downloaded
        if (!$document->is_published) {
            abort(403, 'This document is not published and cannot be downloaded.');
        }

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        // Force download by setting appropriate headers
        $filePath = Storage::disk('public')->path($document->file_path);
        $fileName = $document->file_name;
        $fileType = $document->file_type;

        return response()->download($filePath, $fileName, [
            'Content-Type' => $fileType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache'
        ]);
    }

    public function downloadAttachment($id)
    {
        $attachment = DocumentAttachment::with('document')->findOrFail($id);
        $user = Auth::user();

        // Check if user has access to this document
        if ($attachment->document->department_id !== $user->department->id) {
            abort(403, 'You do not have access to this document.');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    public function deleteAttachment($id)
    {
        $attachment = DocumentAttachment::with('document')->findOrFail($id);
        $user = Auth::user();

        // Check if user has access to this document
        if ($attachment->document->department_id !== $user->department->id) {
            abort(403, 'You do not have access to this document.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    }

    public function checkDuplicate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'document_type_id' => 'required|exists:document_types,id',
            'version' => 'required|numeric'
        ]);

        // Trim the values to ensure exact matching
        $trimmedName = trim($request->name);
        $trimmedVersion = (float)trim($request->version);

        $exists = Document::where('name', $trimmedName)
            ->where('document_type_id', $request->document_type_id)
            ->where('version', $trimmedVersion)
            ->exists();

        // Get existing versions for this document name and type
        $existingVersions = Document::where('name', $trimmedName)
            ->where('document_type_id', $request->document_type_id)
            ->pluck('version')
            ->toArray();

        // Suggest next version
        $suggestedVersion = $this->getNextVersion($trimmedVersion, $existingVersions);

        return response()->json([
            'exists' => $exists,
            'existing_versions' => $existingVersions,
            'suggested_version' => $suggestedVersion
        ]);
    }

    private function getNextVersion($currentVersion, $existingVersions)
    {
        // Convert current version to float for numeric comparison
        $currentVersionFloat = (float)$currentVersion;
        
        // Convert existing versions to floats for numeric comparison
        $existingVersionsFloat = array_map('floatval', $existingVersions);
        
        // Find the highest version number
        $highestVersion = max(array_merge([$currentVersionFloat], $existingVersionsFloat));
        
        // Return the next version (increment by 0.1)
        return $highestVersion + 0.1;
    }

    public function unpublished()
    {
        return view('documents.unpublished');
    }

    public function expired()
    {
        return view('documents.expired');
    }

    public function publish($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        // Admin users and users with edit permissions can publish any document
        if (!$user->hasRole('Admin') && !$user->canEditDocuments()) {
            // Check if user has access to this document based on publishing scope
            $hasAccess = false;
            $userDepartment = $user->department;
            
            // Check if document is published - only published documents are visible to regular users
            if ($document->is_published) {
                // Check publishing scope
                if ($document->publish_scope === 'all_departments' || $document->publish_scope === 'all_roles') {
                    $hasAccess = true;
                } elseif ($document->publish_scope === 'department') {
                    $hasAccess = in_array($userDepartment->id, $document->publish_targets ?? []);
                } elseif ($document->publish_scope === 'role') {
                    $userRoleIds = $user->roles->pluck('id')->toArray();
                    $hasAccess = !empty(array_intersect($userRoleIds, $document->publish_targets ?? []));
                } elseif ($document->publish_scope === 'mixed') {
                    $userRoleIds = $user->roles->pluck('id')->toArray();
                    $targets = $document->publish_targets ?? [];
                    $hasAccess = in_array($userDepartment->id, $targets['departments'] ?? []) || 
                                !empty(array_intersect($userRoleIds, $targets['roles'] ?? []));
                }
            }
            
            if (!$hasAccess) {
                abort(403, 'You do not have access to this document.');
            }
        }

        $departments = InventoryDepartment::where('active', true)->get();
        $roles = $this->getActiveRoles();

        return view('documents.publish', compact('document', 'departments', 'roles'));
    }

    public function storePublish(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        // Admin users and users with edit permissions can publish any document
        if (!$user->hasRole('Admin') && !$user->canEditDocuments()) {
            // Check if user has access to this document based on publishing scope
            $hasAccess = false;
            $userDepartment = $user->department;
            
            // Check if document is published - only published documents are visible to regular users
            if ($document->is_published) {
                // Check publishing scope
                if ($document->publish_scope === 'all_departments' || $document->publish_scope === 'all_roles') {
                    $hasAccess = true;
                } elseif ($document->publish_scope === 'department') {
                    $hasAccess = in_array($userDepartment->id, $document->publish_targets ?? []);
                } elseif ($document->publish_scope === 'role') {
                    $userRoleIds = $user->roles->pluck('id')->toArray();
                    $hasAccess = !empty(array_intersect($userRoleIds, $document->publish_targets ?? []));
                } elseif ($document->publish_scope === 'mixed') {
                    $userRoleIds = $user->roles->pluck('id')->toArray();
                    $targets = $document->publish_targets ?? [];
                    $hasAccess = in_array($userDepartment->id, $targets['departments'] ?? []) || 
                                !empty(array_intersect($userRoleIds, $targets['roles'] ?? []));
                }
            }
            
            if (!$hasAccess) {
                abort(403, 'You do not have access to this document.');
            }
        }

        $request->validate([
            'publish_scope' => 'required|in:role,department,all_departments,all_roles,mixed',
            'publish_targets' => 'nullable|array',
            'publish_targets.*' => 'required_if:publish_scope,role,department|integer',
            'publish_targets.departments' => 'required_if:publish_scope,mixed|array',
            'publish_targets.departments.*' => 'required_if:publish_scope,mixed|integer',
            'publish_targets.roles' => 'required_if:publish_scope,mixed|array',
            'publish_targets.roles.*' => 'required_if:publish_scope,mixed|integer'
        ]);

        // Handle different publishing scopes
        if ($request->publish_scope === 'mixed') {
            $publishTargets = [
                'departments' => $request->publish_targets['departments'] ?? [],
                'roles' => $request->publish_targets['roles'] ?? []
            ];
        } elseif ($request->publish_scope === 'all_departments') {
            $publishTargets = ['all_departments' => true];
        } elseif ($request->publish_scope === 'all_roles') {
            $publishTargets = ['all_roles' => true];
        } else {
            $publishTargets = $request->publish_targets ?? [];
        }

        // Update document with publishing information
        $document->update([
            'is_published' => true,
            'publish_scope' => $request->publish_scope,
            'publish_targets' => $publishTargets,
            'published_by' => Auth::id(),
            'published_at' => now()
        ]);

        // Create publication record
        DocumentPublication::create([
            'document_id' => $document->id,
            'publish_scope' => $request->publish_scope,
            'publish_targets' => $publishTargets,
            'published_by' => Auth::id(),
            'published_at' => now(),
            'is_active' => true
        ]);

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'Document published successfully.');
    }

    public function unpublish($id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        // Check if user has access to this document
        if ($document->department_id !== $user->department->id) {
            abort(403, 'You do not have access to this document.');
        }

        // Update document to unpublish
        $document->update([
            'is_published' => false,
            'publish_scope' => null,
            'publish_targets' => null,
            'published_by' => null,
            'published_at' => null
        ]);

        // Deactivate all publication records for this document
        DocumentPublication::where('document_id', $document->id)
            ->update(['is_active' => false]);

        return redirect()->route('documents.show', $document->id)
            ->with('success', 'Document unpublished successfully.');
    }

    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        $userDepartment = $user->department;

        if ($request->filled('location')) {
            $locTypeId = null;
            $locFolderId = null;
            if (str_starts_with($request->location, 'type_')) {
                $locTypeId = substr($request->location, 5);
            } elseif (str_starts_with($request->location, 'folder_')) {
                $locFolderId = substr($request->location, 7);
                $folder = \App\Models\DocumentFolder::find($locFolderId);
                if ($folder) {
                    $locTypeId = $folder->document_type_id;
                }
            }
            $request->merge([
                'document_type_id' => $locTypeId,
                'document_folder_id' => $locFolderId
            ]);
        }

        // Check if user has permission to create documents
        if (!$user->canAddDocuments()) {
            return redirect()->back()->with('error', 'You do not have permission to create documents. This action requires the "documents.components.document management.add" permission.');
        }

        // Validate request
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'document_folder_id' => 'nullable|exists:document_folders,id',
            'version' => 'required|numeric|min:0.1',
            'validity_period' => 'nullable|date|after:today',
            'status' => 'required|in:active,draft,archived',
            'document_files' => 'required|array|min:1|max:900',
            'document_files.*' => 'required|file|max:10240', // 10MB max per file
            'publish_scope' => 'nullable|in:role,department,all_departments,all_roles,mixed',
            'publish_targets' => 'nullable|array',
            'publish_targets.*' => 'nullable|integer',
            'publish_targets.departments' => 'required_if:publish_scope,mixed|array',
            'publish_targets.departments.*' => 'required_if:publish_scope,mixed|integer',
            'publish_targets.roles' => 'required_if:publish_scope,mixed|array',
            'publish_targets.roles.*' => 'required_if:publish_scope,mixed|integer',
            'publish_targets.*' => 'required_if:publish_scope,role,department|integer',
        ]);

        $documentTypeId = $request->document_type_id;
        $documentFolderId = $request->document_folder_id;
        $version = $request->version;
        $validityPeriod = $request->validity_period;
        $status = $request->status;
        $files = $request->file('document_files');

        // Determine if documents should be published (same logic as single upload)
        $isPublished = false;
        $publishScope = null;
        $publishTargets = null;
        $publishedBy = null;
        $publishedAt = null;

        if ($request->filled('publish_scope')) {
            $isPublished = true;
            $publishScope = $request->publish_scope;
            
            // Handle different publishing scopes
            if ($request->publish_scope === 'mixed') {
                $publishTargets = [
                    'departments' => $request->publish_targets['departments'] ?? [],
                    'roles' => $request->publish_targets['roles'] ?? []
                ];
            } elseif ($request->publish_scope === 'all_departments') {
                $publishTargets = ['all_departments' => true];
            } elseif ($request->publish_scope === 'all_roles') {
                $publishTargets = ['all_roles' => true];
            } else {
                $publishTargets = $request->publish_targets ?? [];
            }
            
            $publishedBy = Auth::id();
            $publishedAt = now();
        }

        // Get existing document names for uniqueness check
        $existingNames = Document::where('document_type_id', $documentTypeId)
            ->pluck('name')
            ->toArray();

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        // Process files in chunks for memory efficiency
        $chunkSize = 50; // Process 50 files at a time
        $chunks = array_chunk($files, $chunkSize);

        foreach ($chunks as $chunkIndex => $fileChunk) {
            // Clear memory after each chunk
            if ($chunkIndex > 0) {
                gc_collect_cycles();
            }

            foreach ($fileChunk as $file) {
                try {
                    // Generate unique document name from filename
                    $baseName = $this->extractBaseNameFromFilename($file->getClientOriginalName());
                    $documentName = $this->generateUniqueDocumentName($baseName, $existingNames);
                    $existingNames[] = $documentName;

                    // Generate unique document number
                    $documentNumber = 'DOC-' . date('Y') . '-' . str_pad(Document::count() + 1, 4, '0', STR_PAD_LEFT);

                    // Store file
                    $filePath = $file->store('documents/' . date('Y/m'), 'public');

                    // Create document record
                    Document::create([
                        'name' => $documentName,
                        'document_number' => $documentNumber,
                        'description' => null,
                        'document_type_id' => $documentTypeId,
                        'document_folder_id' => $documentFolderId,
                        'version' => $version,
                        'validity_period' => $validityPeriod,
                        'status' => $status,
                        'file_path' => $filePath,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'department_id' => $userDepartment->id,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'is_published' => $isPublished,
                        'publish_scope' => $publishScope,
                        'publish_targets' => $publishTargets,
                        'published_by' => $publishedBy,
                        'published_at' => $publishedAt,
                        'notification_frequency_id' => null,
                        'notification_days_before_expiry' => 30,
                        'notifications_enabled' => false,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Error processing {$file->getClientOriginalName()}: " . $e->getMessage();
                }
            }
        }

        // Prepare response message
        $message = "Bulk upload completed. Successfully uploaded {$successCount} documents.";
        if ($errorCount > 0) {
            $message .= " Failed to upload {$errorCount} documents.";
        }

        return redirect()->route('documents.index')
            ->with('success', $message)
            ->with('bulk_upload_errors', $errors);
    }

    /**
     * Extract base name from filename
     */
    private function extractBaseNameFromFilename($filename)
    {
        // Remove file extension
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        
        // Remove common prefixes/suffixes and clean up
        $baseName = preg_replace('/^[0-9]+[-_\s]*/', '', $nameWithoutExt); // Remove leading numbers
        $baseName = preg_replace('/[-_\s]+/', ' ', $baseName); // Replace multiple dashes/underscores with single space
        $baseName = trim($baseName);
        $baseName = preg_replace('/\s+/', ' ', $baseName); // Normalize spaces
        
        return $baseName ?: 'Document'; // Fallback if empty
    }

    /**
     * Generate unique document name
     */
    private function generateUniqueDocumentName($baseName, $existingNames)
    {
        $counter = 1;
        $newName = $baseName;
        
        while (in_array($newName, $existingNames)) {
            $newName = $baseName . $counter;
            $counter++;
        }
        
        return $newName;
    }
}
