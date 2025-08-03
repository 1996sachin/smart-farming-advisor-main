<?php
include('fsession.php');
ini_set('memory_limit', '-1');

if (!isset($_SESSION['farmer_login_user'])) {
    header("location: ../index.php");
    exit(); // Add exit to prevent further execution
}

$query4 = "SELECT * from farmerlogin where email='$user_check'";
$ses_sq4 = mysqli_query($conn, $query4);
$row4 = mysqli_fetch_assoc($ses_sq4);
$para1 = $row4['farmer_id'];
$para2 = $row4['farmer_name'];

// Initialize variables
$formatted_associative_array = [];
$recommendation_processed = false;

// Process form submission
if (isset($_POST['Crop_Recommend'])) {
    $n = (int)trim($_POST['n']);
    $p = (int)trim($_POST['p']);
    $k = (int)trim($_POST['k']);
    $t = (float)trim($_POST['t']);
    $h = (float)trim($_POST['h']);
    $ph = (float)trim($_POST['ph']);
    $r = (float)trim($_POST['r']);

    // Validate inputs
    if ($n >= 0 && $p >= 0 && $k >= 0 && $t > 0 && $h > 0 && $ph > 0 && $r >= 0) {
        // Escape arguments for shell command
        $command = sprintf(
            "python3 ML/crop_recommendation/recommend.py %d %d %d %.2f %.2f %.2f %.2f",
            $n, $p, $k, $t, $h, $ph, $r
        );

        $output = shell_exec($command);

        if ($output) {
            $decoded_output = json_decode($output, true);

            if ($decoded_output && is_array($decoded_output)) {
                // Filter and format output
                $filtered_output = array_filter($decoded_output, function($item) {
                    return is_array($item) && count($item) >= 2 && $item[1] > 0;
                });

                $formatted_associative_array = array_map(function($item) {
                    return [$item[0] => $item[1]];
                }, array_values($filtered_output));

                $recommendation_processed = true;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<?php include('fheader.php'); ?>

<body class="bg-white" id="top">

<?php include('fnav.php'); ?>

<section class="section section-shaped section-lg">
    <div class="shape shape-style-1 shape-primary">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="container-fluid">
        <div class="row row-content">
            <div class="col-md-12 mb-3">

                <div class="card text-white bg-gradient-success mb-3">
                    <form role="form" action="#" method="post">
                        <div class="card-header">
                            <span class="text-color display-4">Crop Recommendation</span>
                            <span class="pull-right">
                                <button type="submit" value="Recommend" name="Crop_Recommend" class="btn text-white btn-submit">SUBMIT</button>
                            </span>
                        </div>

                        <div class="card-body text-dark">
                            <table class="table table-striped table-hover table-bordered bg-gradient-white text-center display text-dark" id="myTable">
                                <thead>
                                <tr class="font-weight-bold text-dark">
                                    <th><center>Nitrogen</center></th>
                                    <th><center>Phosphorous</center></th>
                                    <th><center>Potassium</center></th>
                                    <th><center>Temperature</center></th>
                                    <th><center>Humidity</center></th>
                                    <th><center>PH</center></th>
                                    <th><center>Rainfall</center></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="text-center">
                                    <td>
                                        <div class="form-group">
                                            <input type='number' name='n' placeholder="Nitrogen Eg:90" required min="0" class="form-control" value="<?= isset($_POST['n']) ? htmlspecialchars($_POST['n']) : '' ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input type='number' name='p' placeholder="Phosphorus Eg:42" required min="0" class="form-control" value="<?= isset($_POST['p']) ? htmlspecialchars($_POST['p']) : '' ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input type='number' name='k' placeholder="Potassium Eg:43" required min="0" class="form-control" value="<?= isset($_POST['k']) ? htmlspecialchars($_POST['k']) : '' ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input type='number' name='t' step="0.01" placeholder="Temperature Eg:21" required min="0" class="form-control" value="<?= isset($_POST['t']) ? htmlspecialchars($_POST['t']) : '' ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input type='number' name='h' step="0.01" placeholder="Humidity Eg:82" required min="0" max="100" class="form-control" value="<?= isset($_POST['h']) ? htmlspecialchars($_POST['h']) : '' ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input type='number' name='ph' step="0.01" placeholder="PH Eg:6.5" required min="0" max="14" class="form-control" value="<?= isset($_POST['ph']) ? htmlspecialchars($_POST['ph']) : '' ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input type='number' name='r' step="0.01" placeholder="Rainfall Eg:203" required min="0" class="form-control" value="<?= isset($_POST['r']) ? htmlspecialchars($_POST['r']) : '' ?>">
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>

                <?php if ($recommendation_processed): ?>
                    <div class="card text-white bg-gradient-success mb-3">
                        <div class="card-header">
                            <span class="text-color display-4">Result</span>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($formatted_associative_array)): ?>
                                <h4>Recommended Crops:</h4>
                                <canvas id="myChart" width="400" height="200"></canvas>
                            <?php else: ?>
                                <h4>No suitable crops found for the given parameters.</h4>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<?php if ($recommendation_processed && !empty($formatted_associative_array)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = <?= json_encode(array_map(function($item) { return array_keys($item)[0]; }, $formatted_associative_array)); ?>;
        const data = <?= json_encode(array_map(function($item) { return array_values($item)[0]; }, $formatted_associative_array)); ?>;

        const ctx = document.getElementById('myChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Crop Suitability Score',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
<?php endif; ?>

<?php require("footer.php"); ?>

</body>
</html>