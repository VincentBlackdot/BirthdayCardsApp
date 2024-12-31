document.addEventListener('DOMContentLoaded', function() {
    setupPreviewForm();
});

function setupPreviewForm() {
    const form = document.getElementById('cardForm');
    if (!form) return;

    // Add event listener for the preview button
    const previewButton = form.querySelector('button[onclick="previewCard()"]');
    if (previewButton) {
        previewButton.addEventListener('click', function(e) {
            e.preventDefault();
            previewCard();
        });
    }
}

function previewCard() {
    const name = document.getElementById('name').value;
    const customNote = document.getElementById('customNote').value;

    if (!name) {
        showError('Please enter recipient name');
        return;
    }

    showPreloader();
    fetch('api/preview_card.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            name: name,
            custom_note: customNote
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }

        const preview = document.getElementById('previewArea');
        if (preview) {
            preview.innerHTML = `
                <div class="birthday-card" style="background-image: url('${data.background}')">
                    <div class="card-design">
                        <i class="design-icon design-${data.design}"></i>
                    </div>
                    <div class="card-content">
                        ${data.message}
                    </div>
                </div>
            `;
            hidePreloader();
            showSuccess('Preview generated successfully!');
        } else {
            throw new Error('Preview area not found');
        }
    })
    .catch(error => {
        hidePreloader();
        showError(error.message || 'Failed to generate preview');
    });
}

// Utility functions
function showPreloader() {
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        preloader.style.display = 'flex';
    }
}

function hidePreloader() {
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        preloader.style.display = 'none';
    }
}

function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
    }
}

function showSuccess(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
    }
}
