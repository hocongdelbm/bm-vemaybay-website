// Khởi tạo Viewer cho cả DetailView và EditView
(function() {
    function initViewer() {
        const image = document.getElementById("previewImage");
        
        if (!image) {
            console.log("Preview image not found");
            return;
        }
        
        // Kiểm tra nếu đã có viewer rồi
        if (image.viewer) {
            console.log("Viewer already initialized");
            return;
        }
        
        // Chỉ khởi tạo nếu image có src và visible
        const hasSrc = image.src && image.src !== '' && !image.src.endsWith('/');
        const isVisible = window.getComputedStyle(image).display !== 'none';
        
        if (hasSrc && isVisible) {
            console.log("Initializing Viewer...");
            const viewer = new Viewer(image, {
                toolbar: true,
                movable: true,
                zoomable: true,
                scalable: true,
                navbar: false,
            });
            console.log("Viewer initialized successfully");
        } else {
            console.log("Image not ready - hasSrc:", hasSrc, "isVisible:", isVisible);
        }
    }
    
    // Đợi page load hoàn toàn (bao gồm cả Smarty template render)
    if (document.readyState === 'complete') {
        initViewer();
    } else {
        window.addEventListener('load', initViewer);
    }
})();