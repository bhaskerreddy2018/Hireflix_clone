<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebController extends Controller
{
    /**
     * Show the login page
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Show the register page
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Show the dashboard based on user role
     */
    public function showDashboard()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        switch ($user->role) {
            case 'admin':
                return view('dashboard.admin');
            case 'reviewer':
                return view('dashboard.reviewer');
            case 'candidate':
                return view('dashboard.candidate');
            default:
                return redirect()->route('login');
        }
    }

    /**
     * Handle login form submission
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle register form submission
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
            'role' => 'required|string|in:admin,reviewer,candidate',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Create a new interview
     */
    public function createInterview(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canReview()) {
            return redirect()->back()->with('error', 'Unauthorized to create interviews');
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
        
        $interview = \App\Models\Interview::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => $user->id
        ]);
        
        // Create questions
        foreach ($request->questions as $index => $questionData) {
            \App\Models\Question::create([
                'interview_id' => $interview->id,
                'question_text' => $questionData['text'],
                'question_type' => $questionData['type'],
                'time_limit' => $questionData['time_limit'] ?? null,
                'order' => $questionData['order'] ?? $index + 1,
                'is_required' => $questionData['is_required'] ?? true
            ]);
        }
        
        return redirect()->back()->with('success', 'Interview created successfully!');
    }

    /**
     * Get interviews for AJAX requests
     */
    public function getInterviews()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $interviews = \App\Models\Interview::with(['questions', 'submissions'])->get();
        } elseif ($user->isReviewer()) {
            $interviews = \App\Models\Interview::where('status', 'active')
                ->with(['questions', 'submissions'])
                ->get();
        } else {
            $interviews = \App\Models\Interview::where('status', 'active')
                ->with(['questions'])
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $interviews
        ]);
    }

    /**
     * Get interviews for candidates
     */
    public function getCandidateInterviews()
    {
        $user = Auth::user();
        
        if (!$user->isCandidate()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Get active interviews
        $interviews = \App\Models\Interview::where('status', 'active')
            ->with(['questions'])
            ->get();
        
        // Get candidate's submissions to check progress
        $submissions = \App\Models\Submission::where('candidate_id', $user->id)
            ->with(['question'])
            ->get()
            ->groupBy('interview_id');
        
        // Add submission status to each interview
        $interviews->each(function ($interview) use ($submissions) {
            $interviewSubmissions = $submissions->get($interview->id, collect());
            $interview->submissions_count = $interviewSubmissions->count();
            $interview->questions_count = $interview->questions->count();
            $interview->is_completed = $interviewSubmissions->count() >= $interview->questions->count();
            // Convert collection to array for JSON serialization
            $interview->submissions = $interviewSubmissions->values()->toArray();
        });
        
        return response()->json([
            'success' => true,
            'data' => $interviews
        ]);
    }

    /**
     * Submit answer for a question
     */
    public function submitAnswer(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isCandidate()) {
            return redirect()->back()->with('error', 'Only candidates can submit answers');
        }
        
        $request->validate([
            'interview_id' => 'required|exists:interviews,id',
            'question_id' => 'required|exists:questions,id',
            'answer_text' => 'nullable|string',
            'video' => 'nullable|file|mimes:webm,mp4,avi,mov|max:102400',
            'audio' => 'nullable|file|mimes:webm,mp3,wav|max:51200',
            'submission_time' => 'nullable|integer|min:1'
        ]);
        
        // Verify the question belongs to the interview
        $question = \App\Models\Question::where('id', $request->question_id)
            ->where('interview_id', $request->interview_id)
            ->first();
            
        if (!$question) {
            return redirect()->back()->with('error', 'Question not found in this interview');
        }
        
        // Check if interview is active
        $interview = \App\Models\Interview::find($request->interview_id);
        if ($interview->status !== 'active') {
            return redirect()->back()->with('error', 'Interview is not active');
        }
        
        // Check if submission already exists
        $existingSubmission = \App\Models\Submission::where('interview_id', $request->interview_id)
            ->where('question_id', $request->question_id)
            ->where('candidate_id', $user->id)
            ->first();
            
        if ($existingSubmission) {
            return redirect()->back()->with('error', 'Submission already exists for this question');
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
            $submissionData['video_url'] = \Illuminate\Support\Facades\Storage::url($videoPath);
        }
        
        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')->store('submissions/audio', 'public');
            $submissionData['audio_url'] = \Illuminate\Support\Facades\Storage::url($audioPath);
        }
        
        if ($request->answer_text) {
            $submissionData['answer_text'] = $request->answer_text;
        }
        
        \App\Models\Submission::create($submissionData);
        
        return redirect()->back()->with('success', 'Answer submitted successfully!');
    }

    /**
     * Get submissions for reviewers
     */
    public function getReviewerSubmissions()
    {
        $user = Auth::user();
        
        if (!$user->canReview()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Get all submissions with related data
        $submissions = \App\Models\Submission::with([
            'interview:id,title',
            'question:id,question_text,question_type',
            'candidate:id,name',
            'scores' => function($query) use ($user) {
                $query->where('reviewer_id', $user->id);
            }
        ])
        ->where('status', 'submitted')
        ->orderBy('created_at', 'desc')
        ->get();
        
        // Add review status to each submission
        $submissions->each(function ($submission) use ($user) {
            $submission->is_reviewed_by_me = $submission->scores->isNotEmpty();
            $submission->my_score = $submission->scores->first();
        });
        
        return response()->json([
            'success' => true,
            'data' => $submissions
        ]);
    }

    /**
     * Submit score for a submission
     */
    public function submitScore(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canReview()) {
            return redirect()->back()->with('error', 'Only reviewers can score submissions');
        }
        
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'score' => 'required|integer|min:1|max:10',
            'comments' => 'nullable|string|max:1000',
            'recommend' => 'boolean'
        ]);
        
        $submission = \App\Models\Submission::findOrFail($request->submission_id);
        
        // Check if submission is submitted
        if ($submission->status !== 'submitted') {
            return redirect()->back()->with('error', 'Cannot score pending submissions');
        }
        
        // Check if reviewer has already scored this submission
        $existingScore = \App\Models\Score::where('submission_id', $request->submission_id)
            ->where('reviewer_id', $user->id)
            ->first();
            
        if ($existingScore) {
            return redirect()->back()->with('error', 'You have already scored this submission');
        }
        
        \App\Models\Score::create([
            'submission_id' => $request->submission_id,
            'reviewer_id' => $user->id,
            'score' => $request->score,
            'comments' => $request->comments,
            'reviewed_at' => now()
        ]);
        
        // Update submission status to under_review if it's the first review
        $submission->update(['status' => 'under_review']);
        
        return redirect()->back()->with('success', 'Score submitted successfully!');
    }

    /**
     * Get admin dashboard statistics
     */
    public function getAdminStats()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $stats = [
            'total_interviews' => \App\Models\Interview::count(),
            'total_candidates' => \App\Models\User::where('role', 'candidate')->count(),
            'total_reviewers' => \App\Models\User::where('role', 'reviewer')->count(),
            'total_submissions' => \App\Models\Submission::count()
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get reviewer dashboard statistics
     */
    public function getReviewerStats()
    {
        $user = Auth::user();
        
        if (!$user->canReview()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Get pending reviews (submissions that haven't been reviewed by this user)
        $pendingReviews = \App\Models\Submission::where('status', 'submitted')
            ->whereDoesntHave('scores', function($query) use ($user) {
                $query->where('reviewer_id', $user->id);
            })
            ->count();
        
        // Get completed reviews by this user
        $completedReviews = \App\Models\Score::where('reviewer_id', $user->id)->count();
        
        // Get unique candidates reviewed by this user
        $uniqueCandidatesReviewed = \App\Models\Score::where('reviewer_id', $user->id)
            ->join('submissions', 'scores.submission_id', '=', 'submissions.id')
            ->distinct('submissions.candidate_id')
            ->count('submissions.candidate_id');
        
        // Get total candidates
        $totalCandidates = \App\Models\User::where('role', 'candidate')->count();
        
        // Get average score given by this reviewer
        $averageScore = \App\Models\Score::where('reviewer_id', $user->id)->avg('score');
        
        $stats = [
            'pending_reviews' => $pendingReviews,
            'completed_reviews' => $completedReviews,
            'unique_candidates_reviewed' => $uniqueCandidatesReviewed,
            'total_candidates' => $totalCandidates,
            'average_score' => round($averageScore, 1) ?: 0.0
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get detailed candidates data for admin
     */
    public function getAdminCandidates()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $candidates = \App\Models\User::where('role', 'candidate')
            ->withCount(['submissions', 'scores'])
            ->with(['submissions' => function($query) {
                $query->with('interview:id,title')->latest()->limit(5);
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $candidates
        ]);
    }

    /**
     * Get detailed reviewers data for admin
     */
    public function getAdminReviewers()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $reviewers = \App\Models\User::where('role', 'reviewer')
            ->withCount(['scores'])
            ->with(['scores' => function($query) {
                $query->with('submission.interview:id,title')->latest()->limit(5);
            }])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $reviewers
        ]);
    }

    /**
     * Get detailed submissions data for admin
     */
    public function getAdminSubmissions()
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $submissions = \App\Models\Submission::with([
            'interview:id,title',
            'question:id,question_text,question_type',
            'candidate:id,name',
            'scores' => function($query) {
                $query->with('reviewer:id,name');
            }
        ])
        ->orderBy('created_at', 'desc')
        ->get();
        
        return response()->json([
            'success' => true,
            'data' => $submissions
        ]);
    }

    /**
     * Get detailed interview data for admin
     */
    public function getAdminInterviewDetails($id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $interview = \App\Models\Interview::with([
            'questions' => function($query) {
                $query->orderBy('order');
            },
            'submissions' => function($query) {
                $query->with(['candidate:id,name', 'question:id,question_text', 'scores' => function($q) {
                    $q->with('reviewer:id,name');
                }]);
            }
        ])->find($id);
        
        if (!$interview) {
            return response()->json([
                'success' => false,
                'message' => 'Interview not found'
            ], 404);
        }
        
        // Calculate statistics
        $totalSubmissions = $interview->submissions->count();
        $totalQuestions = $interview->questions->count();
        $completedSubmissions = $interview->submissions->where('status', 'submitted')->count();
        $underReviewSubmissions = $interview->submissions->where('status', 'under_review')->count();
        $reviewedSubmissions = $interview->submissions->where('status', 'reviewed')->count();
        
        // Get unique candidates who submitted
        $candidatesCount = $interview->submissions->pluck('candidate_id')->unique()->count();
        
        // Get average scores
        $allScores = $interview->submissions->flatMap->scores;
        $averageScore = $allScores->isNotEmpty() ? $allScores->avg('score') : 0;
        
        $interview->statistics = [
            'total_questions' => $totalQuestions,
            'total_submissions' => $totalSubmissions,
            'completed_submissions' => $completedSubmissions,
            'under_review_submissions' => $underReviewSubmissions,
            'reviewed_submissions' => $reviewedSubmissions,
            'candidates_count' => $candidatesCount,
            'average_score' => round($averageScore, 2)
        ];
        
        return response()->json([
            'success' => true,
            'data' => $interview
        ]);
    }
}