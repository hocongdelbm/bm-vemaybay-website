/**
 * Multiple File Upload Handler for Documents Module
 * Handles toggle between single/multiple file upload modes
 */

function toggleUploadMode() {
    var mode = document.querySelector('input[name="upload_mode"]:checked').value;
    var singleContainer = document.getElementById('single-upload-container');
    var multipleContainer = document.getElementById('multiple-upload-container');
    var docNameInput = document.getElementById('document_name');
    
    if (mode === 'single') {
        singleContainer.style.display = 'block';
        multipleContainer.style.display = 'none';
        if (docNameInput) {
            docNameInput.disabled = false;
            docNameInput.value = '';
        }
        document.getElementById('uploadfiles').value = '';
        document.getElementById('file-list').innerHTML = '';
        clearPreview();
    } else {
        singleContainer.style.display = 'none';
        multipleContainer.style.display = 'block';
        if (docNameInput) {
            docNameInput.disabled = true;
            docNameInput.value = '(Tự động theo tên file)';
        }
        var singleInput = document.getElementById('filename_file');
        if (singleInput) {
            singleInput.value = '';
        }
        clearPreview();
    }
}

function handleSingleFileSelect(input) {
    var docNameInput = document.getElementById('document_name');
    if (input.files.length > 0 && docNameInput && !docNameInput.disabled) {
        docNameInput.value = input.files[0].name;
    }
    
    // Show preview for single file
    if (input.files.length > 0) {
        showFilePreview(input.files[0]);
    } else {
        clearPreview();
    }
}

function handleMultipleFileSelect(input) {
    var fileList = document.getElementById('file-list');
    if (input.files.length > 0) {
        fileList.innerHTML = '<strong>Đã chọn ' + input.files.length + ' file</strong>';
        // Show preview for all image files
        showMultipleFilePreviews(input.files);
    } else {
        fileList.innerHTML = '';
        clearPreview();
    }
}

function showFilePreview(file) {
    var previewImagesContainer = document.getElementById('file-preview-images');
    var previewText = document.getElementById('file-preview-text');
    
    if (!previewImagesContainer || !previewText) return;
    
    // Clear previous preview
    previewImagesContainer.innerHTML = '';
    
    // Check if file is an image
    if (file.type.match('image.*')) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '300px';
            img.style.maxHeight = '300px';
            img.style.objectFit = 'contain';
            img.style.pointerEvents = 'none';
            img.style.userSelect = 'none';
            img.draggable = false;
            previewImagesContainer.appendChild(img);
            previewImagesContainer.style.display = 'flex';
            previewText.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        // Not an image, show file name instead
        previewImagesContainer.style.display = 'none';
        previewText.style.display = 'block';
        previewText.innerHTML = 'File đã chọn: <strong>' + file.name + '</strong>';
    }
}

function showMultipleFilePreviews(files) {
    var previewImagesContainer = document.getElementById('file-preview-images');
    var previewText = document.getElementById('file-preview-text');
    
    if (!previewImagesContainer || !previewText) return;
    
    // Clear previous previews
    previewImagesContainer.innerHTML = '';
    
    var hasImages = false;
    var imageCount = 0;
    
    // Loop through all files
    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        
        // Only process image files
        if (file.type.match('image.*')) {
            hasImages = true;
            imageCount++;
            
            // Use closure to capture the current file
            (function(currentFile) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var imgWrapper = document.createElement('div');
                    imgWrapper.style.display = 'flex';
                    imgWrapper.style.flexDirection = 'column';
                    imgWrapper.style.alignItems = 'center';
                    imgWrapper.style.flexShrink = '0';
                    imgWrapper.style.gap = '5px';
                    
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '200px';
                    img.style.maxHeight = '200px';
                    img.style.objectFit = 'contain';
                    img.style.border = '1px solid #ccc';
                    img.style.borderRadius = '4px';
                    img.style.pointerEvents = 'none'; // Prevent img drag interference
                    img.style.userSelect = 'none';
                    img.draggable = false;
                    
                    var fileName = document.createElement('div');
                    fileName.textContent = currentFile.name;
                    fileName.style.fontSize = '12px';
                    fileName.style.color = '#666';
                    fileName.style.maxWidth = '200px';
                    fileName.style.wordBreak = 'break-word';
                    fileName.style.textAlign = 'center';
                    fileName.style.padding = '2px 5px';
                    
                    imgWrapper.appendChild(img);
                    imgWrapper.appendChild(fileName);
                    previewImagesContainer.appendChild(imgWrapper);
                };
                reader.readAsDataURL(currentFile);
            })(file);
        }
    }
    
    if (hasImages) {
        previewImagesContainer.style.display = 'flex';
        previewText.style.display = 'none';
    } else {
        previewImagesContainer.style.display = 'none';
        previewText.style.display = 'block';
        previewText.innerHTML = 'Không có file ảnh nào được chọn';
    }
}

function clearPreview() {
    var previewImagesContainer = document.getElementById('file-preview-images');
    var previewText = document.getElementById('file-preview-text');
    
    if (previewImagesContainer) {
        previewImagesContainer.innerHTML = '';
        previewImagesContainer.style.display = 'none';
    }
    if (previewText) {
        previewText.style.display = 'block';
        previewText.innerHTML = 'Chưa chọn file';
    }
}

// ====== DRAG SCROLL FOR PREVIEW CONTAINER ======
// Enable drag-to-scroll horizontally on the preview container
(function initDragScroll() {
    var container = document.getElementById('file-preview-container');
    if (!container) return;
    
    var isDown = false;
    var startX;
    var scrollLeft;
    
    container.addEventListener('mousedown', function(e) {
        e.preventDefault(); // Prevent default drag behavior
        isDown = true;
        container.style.cursor = 'grabbing';
        container.style.userSelect = 'none';
        startX = e.pageX - container.offsetLeft;
        scrollLeft = container.scrollLeft;
    });
    
    container.addEventListener('mouseleave', function() {
        isDown = false;
        container.style.cursor = 'grab';
    });
    
    container.addEventListener('mouseup', function() {
        isDown = false;
        container.style.cursor = 'grab';
    });
    
    container.addEventListener('mousemove', function(e) {
        if (!isDown) return;
        e.preventDefault();
        var x = e.pageX - container.offsetLeft;
        var walk = (x - startX) * 2; // Scroll speed multiplier
        container.scrollLeft = scrollLeft - walk;
    });
    
    // Set initial cursor
    container.style.cursor = 'grab';
})();
