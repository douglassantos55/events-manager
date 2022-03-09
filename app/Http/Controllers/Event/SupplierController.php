<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventSupplier;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function attach(Request $request, Event $event, EventCategory $category)
    {
        $this->authorize(Permission::ADD_SUPPLIER->value, $event);

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

        return redirect()->route('events.view', ['event' => $event]);
    }

    public function detach(Event $event, EventCategory $category, EventSupplier $supplier)
    {
        $this->authorize(Permission::REMOVE_SUPPLIER->value, $event);

        $supplier->delete();
        Storage::delete($supplier->files->pluck('path')->all());

        return redirect()->route('events.view', ['event' => $event]);
    }

    public function update(Request $request, Event $event, EventCategory $category, EventSupplier $supplier)
    {
        $this->authorize(Permission::EDIT_SUPPLIER->value, $event);

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

        return redirect()->route('events.view', ['event' => $event]);
    }
}
