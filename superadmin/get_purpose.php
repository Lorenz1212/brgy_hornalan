<?php
include '../connection/connect.php';

if (isset($_GET['docs_type'])) {
    $docsType = $_GET['docs_type'];  // Use docs_type as the key for the parameter

    // Define purposes based on docs_type
    $purposes = [];

    if ($docsType == "Barangay Clearance" || $docsType == "Indigency") {
        $purposes = [
            "Job Employment",
            "Government Transactions",
            "Travel Requirements",
            "School Requirement",
            "Identification"
        ];
    } 
    elseif ($docsType == "Business Clearance") {
        $purposes = [
            "Business Registration",
            "Renewal of Business Permit",
            "Loan Application",
            "Business Expansion"
        ];
    } 
    elseif ($docsType == "Barangay Certificate") {
        $purposes = [
            "Government Transactions",
            "Identification"

        ];
    } 
    elseif ($docsType == "Residency") {
        $purposes = [
            "Government Transactions",
            "Travel Requirements",
            "Identification",
            "School Requirement"
        ];
    }  
    elseif ($docsType == "Cedula") {
        $purposes = [
            "Government Transactions",
            "Business Registration",
            "Renewal of Business Permit"
        ];
    } 
    elseif ($docsType == "First Time Job Seeker Certificate") {
        $purposes = [
            "Job Employment"
        ];
    }

    // Output purposes as <option> tags
    echo "<option value=''>Select Purpose</option>";
    foreach ($purposes as $purpose) {
        echo "<option value='" . htmlspecialchars($purpose) . "'>" . htmlspecialchars($purpose) . "</option>";
    }
}
?>
