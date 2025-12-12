@if(config('services.feedback.enabled', true))
<div class="feedback-button">
    <button type="button" class="btn btn-primary rounded-pill shadow-lg" 
            data-bs-toggle="modal" data-bs-target="#feedbackModal">
        <i class="fas fa-comment-alt me-2"></i> Feedback
    </button>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Us Your Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('feedback.store') }}" method="POST" id="feedbackForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">What type of feedback do you have?</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="type_bug" value="bug" autocomplete="off" checked>
                            <label class="btn btn-outline-danger" for="type_bug">
                                <i class="fas fa-bug me-2"></i> Bug
                            </label>

                            <input type="radio" class="btn-check" name="type" id="type_feature" value="feature" autocomplete="off">
                            <label class="btn btn-outline-success" for="type_feature">
                                <i class="fas fa-lightbulb me-2"></i> Feature
                            </label>

                            <input type="radio" class="btn-check" name="type" id="type_suggestion" value="suggestion" autocomplete="off">
                            <label class="btn btn-outline-info" for="type_suggestion">
                                <i class="fas fa-comment me-2"></i> Suggestion
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="feedbackMessage" class="form-label">Your Feedback</label>
                        <textarea class="form-control" id="feedbackMessage" name="message" rows="5" 
                                required minlength="10" maxlength="2000"
                                placeholder="Please describe your feedback in detail..."></textarea>
                        <div class="form-text">Minimum 10 characters</div>
                    </div>

                    <input type="hidden" name="page_url" value="{{ url()->current() }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitFeedback">
                        <i class="fas fa-paper-plane me-2"></i> Send Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.feedback-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}
.feedback-button .btn {
    padding: 12px 24px;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}
.feedback-button .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('feedbackForm');
    const submitBtn = document.getElementById('submitFeedback');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
            
            // Submit form via AJAX
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    type: form.querySelector('input[name="type"]:checked').value,
                    message: form.querySelector('#feedbackMessage').value,
                    page_url: form.querySelector('input[name="page_url"]').value
                })
            })
            .then(response => response.json())
            .then(data => {
                // Show success message
                const modal = bootstrap.Modal.getInstance(document.getElementById('feedbackModal'));
                modal.hide();
                
                // Show AlpineJS toast notification
                window.Alpine.$dispatch('toast', { 
                    message: data.message || 'Thank you for your feedback!', 
                    type: 'success' 
                });
                
                // Reset form
                form.reset();
            })
            .catch(error => {
                console.error('Error:', error);
                window.Alpine.$dispatch('toast', { 
                    message: 'There was an error submitting your feedback. Please try again.', 
                    type: 'error' 
                });
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Send Feedback';
            });
        });
    });
</script>
@endpush
@endif
