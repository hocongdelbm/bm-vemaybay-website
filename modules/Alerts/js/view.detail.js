document.addEventListener("DOMContentLoaded", function () {
            const image = document.getElementById("alertImage");
            if (image) {
                const viewer = new Viewer(image, {
                    toolbar: true,       // Hiển thị thanh công cụ zoom
                    movable: true,       // Cho phép kéo ảnh
                    zoomable: true,      // Cho phép zoom
                    scalable: true,      // Cho phép scale
                    // fullscreen: true,    // Cho phép fullscreen
                    navbar: false,       // Ẩn thanh thumbnail
                });
            }
        });