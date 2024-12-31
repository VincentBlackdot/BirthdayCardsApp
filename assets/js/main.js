document.addEventListener('DOMContentLoaded', function() {
    checkSelectedTemplates();
});

function checkSelectedTemplates() {
    const selectedTemplates = localStorage.getItem('selectedTemplates');
    if (selectedTemplates) {
        const templates = JSON.parse(selectedTemplates);
        // Clear storage after retrieving
        localStorage.removeItem('selectedTemplates');
        
        // Fetch and apply templates
        fetch(`api/template_operations.php?action=get_single&id=${templates.message}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    applyTemplate(data.template);
                }
            })
            .catch(error => showError('Failed to load selected template'));
    }
}

function applyTemplate(template) {
    const card = document.querySelector('.birthday-card');
    
    // Handle background image
    let imagePath = template.image_path;
    if (imagePath.startsWith('/')) {
        if (!imagePath.startsWith('/Bdaysphp')) {
            imagePath = '/Bdaysphp' + imagePath;
        }
    } else {
        imagePath = '/Bdaysphp/' + imagePath;
    }
    
    // Set background image with error handling
    card.style.backgroundImage = `url('${imagePath}')`;
    
    // Add error handler for image loading
    const img = new Image();
    img.onerror = () => {
        console.error('Failed to load image:', imagePath);
        card.style.backgroundColor = '#f8f9fa';
        card.innerHTML += '<div class="error-overlay">Image not found</div>';
    };
    img.src = imagePath;
    
    const designIcon = card.querySelector('.design-icon');
    designIcon.className = `design-icon design-${template.design}`;
    
    const content = card.querySelector('.card-content');
    content.textContent = template.message;
}

function previewCard() {
    const name = document.getElementById('name').value;
    const customNote = document.getElementById('customNote').value;

    console.log('Preview Card Input:', { name, customNote });

    if (!name) {
        showError('Please enter recipient name');
        return;
    }

    showPreloader();
    fetch('api/preview_card.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            name: name,
            custom_note: customNote
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Preview Card Response:', data);

        if (data.error) {
            throw new Error(data.error);
        }

        const card = document.querySelector('.birthday-card');
        
        // Handle background image path
        let imagePath = data.background;
        if (imagePath.startsWith('/')) {
            if (!imagePath.startsWith('/Bdaysphp')) {
                imagePath = '/Bdaysphp' + imagePath;
            }
        } else {
            imagePath = '/Bdaysphp/' + imagePath;
        }
        
        console.log('Setting background image:', imagePath);
        
        // Set background image with error handling
        card.style.backgroundImage = `url('${imagePath}')`;
        
        // Add error handler for image loading
        const img = new Image();
        img.onerror = () => {
            console.error('Failed to load image:', imagePath);
            card.style.backgroundColor = '#f8f9fa';
            card.innerHTML += '<div class="error-overlay">Image not found</div>';
        };
        img.src = imagePath;
        
        const designIcon = card.querySelector('.design-icon');
        designIcon.className = `design-icon design-${data.design}`;
        
        const content = card.querySelector('.card-content');
        content.innerHTML = data.message.replace(/\n/g, '<br>');
        
        hidePreloader();
    })
    .catch(error => {
        console.error('Preview error:', error);
        showError(error.message || 'Failed to generate preview');
        hidePreloader();
    });
}

function downloadCard() {
    const name = document.getElementById('name').value;
    if (!name) {
        showError('Please enter recipient name');
        return;
    }

    // Check if preview has been generated
    const card = document.querySelector('.birthday-card');
    const previewGenerated = card.querySelector('.card-content').innerHTML.trim() !== '';
    if (!previewGenerated) {
        showError('Please preview the card first');
        return;
    }

    showPreloader();
    
    // Create a full clone of the card for download
    const downloadCard = card.cloneNode(true);
    downloadCard.classList.add('download-mode');
    
    // Ensure full visibility and sizing
    downloadCard.style.position = 'fixed';
    downloadCard.style.top = '0';
    downloadCard.style.left = '0';
    downloadCard.style.width = '800px';
    downloadCard.style.height = '600px';
    downloadCard.style.transform = 'none';
    downloadCard.style.zIndex = '9999';
    
    // Temporarily add to body
    document.body.appendChild(downloadCard);

    // Use html2canvas with enhanced options
    html2canvas(downloadCard, {
        scale: 3, // Higher resolution
        useCORS: true,
        allowTaint: true,
        backgroundColor: null,
        logging: true,
        width: 800,
        height: 600,
        scrollX: 0,
        scrollY: -window.scrollY,
        imageTimeout: 0,
        onclone: (document, element) => {
            // Additional styling for download
            element.style.position = 'fixed';
            element.style.top = '0';
            element.style.left = '0';
            element.style.width = '800px';
            element.style.height = '600px';
            element.style.transform = 'none';
            element.style.zIndex = '9999';
        }
    }).then(canvas => {
        // Remove the temporary download card
        document.body.removeChild(downloadCard);

        // Create download link
        canvas.toBlob(function(blob) {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            const fileName = `birthday_card_${name}_${Date.now()}.png`;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            // Log the download activity
            fetch('api/log_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'card_downloaded',
                    details: {
                        recipient_name: name,
                        file_name: fileName,
                        template_id: document.querySelector('input[name="template"]:checked')?.value || null,
                        custom_note: document.getElementById('customNote').value || null
                    }
                })
            }).catch(error => console.error('Error logging download:', error));

            hidePreloader();
            showSuccess('Card downloaded successfully!');
        });
    }).catch(error => {
        console.error('Download error:', error);
        if (document.body.contains(downloadCard)) {
            document.body.removeChild(downloadCard);
        }
        hidePreloader();
        showError('Failed to generate download. Please try again.');
    });
}

function sendEmail() {
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const customNote = document.getElementById('customNote').value;

    // Validate inputs
    if (!name) {
        showError('Please enter recipient name');
        return;
    }

    if (!isValidEmail(email)) {
        showError('Please enter a valid email address');
        return;
    }

    // Check if preview has been generated
    const card = document.querySelector('.birthday-card');
    const previewGenerated = card.querySelector('.card-content').innerHTML.trim() !== '';
    if (!previewGenerated) {
        showError('Please preview the card first');
        return;
    }

    showPreloader();
    
    // Create a full clone of the card for image generation
    const emailCard = card.cloneNode(true);
    emailCard.classList.add('email-mode');
    
    // Ensure full visibility and sizing
    emailCard.style.position = 'fixed';
    emailCard.style.top = '0';
    emailCard.style.left = '0';
    emailCard.style.width = '800px';
    emailCard.style.height = '600px';
    emailCard.style.transform = 'none';
    emailCard.style.zIndex = '-9999';
    
    // Update text sizes for email
    const content = emailCard.querySelector('.card-content');
    if (content) {
        content.style.fontSize = '24px';  // Increase base font size
        const heading = content.querySelector('h1, h2, h3');
        if (heading) {
            heading.style.fontSize = '36px';  // Increase heading size
        }
    }
    
    // Temporarily add to body
    document.body.appendChild(emailCard);

    // Generate image
    html2canvas(emailCard, {
        scale: 3,
        useCORS: true,
        allowTaint: true,
        backgroundColor: null,
        logging: true,
        width: 800,
        height: 600,
        scrollX: 0,
        scrollY: -window.scrollY
    }).then(canvas => {
        // Remove temporary element
        document.body.removeChild(emailCard);
        
        // Get image data
        const cardImage = canvas.toDataURL('image/png');
        
        // Prepare request data
        const requestData = {
            name: name.trim(),
            email: email.trim(),
            custom_note: customNote.trim(),
            cardImage: cardImage
        };

        // Send to server
        return fetch('api/send_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        });
    })
    .then(response => {
        return response.text();
    })
    .then(text => {
        // Try to extract JSON from the response if there's other content
        const jsonMatch = text.match(/({[\s\S]*})/);
        const jsonStr = jsonMatch ? jsonMatch[0] : text;
        
        // Try to parse the response as JSON
        let data;
        try {
            data = JSON.parse(jsonStr);
        } catch (e) {
            console.error('Server response parse error:', e);
            console.error('Raw response that failed to parse:', text);
            throw new Error('Server returned invalid JSON response');
        }
        
        if (!data.success) {
            throw new Error(data.error || 'Unknown error occurred');
        }
        
        return data;
    })
    .then(data => {
        console.log('Send Email Response:', data);

        if (data.success) {
            showSuccess('Birthday card sent successfully!');
        } else {
            throw new Error(data.error || 'Failed to send email');
        }
        
        hidePreloader();
    })
    .catch(error => {
        console.error('Send Email Error:', error);
        showError(error.message || 'Failed to send email');
        hidePreloader();
    });
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showPreloader() {
    document.querySelector('.preloader-overlay').classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent scrolling while loading
}

function hidePreloader() {
    document.querySelector('.preloader-overlay').classList.remove('active');
    document.body.style.overflow = ''; // Restore scrolling
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
