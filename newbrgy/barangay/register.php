<?php
session_start();
require_once 'db_connection.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieving form data
    $full_name = $_POST['full_name'];
    $birthdate = $_POST['birthdate'];
    $purok = $_POST['purok'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    $zipcode = $_POST['zipcode'];

    // Generate password by removing spaces from full_name and appending the birthdate
    $password = str_replace(' ', '', $full_name) . str_replace('-', '', $birthdate);

    // Process resident photo (base64 encoded)
    $photo = $_POST['photo'];
    $photo = str_replace('data:image/png;base64,', '', $photo);
    $photo = str_replace(' ', '+', $photo);
    $photo_data = base64_decode($photo);
    $photo_file = 'uploads/' . uniqid() . '_photo.png';
    file_put_contents($photo_file, $photo_data);

    // Process ID photo (base64 encoded)
    $id_photo = $_POST['id_photo'];
    $id_photo = str_replace('data:image/png;base64,', '', $id_photo);
    $id_photo = str_replace(' ', '+', $id_photo);
    $id_photo_data = base64_decode($id_photo);
    $id_photo_file = 'uploads/' . uniqid() . '_id_photo.png';
    file_put_contents($id_photo_file, $id_photo_data);

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO users (full_name, password, birthdate, purok, barangay, city, zipcode, photo, id_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $full_name, $password, $birthdate, $purok, $barangay, $city, $zipcode, $photo_file, $id_photo_file);

    if ($stmt->execute()) {
        $success_message = "Registration successful. You can now log in and here is your $password.";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Barangay Request System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="neumorphic p-4">
                    <h2 class="text-center mb-4">Register as New Resident</h2>
                    <?php
                    if (!empty($error_message)) {
                        echo "<div class='alert alert-danger'>$error_message</div>";
                    }
                    if (!empty($success_message)) {
                        echo "<div class='alert alert-success'>$success_message</div>";
                    }
                    ?>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name:</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="birthdate" class="form-label">Birthdate:</label>
                            <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                        </div>
                        <div class="mb-3">
                            <label for="purok" class="form-label">Purok:</label>
                            <input type="text" class="form-control" id="purok" name="purok" required>
                        </div>
                        <div class="mb-3">
                            <label for="barangay" class="form-label">Barangay:</label>
                            <input type="text" class="form-control" id="barangay" name="barangay" required>
                        </div>
                        <div class="mb-3">
                            <label for="city" class="form-label">City:</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="mb-3">
                            <label for="zipcode" class="form-label">Zipcode:</label>
                            <input type="text" class="form-control" id="zipcode" name="zipcode" required>
                        </div>
                        <div class="mb-3">
                            <div id="my_camera"></div>
                            <label class="form-label">Resident Photo:</label>

                            <button type="button" class="btn btn-primary btn-sm mt-2"
                                onclick="take_snapshot('photo')">Capture Photo</button>
                            <input type="hidden" name="photo" id="photo">
                            <div id="photo_result" class="mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <div id="my_camera_id"></div>
                            <label class="form-label">ID Photo:</label>

                            <button type="button" class="btn btn-primary btn-sm mt-2"
                                onclick="take_snapshot('id_photo')">Capture ID Photo</button>
                            <input type="hidden" name="id_photo" id="id_photo">
                            <div id="id_photo_result" class="mt-2"></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-neumorphic w-100">Register</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="index.php">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Setup ng Webcam
        Webcam.set({
            width: 320,
            height: 240,
            image_format: 'png',
            jpeg_quality: 90
        });

        // I-attach ang webcam sa tamang div
        Webcam.attach('#my_camera');
    });

    // Function para sa pagkuha ng larawan
    function take_snapshot(type) {
        Webcam.snap(function (data_uri) {
            if (type === 'photo') {
                document.getElementById('photo_result').innerHTML = '<img src="' + data_uri + '"/>';
                document.getElementById('photo').value = data_uri;
            } else if (type === 'id_photo') {
                document.getElementById('id_photo_result').innerHTML = '<img src="' + data_uri + '"/>';
                document.getElementById('id_photo').value = data_uri;
            }
        });
    }
</script>


</body>

</html>