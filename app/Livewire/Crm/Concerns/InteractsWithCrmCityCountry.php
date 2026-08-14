<?php

namespace App\Livewire\Crm\Concerns;

use App\City;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

trait InteractsWithCrmCityCountry
{
    public string $city_id = '';

    public string $citySearch = '';

    public bool $showCityDropdown = false;

    /** @var Collection<int, City>|array<int, mixed> */
    public $cities = [];

    protected function bootCityCountry(): void
    {
        if ($this->country_id) {
            $this->loadCitiesForCountry((string) $this->country_id);
        } else {
            $this->cities = collect();
        }
    }

    protected function loadCitiesForCountry(string $countryId): void
    {
        if ($countryId === '') {
            $this->cities = collect();

            return;
        }

        $this->cities = City::query()
            ->where('country_id', $countryId)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'country_id']);
    }

    public function getSelectedCityProperty()
    {
        return collect($this->cities)->first(
            fn ($city) => (string) data_get($city, 'id') === (string) $this->city_id
        );
    }

    public function getFilteredCitiesProperty()
    {
        $search = trim(strtolower($this->citySearch));

        return collect($this->cities)
            ->filter(function ($city) use ($search) {
                if ((string) data_get($city, 'id') === (string) $this->city_id) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower((string) data_get($city, 'name', '')), $search);
            })
            ->values();
    }

    public function selectCity($cityId): void
    {
        $this->city_id = $cityId ? (string) $cityId : '';
        $this->citySearch = '';
        $this->showCityDropdown = false;
    }

    public function clearCity(): void
    {
        $this->city_id = '';
        $this->citySearch = '';
        $this->showCityDropdown = false;
    }

    protected function clearCitySelection(): void
    {
        $this->city_id = '';
        $this->citySearch = '';
        $this->showCityDropdown = false;
        $this->cities = collect();
    }

    /**
     * @return array<string, mixed>
     */
    protected function cityValidationRules(?string $countryId): array
    {
        $countryId = $countryId ? (string) $countryId : '';

        return [
            'city_id' => [
                'nullable',
                'string',
                Rule::exists('cities', 'id')->where(function ($query) use ($countryId) {
                    if ($countryId !== '') {
                        $query->where('country_id', $countryId);
                    }
                }),
            ],
        ];
    }
}
