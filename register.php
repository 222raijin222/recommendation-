<?php
// Database connection
$host = "sql212.infinityfree.com";
$user = "if0_40075012";
$pass = "saguin12345";
$dbname = "if0_40075012_barangay_system";
;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = $_POST["full_name"] ?? '';
    $birthdate = $_POST["birthdate"] ?? '';
    $gender = $_POST["gender"] ?? '';
    $address = $_POST["address"] ?? '';
    $contact_number = $_POST["contact_number"] ?? '';
    $email = $_POST["email"] ?? '';
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';
    $position = $_POST["position"] ?? '';
    $start_term = $_POST["start_term"] ?? '';
    $end_term = $_POST["end_term"] ?? '';
    $barangay_name = $_POST["barangay_name"] ?? '';
    $municipality = $_POST["municipality"] ?? '';
    $province = $_POST["province"] ?? '';
    $barangay_hall = $_POST["barangay_hall"] ?? '';
    $barangay_contact = $_POST["barangay_contact"] ?? '';
    $barangay_email = $_POST["barangay_email"] ?? '';
    $barangay_website = $_POST["barangay_website"] ?? '';
    $total_population = $_POST["total_population"] ?? '';
    $total_households = $_POST["total_households"] ?? '';
    $purok_count = $_POST["purok_count"] ?? '';
    $purok_names = $_POST["purok_names"] ?? '';
    $flood_prone = $_POST["flood_prone"] ?? '';
    $affected_areas = $_POST["affected_areas"] ?? '';
    $evacuation_center = $_POST["evacuation_center"] ?? '';
    $preparedness = $_POST["preparedness"] ?? '';
    $coordination = $_POST["coordination"] ?? '';
    $annual_budget = $_POST["annual_budget"] ?? '';
    $budget_allocation = $_POST["budget_allocation"] ?? '';
    $spending_priorities = $_POST["spending_priorities"] ?? '';

    // Handle image upload - FIXED VERSION
    $id_path = "";
    if (isset($_FILES["official_id_upload"]) && $_FILES["official_id_upload"]["error"] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/";
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Get file info
        $fileName = time() . "_" . basename($_FILES["official_id_upload"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        // Allow certain file formats
        $allowTypes = array('jpg','png','jpeg','gif','pdf');
        
        if (in_array(strtolower($fileType), $allowTypes)) {
            // Upload file to server
            if (move_uploaded_file($_FILES["official_id_upload"]["tmp_name"], $targetFilePath)) {
                $id_path = $targetFilePath;
            } else {
                $id_path = "upload_failed";
            }
        } else {
            $id_path = "invalid_type";
        }
    } else {
        // Handle upload errors
        $uploadError = $_FILES["official_id_upload"]["error"] ?? 'no_file';
        if ($uploadError !== UPLOAD_ERR_NO_FILE) {
            $id_path = "upload_error_" . $uploadError;
        }
    }

    // Handle purok names as array
    if (isset($_POST["purok_names"]) && is_array($_POST["purok_names"])) {
        $purok_names = implode(", ", array_filter($_POST["purok_names"]));
    } else {
        $purok_names = $_POST["purok_names"] ?? '';
    }

    // Handle affected areas as array (if multiple selection)
    if (isset($_POST["affected_areas"]) && is_array($_POST["affected_areas"])) {
        $affected_areas = implode(", ", array_filter($_POST["affected_areas"]));
    } else {
        $affected_areas = $_POST["affected_areas"] ?? '';
    }

    // Insert into DB - FIXED column name
    $sql = "INSERT INTO barangay_registration (
        full_name, birthdate, gender, address, contact_number, email, username, password, position, start_term, end_term,
        barangay_name, municipality, province, barangay_hall, barangay_contact, barangay_email, barangay_website,
        total_population, total_households, purok_count, purok_names, flood_prone, affected_areas, evacuation_center, 
        preparedness, coordination, annual_budget, budget_allocation, spending_priorities, id_path
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $full_name, $birthdate, $gender, $address, $contact_number, $email, $username, password_hash($password, PASSWORD_DEFAULT),
            $position, $start_term, $end_term, $barangay_name, $municipality, $province, $barangay_hall, $barangay_contact, 
            $barangay_email, $barangay_website, $total_population, $total_households, $purok_count, $purok_names, $flood_prone, 
            $affected_areas, $evacuation_center, $preparedness, $coordination, $annual_budget, $budget_allocation, $spending_priorities, $id_path
        ]);

        echo "<script>alert('Registration successful! Please wait for admin approval.'); window.location.href = 'login.php?registered=true';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Registration failed: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Official Registration</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* ID Upload and Modal */
        .id-upload {
            border: 2px dashed #1d3b71;
            border-radius: 10px;
            padding: 25px;
            background: #f8faff;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            position: relative;
        }
        .id-upload:hover { background: #eaf1fb; transform: scale(1.02); }
        .id-upload i { font-size: 2rem; color: #1d3b71; margin-bottom: 8px; }
        .id-preview {
            display: none; margin-top: 15px; max-width: 100%;
            border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .upload-status {
            margin-top: 10px;
            padding: 8px;
            border-radius: 4px;
            font-size: 14px;
            display: none;
        }
        .upload-status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .upload-status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .modal {
            display: none; position: fixed; z-index: 3000;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7); justify-content: center; align-items: center;
        }
        .modal-content {
            background: white; padding: 20px; border-radius: 12px;
            text-align: center; position: relative; max-width: 500px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.4);
        }
        .close-btn {
            position: absolute; top: 10px; right: 15px;
            color: #333; font-size: 22px; cursor: pointer;
        }
        .close-btn:hover { color: red; }

        /* Section Titles */
        h3 {
            color: #1d3b71;
            margin-top: 25px;
            margin-bottom: 10px;
            font-size: 1.1rem;
            border-left: 4px solid #1d3b71;
            padding-left: 8px;
        }
        .form-section hr {
            border: none;
            border-top: 1px solid #dfe3eb;
            margin: 25px 0;
        }
        
        /* Disabled fields styling */
        input:disabled, select:disabled {
            background-color: #f5f5f5;
            color: #666;
            cursor: not-allowed;
        }
        
        /* Purok fields styling */
        .purok-input {
            margin-bottom: 8px;
        }
        .purok-input input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        /* Checkbox styling */
        .checkbox-container {
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background: #f9f9f9;
        }
        .checkbox-item {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .checkbox-item input[type="checkbox"] {
            margin-left: 8px;
            order: 2;
        }
        .checkbox-item label {
            cursor: pointer;
            font-weight: normal;
            order: 1;
            flex-grow: 1;
        }
        
        /* Example text styling */
        .example-text {
            color: #666;
            font-size: 12px;
            font-style: italic;
            margin-top: 5px;
            margin-bottom: 10px;
            padding: 8px;
            background: #f8f9fa;
            border-left: 3px solid #1d3b71;
            border-radius: 3px;
        }
        
        /* Conditional fields */
        .conditional-field {
            display: none;
        }
        .conditional-field.visible {
            display: block;
        }
        
        /* File input styling */
        .file-input-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        /* Verification Modal Style */
.verify-modal-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.65);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 99999;
}

.verify-modal-box {
    background: white;
    padding: 25px;
    width: 90%;
    max-width: 420px;
    text-align: center;
    border-radius: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.4);
    animation: fadeIn 0.3s ease;
}

.verify-modal-box h2 {
    margin-bottom: 10px;
    color: #1d3b71;
}

.verify-modal-box p {
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.verify-btn-group {
    display: flex;
    justify-content: space-between;
}

.yes-btn, .no-btn {
    padding: 10px 16px;
    width: 48%;
    font-size: 0.95rem;
    cursor: pointer;
    border-radius: 8px;
    border: none;
}

.yes-btn {
    background: #1d3b71;
    color: white;
}

.yes-btn:hover {
    background: #24498f;
}

.no-btn {
    background: #ccc;
}

.no-btn:hover {
    background: #b5b5b5;
}

@keyframes fadeIn {
    from { transform: scale(0.85); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

    </style>
</head>
<body>
    <!-- Barangay Captain Verification Modal -->
<div id="captainModal" class="verify-modal-overlay">
    <div class="verify-modal-box">
        <h2>Barangay Captain Verification</h2>
        <p>Only the Barangay Captain is authorized to continue with the registration.</p>

        <div class="verify-btn-group">
            <button id="yesCaptain" class="yes-btn">Yes, I am the Barangay Captain</button>
            <button id="noCaptain" class="no-btn">No, take me back</button>
        </div>
    </div>
</div>

<div style="margin: 20px;">
    <button type="button" 
            onclick="window.location.href='index.php';"  
            style="background-color:#1d3b71; 
            color:white; 
            border:none; 
            padding:10px 20px; 
            border-radius:4px; 
            cursor:pointer;">
        ← Go to main page
    </button>
</div> 
<div class="registration-container">
    <div class="registration-header">
        <h1>Barangay Official Registration</h1>
        <p>Please fill out the form to register as a barangay official</p>
    </div>

    <form class="registration-form" method="POST" enctype="multipart/form-data">

        <!-- 🧍 PERSONAL INFORMATION -->
        <div class="form-section">
            <h2><i class="fas fa-user"></i> Personal Information</h2>

            <div class="form-row">
                <div class="form-group"><label>Full Name</label><input type="text" name="full_name" required></div>
                <div class="form-group"><label>Birthdate</label><input type="date" name="birthdate"></div>
                <div class="form-group"><label>Gender</label>
                    <select name="gender" required><option value="">Select</option><option>Male</option><option>Female</option></select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group"><label>Address</label><input type="text" name="address"></div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="tel" name="contact_number" maxlength="11" pattern="[0-9]{11}" 
                           title="Please enter exactly 11 digits" 
                           oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)" required>
                    <small style="color: #666; font-size: 12px;">Must be exactly 11 digits</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
            </div>

<div class="form-row">
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" id="password" required>
    </div>

    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" id="confirm_password" required>
        <small id="password_message" style="font-weight:bold;"></small>
    </div>
</div>


            <div class="form-row">
<div class="form-group">
    <label>Position</label>

    <!-- Visible but disabled -->
    <select disabled>
        <option value="Captain">Captain</option>
    </select>

    <!-- Hidden but submitted to database -->
    <input type="hidden" name="position" value="Captain">
</div>

                <div class="form-group"><label>Start Term</label><input type="date" name="start_term" required></div>
                <div class="form-group"><label>End Term</label><input type="date" name="end_term" required></div>
            </div>

            <div class="form-group">
                <label>Upload Official ID</label>
                <label class="id-upload" for="official_id_upload">
                    <i class="fas fa-id-card"></i>
                    <p>Click here to upload your Barangay ID (JPG, PNG, GIF, or PDF)</p>
                    <input type="file" id="official_id_upload" name="official_id_upload" accept="image/*,.pdf" onchange="previewID(event)" style="display: none;">
                    <img id="idPreview" class="id-preview" alt="ID Preview">
                    <div id="uploadStatus" class="upload-status"></div>
                </label>
                <div class="file-input-info">
                    <strong>Accepted formats:</strong> JPG, PNG, GIF, PDF | <strong>Max size:</strong> 5MB
                </div>
            </div>
        </div>

        <!-- 🏘️ BARANGAY INFORMATION -->
        <div class="form-section">
            <h2>Barangay Information</h2>

            <div class="form-row">
                <div class="form-group">
                    <label>Barangay Name</label>
                    <select name="barangay_name" id="barangaySelect" onchange="updatePurokCount()" required>
                        <option value="">Select Barangay</option>
                        <option value="Alasas">Alasas</option>
                        <option value="Del Rosario">Del Rosario</option>
                        <option value="Saguin">Saguin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Municipality/City</label>
                    <input type="text" name="municipality" value="City of San Fernando" disabled required>
                    <input type="hidden" name="municipality" value="City of San Fernando">
                </div>
                <div class="form-group">
                    <label>Province</label>
                    <input type="text" name="province" value="Pampanga" disabled required>
                    <input type="hidden" name="province" value="Pampanga">
                </div>
            </div>

            <h3>Barangay Contact Information</h3>
            <div class="form-row">
                <div class="form-group"><label>Barangay Hall Address</label><input type="text" name="barangay_hall"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="tel" name="barangay_contact" maxlength="11" pattern="[0-9]{11}" 
                           title="Please enter exactly 11 digits" 
                           oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)">
                    <small style="color: #666; font-size: 12px;">Must be exactly 11 digits</small>
                </div>
                <div class="form-group"><label>Email Address</label><input type="email" name="barangay_email"></div>
                <div class="form-group"><label>Website / Facebook Page</label><input type="text" name="barangay_website"></div>
            </div>

            <h3>Barangay Profile</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Total Population</label>
                    <input type="number" name="total_population" min="0" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                </div>
                <div class="form-group">
                    <label>Total Households</label>
                    <input type="number" name="total_households" min="0" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Puroks</label>
                    <input type="number" name="purok_count" id="purokCount" min="1" max="20" readonly>
                    <small style="color: #666; font-size: 12px;">Automatically set based on barangay selection</small>
                </div>
            </div>

            <div class="form-group" id="purokNamesContainer"></div>

            <h3>Disaster and Risk Management</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Is your barangay flood-prone?</label>
                    <select name="flood_prone" id="floodProneSelect" onchange="toggleAffectedAreas()">
                        <option value="">Select</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group" id="affectedAreasContainer" style="display: none;">
                    <label>Most affected areas</label>
                    <div class="checkbox-container" id="affectedAreasCheckboxes">
                        <!-- Checkboxes will be dynamically added here -->
                    </div>
                    <small style="color: #666; font-size: 12px;">Check all puroks that are flood-prone</small>
                </div>
            </div>

<div class="form-group">
    <label>Designated Evacuation Center</label>
    <select name="evacuation_center" required>
        <option value="" disabled selected>Select evacuation center</option>
        <option value="Barangay Hall">Barangay Hall</option>
        <option value="Covered Court">Covered Court</option>
        <option value="Elementary School">Elementary School</option>
        <option value="Multi-Purpose Hall">Multi-Purpose Hall</option>
        <option value="Evacuation Center Building">Evacuation Center Building</option>
        <option value="Nearby School Gymnasium">Nearby School Gymnasium</option>
        <option value="Community Center">Community Center</option>
    </select>
</div>

            <h3>Barangay Budget and Finance</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Annual Budget (₱)</label>
                    <input type="number" name="annual_budget" min="0" step="0.01" oninput="this.value=this.value.replace(/[^0-9.]/g,'')">
                </div>
            </div><div class="form-group">
    <label>Sources of Funds</label>
    <select name="budget_allocation" required>
        <option value="" disabled selected>Select main source of funds</option>
        <option value="Internal Revenue Allotment (IRA)">Internal Revenue Allotment (IRA)</option>
        <option value="Real Property Tax Share">Real Property Tax Share</option>
        <option value="Community Tax Share">Community Tax Share</option>
        <option value="Sand / Quarry Tax Share">Sand / Quarry Tax Share</option>
        <option value="Barangay Enterprises / Public Utilities">Barangay Enterprises / Public Utilities</option>
        <option value="Fees & Charges">Fees & Charges (clearance, facility, etc.)</option>
        <option value="Fines and Penalties">Fines and Penalties</option>
        <option value="Fundraising / Donations">Fundraising / Donations</option>
        <option value="Subsidy from Other LGUs">Subsidy from Other LGUs</option>
    </select>
</div>

        </div>

        <div class="form-submit">
            <button type="submit" class="submit-btn"><i class="fas fa-user-plus"></i> Register as Official</button>
            <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </form>
</div>

<script>
// Store purok names for later use
let purokNames = [];

// Barangay to purok count mapping
const barangayPurokCount = {
    "Alasas": 3,
    "Del Rosario": 4,
    "Saguin": 7
};

function updatePurokCount() {
    const barangaySelect = document.getElementById('barangaySelect');
    const purokCountInput = document.getElementById('purokCount');
    const selectedBarangay = barangaySelect.value;
    
    if (selectedBarangay && barangayPurokCount[selectedBarangay]) {
        purokCountInput.value = barangayPurokCount[selectedBarangay];
        generatePurokFields();
    } else {
        purokCountInput.value = '';
        document.getElementById('purokNamesContainer').innerHTML = '';
        purokNames = [];
    }
}

function previewID(event) {
    const preview = document.getElementById('idPreview');
    const file = event.target.files[0];
    const status = document.getElementById('uploadStatus');
    
    if (file) {
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            status.textContent = 'File too large! Maximum size is 5MB.';
            status.className = 'upload-status error';
            status.style.display = 'block';
            event.target.value = '';
            preview.style.display = 'none';
            return;
        }
        
        // Validate file type
        const fileType = file.type.toLowerCase();
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        
        if (!validTypes.includes(fileType)) {
            status.textContent = 'Invalid file type! Please upload JPG, PNG, GIF, or PDF.';
            status.className = 'upload-status error';
            status.style.display = 'block';
            event.target.value = '';
            preview.style.display = 'none';
            return;
        }
        
        // Show preview for images
        if (fileType.startsWith('image/')) {
            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
        
        // Show success message
        status.textContent = 'File selected: ' + file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
        status.className = 'upload-status success';
        status.style.display = 'block';
    } else {
        preview.style.display = 'none';
        status.style.display = 'none';
    }
}

function generatePurokFields() {
    const count = parseInt(document.getElementById('purokCount').value);
    const container = document.getElementById('purokNamesContainer');
    container.innerHTML = '';
    purokNames = []; // Reset purok names array
    
    if (count > 0 && count <= 20) {
        const label = document.createElement('label');
        label.textContent = "Names of Puroks/Sitios:";
        label.style.display = 'block';
        label.style.marginBottom = '10px';
        label.style.fontWeight = 'bold';
        container.appendChild(label);
        
        for (let i = 1; i <= count; i++) {
            const div = document.createElement('div');
            div.className = 'purok-input';
            
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'purok_names[]';
            input.placeholder = `Purok ${i} Name`;
            input.required = true;
            input.addEventListener('input', updateAffectedAreasCheckboxes);
            
            div.appendChild(input);
            container.appendChild(div);
        }
    } else if (count > 20) {
        alert('Maximum of 20 puroks allowed');
        document.getElementById('purokCount').value = '';
        container.innerHTML = '';
    }
}

function updateAffectedAreasCheckboxes() {
    const purokInputs = document.querySelectorAll('input[name="purok_names[]"]');
    purokNames = Array.from(purokInputs)
        .map(input => input.value.trim())
        .filter(name => name !== '');
    
    const affectedAreasContainer = document.getElementById('affectedAreasCheckboxes');
    affectedAreasContainer.innerHTML = '';
    
    purokNames.forEach((purokName, index) => {
        const div = document.createElement('div');
        div.className = 'checkbox-item';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'affected_areas[]';
        checkbox.value = purokName;
        checkbox.id = `purok_${index}`;
        
        const label = document.createElement('label');
        label.htmlFor = `purok_${index}`;
        label.textContent = purokName;
        
        div.appendChild(label);
        div.appendChild(checkbox);
        affectedAreasContainer.appendChild(div);
    });
    
    // If flood-prone is already set to "Yes", show the checkboxes
    if (document.getElementById('floodProneSelect').value === 'Yes') {
        toggleAffectedAreas();
    }
}

function toggleAffectedAreas() {
    const floodProneSelect = document.getElementById('floodProneSelect');
    const affectedAreasContainer = document.getElementById('affectedAreasContainer');
    const affectedAreasCheckboxes = document.getElementById('affectedAreasCheckboxes');
    
    if (floodProneSelect.value === 'Yes') {
        if (purokNames.length > 0) {
            affectedAreasContainer.style.display = 'block';
        } else {
            alert('Please enter purok names first before selecting flood-prone areas.');
            floodProneSelect.value = '';
            affectedAreasContainer.style.display = 'none';
        }
    } else {
        affectedAreasContainer.style.display = 'none';
        // Clear selected checkboxes when hiding
        const checkboxes = affectedAreasCheckboxes.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);
    }
}

// Contact number validation
document.addEventListener('DOMContentLoaded', function() {
    const contactInputs = document.querySelectorAll('input[type="tel"]');
    contactInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
        });
    });
});
</script>
<script>
// Show modal on page load
window.addEventListener("load", function () {
    const modal = document.getElementById("captainModal");

    document.getElementById("yesCaptain").onclick = function () {
        modal.style.display = "none"; // allow access
    };

    document.getElementById("noCaptain").onclick = function () {
        window.location.href = "index.php"; // change if your landing page is different
    };
});
</script>

<script>
const pass = document.getElementById("password");
const confirmPass = document.getElementById("confirm_password");
const message = document.getElementById("password_message");

// Live checking
confirmPass.addEventListener("keyup", function () {
    if (confirmPass.value.length === 0) {
        message.textContent = "";
        return;
    }

    if (pass.value === confirmPass.value) {
        message.textContent = "✔ Passwords match";
        message.style.color = "green";
    } else {
        message.textContent = "✖ Passwords do not match";
        message.style.color = "red";
    }
});

// Also prevent form submission if mismatch
document.querySelector("form").addEventListener("submit", function (e) {
    if (pass.value !== confirmPass.value) {
        e.preventDefault();
        alert("Passwords do not match. Please fix it before submitting.");
    }
});
</script>


</body>
</html>