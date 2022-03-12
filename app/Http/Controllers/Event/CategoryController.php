<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function attach(Request $request, Event $event)
    {
        $this->authorize(Permission::ADD_CATEGORY->value, $event);

        $validated = $request->validate([
            'category' => ['required', 'exists:App\Models\SupplierCategory,id'],
            'budget' => ['required', 'numeric'],
        ]);

        if (!$event->categories->contains('category_id', $validated['category'])) {
            $event->categories()->create([
                'category_id' => $validated['category'],
                'budget' => $validated['budget'],
            ]);
        }

        return redirect()->route('events.view', ['event' => $event]);
    }

    public function detach(EventCategory $category)
    {
        $this->authorize(Permission::REMOVE_CATEGORY->value, $category);

        DB::transaction(function () use ($category) {
            if ($category->delete()) {
                foreach ($category->suppliers as $supplier) {
                    // model events aren't triggered with cascade
                    if (!Storage::delete($supplier->files->pluck('path')->all())) {
                        throw new \ErrorException("Could not remove files");
                    }
                }
            }
        });

        return redirect()->route('events.view', ['event' => $category->event]);
    }
}
