function trackShipment() {
  const inputValue = document.getElementById("trackingInput").value.trim();
  const resultBox = document.getElementById("result");

  if (inputValue === "") {
    alert("Please enter a value.");
    return;
  }

const selectedOption = document.querySelector('input[name="trackBy"]:checked').value;

  fetch(`/api/tracking?${selectedOption}=${inputValue}`)
    .then(response => response.json())
    .then(data => {
      if (data.status === "success") {
        const t = data.data;
        const history = data.history ?? []; 

        const steps = [
          "Shipment Details Received",  
          "Arrived at your Nearest Hub", 
          "Out For Delivery",            
          "Delivered"                   
        ];

        const statusMap = { 221: 0, 223: 1, 224: 2, 226: 3 };

        let statusCode = parseInt(t.status_code, 10);
        let activeIndex = statusMap[statusCode] ?? -1;

        console.log("Status Code from API:", statusCode, "Active Index:", activeIndex);

        if (activeIndex === -1) {
          console.warn("⚠ Unknown status code:", statusCode);
          activeIndex = 0; 
        }

        const progressHTML = `
          <div class="progress-bar">
            ${steps.map((step, index) => {
              let isActive = index <= activeIndex ? "active" : "";
              return `<div class="step ${isActive}">${step}</div>`;
            }).join("")}
          </div>
        `;

        const timelineHTML = `
          <div class="timeline">
            ${history.map(h => `
              <div class="timeline-item">
                <div class="timeline-date">${h.status_date}</div>
                <div class="timeline-content">
                  <h4>${h.status_name ?? h.status_code}</h4>
                  <p>${h.tracking_data ?? ""}</p>
                </div>
              </div>
            `).join("")}
          </div>
        `;

        resultBox.style.display = "block";
        resultBox.innerHTML = `
          <div class="tracking-panel">
            <div><p><strong>Tracking ID:</strong><br>${t.awb_number}</p></div>
            <div><p><strong>Merchant:</strong><br>ShiParcel.ins</p></div>
            <div><p><strong>Received by:</strong><br>${t.consignee_name ?? "-"}</p></div>
            <div><p><strong>Status:</strong><br>${t.status_name ?? t.status_code}</p></div>
          </div>

          <p><strong>Current Status:</strong> 
            <span style="color:#004080;">${t.status_name ?? "Unknown"}</span>
          </p>

          ${progressHTML}

          <h3>Tracking History</h3>
          ${timelineHTML}
        `;
      } else {
        resultBox.style.display = "block";
        resultBox.innerHTML = `<p style="color:red;">${data.message}</p>`;
      }
    })
    .catch(err => {
      console.error(err);
      resultBox.style.display = "block";
      resultBox.innerHTML = `<p style="color:red;">Error fetching tracking data.</p>`;
    });
}

let trackingOption = "order"; 

function setTrackingOption(option) {
    trackingOption = option;

    const input = document.getElementById("trackingInput");
    const exampleText = document.getElementById("exampleText");

    if (option === "order") {
        input.placeholder = "e.g., ORDER12345";
        exampleText.innerText = "Example: ORDER12345";
    } else if (option === "awb") {
        input.placeholder = "e.g., AWB123456789";
        exampleText.innerText = "Example: AWB123456789";
    } else if (option === "mobile") {
        input.placeholder = "e.g., 9876543210";
        exampleText.innerText = "Example: 9876543210";
    }
}

