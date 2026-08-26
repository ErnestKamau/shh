<?php

namespace App\Livewire\System;

use App\Company;
use App\Country;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CompanyManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 10;
    public array $perPageOptions = [10, 25, 50, 100];

    public bool $showCompanyModal = false;
    public ?string $editingCompanyId = null;

    public string $name = '';
    public ?string $code = null;
    public ?string $location = null;
    public ?string $address = null;
    public ?string $country_id = null;
    public ?string $website = null;
    public ?string $email = null;
    public ?string $cell_phone = null;
    public ?string $telephone = null;
    public ?string $street = null;
    public ?string $fax = null;
    public $logoFile = null;
    public ?string $existingLogo = null;
    public $faviconFile = null;
    public ?string $existingFavicon = null;
    public $watermarkFile = null;
    public ?string $existingWatermark = null;
    public array $reportLogos = [];
    public array $reportLogosToDelete = [];

    // Maintenance period (global equipment maintenance year range)
    public ?int $maintenanceStartYear = null;
    public ?int $maintenanceStartMonth = null;
    public ?int $maintenanceEndYear = null;
    public ?int $maintenanceEndMonth = null;

    public bool $showStatusModal = false;
    public ?string $statusCompanyId = null;
    public string $statusCompanyName = '';
    public bool $setDefault = false;
    public bool $showOnReports = false;

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->authorizeAction('system.companies.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorizeAction('system.companies.add');

        $this->resetCompanyForm();
        $this->editingCompanyId = null;
        $this->reportLogos = [];
        $this->reportLogosToDelete = [];
        $this->showCompanyModal = true;
    }

    public function openEditModal(string $id): void
    {
        $this->authorizeAction('system.companies.edit');

        $company = Company::query()->findOrFail($id);

        $this->editingCompanyId = $company->id;
        $this->name = (string) $company->name;
        $this->code = $company->code;
        $this->location = $company->location;
        $this->address = $company->address;
        $this->country_id = $company->country_id;
        $this->website = $company->website;
        $this->email = $company->email;
        $this->cell_phone = $company->cell_phone;
        $this->telephone = $company->telephone;
        $this->street = $company->street;
        $this->fax = $company->fax;
        $this->logoFile = null;
        $this->existingLogo = $company->logo;
        $this->faviconFile = null;
        $this->existingFavicon = $company->favicon;
        $this->watermarkFile = null;
        $this->existingWatermark = $company->watermark;
        
        $this->reportLogos = $company->reportLogos->map(function ($logo) {
            $path = (string) ($logo->logo_path ?? '');

            return [
                'id' => $logo->id,
                'name' => $logo->name,
                'existing_path' => $path,
                'file' => null,
                'file_missing' => $path !== '' && ! $this->publicLogoExists($path),
                'position_vertical' => $logo->position_vertical ?? 'top',
                'position_horizontal' => $logo->position_horizontal ?? 'left',
                'show_on_every_page' => $logo->show_on_every_page ?? true,
                'report_type' => $logo->report_type ?? '',
            ];
        })->toArray();
        $this->reportLogosToDelete = [];
        $this->maintenanceStartYear = $company->maintenance_start_year;
        $this->maintenanceStartMonth = $company->maintenance_start_month;
        $this->maintenanceEndYear = $company->maintenance_end_year;
        $this->maintenanceEndMonth = $company->maintenance_end_month;

        $this->showCompanyModal = true;
    }

    public function closeCompanyModal(): void
    {
        $this->showCompanyModal = false;
        $this->resetValidation();
    }

    public function saveCompany(): void
    {
        $permission = $this->editingCompanyId === null
            ? 'system.companies.add'
            : 'system.companies.edit';

        $this->authorizeAction($permission);

        $this->code = $this->normalizeCompanyCode($this->code);

        $codeRules = [
            'required',
            'string',
            'max:32',
            'regex:/^[a-z][a-z0-9_-]*$/',
        ];

        if (Schema::hasColumn('companies', 'code')) {
            $codeRules[] = Rule::unique('companies', 'code')->ignore($this->editingCompanyId);
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => $codeRules,
            'location' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'country_id' => ['nullable', 'string', 'exists:countries,id'],
            'website' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'cell_phone' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'fax' => ['nullable', 'string', 'max:255'],
            'logoFile' => ['nullable', 'image', 'max:5120'],
            'faviconFile' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,ico,gif,webp', 'max:2048'],
            'watermarkFile' => ['nullable', 'image', 'max:5120'],
            'reportLogos.*.name' => ['required', 'string', 'max:255'],
            'reportLogos.*.file' => ['nullable', 'image', 'max:5120'],
            'maintenanceStartYear' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'maintenanceStartMonth' => ['nullable', 'integer', 'min:1', 'max:12'],
            'maintenanceEndYear' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'maintenanceEndMonth' => ['nullable', 'integer', 'min:1', 'max:12'],
        ], [
            'code.regex' => 'Use a lowercase code starting with a letter (e.g. brl, uae). Letters, numbers, hyphens, and underscores only.',
            'code.unique' => 'This company code is already used by another company.',
        ]);

        $company = $this->editingCompanyId === null
            ? new Company()
            : Company::query()->findOrFail($this->editingCompanyId);

        $company->name = $validated['name'];
        $company->code = $validated['code'];
        $company->location = $validated['location'];
        $company->address = $validated['address'];
        $company->country_id = $validated['country_id'];
        $company->website = $validated['website'];
        $company->email = $validated['email'];
        $company->cell_phone = $validated['cell_phone'];
        $company->telephone = $validated['telephone'];
        $company->street = $validated['street'];
        $company->fax = $validated['fax'];
        $company->maintenance_start_year = $validated['maintenanceStartYear'];
        $company->maintenance_start_month = $validated['maintenanceStartMonth'];
        $company->maintenance_end_year = $validated['maintenanceEndYear'];
        $company->maintenance_end_month = $validated['maintenanceEndMonth'];

        if ($this->logoFile) {
            $logoPath = '/storage/' . $this->logoFile->store('companies', 'public');
            $company->logo = $logoPath;
            $company->report_logo = $logoPath;
        } elseif (empty($company->report_logo) && $company->logo) {
            $company->report_logo = $company->logo;
        }

        if ($this->faviconFile) {
            $company->favicon = '/storage/' . $this->faviconFile->store('companies/favicons', 'public');
        }

        if ($this->watermarkFile) {
            $company->watermark = '/storage/' . $this->watermarkFile->store('companies/watermarks', 'public');
        }

        $company->save();

        \Illuminate\Support\Facades\Cache::forget('system_favicon_url');

        if (!empty($this->reportLogosToDelete)) {
            \App\CompanyReportLogo::whereIn('id', $this->reportLogosToDelete)->delete();
        }

        foreach ($this->reportLogos as $index => $logoData) {
            $logoPath = $logoData['existing_path'] ?? null;
            if (isset($logoData['file']) && $logoData['file']) {
                $stored = $logoData['file']->store('companies', 'public');
                if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($stored)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "reportLogos.{$index}.file" => 'Failed to store the report logo. Please try uploading again.',
                    ]);
                }
                $logoPath = '/storage/'.$stored;
            }

            if ($logoPath) {
                $placement = [
                    'company_id'          => $company->id,
                    'name'                => $logoData['name'],
                    'logo_path'           => $logoPath,
                    'position_vertical'   => $logoData['position_vertical'] ?? 'top',
                    'position_horizontal' => $logoData['position_horizontal'] ?? 'left',
                    'show_on_every_page'  => isset($logoData['show_on_every_page']) ? (bool) $logoData['show_on_every_page'] : true,
                    'report_type'         => ($logoData['report_type'] ?? null) !== ''
                        ? ($logoData['report_type'] ?? null)
                        : null,
                ];
                if (empty($logoData['id'])) {
                    \App\CompanyReportLogo::create($placement);
                } else {
                    \App\CompanyReportLogo::where('id', $logoData['id'])->update($placement);
                }
            }
        }

        session()->flash('success', $this->editingCompanyId === null
            ? 'Company added.'
            : 'Company edited.');

        $this->showCompanyModal = false;
        $this->resetCompanyForm();
        $this->resetPage();
    }

    public function openStatusModal(string $id): void
    {
        $this->authorizeAction('system.companies.edit');

        $company = Company::query()->findOrFail($id);

        $this->statusCompanyId = $company->id;
        $this->statusCompanyName = (string) $company->name;
        $this->setDefault = (bool) $company->active;
        $this->showOnReports = (bool) $company->show_on_reports;
        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->statusCompanyId = null;
        $this->statusCompanyName = '';
        $this->setDefault = false;
        $this->showOnReports = false;
    }

    public function applyStatus(): void
    {
        $this->authorizeAction('system.companies.edit');

        if ($this->statusCompanyId === null) {
            return;
        }

        Company::query()->update(['active' => 0]);

        $company = Company::query()->findOrFail($this->statusCompanyId);
        $company->active = $this->setDefault ? 1 : 0;
        $company->show_on_reports = $this->showOnReports ? 1 : 0;
        $company->save();

        \Illuminate\Support\Facades\Cache::forget('system_favicon_url');

        session()->flash('success', 'Company activated successfully!');
        $this->closeStatusModal();
        $this->resetPage();
    }

    public function addReportLogo(): void
    {
        $this->reportLogos[] = [
            'id' => null,
            'name' => '',
            'existing_path' => null,
            'file' => null,
            'position_vertical' => 'top',
            'position_horizontal' => 'left',
            'show_on_every_page' => true,
            'report_type' => '',
        ];
    }

    public function removeReportLogo(int $index): void
    {
        if (!empty($this->reportLogos[$index]['id'])) {
            $this->reportLogosToDelete[] = $this->reportLogos[$index]['id'];
        }
        unset($this->reportLogos[$index]);
        $this->reportLogos = array_values($this->reportLogos);
    }

    public function getCountriesProperty()
    {
        return Country::query()->orderBy('name')->get(['id', 'name']);
    }

    public function getCompaniesProperty()
    {
        return Company::query()
            ->leftJoin('countries as c', 'c.id', '=', 'companies.country_id')
            ->select('companies.*', 'c.name as country_name')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($subQuery): void {
                    $subQuery->where('companies.id', 'like', '%' . $this->search . '%')
                        ->orWhere('companies.code', 'like', '%' . $this->search . '%')
                        ->orWhere('c.name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query): void {
                $query->where('companies.active', (int) $this->statusFilter);
            })
            ->orderByDesc('companies.active')
            ->orderByDesc('companies.created_at')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.system.company-manager');
    }

    private function resetCompanyForm(): void
    {
        $this->name = '';
        $this->code = null;
        $this->location = null;
        $this->address = null;
        $this->country_id = null;
        $this->website = null;
        $this->email = null;
        $this->cell_phone = null;
        $this->telephone = null;
        $this->street = null;
        $this->fax = null;
        $this->logoFile = null;
        $this->existingLogo = null;
        $this->faviconFile = null;
        $this->existingFavicon = null;
        $this->watermarkFile = null;
        $this->existingWatermark = null;
        $this->reportLogos = [];
        $this->reportLogosToDelete = [];
        $this->maintenanceStartYear = null;
        $this->maintenanceStartMonth = null;
        $this->maintenanceEndYear = null;
        $this->maintenanceEndMonth = null;
    }

    private function publicLogoExists(string $path): bool
    {
        $path = trim($path);
        if ($path === '') {
            return false;
        }

        $relative = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        try {
            if ($relative !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($relative)) {
                return true;
            }
        } catch (\Throwable) {
            // fall through to filesystem checks
        }

        foreach ([
            public_path($path),
            public_path(ltrim($path, '/')),
            public_path('storage/'.$relative),
            storage_path('app/public/'.$relative),
        ] as $candidate) {
            if (is_readable($candidate)) {
                return true;
            }
        }

        return false;
    }

    private function authorizeAction(string $permission): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if ((method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) || $user->can($permission)) {
            return;
        }

        abort(403);
    }

    private function normalizeCompanyCode(?string $code): ?string
    {
        $normalized = strtolower(trim((string) $code));

        return $normalized === '' ? null : $normalized;
    }
}