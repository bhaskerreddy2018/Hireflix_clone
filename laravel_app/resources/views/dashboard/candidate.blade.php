@extends('layouts.app')

@section('title', 'Candidate Dashboard - Hireflix Clone')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-user-graduate text-primary me-2"></i>My Interviews</h1>
            <div class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Complete your assigned interviews to showcase your skills
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clipboard-list fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Available Interviews</h5>
                <h3 class="text-primary" id="availableInterviews">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-video fa-2x text-success mb-2"></i>
                <h5 class="card-title">Completed</h5>
                <h3 class="text-success" id="completedInterviews">0</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h5 class="card-title">In Progress</h5>
                <h3 class="text-warning" id="inProgressInterviews">0</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Interview List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Interview Title</th>
                                <th>Description</th>
                                <th>Questions</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="interviewsTable">
                            <tr>
                                <td colspan="5" class="text-center text-muted">No interviews available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interview Modal -->
<div class="modal fade" id="interviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="interviewModalTitle">Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="interviewContent">
                    <!-- Interview content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Video Recording Modal -->
<div class="modal fade" id="videoRecordingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Your Answer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="videoContainer">
                    <video id="videoPreview" width="100%" height="300" autoplay muted></video>
                </div>
                <div class="mt-3">
                    <button id="startRecording" class="btn btn-success me-2">
                        <i class="fas fa-play me-1"></i>Start Recording
                    </button>
                    <button id="stopRecording" class="btn btn-danger me-2" disabled>
                        <i class="fas fa-stop me-1"></i>Stop Recording
                    </button>
                    <button id="playRecording" class="btn btn-info" disabled>
                        <i class="fas fa-play me-1"></i>Play Recording
                    </button>
                </div>
                <div class="mt-3">
                    <video id="recordedVideo" width="100%" height="300" controls style="display: none;"></video>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitRecording" disabled>Submit Answer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let mediaRecorder;
let recordedChunks = [];
let currentQuestion = null;
let currentInterview = null;

// Load dashboard data
async function loadDashboardData() {
    try {
        const response = await fetch('/candidate/interviews');
        const result = await response.json();
        
        if (result.success) {
            const interviews = result.data;
            
            // Count interviews by status
            let available = 0, completed = 0, inProgress = 0;
            
            interviews.forEach(interview => {
                if (interview.is_completed) {
                    completed++;
                } else if (interview.submissions_count > 0) {
                    inProgress++;
                } else {
                    available++;
                }
            });
            
            document.getElementById('availableInterviews').textContent = available;
            document.getElementById('completedInterviews').textContent = completed;
            document.getElementById('inProgressInterviews').textContent = inProgress;
            
            // Load interviews table
            const tbody = document.getElementById('interviewsTable');
            if (interviews.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No interviews available</td></tr>';
            } else {
                tbody.innerHTML = interviews.map(interview => {
                    const status = interview.is_completed ? 'completed' : 
                                  interview.submissions_count > 0 ? 'in_progress' : 'available';
                    const statusClass = status === 'completed' ? 'success' : 
                                       status === 'in_progress' ? 'warning' : 'primary';
                    const statusText = status === 'completed' ? 'Completed' : 
                                      status === 'in_progress' ? 'In Progress' : 'Available';
                    
                    return `
                        <tr>
                            <td>${interview.title}</td>
                            <td>${interview.description || 'No description'}</td>
                            <td>${interview.questions_count} questions</td>
                            <td>
                                <span class="badge bg-${statusClass}">${statusText}</span>
                                ${interview.submissions_count > 0 ? `<br><small>${interview.submissions_count}/${interview.questions_count} answered</small>` : ''}
                            </td>
                            <td>
                                ${status === 'completed' ? 
                                    '<span class="text-success"><i class="fas fa-check-circle"></i> Completed</span>' :
                                    `<button class="btn btn-primary btn-sm" onclick="startInterview(${interview.id})">
                                        <i class="fas fa-play me-1"></i>${status === 'in_progress' ? 'Continue' : 'Start'}
                                    </button>`
                                }
                            </td>
                        </tr>
                    `;
                }).join('');
            }
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

// Start interview
function startInterview(interviewId) {
    currentInterview = interviewId;
    // Load interview questions and start the interview process
    loadInterviewQuestions(interviewId);
}

// Load interview questions
async function loadInterviewQuestions(interviewId) {
    try {
        const response = await fetch('/candidate/interviews');
        const result = await response.json();
        
        if (result.success) {
            const interview = result.data.find(i => i.id == interviewId);
            if (interview && interview.questions) {
                displayInterviewQuestions(interview.questions, interview);
            } else {
                alert('Interview not found or no questions available');
            }
        }
    } catch (error) {
        console.error('Error loading interview questions:', error);
        alert('Error loading interview questions');
    }
}

// Display interview questions
function displayInterviewQuestions(questions, interview) {
    const modal = new bootstrap.Modal(document.getElementById('interviewModal'));
    const title = document.getElementById('interviewModalTitle');
    const content = document.getElementById('interviewContent');
    
    title.textContent = interview.title;
    
    
    // Calculate progress
    const totalQuestions = questions.length;
    const completedQuestions = interview.submissions ? interview.submissions.length : 0;
    const progressPercentage = totalQuestions > 0 ? (completedQuestions / totalQuestions) * 100 : 0;
    
    content.innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Please answer each question by recording a video response. You have a limited time for each question.
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Progress: ${completedQuestions}/${totalQuestions} questions completed</span>
                <span class="text-muted">${Math.round(progressPercentage)}%</span>
            </div>
            <div class="progress">
                <div class="progress-bar bg-success" role="progressbar" style="width: ${progressPercentage}%"></div>
            </div>
        </div>
        <div id="questionsList">
            ${questions.map((question, index) => {
                const submission = interview.submissions ? interview.submissions.find(s => s.question_id == question.id) : null;
                const isSubmitted = !!submission;
                
                
                return `
                    <div class="card mb-3 ${isSubmitted ? 'border-success' : ''}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title">Question ${index + 1}</h6>
                                    <p class="card-text">${question.question_text}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Time limit: ${question.time_limit || 'No limit'} seconds
                                    </small>
                                </div>
                                <div class="text-end">
                                    ${isSubmitted ? 
                                        '<i class="fas fa-check-circle text-success fa-2x mb-2"></i><br><span class="text-success"><i class="fas fa-check me-1"></i>Completed</span>' :
                                        `<button class="btn btn-primary btn-sm" onclick="startQuestion(${question.id}, '${question.question_text.replace(/'/g, "\\'")}', ${question.time_limit || 0})">
                                            <i class="fas fa-video me-1"></i>Record Answer
                                        </button>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('')}
        </div>
    `;
    
    modal.show();
}

// Start recording for a question
async function startQuestion(questionId, questionText, timeLimit) {
    currentQuestion = { id: questionId, text: questionText, timeLimit };
    
    // Clear previous recording data
    recordedChunks = [];
    
    // Reset UI elements
    const videoPreview = document.getElementById('videoPreview');
    const recordedVideo = document.getElementById('recordedVideo');
    const startBtn = document.getElementById('startRecording');
    const stopBtn = document.getElementById('stopRecording');
    const playBtn = document.getElementById('playRecording');
    const submitBtn = document.getElementById('submitRecording');
    
    // Clear previous video sources
    videoPreview.srcObject = null;
    recordedVideo.src = '';
    recordedVideo.style.display = 'none';
    
    // Reset button states
    startBtn.disabled = false;
    stopBtn.disabled = true;
    playBtn.disabled = true;
    submitBtn.disabled = true;
    
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        videoPreview.srcObject = stream;
        
        mediaRecorder = new MediaRecorder(stream);
        
        mediaRecorder.ondataavailable = function(event) {
            if (event.data.size > 0) {
                recordedChunks.push(event.data);
            }
        };
        
        mediaRecorder.onstop = function() {
            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            const url = URL.createObjectURL(blob);
            recordedVideo.src = url;
            recordedVideo.style.display = 'block';
            submitBtn.disabled = false;
            playBtn.disabled = false;
        };
        
        const modal = new bootstrap.Modal(document.getElementById('videoRecordingModal'));
        modal.show();
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        alert('Error accessing camera. Please make sure you have granted camera permissions.');
    }
}

// Event listeners for recording controls
document.getElementById('startRecording').addEventListener('click', function() {
    mediaRecorder.start();
    this.disabled = true;
    document.getElementById('stopRecording').disabled = false;
});

document.getElementById('stopRecording').addEventListener('click', function() {
    mediaRecorder.stop();
    this.disabled = true;
    document.getElementById('startRecording').disabled = false;
});

document.getElementById('playRecording').addEventListener('click', function() {
    const recordedVideo = document.getElementById('recordedVideo');
    recordedVideo.play();
});

document.getElementById('submitRecording').addEventListener('click', function() {
    if (currentQuestion && recordedChunks.length > 0) {
        // Submit the recording
        submitAnswer(currentQuestion.id, recordedChunks);
    }
});

// Submit answer
async function submitAnswer(questionId, recordingData) {
    try {
        const blob = new Blob(recordingData, { type: 'video/webm' });
        const formData = new FormData();
        formData.append('question_id', questionId);
        formData.append('interview_id', currentInterview);
        formData.append('video', blob, 'answer.webm');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const response = await fetch('/submissions', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            alert('Answer submitted successfully!');
            bootstrap.Modal.getInstance(document.getElementById('videoRecordingModal')).hide();
            
            // Close camera stream
            closeCamera();
            
            // Reload the interview questions to show updated status
            await loadInterviewQuestions(currentInterview);
            
            // Check if all questions are completed
            await checkInterviewCompletion();
            
            // Reload dashboard data
            loadDashboardData();
        } else {
            const errorText = await response.text();
            alert('Error submitting answer: ' + errorText);
        }
    } catch (error) {
        console.error('Error submitting answer:', error);
        alert('Error submitting answer');
    }
}

// Close camera stream
function closeCamera() {
    if (mediaRecorder && mediaRecorder.stream) {
        mediaRecorder.stream.getTracks().forEach(track => track.stop());
    }
    
    const videoPreview = document.getElementById('videoPreview');
    if (videoPreview && videoPreview.srcObject) {
        videoPreview.srcObject.getTracks().forEach(track => track.stop());
        videoPreview.srcObject = null;
    }
}

// Check if interview is completed
async function checkInterviewCompletion() {
    try {
        const response = await fetch('/candidate/interviews');
        const result = await response.json();
        
        if (result.success) {
            const interview = result.data.find(i => i.id == currentInterview);
            if (interview && interview.is_completed) {
                // Show completion alert
                alert('🎉 Congratulations! You have completed the interview successfully!');

                window.location.href = '/dashboard';
                
                // Close the interview modal after a short delay
                // setTimeout(() => {
                //     const interviewModal = bootstrap.Modal.getInstance(document.getElementById('interviewModal'));
                //     if (interviewModal) {
                //         interviewModal.hide();
                //     }
                // }, 100);
            }
        }
    } catch (error) {
        console.error('Error checking interview completion:', error);
    }
}

// Add event listener to close camera when modal is closed
document.addEventListener('DOMContentLoaded', function() {
    // Close camera when video recording modal is closed
    const videoModal = document.getElementById('videoRecordingModal');
    if (videoModal) {
        videoModal.addEventListener('hidden.bs.modal', function() {
            closeCamera();
        });
    }
    
    // Close camera when interview modal is closed
    const interviewModal = document.getElementById('interviewModal');
    if (interviewModal) {
        interviewModal.addEventListener('hidden.bs.modal', function() {
            closeCamera();
        });
    }
    
    loadDashboardData();
});
</script>
@endsection
