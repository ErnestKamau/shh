<?php

namespace App\Services\Personnel;

use App\Http\Controllers\PersonnelWorkHistoryController;
use App\User;
use App\UserLabRelation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PersonnelProfileService
{
    public function __construct(
        private readonly PersonnelSignatureService $signatureService,
    ) {}

    /**
     * @param  array{
     *     first_name: string,
     *     middle_name?: string|null,
     *     last_name?: string|null,
     *     email: string,
     *     phone?: string|null,
     *     id_number: string,
     *     date_of_birth?: string|null,
     *     employment_date?: string|null,
     *     designation?: string|null,
     *     education_level?: string|null,
     *     position?: string|null,
     *     department_id?: string|null,
     *     analyst_is_gazzetted?: bool,
     *     date_of_gazzette?: string|null,
     *     gazzette_no?: string|null,
     *     start_of_career?: string|null,
     *     lab_section_ids?: array<int, string>,
     *     lab_ids?: array<int, string>,
     *     kra_pin?: string|null,
     *     nssf?: string|null,
     *     nhif?: string|null,
     * }  $attributes
     */
    public function update(
        User $user,
        array $attributes,
        ?UploadedFile $signatureUpload = null,
        string $signatureData = '',
        ?UploadedFile $photoUpload = null,
        ?string $plainPassword = null,
    ): User {
        $originalDepartment = (string) ($user->department_id ?? '');
        $originalPosition = (string) ($user->position ?? '');

        $firstName = trim((string) ($attributes['first_name'] ?? ''));
        $middleName = trim((string) ($attributes['middle_name'] ?? ''));
        $lastName = trim((string) ($attributes['last_name'] ?? ''));

        $user->first_name = $firstName;
        $user->middle_name = $middleName !== '' ? $middleName : null;
        $user->last_name = $lastName !== '' ? $lastName : null;
        $user->name = trim($firstName . ' ' . $middleName . ' ' . $lastName);
        $user->email = trim((string) ($attributes['email'] ?? ''));
        $user->phone = trim((string) ($attributes['phone'] ?? '')) ?: null;
        $user->id_number = trim((string) ($attributes['id_number'] ?? ''));
        $user->date_of_birth = ($attributes['date_of_birth'] ?? null) ?: null;
        $user->employment_date = ($attributes['employment_date'] ?? null) ?: null;
        $user->designation = ($attributes['designation'] ?? null) ?: null;
        $user->education_level = ($attributes['education_level'] ?? null) ?: null;
        $user->position = ($attributes['position'] ?? null) ?: null;
        $user->department_id = ($attributes['department_id'] ?? null) ?: null;

        $isGazzetted = (bool) ($attributes['analyst_is_gazzetted'] ?? false);
        $user->analyst_is_gazzetted = $isGazzetted;
        $user->date_of_gazzette = $isGazzetted ? (($attributes['date_of_gazzette'] ?? null) ?: null) : null;
        $gazzetteNo = trim((string) ($attributes['gazzette_no'] ?? ''));
        $user->gazzette_no = $isGazzetted && $gazzetteNo !== '' ? $gazzetteNo : null;
        $user->start_of_career = ($attributes['start_of_career'] ?? null) ?: null;

        if (array_key_exists('kra_pin', $attributes)) {
            $user->kra_pin = trim((string) ($attributes['kra_pin'] ?? '')) ?: null;
        }
        if (array_key_exists('nssf', $attributes)) {
            $user->nssf = trim((string) ($attributes['nssf'] ?? '')) ?: null;
        }
        if (array_key_exists('nhif', $attributes)) {
            $user->nhif = trim((string) ($attributes['nhif'] ?? '')) ?: null;
        }

        $labSectionIds = array_values(array_unique(array_filter(
            (array) ($attributes['lab_section_ids'] ?? []),
            fn ($id): bool => (string) $id !== ''
        )));
        $user->lab_section_id = implode(',', $labSectionIds);

        $this->signatureService->applyToUser($user, $signatureUpload, $signatureData);

        if ($photoUpload !== null) {
            $storedPath = $photoUpload->storeAs('personnel-photos', $photoUpload->hashName(), 'public');
            $user->photo = '/storage/' . $storedPath;
        }

        if ($plainPassword !== null && $plainPassword !== '') {
            $user->password = Hash::make($plainPassword);
            $user->password_changed_at = now();
        }

        $user->save();

        $labIds = array_values(array_unique(array_filter(
            (array) ($attributes['lab_ids'] ?? []),
            fn ($id): bool => (string) $id !== ''
        )));

        if (Schema::hasTable('user_lab_relation')) {
            UserLabRelation::where('user_id', $user->id)->delete();
            foreach ($labIds as $labId) {
                UserLabRelation::create([
                    'user_id' => $user->id,
                    'lab_id' => (string) $labId,
                ]);
            }
        }

        if ($originalDepartment !== (string) ($user->department_id ?? '') || $originalPosition !== (string) ($user->position ?? '')) {
            (new PersonnelWorkHistoryController())->updateWorkHistory($user->id, $user->department_id, $user->position);
        }

        return $user->fresh();
    }
}
