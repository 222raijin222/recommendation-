<?php
require_once "config.php";

// Check login
$is_logged_in = isLoggedIn();
$is_super_admin = ($is_logged_in && $_SESSION['user']['role'] === 'super_admin');
$is_captain = ($is_logged_in && $_SESSION['user']['role'] === 'captain');
$captain_barangay_name = $_SESSION['user']['barangay_name'] ?? null;

// GET DATA
if ($is_super_admin) {
    $total_residents = getResidentCount();
    $total_households = getHouseholdCount();
    $age_distribution = getAgeDistribution();
    $gender_distribution = getGenderDistribution();
    $employment_status = getEmploymentStatus();
    $barangay_scope = "All Barangays";
} elseif ($is_captain) {
    $total_residents = getResidentCountByBarangay($captain_barangay_name);
    $total_households = getHouseholdCountByBarangay($captain_barangay_name);
    $age_distribution = getAgeDistributionByBarangay($captain_barangay_name);
    $gender_distribution = getGenderDistributionByBarangay($captain_barangay_name);
    $employment_status = getEmploymentStatusByBarangay($captain_barangay_name);
    $barangay_scope = "Barangay " . $captain_barangay_name;
} else {
    $total_residents = getResidentCount();
    $total_households = getHouseholdCount();
    $age_distribution = getAgeDistribution();
    $gender_distribution = getGenderDistribution();
    $employment_status = getEmploymentStatus();
    $barangay_scope = "Public View (System Default)";
}

// Narrative generator function
function narrative($total_residents, $total_households, $age_data, $gender_data, $employment_data) {

    $youth = $age_data['0-17'] ?? 0;
    $young_adults = $age_data['18-24'] ?? 0;
    $adults = $age_data['25-34'] ?? 0;

    $male = $gender_data['Male'] ?? 0;
    $female = $gender_data['Female'] ?? 0;

    $employed = $employment_data['Employed'] ?? 0;
    $unemployed = $employment_data['Unemployed'] ?? 0;
    $students = $employment_data['Student'] ?? 0;

    return "
        This report provides an overview of the demographic and socioeconomic profile of the community.
        As of the latest update, the population consists of <b>{$total_residents} residents</b> belonging
        to <b>{$total_households} households</b>. A significant portion of the population is composed of
        youth aged 0–17, totaling <b>{$youth}</b> individuals. Meanwhile, young adults aged 18–24 account
        for <b>{$young_adults}</b> residents, and adults aged 25–34 make up <b>{$adults}</b> residents.

        In terms of gender distribution, the community has <b>{$male}</b> males and <b>{$female}</b> females.
        Employment records reveal that approximately <b>{$employed}</b> residents are employed, while 
        <b>{$unemployed}</b> are currently unemployed. There are also <b>{$students}</b> students actively
        pursuing education.

        The information presented in this report aims to support barangay officials in planning future
        programs, allocating resources, and identifying priority areas for intervention and community
        development.
    ";
}

// Convert array results into simple label-value maps
$age_map = [];
foreach ($age_distribution as $row) {
    $age_map[$row['age_group']] = $row['count'];
}
$gender_map = [];
foreach ($gender_distribution as $row) {
    $gender_map[$row['gender']] = $row['count'];
}
$employment_map = [];
foreach ($employment_status as $row) {
    $employment_map[$row['employment_status']] = $row['count'];
}

// Generate narrative
$narrative = narrative($total_residents, $total_households, $age_map, $gender_map, $employment_map);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Printable Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }
        h1, h2 {
            text-align: center;
        }
        .section {
            margin: 25px 0;
        }
        .print-btn {
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            margin-bottom: 20px;
            cursor: pointer;
        }
        @media print {
            .print-btn {
                display: none;
            }
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }
        table th, table td {
            border: 1px solid #999;
            padding: 8px;
        }
    </style>
</head>

<body>

<button class="print-btn" onclick="window.print()">Print This Report</button>

<h1>Barangay Demographic & Socioeconomic Report</h1>
<h2><?= $barangay_scope ?></h2>
<p><small>Generated on <?= date("F j, Y, g:i A") ?></small></p>

<div class="section">
    <h2>Summary Overview</h2>
    <p><?= nl2br($narrative) ?></p>
</div>

<div class="section">
    <h2>Statistical Data</h2>

    <h3>Total Counts</h3>
    <table>
        <tr><th>Total Residents</th><td><?= number_format($total_residents) ?></td></tr>
        <tr><th>Total Households</th><td><?= number_format($total_households) ?></td></tr>
    </table>

    <h3>Age Distribution</h3>
    <table>
        <tr><th>Age Group</th><th>Count</th></tr>
        <?php foreach ($age_map as $group => $count): ?>
            <tr><td><?= $group ?></td><td><?= $count ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h3>Gender Distribution</h3>
    <table>
        <tr><th>Gender</th><th>Count</th></tr>
        <?php foreach ($gender_map as $g => $count): ?>
            <tr><td><?= $g ?></td><td><?= $count ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h3>Employment Status</h3>
    <table>
        <tr><th>Status</th><th>Count</th></tr>
        <?php foreach ($employment_map as $status => $count): ?>
            <tr><td><?= $status ?></td><td><?= $count ?></td></tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
