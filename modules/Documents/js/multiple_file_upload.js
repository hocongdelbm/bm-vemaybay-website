/**
 * Multiple File Upload Handler for Documents Module
 * Handles toggle between single/multiple file upload modes with drag-drop and paste support
 */

// Global file storage
var fileStorage = {
    files: [],
    currentMode: 'single'
};

// ---------- file-type icons ----------
// map file extensions (without dot) to inline SVG strings. you can
// populate this object with the actual SVG markup you've imported
// elsewhere (for example you might have copied <svg>…</svg> blobs here)
var extensionIcons = {
    // example placeholders – replace with real svg content
    docx: '<svg fill="#0055ff" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 548.29 548.291" xml:space="preserve" stroke="#0055ff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M486.2,196.124h-13.164V132.59c0-0.396-0.064-0.795-0.116-1.196c-0.021-2.523-0.824-5-2.551-6.963L364.656,3.677 c-0.031-0.031-0.064-0.042-0.085-0.075c-0.629-0.704-1.364-1.29-2.141-1.796c-0.231-0.154-0.462-0.283-0.704-0.418 c-0.672-0.366-1.386-0.671-2.121-0.892c-0.199-0.055-0.377-0.134-0.576-0.188C358.229,0.118,357.4,0,356.562,0H96.757 C84.893,0,75.256,9.649,75.256,21.502v174.616H62.093c-16.972,0-30.733,13.753-30.733,30.73v159.812 c0,16.961,13.761,30.731,30.733,30.731h13.163V526.79c0,11.854,9.637,21.501,21.501,21.501h354.777 c11.853,0,21.502-9.647,21.502-21.501V417.392H486.2c16.966,0,30.729-13.764,30.729-30.731V226.854 C516.93,209.872,503.166,196.124,486.2,196.124z M96.757,21.502h249.053v110.006c0,5.943,4.818,10.751,10.751,10.751h94.973v53.864 H96.757V21.502z M354.739,298.02c0,48.877-29.634,78.505-73.208,78.505c-44.229,0-70.106-33.392-70.106-75.849 c0-44.677,28.528-78.069,72.537-78.069C329.736,222.607,354.739,256.88,354.739,298.02z M64.345,373.432V227.037 c12.384-1.995,28.525-3.102,45.562-3.102c28.305,0,46.657,5.089,61.033,15.921c15.48,11.503,25.216,29.861,25.216,56.174 c0,28.53-10.392,48.21-24.764,60.373c-15.704,13.05-39.591,19.238-68.786,19.238C85.125,375.642,72.746,374.536,64.345,373.432z M451.534,520.962H96.757v-103.57h354.777V520.962z M453.16,348.447c10.174,0,21.455-2.223,28.085-4.867l5.093,26.315 c-6.196,3.108-20.127,6.409-38.258,6.409c-51.528,0-78.069-32.063-78.069-74.526c0-50.853,36.267-79.171,81.375-79.171 c17.47,0,30.751,3.538,36.726,6.638l-6.861,26.754c-6.851-2.872-16.362-5.531-28.309-5.531c-26.758,0-47.55,16.147-47.55,49.316 C405.387,329.642,423.082,348.447,453.16,348.447z"></path> <path d="M160.322,297.137c0.221-30.968-17.917-47.331-46.88-47.331c-7.52,0-12.396,0.661-15.265,1.329v97.532 c2.868,0.665,7.52,0.665,11.724,0.665C140.417,349.548,160.322,332.739,160.322,297.137z"></path> <path d="M247.032,300.004c0,29.202,13.714,49.765,36.269,49.765c22.782,0,35.827-21.68,35.827-50.646 c0-26.768-12.824-49.758-36.048-49.758C260.311,249.371,247.032,271.043,247.032,300.004z"></path> </g> </g></svg>',
    pdf: '<svg fill="#ff0000" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 550.801 550.801" xml:space="preserve" stroke="#ff0000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M160.381,282.225c0-14.832-10.299-23.684-28.474-23.684c-7.414,0-12.437,0.715-15.071,1.432V307.6 c3.114,0.707,6.942,0.949,12.192,0.949C148.419,308.549,160.381,298.74,160.381,282.225z"></path> <path d="M272.875,259.019c-8.145,0-13.397,0.717-16.519,1.435v105.523c3.116,0.729,8.142,0.729,12.69,0.729 c33.017,0.231,54.554-17.946,54.554-56.474C323.842,276.719,304.215,259.019,272.875,259.019z"></path> <path d="M488.426,197.019H475.2v-63.816c0-0.398-0.063-0.799-0.116-1.202c-0.021-2.534-0.827-5.023-2.562-6.995L366.325,3.694 c-0.032-0.031-0.063-0.042-0.085-0.076c-0.633-0.707-1.371-1.295-2.151-1.804c-0.231-0.155-0.464-0.285-0.706-0.419 c-0.676-0.369-1.393-0.675-2.131-0.896c-0.2-0.056-0.38-0.138-0.58-0.19C359.87,0.119,359.037,0,358.193,0H97.2 c-11.918,0-21.6,9.693-21.6,21.601v175.413H62.377c-17.049,0-30.873,13.818-30.873,30.873v160.545 c0,17.043,13.824,30.87,30.873,30.87h13.224V529.2c0,11.907,9.682,21.601,21.6,21.601h356.4c11.907,0,21.6-9.693,21.6-21.601 V419.302h13.226c17.044,0,30.871-13.827,30.871-30.87v-160.54C519.297,210.838,505.47,197.019,488.426,197.019z M97.2,21.605 h250.193v110.513c0,5.967,4.841,10.8,10.8,10.8h95.407v54.108H97.2V21.605z M362.359,309.023c0,30.876-11.243,52.165-26.82,65.333 c-16.971,14.117-42.82,20.814-74.396,20.814c-18.9,0-32.297-1.197-41.401-2.389V234.365c13.399-2.149,30.878-3.346,49.304-3.346 c30.612,0,50.478,5.508,66.039,17.226C351.828,260.69,362.359,280.547,362.359,309.023z M80.7,393.499V234.365 c11.241-1.904,27.042-3.346,49.296-3.346c22.491,0,38.527,4.308,49.291,12.928c10.292,8.131,17.215,21.534,17.215,37.328 c0,15.799-5.25,29.198-14.829,38.285c-12.442,11.728-30.865,16.996-52.407,16.996c-4.778,0-9.1-0.243-12.435-0.723v57.67H80.7 V393.499z M453.601,523.353H97.2V419.302h356.4V523.353z M484.898,262.127h-61.989v36.851h57.913v29.674h-57.913v64.848h-36.593 V232.216h98.582V262.127z"></path> </g> </g></svg>',
    xlsx: '<svg fill="#00c227" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 548.291 548.291" xml:space="preserve" stroke="#00c227"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M486.206,196.121H473.04v-63.525c0-0.396-0.062-0.795-0.109-1.2c-0.021-2.52-0.829-4.997-2.556-6.96L364.657,3.677 c-0.033-0.031-0.064-0.042-0.085-0.075c-0.63-0.704-1.364-1.29-2.143-1.796c-0.229-0.154-0.461-0.283-0.702-0.419 c-0.672-0.365-1.387-0.672-2.121-0.893c-0.2-0.052-0.379-0.134-0.577-0.186C358.23,0.118,357.401,0,356.562,0H96.757 C84.894,0,75.256,9.649,75.256,21.502v174.613H62.092c-16.971,0-30.732,13.756-30.732,30.733v159.812 c0,16.961,13.761,30.731,30.732,30.731h13.164V526.79c0,11.854,9.638,21.501,21.501,21.501h354.776 c11.853,0,21.501-9.647,21.501-21.501V417.392h13.166c16.966,0,30.729-13.764,30.729-30.731V226.854 C516.93,209.872,503.176,196.121,486.206,196.121z M96.757,21.502h249.054v110.006c0,5.94,4.817,10.751,10.751,10.751h94.972 v53.861H96.757V21.502z M314.576,314.661c-21.124-7.359-34.908-19.045-34.908-37.544c0-21.698,18.11-38.297,48.116-38.297 c14.331,0,24.903,3.014,32.442,6.413l-6.411,23.2c-5.091-2.446-14.146-6.037-26.598-6.037s-18.488,5.662-18.488,12.266 c0,8.115,7.171,11.696,23.58,17.921c22.446,8.305,33.013,20,33.013,37.921c0,21.323-16.415,39.435-51.318,39.435 c-14.524,0-28.861-3.769-36.031-7.737l5.843-23.77c7.738,3.958,19.627,7.927,31.885,7.927c13.218,0,20.188-5.47,20.188-13.774 C335.894,324.667,329.858,320.13,314.576,314.661z M265.917,343.9v24.157h-79.439V240.882h28.877V343.9H265.917z M94.237,368.057 H61.411l36.788-64.353l-35.473-62.827h33.021l11.125,23.21c3.774,7.736,6.606,13.954,9.628,21.135h0.367 c3.027-8.115,5.477-13.775,8.675-21.135l10.756-23.21h32.827l-35.848,62.066l37.74,65.103h-33.202l-11.515-23.022 c-4.709-8.855-7.73-15.465-11.316-22.824h-0.375c-2.645,7.359-5.845,13.969-9.811,22.824L94.237,368.057z M451.534,520.968H96.757 V417.392h354.776V520.968z M451.728,368.057l-11.512-23.022c-4.715-8.863-7.733-15.465-11.319-22.825h-0.366 c-2.646,7.36-5.858,13.962-9.827,22.825l-10.551,23.022h-32.836l36.788-64.353l-35.471-62.827h33.02l11.139,23.21 c3.77,7.736,6.593,13.954,9.618,21.135h0.377c3.013-8.115,5.459-13.775,8.671-21.135l10.752-23.21h32.835l-35.849,62.066 l37.733,65.103h-33.202V368.057z"></path> </g> </g></svg>',
    pptx: '<svg fill="#ee7e17" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 548.291 548.291" xml:space="preserve" stroke="#ee7e17"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M222.581,262.339c-5.504,0-9.229,0.538-11.176,1.061v35.292c2.312,0.541,5.145,0.715,9.046,0.715 c14.374,0,23.237-7.276,23.237-19.519C243.688,268.904,236.051,262.339,222.581,262.339z"></path> <path d="M486.201,196.116h-13.166V132.59c0-0.399-0.062-0.795-0.115-1.2c-0.021-2.522-0.825-5-2.552-6.96L364.657,3.677 c-0.033-0.034-0.064-0.044-0.085-0.075c-0.63-0.704-1.364-1.292-2.143-1.796c-0.229-0.157-0.461-0.286-0.702-0.419 c-0.672-0.365-1.387-0.672-2.121-0.893c-0.2-0.052-0.379-0.134-0.577-0.188C358.23,0.118,357.401,0,356.562,0H96.757 C84.894,0,75.256,9.649,75.256,21.502v174.613H62.092c-16.971,0-30.732,13.756-30.732,30.73v159.81 c0,16.966,13.761,30.736,30.732,30.736h13.164V526.79c0,11.854,9.638,21.501,21.501,21.501h354.776 c11.853,0,21.501-9.647,21.501-21.501V417.392h13.166c16.966,0,30.729-13.764,30.729-30.731V226.854 C516.93,209.872,503.167,196.116,486.201,196.116z M96.757,21.502h249.054v110.006c0,5.94,4.817,10.751,10.751,10.751h94.972 v53.861H96.757V21.502z M278.639,265.544v-22.711h91.863v22.711h-32.63v96.822h-27.128v-96.822H278.639z M270.452,279.195 c0,11.696-3.89,21.622-10.987,28.363c-9.228,8.692-22.887,12.604-38.837,12.604c-3.556,0-6.75-0.179-9.228-0.54v42.74h-26.78 V244.429c8.336-1.417,20.045-2.488,36.536-2.488c16.68,0,28.555,3.194,36.544,9.575 C265.326,257.552,270.452,267.484,270.452,279.195z M81.578,362.362V244.429c8.336-1.417,20.034-2.488,36.526-2.488 c16.675,0,28.549,3.194,36.536,9.575c7.627,6.037,12.766,15.968,12.766,27.669c0,11.696-3.903,21.627-10.998,28.368 c-9.229,8.692-22.876,12.598-38.842,12.598c-3.536,0-6.746-0.179-9.229-0.535v42.74H81.578V362.362z M451.534,520.962H96.757 v-103.57h354.776V520.962z M451.041,362.362l-10.824-21.638c-4.425-8.336-7.276-14.541-10.646-21.454h-0.346 c-2.478,6.913-5.503,13.118-9.219,21.454l-9.937,21.638h-30.852l34.573-60.488l-33.329-59.041h31.028l10.458,21.819 c3.555,7.268,6.215,13.122,9.05,19.861h0.356c2.835-7.633,5.145-12.953,8.159-19.861l10.109-21.819h30.867l-33.702,58.347 l35.466,61.182H451.041z"></path> <path d="M140.627,279.888c0-10.989-7.628-17.554-21.109-17.554c-5.487,0-9.231,0.538-11.181,1.061v35.297 c2.315,0.535,5.145,0.715,9.053,0.715C131.763,299.407,140.627,292.135,140.627,279.888z"></path> </g> </g></svg>',

};

// default icon used when extension not found
var defaultFileIcon = '<svg fill="#000000" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 33.34 33.34" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M17.335,2.127L16.88,1.695H2.9V33.34h25.262V12.504H17.335V2.127L17.335,2.127z M24.17,28.004H7.003v-2H24.17V28.004z M24.17,20.672v2H7.003v-2H24.17z M30.439,10.285v0.096H19.613V0L30.439,10.285z"></path> </g> </g></svg>';

function getExtension(filename) {
    var parts = filename.split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : '';
}

function getIconForExtension(ext) {
    return extensionIcons[ext] || defaultFileIcon;
}


/**
 * Switch between single and multiple upload modes programmatically
 * @param {string} mode - 'single' or 'multiple'
 */
function switchToMode(mode) {
    var singleContainer = document.getElementById('single-upload-container');
    var multipleContainer = document.getElementById('multiple-upload-container');
    var docNameInput = document.getElementById('document_name');
    var previewText = document.getElementById('file-preview-text');
    
    fileStorage.currentMode = mode;
    
    // Force hide preview text if we have files
    if (previewText && fileStorage.files.length > 0) {
        previewText.style.setProperty('display', 'none', 'important');
    }
    
    if (mode === 'single') {
        singleContainer.style.display = 'block';
        multipleContainer.style.display = 'none';
        if (docNameInput) {
            docNameInput.disabled = false;
            if (fileStorage.files.length > 0) {
                docNameInput.value = fileStorage.files[0].name;
            }
        }
    } else {
        singleContainer.style.display = 'none';
        multipleContainer.style.display = 'block';
        if (docNameInput) {
            docNameInput.disabled = true;
            docNameInput.value = '(Tự động theo tên file)';
        }
    }
}

function handleSingleFileSelect(input) {
    if (input.files.length > 0) {
        // Hide preview text immediately
        var previewText = document.getElementById('file-preview-text');
        if (previewText) {
            previewText.style.setProperty('display', 'none', 'important');
        }
        
        // Add files to storage (consistent with paste/drag-drop behavior)
        addFilesToStorage(Array.from(input.files));
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
        // Hide preview text immediately
        var previewText = document.getElementById('file-preview-text');
        if (previewText) {
            previewText.style.setProperty('display', 'none', 'important');
        }
        
        // Add files to storage (consistent with paste/drag-drop behavior)
        addFilesToStorage(Array.from(input.files));
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
    
    // Hide "Chưa chọn file" text immediately when we have files
    var previewText = document.getElementById('file-preview-text');
    if (previewText && fileStorage.files.length > 0) {
        previewText.style.setProperty('display', 'none', 'important');
    }
    
    // Auto-switch to multiple mode if more than 1 file
    if (fileStorage.files.length > 1 && fileStorage.currentMode === 'single') {
        // Clear old single file input before switching
        var oldInput = document.getElementById('filename_file');
        if (oldInput) {
            oldInput.value = '';
        }
        
        // Switch to multiple mode
        switchToMode('multiple');
    } else if (fileStorage.files.length === 1 && fileStorage.currentMode === 'multiple') {
        // Switch back to single mode if only 1 file left
        switchToMode('single');
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
    
    // Auto-switch back to single mode if only 1 file left
    if (fileStorage.files.length === 1 && fileStorage.currentMode === 'multiple') {
        switchToMode('single');
    }
    
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
    
    // Hide text immediately when we have files
    previewText.style.setProperty('display', 'none', 'important');
    previewImagesContainer.style.display = 'flex';
    
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
                // use extension-specific SVG, fall back to generic icon
                var ext = getExtension(currentFile.name);
                fileIcon.innerHTML = getIconForExtension(ext);
                // ensure icons scale reasonably
                fileIcon.style.width = '48px';
                fileIcon.style.height = '48px';
                fileIcon.style.display = 'flex';
                fileIcon.style.alignItems = 'center';
                fileIcon.style.justifyContent = 'center';
                
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
        
        // Check if file upload is disabled (edit mode)
        var fileInput = document.getElementById('filename_file');
        if (fileInput && fileInput.disabled) {
            console.log('Paste blocked: File upload is disabled in edit mode');
            return; // Don't allow paste in edit mode
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
    
    // Highlight drop zone when dragging over it (only if upload is enabled)
    ['dragenter', 'dragover'].forEach(function(eventName) {
        dropZone.addEventListener(eventName, function(e) {
            var fileInput = document.getElementById('filename_file');
            if (fileInput && !fileInput.disabled) {
                dropZone.style.backgroundColor = '#e3f2fd';
                dropZone.style.borderColor = '#2196F3';
            }
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
        // Check if file upload is disabled (edit mode)
        var fileInput = document.getElementById('filename_file');
        if (fileInput && fileInput.disabled) {
            console.log('Drop blocked: File upload is disabled in edit mode');
            return; // Don't allow drop in edit mode
        }
        
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
    
    // Check if upload is disabled (edit mode)
    var fileInput = document.getElementById('filename_file');
    var isDisabled = fileInput && fileInput.disabled;
    
    // Don't enable drag scroll if upload is disabled
    if (isDisabled) {
        return;
    }
    
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
    
    // Set initial cursor (only if not disabled)
    container.style.cursor = 'grab';
})();

// ====== INIT ON DOM READY ======
// Force hide preview text if we have files when page loads
(function initOnLoad() {
    // Run immediately
    function forceHideTextIfHasFiles() {
        var previewText = document.getElementById('file-preview-text');
        if (previewText && fileStorage.files.length > 0) {
            previewText.style.setProperty('display', 'none', 'important');
        }
    }
    
    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forceHideTextIfHasFiles);
    } else {
        forceHideTextIfHasFiles();
    }
    
    // Also run after a tiny delay to catch late DOM updates
    setTimeout(forceHideTextIfHasFiles, 100);
    
    // Watch for any changes and re-hide if needed
    setInterval(function() {
        var previewText = document.getElementById('file-preview-text');
        if (previewText && fileStorage.files.length > 0) {
            // Aggressively hide and clear content
            if (previewText.style.display !== 'none' || previewText.textContent.trim() !== '') {
                previewText.style.setProperty('display', 'none', 'important');
                previewText.innerHTML = ''; // Force clear content
                previewText.textContent = ''; // Backup clear
            }
        }
    }, 30); // Check every 30ms for aggressive hiding
})();
