<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Retrieve the selected values from the form
    $company_id = $_POST['company'];
    $product_id = $_POST['product'];

    // Validate the inputs (simple validation)
    if (empty($company_id) || empty($product_id)) {
        echo "Both fields are required!";
    } else {
        // Fetch the company name from the database
        $stmt = $conn->prepare("SELECT name FROM company WHERE id = ?");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $stmt->bind_result($company_name);
        $stmt->fetch();
        $stmt->close();

        // Fetch the product name from the database
        $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($product_name);
        $stmt->fetch();
        $stmt->close();

        // Display the selected values
        echo "<h2>Your Selection</h2>";
        echo "Selected Company: " . htmlspecialchars($company_name) . "<br>";
        echo "Selected Product: " . htmlspecialchars($product_name) . "<br>";
    }
}

// Fetch all companies from the database
$sql = "SELECT id, name FROM company";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorite Selection Form</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#company').change(function() {
                var company_id = $(this).val();
                if (company_id) {
                    $.ajax({
                        type: 'POST',
                        url: '', // Since this is the same file, leave it empty
                        data: { get_products: true, company_id: company_id },
                        success: function(html) {
                            $('#product').html(html);
                        }
                    });
                } else {
                    $('#product').html('<option value="">Select company first</option>');
                }
            });
        });
    </script>
</head>
<body>
    <h2>Favorite Selection Form</h2>
    <form action="" method="POST">
        <label for="company">Select your favorite company:</label>
        <select name="company" id="company" required>
            <option value="">--Select Company--</option>
            <?php
            if ($result->num_rows > 0) {
                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row["id"] . "'>" . $row["name"]."-".$row["id"] . "</option>";
                }
            } else {
                echo "<option value=''>No companies available</option>";
            }
            ?>
        </select>
        <br><br>
        <label for="product">Select your favorite product:</label>
        <select name="product" id="product" required>
            <option value="">--Select Company First--</option>
        </select>
        <br><br>
        <button type="submit" name="submit">Submit</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['get_products'])) {
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $company_id = $_POST["company_id"];
        
        $sql = "SELECT id, name FROM products WHERE company_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<option value="">--Select Product--</option>';
            while($row = $result->fetch_assoc()) {
                echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
            }
        } else {
            echo '<option value="">No products available</option>';
        }
        $stmt->close();
        $conn->close();
        exit;
    }
    ?>
</body>
</html>
