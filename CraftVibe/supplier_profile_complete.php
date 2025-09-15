<?php
// supplier_profile_complete.php
session_start();
require_once 'auth_check.php';

// Check if user is supplier
if ($_SESSION['user_role'] !== 'supplier') {
    header("Location: index.php");
    exit();
}

$con = mysqli_connect("localhost", "root", "", "tribal_arts_db") or die("Couldn't connect");

// Check if supplier profile already exists
$user_id = $_SESSION['user_id'];
$supplier_query = mysqli_query($con, "SELECT * FROM suppliers WHERE user_id = $user_id");
$supplier = mysqli_fetch_assoc($supplier_query);

// If supplier profile exists, redirect to supplier panel
if ($supplier) {
    header("Location: supplier.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = mysqli_real_escape_string($con, $_POST['company_name']);
    $contact_number = mysqli_real_escape_string($con, $_POST['contact_number']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $business_registration = mysqli_real_escape_string($con, $_POST['business_registration']);
    $tax_id = mysqli_real_escape_string($con, $_POST['tax_id']);
    
    // Insert supplier details
    $query = "INSERT INTO suppliers (user_id, company_name, contact_number, address, business_registration, tax_id, status) 
              VALUES ($user_id, '$company_name', '$contact_number', '$address', '$business_registration', '$tax_id', 'pending')";
    
    if (mysqli_query($con, $query)) {
        // Redirect to supplier panel
        header("Location: supplier.php");
        exit();
    } else {
        $error = "Error creating supplier profile: " . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Supplier Profile - Artisan Ark</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --bg-color: #f4f7fe;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: #333;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .profile-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 600px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header h1 {
            color: var(--primary);
            margin-bottom: 10px;
        }

        .profile-header p {
            color: #666;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: #ffebee;
            color: #f44336;
            border: 1px solid #ffcdd2;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Complete Your Supplier Profile</h1>
            <p>Please provide your business information to access the supplier panel.</p>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="company_name">Company Name *</label>
                <input type="text" id="company_name" name="company_name" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contact_number">Contact Number *</label>
                    <input type="text" id="contact_number" name="contact_number" required>
                </div>

                <div class="form-group">
                    <label for="tax_id">Tax ID (Optional)</label>
                    <input type="text" id="tax_id" name="tax_id">
                </div>
            </div>

            <div class="form-group">
                <label for="business_registration">Business Registration Number (Optional)</label>
                <input type="text" id="business_registration" name="business_registration">
            </div>

            <div class="form-group">
                <label for="address">Business Address *</label>
                <textarea id="address" name="address" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Complete Profile</button>
        </form>
    </div>
</body>
</html>
<?php
// Close database connection
if (isset($con)) {
    mysqli_close($con);
}