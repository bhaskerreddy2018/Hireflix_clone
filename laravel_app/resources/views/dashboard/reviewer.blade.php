@extends('layouts.app')

@section('title', 'Reviewer Dashboard - Hireflix Clone')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-user-check text-success me-2"></i>Review Dashboard</h1>
            <div class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Review and score candidate submissions
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-video fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Pending Reviews</h5>
                <h3 class="text-primary" id="pendingReviews">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h5 class="card-title">Completed Reviews</h5>
                <h3 class="text-success" id="completedReviews">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-users fa-2x text-info mb-2"></i>
                <h5 class="card-title">Total Candidates</h5>
                <h3 class="text-info" id="totalCandidates">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-star fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Average Score</h5>
                <h3 class="text-warning" id="averageScore">0.0</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Submissions to Review</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Interview</th>
                                <th>Question</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="submissionsTable">
                            <tr>
                                <td colspan="6" class="text-center text-muted">No submissions to review</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Candidate Response</h6>
                            </div>
                            <div class="card-body">
                                <div id="submissionContent">
                                    <!-- Submission content will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Scoring</h6>
                            </div>
                            <div class="card-body">
                                <form id="reviewForm">
                                    <div class="mb-3">
                                        <label for="score" class="form-label">Score (1-10)</label>
                                        <select class="form-select" id="score" name="score" required>
                                            <option value="">Select score</option>
                                            <option value="1">1 - Poor</option>
                                            <option value="2">2 - Below Average</option>
                                            <option value="3">3 - Below Average</option>
                                            <option value="4">4 - Below Average</option>
                                            <option value="5">5 - Average</option>
                                            <option value="6">6 - Average</option>
                                            <option value="7">7 - Good</option>
                                            <option value="8">8 - Good</option>
                                            <option value="9">9 - Excellent</option>
                                            <option value="10">10 - Outstanding</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="comments" class="form-label">Comments</label>
                                        <textarea class="form-control" id="comments" name="comments" rows="4" 
                                                  placeholder="Provide detailed feedback on the candidate's response..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="recommend" name="recommend">
                                            <label class="form-check-label" for="recommend">
                                                Recommend for next round
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitReview">Submit Review</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentSubmission = null;

// Load dashboard data
async function loadDashboardData() {
    try {
        // Load reviewer statistics
        const statsResponse = await fetch('/reviewer/stats');
        const statsResult = await statsResponse.json();
        
        if (statsResult.success) {
            const stats = statsResult.data;
            document.getElementById('pendingReviews').textContent = stats.pending_reviews;
            document.getElementById('completedReviews').textContent = stats.completed_reviews;
            document.getElementById('totalCandidates').textContent = stats.total_candidates;
            document.getElementById('averageScore').textContent = stats.average_score;
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

// Load submissions
async function loadSubmissions() {
    try {
        const response = await fetch('/reviewer/submissions');
        const result = await response.json();
        
        if (result.success) {
            displaySubmissions(result.data);
        } else {
            console.error('Error loading submissions:', result.message);
        }
    } catch (error) {
        console.error('Error loading submissions:', error);
    }
}

// Display submissions
function displaySubmissions(submissions) {
    const tbody = document.getElementById('submissionsTable');
    
    if (submissions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No submissions to review</td></tr>';
        return;
    }
    
    tbody.innerHTML = submissions.map(submission => {
        const isReviewed = submission.is_reviewed_by_me;
        const buttonText = isReviewed ? 'View Review' : 'Review';
        const buttonClass = isReviewed ? 'btn-outline-success' : 'btn-primary';
        const statusClass = submission.status === 'submitted' ? 'warning' : 'success';
        
        return `
            <tr>
                <td>${submission.candidate.name}</td>
                <td>${submission.interview.title}</td>
                <td>${submission.question.question_text}</td>
                <td>${new Date(submission.created_at).toLocaleDateString()}</td>
                <td>
                    <span class="badge bg-${statusClass}">${submission.status}</span>
                    ${isReviewed ? '<br><small class="text-success">Reviewed</small>' : ''}
                </td>
                <td>
                    <button class="btn ${buttonClass} btn-sm" onclick="reviewSubmission(${submission.id})">
                        <i class="fas fa-eye me-1"></i>${buttonText}
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// Review submission
async function reviewSubmission(submissionId) {
    currentSubmission = submissionId;
    
    try {
        const response = await fetch('/reviewer/submissions');
        const result = await response.json();
        
        if (result.success) {
            const submission = result.data.find(s => s.id == submissionId);
            if (submission) {
                displaySubmissionForReview(submission);
            } else {
                alert('Submission not found');
            }
        }
    } catch (error) {
        console.error('Error loading submission:', error);
        alert('Error loading submission');
    }
}

// Display submission for review
function displaySubmissionForReview(submission) {
    const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
    const content = document.getElementById('submissionContent');
    
    let answerHtml = '';
    if (submission.video_url) {
        answerHtml = `
            <video controls width="100%" height="300">
                <source src="${submission.video_url}" type="video/webm">
                Your browser does not support the video tag.
            </video>
        `;
    } else if (submission.audio_url) {
        answerHtml = `
            <audio controls style="width: 100%;">
                <source src="${submission.audio_url}" type="audio/webm">
                Your browser does not support the audio tag.
            </audio>
        `;
    } else if (submission.answer_text) {
        answerHtml = `<p class="form-control" style="height: 200px; overflow-y: auto;">${submission.answer_text}</p>`;
    } else {
        answerHtml = '<p class="text-muted">No answer provided</p>';
    }
    
    content.innerHTML = `
        <div class="mb-3">
            <h6>Candidate:</h6>
            <p class="text-muted">${submission.candidate.name}</p>
        </div>
        <div class="mb-3">
            <h6>Interview:</h6>
            <p class="text-muted">${submission.interview.title}</p>
        </div>
        <div class="mb-3">
            <h6>Question:</h6>
            <p class="text-muted">${submission.question.question_text}</p>
        </div>
        <div class="mb-3">
            <h6>Candidate Response:</h6>
            ${answerHtml}
        </div>
        <div class="mb-3">
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i>
                Submitted: ${new Date(submission.created_at).toLocaleString()}
            </small>
        </div>
    `;
    
    // Reset form and populate if already reviewed
    const form = document.getElementById('reviewForm');
    form.reset();
    
    if (submission.my_score) {
        document.getElementById('score').value = submission.my_score.score;
        document.getElementById('comments').value = submission.my_score.comments || '';
        document.getElementById('recommend').checked = submission.my_score.recommend || false;
    }
    
    modal.show();
}

// Submit review
document.getElementById('submitReview').addEventListener('click', async function() {
    const form = document.getElementById('reviewForm');
    const formData = new FormData(form);
    
    // Add submission ID and CSRF token
    formData.append('submission_id', currentSubmission);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    if (!formData.get('score')) {
        alert('Please select a score');
        return;
    }
    
    try {
        const response = await fetch('/scores', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            alert('Review submitted successfully!');
            bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
            loadSubmissions(); // Reload submissions
        } else {
            const errorText = await response.text();
            alert('Error submitting review: ' + errorText);
        }
    } catch (error) {
        console.error('Error submitting review:', error);
        alert('Error submitting review');
    }
});

// Load data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    loadSubmissions();
});
</script>
@endsection
