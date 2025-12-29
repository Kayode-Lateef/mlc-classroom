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
        // Custom validation messages
        $messages = [
            'title.required' => 'Resource title is required.',
            'resource_type.required' => 'Please select a resource type.',
            'file.required_if' => 'Please upload a file for this resource type.',
            'file.max' => 'File size must not exceed 10MB.',
            'video_url.required_if' => 'Please provide a video URL.',
            'video_url.url' => 'Please provide a valid video URL.',
            'external_link.required_if' => 'Please provide an external link.',
            'external_link.url' => 'Please provide a valid URL.',
            'class_id.exists' => 'Selected class does not exist.',
        ];

        // Validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'resource_type' => 'required|in:pdf,video,link,image,document',
            'subject' => 'nullable|string|max:100',
            'class_id' => 'nullable|exists:classes,id',
            'file' => 'required_if:resource_type,pdf,image,document|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:10240',
            'video_url' => 'required_if:resource_type,video|nullable|url|max:500',
            'external_link' => 'required_if:resource_type,link|nullable|url|max:500',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        try {
            $filePath = null;

            // Handle file upload for pdf, image, document
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Additional file validation
                $maxSize = 10 * 1024 * 1024; // 10MB in bytes
                if ($file->getSize() > $maxSize) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'File size exceeds 10MB limit.');
                }

                // Validate file type based on resource type
                $resourceType = $request->resource_type;
                $allowedMimes = [];
                
                if ($resourceType === 'pdf') {
                    $allowedMimes = ['application/pdf'];
                } elseif ($resourceType === 'document') {
                    $allowedMimes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                } elseif ($resourceType === 'image') {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                }

                if (!empty($allowedMimes) && !in_array($file->getMimeType(), $allowedMimes)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Invalid file type for the selected resource type.');
                }

                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                
                // Store file
                $filePath = $file->storeAs('learning-resources', $filename, 'public');
                
                if (!$filePath) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Failed to upload file. Please try again.');
                }
            } 
            // Handle video URL
            elseif ($request->filled('video_url')) {
                $filePath = $request->video_url;
                
                // Validate video URL (YouTube or Vimeo)
                if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|vimeo\.com)\/.*/', $filePath)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Please provide a valid YouTube or Vimeo URL.');
                }
            } 
            // Handle external link
            elseif ($request->filled('external_link')) {
                $filePath = $request->external_link;
            }
            // No file provided
            else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Please provide a file, video URL, or external link.');
            }

            // Create resource
            $resource = LearningResource::create([
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $filePath,
                'resource_type' => $request->resource_type,
                'uploaded_by' => auth()->id(),
                'class_id' => $request->class_id ?: null,
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
            // Delete uploaded file if exists
            if (isset($filePath) && $filePath && !filter_var($filePath, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($filePath);
            }
            
            \Log::error('Learning resource creation failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create learning resource: ' . $e->getMessage());
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
        // Custom validation messages
        $messages = [
            'title.required' => 'Resource title is required.',
            'resource_type.required' => 'Please select a resource type.',
            'file.max' => 'File size must not exceed 10MB.',
            'file.mimes' => 'Invalid file type. Please upload a valid file.',
            'video_url.url' => 'Please provide a valid video URL.',
            'external_link.url' => 'Please provide a valid URL.',
            'class_id.exists' => 'Selected class does not exist.',
        ];

        // Validation rules - file is optional on update
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'resource_type' => 'required|in:pdf,video,link,image,document',
            'subject' => 'nullable|string|max:100',
            'class_id' => 'nullable|exists:classes,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:10240',
            'video_url' => 'nullable|url|max:500',
            'external_link' => 'nullable|url|max:500',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        try {
            $filePath = $resource->file_path; // Keep existing file path by default
            $oldFilePath = $resource->file_path; // Store old path for cleanup

            // Handle new file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Additional file validation
                $maxSize = 10 * 1024 * 1024; // 10MB in bytes
                if ($file->getSize() > $maxSize) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'File size exceeds 10MB limit.');
                }

                // Validate file type based on resource type
                $resourceType = $request->resource_type;
                $allowedMimes = [];
                
                if ($resourceType === 'pdf') {
                    $allowedMimes = ['application/pdf'];
                } elseif ($resourceType === 'document') {
                    $allowedMimes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                } elseif ($resourceType === 'image') {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                }

                if (!empty($allowedMimes) && !in_array($file->getMimeType(), $allowedMimes)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Invalid file type for the selected resource type.');
                }

                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                
                // Store new file
                $filePath = $file->storeAs('learning-resources', $filename, 'public');
                
                if (!$filePath) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Failed to upload file. Please try again.');
                }

                // Delete old file if it exists and is local (not a URL)
                if ($oldFilePath && !filter_var($oldFilePath, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            } 
            // Handle video URL update
            elseif ($request->filled('video_url') && $request->resource_type === 'video') {
                $filePath = $request->video_url;
                
                // Validate video URL (YouTube or Vimeo)
                if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|vimeo\.com)\/.*/', $filePath)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Please provide a valid YouTube or Vimeo URL.');
                }

                // Delete old file if it was a local file
                if ($oldFilePath && !filter_var($oldFilePath, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            } 
            // Handle external link update
            elseif ($request->filled('external_link') && $request->resource_type === 'link') {
                $filePath = $request->external_link;

                // Delete old file if it was a local file
                if ($oldFilePath && !filter_var($oldFilePath, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            }
            // If resource type changed but no new file/url provided
            elseif ($request->resource_type !== $resource->resource_type) {
                // Check if new resource type requires a file/url
                if (in_array($request->resource_type, ['pdf', 'image', 'document'])) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Please upload a file for the selected resource type.');
                } elseif ($request->resource_type === 'video') {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Please provide a video URL for video resources.');
                } elseif ($request->resource_type === 'link') {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Please provide an external link.');
                }
            }

            // Update resource
            $resource->update([
                'title' => $request->title,
                'description' => $request->description,
                'file_path' => $filePath,
                'resource_type' => $request->resource_type,
                'class_id' => $request->class_id ?: null,
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
            // If new file was uploaded but update failed, delete the new file
            if (isset($filePath) && $filePath !== $oldFilePath && !filter_var($filePath, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            \Log::error('Learning resource update failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update learning resource: ' . $e->getMessage());
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