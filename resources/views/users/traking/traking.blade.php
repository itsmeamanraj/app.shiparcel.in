<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Track Your Shipment - Shiparcel</title>
  <link rel="stylesheet" href="{{ asset('assets/css/traking.css') }}" />
</head>
<body>

  <!-- Sticky Header -->
  <div class="navbar">
    <div class="logo">
      <h1>Shiparcel</h1>
    </div>
    <div class="nav-links">
      <a href="#">Products</a>
      <a href="#">Logistics</a>
      <a href="#">Platform</a>
      <a href="#">Pricing</a>
      <a href="#">Track Order</a>
      <a href="#">Blogs</a>
    </div>
  </div>

  <!-- Tracking Section -->
  <div class="container">
    <h2>Track Your Shipment</h2>

    <div class="radio-options horizontal-options">
      <label><input type="radio" name="trackBy" value="order" onchange="setTrackingOption(this.value)" checked> By Order ID</label>
      <label><input type="radio" name="trackBy" value="awb" onchange="setTrackingOption(this.value)"> By AWB Number</label>
      <label><input type="radio" name="trackBy" value="mobile" onchange="setTrackingOption(this.value)"> By Mobile Number</label>
    </div>

    <input type="text" id="trackingInput" placeholder="e.g., ORDER12345" />
    <small id="exampleText">Example: ORDER12345</small>

    <button class="track-btn" onclick="trackShipment()">Track</button>

    <div class="tracking-result" id="result" style="display:none;"></div>
  </div>

  <!-- Footer -->
  <footer>
    © 2025 Shiparcel. All rights reserved.
  </footer>

  <script src="{{ asset('assets/js/script.js') }}"></script>
</body>
</html>
<script>
  // Auto fill input from query params
  const params = new URLSearchParams(window.location.search);

  // Agar order / awb / mobile aaya hai URL me to input me daal do
  if (params.has('awb')) {
    document.getElementById("trackingInput").value = params.get('awb');
    trackShipment(); // Auto call API
  }
  if (params.has('order')) {
    document.getElementById("trackingInput").value = params.get('order');
    trackShipment();
  }
  if (params.has('mobile')) {
    document.getElementById("trackingInput").value = params.get('mobile');
    trackShipment();
  }
</script>
