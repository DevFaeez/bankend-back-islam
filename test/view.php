<?php
include '../Config/Database.php';

use Config\Database;

$conn = Database::getConnection();

// SQL queries
$sqlUsers = "SELECT * FROM USERS";
$sqlAccounts = "SELECT * FROM ACCOUNT";

// Execute USERS query
$stidUsers = oci_parse($conn, $sqlUsers);
oci_execute($stidUsers);

// Execute ACCOUNT query
$stidAccounts = oci_parse($conn, $sqlAccounts);
oci_execute($stidAccounts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User & Account Tables</title>
    <style>
        body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 10;
        padding: 0;
        display: flex;
        justify-content: center;    /* center horizontally */
        align-items: center;        /* center vertically */
        min-height: 100vh;          /* full screen height */
        flex-direction: column;
    }

        h2 {
            text-align: center;
        }

        .table-container {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .table-box {
            flex: 1;
            min-width: 300px;
            max-width: 100%;
        }

        .table-scroll {
            overflow-x: auto;
            border-radius: 10px;
             box-shadow: 0 5px 10px rgba(0, 0, 0, 0.34);

        }

        table {
            min-width: 600px;
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px 16px;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        th {
            background-color: rgb(185, 29, 69);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f1f3f5;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        .register-form {
            background-color: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin-top: 20px;
        }

    .register-form h2 {
      text-align: center;
      margin-bottom: 25px;
      color: rgb(185, 29, 69);
    }

    .register-form label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
    }

    .register-form input {
      width: 100%;
      padding: 10px;
      margin-bottom: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .register-form button {
      width: 100%;
      padding: 12px;
      background-color: rgb(185, 29, 69);
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }

    .register-form button:hover {
      background-color: #8a132f;
    }

    .register-form .note {
      font-size: 13px;
      color: #777;
      margin-top: -10px;
      margin-bottom: 16px;
    }
    </style>
</head>
<body>

<h2>User and Account Information</h2>

<div class="table-container">

    <!-- USERS Table -->
    <div class="table-box">
        <h3 style="text-align:center;">Users</h3>
        <div class="table-scroll">
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>NRIC</th>
                    <th>Full Name</th>
                    <th>Phone Number</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = oci_fetch_assoc($stidUsers)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['USERID']) ?></td>
                        <td><?= htmlspecialchars($row['EMAIL']) ?></td>
                        <td><?= htmlspecialchars($row['NRICNUMBER']) ?></td>
                        <td><?= htmlspecialchars($row['FULLNAME']) ?></td>
                        <td><?= htmlspecialchars($row['PHONENUMBER']) ?></td>
                        <td><?= htmlspecialchars($row['STATUS']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <!-- ACCOUNT Table -->
    <div class="table-box">
        <h3 style="text-align:center;">Accounts</h3>
        <div class="table-scroll">
            <table>
                <tr>
                    <th>Account ID</th>
                    <th>Account Number</th>
                    <th>Password</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Opened At</th>
                    <th>Username</th>
                    <th>User ID</th>
                    <th>Employee ID</th>
                </tr>
                <?php while ($row = oci_fetch_assoc($stidAccounts)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ACCOUNTID']) ?></td>
                        <td><?= htmlspecialchars($row['ACCOUNTNUMBER']) ?></td>
                        <td><?= htmlspecialchars($row['PASSWORD']) ?></td>
                        <td><?= htmlspecialchars($row['BALANCE']) ?></td>
                        <td><?= htmlspecialchars($row['STATUS']) ?></td>
                        <td><?= htmlspecialchars($row['OPENEDAT']) ?></td>
                        <td><?= htmlspecialchars($row['USERNAME']) ?></td>
                        <td><?= htmlspecialchars($row['USERID']) ?></td>
                        <td><?= htmlspecialchars($row['EMPLOYEEID'] ?? 'null') ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

</div>

<form class="register-form" id="registerForm">
    <h2>User Registration</h2>

    <label for="email">Email</label>
    <input type="email" name="email" id="email" required>

    <label for="nricNumber">NRIC Number</label>
    <input type="text" name="nricNumber" id="nricNumber" required>

    <label for="fullName">Full Name</label>
    <input type="text" name="fullName" id="fullName" required>

    <label for="phoneNumber">Phone Number</label>
    <input type="text" name="phoneNumber" id="phoneNumber" required>

    <label for="password">Password</label>
    <input type="password" name="password" id="password" required>

    <label for="employeeId">Employee ID</label>
    <input type="number" name="employeeId" id="employeeId" value="3001" required>

    <input type="hidden" name="status" value="active">

    <button type="submit">Register</button>
  </form>

  <script>
document.getElementById("registerForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const form = e.target;
    const data = {
        email: form.email.value,
        nricNumber: form.nricNumber.value,
        fullName: form.fullName.value,
        phoneNumber: form.phoneNumber.value,
        status: form.status.value,
        password: form.password.value,
        employeeId: parseInt(form.employeeId.value),
        openedAt: "" // Optional field
    };

    try {
        const response = await fetch('../controller/UserController.php?action=register', {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        alert(result.message);

        if (result.result === "success") {
            location.reload(); // Refresh to show new record
        }
    } catch (error) {
        alert("Registration failed: " + error.message);
    }
});
</script>


</body>
</html>

<?php
oci_free_statement($stidUsers);
oci_free_statement($stidAccounts);
oci_close($conn);
?>
