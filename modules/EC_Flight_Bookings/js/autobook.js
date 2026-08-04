const ENTRYPOINT = "index.php?entryPoint=entryPointGeneral";
const PREFIX = "autobook";
const MAPPING_SYSTEM_CODE = {
  VJA: "VJ",
  VNA: "VN",
  VNP: "VN",
  BBA: "QH",
  VTA: "VU",
};
const DATACOM_DOCUMENT_TYPE_MAP = {
  I: "identity",
  P: "passport",
};

// Policy details shown by the "Xem chi tiết" links in the auto-book note section.
// Content is a placeholder until the real policy wording is provided.
const POLICY_INFO = {
  "identity-docs": {
    title: "Quy định giấy tờ tùy thân",
    content: `
      <ul>
        <li>Bắt buộc bổ sung <b>Ngày hết hạn</b> của giấy tờ sử dụng.</li>
        <li>Bắt buộc bổ sung <b>Mã quốc tịch</b>, <b>Mã quốc gia cấp</b> đối với Passport.</li>
        <li>Họ, Tên, Ngày sinh giữ chỗ phải trùng khớp chính xác với giấy tờ tùy thân.</li>
        <li>Giấy tờ tùy thân phải còn hạn cho đến thời điểm bay.</li>
      </ul>
    `,
  },
  "baby-name": {
    title: "Quy định điền tên quá dài khi giữ chỗ",
    content: `
      <h6>Hãng Sun PhuQuoc Airways (9G)</h6>
      <p>Nguyên tắc chung:</p>
      <ul>
        <li>Tên khách thông thường hoặc tên khách đi cùng trẻ sơ sinh có tổng ký tự vượt quá 59, có thể viết tắt phần tên đệm của khách người lớn và trẻ sơ sinh sao cho không vượt quá tổng số ký tự cho phép.</li>
        <li>Giữ đầy đủ phần họ và tên gọi của hành khách người lớn và trẻ sơ sinh.</li>
        <li>Viết tắt bằng cách lấy chữ cái đầu của từng tên đệm, không sử dụng dấu chấm hoặc ký tự đặc biệt.</li>   
      </ul>
      <p style="margin-top:6px">Thứ tự ưu tiên lược bớt:</p>
      <ul>
        <li>Lược bỏ khoảng trắng giữa tên và tên đệm.</li>
        <li>Lược bỏ danh xưng nếu có (Ví dụ: Ông, Bà, Mr, Ms, Mrs,...)</li>
        <li>Viết tắt tên đệm của trẻ sơ sinh.</li>
        <li>Viết tắt tên đệm của người lớn.</li>
      </ul>
      <p style="margin-top:6px">Ví dụ: <b>NGUYEN TRUNG QUAN NGOC VAN HO ANH TUAN TU NAM</b> => <b>N T Q N V H A TUAN TU NAM</b></p>

      <hr />

      <h6>Hãng Bamboo Airways (QH)</h6>
      <p>Nguyên tắc chung:</p>
      <ul>
        <li>Hệ thống của BAV cho phép tối đa 53 ký tự trong trường tên, bao gồm họ, tên đệm, title, Passenger Type Code (PTC), DOB, và Identification code (ID or CR), dấu trống,… </li>
        <li>Đối với hành khách là người lớn đi kèm em bé dưới 2 tuổi: tối đa kí tự trường tên, bao gồm cả tên, ngày sinh của em bé đi kèm người lớn.</li>
      </ul>
      <p style="margin-top:6px">Thứ tự ưu tiên lược bớt:</p>
      <ul>
        <li>Ưu tiên bỏ dấu cách trong tên của đối tượng khách em bé.</li>
        <li>Trong trường hợp vẫn vượt quá số lượng kí tự, viết tắt kí tự đầu tiên của tên đệm của em bé (tên người lớn viết đầy đủ) sao cho tổng số kí tự tối đa có thể và không vượt quá số lượng kí tự cho phép.</li>
        <li>Trường hợp số lượng kí tự vẫn vượt và báo lỗi không xuất được vé, Đại lý ưu tiên giữ chỗ với tên đầy đủ của người lớn và liên hệ Hãng để được hỗ trợ thêm thông tin em bé.</li>
      </ul>
      <p style="margin-top:6px">Ví dụ: <b> NGUYEN BAO NGOC MINH CHAU</b> => <b>NGUYEN B N MINH CHAU</b></p>
    `,
  },
};

var bookingId = "";
var isInter = 0;
var statusAutoBook = 1;

$(document).ready(function () {
  bookingId = $(`#formDetailView input[name="record"]`).val();
  let ticketType = $(`input[name="ticket_type"]`).val(); // '1':Domestic ; '2':International
  isInter = ticketType == "2" ? 1 : 0;

  // Open auto book dialog
  $(".btn-auto-book").click(function () {
    let entryClass = $(this).attr("data-entry-class");
    let listItineraryId = getSelectedItinerariesData();
    let listDetailId = getSelectedDetailsData();
    let listPassengers = getSelectedPassengersData(); // Object
    let listPassengerId = Object.keys(listPassengers);

    // Validate
    if (!bookingId || bookingId.length < 30) {
      showToastNotify("warning", "Chưa có thông tin booking");
      return false;
    }
    if (!entryClass || entryClass.length == 0) {
      showToastNotify("warning", "Chưa chọn thông tin nhà cung cấp");
      return false;
    }
    if (!listItineraryId || listItineraryId.length == 0) {
      showToastNotify("warning", "Vui lòng chọn hành trình");
      return false;
    }
    if (!listDetailId || listDetailId.length == 0) {
      showToastNotify("warning", "Vui lòng chọn chi tiết vé");
      return false;
    }
    if (!listPassengerId || listPassengerId.length == 0) {
      showToastNotify("warning", "Vui lòng chọn hành khách");
      return false;
    }

    let adtCount = 0;
    let chdCount = 0;
    let infCount = 0;
    Object.entries(listPassengers).forEach(([key, value]) => {
      if (value === "0" || value === 0) adtCount++;
      else if (value === "1" || value === 1) chdCount++;
      else if (value === "2" || value === 2) infCount++;
    });
    if (adtCount < 1) {
      showToastNotify("warning", "Booking phải có người lớn");
      return;
    }
    if (adtCount < infCount) {
      showToastNotify("warning", "Số lượng em bé nhiều hơn người lớn");
      return;
    }
    if (adtCount + chdCount + infCount > 9) {
      showToastNotify("warning", "Giữ chỗ chỉ được tối đa 9 hành khách");
      return;
    }

    $.ajax({
      url: ENTRYPOINT,
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        class: entryClass,
        method: "getDataAutoBook",
        params: {
          bookingId: bookingId,
          listItineraryId: listItineraryId,
          listPassengerId: listPassengerId,
          listDetailId: listDetailId,
        },
      }),
      beforeSend: function () {
        $(".container-waiting").show();
      },
      success: function (response) {
        try {
          $(".container-waiting").hide();
          const objData = JSON.parse(response);
          if (objData.status == 1) {
            showDialogAutoBook(objData.data);
          } else {
            showModalNotify(
              "error",
              objData.message ?? "Lỗi trong quá trình xử lý",
              objData.description ?? "",
            );
            console.error(objData);
          }
        } catch (e) {
          handleException(e);
        }
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        $(".container-waiting").hide();
        // console.error(XMLHttpRequest);
        console.error(`Status: ${textStatus}`);
        console.error(`Error: ${errorThrown}`);
      },
    });
  });

  // Show steps in auto book dialog
  $(document).on("click", "#confirmAutoBook", async function () {
    statusAutoBook = 1;
    try {
      const entryClass = $('input[name="entryClass"]').val();

      /******  STEP 1: RESEARCHING FLIGHTS INFO  ******/
      var step = 1;
      var flightData = []; // Data for next step
      var textItiSuccess = `<b style="color:#4285f4; margin-left:8px">
                <svg width="18px" height="18px" fill="#4285f4" style="vertical-align:sub;" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm5.676,8.237-6,5.5a1,1,0,0,1-1.383-.03l-3-3a1,1,0,1,1,1.414-1.414l2.323,2.323,5.294-4.853a1,1,0,1,1,1.352,1.474Z"></path></g></svg> Ok
            </b>`;
      // Get flight info
      var listItineraryId = $(`input[name="${PREFIX}ItineraryId[]"]`)
        .map((i, el) => el.value)
        .get();
      var airlineCodes = $(`input[name="${PREFIX}AirlineCode[]"]`)
        .map((i, el) => MAPPING_SYSTEM_CODE[el.value] ?? el.value)
        .get();
      var depCodes = $(`input[name="${PREFIX}DepCode[]"]`)
        .map((i, el) => el.value)
        .get();
      var desCodes = $(`input[name="${PREFIX}DesCode[]"]`)
        .map((i, el) => el.value)
        .get();
      var departureDate = $(`input[name="${PREFIX}DepartureDate[]"]`)
        .map((i, el) => el.value)
        .get();
      var ticketClass = $(`input[name="${PREFIX}TicketClass[]"]`)
        .map((i, el) => el.value)
        .get();
      var flightNo = $(`input[name="${PREFIX}FlightNo[]"]`)
        .map((i, el) => el.value)
        .get();
      var within24h = $(`input[name="${PREFIX}Within24h[]"]`)
        .map((i, el) => el.value)
        .get();
      // Get passenger info
      var adt = 0;
      var chd = 0;
      var inf = 0;
      var listPassengerId = [];
      var passIdInputs = document.querySelectorAll(
        `input[name="${PREFIX}PassengerId[]"]`,
      );
      var passTypeInputs = document.querySelectorAll(
        `input[name="${PREFIX}PassengerType[]"]`,
      );
      var passDateOfBirthInputs = document.querySelectorAll(
        `input[name="${PREFIX}PassengerDateOfBirth[]"]`,
      );
      var passLastNameInputs = document.querySelectorAll(
        `input[name="${PREFIX}PassengerLastName[]"]`,
      );
      var passFirstNameInputs = document.querySelectorAll(
        `input[name="${PREFIX}PassengerFirstName[]"]`,
      );

      // Validate passenger type against date of birth: inf (< 2y), chd (< 12y), adt (>= 12y).
      // Age is calculated at the first departure date (fallback: today).
      var referenceDate =
        departureDate && departureDate.length > 0 ? departureDate[0] : "";
      var typeMismatches = [];
      passIdInputs.forEach((input, index) => {
        let passId = input.value;
        if (!passId || passId.length == 0) return;

        listPassengerId.push(passId);

        let passType = passTypeInputs[index].value.toLowerCase();
        let expectedType = getPassengerTypeByAge(
          calculateAge(passDateOfBirthInputs[index].value, referenceDate),
        );
        if (expectedType && expectedType != passType) {
          typeMismatches.push({
            index: index,
            name: passLastNameInputs[index]
              ? `${passLastNameInputs[index].value} ${passFirstNameInputs[index].value}`.trim()
              : "",
            birthdate: passDateOfBirthInputs[index].value,
            oldType: passType,
            newType: expectedType,
          });
        }
      });

      // Mismatch found -> warn and let the user confirm the auto-correction
      if (typeMismatches.length > 0) {
        const typeLabels = { adt: "Người lớn", chd: "Trẻ em", inf: "Em bé" };
        let detailHTML = typeMismatches
          .map(
            (m) =>
              `<div style="margin:4px 0">• <b>${m.name}</b> (${formatDateOfBirth(m.birthdate, "DMY")}): ` +
              `<span style="color:#d9534f">${typeLabels[m.oldType] ?? m.oldType}</span> &rarr; ` +
              `<b style="color:#198754">${typeLabels[m.newType] ?? m.newType}</b></div>`,
          )
          .join("");

        $("#autoBookDialog").hide();
        let confirmed = await showConfirmNotify(
          "Loại hành khách chưa khớp với ngày sinh. Bạn có muốn hệ thống tự điều chỉnh và tiếp tục?",
          detailHTML,
        );
        if (!confirmed) return;
        $("#autoBookDialog").show();

        // Apply the corrections to the hidden input (booking payload) and the visible card label
        const typeValueMap = { adt: "Adt", chd: "Chd", inf: "Inf" }; // autobookPassengerType[] format
        const typeDbMap = { adt: "0", chd: "1", inf: "2" }; // ec_booking_passengers.type enum
        typeMismatches.forEach((m) => {
          passTypeInputs[m.index].value = typeValueMap[m.newType];

          let card = passTypeInputs[m.index].closest(".passenger-info");
          let label = card ? card.querySelector(".passenger-type-label") : null;
          if (label) label.textContent = typeLabels[m.newType] ?? m.newType;
        });

        // Persist the corrected types to the database via entrypoint
        try {
          let updateResults = await Promise.all(
            typeMismatches.map((m) =>
              $.ajax({
                url: ENTRYPOINT,
                method: "POST",
                contentType: "application/json",
                dataType: "json",
                data: JSON.stringify({
                  class: "entryBookingClass",
                  method: "updatePassengerFields",
                  params: {
                    passengerId: passIdInputs[m.index].value,
                    fields: { type: typeDbMap[m.newType] },
                  },
                }),
              }),
            ),
          );
          let failed = updateResults.filter((r) => !r || r.status != 1).length;
          if (failed > 0)
            showToastNotify(
              "warning",
              `Có ${failed} hành khách chưa cập nhật được loại vào hệ thống`,
            );
        } catch (e) {
          console.error(e);
          showToastNotify(
            "warning",
            "Cập nhật loại hành khách vào hệ thống chưa thành công",
          );
        }
      }

      // Count passenger types after any correction above
      passIdInputs.forEach((input, index) => {
        if (!input.value || input.value.length == 0) return;
        let passType = passTypeInputs[index].value.toLowerCase();
        if (passType == "adt") adt++;
        else if (passType == "chd") chd++;
        else if (passType == "inf") inf++;
      });
      // Get price info
      var adtDetailId = $(`input[name="${PREFIX}AdtDetailId[]"]`)
        .map((i, el) => el.value)
        .get();
      var chdDetailId = $(`input[name="${PREFIX}ChdDetailId[]"]`)
        .map((i, el) => el.value)
        .get();
      var infDetailId = $(`input[name="${PREFIX}InfDetailId[]"]`)
        .map((i, el) => el.value)
        .get();
      var adtFare = $(`input[name="${PREFIX}AdtFare[]"]`)
        .map((i, el) => el.value)
        .get();
      var chdFare = $(`input[name="${PREFIX}ChdFare[]"]`)
        .map((i, el) => el.value)
        .get();
      var infFare = $(`input[name="${PREFIX}InfFare[]"]`)
        .map((i, el) => el.value)
        .get();
      var adtTax = $(`input[name="${PREFIX}AdtTax[]"]`)
        .map((i, el) => el.value)
        .get();
      var chdTax = $(`input[name="${PREFIX}ChdTax[]"]`)
        .map((i, el) => el.value)
        .get();
      var infTax = $(`input[name="${PREFIX}InfTax[]"]`)
        .map((i, el) => el.value)
        .get();
      var adtPrice = $(`input[name="${PREFIX}AdtPrice[]"]`)
        .map((i, el) => el.value)
        .get();
      var chdPrice = $(`input[name="${PREFIX}ChdPrice[]"]`)
        .map((i, el) => el.value)
        .get();
      var infPrice = $(`input[name="${PREFIX}InfPrice[]"]`)
        .map((i, el) => el.value)
        .get();
      var listDetailId = [...adtDetailId, ...chdDetailId, ...infDetailId];

      // Roundtrip flight has the same airline or combine internation flight
      if (
        airlineCodes.length > 1 &&
        (airlineCodes[0] === airlineCodes[1] || isInter)
      ) {
        var searchInfo = {
          airlineCode: airlineCodes[0],
          depCode: depCodes[0],
          desCode: desCodes[0],
          depDate: departureDate[0],
          retDate: departureDate[1],
          adt: adt,
          chd: chd,
          inf: inf,
          isInter: isInter,
          flightNo: flightNo,
          listItineraryId: listItineraryId,
          adtFare: adtFare,
          chdFare: chdFare,
          infFare: infFare,
          adtTax: adtTax,
          chdTax: chdTax,
          infTax: infTax,
          adtPrice: adtPrice,
          chdPrice: chdPrice,
          infPrice: infPrice,
          adtDetailId: adtDetailId,
          chdDetailId: chdDetailId,
          infDetailId: infDetailId,
        };

        var flightResponse = {};
        if (statusAutoBook == 1) {
          let itiText = `Hành trình ${searchInfo["depCode"]} đi ${searchInfo["desCode"]}
                        <br />Hành trình ${searchInfo["desCode"]} đi ${searchInfo["depCode"]}`;

          showStepsInDialogAutoBook(step, itiText);
          flightResponse = await $.ajax({
            url: ENTRYPOINT,
            method: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({
              class: entryClass,
              method: "research",
              params: searchInfo,
            }),
          });

          // Success
          if (
            flightResponse &&
            "status" in flightResponse &&
            flightResponse.status == 1
          ) {
            itiText = `Hành trình ${searchInfo["depCode"]} đi ${searchInfo["desCode"]}${textItiSuccess}
                            <br />Hành trình ${searchInfo["desCode"]} đi ${searchInfo["depCode"]}${textItiSuccess}`;
            showStepsInDialogAutoBook(step, itiText);
            flightData = flightResponse.data.standardData;
          }
          // Fail
          else {
            let errorCode = flightResponse?.errorCode ?? "";
            let message =
              flightResponse?.message ?? "Lỗi, vui lòng thử lại sau";

            if (errorCode == "UNMATCHED_INFO") {
              showStepsInDialogAutoBook(
                step,
                itiText,
                "Thông tin chưa khớp, vui lòng kiểm tra lại",
              );

              let checkUpdatedDirection = "";
              Object.entries(flightResponse.data.updateData).forEach(
                ([key, value]) => {
                  if (key == "1") {
                    checkUpdatedDirection += key;

                    let retSearchInfo = searchInfo;
                    retSearchInfo.depCode = depCodes[1];
                    retSearchInfo.desCode = desCodes[1];
                    retSearchInfo.depDate = departureDate[1];
                    retSearchInfo.retDate = "";
                    showUpdateFlightData(retSearchInfo, value, entryClass);
                  } else {
                    checkUpdatedDirection += key;
                    showUpdateFlightData(searchInfo, value, entryClass);
                  }

                  let flight_info_id =
                    key == "1" ? "flight-info-ret" : "flight-info-dep";
                  if ($(`#${flight_info_id}`).length) {
                    $("#autobookForm").animate(
                      {
                        scrollTop: $(`#${flight_info_id}`).position().top,
                      },
                      500,
                    );
                  }
                },
              );

              if (isInter) {
                showStepsInDialogAutoBook(
                  step,
                  itiText,
                  "Thông tin chưa khớp, vui lòng kiểm tra lại",
                );
              } else if (checkUpdatedDirection == "0") {
                itiText = `Hành trình ${searchInfo["depCode"]} đi ${searchInfo["desCode"]}
                                    <br />Hành trình ${searchInfo["desCode"]} đi ${searchInfo["depCode"]}${textItiSuccess}`;
                showStepsInDialogAutoBook(
                  step,
                  itiText,
                  "Thông tin chưa khớp, vui lòng kiểm tra lại",
                );
              } else if (checkUpdatedDirection == "1") {
                itiText = `Hành trình ${searchInfo["depCode"]} đi ${searchInfo["desCode"]}${textItiSuccess}
                                    <br />Hành trình ${searchInfo["desCode"]} đi ${searchInfo["depCode"]}`;
                showStepsInDialogAutoBook(
                  step,
                  itiText,
                  "Thông tin chưa khớp, vui lòng kiểm tra lại",
                );
              }
            } else if (errorCode == "NOT_FOUND_FLIGHT") {
              if (
                message.includes(flightNo[0]) &&
                message.includes(flightNo[1])
              ) {
                showStepsInDialogAutoBook(step, itiText, message);
              } else if (message.includes(flightNo[0])) {
                itiText = `Hành trình ${searchInfo["depCode"]} đi ${searchInfo["desCode"]}
                                    <br />Hành trình ${searchInfo["desCode"]} đi ${searchInfo["depCode"]}${textItiSuccess}`;
                showStepsInDialogAutoBook(step, itiText, message);
              } else if (message.includes(flightNo[1])) {
                itiText = `Hành trình ${searchInfo["depCode"]} đi ${searchInfo["desCode"]}${textItiSuccess}
                                    <br />Hành trình ${searchInfo["desCode"]} đi ${searchInfo["depCode"]}`;
                showStepsInDialogAutoBook(step, itiText, message);
              } else {
                showStepsInDialogAutoBook(step, itiText, message);
              }
              return;
            } else {
              showStepsInDialogAutoBook(step, itiText, message);
            }
            return;
          }
        } else if (statusAutoBook == 0) {
          showStepsInDialogAutoBook(step, "", "Đã hủy quá trình đặt chỗ");
          return;
        }
      } else {
        var flightResponse = [];
        var itiText = [];
        for (let i = 0; i < airlineCodes.length; i++) {
          let searchInfo = {
            airlineCode: airlineCodes[i],
            depCode: depCodes[i],
            desCode: desCodes[i],
            depDate: departureDate[i],
            adt: adt,
            chd: chd,
            inf: inf,
            isInter: isInter,
            flightNo: [flightNo[i]],
            listItineraryId: [listItineraryId[i]],
            adtFare: [adtFare[i]],
            chdFare: [chdFare[i]],
            infFare: [infFare[i]],
            adtTax: [adtTax[i]],
            chdTax: [chdTax[i]],
            infTax: [infTax[i]],
            adtPrice: [adtPrice[i]],
            chdPrice: [chdPrice[i]],
            infPrice: [infPrice[i]],
            adtDetailId: [adtDetailId[i]],
            chdDetailId: [chdDetailId[i]],
            infDetailId: [infDetailId[i]],
          };

          if (statusAutoBook == 1) {
            itiText[i] =
              `Hành trình ${searchInfo["depCode"]} đi ${searchInfo["desCode"]}`;
            if (i > 0) {
              itiText[i - 1] = `${itiText[i - 1]}${textItiSuccess}</br>`;
              itiText[i] = itiText[i - 1] + itiText[i];
            }

            showStepsInDialogAutoBook(step, itiText[i]);
            flightResponse[i] = await $.ajax({
              url: ENTRYPOINT,
              method: "POST",
              contentType: "application/json",
              dataType: "json",
              data: JSON.stringify({
                class: entryClass,
                method: "research",
                params: searchInfo,
              }),
            });

            if (
              flightResponse[i] &&
              "status" in flightResponse[i] &&
              flightResponse[i].status == 1
            ) {
              if (i == airlineCodes.length - 1)
                showStepsInDialogAutoBook(step, itiText[i] + textItiSuccess);
              else showStepsInDialogAutoBook(step, itiText[i]);
              flightData.push(flightResponse[i].data.standardData[0]);
            } else {
              let errorCode = flightResponse[i]?.errorCode ?? "";
              let message =
                flightResponse[i]?.message ?? "Lỗi, vui lòng thử lại sau";

              if (errorCode == "UNMATCHED_INFO") {
                showStepsInDialogAutoBook(
                  step,
                  itiText[i],
                  "Thông tin chưa khớp, vui lòng kiểm tra lại",
                );
                showUpdateFlightData(
                  searchInfo,
                  flightResponse[i].data.updateData[0] ?? {},
                  entryClass,
                );

                let flight_info_id =
                  i == 1 ? "flight-info-ret" : "flight-info-dep";
                if ($(`#${flight_info_id}`).length) {
                  $("#autobookForm").animate(
                    {
                      scrollTop: $(`#${flight_info_id}`).position().top,
                    },
                    500,
                  );
                }
              } else {
                showStepsInDialogAutoBook(step, itiText[i], message);
              }
              return;
            }

            if (i == airlineCodes.length - 1)
              showStepsInDialogAutoBook(step, itiText[i] + textItiSuccess);
          } else if (statusAutoBook == 0) {
            showStepsInDialogAutoBook(step, "", "Đã hủy quá trình đặt chỗ");
            return;
          }
        }
      }

      /******  STEP 2: VERIFY & PREPARE DATA TO BOOKING  ******/
      step = 2;
      var isWithin24h = within24h.includes("1") ? 1 : 0;
      var requestBody = {}; // Data for next step
      if (entryClass == "entryAutoBookPhuongNamClass") {
        if (statusAutoBook == 1) {
          const timeoutShowStep2 = setTimeout(() => {
            showStepsInDialogAutoBook(step);
          }, 300);

          if (Object.keys(flightData).length > 0) {
            // Contact
            let contactPhone = $(`input[name="${PREFIX}ContactPhone"]`)
              .val()
              .trim();
            let contactInfo = {
              Title: $(`input[name="${PREFIX}ContactTitle"]`)
                .val()
                .replace(/\./g, "")
                .trim(), // Mr
              Name: $(`input[name="${PREFIX}ContactName"]`).val().trim(),
              Phone: contactPhone,
              Email: $(`input[name="${PREFIX}ContactEmail"]`).val().trim(),
              Address: $(`input[name="${PREFIX}ContactAddress"]`).val().trim(),
            };

            // Passengers
            let listPassenger = [];
            let passTitleInputs = document.querySelectorAll(
              `input[name="${PREFIX}PassengerTitle[]"]`,
            );
            let passLastNameInputs = document.querySelectorAll(
              `input[name="${PREFIX}PassengerLastName[]"]`,
            );
            let passFirstNameInputs = document.querySelectorAll(
              `input[name="${PREFIX}PassengerFirstName[]"]`,
            );
            let passPassportTypeInputs = document.querySelectorAll(
              `input[name="${PREFIX}PassengerPassportType[]"]`,
            );
            let passPassportInputs = document.querySelectorAll(
              `input[name="${PREFIX}PassengerPassportNum[]"]`,
            );
            let passPassportExpiredDateInputs = document.querySelectorAll(
              `input[name="${PREFIX}PassengerPassportExpiredDate[]"]`,
            );
            let passPassportNationalityInputs = document.querySelectorAll(
              `input[name="${PREFIX}PassengerPassportNationality[]"]`,
            );
            let passPassportIssueCountryInputs = document.querySelectorAll(
              `input[name="${PREFIX}PassengerPassportIssueCountry[]"]`,
            );
            let passParentIdInputs = document.querySelectorAll(
              `select[name="${PREFIX}PassengerParentId[]"]`,
            );
            passIdInputs.forEach((input, index) => {
              let title = passTitleInputs[index].value;
              let gender = title == "Ms" ? "F" : "M";
              let type = passTypeInputs[index].value.toLowerCase();
              let passengerTypeId = null;
              let parentId = parseInt(passParentIdInputs[index].value);

              if (type != "adt") title = null;
              if (type == "adt") {
                passengerTypeId = 1;
                parentId = null;
              } else if (type == "chd") {
                passengerTypeId = 6;
                parentId = null;
              } else if (type == "inf") {
                passengerTypeId = 5;
                parentId++;
              }

              listPassenger.push({
                PersonOrgId: (index + 1).toString(),
                RowNumber: index + 1,
                SortOrder: index + 1,
                PassengerTypeId: passengerTypeId,
                ParentGuestId: parentId,
                FirstName   : passFirstNameInputs[index].value.trim(),
                LastName    : passLastNameInputs[index].value.trim(),
                BirthDay    : passDateOfBirthInputs[index].value,
                Gender      : gender,
                Title       : "",
                Phone       : type != "inf" ? contactInfo.Phone : null,
                Email       : type != "inf" ? contactInfo.Email : null,
                PassportType    : passPassportTypeInputs[index].value || null,
                Passport        : passPassportInputs[index].value || null,
                PassportExpired : passPassportExpiredDateInputs[index].value || null,
                Nationality     : passPassportNationalityInputs[index].value || null,
                PassportIssuer  : passPassportIssueCountryInputs[index].value || null,
              });
            });

            let verifyResponse = await $.ajax({
              url: ENTRYPOINT,
              method: "POST",
              contentType: "application/json",
              dataType: "json",
              data: JSON.stringify({
                class: entryClass,
                method: "verify",
                params: {
                  bookingId: bookingId,
                  isWithin24h: isWithin24h,
                  flights: flightData,
                  contact: contactInfo,
                  listPassenger: listPassenger,
                },
              }),
            });

            if (
              !verifyResponse.hasOwnProperty("status") ||
              verifyResponse.status == 0 ||
              !verifyResponse.hasOwnProperty("requestBody") ||
              verifyResponse.requestBody.length == 0
            ) {
              clearTimeout(timeoutShowStep2);
              showStepsInDialogAutoBook(
                step,
                "",
                verifyResponse.hasOwnProperty("message")
                  ? verifyResponse.message
                  : "Lỗi, vui lòng thử lại sau",
              );
              return;
            } else {
              requestBody = verifyResponse.requestBody;
            }
          } else {
            if (timeoutShowStep2) clearTimeout(timeoutShowStep2);
            showStepsInDialogAutoBook(step, "", "Thiếu thông tin xác thực");
            console.error(flights);
            return;
          }
        } else if (statusAutoBook == 0) {
          clearTimeout(timeoutShowStep2);
          showStepsInDialogAutoBook(step, "", "Đã hủy quá trình đặt chỗ");
          return;
        }
      } else {
        if (statusAutoBook == 1) {
          const timeoutShowStep2 = setTimeout(() => {
            showStepsInDialogAutoBook(step);
          }, 300);

          if (
            !isInter &&
            airlineCodes.length > 1 &&
            airlineCodes[0] != airlineCodes[1]
          ) {
            showStepsInDialogAutoBook(
              step,
              caption,
              "Phải giữ chung 1 hãng",
            );
            return;
          }

          // Flight
          requestBody.ListAirOption = flightData;

          // Contact
          let contactPhone = $(`input[name="${PREFIX}ContactPhone"]`)
            .val()
            .trim();
          if (contactPhone.startsWith("0"))
            contactPhone = contactPhone.substring(1);
          requestBody.GuestContact = {
            Title: $(`input[name="${PREFIX}ContactTitle"]`)
              .val()
              .replace(/\./g, "")
              .trim()
              .toUpperCase(), // MR
            Name: $(`input[name="${PREFIX}ContactName"]`).val().trim(),
            Area: "+84",
            Phone: contactPhone,
            Email: $(`input[name="${PREFIX}ContactEmail"]`).val().trim(),
            Address: $(`input[name="${PREFIX}ContactAddress"]`).val().trim(),
            Remark: "",
            ReceiveEmail: true,
          };
          if (
            airlineCodes.includes("VJ") &&
            requestBody.GuestContact.Address.length > 50
          ) {
            showStepsInDialogAutoBook(
              step,
              caption,
              "Vietjet địa chỉ liên hệ tối đa 50 ký tự",
            );
            return;
          }

          // Passengers
          requestBody.ListPassenger = [];
          let passTitleInputs = document.querySelectorAll(
            `input[name="${PREFIX}PassengerTitle[]"]`,
          );
          let passLastNameInputs = document.querySelectorAll(
            `input[name="${PREFIX}PassengerLastName[]"]`,
          );
          let passFirstNameInputs = document.querySelectorAll(
            `input[name="${PREFIX}PassengerFirstName[]"]`,
          );
          let passDateOfBirthInputs = document.querySelectorAll(
            `input[name="${PREFIX}PassengerDateOfBirth[]"]`,
          );
          let passParentIdInputs = document.querySelectorAll(
            `select[name="${PREFIX}PassengerParentId[]"]`,
          );
          let passPassportNumInputs = document.querySelectorAll(
            `input[name="${PREFIX}PassengerPassportNum[]"]`,
          );
          let passPassportTypeInputs = document.querySelectorAll(
            `input[name="${PREFIX}PassengerPassportType[]"]`,
          );
          let passPassportExpiredDateInputs = document.querySelectorAll(
            `input[name="${PREFIX}PassengerPassportExpiredDate[]"]`,
          );
          let passPassportNationalityInputs = document.querySelectorAll(
            `input[name="${PREFIX}PassengerPassportNationality[]"]`,
          );
          let passPassportIssueCountryInputs = document.querySelectorAll(
            `input[name="${PREFIX}PassengerPassportIssueCountry[]"]`,
          );
          passIdInputs.forEach((input, index) => {
            let title = passTitleInputs[index].value;
            let gender = title == "Ms" ? 0 : 1;
            let type = passTypeInputs[index].value.toUpperCase();
            let firstName = passFirstNameInputs[index].value.trim();
            let parentId = parseInt(passParentIdInputs[index].value);

            if (type == "INF") {
              parentId++;
              if (airlineCodes.includes("QH")) {
                firstName = getFirstName(passFirstNameInputs[index].value);
              } else if (airlineCodes.includes("9G")) {
                title = title == "Mr" ? "MSTR" : "MISS";
              }
            }
            if (airlineCodes.includes("9G")) {
              title = title.toUpperCase();
            }

            requestBody.ListPassenger.push({
              Index: index + 1,
              ParentId: parentId,
              Type: type,
              Title: title,
              Gender: gender,
              Surname: passLastNameInputs[index].value.trim(),
              GivenName: firstName,
              DateOfBirth: passDateOfBirthInputs[index].value.split("-").reverse().join(""),
              Passport: {
                "DocumentType": DATACOM_DOCUMENT_TYPE_MAP[passPassportTypeInputs[index].value.trim()] || null,
                "DocumentCode": passPassportNumInputs[index].value.trim() || null,
                "DocumentExpiry": passPassportExpiredDateInputs[index].value.split("-").reverse().join("") || null,
                "Nationality": passPassportNationalityInputs[index].value.trim() || null,
                "IssueCountry": passPassportIssueCountryInputs[index].value.trim() || null
              }
            });
          });

          // Option
          const issueTicket =
            isWithin24h && ["VJ"].includes(airlineCodes[0]) ? true : false;
          requestBody.Option = {
            IssueTicket: issueTicket,
            SeparateBooking: false,
          };
        } else if (statusAutoBook == 0) {
          if (timeoutShowStep2) clearTimeout(timeoutShowStep2);
          showStepsInDialogAutoBook(step, "", "Đã hủy quá trình đặt chỗ");
          return;
        }
      }

      /******  STEP 3: BOOKING  ******/
      step = 3;
      var bookingResponse = {};
      if (statusAutoBook == 1) {
        let caption = "Đặt chỗ";
        let isIssueTicket = false;
        if (isWithin24h) {
          if (
            entryClass == "entryAutoBookPhuongNamClass" &&
            ["VJ"].includes(airlineCodes[0])
          ) {
            caption = "Xuất vé cận";
            isIssueTicket = true;
          } else if (
            entryClass == "entryAutoBookDatacomClass" &&
            ["VJ"].includes(airlineCodes[0])
          ) {
            caption = "Xuất vé cận";
            isIssueTicket = true;
          }
        }

        showStepsInDialogAutoBook(step, caption + "...");
        $("#btnAutoBookAction").prop("disabled", true); // Disable button action

        try {
          bookingResponse = await $.ajax({
            url: ENTRYPOINT,
            method: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({
              class: entryClass,
              method: "booking",
              params: {
                bookingId: bookingId,
                listPassengerId: listPassengerId,
                listItineraryId: listItineraryId,
                listDetailId: listDetailId,
                requestBody: requestBody,
              },
            }),
          });

          // Enable button action
          $("#btnAutoBookAction").html("Đóng");
          $("#btnAutoBookAction").attr("action", "close");
          $("#btnAutoBookAction").prop("disabled", false);

          if (
            !bookingResponse ||
            !bookingResponse.status ||
            bookingResponse.status == 0
          ) {
            showStepsInDialogAutoBook(
              step,
              caption,
              bookingResponse.message ??
                "Lỗi, vui lòng kiểm tra code vé và thử lại",
            );
            return;
          }

          // Display BookingCodes (PNR) to client
          caption = isIssueTicket ? "Xuất vé thành công" : "Đặt chỗ thành công";
          if (entryClass == "entryAutoBookPhuongNamClass") {
            const bookingCodes = bookingResponse.data.map(
              (item) => item.BookingCode,
            );
            bookingCodes.forEach((code) => {
              caption +=
                caption.length == 0 ? `<b>${code}</b>` : `<br/><b>${code}</b>`;
            });
          } else {
            const bookingCodes = bookingResponse.data.ListBooking.map(
              (item) => `${item.Airline}: ${item.GdsCode ?? item.BookingCode}`,
            );
            bookingCodes.forEach((code) => {
              caption +=
                caption.length == 0 ? `<b>${code}</b>` : `<br/><b>${code}</b>`;
            });
          }
          showStepsInDialogAutoBook(step, caption, "", 1);
        } catch (e) {
          showStepsInDialogAutoBook(
            step,
            caption,
            bookingResponse ? JSON.stringify(bookingResponse) : e.stack,
          );
        }
      } else if (statusAutoBook == 0) {
        showStepsInDialogAutoBook(step, "", "Đã hủy quá trình đặt chỗ");
        return;
      }
    } catch (e) {
      hideDialogAutoBook();
      handleException(e);
    }
  });

  $(document).on("click", "#btnAutoBookAction", function () {
    let action = $(this).attr("action");

    if (action == "cancel") {
      if (statusAutoBook == 1) {
        statusAutoBook = 0;
        $(this).attr("action", "close");
        $(this).html("Đóng");
        $(this).prop("disabled", true);
      } else if (statusAutoBook == 0) {
        statusAutoBook = 1;
        $("#autoBookDialog .loading-overlay").html("");
        $("#autoBookDialog .loading-overlay").removeClass("active");
      }
    } else if (action == "close") {
      $("#autoBookDialog .loading-overlay").html("");
      $("#autoBookDialog .loading-overlay").removeClass("active");
    } else if (action == "complete") {
      hideDialogAutoBook();
      $(".container-waiting").show();
      setTimeout(function () {
        location.reload();
      }, 500);
    }
  });

  // Show policy details related to a note in the auto-book dialog
  $(document).on("click", ".policy-info-trigger", function () {
    const policy = POLICY_INFO[$(this).data("policy")];
    if (!policy) return;
    showPolicyInfoNotify(policy.title, policy.content);
  });

  // Update data (flight datetime, fares) to BM
  $(document).on("click", ".btn-update-auto-book", function () {
    const data = $(this).attr("data");
    const entryClass = $(this).attr("data-entry-class");

    if (data && data.length > 0) {
      $.ajax({
        url: ENTRYPOINT,
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
          class: entryClass,
          method: "updateDataBooking",
          params: decodeAutoBook(data),
        }),
        beforeSend: function () {
          hideDialogAutoBook();
          $(".container-waiting").show();
        },
        success: function (response) {
          try {
            const objData = JSON.parse(response);
            if (objData.status == 1) {
              $(`.btn-auto-book[data-entry-class="${entryClass}"]`).trigger(
                "click",
              );
              return;
            } else {
              $(".container-waiting").hide();
              hideDialogAutoBook();
              showModalNotify(
                "error",
                objData.message ??
                  "Cập nhật không thành công, vui lòng F5 và thử lại",
              );
              console.error(objData);
            }
          } catch (e) {
            $(".container-waiting").hide();
            hideDialogAutoBook();
            handleException(
              e,
              "Cập nhật không thành công, vui lòng F5 và thử lại",
            );
          }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          $(".container-waiting").hide();
          console.error(XMLHttpRequest);
          console.error("Status: " + textStatus);
          console.error("Error: " + errorThrown);
        },
      });
    }
  });
});

function showDialogAutoBook(bookingData) {
  const existingDialog = document.getElementById("autoBookDialog");
  if (existingDialog) existingDialog.remove(); // Prevent multiple dialogs

  const dialog = document.createElement("dialog");
  dialog.className = "auto-book-dialog";
  dialog.id = "autoBookDialog";

  const autobookForm = document.createElement("form");
  autobookForm.id = "autobookForm";
  autobookForm.className = "dialog-form";

  // Header
  const header = document.createElement("div");
  header.className = "header autobookFormHeader";
  header.id = "autobookFormHeader";
  header.innerHTML = `<h4 class="title">Auto book ${bookingData.supplierName}</h4>`;
  const closeBtn = document.createElement("button");
  closeBtn.className = "close-btn";
  closeBtn.textContent = "✕";
  closeBtn.onclick = () => dialog.remove();
  header.appendChild(closeBtn);

  const content = document.createElement("div");

  // Render Flight + Fare Section
  var BookingWithin24h = false;
  const { dep, ret } = bookingData.itineraries;
  const depFare = bookingData.fareDetails.dep;
  const retFare = bookingData.fareDetails.ret ?? [];
  const passengerTypes = { 0: "Người lớn", 1: "Trẻ em", 2: "Em bé" };
  const passengerTextTypes = { 0: "Adt", 1: "Chd", 2: "Inf" };
  const passportTypeLabels = { I: "CCCD/ID", P: "Passport" };

  /**
   * Render flight and fare HTML
   *
   * @param {Object} flight
   * @param {Array} fareArray
   * @param {String} dir
   * @param {Object} flight2 Using for combine inter (Roundtrip)
   * @returns {String} HTML
   */
  function renderFlightWithFare(flight, fareArray, dir, flight2) {
    var totalAmount = 0;
    var title = dir == "ret" ? "✈️ Chuyến về" : "✈️ Chuyến đi";
    var label = dir == "ret" ? "chuyến về" : "chuyến đi";
    var direction = dir == "ret" ? 1 : 0;

    // Create fare HTML for each type: Adult, Child, Infant
    const fareColumns = ["0", "1", "2"]
      .map((type) => {
        const fare = fareArray[parseInt(type)];
        if (!fare) return "";

        totalAmount += fare.price * fare.qty;

        return `<div class="fare-column">
                <input type="hidden" name="autobook${passengerTextTypes[type]}DetailId[]" value="${fare.id}" readonly />
                <input type="hidden" name="autobook${passengerTextTypes[type]}[]" value="${fare.qty}" readonly />
                <input type="hidden" name="autobook${passengerTextTypes[type]}Fare[]" value="${fare.fare}" readonly />
                <input type="hidden" name="autobook${passengerTextTypes[type]}Tax[]" value="${fare.tax}" readonly />
                <input type="hidden" name="autobook${passengerTextTypes[type]}Fee[]" value="${fare.fee}" readonly />
                <input type="hidden" name="autobook${passengerTextTypes[type]}Price[]" value="${fare.price}" readonly />
                <input type="hidden" id="autobook${passengerTextTypes[type]}Fare${flight.depCode}${flight.desCode}" value="${fare.fare}" readonly />
                <input type="hidden" id="autobook${passengerTextTypes[type]}Tax${flight.depCode}${flight.desCode}" value="${fare.tax}" readonly />
                <input type="hidden" id="autobook${passengerTextTypes[type]}Fee${flight.depCode}${flight.desCode}" value="${fare.fee}" readonly />
                <input type="hidden" id="autobook${passengerTextTypes[type]}Price${flight.depCode}${flight.desCode}" value="${fare.price}" readonly />

                <div class="info-row"><b>${passengerTypes[type]} x1</b></div>
                <div class="info-row">
                    <div class="lbl">Giá vé:</div>
                    <div class="value" id="autobook${passengerTextTypes[type]}Fare${flight.depCode}${flight.desCode}Display">
                        <span class="old-value"></span>
                        <span class="cur-value">${fare.fareFormat}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="lbl">Thuế (VAT):</div>
                    <div class="value" id="autobook${passengerTextTypes[type]}Tax${flight.depCode}${flight.desCode}Display">
                        <span class="old-value"></span>
                        <span class="cur-value">${fare.taxFormat}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="lbl">Phí:</div>
                    <div class="value" id="autobook${passengerTextTypes[type]}Fee${flight.depCode}${flight.desCode}Display">
                        <span class="old-value"></span>
                        <span class="cur-value">${fare.feeFormat}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="lbl">Tổng mua:</div>
                    <div class="value" id="autobook${passengerTextTypes[type]}Price${flight.depCode}${flight.desCode}Display">
                        <b class="old-value"></b>
                        <b class="cur-value">${fare.priceFormat}</b>
                    </div>
                </div>
            </div>`;
      })
      .join("");

    // Only display the fare section if there is at least one fare column
    if (!fareColumns) return "";

    // Handle flight2 object
    let iti2 = "";
    if (flight2 && Object.keys(flight2).length > 0) {
      label = "";
      iti2 = `
                <input type="hidden" name="autobookItineraryId[]" value="${flight2.id}" readonly />
                <input type="hidden" name="autobookAirlineCode[]" value="${flight2.airlineCode}" readonly />
                <input type="hidden" name="autobookDepCode[]" value="${flight2.depCode}" readonly />
                <input type="hidden" name="autobookDesCode[]" value="${flight2.desCode}" readonly />
                <input type="hidden" name="autobookDepartureDate[]" value="${flight2.departureDate}" readonly />
                <input type="hidden" name="autobookTicketClass[]" value="${flight2.ticketClass}" readonly />
                <input type="hidden" name="autobookFlightNo[]" value="${flight2.flightNo}" readonly />
                <input type="hidden" name="autobookWithin24h[]" value="0" readonly />

                <div class="section-title">
                    ✈️ Chuyến về
                    <img class="ms-3" src="${getLinkImageAirline(flight2.airlineCode)}" alt="${flight2.airlineCode}" />
                </div>
                <div class="info-row d-flex justify-content-between">
                    <div>Hành trình: <b>${flight2.depCode} → ${flight2.desCode}</b></div>
                    <div>Mã chuyến: <b>${flight2.flightNo}</b></div>
                </div>
                <div class="info-row d-flex justify-content-between mb-3">
                    <div class="d-flex gap-1">
                        <div>Ngày giờ bay:</div>
                        <div id="autobookDepartureDate${flight2.depCode}${flight2.desCode}Display">
                            <span class="old-value"></span>
                            <b class="cur-value">${flight2.departureDate.replace(" ", " lúc ")}</b>
                        </div>
                    </div>
                    <div>Hạng vé: <b>${flight2.ticketClass}</b></div>
                </div>
            `;
    }

    if (!BookingWithin24h)
      BookingWithin24h = flight.within24h && flight.airlineCode == "VJ";
    return `<div id="flight-info-${dir}" class="flight-info">
            <input type="hidden" name="autobookItineraryId[]" value="${flight.id}" readonly />
            <input type="hidden" name="autobookAirlineCode[]" value="${flight.airlineCode}" readonly />
            <input type="hidden" name="autobookDepCode[]" value="${flight.depCode}" readonly />
            <input type="hidden" name="autobookDesCode[]" value="${flight.desCode}" readonly />
            <input type="hidden" name="autobookDepartureDate[]" value="${flight.departureDate}" readonly />
            <input type="hidden" name="autobookTicketClass[]" value="${flight.ticketClass}" readonly />
            <input type="hidden" name="autobookFlightNo[]" value="${flight.flightNo}" readonly />
            <input type="hidden" name="autobookWithin24h[]" value="${flight.within24h}" readonly />

            <div class="section-title">
                ${title}
                <img class="ms-3" src="${getLinkImageAirline(flight.airlineCode)}" alt="${flight.airlineCode}" style="height:40px" />
                ${flight.within24h ? '<span class="within24h">Vé cận</span>' : ""}
            </div>
            <div class="info-row d-flex justify-content-between">
                <div>Hành trình: <b>${flight.depCode} → ${flight.desCode}</b></div>
                <div>Mã chuyến: <b>${flight.flightNo}</b></div>
            </div>
            <div class="info-row d-flex justify-content-between">
                <div class="d-flex gap-1">
                    <div>Ngày giờ bay:</div>
                    <div id="autobookDepartureDate${flight.depCode}${flight.desCode}Display">
                        <span class="old-value"></span>
                        <b class="cur-value">${flight.departureDate.replace(" ", " lúc ")}</b>
                    </div>
                </div>
                <div>Hạng vé: <b>${flight.ticketClass}</b></div>
            </div>
            ${iti2}
            <div class="info-row mt-1"><b>💰 Chi tiết giá vé</b></div>
            <div class="fare-row">${fareColumns}</div>
            <div class="d-flex justify-content-end gap-2 mt-2">
                <label style="font-size:15px">Tổng mua ${label}: </label>
                <b title="Đã gồm số lượng HK bên dưới" style="color:red !important; font-size:15px; text-align:right">
                    <p id="autobookTotalAmount${flight.depCode}${flight.desCode}Display" class="old-value"></p>
                    <input type="text" value="${formatNumber(totalAmount)} VND" id="autobookTotalAmount${flight.depCode}${flight.desCode}" class="npvalue" readonly />
                </b>
            </div>
            <center>
                <button type="button" id="btnUpdate${flight.depCode}${flight.desCode}" class="btn-update-auto-book btn btn-warning mt-2" direction="${direction}" style="display:none">Cập nhật</button>
            </center>
        </div>`;
  }
  if (isInter && dep && ret) {
    content.innerHTML += renderFlightWithFare(dep, depFare, "dep", ret);
  } else {
    if (dep) content.innerHTML += renderFlightWithFare(dep, depFare, "dep");
    if (ret) content.innerHTML += renderFlightWithFare(ret, retFare, "ret");
  }

  // Passengers
  var optionParentId = "";
  var passengersHTML = "";
  Object.entries(bookingData.passengers).forEach(([key, value], index) => {
    if (value.type === "0") {
      optionParentId += `<option value="${index}" data-id="${key}">${index + 1}. ${value.name}</option>`;
    }

    let parentIdHTML = '<select name="autobookPassengerParentId[]" style="display:none"><option value="-1" selected></option></select>';
    if (value.type === "2") {
      parentIdHTML = `<div class="info-row d-flex align-items-center gap-2">
        <label for="selectparent${index}" class="form-label fw-normal m-0">Đi kèm người lớn:</label>
        <select name="autobookPassengerParentId[]" id="selectparent${index}" class="form-select form-select-sm w-50">
          ${optionParentId}
        </select>
      </div>`;
    }

    passengersHTML += `<div class="passenger-info">
      <input type="hidden" name="autobookPassengerId[]" value="${value.id}" readonly />
      <input type="hidden" name="autobookPassengerType[]" value="${passengerTextTypes[value.type]}" readonly />
      <input type="hidden" name="autobookPassengerTitle[]" value="${value.salutation}" readonly />
      <input type="hidden" name="autobookPassengerDateOfBirth[]" value="${value.dateOfBirth}" readonly />
      <input type="hidden" name="autobookPassengerPassportNum[]" value="${value.passportNumber}" readonly />
      <input type="hidden" name="autobookPassengerPassportType[]" value="${value.passportType}" readonly />
      <input type="hidden" name="autobookPassengerPassportExpiredDate[]" value="${value.passportExpiredDate}" readonly />
      <input type="hidden" name="autobookPassengerPassportIssueDate[]" value="${value.passportIssueDate}" readonly />
      <input type="hidden" name="autobookPassengerPassportIssueCountry[]" value="${value.passportIssueCountry}" readonly />
      <input type="hidden" name="autobookPassengerPassportNationality[]" value="${value.passportNationality}" readonly />
      <div class="info-row d-flex align-items-center gap-2">
        <span style="font-weight:700;color:${value.salutation == "Ms" ? "#f7689e" : "#2d87d5"}">${value.salutation}.</span>
        <input type="text" name="autobookPassengerLastName[]" class="passenger-name-input passenger-lastname-input" value="${getLastName(value.name)}" placeholder="Họ" />
        <input type="text" name="autobookPassengerFirstName[]" class="passenger-name-input passenger-firstname-input" value="${getMiddleAndFirstName(value.name)}" placeholder="Tên" />
      </div>
      <div class="info-row passenger-detail-row">
        <div>Loại: <b class="passenger-type-label">${passengerTypes[value.type]}</b></div>
        <div>Ngày sinh: <b>${value.dateOfBirth}</b></div>
        <div>${passportTypeLabels[value.passportType] || "CCCD/Passport"}: <b>${value.passportNumber || "-"}</b></div>
        <div>Hết hạn: <b>${value.passportExpiredDate || "-"}</b></div>
        <div>Quốc tịch: <b>${value.passportNationality || "-"}</b></div>
        <div>Nơi cấp: <b>${value.passportIssueCountry || "-"}</b></div>
        <div>Ngày cấp: <b>${value.passportIssueDate || "-"}</b></div>
      </div>
      ${parentIdHTML}
    </div>`;
  });
  content.innerHTML += `<div class="section">
    <div class="section-title">Thông tin hành khách</div>
    ${passengersHTML}
  </div>`;

  // Contact
  const contactInfo = bookingData.contact;
  const contactHTML = `<div class="contact-info">
  <div class="section-title">Thông tin liên hệ</div>
  <div class="info-row contact-field">
    <label>Tên liên hệ</label>
    <input type="text" name="autobookContactName" value="${contactInfo.title || ''} ${contactInfo.name || ''}" class="contact-input" maxlength="60" readonly />
    <input type="hidden" name="autobookContactTitle" value="${contactInfo.title || ''}" readonly />
  </div>
  <div class="info-row contact-field">
    <label>Số điện thoại</label>
    <input type="text" name="autobookContactPhone" value="${contactInfo.phone || ''}" class="contact-input" maxlength="12" />
  </div>
  <div class="info-row contact-field">
    <label>Email</label>
    <input type="text" name="autobookContactEmail" value="${contactInfo.email || ''}" class="contact-input" />
  </div>
  <div class="info-row contact-field">
    <label>Địa chỉ</label>
    <input type="text" name="autobookContactAddress" value="${contactInfo.address || ''}" class="contact-input" />
  </div>
  </div>`;
  content.innerHTML += contactHTML;

  // Note
  let noteHTML = `<div class="note p-2 mt-3" style="background:#e0ecfc">
    ${BookingWithin24h ? '<p style="color:red">- Đây là <b>vé cận</b>, sẽ tiến hành thanh toán ngay.</p>' : "<p>- <b>Vé cận VJ, VU, QH</b> sẽ tiến hành thanh toán ngay.</p>"}
    <p>- Vui lòng bổ sung đầy đủ thông tin ngày sinh, giấy tờ thùy thân đối với hãng <b>VJ</b>, <b>VN</b>. <a class="policy-info-trigger" data-policy="identity-docs">Xem chi tiết</a></p>
    <p>- Tên hành khách hoặc em bé đi cùng quá dài vui lòng điều chỉnh theo quy định. <a class="policy-info-trigger" data-policy="baby-name">Xem chi tiết</a></p>
    <p>- Kiểm tra kỹ càng thông tin trước khi xác nhận.</p>
  </div>`;
  content.innerHTML += noteHTML;

  // More input data
  const supplierClassInput = document.createElement("input");
  supplierClassInput.type = "hidden";
  supplierClassInput.value = bookingData.entryClass;
  supplierClassInput.id = "entryClass";
  supplierClassInput.name = "entryClass";

  // Footer
  const footer = document.createElement("div");
  footer.className = "dialog-footer";
  // Create Confirm button
  const confirmBtn = document.createElement("button");
  confirmBtn.id = "confirmAutoBook";
  confirmBtn.className = "dialog-btn confirm-btn";
  confirmBtn.type = "button";
  confirmBtn.textContent = "Xác nhận";
  // Create Cancel button
  const cancelBtn = document.createElement("button");
  cancelBtn.className = "dialog-btn cancel-btn";
  cancelBtn.textContent = "Huỷ";
  cancelBtn.addEventListener("click", () => {
    dialog.remove();
  });
  // Add both buttons to footer
  footer.appendChild(confirmBtn);
  footer.appendChild(cancelBtn);

  // Loading
  const loadingOverlay = document.createElement("div");
  loadingOverlay.className = "loading-overlay";

  autobookForm.appendChild(header);
  autobookForm.appendChild(content);
  autobookForm.appendChild(supplierClassInput);
  autobookForm.appendChild(footer);
  // autobookForm.appendChild(loadingOverlay);

  // Create the wrapper div
  const formWrapper = document.createElement("div");
  formWrapper.classList.add("form-wrapper");
  formWrapper.appendChild(autobookForm);
  formWrapper.appendChild(loadingOverlay);

  dialog.appendChild(formWrapper);
  document.body.appendChild(dialog);
  dialog.showModal();

  $("#autoBookDialog").draggable({
    handle: "#autobookFormHeader",
  }); // Using only by Jquery
}

function hideDialogAutoBook() {
  let dialog = document.getElementById("autoBookDialog");
  if (dialog) dialog.remove();
}

function showStepsInDialogAutoBook(
  current_step = 1,
  current_caption = "",
  current_error = "",
  is_finished = 0,
) {
  // Show overlay
  const dialog = $("#autoBookDialog");
  const dialogOverlay = dialog.find(".loading-overlay");
  // dialogOverlay.css('height', dialog[0].scrollHeight + 'px').addClass('active');

  let icon_finished = `<svg width="22px" height="22px" viewBox="0 0 16 16" stroke="#fff" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><polyline points="2.75 8.75,6.25 12.25,13.25 4.75"></polyline></g></svg>`;
  let icon_error = `<svg width="18px" height="18px" class="me-1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:sub;"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm-1.5-5.009c0-.867.659-1.491 1.491-1.491.85 0 1.509.624 1.509 1.491 0 .867-.659 1.509-1.509 1.509-.832 0-1.491-.642-1.491-1.509zM11.172 6a.5.5 0 0 0-.499.522l.306 7a.5.5 0 0 0 .5.478h1.043a.5.5 0 0 0 .5-.478l.305-7a.5.5 0 0 0-.5-.522h-1.655z" fill="#ff0000"></path></g></svg>`;
  let steps = {
    1: {
      title: "Đối sánh thông tin chuyến bay",
      caption: "",
      error: "",
    },
    2: {
      title: "Xác thực thông tin",
      caption: "Quá trình xác thực với NCC",
      error: "",
    },
    3: {
      title: "Tiến hành đặt chỗ trên hãng",
      caption: "",
      error: "",
    },
  };

  let buttonAction = "cancel";
  let stepsHTML = "";
  $.each(steps, function (stepNum, stepData) {
    let stepClass = "";
    if (stepNum == current_step) stepClass = "step-active";
    else if (stepNum < current_step) stepClass = "step-finished";
    if (is_finished == 1) stepClass = "step-finished";
    stepClass += stepNum == Object.keys(steps).length ? " last-step" : "";

    let caption = "",
      error = "",
      loaderHTML = "";
    if (stepNum == current_step) {
      caption =
        current_caption && current_caption.length > 0
          ? current_caption
          : stepData.caption;
      error =
        current_error && current_error.length > 0
          ? current_error
          : stepData.error;
      if (error.length == 0 && is_finished == 0 && statusAutoBook == 1)
        loaderHTML = '<div><div class="loader-step"></div></div>';
      else if (error.length > 0) {
        error = icon_error + error;
        buttonAction = "close";
      }
    } else {
      caption = stepData.caption;
    }

    stepsHTML += `<div class="step ${stepClass}">
            <div>
                <div class="circle">${stepNum < current_step || is_finished == 1 ? icon_finished : stepNum}</div>
            </div>
            <div>
                <div class="title">${stepData.title}</div>
                <div class="caption">${caption}</div>
                <div class="error">${error}</div>
            </div>
            ${loaderHTML}
        </div>`;
  });

  let buttonClass = "btn-secondary";
  let buttonText = buttonAction == "cancel" ? "Hủy" : "Đóng";
  if (is_finished == 1) {
    buttonAction = "complete";
    buttonClass = "btn-primary";
    buttonText = "Hoàn tất";
  }
  let content = `<div class="loading-content">
        ${stepsHTML}
        <div class="buttons">
            <button type="button" id="btnAutoBookAction" class="btn ${buttonClass}" action="${buttonAction}">${buttonText}</button>
        </div>
    </div>`;
  dialogOverlay.html(content);
  dialogOverlay.addClass("active");
}

function showUpdateFlightData(searchData, updateData, entryClass) {
  let depCode = searchData?.depCode ?? "";
  let desCode = searchData?.desCode ?? "";
  let adtCount = searchData?.adt ?? 0;
  let chdCount = searchData?.chd ?? 0;
  let infCount = searchData?.inf ?? 0;

  // Update departure date
  if ("departureDate" in updateData) {
    let oldDate = $(
      `#${PREFIX}DepartureDate${depCode}${desCode}Display .cur-value`,
    ).text();
    let newDate = updateData.departureDate.replace(" ", " lúc ");
    $(`#${PREFIX}DepartureDate${depCode}${desCode}Display .cur-value`).text(
      newDate,
    );
    $(`#${PREFIX}DepartureDate${depCode}${desCode}Display .old-value`).text(
      oldDate,
    );
  }

  // Update fare
  let isUpdateFare = false;
  const passengerTypes = [
    { type: "Adt", count: adtCount },
    { type: "Chd", count: chdCount },
    { type: "Inf", count: infCount },
  ];
  passengerTypes.forEach(({ type, count }) => {
    const fareKey = `${type.toLowerCase()}Fare`;
    const fareData = updateData[fareKey];

    if (fareData) {
      const listLabelFare = ["fare", "tax", "fee", "price"];
      for (let key in fareData) {
        if (!listLabelFare.includes(key)) continue;

        const capKey = key.charAt(0).toUpperCase() + key.slice(1);
        const newValue = fareData[key];
        const inputId = `input#${PREFIX}${type}${capKey}${depCode}${desCode}`;
        const displayPrefix = `#${PREFIX}${type}${capKey}${depCode}${desCode}Display`;
        const oldValue = $(inputId).val();

        if (newValue != oldValue) {
          $(`${displayPrefix} .cur-value`).text(formatNumber(newValue));
          $(`${displayPrefix} .old-value`).text(formatNumber(oldValue));
          isUpdateFare = true;
        }
      }
    }
  });
  if (isUpdateFare) {
    let updateOldTotalAmount = $(
      `input#${PREFIX}TotalAmount${depCode}${desCode}`,
    ).val();
    let updateNewTotalAmount = updateData?.totalAmount ?? 0;
    $(`#${PREFIX}TotalAmount${depCode}${desCode}Display`).text(
      formatNumber(updateOldTotalAmount),
    );
    $(`input#${PREFIX}TotalAmount${depCode}${desCode}`).val(
      formatNumber(updateNewTotalAmount) + " VND",
    );
  }

  // Button update
  let direction = $(`#btnUpdate${depCode}${desCode}`).attr("direction");
  updateData.bookingId = bookingId;
  updateData.isInter = isInter;
  updateData.direction = parseInt(direction);
  $(`#btnUpdate${depCode}${desCode}`).attr("data", encodeAutoBook(updateData));
  $(`#btnUpdate${depCode}${desCode}`).attr("data-entry-class", entryClass);
  $(`#btnUpdate${depCode}${desCode}`).show();
}

function getSelectedPassengersData() {
  let list_passenger = {};
  $("#tbl_pax input[name=check-passenger]:checked").each(function () {
    let pass_id = $(this).attr("data-id");
    let pass_type = $(this).attr("data-type");
    if (pass_id !== undefined && pass_id.length > 30) {
      list_passenger[pass_id] = pass_type;
    }
  });
  return list_passenger;
}

function getSelectedItinerariesData() {
  let list_id = [];
  $("#itinerary_tbl input[name=check-itinerary]:checked").each(function () {
    let id = $(this).attr("data-id");
    if (id !== undefined && id.length > 30) list_id.push(id);
  });
  return list_id;
}

function getSelectedDetailsData() {
  let list_id = [];
  $("#line_details_tbl input[name=check-detail]:checked").each(function () {
    let id = $(this).attr("data-id");
    if (id !== undefined && id.length > 30) list_id.push(id);
  });
  return list_id;
}

function formatNumber(number) {
  sep = num_grp_sep;
  dec = dec_sep;
  const parts = number.toString().split(".");
  const integerPart = parts[0];
  const decimalPart = parts[1] || "";

  const formattedInt = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, sep);
  return decimalPart ? `${formattedInt}${dec}${decimalPart}` : formattedInt;
}

function unformatNumber(formattedStr) {
  const sep = num_grp_sep;
  const dec = dec_sep;

  // Escape group separator if needed (e.g., dot)
  const escapedSep = sep.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  const escapedDec = dec.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

  // Remove group separator, replace decimal with dot
  const cleaned = formattedStr
    .replace(new RegExp(escapedSep, "g"), "")
    .replace(new RegExp(escapedDec), ".");

  return parseFloat(cleaned);
}

function getLinkImageAirline(airlineCode) {
  return airline_logo_map?.[airlineCode?.toUpperCase()] ?? `#`;
}

function getLastName(fullname) {
  if (!fullname) return "";
  return fullname.trim().split(" ")[0];
}

function getFirstName(fullname) {
  if (!fullname) return "";
  let parts = fullname.trim().split(" ");
  return parts[parts.length - 1];
}

function getMiddleAndFirstName(fullname) {
  if (!fullname) return "";
  let parts = fullname.trim().split(" ");
  parts.shift(); // remove the first part (last name)
  return parts.join(" ");
}

function handleException(e, msg = "") {
  console.error(e);
  if (msg && msg != "") msg = "Lỗi trong quá trình xử lý, vui lòng thử lại sau";
  if (e.stack) {
    // Optional: Extract line and column using regex (browser-compatible)
    const match =
      e.stack.match(/at\s.+\((.+):(\d+):(\d+)\)/) ||
      e.stack.match(/at\s(.+):(\d+):(\d+)/);
    if (match) {
      const file = match[1] ?? "";
      const line = match[2] ?? "";
      const column = match[3] ?? "";
      showModalNotify("error", msg, `${e.message} on line ${line}`);
      return;
    }
  }
  showModalNotify("error", msg, e.message);
}

/**
 * Parse a date string into a Date. Supports the app format "DD-MM-YYYY[ HH:MM]"
 * (also tolerates "/" separators and ISO "YYYY-MM-DD"). Returns null when invalid.
 *
 * @param {string} value
 * @returns {Date|null}
 */
function parseLocalDate(value) {
  if (!value) return null;
  const datePart = String(value).trim().split(" ")[0]; // drop the time part
  const parts = datePart.split(/[-\/]/);
  if (parts.length !== 3) return null;

  // Default DD-MM-YYYY; if the first token is a 4-digit year, treat as YYYY-MM-DD.
  let d = +parts[0],
    m = +parts[1],
    y = +parts[2];
  if (parts[0].length === 4) {
    y = +parts[0];
    m = +parts[1];
    d = +parts[2];
  }
  if (!d || !m || !y) return null;

  const date = new Date(y, m - 1, d);
  return isNaN(date.getTime()) ? null : date;
}

/**
 * Calculate age (in full years) at a reference date.
 *
 * @param {string} birthdate "DD-MM-YYYY"
 * @param {string} referenceDate "DD-MM-YYYY[ HH:MM]" (default: today)
 * @returns {number|null} age in years, or null when birthdate is empty/invalid
 */
function calculateAge(birthdate, referenceDate = "") {
  const birth = parseLocalDate(birthdate);
  if (!birth) return null;

  const ref = parseLocalDate(referenceDate) || new Date();

  let age = ref.getFullYear() - birth.getFullYear();
  const monthDiff = ref.getMonth() - birth.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && ref.getDate() < birth.getDate()))
    age--;
  return age;
}

/**
 * Map an age to a passenger type code.
 *
 * @param {number|null} age
 * @returns {string|null} 'inf' (< 2), 'chd' (< 12), 'adt' (>= 12), or null when unknown
 */
function getPassengerTypeByAge(age) {
  if (age === null || age === undefined || isNaN(age)) return null;
  if (age < 2) return "inf";
  if (age < 12) return "chd";
  return "adt";
}

/**
 * Format a date string as "DD/MM/YYYY" for display.
 *
 * @param {string} birthdate
 * @param {string} inputFormat 'YMD' (default, input is YYYY-MM-DD) or 'DMY' (input is DD-MM-YYYY)
 * @returns {string}
 */
function formatDateOfBirth(birthdate, inputFormat = "YMD") {
  if (!birthdate) return "";
  const parts = String(birthdate).split(" ")[0].split("-");
  if (parts.length !== 3) return birthdate;
  return inputFormat === "DMY"
    ? `${parts[0]}/${parts[1]}/${parts[2]}`
    : `${parts[2]}/${parts[1]}/${parts[0]}`;
}

/**
 * Warning popup with Confirm/Cancel buttons.
 *
 * Uses its own native <dialog> opened with showModal(): top-layer dialogs stack by
 * open order, so this renders above #autoBookDialog (a plain #modal-container could
 * not, since it sits below the dialog's top layer regardless of z-index).
 *
 * @param {string} text_modal Main message
 * @param {string} text_detail Optional HTML detail shown in the body
 * @returns {Promise<boolean>} resolves true on confirm, false on cancel/dismiss
 */
function showConfirmNotify(text_modal, text_detail = "") {
  return new Promise((resolve) => {
    const existing = document.getElementById("confirmNotifyDialog");
    if (existing) existing.remove();

    let detailHTML = "";
    if (text_detail && text_detail.length > 0) {
      detailHTML = `<div style="text-align:left; background:#fff8e1; border-radius:4px; padding:8px; margin-top:10px">${text_detail}</div>`;
    }

    const dialog = document.createElement("dialog");
    dialog.id = "confirmNotifyDialog";
    dialog.style.cssText =
      "border:none; border-radius:8px; padding:0; max-width:440px; box-shadow:0 8px 30px rgba(0,0,0,.25)";
    dialog.innerHTML = `<div style="padding:20px; text-align:center">
            <div style="width:56px; height:56px; border-radius:50%; background:#ffc107; display:flex; align-items:center; justify-content:center; margin:0 auto 12px">
                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="#fff" class="bi bi-exclamation-lg" viewBox="0 0 16 16">
                    <path d="M7.005 3.1a1 1 0 1 1 1.99 0l-.388 6.35a.61.61 0 0 1-1.214 0L7.005 3.1ZM7 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0"/>
                </svg>
            </div>
            <p style="font-size:15px; margin:0">${text_modal}</p>
            ${detailHTML}
            <div style="display:flex; gap:8px; margin-top:16px">
                <button type="button" id="btnConfirmNotifyYes" class="btn btn-warning" style="flex:1">Xác nhận</button>
                <button type="button" id="btnConfirmNotifyNo" class="btn btn-secondary" style="flex:1">Hủy</button>
            </div>
        </div>`;

    document.body.appendChild(dialog);
    dialog.showModal();

    let settled = false;
    const settle = (result) => {
      if (settled) return;
      settled = true;
      dialog.close();
      dialog.remove();
      resolve(result);
    };
    dialog
      .querySelector("#btnConfirmNotifyYes")
      .addEventListener("click", () => settle(true));
    dialog
      .querySelector("#btnConfirmNotifyNo")
      .addEventListener("click", () => settle(false));
    // Esc key -> treat as cancel
    dialog.addEventListener("cancel", (e) => {
      e.preventDefault();
      settle(false);
    });
  });
}

function showPolicyInfoNotify(title, content) {
  const existing = document.getElementById("policyInfoDialog");
  if (existing) existing.remove();

  const dialog = document.createElement("dialog");
  dialog.id = "policyInfoDialog";
  dialog.style.cssText =
    "border:none; border-radius:8px; padding:0; max-width:440px; box-shadow:0 8px 30px rgba(0,0,0,.25)";
  dialog.innerHTML = `<div style="padding:20px">
        <h5 style="margin:0 0 10px">${title}</h5>
        <div class="policy-info-content" style="text-align:left; background:#f7f9fb; border-radius:4px; padding:8px">${content}</div>
        <button type="button" id="btnPolicyInfoClose" class="btn btn-warning" style="width:100%; margin-top:16px">Đã hiểu</button>
    </div>`;

  document.body.appendChild(dialog);
  dialog.showModal();

  const close = () => {
    dialog.close();
    dialog.remove();
  };
  dialog.querySelector("#btnPolicyInfoClose").addEventListener("click", close);
  dialog.addEventListener("cancel", (e) => {
    e.preventDefault();
    close();
  });
}

function encodeAutoBook(value) {
  if (!value) return value;
  if (typeof value === "object")
    return btoa(encodeURIComponent(JSON.stringify(value)));
  if (typeof value === "string") return btoa(encodeURIComponent(value));
}

function decodeAutoBook(value) {
  if (!value) return value;
  if (typeof value === "string")
    return JSON.parse(decodeURIComponent(atob(value)));
}
