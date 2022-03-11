<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\ContractFile;
use App\Models\EventCategory;
use App\Models\EventSupplier;
use App\Models\Installment;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function attach(Request $request, EventCategory $category)
    {
        $this->authorize(Permission::ADD_SUPPLIER->value, $category);

        $validated = $request->validate([
            'value' => ['required', 'numeric'],
            'status' => [
                'required',
                Rule::in(['pending', 'hired']),
            ],
            'supplier_id' => [
                'required',
                Rule::exists('App\Models\Supplier', 'id')
                    ->where('category_id', $category->category_id)
            ],
        ]);

        if (!$category->suppliers->contains('supplier_id', $validated['supplier_id'])) {
            $category->suppliers()->create($validated);
        }

        return redirect()->route('events.view', ['event' => $category->event]);
    }

    public function detach(EventSupplier $supplier)
    {
        $this->authorize(Permission::REMOVE_SUPPLIER->value, $supplier);

        if ($supplier->delete()) {
            Storage::delete($supplier->files->pluck('path')->all());
        }

        return redirect()->route('events.view', ['event' => $supplier->category->event]);
    }

    public function update(Request $request, EventSupplier $supplier)
    {
        $this->authorize(Permission::EDIT_SUPPLIER->value, $supplier);

        $request->validate([
            'value' => ['required', 'numeric'],
            'contract.*' => ['sometimes', 'required_if:status,hired', 'file'],
            'status' => [
                'required',
                Rule::in(['pending', 'hired']),
            ],
        ]);

        $supplier->update($request->only(['value', 'status']));

        if ($request->file('contract')) {
            /** @var UploadedFile $file */
            foreach ($request->file('contract') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('contracts');
                    $supplier->files()->create(['path' => $path]);
                }
            }
        }

        return redirect()->route('events.view', ['event' => $supplier->category->event]);
    }

    public function deleteFile(ContractFile $file)
    {
        $event = $file->supplier->category->event;
        $this->authorize(Permission::EDIT_SUPPLIER->value, $file->supplier);

        if ($file->delete()) {
            Storage::delete($file->path);
        }

        return redirect(route('events.view', ['event' => $event]));
    }

    public function createInstallment(Request $request, EventSupplier $supplier)
    {
        $this->authorize(Permission::EDIT_SUPPLIER->value, $supplier);

        $validated = $request->validate([
            'value' => ['required', 'numeric'],
            'status' => [
                'required',
                Rule::in([Installment::STATUS_PAID, Installment::STATUS_PENDING])
            ],
            'due_date' => ['required', 'date'],
        ]);

        $validated['due_date'] = Carbon::create($validated['due_date']);
        $installment = Installment::make($validated);

        if (!$supplier->canCreateInstallment($installment)) {
            return back()->withErrors([
                'value' => 'The sum of installments exceeds the value hired.',
            ]);
        }

        $supplier->installments()->save($installment);

        return redirect()->route('events.view', ['event' => $supplier->category->event]);
    }

    public function updateInstallment(Request $request, Installment $installment)
    {
        $this->authorize(Permission::EDIT_SUPPLIER->value, $installment->supplier);

        $validated = $request->validate([
            'value' => ['sometimes', 'required', 'numeric'],
            'status' => [
                'sometimes',
                'required',
                Rule::in([Installment::STATUS_PAID, Installment::STATUS_PENDING])
            ],
            'due_date' => ['sometimes', 'required', 'date'],
        ]);

        if (isset($validated['due_date'])) {
            $validated['due_date'] = Carbon::create($validated['due_date']);
        }

        $installment->update($validated);
        return redirect()->route('events.view', ['event' => $installment->supplier->category->event]);
    }


    public function deleteInstallment(Installment $installment)
    {
        $this->authorize(Permission::EDIT_SUPPLIER->value, $installment->supplier);

        $installment->delete();

        return redirect()->route('events.view', ['event' => $installment->supplier->category->event]);
    }
}
