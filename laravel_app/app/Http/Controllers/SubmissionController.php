<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Interview;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isCandidate()) {
            // Candidates can see their own submissions
            $submissions = Submission::where('candidate_id', $user->id)
                ->with(['interview', 'question', 'scores'])
                ->get();
        } else {
            // Admins and reviewers can see all submissions
            $submissions = Submission::with(['interview', 'question', 'candidate', 'scores'])
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $submissions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCandidate()) {
            return response()->json([
                'success' => false,
                'message' => 'Only candidates can submit answers'
            ], 403);
        }
        
        $request->validate([
            'interview_id' => 'required|exists:interviews,id',
            'question_id' => 'required|exists:questions,id',
            'answer_text' => 'nullable|string',
            'video' => 'nullable|file|mimes:webm,mp4,avi,mov|max:102400', // 100MB max
            'audio' => 'nullable|file|mimes:webm,mp3,wav|max:51200', // 50MB max
            'submission_time' => 'nullable|integer|min:1'
        ]);
        
        // Verify the question belongs to the interview
        $question = Question::where('id', $request->question_id)
            ->where('interview_id', $request->interview_id)
            ->first();
            
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found in this interview'
            ], 404);
        }
        
        // Check if interview is active
        $interview = Interview::find($request->interview_id);
        if ($interview->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Interview is not active'
            ], 400);
        }
        
        // Check if submission already exists
        $existingSubmission = Submission::where('interview_id', $request->interview_id)
            ->where('question_id', $request->question_id)
            ->where('candidate_id', $user->id)
            ->first();
            
        if ($existingSubmission) {
            return response()->json([
                'success' => false,
                'message' => 'Submission already exists for this question'
            ], 400);
        }
        
        $submissionData = [
            'interview_id' => $request->interview_id,
            'question_id' => $request->question_id,
            'candidate_id' => $user->id,
            'submission_time' => $request->submission_time,
            'status' => 'submitted'
        ];
        
        // Handle file uploads
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('submissions/videos', 'public');
            $submissionData['video_url'] = Storage::url($videoPath);
        }
        
        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')->store('submissions/audio', 'public');
            $submissionData['audio_url'] = Storage::url($audioPath);
        }
        
        if ($request->answer_text) {
            $submissionData['answer_text'] = $request->answer_text;
        }
        
        $submission = Submission::create($submissionData);
        $submission->load(['interview', 'question']);
        
        return response()->json([
            'success' => true,
            'message' => 'Submission created successfully',
            'data' => $submission
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $submission = Submission::with(['interview', 'question', 'candidate', 'scores.reviewer'])
            ->findOrFail($id);
        
        $user = Auth::user();
        
        // Check if user can view this submission
        if ($user->isCandidate() && $submission->candidate_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this submission'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $submission
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $submission = Submission::findOrFail($id);
        $user = Auth::user();
        
        // Only the candidate who made the submission can update it
        if ($submission->candidate_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this submission'
            ], 403);
        }
        
        // Only allow updates if status is pending
        if ($submission->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update submitted submission'
            ], 400);
        }
        
        $request->validate([
            'answer_text' => 'nullable|string',
            'video' => 'nullable|file|mimes:webm,mp4,avi,mov|max:102400',
            'audio' => 'nullable|file|mimes:webm,mp3,wav|max:51200',
            'submission_time' => 'nullable|integer|min:1'
        ]);
        
        $updateData = [];
        
        if ($request->hasFile('video')) {
            // Delete old video if exists
            if ($submission->video_url) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $submission->video_url));
            }
            $videoPath = $request->file('video')->store('submissions/videos', 'public');
            $updateData['video_url'] = Storage::url($videoPath);
        }
        
        if ($request->hasFile('audio')) {
            // Delete old audio if exists
            if ($submission->audio_url) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $submission->audio_url));
            }
            $audioPath = $request->file('audio')->store('submissions/audio', 'public');
            $updateData['audio_url'] = Storage::url($audioPath);
        }
        
        if ($request->has('answer_text')) {
            $updateData['answer_text'] = $request->answer_text;
        }
        
        if ($request->has('submission_time')) {
            $updateData['submission_time'] = $request->submission_time;
        }
        
        if (!empty($updateData)) {
            $updateData['status'] = 'submitted';
            $submission->update($updateData);
        }
        
        $submission->load(['interview', 'question']);
        
        return response()->json([
            'success' => true,
            'message' => 'Submission updated successfully',
            'data' => $submission
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $submission = Submission::findOrFail($id);
        $user = Auth::user();
        
        // Only the candidate who made the submission can delete it
        if ($submission->candidate_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this submission'
            ], 403);
        }
        
        // Delete associated files
        if ($submission->video_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $submission->video_url));
        }
        
        if ($submission->audio_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $submission->audio_url));
        }
        
        $submission->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Submission deleted successfully'
        ]);
    }
}