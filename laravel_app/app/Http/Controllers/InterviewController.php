<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            // Admins can see all interviews
            $interviews = Interview::with(['questions', 'submissions'])->get();
        } elseif ($user->isReviewer()) {
            // Reviewers can see active interviews
            $interviews = Interview::where('status', 'active')
                ->with(['questions', 'submissions'])
                ->get();
        } else {
            // Candidates can see active interviews
            $interviews = Interview::where('status', 'active')
                ->with(['questions'])
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $interviews
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canReview()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create interviews'
            ], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,active,completed,archived',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|in:video,audio,text',
            'questions.*.time_limit' => 'nullable|integer|min:30|max:600',
            'questions.*.is_required' => 'boolean',
            'questions.*.order' => 'integer'
        ]);
        
        $interview = Interview::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => $user->id
        ]);
        
        // Create questions
        foreach ($request->questions as $index => $questionData) {
            Question::create([
                'interview_id' => $interview->id,
                'question_text' => $questionData['text'],
                'question_type' => $questionData['type'],
                'time_limit' => $questionData['time_limit'] ?? null,
                'order' => $questionData['order'] ?? $index + 1,
                'is_required' => $questionData['is_required'] ?? true
            ]);
        }
        
        $interview->load('questions');
        
        return response()->json([
            'success' => true,
            'message' => 'Interview created successfully',
            'data' => $interview
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $interview = Interview::with(['questions', 'creator'])->findOrFail($id);
        
        $user = Auth::user();
        
        // Check if user can access this interview
        if ($interview->status === 'draft' && !$user->canReview()) {
            return response()->json([
                'success' => false,
                'message' => 'Interview not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $interview
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        
        if (!$user->canReview()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update interviews'
            ], 403);
        }
        
        $interview = Interview::findOrFail($id);
        
        // Only the creator or admin can update
        if ($interview->created_by !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this interview'
            ], 403);
        }
        
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:draft,active,completed,archived'
        ]);
        
        $interview->update($request->only(['title', 'description', 'status']));
        
        return response()->json([
            'success' => true,
            'message' => 'Interview updated successfully',
            'data' => $interview
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete interviews'
            ], 403);
        }
        
        $interview = Interview::findOrFail($id);
        $interview->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Interview deleted successfully'
        ]);
    }
}