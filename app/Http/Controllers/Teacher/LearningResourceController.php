<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use App\Models\ClassModel;
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
        $teacher = auth()->user();

        $query = LearningResource::with(['uploader', 'class'])
            ->where('uploaded_by', $teacher->id); // ONLY teacher's resources

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

        // Get filter options (only teacher's classes)
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        $subjects = LearningResource::where('uploaded_by', $teacher->id)
            ->distinct('subject')
            ->pluck('subject')
            ->filter();

        // Statistics
        $stats = [
            'total_resources' => LearningResource::where('uploaded_by', $teacher->id)->count(),
            'pdf_count' => LearningResource::where('uploaded_by', $teacher->id)->where('resource_type', 'pdf')->count(),
            'video_count' => LearningResource::where('uploaded_by', $teacher->id)->where('resource_type', 'video')->count(),
            'general_resources' => LearningResource::where('uploaded_by', $teacher->id)->whereNull('class_id')->count(),
        ];

        return view('teacher.resources.index', compact(
            'resources',
            'classes',
            'subjects',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new resource
     */
    public function create()
    {
        $teacher = auth()->user();

        // Only teacher's classes
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        return view('teacher.resources.create', compact('classes'));
    }

    /**
     * Store a newly created resource
     */
    public function store(Request $request)
    {
        $teacher = auth()->user();

        // Custom validation messages
        $messages = [
            'title.required' => 'Resource title is required.',
            'title.max' => 'Title must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 1000 characters.',
            'resource_type.required' => 'Please select a resource type.',
            'resource_type.in' => 'Invalid resource type selected.',
            'subject.max' => 'Subject must not exceed 100 characters.',
            'class_id.exists' => 'Selected class does not exist.',
            'file.required_if' => 'Please upload a file for this resource type.',
            'file.file' => 'Invalid file uploaded.',
            'file.max' => 'File size must not exceed 10MB.',
            'file.mimes' => 'Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG, GIF.',
            'video_url.required_if' => 'Please provide a video URL for video resources.',
            'video_url.url' => 'Please provide a valid video URL.',
            'video_url.max' => 'Video URL must not exceed 500 characters.',
            'external_link.required_if' => 'Please provide an external link.',
            'external_link.url' => 'Please provide a valid URL.',
            'external_link.max' => 'External link must not exceed 500 characters.',
        ];

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

        // Verify class belongs to teacher (if provided)
        if ($request->filled('class_id')) {
            $class = ClassModel::where('id', $request->class_id)
                ->where('teacher_id', $teacher->id)
                ->firstOrFail();
        }

        try {
            $filePath = null;

            // Handle file upload for pdf, image, document
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Additional file validation
                if ($file->getSize() > 10485760) { // 10MB
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'File size exceeds 10MB limit.');
                }

                // Validate file type based on resource type
                $allowedMimes = [];
                if ($request->resource_type === 'pdf') {
                    $allowedMimes = ['application/pdf'];
                } elseif ($request->resource_type === 'document') {
                    $allowedMimes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                } elseif ($request->resource_type === 'image') {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                }

                if (!empty($allowedMimes) && !in_array($file->getMimeType(), $allowedMimes)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Invalid file type for the selected resource type.');
                }

                // Generate unique filename
                $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                
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
                'uploaded_by' => $teacher->id,
                'class_id' => $request->class_id ?: null,
                'subject' => $request->subject,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => $teacher->id,
                'action' => 'created_learning_resource',
                'model_type' => 'LearningResource',
                'model_id' => $resource->id,
                'description' => "Created learning resource: {$resource->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('teacher.resources.index')
                ->with('success', 'Learning resource created successfully!');

        } catch (\Exception $e) {
            // Delete uploaded file if exists
            if (isset($filePath) && $filePath && !filter_var($filePath, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($filePath);
            }
            
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
        $teacher = auth()->user();

        // Verify resource belongs to teacher
        if ($resource->uploaded_by !== $teacher->id) {
            abort(403, 'You do not have permission to view this resource.');
        }

        $resource->load(['uploader', 'class']);

        return view('teacher.resources.show', compact('resource'));
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit(LearningResource $resource)
    {
        $teacher = auth()->user();

        // Verify resource belongs to teacher
        if ($resource->uploaded_by !== $teacher->id) {
            abort(403, 'You do not have permission to edit this resource.');
        }

        // Only teacher's classes
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        return view('teacher.resources.edit', compact('resource', 'classes'));
    }

    /**
     * Update the specified resource
     */
    public function update(Request $request, LearningResource $resource)
    {
        $teacher = auth()->user();

        // Verify resource belongs to teacher
        if ($resource->uploaded_by !== $teacher->id) {
            abort(403, 'You do not have permission to edit this resource.');
        }

        // Same validation as store (file optional on update)
        $messages = [
            'title.required' => 'Resource title is required.',
            'title.max' => 'Title must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 1000 characters.',
            'resource_type.required' => 'Please select a resource type.',
            'resource_type.in' => 'Invalid resource type selected.',
            'subject.max' => 'Subject must not exceed 100 characters.',
            'class_id.exists' => 'Selected class does not exist.',
            'file.max' => 'File size must not exceed 10MB.',
            'file.mimes' => 'Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG, GIF.',
            'video_url.url' => 'Please provide a valid video URL.',
            'video_url.max' => 'Video URL must not exceed 500 characters.',
            'external_link.url' => 'Please provide a valid URL.',
            'external_link.max' => 'External link must not exceed 500 characters.',
        ];

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

        // Verify class belongs to teacher (if provided)
        if ($request->filled('class_id')) {
            $class = ClassModel::where('id', $request->class_id)
                ->where('teacher_id', $teacher->id)
                ->firstOrFail();
        }

        try {
            $filePath = $resource->file_path;
            $oldFilePath = $resource->file_path;

            // Handle new file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                if ($file->getSize() > 10485760) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'File size exceeds 10MB limit.');
                }

                $allowedMimes = [];
                if ($request->resource_type === 'pdf') {
                    $allowedMimes = ['application/pdf'];
                } elseif ($request->resource_type === 'document') {
                    $allowedMimes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                } elseif ($request->resource_type === 'image') {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                }

                if (!empty($allowedMimes) && !in_array($file->getMimeType(), $allowedMimes)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Invalid file type for the selected resource type.');
                }

                $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                
                $filePath = $file->storeAs('learning-resources', $filename, 'public');
                
                if (!$filePath) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Failed to upload file. Please try again.');
                }

                // Delete old file
                if ($oldFilePath && !filter_var($oldFilePath, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            } 
            // Handle video URL update
            elseif ($request->filled('video_url') && $request->resource_type === 'video') {
                $filePath = $request->video_url;
                
                if (!preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|vimeo\.com)\/.*/', $filePath)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Please provide a valid YouTube or Vimeo URL.');
                }

                if ($oldFilePath && !filter_var($oldFilePath, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            } 
            // Handle external link update
            elseif ($request->filled('external_link') && $request->resource_type === 'link') {
                $filePath = $request->external_link;

                if ($oldFilePath && !filter_var($oldFilePath, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
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
                'user_id' => $teacher->id,
                'action' => 'updated_learning_resource',
                'model_type' => 'LearningResource',
                'model_id' => $resource->id,
                'description' => "Updated learning resource: {$resource->title}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('teacher.resources.index')
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
        $teacher = auth()->user();

        // Verify resource belongs to teacher
        if ($resource->uploaded_by !== $teacher->id) {
            abort(403, 'You do not have permission to delete this resource.');
        }

        try {
            $title = $resource->title;
            $id = $resource->id;

            // Delete file if exists and is local
            if ($resource->file_path && !filter_var($resource->file_path, FILTER_VALIDATE_URL)) {
                if (Storage::disk('public')->exists($resource->file_path)) {
                    Storage::disk('public')->delete($resource->file_path);
                }
            }

            $resource->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => $teacher->id,
                'action' => 'deleted_learning_resource',
                'model_type' => 'LearningResource',
                'model_id' => $id,
                'description' => "Deleted learning resource: {$title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('teacher.resources.index')
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
        $teacher = auth()->user();

        // Verify resource belongs to teacher
        if ($resource->uploaded_by !== $teacher->id) {
            abort(403, 'You do not have permission to download this file.');
        }

        // Check if file is URL (video/link)
        if (filter_var($resource->file_path, FILTER_VALIDATE_URL)) {
            return redirect($resource->file_path);
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($resource->file_path)) {
            return redirect()->back()
                ->with('error', 'File not found.');
        }

        // Log activity
        ActivityLog::create([
            'user_id' => $teacher->id,
            'action' => 'downloaded_learning_resource',
            'model_type' => 'LearningResource',
            'model_id' => $resource->id,
            'description' => "Downloaded learning resource: {$resource->title}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::disk('public')->download(
            $resource->file_path,
            $resource->title . '.' . pathinfo($resource->file_path, PATHINFO_EXTENSION)
        );
    }
}