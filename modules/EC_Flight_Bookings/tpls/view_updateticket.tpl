{* <!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách chuyến bay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href= "../assets/css/t7.css">
</head>
<body> *}
    <div class="container">
        <div class="header">
            <h1>🛫 Tìm chuyến bay</h1>
            <div class="route-info">
                <span class="route" id="routeInfo">Loading...</span>
                <span class="date" id="dateInfo"></span>
            </div>
        </div>
        
        <div class="tabs">
            <a href="#departure" class="tab active" onclick="showTab(event, 'departure')">Chiều đi</a>
            <a href="#return" class="tab" onclick="showTab(event, 'return')">Chiều về</a>
        </div>
        
        <div id="departure" class="tab-content active">
            <div class="section-title" id="depTitle">Chuyến bay đi</div>
            <div class="flight-list" id="depFlightList">
                <div class="loading">Đang tải dữ liệu...</div>
            </div>
        </div>
        
        <div id="return" class="tab-content">
            <div class="section-title" id="retTitle">Chuyến bay về</div>
            <div class="flight-list" id="retFlightList">
                <div class="loading">Đang tải dữ liệu...</div>
            </div>
        </div>
    </div>
{* </body>
</html> *}