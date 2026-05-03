<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - D'Jali Team</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; background: #f5f5f5; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 0; margin-bottom: 30px; }
        header h1 { font-size: 2.5rem; margin-bottom: 10px; }
        header p { opacity: 0.9; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .badge-get { background: #61affe; color: white; }
        .badge-post { background: #49cc90; color: white; }
        .badge-put { background: #fca130; color: white; }
        .badge-patch { background: #50e3c2; color: #333; }
        .badge-delete { background: #f93e3e; color: white; }
        .endpoint { background: white; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .endpoint-header { display: flex; align-items: center; padding: 20px; border-bottom: 1px solid #eee; gap: 15px; }
        .endpoint-url { flex: 1; font-family: 'Monaco', 'Menlo', monospace; font-size: 0.95rem; color: #333; }
        .endpoint-body { padding: 20px; background: #fafafa; border-top: 1px solid #eee; }
        .endpoint h3 { margin-bottom: 15px; color: #667eea; font-size: 1.1rem; }
        pre { background: #282c34; color: #abb2bf; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 0.85rem; line-height: 1.5; }
        code { font-family: 'Monaco', 'Menlo', monospace; }
        .json-key { color: #e06c75; }
        .json-string { color: #98c379; }
        .json-number { color: #d19a66; }
        .section { margin-bottom: 40px; }
        .section h2 { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #667eea; color: #333; }
        .auth-note { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { font-weight: 600; color: #555; }
        .param-required { color: #f93e3e; font-size: 0.8rem; }
        .param-optional { color: #999; font-size: 0.8rem; }
        footer { text-align: center; padding: 30px; color: #666; font-size: 0.9rem; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📱 D'Jali Team API Documentation</h1>
            <p>RESTful API v1 - Mobile Application Backend</p>
            <p style="margin-top: 10px; font-size: 0.9rem;">Base URL: <code>/api/v1</code></p>
        </div>
    </header>

    <div class="container">
        <div class="auth-note">
            <strong>🔐 Authentication:</strong> Most endpoints require a Bearer token. Include in header: <code>Authorization: Bearer {token}</code>
        </div>

        <div class="section">
            <h2>A. Authentication</h2>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-post">POST</span>
                    <span class="endpoint-url">/api/v1/auth/login</span>
                </div>
                <div class="endpoint-body">
                    <h3>Login User</h3>
                    <p>Authenticate user and return JWT token.</p>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>email</td><td>string</td><td class="param-required">Yes</td><td>User email address</td></tr>
                        <tr><td>password</td><td>string</td><td class="param-required">Yes</td><td>User password</td></tr>
                        <tr><td>device_name</td><td>string</td><td class="param-optional">No</td><td>Device identifier for token</td></tr>
                    </table>
                    <h4 style="margin: 15px 0 10px;">Request:</h4>
                    <pre><code>{
  <span class="json-key">"email"</span>: <span class="json-string">"driver@driver.com"</span>,
  <span class="json-key">"password"</span>: <span class="json-string">"driver"</span>
}</code></pre>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"data"</span>: {
    <span class="json-key">"token"</span>: <span class="json-string">"7|ORnKLzquJWgHLMJd..."</span>,
    <span class="json-key">"user"</span>: {
      <span class="json-key">"id"</span>: <span class="json-number">1</span>,
      <span class="json-key">"name"</span>: <span class="json-string">"Admin Siwride"</span>,
      <span class="json-key">"email"</span>: <span class="json-string">"admin@siwride.com"</span>,
      <span class="json-key">"role"</span>: <span class="json-string">"admin"</span>,
      <span class="json-key">"status"</span>: <span class="json-string">"active"</span>
    }
  }
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-post">POST</span>
                    <span class="endpoint-url">/api/v1/auth/register</span>
                </div>
                <div class="endpoint-body">
                    <h3>Register Driver</h3>
                    <p>Register new driver account. Status defaults to "pending" (requires admin approval).</p>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>firstname</td><td>string</td><td class="param-required">Yes</td><td>Driver first name</td></tr>
                        <tr><td>lastname</td><td>string</td><td class="param-optional">No</td><td>Driver last name</td></tr>
                        <tr><td>email</td><td>string</td><td class="param-required">Yes</td><td>Unique email address</td></tr>
                        <tr><td>phone</td><td>string</td><td class="param-required">Yes</td><td>Unique phone number</td></tr>
                        <tr><td>password</td><td>string</td><td class="param-required">Yes</td><td>Min 8 characters</td></tr>
                    </table>
                    <h4 style="margin: 15px 0 10px;">Response (201):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Registration successful. Please wait for admin approval."</span>
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-post">POST</span>
                    <span class="endpoint-url">/api/v1/auth/logout</span>
                </div>
                <div class="endpoint-body">
                    <h3>Logout</h3>
                    <p>Invalidate current token (logout).</p>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Logged out successfully."</span>
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-post">POST</span>
                    <span class="endpoint-url">/api/v1/auth/logout-all</span>
                </div>
                <div class="endpoint-body">
                    <h3>Logout All Devices</h3>
                    <p>Invalidate all tokens for current user (logout from all devices).</p>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"All tokens revoked."</span>
}</code></pre>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>B. User Profile</h2>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-get">GET</span>
                    <span class="endpoint-url">/api/v1/user</span>
                </div>
                <div class="endpoint-body">
                    <h3>Get Current User Profile</h3>
                    <p>Get authenticated user information.</p>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"data"</span>: {
    <span class="json-key">"id"</span>: <span class="json-number">1</span>,
    <span class="json-key">"firstname"</span>: <span class="json-string">"Admin"</span>,
    <span class="json-key">"lastname"</span>: <span class="json-string">"Siwride"</span>,
    <span class="json-key">"email"</span>: <span class="json-string">"admin@siwride.com"</span>,
    <span class="json-key">"phone"</span>: <span class="json-string">"08123456789"</span>,
    <span class="json-key">"role"</span>: <span class="json-string">"admin"</span>,
    <span class="json-key">"status"</span>: <span class="json-string">"active"</span>
  }
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-put">PUT</span>
                    <span class="endpoint-url">/api/v1/user</span>
                </div>
                <div class="endpoint-body">
                    <h3>Update Profile</h3>
                    <p>Update authenticated user profile.</p>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>firstname</td><td>string</td><td class="param-optional">No</td><td>First name</td></tr>
                        <tr><td>lastname</td><td>string</td><td class="param-optional">No</td><td>Last name</td></tr>
                        <tr><td>phone</td><td>string</td><td class="param-optional">No</td><td>Phone number</td></tr>
                    </table>
                    <h4 style="margin: 15px 0 10px;">Request:</h4>
                    <pre><code>{
  <span class="json-key">"firstname"</span>: <span class="json-string">"Budi"</span>,
  <span class="json-key">"lastname"</span>: <span class="json-string">"Santoso"</span>
}</code></pre>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Profile updated successfully."</span>,
  <span class="json-key">"data"</span>: { ... }
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-post">POST</span>
                    <span class="endpoint-url">/api/v1/user/photo</span>
                </div>
                <div class="endpoint-body">
                    <h3>Upload Profile Photo</h3>
                    <p>Upload profile photo for authenticated user.</p>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>photo</td><td>file</td><td class="param-required">Yes</td><td>Image file, max 2MB</td></tr>
                    </table>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"message"</span>: <span class="json_string">"Photo uploaded successfully."</span>,
  <span class="json_key">"data"</span>: {
    <span class="json_key">"image"</span>: <span class="json_string">"profiles/xxx.jpg"</span>
  }
}</code></pre>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>C. Job Management (Driver)</h2>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-get">GET</span>
                    <span class="endpoint-url">/api/v1/jobs/shared</span>
                </div>
                <div class="endpoint-body">
                    <h3>Get Shared Jobs / Job Pool</h3>
                    <p>Retrieve available jobs that can be taken by drivers. Guest info is hidden before 4 PM on the same day.</p>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"data"</span>: [
    {
      <span class="json-key">"id"</span>: <span class="json-number">101</span>,
      <span class="json-key">"pickup_address"</span>: <span class="json-string">"Bandara Ngurah Rai"</span>,
      <span class="json-key">"dropoff_address"</span>: <span class="json-string">"Hotel Kuta"</span>,
      <span class="json-key">"date"</span>: <span class="json-string">"2026-04-27"</span>,
      <span class="json-key">"time"</span>: <span class="json-string">"14:00:00"</span>,
      <span class="json-key">"price"</span>: <span class="json-number">150000</span>,
      <span class="json-key">"guest_info_hidden"</span>: <span class="json-number">true</span>
    }
  ]
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-get">GET</span>
                    <span class="endpoint-url">/api/v1/jobs/my</span>
                </div>
                <div class="endpoint-body">
                    <h3>Get My Rides</h3>
                    <p>Get jobs assigned to the current driver (pending, otw, tiba status). Filter: date >= today, max 20 data.</p>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-get">GET</span>
                    <span class="endpoint-url">/api/v1/jobs/{id}</span>
                </div>
                <div class="endpoint-body">
                    <h3>Get Job Detail</h3>
                    <p>Get detailed job information. Only owner (driver) or admin can access. Guest info hidden before 4 PM for today's jobs.</p>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"data"</span>: {
    <span class="json-key">"id"</span>: <span class="json-number">175</span>,
    <span class="json-key">"customer_name"</span>: <span class="json-string">"Budi Santoso"</span>,
    <span class="json-key">"customer_phone"</span>: <span class="json-string">"081234567890"</span>,
    <span class="json-key">"pickup_address"</span>: <span class="json-string">"Bandara Ngurah Rai"</span>,
    <span class="json-key">"dropoff_address"</span>: <span class="json-string">"Hotel Kuta"</span>,
    <span class="json-key">"date"</span>: <span class="json-string">"2026-04-27"</span>,
    <span class="json-key">"time"</span>: <span class="json-string">"14:00:00"</span>,
    <span class="json-key">"price"</span>: <span class="json-number">150000</span>,
    <span class="json-key">"status"</span>: <span class="json-string">"pending"</span>,
    <span class="json-key">"driver_id"</span>: <span class="json-number">3</span>,
    <span class="json-key">"is_shared"</span>: <span class="json-number">false</span>,
    <span class="json-key">"guest_info_hidden"</span>: <span class="json-number">false</span>,
    <span class="json-key">"driver"</span>: { <span class="json-key">"id"</span>: <span class="json-number">3</span>, <span class="json-key">"firstname"</span>: <span class="json-string">"Driver"</span> }
  }
}</code></pre>
                    <h4 style="margin: 15px 0 10px;">Error Response (403):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"error"</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Unauthorized to view this job."</span>
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-post">POST</span>
                    <span class="endpoint-url">/api/v1/jobs/{id}/take</span>
                </div>
                <div class="endpoint-body">
                    <h3>Take Job from Pool</h3>
                    <p>Take a shared job. Uses database transaction with row locking to prevent race conditions.</p>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Berhasil mengambil pekerjaan."</span>
}</code></pre>
                    <h4 style="margin: 15px 0 10px;">Error Response (400):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"error"</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Maaf, pekerjaan ini sudah diambil oleh driver lain."</span>
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-patch">PATCH</span>
                    <span class="endpoint-url">/api/v1/jobs/{id}/status</span>
                </div>
                <div class="endpoint-body">
                    <h3>Update Job Status</h3>
                    <p>Update job progress (otw → tiba → selesai). Automatically creates history record.</p>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>status</td><td>string</td><td class="param-required">Yes</td><td>Enum: otw, tiba, selesai</td></tr>
                        <tr><td>latitude</td><td>number</td><td class="param-optional">No</td><td>GPS latitude</td></tr>
                        <tr><td>longitude</td><td>number</td><td class="param-optional">No</td><td>GPS longitude</td></tr>
                        <tr><td>notes</td><td>string</td><td class="param-optional">No</td><td>Catatan opsional</td></tr>
                    </table>
                    <h4 style="margin: 15px 0 10px;">Request:</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"otw"</span>,
  <span class="json-key">"latitude"</span>: <span class="json-number">-8.748112</span>,
  <span class="json-key">"longitude"</span>: <span class="json-number">115.167156</span>
}</code></pre>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Status updated successfully."</span>,
  <span class="json-key">"data"</span>: { ... }
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-get">GET</span>
                    <span class="endpoint-url">/api/v1/jobs/{id}/history</span>
                </div>
                <div class="endpoint-body">
                    <h3>Get Job Status History</h3>
                    <p>Get complete status change history for a job.</p>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"data"</span>: [
    {
      <span class="json-key">"id"</span>: <span class="json-number">1</span>,
      <span class="json-key">"order_id"</span>: <span class="json-number">175</span>,
      <span class="json-key">"driver_id"</span>: <span class="json-number">3</span>,
      <span class="json-key">"status"</span>: <span class="json-string">"otw"</span>,
      <span class="json-key">"latitude"</span>: <span class="json-number">-8.748112</span>,
      <span class="json-key">"longitude"</span>: <span class="json_number">115.167156</span>,
      <span class="json-key">"notes"</span>: <span class="json-string">"Sedang perjalanan"</span>,
      <span class="json-key">"created_at"</span>: <span class="json_string">"2026-04-26T18:00:00Z"</span>
    },
    {
      <span class="json-key">"id"</span>: <span class="json-number">2</span>,
      <span class="json-key">"order_id"</span>: <span class="json_number">175</span>,
      <span class="json_key">"driver_id"</span>: <span class="json_number">3</span>,
      <span class="json_key">"status"</span>: <span class="json_string">"tiba"</span>,
      <span class="json_key">"created_at"</span>: <span class="json_string">"2026-04-26T18:30:00Z"</span>
    }
  ]
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-post">POST</span>
                    <span class="endpoint-url">/api/v1/jobs/{id}/evidence</span>
                </div>
                <div class="endpoint-body">
                    <h3>Upload Evidence Photo</h3>
                    <p>Upload proof photo when arriving at location or departing with guest.</p>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>type</td><td>string</td><td class="param-required">Yes</td><td>Enum: berangkat, tiba</td></tr>
                        <tr><td>photo</td><td>file</td><td class="param-required">Yes</td><td>Image file, max 5MB</td></tr>
                        <tr><td>latitude</td><td>number</td><td class="param-optional">No</td><td>GPS latitude</td></tr>
                        <tr><td>longitude</td><td>number</td><td class="param-optional">No</td><td>GPS longitude</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>D. GPS Tracking</h2>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-post">POST</span>
                    <span class="endpoint-url">/api/v1/tracking/update</span>
                </div>
                <div class="endpoint-body">
                    <h3>Update Driver Location</h3>
                    <p>Update current driver location (call every 30 seconds).</p>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>latitude</td><td>number</td><td class="param-required">Yes</td><td>Latitude coordinate</td></tr>
                        <tr><td>longitude</td><td>number</td><td class="param-required">Yes</td><td>Longitude coordinate</td></tr>
                    </table>
                    <h4 style="margin: 15px 0 10px;">Request:</h4>
                    <pre><code>{
  <span class="json-key">"latitude"</span>: <span class="json-number">-8.748112</span>,
  <span class="json-key">"longitude"</span>: <span class="json-number">115.167156</span>
}</code></pre>
                    <h4 style="margin: 15px 0 10px;">Response (200):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"message"</span>: <span class="json-string">"Location updated."</span>
}</code></pre>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>E. Admin Routes</h2>
            <div class="auth-note">
                <strong>⚠️ Admin Only:</strong> These routes require <code>role: admin</code> in user record.
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-get">GET</span>
                    <span class="endpoint-url">/api/v1/jobs</span>
                </div>
                <div class="endpoint-body">
                    <h3>Get All Jobs</h3>
                    <p>Get all jobs with optional status filter.</p>
                    <p><strong>Query Params:</strong> <code>?status=pending</code></p>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-post">POST</span>
                    <span class="endpoint-url">/api/v1/jobs</span>
                </div>
                <div class="endpoint-body">
                    <h3>Create Job</h3>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>customer_name</td><td>string</td><td class="param-required">Yes</td><td>Guest name</td></tr>
                        <tr><td>customer_phone</td><td>string</td><td class="param-required">Yes</td><td>Guest phone</td></tr>
                        <tr><td>pickup_address</td><td>string</td><td class="param-required">Yes</td><td>Pickup location</td></tr>
                        <tr><td>dropoff_address</td><td>string</td><td class="param-required">Yes</td><td>Drop-off location</td></tr>
                        <tr><td>date</td><td>date</td><td class="param-required">Yes</td><td>Job date (YYYY-MM-DD)</td></tr>
                        <tr><td>time</td><td>time</td><td class="param-required">Yes</td><td>Pickup time</td></tr>
                        <tr><td>price</td><td>number</td><td class="param-required">Yes</td><td>Job fee</td></tr>
                        <tr><td>is_cash</td><td>boolean</td><td class="param-optional">No</td><td>Cash payment (excluded from salary)</td></tr>
                        <tr><td>driver_id</td><td>integer</td><td class="param-optional">No</td><td>Assign directly to driver</td></tr>
                    </table>
                    <h4 style="margin: 15px 0 10px;">Response (201):</h4>
                    <pre><code>{
  <span class="json-key">"status"</span>: <span class="json-string">"success"</span>,
  <span class="json-key">"data"</span>: { ... }
}</code></pre>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-post">POST</span>
                    <span class="endpoint-url">/api/v1/jobs/{id}/assign</span>
                </div>
                <div class="endpoint-body">
                    <h3>Assign Job to Driver</h3>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>driver_id</td><td>integer</td><td class="param-required">Yes</td><td>Driver user ID</td></tr>
                    </table>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-get">GET</span>
                    <span class="endpoint-url">/api/v1/users/pending</span>
                </div>
                <div class="endpoint-body">
                    <h3>Get Pending Drivers</h3>
                    <p>List all driver registrations awaiting admin approval.</p>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-put">PUT</span>
                    <span class="endpoint-url">/api/v1/users/{id}/status</span>
                </div>
                <div class="endpoint-body">
                    <h3>Update Driver Status</h3>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>status</td><td>string</td><td class="param-required">Yes</td><td>Enum: approved, rejected</td></tr>
                    </table>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-get">GET</span>
                    <span class="endpoint-url">/api/v1/tracking/active</span>
                </div>
                <div class="endpoint-body">
                    <h3>Get Active Driver Locations</h3>
                    <p>Get all drivers currently online with their locations (updated within last 5 minutes).</p>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-get">GET</span>
                    <span class="endpoint-url">/api/v1/reports/salary</span>
                </div>
                <div class="endpoint-body">
                    <h3>Get Salary Report</h3>
                    <p>Get driver salary recap by period.</p>
                    <p><strong>Query Params:</strong></p>
                    <table>
                        <tr><th>Param</th><th>Description</th><th>Default</th></tr>
                        <tr><td>month</td><td>Month (1-12)</td><td>Current month</td></tr>
                        <tr><td>year</td><td>Year</td><td>Current year</td></tr>
                        <tr><td>period</td><td>1 (1-15) or 2 (16-31)</td><td>1</td></tr>
                    </table>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-get">GET</span>
                    <span class="endpoint-url">/api/v1/settings</span>
                </div>
                <div class="endpoint-body">
                    <h3>Get All Settings</h3>
                    <p>Retrieve system configuration settings.</p>
                </div>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="badge badge-put">PUT</span>
                    <span class="endpoint-url">/api/v1/settings</span>
                </div>
                <div class="endpoint-body">
                    <h3>Update Settings</h3>
                    <table>
                        <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
                        <tr><td>settings</td><td>object</td><td class="param-required">Yes</td><td>Key-value pairs of settings</td></tr>
                    </table>
                    <h4 style="margin: 15px 0 10px;">Request:</h4>
                    <pre><code>{
  <span class="json-key">"settings"</span>: {
    <span class="json-key">"daily_job_limit"</span>: <span class="json-number">10</span>
  }
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2026 D'Jali Team API Documentation</p>
    </footer>
</body>
</html>