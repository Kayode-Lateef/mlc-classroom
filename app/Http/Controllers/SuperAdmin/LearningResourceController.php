<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LearningResourceController extends Controller
{
    /**
     * Display a listing of learning resources
     */
    public function index(Request $request)
    {
        $query = LearningResource::with(['uploader', 'class']);

        // Filter by resource type
        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->resource_type);
        }

        // Filter by subject
        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by teacher
        if ($request->filled('uploaded_by')) {
            $query->where('uploaded_by', $request->uploaded_by);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $resources = $query->paginate(20);

        // Get filter options
        $classes = ClassModel::orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $subjects = LearningResource::distinct('subject')->pluck('subject')->filter();

        // Statistics
        $stats = [
            'total_resources' => LearningResource::count(),
            'pdf_count' => LearningResource::where('resource_type', 'pdf')->count(),
            'video_count' => LearningResource::where('resource_type', 'video')->count(),
            'general_resources' => LearningResource::whereNull('class_id')->count(),
        ];

        return view('superadmin.learning-resources.index', compact(
            'resources',
            'classes',
            'teachers',
            'subjects',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new resource
     */
    public function create()
    {
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        
        return view('superadmin.learning-resources.create', compact('classes'));
    }

    /**
     * Store a newly created resource
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'resource_type' => 'required|in:pdf,video,link,image,document',
            'subject' => 'nullable|string|max:100',
            'class_id' => 'nullable|exists:classes,id',
            'file' => 'required_if:resource_type,pdf,image,document|file|max:10240', // 10MB max
            'video_url' => 'required_if:resource_type,video|url|max:500',
            'external_link' => 'required_if:resource_type,link|url|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        try {
            $filePath = null;

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . str_replace(' ', '_', $originalName);
                
                $filePath = $file->storeAs('learning-resources', $filename, 'public');
            } elseif ($request->filled('video_url')) {
                $filePath = $request->video_url;
            } elseif ($request->filled('external_link')) {
                $filePath = $request->external_link;
            }

            // Create resource
            $resource = LearningResource::create([
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $filePath,
                'resource_type' => $request->resource_type,
                'uploaded_by' => auth()->id(),
                'class_id' => $request->class_id,
                'subject' => $request->subject,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created_learning_resource',
                'model_type' => 'LearningResource',
                'model_id' => $resource->id,
                'description' => "Created learning resource: {$resource->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('superadmin.resources.index')
                ->with('success', 'Learning resource created successfully!');

        } catch (\Exception $e) {
            \Log::error('Learning resource creation failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create learning resource. Please try again.');
        }
    }

    /**
     * Display the specified resource
     */
    public function show(LearningResource $resource)
    {
        $resource->load(['uploader', 'class']);

        return view('superadmin.learning-resources.show', compact('resource'));
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit(LearningResource $resource)
    {
        $classes = ClassModel::with('teacher')->orderBy('name')->get();
        
        return view('superadmin.learning-resources.edit', compact('resource', 'classes'));
    }

    /**
     * Update the specified resource
     */
    public function update(Request $request, LearningResource $resource)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'resource_type' => 'required|in:pdf,video,link,image,document',
            'subject' => 'nullable|string|max:100',
            'class_id' => 'nullable|exists:classes,id',
            'file' => 'nullable|file|max:10240',
            'video_url' => 'nullable|url|max:500',
            'external_link' => 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        try {
            $filePath = $resource->file_path;

            // Handle new file upload
            if ($request->hasFile('file')) {
                // Delete old file if it exists and is local
                if ($resource->file_path && !filter_var($resource->file_path, FILTER_VALIDATE_URL)) {
                    Storage::disk('public')->delete($resource->file_path);
                }

                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . str_replace(' ', '_', $originalName);
                
                $filePath = $file->storeAs('learning-resources', $filename, 'public');
            } elseif ($request->filled('video_url')) {
                $filePath = $request->video_url;
            } elseif ($request->filled('external_link')) {
                $filePath = $request->external_link;
            }

            // Update resource
            $resource->update([
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $filePath,
                'resource_type' => $request->resource_type,
                'class_id' => $request->class_id,
                'subject' => $request->subject,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_learning_resource',
                'model_type' => 'LearningResource',
                'model_id' => $resource->id,
                'description' => "Updated learning resource: {$resource->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('superadmin.resources.index')
                ->with('success', 'Learning resource updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Learning resource update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update learning resource. Please try again.');
        }
    }

    /**
     * Remove the specified resource
     */
    public function destroy(LearningResource $resource)
    {
        try {
            $title = $resource->title;
            $resourceId = $resource->id;

            // Delete file if it's local
            if ($resource->file_path && !filter_var($resource->file_path, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($resource->file_path);
            }

            $resource->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'deleted_learning_resource',
                'model_type' => 'LearningResource',
                'model_id' => $resourceId,
                'description' => "Deleted learning resource: {$title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('superadmin.resources.index')
                ->with('success', 'Learning resource deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Learning resource deletion failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete learning resource. Please try again.');
        }
    }

    /**
     * Download resource file
     */
    public function download(LearningResource $resource)
    {
        if (!$resource->file_path || filter_var($resource->file_path, FILTER_VALIDATE_URL)) {
            return redirect()->back()->with('error', 'This resource cannot be downloaded.');
        }

        if (!Storage::disk('public')->exists($resource->file_path)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        // Log download activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'downloaded_resource',
            'model_type' => 'LearningResource',
            'model_id' => $resource->id,
            'description' => "Downloaded resource: {$resource->title}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::disk('public')->download(
            $resource->file_path,
            $resource->title . '.' . pathinfo($resource->file_path, PATHINFO_EXTENSION)
        );
    }
}