document.addEventListener('DOMContentLoaded', function() {
    loadTemplates();
    setupImagePreview();
});

function loadTemplates() {
    fetch('api/template_operations.php?action=get_all')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.querySelector('.message-templates');
                container.innerHTML = ''; // Clear existing templates
                data.templates.forEach(template => {
                    const card = createTemplateCard(template);
                    container.appendChild(card);
                });
            } else {
                showError('Failed to load templates');
            }
        })
        .catch(error => showError('Failed to load templates'));
}

function setupImagePreview() {
    const imageInput = document.getElementById('templateImage');
    const preview = document.getElementById('templatePreview');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate file size
            if (file.size > 2 * 1024 * 1024) { // 2MB
                showError('File size exceeds maximum limit of 2MB');
                this.value = '';
                return;
            }

            // Validate file type
            if (!file.type.match('image.*')) {
                showError('Please select an image file (JPG, PNG, or GIF)');
                this.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="birthday-card" style="background-image: url('${e.target.result}')">
                        <div class="card-design">
                            <i class="design-icon design-${document.getElementById('templateDesign').value || 'stars'}"></i>
                        </div>
                        <div class="card-content">
                            ${document.getElementById('templateMessage').value || 'Your message will appear here'}
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<div class="preview-placeholder">Upload an image to see preview</div>';
        }
    });

    // Update preview when message or design changes
    document.getElementById('templateMessage').addEventListener('input', updatePreview);
    document.getElementById('templateDesign').addEventListener('change', updatePreview);
}

function updatePreview() {
    const preview = document.getElementById('templatePreview');
    const imageInput = document.getElementById('templateImage');
    
    if (imageInput.files && imageInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="birthday-card" style="background-image: url('${e.target.result}')">
                    <div class="card-design">
                        <i class="design-icon design-${document.getElementById('templateDesign').value || 'stars'}"></i>
                    </div>
                    <div class="card-content">
                        ${document.getElementById('templateMessage').value || 'Your message will appear here'}
                    </div>
                </div>
            `;
        };
        reader.readAsDataURL(imageInput.files[0]);
    }
}

function createTemplateCard(template) {
    const div = document.createElement('div');
    div.className = 'template-card';
    
    const card = document.createElement('div');
    card.className = 'birthday-card';
    
    // Add error handling for image path
    let imagePath = template.image_path;
    
    // Log the image path for debugging
    console.log('Template image path:', imagePath);
    
    // Make sure path starts with /Bdaysphp if it's a relative path
    if (imagePath.startsWith('/')) {
        if (!imagePath.startsWith('/Bdaysphp')) {
            imagePath = '/Bdaysphp' + imagePath;
        }
    } else {
        imagePath = '/Bdaysphp/' + imagePath;
    }
    
    console.log('Final image path:', imagePath);
            
    // Add error handling for background image
    try {
        card.style.backgroundImage = `url('${imagePath}')`;
        
        // Add error handler for image loading
        const img = new Image();
        img.onerror = () => {
            console.error('Failed to load image:', imagePath);
            card.style.backgroundColor = '#f8f9fa';
            card.innerHTML += '<div class="error-overlay">Image not found</div>';
        };
        img.src = imagePath;
    } catch (e) {
        console.error('Error setting background image:', e);
        card.style.backgroundColor = '#f8f9fa';
    }
    
    const designDiv = document.createElement('div');
    designDiv.className = 'card-design';
    designDiv.innerHTML = `<i class="design-icon design-${template.design}"></i>`;
    
    const contentDiv = document.createElement('div');
    contentDiv.className = 'card-content';
    contentDiv.textContent = template.message;
    
    card.appendChild(designDiv);
    card.appendChild(contentDiv);
    div.appendChild(card);
    
    div.addEventListener('click', () => selectTemplate(div, template));
    return div;
}

function uploadTemplate() {
    const form = document.getElementById('templateUploadForm');
    const formData = new FormData(form);

    // Debug logging
    console.log('Form data entries:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]));
    }

    // Validate form
    if (!validateTemplateForm(formData)) {
        return;
    }

    showPreloader();
    fetch('api/template_operations.php?action=upload', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        console.log('Raw response:', text);
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse JSON response:', text);
            throw new Error('Server returned invalid JSON');
        }
    })
    .then(data => {
        if (data.success) {
            showSuccess('Template uploaded successfully');
            form.reset();
            document.getElementById('templatePreview').innerHTML = 
                '<div class="preview-placeholder">Upload an image to see preview</div>';
            loadTemplates();
            bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
        } else {
            showError(data.message || 'Failed to upload template');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showError(error.message || 'Failed to upload template');
    })
    .finally(() => hidePreloader());
}

function validateTemplateForm(formData) {
    const name = formData.get('name');
    const message = formData.get('message');
    const design = formData.get('design');
    const image = formData.get('image');

    const missingFields = [];
    
    if (!name) missingFields.push('Template Name');
    if (!message) missingFields.push('Message');
    if (!design) missingFields.push('Design Icon');
    if (!image || image.size === 0) missingFields.push('Background Image');

    if (missingFields.length > 0) {
        showError(`Please fill in the following required fields: ${missingFields.join(', ')}`);
        return false;
    }

    // Case-insensitive check for [NAME] placeholder
    if (!message.toUpperCase().includes('[NAME]')) {
        showError('Message must include [NAME] or [name] placeholder');
        return false;
    }

    return true;
}

function selectTemplate(card, template) {
    // Clear previous selections
    document.querySelectorAll('.template-card').forEach(c => c.classList.remove('selected'));
    
    // Select this card
    card.classList.add('selected');
    
    // Log template selection
    fetch('api/log_activity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'template_selected',
            details: {
                template_id: template.id,
                template_name: template.name,
                design: template.design
            }
        })
    }).catch(error => console.error('Error logging template selection:', error));
}

function applySelectedTemplates() {
    const selectedTemplate = document.querySelector('.template-card.selected');
    if (!selectedTemplate) {
        showError('Please select a template first');
        return;
    }

    const templateInput = selectedTemplate.querySelector('input[name="template"]');
    const templateData = {
        id: templateInput.value,
        message: templateInput.dataset.message,
        background: templateInput.dataset.background,
        design: templateInput.dataset.design
    };

    // Log template application
    fetch('api/log_activity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'template_applied',
            details: {
                template_id: templateData.id,
                design: templateData.design
            }
        })
    }).catch(error => console.error('Error logging template application:', error));

    // Store the selected template data
    localStorage.setItem('selectedTemplate', JSON.stringify(templateData));
    
    // Redirect back to the generator
    window.location.href = 'index.php';
}

function showPreloader() {
    document.getElementById('preloader').classList.remove('d-none');
}

function hidePreloader() {
    document.getElementById('preloader').classList.add('d-none');
}

function showError(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').insertBefore(alert, document.querySelector('.container').firstChild);
}

function showSuccess(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').insertBefore(alert, document.querySelector('.container').firstChild);
}
