<?php

namespace App\Livewire\Pages;

use App\Models\Countries;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;

class CountriesIndex extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $status = 'all'; // all, active, historic

    #[Url]
    public string $championships = 'all'; // all, 1-5, 6-10, 10+

    #[Url]
    public int $page = 1;

    public int $perPage = 9;

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedStatus(): void
    {
        $this->page = 1;
    }

    public function updatedChampionships(): void
    {
        $this->page = 1;
    }

    public function getCountriesProperty(): LengthAwarePaginator
    {
        $query = Countries::query()->orderBy('name');

        if ($this->search !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term);
            });
        }

        if ($this->status === 'active') {
            $query->where('is_active', true);
        } elseif ($this->status === 'historic') {
            $query->where('is_active', false);
        }

        if ($this->championships === '1-5') {
            $query->whereBetween('world_championships_won', [1, 5]);
        } elseif ($this->championships === '6-10') {
            $query->whereBetween('world_championships_won', [6, 10]);
        } elseif ($this->championships === '10+') {
            $query->where('world_championships_won', '>=', 10);
        }

        return $query->paginate($this->perPage, ['*'], 'page', $this->page)->withQueryString();
    }

    public function render()
    {
        return view('livewire.pages.countries-index', [
            'countries' => $this->countries,
        ]);
    }
}
