<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\ContractFile;
use App\Models\EventCategory;
use App\Models\EventSupplier;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($supplier) {
            if ($supplier->delete()) {
                if (!Storage::delete($supplier->files->pluck('path')->all())) {
                    throw new \ErrorException("Could not remove files");
                }
            }
        });

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

        DB::transaction(function () use ($supplier, $request) {
            $supplier->update($request->only(['value', 'status']));

            if ($request->file('contract')) {
                /** @var UploadedFile $file */
                foreach ($request->file('contract') as $file) {
                    if ($file->isValid()) {
                        $path = $file->store('contracts');

                        if ($path === false) {
                            throw new \ErrorException("Could not upload file");
                        }

                        $supplier->files()->create(['path' => $path]);
                    }
                }
            }
        });

        return redirect()->route('events.view', ['event' => $supplier->category->event]);
    }

    public function deleteFile(ContractFile $file)
    {
        $event = $file->supplier->category->event;
        $this->authorize(Permission::EDIT_SUPPLIER->value, $file->supplier);

        DB::transaction(function () use ($file) {
            if ($file->delete()) {
                if (!Storage::delete($file->path)) {
                    throw new \ErrorException("Could not remove file");
                }
            }
        });

        return redirect(route('events.view', ['event' => $event]));
    }
}
