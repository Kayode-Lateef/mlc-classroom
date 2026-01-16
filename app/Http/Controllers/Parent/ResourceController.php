<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\LearningResource;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    /**
     * Display learning resources for parent's children
     */
    public function index(Request $request)
    {
        $parent = auth()->user();

        // Get parent's children
        $children = $parent->children()->where('status', 'active')->get();

        // If no children, show message
        if ($children->isEmpty()) {
            return view('parent.resources.index', [
                'children' => $children,
                'selectedChild' => null,
                'resources' => collect(),
                'stats' => null,
            ]);
        }

        // Select child (default to first child or from request)
        $selectedChildId = $request->get('child_id', $children->first()->id);
        $selectedChild = $children->firstWhere('id', $selectedChildId);

        // Verify child belongs to parent
        if (!$selectedChild || $selectedChild->parent_id !== $parent->id) {
            $selectedChild = $children->first();
        }

        // ============================================================
        // GET CHILD'S CLASS IDs
        // ============================================================
        
        $childClassIds = $selectedChild->classes()
            ->wherePivot('status', 'active')
            ->pluck('classes.id')
            ->toArray();

        // ============================================================
        // BUILD QUERY
        // ============================================================
        
        // Resources available to this child:
        // 1. General resources (class_id = null)
        // 2. Resources for child's enrolled classes
        $query = LearningResource::where(function($q) use ($childClassIds) {
            $q->whereNull('class_id') // General resources
              ->orWhereIn('class_id', $childClassIds); // Class-specific resources
        })->with(['uploader', 'class']);

        // Resource type filter
        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->resource_type);
        }

        // Subject filter
        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('subject', 'like', '%' . $search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $resources = $query->paginate(20);

        // ============================================================
        // STATISTICS
        // ============================================================
        
        $allResources = LearningResource::where(function($q) use ($childClassIds) {
            $q->whereNull('class_id')
              ->orWhereIn('class_id', $childClassIds);
        })->get();
        
        $stats = [
            'total' => $allResources->count(),
            'pdf' => $allResources->where('resource_type', 'pdf')->count(),
            'video' => $allResources->where('resource_type', 'video')->count(),
            'link' => $allResources->where('resource_type', 'link')->count(),
            'image' => $allResources->where('resource_type', 'image')->count(),
            'document' => $allResources->where('resource_type', 'document')->count(),
            'general' => $allResources->whereNull('class_id')->count(),
            'class_specific' => $allResources->whereNotNull('class_id')->count(),
            'by_class' => [],
            'by_subject' => [],
        ];

        // By class breakdown
        $enrolledClasses = $selectedChild->classes()
            ->wherePivot('status', 'active')
            ->with('teacher')
            ->get();

        foreach ($enrolledClasses as $class) {
            $classResources = LearningResource::where('class_id', $class->id)->count();
            
            if ($classResources > 0) {
                $stats['by_class'][] = [
                    'class' => $class,
                    'count' => $classResources,
                ];
            }
        }

        // By subject breakdown
        $subjects = $allResources->pluck('subject')->filter()->unique();
        foreach ($subjects as $subject) {
            $subjectCount = $allResources->where('subject', $subject)->count();
            $stats['by_subject'][] = [
                'subject' => $subject,
                'count' => $subjectCount,
            ];
        }

        // Sort by count
        usort($stats['by_subject'], function($a, $b) {
            return $b['count'] - $a['count'];
        });

        // ============================================================
        // FILTER OPTIONS
        // ============================================================
        
        $classes = $selectedChild->classes()
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get();

        $availableSubjects = LearningResource::where(function($q) use ($childClassIds) {
            $q->whereNull('class_id')
              ->orWhereIn('class_id', $childClassIds);
        })->distinct('subject')->pluck('subject')->filter()->sort()->values();

        return view('parent.resources.index', compact(
            'children',
            'selectedChild',
            'resources',
            'classes',
            'availableSubjects',
            'stats'
        ));
    }

    /**
     * Display specific resource details
     */
    public function show(LearningResource $resource)
    {
        $parent = auth()->user();

        // Get parent's children
        $children = $parent->children()->where('status', 'active')->get();

        if ($children->isEmpty()) {
            abort(403, 'You do not have permission to view this resource.');
        }

        // Check if resource is accessible to any of parent's children
        $hasAccess = false;

        // General resources are accessible to all
        if ($resource->class_id === null) {
            $hasAccess = true;
        } else {
            // Check if any child is enrolled in the resource's class
            foreach ($children as $child) {
                $isEnrolled = $child->classes()
                    ->wherePivot('status', 'active')
                    ->where('classes.id', $resource->class_id)
                    ->exists();
                
                if ($isEnrolled) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        if (!$hasAccess) {
            abort(403, 'You do not have permission to view this resource.');
        }

        // Load relationships
        $resource->load(['uploader', 'class']);

        // Determine which children can access this resource
        $accessibleChildren = collect();
        
        if ($resource->class_id === null) {
            // General resource - all children can access
            $accessibleChildren = $children;
        } else {
            // Class-specific - only enrolled children
            foreach ($children as $child) {
                $isEnrolled = $child->classes()
                    ->wherePivot('status', 'active')
                    ->where('classes.id', $resource->class_id)
                    ->exists();
                
                if ($isEnrolled) {
                    $accessibleChildren->push($child);
                }
            }
        }

        return view('parent.resources.show', compact(
            'resource',
            'children',
            'accessibleChildren'
        ));
    }

    /**
     * Download resource file
     */
    public function download(LearningResource $resource)
    {
        $parent = auth()->user();

        // Get parent's children
        $children = $parent->children()->where('status', 'active')->get();

        if ($children->isEmpty()) {
            abort(403, 'You do not have permission to download this resource.');
        }

        // Check if resource is accessible to any of parent's children
        $hasAccess = false;

        // General resources are accessible to all
        if ($resource->class_id === null) {
            $hasAccess = true;
        } else {
            // Check if any child is enrolled in the resource's class
            foreach ($children as $child) {
                $isEnrolled = $child->classes()
                    ->wherePivot('status', 'active')
                    ->where('classes.id', $resource->class_id)
                    ->exists();
                
                if ($isEnrolled) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        if (!$hasAccess) {
            abort(403, 'You do not have permission to download this resource.');
        }

        // Check if resource is a URL (video or external link)
        if (filter_var($resource->file_path, FILTER_VALIDATE_URL)) {
            // Redirect to external URL
            return redirect($resource->file_path);
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($resource->file_path)) {
            return redirect()->back()
                ->with('error', 'File not found. Please contact the administrator.');
        }

        try {
            // Log download activity
            ActivityLog::create([
                'user_id' => $parent->id,
                'action' => 'downloaded_resource',
                'model_type' => 'LearningResource',
                'model_id' => $resource->id,
                'description' => "Parent downloaded resource: {$resource->title}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Generate proper filename for download
            $extension = pathinfo($resource->file_path, PATHINFO_EXTENSION);
            $downloadName = $resource->title . '.' . $extension;

            return Storage::disk('public')->download(
                $resource->file_path,
                $downloadName
            );

        } catch (\Exception $e) {
            \Log::error('Resource download failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to download resource. Please try again.');
        }
    }
}