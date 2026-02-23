/**
 * Multiple File Upload Handler for Documents Module
 * Handles toggle between single/multiple file upload modes with drag-drop and paste support
 */

// Global file storage
var fileStorage = {
    files: [],
    currentMode: 'single'
};

function toggleUploadMode() {
    var mode = document.querySelector('input[name="upload_mode"]:checked').value;
    var singleContainer = document.getElementById('single-upload-container');
    var multipleContainer = document.getElementById('multiple-upload-container');
    var docNameInput = document.getElementById('document_name');
    
    fileStorage.currentMode = mode;
    fileStorage.files = []; // Clear files when switching modes
    
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
    if (input.files.length > 0) {
        // Replace files (user explicitly selected from dialog)
        fileStorage.files = [input.files[0]];
        updateFileInput('filename_file', fileStorage.files);
        
        var docNameInput = document.getElementById('document_name');
        if (docNameInput && !docNameInput.disabled) {
            docNameInput.value = input.files[0].name;
        }
        
        // Show preview
        renderFilePreviews();
    } else {
        // Don't clear if we already have files (user cancelled dialog)
        if (fileStorage.files.length === 0) {
            clearPreview();
        }
        // If we have files, keep them and update the input
        else {
            updateFileInput('filename_file', fileStorage.files);
        }
    }
}

function handleMultipleFileSelect(input) {
    if (input.files.length > 0) {
        // Replace files (user explicitly selected from dialog)
        fileStorage.files = Array.from(input.files);
        updateFileInput('uploadfiles', fileStorage.files);
        
        var fileList = document.getElementById('file-list');
        fileList.innerHTML = '<strong>Đã chọn ' + fileStorage.files.length + ' file</strong>';
        
        // Show preview
        renderFilePreviews();
    } else {
        // Don't clear if we already have files (user cancelled dialog)
        if (fileStorage.files.length === 0) {
            clearPreview();
        }
        // If we have files, keep them and update the input
        else {
            updateFileInput('uploadfiles', fileStorage.files);
            var fileList = document.getElementById('file-list');
            if (fileList) {
                fileList.innerHTML = '<strong>Đã chọn ' + fileStorage.files.length + ' file</strong>';
            }
        }
    }
}

function addFilesToStorage(newFiles) {
    // Add new files to existing files
    for (var i = 0; i < newFiles.length; i++) {
        fileStorage.files.push(newFiles[i]);
    }
    
    // Auto-switch to multiple mode if more than 1 file
    if (fileStorage.files.length > 1 && fileStorage.currentMode === 'single') {
        // Clear old single file input before switching
        var oldInput = document.getElementById('filename_file');
        if (oldInput) {
            oldInput.value = '';
        }
        
        // Switch to multiple mode
        var multipleRadio = document.querySelector('input[name="upload_mode"][value="multiple"]');
        if (multipleRadio) {
            multipleRadio.checked = true;
            fileStorage.currentMode = 'multiple';
            
            var singleContainer = document.getElementById('single-upload-container');
            var multipleContainer = document.getElementById('multiple-upload-container');
            var docNameInput = document.getElementById('document_name');
            
            singleContainer.style.display = 'none';
            multipleContainer.style.display = 'block';
            
            if (docNameInput) {
                docNameInput.disabled = true;
                docNameInput.value = '(Tự động theo tên file)';
            }
        }
    }
    
    // Update the file input
    var targetInput = fileStorage.currentMode === 'single' ? 'filename_file' : 'uploadfiles';
    updateFileInput(targetInput, fileStorage.files);
    
    // Update file list display
    if (fileStorage.currentMode === 'multiple') {
        var fileList = document.getElementById('file-list');
        if (fileList) {
            fileList.innerHTML = '<strong>Đã chọn ' + fileStorage.files.length + ' file</strong>';
        }
    } else if (fileStorage.currentMode === 'single') {
        // Update document name for single file
        var docNameInput = document.getElementById('document_name');
        if (docNameInput && !docNameInput.disabled && fileStorage.files.length > 0) {
            docNameInput.value = fileStorage.files[0].name;
        }
    }
}

function removeFileFromStorage(index) {
    fileStorage.files.splice(index, 1);
    
    // Update file input
    var targetInput = fileStorage.currentMode === 'single' ? 'filename_file' : 'uploadfiles';
    updateFileInput(targetInput, fileStorage.files);
    
    // Update display
    if (fileStorage.files.length === 0) {
        clearPreview();
        if (fileStorage.currentMode === 'multiple') {
            var fileList = document.getElementById('file-list');
            if (fileList) {
                fileList.innerHTML = '';
            }
        } else if (fileStorage.currentMode === 'single') {
            var docNameInput = document.getElementById('document_name');
            if (docNameInput && !docNameInput.disabled) {
                docNameInput.value = '';
            }
        }
    } else {
        renderFilePreviews();
        if (fileStorage.currentMode === 'multiple') {
            var fileList = document.getElementById('file-list');
            if (fileList) {
                fileList.innerHTML = '<strong>Đã chọn ' + fileStorage.files.length + ' file</strong>';
            }
        } else if (fileStorage.currentMode === 'single' && fileStorage.files.length > 0) {
            var docNameInput = document.getElementById('document_name');
            if (docNameInput && !docNameInput.disabled) {
                docNameInput.value = fileStorage.files[0].name;
            }
        }
    }
}

function updateFileInput(inputId, files) {
    var input = document.getElementById(inputId);
    if (!input) return;
    
    try {
        var dataTransfer = new DataTransfer();
        for (var i = 0; i < files.length; i++) {
            dataTransfer.items.add(files[i]);
        }
        input.files = dataTransfer.files;
        return true;
    } catch (e) {
        // Fallback for older browsers
        console.warn('DataTransfer not supported. Files are stored but may need manual selection.');
        // Files are still in fileStorage, so preview will work
        // but form submission might need additional handling
        return false;
    }
}

function renderFilePreviews() {
    var previewImagesContainer = document.getElementById('file-preview-images');
    var previewText = document.getElementById('file-preview-text');
    
    if (!previewImagesContainer || !previewText) return;
    
    // Clear previous previews
    previewImagesContainer.innerHTML = '';
    
    if (fileStorage.files.length === 0) {
        clearPreview();
        return;
    }
    
    var hasImages = false;
    
    // Loop through all files
    for (var i = 0; i < fileStorage.files.length; i++) {
        var file = fileStorage.files[i];
        var index = i; // Capture index for closure
        
        // Check if file is an image
        if (file.type.match('image.*')) {
            hasImages = true;
            
            // Use closure to capture the current file and index
            (function(currentFile, currentIndex) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var imgWrapper = document.createElement('div');
                    imgWrapper.style.position = 'relative';
                    imgWrapper.style.display = 'flex';
                    imgWrapper.style.flexDirection = 'column';
                    imgWrapper.style.alignItems = 'center';
                    imgWrapper.style.flexShrink = '0';
                    imgWrapper.style.gap = '5px';
                    
                    // Remove button (X)
                    var removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.type = 'button';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '-8px';
                    removeBtn.style.right = '-8px';
                    removeBtn.style.width = '24px';
                    removeBtn.style.height = '24px';
                    removeBtn.style.borderRadius = '50%';
                    removeBtn.style.border = 'none';
                    removeBtn.style.backgroundColor = '#dc3545';
                    removeBtn.style.color = 'white';
                    removeBtn.style.fontSize = '18px';
                    removeBtn.style.fontWeight = 'bold';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.display = 'flex';
                    removeBtn.style.alignItems = 'center';
                    removeBtn.style.justifyContent = 'center';
                    removeBtn.style.padding = '0';
                    removeBtn.style.lineHeight = '1';
                    removeBtn.style.zIndex = '10';
                    removeBtn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
                    removeBtn.title = 'Xóa file';
                    
                    removeBtn.onmouseover = function() {
                        this.style.backgroundColor = '#c82333';
                    };
                    removeBtn.onmouseout = function() {
                        this.style.backgroundColor = '#dc3545';
                    };
                    
                    removeBtn.onclick = function(event) {
                        event.preventDefault();
                        event.stopPropagation();
                        removeFileFromStorage(currentIndex);
                    };
                    
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '200px';
                    img.style.maxHeight = '200px';
                    img.style.objectFit = 'contain';
                    img.style.border = '1px solid #ccc';
                    img.style.borderRadius = '4px';
                    img.style.pointerEvents = 'none';
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
                    
                    imgWrapper.appendChild(removeBtn);
                    imgWrapper.appendChild(img);
                    imgWrapper.appendChild(fileName);
                    previewImagesContainer.appendChild(imgWrapper);
                };
                reader.readAsDataURL(currentFile);
            })(file, index);
        } else {
            // Non-image file
            (function(currentFile, currentIndex) {
                var fileWrapper = document.createElement('div');
                fileWrapper.style.position = 'relative';
                fileWrapper.style.display = 'flex';
                fileWrapper.style.flexDirection = 'column';
                fileWrapper.style.alignItems = 'center';
                fileWrapper.style.flexShrink = '0';
                fileWrapper.style.gap = '5px';
                fileWrapper.style.padding = '10px';
                fileWrapper.style.border = '1px solid #ccc';
                fileWrapper.style.borderRadius = '4px';
                fileWrapper.style.minWidth = '200px';
                fileWrapper.style.backgroundColor = '#f8f9fa';
                
                // Remove button (X)
                var removeBtn = document.createElement('button');
                removeBtn.innerHTML = '&times;';
                removeBtn.type = 'button';
                removeBtn.style.position = 'absolute';
                removeBtn.style.top = '-8px';
                removeBtn.style.right = '-8px';
                removeBtn.style.width = '24px';
                removeBtn.style.height = '24px';
                removeBtn.style.borderRadius = '50%';
                removeBtn.style.border = 'none';
                removeBtn.style.backgroundColor = '#dc3545';
                removeBtn.style.color = 'white';
                removeBtn.style.fontSize = '18px';
                removeBtn.style.fontWeight = 'bold';
                removeBtn.style.cursor = 'pointer';
                removeBtn.style.display = 'flex';
                removeBtn.style.alignItems = 'center';
                removeBtn.style.justifyContent = 'center';
                removeBtn.style.padding = '0';
                removeBtn.style.lineHeight = '1';
                removeBtn.style.zIndex = '10';
                removeBtn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
                removeBtn.title = 'Xóa file';
                
                removeBtn.onmouseover = function() {
                    this.style.backgroundColor = '#c82333';
                };
                removeBtn.onmouseout = function() {
                    this.style.backgroundColor = '#dc3545';
                };
                
                removeBtn.onclick = function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    removeFileFromStorage(currentIndex);
                };
                
                var fileIcon = document.createElement('div');
                fileIcon.innerHTML = '📄';
                fileIcon.style.fontSize = '48px';
                
                var fileName = document.createElement('div');
                fileName.textContent = currentFile.name;
                fileName.style.fontSize = '12px';
                fileName.style.color = '#666';
                fileName.style.maxWidth = '200px';
                fileName.style.wordBreak = 'break-word';
                fileName.style.textAlign = 'center';
                
                fileWrapper.appendChild(removeBtn);
                fileWrapper.appendChild(fileIcon);
                fileWrapper.appendChild(fileName);
                previewImagesContainer.appendChild(fileWrapper);
            })(file, index);
        }
    }
    
    previewImagesContainer.style.display = 'flex';
    previewText.style.display = 'none';
}

function showFilePreview(file) {
    // Legacy function - now uses renderFilePreviews
    fileStorage.files = [file];
    renderFilePreviews();
}

function showMultipleFilePreviews(files) {
    // Legacy function - now uses renderFilePreviews
    fileStorage.files = Array.from(files);
    renderFilePreviews();
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

// ====== PASTE FUNCTIONALITY (Ctrl + V) ======
(function initPasteHandler() {
    document.addEventListener('paste', function(e) {
        // Check if we're in an input field that should handle text paste
        var target = e.target;
        if (target.tagName === 'INPUT' && target.type === 'text' || 
            target.tagName === 'TEXTAREA') {
            return; // Let default paste behavior happen
        }
        
        var items = e.clipboardData.items;
        var pastedFiles = [];
        
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            if (item.kind === 'file') {
                var file = item.getAsFile();
                if (file) {
                    pastedFiles.push(file);
                }
            }
        }
        
        if (pastedFiles.length > 0) {
            e.preventDefault();
            addFilesToStorage(pastedFiles);
            renderFilePreviews();
        }
    });
})();

// ====== DRAG AND DROP FUNCTIONALITY ======
(function initDragDropHandler() {
    var dropZone = document.getElementById('file-preview-container');
    if (!dropZone) return;
    
    // Prevent default drag behaviors on the whole document
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
        document.body.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });
    
    // Highlight drop zone when dragging over it
    ['dragenter', 'dragover'].forEach(function(eventName) {
        dropZone.addEventListener(eventName, function(e) {
            dropZone.style.backgroundColor = '#e3f2fd';
            dropZone.style.borderColor = '#2196F3';
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(function(eventName) {
        dropZone.addEventListener(eventName, function(e) {
            dropZone.style.backgroundColor = '';
            dropZone.style.borderColor = '#ddd';
        }, false);
    });
    
    // Handle dropped files
    dropZone.addEventListener('drop', function(e) {
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            addFilesToStorage(Array.from(files));
            renderFilePreviews();
        }
    }, false);
})();

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
