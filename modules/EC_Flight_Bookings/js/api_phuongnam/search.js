const ENDPOINT_AUTO_BOOK = "index.php?entryPoint=entryPointAutoBook";

$(document).ready(function () {
    $(document).on('keypress', function (e) {
        if (e.which == 13) {
            $("#btnSearch").trigger("click");
            e.preventDefault();
        }
    });
    $('#btnSearch').click(function () {
        let bookingCode = $('input[name="bookingCode"]').val();
        let systemCode = $('select[name="systemCode"]').val();

        $.ajax({
            url: ENDPOINT_AUTO_BOOK,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                action: "get_booking",
                systemCode: systemCode,
                bookingCode: bookingCode
            }),
            beforeSend: function () {
                $('.container-waiting').show();
            },
            success: function (response) {
                try {
                    $('.container-waiting').hide();
                    const objData = JSON.parse(response);
                    if (objData.status == 1) {
                        renderBooking(objData.data);
                    }
                    else {
                        showModalNotify("error", objData.message ?? "Lỗi trong quá trình lấy dữ liệu");
                        console.error(objData);
                    }
                }
                catch (e) {
                    showModalNotify("error", "Lỗi trong quá trình lấy dữ liệu", e.message);
                    console.error(e);
                    console.error("Response was:", response);
                }
            }
        });
    });
});

// Helper functions
function formatDateTime(dt) {
    if (!dt) return '';
    const d = new Date(dt);
    return d.toLocaleString('en-GB', { hour12: false });
}
function formatDate(dt) {
    if (!dt) return '';
    const d = new Date(dt);
    return d.toLocaleDateString('en-GB');
}
function formatTime(dt) {
    if (!dt) return '';
    const d = new Date(dt);
    return d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false });
}
function formatMoney(n) {
    return n ? n.toLocaleString('en-US') : '0';
}
function getPassengerTypeName(id) {
    switch (id) {
        case 1: return 'Người lớn';
        case 5: return 'Em bé';
        case 6: return 'Trẻ em';
        default: return '';
    }
}
function getPassengerNote(id) {
    switch (id) {
        case 1: return 'Contact';
        case 5: return 'Under 2 years';
        case 6: return '2-12 years';
        default: return '';
    }
}
function getGenderName(gender) {
    return gender === 'M' ? 'Nam' : gender === 'F' ? 'Nữ' : '';
}

// Group fare charges by FareBasis (usually unique per segment/airline)
function groupFareChargesBySegment(fareCharges) {
    const map = {};
    fareCharges.forEach(fc => {
    // Use FareBasis as a key (e.g., "I1_ECO,J1_ECO"), or combine with CarrierCode if needed
    const key = fc.FareBasis;
    if (!map[key]) map[key] = [];
    map[key].push(fc);
    });
    return map;
}

// Get readable segment label from FareBasis and Flights
function getSegmentLabel(fareBasis, flights, flightDetails) {
    // Try to find the matching flight(s) by FareBasis
    // Fallback: show FareBasis
    let label = '';
    const fareBasisArr = fareBasis.split(',');
    let foundFlights = [];
    fareBasisArr.forEach(fb => {
    // Try to find a flight with this FareBasis
    let f = flightDetails.find(fd => fd.FareBasis === fb);
    if (f) foundFlights.push(f);
    });
    if (foundFlights.length === 0) {
    // Try to match by FareClass
    fareBasisArr.forEach(fb => {
        let f = flightDetails.find(fd => fd.FareClass === fb.split('_')[0]);
        if (f) foundFlights.push(f);
    });
    }
    if (foundFlights.length > 0) {
    label = foundFlights.map(f =>
        `${f.Origin} (${f.OriginNameEn}) → ${f.Destination} (${f.DestinationNameEn}) [${f.CarrierCode}]`
    ).join(', ');
    } else {
    label = fareBasis;
    }
    return label;
}

// Main rendering function
function renderBooking(data) {
    const d = data;
    // Booking Overview
    const overviewHtml = `
      <div class="section">
        <h2>Booking Overview</h2>
        <div class="overview-columns">
          <ul class="info-list">
            <li><span class="highlight">Booking Code:</span> ${d.BookingCode}</li>
            <li><span class="highlight">Status:</span> <span style="color:#d8242a;">${d.IsPaid ? 'Paid' : 'Unpaid'}</span></li>
            <li><span class="highlight">Total Amount:</span> ${formatMoney(d.TotalAmount)} VND</li>
            <li><span class="highlight">Booking Date:</span> ${formatDateTime(d.BookingDate)}</li>
            <li><span class="highlight">Booking Expiry:</span> ${formatDateTime(d.BookingExpired)}</li>
          </ul>
          <ul class="info-list">
            <li><span class="highlight">Contact:</span> ${d.ContactName}</li>
            <li><span class="highlight">Phone:</span> ${d.ContactPhone}</li>
            <li><span class="highlight">Email:</span> ${d.ContactEmail}</li>
            <li><span class="highlight">Address:</span> ${d.ContactAddress}</li>
          </ul>
        </div>
      </div>`;

    // Flight Itinerary
    const flightsHtml = d.FlightDetails.map(f => `
        <tr>
          <td>${f.FlightNumber}</td>
          <td>${f.Origin} (${f.OriginNameEn}) → ${f.Destination} (${f.DestinationNameEn})</td>
          <td>${formatTime(f.DepartureTime)}, ${formatDate(f.DepartureTime)}</td>
          <td>${formatTime(f.Arrivaltime)}, ${formatDate(f.Arrivaltime)}</td>
          <td>${f.CabinNameEN}</td>
          <td>${f.AirCraftType ? 'Airbus ' + f.AirCraftType : ''}</td>
          <td>${f.FlightDuration}</td>
        </tr>
      `).join('');
    const itineraryHtml = `
      <div class="section">
        <h2>Flight Itinerary</h2>
        <table class="flight-table">
          <thead>
            <tr>
              <th>Flight</th>
              <th>From → To</th>
              <th>Departure</th>
              <th>Arrival</th>
              <th>Class</th>
              <th>Aircraft</th>
              <th>Duration</th>
            </tr>
          </thead>
          <tbody>
            ${flightsHtml}
          </tbody>
        </table>
      </div>`;

    // Passenger List
    const passengersHtml = d.Customers.map(c => `
        <tr>
          <td>${c.LastName} ${c.FirstName}</td>
          <td>${getPassengerTypeName(c.PassengerTypeId)}</td>
          <td>${formatDate(c.BirthDay)}</td>
          <td>${getGenderName(c.Gender)}</td>
          <td>${getPassengerNote(c.PassengerTypeId)}</td>
        </tr>
      `).join('');
    const passengerListHtml = `
      <div class="section">
        <h2>Passenger List</h2>
        <table class="passenger-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Type</th>
              <th>Birthday</th>
              <th>Gender</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>
            ${passengersHtml}
          </tbody>
        </table>
      </div>`;

    // Price Details (by passenger type, whole trip)
    // Price Details - group by FareBasis (segment)
      const fareCharges = d.SumCharge.FareCharges || [];
      const grouped = groupFareChargesBySegment(fareCharges);

      let priceHtml = `<div class="section"><h2>Price Details by Segment</h2>`;
      Object.keys(grouped).forEach((fareBasis, idx) => {
        const segmentLabel = getSegmentLabel(fareBasis, d.Flights, d.FlightDetails);
        const rows = grouped[fareBasis].map(fc => `
          <tr>
            <td>${getPassengerTypeName(fc.PassengerTypeId)}</td>
            <td>${formatMoney(fc.FareBaseAmount + fc.TaxAmount + fc.AirportFeesAmount + fc.VATAmount)}</td>
            <td>${formatMoney(fc.TotalAmount)}</td>
          </tr>
        `).join('');
        const total = grouped[fareBasis].reduce((sum, fc) => sum + (fc.TotalAmount || 0), 0);
        priceHtml += `
          <h3>Segment ${idx + 1}: ${segmentLabel}</h3>
          <table class="price-table">
            <thead>
              <tr>
                <th>Passenger Type</th>
                <th>Fare + Taxes/Fees</th>
                <th>Total (VND)</th>
              </tr>
            </thead>
            <tbody>
              ${rows}
              <tr>
                <td colspan="2" class="highlight">Total for Segment</td>
                <td class="highlight">${formatMoney(total)}</td>
              </tr>
            </tbody>
          </table>
        `;
      });
      priceHtml += `</div>`;

    // Note
    const noteHtml = `
      <div class="note">
        <ul>
          <li><b>${d.IsPaid ? 'Paid' : 'Unpaid'}:</b> Please complete payment before <b>${formatDateTime(d.BookingExpired)}</b> to secure your booking.</li>
          <li><b>No e-ticket number issued yet.</b> (Ticket will be issued after payment.)</li>
          <li><b>No checked baggage included.</b> (Contact agency to add baggage if needed.)</li>
          <li>Check your email for updates and further instructions.</li>
        </ul>
      </div>`;

    document.getElementById('booking-container').innerHTML =
        overviewHtml +
        itineraryHtml +
        passengerListHtml +
        priceHtml +
        noteHtml;
}