/* global SUGAR */

$("#es-test-connection").click(function () {
    var url = "index.php?module=Administration&action=ElasticSearchSettings&do=TestConnection";
    var host = $("#es-host").val();
    var user = $("#es-user").val();
    var pass = $("#es-password").val();

    $.ajax({
        url: url,
        method: "POST",
        data: {
            host: host,
            user: user,
            pass: pass
        }
    }).done(function (data) {
        if (data.status === "success") {
            alert(
                SUGAR.language.get("Administration", "LBL_ELASTIC_SEARCH_TEST_CONNECTION_SUCCESS")
                + "\n\nPing: " + data.ping / 1000 + " ms\nElasticsearch v" + data.info.version.number
            );
        } else {
            alert(
                SUGAR.language.get("Administration", "LBL_ELASTIC_SEARCH_TEST_CONNECTION_FAIL")
                + "\n\n" + data.error + "."
            );
        }
    }).error(function () {
        alert(SUGAR.language.get("Administration", "LBL_ELASTIC_SEARCH_TEST_CONNECTION_ERROR"));
    });
});

$("#es-full-index").click(function () {
    var url = "index.php?module=Administration&action=ElasticSearchSettings&do=FullIndex";

    $.ajax(url).done(function () {
        alert(SUGAR.language.get("Administration", "LBL_ELASTIC_SEARCH_INDEX_SCHEDULE_FULL_SUCCESS"));
    }).error(function () {
        alert(SUGAR.language.get("Administration", "LBL_ELASTIC_SEARCH_INDEX_SCHEDULE_FULL_FAIL"));
    });
});

$("#es-partial-index").click(function () {
    var url = "index.php?module=Administration&action=ElasticSearchSettings&do=PartialIndex";

    $.ajax(url).done(function () {
        alert(SUGAR.language.get("Administration", "LBL_ELASTIC_SEARCH_INDEX_SCHEDULE_PART_SUCCESS"));
    }).error(function () {
        alert(SUGAR.language.get("Administration", "LBL_ELASTIC_SEARCH_INDEX_SCHEDULE_PART_FAIL"));
    });
});
