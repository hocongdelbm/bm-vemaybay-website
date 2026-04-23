// Hàm resize ảnh (tái sử dụng logic chung)
function resizeImageCommon(img, maxWidth, maxHeight) {
    var width = img.width;
    var height = img.height;
    var scale = Math.min(maxWidth / width, maxHeight / height, 1);
    var newWidth = width * scale;
    var newHeight = height * scale;
    
    var canvas = document.createElement('canvas');
    canvas.width = newWidth;
    canvas.height = newHeight;
    
    var ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0, newWidth, newHeight);
    
    return canvas.toDataURL('image/jpeg', 0.9);
}

// Từ File (EditView)
function resizeImage(file, maxWidth, maxHeight, callback) {
    var reader = new FileReader();
    reader.onload = function(e) {
        var img = new Image();
        img.onload = function() {
            callback(resizeImageCommon(img, maxWidth, maxHeight));
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Từ URL (DetailView)
function resizeImageFromUrl(imageUrl, maxWidth, maxHeight, callback) {
    var img = new Image();
    img.onload = function() {
        callback(resizeImageCommon(img, maxWidth, maxHeight));
    };
    img.onerror = function() {
        callback(null);
    };
    img.src = imageUrl;
}
$(document).ready(function () {
  // EditView - Resize khi chọn file
  $('input[name="filename_file"]').on("change", function (e) {
    var file = e.target.files[0];
    var previewImg = $("#file-preview-image");
    var previewText = $("#file-preview-text");

    if (file) {
      if (file.type.match("image.*")) {
        resizeImage(file, 800, 600, function(resizedDataUrl) {
          previewImg.attr("src", resizedDataUrl);
          previewImg.show();
          previewText.hide();
        });
      } else {
        previewImg.hide();
        previewText.text("Không phải file hình ảnh").show();
      }
    } else {
      previewImg.hide();
      previewText.text("Chưa chọn file").show();
    }
  });
  
  // DetailView - Resize ảnh preview đã có sẵn
  var previewImgDetail = $("#preview-image-detail");
  if (previewImgDetail.length > 0 && previewImgDetail.attr("src")) {
    var originalSrc = previewImgDetail.attr("src");
    
    resizeImageFromUrl(originalSrc, 300, 300, function(resizedUrl) {
      if (resizedUrl) {
        previewImgDetail.attr("src", resizedUrl);
      }
    });
  }
});
