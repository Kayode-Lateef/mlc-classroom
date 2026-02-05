<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\HomeworkTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ActivityLog;

class HomeworkTopicController extends Controller
{
    /**
     * Display a listing of topics
     */
    public function index()
    {
        $topics = HomeworkTopic::orderBy('subject')->orderBy('name')->paginate(20);
        
        return view('superadmin.homework-topics.index', compact('topics'));
    }

    /**
     * Show the form for creating a new topic
     */
    public function create()
    {
        return view('superadmin.homework-topics.create');
    }

    /**
     * Store a newly created topic
     */
    public function store(Request $request)
    {
        // ✅ FIXED: Changed validation for checkbox
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:homework_topics,name',
            'description' => 'nullable|string|max:1000',
            'subject' => 'nullable|string|max:100',
            'is_active' => 'nullable|in:on,1,true', // Checkbox sends 'on', '1', or nothing
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors.');
        }

        try {
            // ✅ FIXED: Convert checkbox value to 1 or 0
            $topic = HomeworkTopic::create([
                'name' => $request->name,
                'description' => $request->description,
                'subject' => $request->subject,
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'created_topic',
                'model_type' => 'HomeworkTopic',
                'model_id' => $topic->id,
                'description' => "Created homework topic: {$topic->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('superadmin.homework-topics.index')
                ->with('success', 'Homework topic created successfully!');

        } catch (\Exception $e) {
            \Log::error('Topic creation failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create topic. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified topic
     */
    public function edit(HomeworkTopic $homeworkTopic)
    {
        return view('superadmin.homework-topics.edit', compact('homeworkTopic'));
    }

    /**
     * Update the specified topic
     */
    public function update(Request $request, HomeworkTopic $homeworkTopic)
    {
        // ✅ FIXED: Changed validation for checkbox
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:homework_topics,name,' . $homeworkTopic->id,
            'description' => 'nullable|string|max:1000',
            'subject' => 'nullable|string|max:100',
            'is_active' => 'nullable|in:on,1,true', // Checkbox sends 'on', '1', or nothing
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors.');
        }

        try {
            // ✅ FIXED: Convert checkbox value to 1 or 0
            $homeworkTopic->update([
                'name' => $request->name,
                'description' => $request->description,
                'subject' => $request->subject,
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'updated_topic',
                'model_type' => 'HomeworkTopic',
                'model_id' => $homeworkTopic->id,
                'description' => "Updated homework topic: {$homeworkTopic->name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('superadmin.homework-topics.index')
                ->with('success', 'Homework topic updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Topic update failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update topic. Please try again.');
        }
    }

    /**
     * Remove the specified topic
     */
    public function destroy(HomeworkTopic $homeworkTopic)
    {
        try {
            // Check if topic is used in any homework
            if ($homeworkTopic->homeworkAssignments()->count() > 0) {
                return redirect()->back()
                    ->with('warning', 'Cannot delete topic as it is assigned to homework assignments.');
            }

            $name = $homeworkTopic->name;
            $homeworkTopic->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'deleted_topic',
                'model_type' => 'HomeworkTopic',
                'model_id' => $homeworkTopic->id,
                'description' => "Deleted homework topic: {$name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('superadmin.homework-topics.index')
                ->with('success', 'Homework topic deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Topic deletion failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete topic. Please try again.');
        }
    }
}