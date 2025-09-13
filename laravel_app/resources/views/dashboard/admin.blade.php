@extends('layouts.app')

@section('title', 'Admin Dashboard - Hireflix Clone')

@section('content')
<style>
.dashboard-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.2s ease;
    background: #fff;
}

.dashboard-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0,123,255,0.1);
}

.stat-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 1.5rem;
    text-align: center;
}

.stat-card .icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.stat-card .number {
    font-size: 2rem;
    font-weight: 600;
    margin: 0.5rem 0;
}

.stat-card .label {
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-classic {
    background: #007bff;
    border: 1px solid #007bff;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
}

.btn-classic:hover {
    background: #0056b3;
    border-color: #0056b3;
    color: white;
    text-decoration: none;
}

.table-classic {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    overflow: hidden;
}

.table-classic thead {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.table-classic th {
    border: none;
    padding: 1rem;
    font-weight: 600;
    color: #495057;
}

.table-classic td {
    border: none;
    padding: 1rem;
    border-bottom: 1px solid #f1f3f4;
}

.table-classic tbody tr:hover {
    background: #f8f9fa;
}
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1" style="color: #2c3e50; font-weight: 600;">Admin Dashboard</h2>
                    <p class="text-muted mb-0">Manage interviews, candidates, and submissions</p>
                </div>
                <button class="btn-classic" data-bs-toggle="modal" data-bs-target="#createInterviewModal">
                    <i class="fas fa-plus me-2"></i>Create Interview
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card dashboard-card" style="cursor: pointer;" onclick="showCandidatesModal()">
                <div class="icon text-primary">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="number text-primary" id="totalInterviews">0</div>
                <div class="label">Total Interviews</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card dashboard-card" style="cursor: pointer;" onclick="showCandidatesModal()">
                <div class="icon text-success">
                    <i class="fas fa-users"></i>
                </div>
                <div class="number text-success" id="totalCandidates">0</div>
                <div class="label">Total Candidates</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card dashboard-card" style="cursor: pointer;" onclick="showReviewersModal()">
                <div class="icon text-info">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="number text-info" id="totalReviewers">0</div>
                <div class="label">Total Reviewers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card dashboard-card" style="cursor: pointer;" onclick="showSubmissionsModal()">
                <div class="icon text-warning">
                    <i class="fas fa-video"></i>
                </div>
                <div class="number text-warning" id="totalSubmissions">0</div>
                <div class="label">Total Submissions</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 1rem 1.5rem;">
                    <h5 class="mb-0" style="color: #495057; font-weight: 600;">
                        <i class="fas fa-list me-2"></i>Recent Interviews
                    </h5>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="table table-classic mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Questions</th>
                                    <th>Submissions</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="interviewsTable">
                                <tr>
                                    <td colspan="6" class="text-center text-muted" style="padding: 2rem;">No interviews found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Interview Modal -->
<div class="modal fade" id="createInterviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createInterviewForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="interviewTitle" class="form-label">Interview Title</label>
                        <input type="text" class="form-control" id="interviewTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="interviewDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="interviewDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="interviewStatus" class="form-label">Status</label>
                        <select class="form-select" id="interviewStatus" name="status">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Questions</label>
                        <div id="questionsContainer">
                            <div class="question-item border p-3 mb-3 rounded">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control mb-2" name="questions[0][text]" placeholder="Question text" required>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select mb-2" name="questions[0][type]">
                                            <option value="video">Video</option>
                                            <option value="audio">Audio</option>
                                            <option value="text">Text</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" name="questions[0][time_limit]" placeholder="Time limit (seconds)" min="30" max="600">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="questions[0][is_required]" checked>
                                            <label class="form-check-label">Required</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addQuestion()">
                            <i class="fas fa-plus me-1"></i>Add Question
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Interview</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Candidates Modal -->
<div class="modal fade" id="candidatesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Candidates List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Submissions</th>
                                <th>Scores</th>
                                <th>Joined</th>
                                <th>Recent Activity</th>
                            </tr>
                        </thead>
                        <tbody id="candidatesTableBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reviewers Modal -->
<div class="modal fade" id="reviewersModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-check me-2"></i>Reviewers List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Reviews Given</th>
                                <th>Joined</th>
                                <th>Recent Activity</th>
                            </tr>
                        </thead>
                        <tbody id="reviewersTableBody">
                            <tr>
                                <td colspan="5" class="text-center text-muted">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submissions Modal -->
<div class="modal fade" id="submissionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-video me-2"></i>Submissions List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Candidate</th>
                                <th>Interview</th>
                                <th>Question</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Scores</th>
                            </tr>
                        </thead>
                        <tbody id="submissionsTableBody">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interview Details Modal -->
<div class="modal fade" id="interviewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clipboard-list me-2"></i>Interview Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="interviewDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading interview details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let questionIndex = 1;

function addQuestion() {
    const container = document.getElementById('questionsContainer');
    const questionHtml = `
        <div class="question-item border p-3 mb-3 rounded">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" class="form-control mb-2" name="questions[${questionIndex}][text]" placeholder="Question text" required>
                </div>
                <div class="col-md-3">
                    <select class="form-select mb-2" name="questions[${questionIndex}][type]">
                        <option value="video">Video</option>
                        <option value="audio">Audio</option>
                        <option value="text">Text</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <input type="number" class="form-control" name="questions[${questionIndex}][time_limit]" placeholder="Time limit (seconds)" min="30" max="600">
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="questions[${questionIndex}][is_required]" checked>
                        <label class="form-check-label">Required</label>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', questionHtml);
    questionIndex++;
}

function removeQuestion(button) {
    button.closest('.question-item').remove();
}

// Handle form submission
document.getElementById('createInterviewForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    
    // Add basic form data
    formData.append('title', document.getElementById('interviewTitle').value);
    formData.append('description', document.getElementById('interviewDescription').value);
    formData.append('status', document.getElementById('interviewStatus').value);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    // Process questions
    const questionItems = document.querySelectorAll('.question-item');
    questionItems.forEach((item, index) => {
        const text = item.querySelector('input[name*="[text]"]').value;
        const type = item.querySelector('select[name*="[type]"]').value;
        const timeLimit = item.querySelector('input[name*="[time_limit]"]').value;
        const isRequired = item.querySelector('input[name*="[is_required]"]').checked;
        
        if (text.trim()) {
            formData.append(`questions[${index}][text]`, text);
            formData.append(`questions[${index}][type]`, type);
            formData.append(`questions[${index}][time_limit]`, timeLimit || '');
            formData.append(`questions[${index}][is_required]`, isRequired ? '1' : '0');
            formData.append(`questions[${index}][order]`, index + 1);
        }
    });
    
    try {
        const response = await fetch('/interviews', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('createInterviewModal')).hide();
            location.reload();
        } else {
            const errorText = await response.text();
            alert('Error creating interview: ' + errorText);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error creating interview');
    }
});

// Load dashboard data
async function loadDashboardData() {
    try {
        // Load interviews
        const interviewsResponse = await fetch('/interviews');
        const interviewsResult = await interviewsResponse.json();
        
        if (interviewsResult.success) {
            const interviews = interviewsResult.data;
            document.getElementById('totalInterviews').textContent = interviews.length;
            
            // Load interviews table
            const tbody = document.getElementById('interviewsTable');
            if (interviews.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No interviews found</td></tr>';
            } else {
                tbody.innerHTML = interviews.map(interview => `
                    <tr>
                        <td>${interview.title}</td>
                        <td><span class="badge bg-${interview.status === 'active' ? 'success' : 'secondary'}">${interview.status}</span></td>
                        <td>${interview.questions ? interview.questions.length : 0}</td>
                        <td>${interview.submissions ? interview.submissions.length : 0}</td>
                        <td>${new Date(interview.created_at).toLocaleDateString()}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewInterview(${interview.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }
        
        // Load admin statistics
        const statsResponse = await fetch('/admin/stats');
        const statsResult = await statsResponse.json();
        
        if (statsResult.success) {
            const stats = statsResult.data;
            document.getElementById('totalCandidates').textContent = stats.total_candidates;
            document.getElementById('totalReviewers').textContent = stats.total_reviewers;
            document.getElementById('totalSubmissions').textContent = stats.total_submissions;
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

async function viewInterview(interviewId) {
    const modal = new bootstrap.Modal(document.getElementById('interviewDetailsModal'));
    modal.show();
    
    try {
        const response = await fetch(`/admin/interviews/${interviewId}`);
        const result = await response.json();
        
        if (result.success) {
            const interview = result.data;
            const content = document.getElementById('interviewDetailsContent');
            
            content.innerHTML = `
                <div class="row mb-4">
                    <div class="col-12">
                        <h4>${interview.title}</h4>
                        <p class="text-muted">${interview.description || 'No description provided'}</p>
                        <div class="d-flex gap-3">
                            <span class="badge bg-${interview.status === 'active' ? 'success' : 'secondary'}">${interview.status}</span>
                            <small class="text-muted">Created: ${new Date(interview.created_at).toLocaleDateString()}</small>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="text-primary">${interview.statistics.total_questions}</h5>
                                <small class="text-muted">Total Questions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="text-success">${interview.statistics.total_submissions}</h5>
                                <small class="text-muted">Total Submissions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="text-info">${interview.statistics.candidates_count}</h5>
                                <small class="text-muted">Candidates</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="text-warning">${interview.statistics.average_score}</h5>
                                <small class="text-muted">Avg Score</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-list me-2"></i>Questions</h5>
                        <div class="list-group">
                            ${interview.questions.map((question, index) => `
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Question ${index + 1}</h6>
                                            <p class="mb-1">${question.question_text}</p>
                                            <small class="text-muted">
                                                Type: ${question.question_type} | 
                                                Time Limit: ${question.time_limit || 'No limit'}s | 
                                                Required: ${question.is_required ? 'Yes' : 'No'}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h5><i class="fas fa-video me-2"></i>Recent Submissions</h5>
                        <div class="list-group" style="max-height: 400px; overflow-y: auto;">
                            ${interview.submissions.length > 0 ? interview.submissions.map(submission => {
                                const statusClass = submission.status === 'submitted' ? 'warning' : 
                                                  submission.status === 'under_review' ? 'info' : 
                                                  submission.status === 'reviewed' ? 'success' : 'secondary';
                                
                                const scoresHtml = submission.scores && submission.scores.length > 0 ? 
                                    submission.scores.map(score => `<span class="badge bg-primary me-1">${score.score}/10</span>`).join('') : 
                                    '<span class="text-muted">No scores</span>';
                                
                                return `
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">${submission.candidate.name}</h6>
                                                <p class="mb-1 text-muted">${submission.question.question_text.substring(0, 50)}${submission.question.question_text.length > 50 ? '...' : ''}</p>
                                                <small class="text-muted">
                                                    Submitted: ${new Date(submission.created_at).toLocaleDateString()}
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-${statusClass} mb-1">${submission.status}</span><br>
                                                ${scoresHtml}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('') : '<div class="list-group-item text-center text-muted">No submissions yet</div>'}
                        </div>
                    </div>
                </div>
            `;
        } else {
            document.getElementById('interviewDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading interview details: ${result.message}
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading interview details:', error);
        document.getElementById('interviewDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error loading interview details. Please try again.
            </div>
        `;
    }
}

// Show candidates modal
async function showCandidatesModal() {
    const modal = new bootstrap.Modal(document.getElementById('candidatesModal'));
    modal.show();
    
    try {
        const response = await fetch('/admin/candidates');
        const result = await response.json();
        
        if (result.success) {
            const candidates = result.data;
            const tbody = document.getElementById('candidatesTableBody');
            
            if (candidates.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No candidates found</td></tr>';
            } else {
                tbody.innerHTML = candidates.map(candidate => `
                    <tr>
                        <td>${candidate.name}</td>
                        <td>${candidate.email}</td>
                        <td><span class="badge bg-primary">${candidate.submissions_count}</span></td>
                        <td><span class="badge bg-info">${candidate.scores_count}</span></td>
                        <td>${new Date(candidate.created_at).toLocaleDateString()}</td>
                        <td>
                            ${candidate.submissions && candidate.submissions.length > 0 ? 
                                candidate.submissions.map(sub => `<small class="text-muted">${sub.interview.title}</small>`).join('<br>') : 
                                '<small class="text-muted">No recent activity</small>'
                            }
                        </td>
                    </tr>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading candidates:', error);
        document.getElementById('candidatesTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading candidates</td></tr>';
    }
}

// Show reviewers modal
async function showReviewersModal() {
    const modal = new bootstrap.Modal(document.getElementById('reviewersModal'));
    modal.show();
    
    try {
        const response = await fetch('/admin/reviewers');
        const result = await response.json();
        
        if (result.success) {
            const reviewers = result.data;
            const tbody = document.getElementById('reviewersTableBody');
            
            if (reviewers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No reviewers found</td></tr>';
            } else {
                tbody.innerHTML = reviewers.map(reviewer => `
                    <tr>
                        <td>${reviewer.name}</td>
                        <td>${reviewer.email}</td>
                        <td><span class="badge bg-info">${reviewer.scores_count}</span></td>
                        <td>${new Date(reviewer.created_at).toLocaleDateString()}</td>
                        <td>
                            ${reviewer.scores && reviewer.scores.length > 0 ? 
                                reviewer.scores.map(score => `<small class="text-muted">${score.submission.interview.title}</small>`).join('<br>') : 
                                '<small class="text-muted">No recent activity</small>'
                            }
                        </td>
                    </tr>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Error loading reviewers:', error);
        document.getElementById('reviewersTableBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading reviewers</td></tr>';
    }
}

// Show submissions modal
async function showSubmissionsModal() {
    const modal = new bootstrap.Modal(document.getElementById('submissionsModal'));
    modal.show();
    
    try {
        const response = await fetch('/admin/submissions');
        const result = await response.json();
        
        if (result.success) {
            const submissions = result.data;
            const tbody = document.getElementById('submissionsTableBody');
            
            if (submissions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No submissions found</td></tr>';
            } else {
                tbody.innerHTML = submissions.map(submission => {
                    const statusClass = submission.status === 'submitted' ? 'warning' : 
                                      submission.status === 'under_review' ? 'info' : 
                                      submission.status === 'completed' ? 'success' : 'secondary';
                    
                    const scoresHtml = submission.scores && submission.scores.length > 0 ? 
                        submission.scores.map(score => `<span class="badge bg-primary me-1">${score.score}/10</span>`).join('') : 
                        '<span class="text-muted">No scores yet</span>';
                    
                    return `
                        <tr>
                            <td>${submission.candidate.name}</td>
                            <td>${submission.interview.title}</td>
                            <td>${submission.question.question_text.substring(0, 50)}${submission.question.question_text.length > 50 ? '...' : ''}</td>
                            <td><span class="badge bg-secondary">${submission.question.question_type}</span></td>
                            <td><span class="badge bg-${statusClass}">${submission.status}</span></td>
                            <td>${new Date(submission.created_at).toLocaleDateString()}</td>
                            <td>${scoresHtml}</td>
                        </tr>
                    `;
                }).join('');
            }
        }
    } catch (error) {
        console.error('Error loading submissions:', error);
        document.getElementById('submissionsTableBody').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading submissions</td></tr>';
    }
}

// Load data when page loads
document.addEventListener('DOMContentLoaded', loadDashboardData);
</script>
@endsection
