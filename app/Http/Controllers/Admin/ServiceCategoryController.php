<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceCategory::withCount('services')->with('createdBy');

        if ($search = $request->search) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $categories = $query->latest()->paginate(10)->withQueryString();

        return view('admin.service-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:100|unique:service_categories,name',
            'status' => 'required|in:active,inactive',
        ]);

        $cat = ServiceCategory::create([
            'name'       => $request->name,
            'status'     => $request->status,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => "Category \"{$cat->name}\" created.", 'category' => $cat]);
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $request->validate([
            'name'   => ['required', 'string', 'max:100', Rule::unique('service_categories')->ignore($serviceCategory->id)],
            'status' => 'required|in:active,inactive',
        ]);

        $serviceCategory->update(['name' => $request->name, 'status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Category updated successfully.']);
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        $serviceCategory->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    }
}
