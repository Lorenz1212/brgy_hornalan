<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Popup Notifications</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin: 5px;
        }

        .success-btn { background: #16a085; color: white; }
        .error-btn { background: #e74c3c; color: white; }
        .warning-btn { background: #f39c12; color: white; }
        .info-btn { background: #3498db; color: white; }

        button:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>

    <h1>Try These Modern Popups</h1>

    <button class="success-btn" onclick="showSuccess()">Success</button>
    <button class="error-btn" onclick="showError()">Error</button>
    <button class="warning-btn" onclick="showWarning()">Warning</button>
    <button class="info-btn" onclick="showInfo()">Info</button>

    <script>
        function showSuccess() {
            Swal.fire({
                title: 'Success!',
                text: 'Your action was successful.',
                icon: 'success',
                showConfirmButton: false,  // Hide the button initially
                didOpen: () => {
                    Swal.showLoading();  // Show the spinner
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Your action was successful.',
                            icon: 'success',
                            confirmButtonColor: '#16a085'
                        });
                    }, 2000);  // Simulate loading time (2 seconds)
                }
            });
        }

        function showError() {
            Swal.fire({
                title: 'Error!',
                text: 'Something went wrong. Please try again.',
                icon: 'error',
                showConfirmButton: false,  // Hide the button initially
                didOpen: () => {
                    Swal.showLoading();  // Show the spinner
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Something went wrong. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#e74c3c'
                        });
                    }, 2000);  // Simulate loading time (2 seconds)
                }
            });
        }

        function showWarning() {
            Swal.fire({
                title: 'Warning!',
                text: 'Are you sure you want to proceed?',
                icon: 'warning',
                showConfirmButton: false,  // Hide the button initially
                didOpen: () => {
                    Swal.showLoading();  // Show the spinner
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Warning!',
                            text: 'Are you sure you want to proceed?',
                            icon: 'warning',
                            confirmButtonColor: '#f39c12'
                        });
                    }, 2000);  // Simulate loading time (2 seconds)
                }
            });
        }

        function showInfo() {
            Swal.fire({
                title: 'Information',
                text: 'This is an important notice.',
                icon: 'info',
                showConfirmButton: false,  // Hide the button initially
                didOpen: () => {
                    Swal.showLoading();  // Show the spinner
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Information',
                            text: 'This is an important notice.',
                            icon: 'info',
                            confirmButtonColor: '#3498db'
                        });
                    }, 2000);  // Simulate loading time (2 seconds)
                }
            });
        }
    </script>

</body>
</html>
