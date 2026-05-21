<?php

namespace App\Livewire\Registry;

use App\DTOs\Registry\RegistryFilterDTO;
use App\Repositories\Registry\RegistryRequestRepository;
use Livewire\Component;
use Livewire\WithPagination;

class RegistryCorrespondenceRegister extends Component
{
    use WithPagination;

    public string $direction = 'incoming';

    public string $search = '';

    public function render(RegistryRequestRepository $repository)
    {
        $filter = new RegistryFilterDTO(
            direction: $this->direction,
            search: $this->search !== '' ? $this->search : null,
        );

        $requests = $repository->paginate($filter, 20);

        return view('livewire.registry.registry-correspondence-register', compact('requests'));
    }
}
