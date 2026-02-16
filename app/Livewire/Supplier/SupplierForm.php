<?php

namespace App\Livewire\Supplier;

use App\Models\Supplier;
use Livewire\Component;

class SupplierForm extends Component
{
    public ?Supplier $supplier = null;

    public string $name = '';

    public string $contact_person = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public function mount(?int $supplierId = null): void
    {
        if ($supplierId) {
            $this->supplier = Supplier::findOrFail($supplierId);
            $this->fill($this->supplier->only([
                'name', 'contact_person', 'phone', 'email', 'address',
            ]));
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'contact_person' => $this->contact_person ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'address' => $this->address ?: null,
        ];

        if ($this->supplier) {
            $this->supplier->update($data);
            session()->flash('success', 'Supplier berhasil diperbarui.');
        } else {
            Supplier::create($data);
            session()->flash('success', 'Supplier berhasil ditambahkan.');
        }

        $this->redirect(route('suppliers.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.supplier.supplier-form');
    }
}
