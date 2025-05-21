<?php
include 'db.php';
require_once('tcpdf/tcpdf.php');

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM groceries WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Handle ADD
if (isset($_POST['add'])) {
    $stmt = $conn->prepare("INSERT INTO groceries (name, category, price, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $_POST['name'], $_POST['category'], $_POST['price'], $_POST['quantity']);
    $stmt->execute();
    header("Location: index.php");
    exit;
}

// Handle PDF generation
if (isset($_POST['generate_pdf'])) {
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Grocery Inventory Report', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', '', 12);
    $tbl = '<table border="1" cellpadding="4">
            <thead>
                <tr style="background-color:#dfe6e9;">
                    <th><b>Name</b></th>
                    <th><b>Category</b></th>
                    <th><b>Price</b></th>
                    <th><b>Quantity</b></th>
                </tr>
            </thead><tbody>';

    $result = $conn->query("SELECT * FROM groceries");
    while ($row = $result->fetch_assoc()) {
        $tbl .= '<tr>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . htmlspecialchars($row['category']) . '</td>
                    <td>' . number_format($row['price'], 2) . '</td>
                    <td>' . $row['quantity'] . '</td>
                 </tr>';
    }

    $tbl .= '</tbody></table>';
    $pdf->writeHTML($tbl, true, false, false, false, '');

    $pdf->Output('grocery_report.pdf', 'I');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Grocery CRUD System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f2f2f2;
            display: flex;
        }
        .sidebar {
            width: 220px;
            background: #2d3436;
            color: #fff;
            padding: 20px;
            height: 100vh;
            position: fixed;
        }
        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 30px;
        }
        .sidebar button, .sidebar .tab-btn {
            background: #636e72;
            color: #fff;
            border: none;
            padding: 10px 15px;
            margin: 10px 0;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        .sidebar .tab-btn:hover {
            background: #00b894;
        }

        .main-content {
            margin-left: 240px;
            padding: 30px;
            width: 100%;
        }

        h1 {
            color: #2d3436;
        }

        .grocery-form input, .grocery-form button {
            padding: 10px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .grocery-form button {
            background: #0984e3;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .styled-table th, .styled-table td {
            padding: 12px 15px;
            border: 1px solid #ccc;
            text-align: left;
        }
        .styled-table th {
            background: #00cec9;
            color: #fff;
        }
        .btn.delete {
            background: #d63031;
            padding: 5px 10px;
            border-radius: 5px;
            color: #fff;
            text-decoration: none;
        }

        .tab {
            display: none;
        }
        .tab.active {
            display: block;
        }
    </style>
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        }

        window.onload = function () {
            showTab('view');
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-store"></i> Grocery App</h2>
        <button class="tab-btn" onclick="showTab('add')"><i class="fas fa-plus"></i> Add Grocery</button>
        <button class="tab-btn" onclick="showTab('view')"><i class="fas fa-list"></i> View Groceries</button>
        <form method="post">
            <button name="generate_pdf" class="tab-btn"><i class="fas fa-file-pdf"></i> Generate Report</button>
        </form>
    </div>

    <div class="main-content">
        <div id="add" class="tab">
            <h1><i class="fas fa-plus-circle"></i> Add Grocery</h1>
            <form method="post" class="grocery-form">
                <input type="text" name="name" placeholder="Name" required>
                <input type="text" name="category" placeholder="Category" required>
                <input type="number" step="0.01" name="price" placeholder="Price" required>
                <input type="number" name="quantity" placeholder="Quantity" required>
                <button type="submit" name="add"><i class="fas fa-plus-circle"></i> Add</button>
            </form>
        </div>

        <div id="view" class="tab">
            <h1><i class="fas fa-table"></i> Grocery List</h1>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Name</th><th>Category</th><th>Price</th><th>Quantity</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM groceries");
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= number_format($row['price'], 2) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td>
                            <a href="?delete=<?= $row['id'] ?>" class="btn delete" onclick="return confirm('Delete this item?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php   
$conn->close();
?>