<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->canReview()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view scores'
            ], 403);
        }
        
        $scores = Score::with(['submission.interview', 'submission.question', 'submission.candidate'])
            ->where('reviewer_id', $user->id)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $scores
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
                'message' => 'Only reviewers and admins can score submissions'
            ], 403);
        }
        
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'score' => 'required|integer|min:1|max:10',
            'comments' => 'nullable|string|max:1000',
            'recommend' => 'boolean'
        ]);
        
        $submission = Submission::findOrFail($request->submission_id);
        
        // Check if submission is submitted
        if ($submission->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot score pending submissions'
            ], 400);
        }
        
        // Check if reviewer has already scored this submission
        $existingScore = Score::where('submission_id', $request->submission_id)
            ->where('reviewer_id', $user->id)
            ->first();
            
        if ($existingScore) {
            return response()->json([
                'success' => false,
                'message' => 'You have already scored this submission'
            ], 400);
        }
        
        $score = Score::create([
            'submission_id' => $request->submission_id,
            'reviewer_id' => $user->id,
            'score' => $request->score,
            'comments' => $request->comments,
            'reviewed_at' => now()
        ]);
        
        // Update submission status to under_review if it's the first review
        $submission->update(['status' => 'under_review']);
        
        $score->load(['submission.interview', 'submission.question', 'submission.candidate']);
        
        return response()->json([
            'success' => true,
            'message' => 'Score submitted successfully',
            'data' => $score
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $score = Score::with(['submission.interview', 'submission.question', 'submission.candidate', 'reviewer'])
            ->findOrFail($id);
        
        $user = Auth::user();
        
        // Check if user can view this score
        if ($user->isCandidate()) {
            // Candidates can only see scores for their own submissions
            if ($score->submission->candidate_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this score'
                ], 403);
            }
        } elseif ($user->canReview()) {
            // Reviewers can see their own scores
            if ($score->reviewer_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this score'
                ], 403);
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $score
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $score = Score::findOrFail($id);
        $user = Auth::user();
        
        // Only the reviewer who created the score can update it
        if ($score->reviewer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this score'
            ], 403);
        }
        
        $request->validate([
            'score' => 'sometimes|required|integer|min:1|max:10',
            'comments' => 'nullable|string|max:1000',
            'recommend' => 'boolean'
        ]);
        
        $updateData = [];
        
        if ($request->has('score')) {
            $updateData['score'] = $request->score;
        }
        
        if ($request->has('comments')) {
            $updateData['comments'] = $request->comments;
        }
        
        if (!empty($updateData)) {
            $updateData['reviewed_at'] = now();
            $score->update($updateData);
        }
        
        $score->load(['submission.interview', 'submission.question', 'submission.candidate']);
        
        return response()->json([
            'success' => true,
            'message' => 'Score updated successfully',
            'data' => $score
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $score = Score::findOrFail($id);
        $user = Auth::user();
        
        // Only the reviewer who created the score can delete it
        if ($score->reviewer_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this score'
            ], 403);
        }
        
        $score->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Score deleted successfully'
        ]);
    }

    /**
     * Get scores for a specific submission
     */
    public function getSubmissionScores(string $submissionId)
    {
        $submission = Submission::findOrFail($submissionId);
        $user = Auth::user();
        
        // Check if user can view scores for this submission
        if ($user->isCandidate() && $submission->candidate_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view scores for this submission'
            ], 403);
        }
        
        $scores = Score::with('reviewer')
            ->where('submission_id', $submissionId)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $scores
        ]);
    }

    /**
     * Get statistics for a reviewer
     */
    public function getReviewerStats()
    {
        $user = Auth::user();
        
        if (!$user->canReview()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view reviewer statistics'
            ], 403);
        }
        
        $stats = [
            'total_reviews' => Score::where('reviewer_id', $user->id)->count(),
            'average_score' => Score::where('reviewer_id', $user->id)->avg('score'),
            'pending_reviews' => Submission::where('status', 'submitted')
                ->whereDoesntHave('scores', function($query) use ($user) {
                    $query->where('reviewer_id', $user->id);
                })
                ->count()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}