<?php
/**
 * HireGenius - Video Interview Page
 * Records video responses with text backup
 */
require_once '../includes/init.php';

// Check session
if (!isset($_SESSION['interview_candidate_id']) || !isset($_SESSION['interview_id'])) {
    redirect('join.php');
}

$interviewId = $_SESSION['interview_id'];
$interviewCandidateId = $_SESSION['interview_candidate_id'];
$candidateName = $_SESSION['candidate_name'];
$interviewTitle = $_SESSION['interview_title'];
$timePerQuestion = $_SESSION['time_per_question'] ?? 180;

// Fetch questions
$stmt = db()->prepare("SELECT id, question_text, question_order FROM questions WHERE interview_id = ? ORDER BY question_order");
$stmt->bind_param("i", $interviewId);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($questions)) {
    setFlash('error', 'No questions found for this interview.');
    redirect('join.php');
}

// Update status to started
$stmt = db()->prepare("UPDATE interview_candidates SET status = 'started', started_at = NOW() WHERE id = ? AND status = 'invited'");
$stmt->bind_param("i", $interviewCandidateId);
$stmt->execute();

$pageTitle = $interviewTitle . ' - HireGenius';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="interview-body">
    <!-- Camera Permission Modal -->
    <div class="modal-overlay" id="permission-modal">
        <div class="modal-content permission-modal">
            <div class="modal-icon">
                <i class="fas fa-video"></i>
            </div>
            <h2>Camera & Microphone Access Required</h2>
            <p>This interview requires video recording. Please allow access to your camera and microphone to proceed.</p>
            <div class="permission-tips">
                <h4><i class="fas fa-lightbulb"></i> Tips for a great interview:</h4>
                <ul>
                    <li><i class="fas fa-sun"></i> Ensure good lighting on your face</li>
                    <li><i class="fas fa-volume-up"></i> Find a quiet environment</li>
                    <li><i class="fas fa-wifi"></i> Check your internet connection</li>
                    <li><i class="fas fa-eye"></i> Look at the camera when speaking</li>
                </ul>
            </div>
            <button class="btn btn-primary btn-lg" id="request-permission-btn">
                <i class="fas fa-camera"></i> Allow Camera Access
            </button>
            <p class="permission-note">
                <i class="fas fa-lock"></i> Your privacy is important. Videos are only shared with the recruiter.
            </p>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal-overlay" id="error-modal" style="display: none;">
        <div class="modal-content error-modal">
            <div class="modal-icon error">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2>Camera Access Denied</h2>
            <p id="error-message">Unable to access camera. Please check your browser permissions and try again.</p>
            <div class="error-actions">
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-redo"></i> Try Again
                </button>
                <a href="join.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
            </div>
        </div>
    </div>

    <nav class="navbar interview-navbar">
        <div class="nav-brand">
            <span>Hire<span class="accent">Genius</span></span>
        </div>
        <div class="nav-info">
            <span class="interview-badge"><i class="fas fa-briefcase"></i> <?= e($interviewTitle) ?></span>
            <span class="candidate-info"><i class="fas fa-user"></i> <?= e($candidateName) ?></span>
        </div>
    </nav>

    <main class="interview-container video-interview" id="interview-main" style="display: none;">
        <div class="interview-layout">
            <!-- Left Panel - Video Preview -->
            <div class="video-panel">
                <div class="video-container">
                    <video id="video-preview" autoplay muted playsinline></video>
                    <div class="video-overlay" id="video-overlay">
                        <div class="recording-indicator" id="recording-indicator">
                            <span class="rec-dot"></span>
                            <span>REC</span>
                        </div>
                    </div>
                    <div class="video-controls">
                        <button class="video-control-btn" id="toggle-camera" title="Toggle Camera">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="video-control-btn" id="toggle-mic" title="Toggle Microphone">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                </div>
                <div class="video-status">
                    <div class="status-item" id="camera-status">
                        <i class="fas fa-video"></i>
                        <span>Camera Ready</span>
                    </div>
                    <div class="status-item" id="mic-status">
                        <i class="fas fa-microphone"></i>
                        <span>Microphone Ready</span>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Question & Controls -->
            <div class="question-panel">
                <div class="interview-progress">
                    <div class="progress-info">
                        <span class="progress-text">Question <span id="current-question">1</span> of <?= count($questions) ?></span>
                        <div class="timer" id="timer">
                            <i class="fas fa-clock"></i>
                            <span id="time-display"><?= floor($timePerQuestion / 60) ?>:<?= str_pad($timePerQuestion % 60, 2, '0', STR_PAD_LEFT) ?></span>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
                    </div>
                </div>

                <div class="question-card" id="question-card">
                    <div class="question-badge" id="question-number">
                        <i class="fas fa-question-circle"></i> Question 1
                    </div>
                    
                    <div class="question-text" id="question-text"></div>
                    
                    <div class="recording-status" id="recording-status">
                        <div class="status-preparing">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Preparing to record...</span>
                        </div>
                    </div>

                    <!-- Text Answer (Optional/Backup) -->
                    <div class="text-answer-section">
                        <label for="answer">
                            <i class="fas fa-keyboard"></i> Additional Notes (Optional):
                        </label>
                        <textarea id="answer" name="answer" rows="3" 
                                  placeholder="Add any additional notes here..."></textarea>
                    </div>
                    
                    <div class="question-actions">
                        <button type="button" id="start-recording-btn" class="btn btn-success btn-lg">
                            <i class="fas fa-circle"></i> Start Recording
                        </button>
                        <button type="button" id="stop-recording-btn" class="btn btn-danger btn-lg" style="display: none;">
                            <i class="fas fa-stop"></i> Stop Recording
                        </button>
                        <button type="button" id="next-btn" class="btn btn-primary btn-lg" style="display: none;">
                            Next Question <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Instructions Panel -->
                <div class="instructions-panel">
                    <h4><i class="fas fa-info-circle"></i> How it works:</h4>
                    <ol>
                        <li>Read the question carefully</li>
                        <li>Click "Start Recording" when ready</li>
                        <li>Record your video answer</li>
                        <li>Click "Stop Recording" when done</li>
                        <li>Move to the next question</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Complete Screen -->
        <div class="interview-complete" id="complete-screen" style="display: none;">
            <div class="complete-content">
                <div class="complete-animation">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Interview Complete!</h2>
                <p>Thank you for completing the interview, <strong><?= e($candidateName) ?></strong>.</p>
                <p>Your video responses have been successfully submitted.</p>
                <div class="complete-stats" id="complete-stats">
                    <div class="stat-item">
                        <i class="fas fa-question-circle"></i>
                        <span><?= count($questions) ?> Questions</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-video"></i>
                        <span id="videos-recorded">0 Videos</span>
                    </div>
                </div>
                <p class="redirect-notice">
                    <i class="fas fa-spinner fa-spin"></i> Redirecting in <span id="countdown">5</span> seconds...
                </p>
            </div>
        </div>
    </main>

    <!-- Upload Progress Modal -->
    <div class="modal-overlay" id="upload-modal" style="display: none;">
        <div class="modal-content upload-modal">
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <h3>Uploading Video...</h3>
            <div class="upload-progress">
                <div class="upload-progress-bar" id="upload-progress-bar"></div>
            </div>
            <p class="upload-text" id="upload-text">Please wait while your video is being uploaded.</p>
        </div>
    </div>

    <script>
        // Configuration
        const questions = <?= json_encode($questions) ?>;
        const interviewCandidateId = <?= $interviewCandidateId ?>;
        const timePerQuestion = <?= $timePerQuestion ?>;
        const totalQuestions = questions.length;
        
        // State variables
        let currentIndex = 0;
        let timeLeft = timePerQuestion;
        let timer = null;
        let startTime = null;
        let mediaRecorder = null;
        let mediaStream = null;
        let recordedChunks = [];
        let isRecording = false;
        let videosRecorded = 0;
        let cameraEnabled = true;
        let micEnabled = true;

        // DOM Elements
        const permissionModal = document.getElementById('permission-modal');
        const errorModal = document.getElementById('error-modal');
        const interviewMain = document.getElementById('interview-main');
        const videoPreview = document.getElementById('video-preview');
        const recordingIndicator = document.getElementById('recording-indicator');
        const startRecordingBtn = document.getElementById('start-recording-btn');
        const stopRecordingBtn = document.getElementById('stop-recording-btn');
        const nextBtn = document.getElementById('next-btn');
        const recordingStatus = document.getElementById('recording-status');
        const uploadModal = document.getElementById('upload-modal');

        // Request Camera Permission
        document.getElementById('request-permission-btn').addEventListener('click', async () => {
            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'user'
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                });
                
                videoPreview.srcObject = mediaStream;
                permissionModal.style.display = 'none';
                interviewMain.style.display = 'block';
                updateDisplay();
                
            } catch (err) {
                console.error('Camera access error:', err);
                permissionModal.style.display = 'none';
                errorModal.style.display = 'flex';
                document.getElementById('error-message').textContent = 
                    err.name === 'NotAllowedError' 
                        ? 'Camera access was denied. Please allow camera access in your browser settings and try again.'
                        : 'Could not access camera: ' + err.message;
            }
        });

        // Toggle Camera
        document.getElementById('toggle-camera').addEventListener('click', () => {
            const videoTrack = mediaStream.getVideoTracks()[0];
            if (videoTrack) {
                cameraEnabled = !cameraEnabled;
                videoTrack.enabled = cameraEnabled;
                const btn = document.getElementById('toggle-camera');
                btn.innerHTML = cameraEnabled ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
                btn.classList.toggle('disabled', !cameraEnabled);
                updateCameraStatus();
            }
        });

        // Toggle Microphone
        document.getElementById('toggle-mic').addEventListener('click', () => {
            const audioTrack = mediaStream.getAudioTracks()[0];
            if (audioTrack) {
                micEnabled = !micEnabled;
                audioTrack.enabled = micEnabled;
                const btn = document.getElementById('toggle-mic');
                btn.innerHTML = micEnabled ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
                btn.classList.toggle('disabled', !micEnabled);
                updateMicStatus();
            }
        });

        function updateCameraStatus() {
            const status = document.getElementById('camera-status');
            status.innerHTML = cameraEnabled 
                ? '<i class="fas fa-video"></i><span>Camera Ready</span>'
                : '<i class="fas fa-video-slash"></i><span>Camera Off</span>';
            status.classList.toggle('disabled', !cameraEnabled);
        }

        function updateMicStatus() {
            const status = document.getElementById('mic-status');
            status.innerHTML = micEnabled 
                ? '<i class="fas fa-microphone"></i><span>Microphone Ready</span>'
                : '<i class="fas fa-microphone-slash"></i><span>Microphone Off</span>';
            status.classList.toggle('disabled', !micEnabled);
        }

        function updateDisplay() {
            const question = questions[currentIndex];
            document.getElementById('question-number').innerHTML = 
                `<i class="fas fa-question-circle"></i> Question ${currentIndex + 1}`;
            document.getElementById('current-question').textContent = currentIndex + 1;
            document.getElementById('question-text').textContent = question.question_text;
            document.getElementById('answer').value = '';
            
            const progress = ((currentIndex) / totalQuestions) * 100;
            document.getElementById('progress-fill').style.width = progress + '%';
            
            // Reset buttons
            startRecordingBtn.style.display = 'inline-flex';
            stopRecordingBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            
            // Update status
            recordingStatus.innerHTML = `
                <div class="status-ready">
                    <i class="fas fa-play-circle"></i>
                    <span>Ready to record</span>
                </div>`;
            
            // Reset timer
            timeLeft = timePerQuestion;
            startTime = Date.now();
            updateTimer();
        }

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('time-display').textContent = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            const timerEl = document.getElementById('timer');
            timerEl.classList.remove('warning', 'danger');
            if (timeLeft <= 30) timerEl.classList.add('warning');
            if (timeLeft <= 10) timerEl.classList.add('danger');
        }

        function startTimer() {
            clearInterval(timer);
            timer = setInterval(() => {
                timeLeft--;
                updateTimer();
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    if (isRecording) {
                        stopRecording(true);
                    }
                }
            }, 1000);
        }

        // Start Recording
        startRecordingBtn.addEventListener('click', () => {
            startRecording();
        });

        function startRecording() {
            recordedChunks = [];
            
            try {
                mediaRecorder = new MediaRecorder(mediaStream, {
                    mimeType: 'video/webm;codecs=vp9,opus'
                });
            } catch (e) {
                mediaRecorder = new MediaRecorder(mediaStream, {
                    mimeType: 'video/webm'
                });
            }
            
            mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    recordedChunks.push(event.data);
                }
            };
            
            mediaRecorder.onstop = () => {
                isRecording = false;
                recordingIndicator.classList.remove('active');
                uploadVideo();
            };
            
            mediaRecorder.start(1000);
            isRecording = true;
            startTime = Date.now();
            
            recordingIndicator.classList.add('active');
            startRecordingBtn.style.display = 'none';
            stopRecordingBtn.style.display = 'inline-flex';
            
            recordingStatus.innerHTML = `
                <div class="status-recording">
                    <span class="rec-pulse"></span>
                    <span>Recording in progress...</span>
                </div>`;
            
            startTimer();
        }

        // Stop Recording
        stopRecordingBtn.addEventListener('click', () => {
            stopRecording(false);
        });

        function stopRecording(timedOut) {
            clearInterval(timer);
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
            
            stopRecordingBtn.style.display = 'none';
            recordingStatus.innerHTML = `
                <div class="status-processing">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Processing video...</span>
                </div>`;
        }

        function uploadVideo() {
            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            const question = questions[currentIndex];
            const timeTaken = Math.floor((Date.now() - startTime) / 1000);
            const textAnswer = document.getElementById('answer').value.trim();
            
            const formData = new FormData();
            formData.append('video', blob, `answer_${interviewCandidateId}_${question.id}.webm`);
            formData.append('interview_candidate_id', interviewCandidateId);
            formData.append('question_id', question.id);
            formData.append('answer_text', textAnswer);
            formData.append('time_taken', timeTaken);
            
            uploadModal.style.display = 'flex';
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload-video.php', true);
            
            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    document.getElementById('upload-progress-bar').style.width = percent + '%';
                    document.getElementById('upload-text').textContent = 
                        `Uploading... ${Math.round(percent)}%`;
                }
            };
            
            xhr.onload = () => {
                uploadModal.style.display = 'none';
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            videosRecorded++;
                            showNextButton();
                        } else {
                            alert('Upload failed: ' + response.message);
                            showNextButton();
                        }
                    } catch (e) {
                        console.error('Parse error:', e);
                        showNextButton();
                    }
                } else {
                    alert('Upload failed. Please try again.');
                    showNextButton();
                }
            };
            
            xhr.onerror = () => {
                uploadModal.style.display = 'none';
                alert('Upload failed. Please check your connection.');
                showNextButton();
            };
            
            xhr.send(formData);
        }

        function showNextButton() {
            recordingStatus.innerHTML = `
                <div class="status-complete">
                    <i class="fas fa-check-circle"></i>
                    <span>Video recorded successfully!</span>
                </div>`;
            
            nextBtn.style.display = 'inline-flex';
            nextBtn.innerHTML = currentIndex === totalQuestions - 1 
                ? '<i class="fas fa-flag-checkered"></i> Finish Interview'
                : 'Next Question <i class="fas fa-arrow-right"></i>';
        }

        // Next Question
        nextBtn.addEventListener('click', () => {
            currentIndex++;
            
            if (currentIndex >= totalQuestions) {
                completeInterview();
            } else {
                updateDisplay();
            }
        });

        function completeInterview() {
            document.querySelector('.interview-layout').style.display = 'none';
            document.getElementById('complete-screen').style.display = 'flex';
            document.getElementById('progress-fill').style.width = '100%';
            document.getElementById('videos-recorded').textContent = videosRecorded + ' Videos';
            
            // Stop media stream
            if (mediaStream) {
                mediaStream.getTracks().forEach(track => track.stop());
            }
            
            // Mark interview complete
            fetch('complete-interview.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    interview_candidate_id: interviewCandidateId
                })
            });
            
            // Countdown redirect
            let countdown = 5;
            const countdownEl = document.getElementById('countdown');
            const redirectInterval = setInterval(() => {
                countdown--;
                countdownEl.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(redirectInterval);
                    window.location.href = 'thank-you.php';
                }
            }, 1000);
        }

        // Prevent accidental navigation
        window.addEventListener('beforeunload', (e) => {
            if (currentIndex < totalQuestions && mediaStream) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>
