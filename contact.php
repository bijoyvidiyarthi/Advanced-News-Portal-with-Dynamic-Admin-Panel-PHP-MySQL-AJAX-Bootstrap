<?php
include_once "admin/config.php"; // আপনার ডাটাবেজ কানেকশন
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Global News 24/7</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }

        .contact-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .contact-info {
            background: #0d6efd;
            color: white;
            padding: 40px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card contact-card shadow">
                    <div class="row g-0">
                        <div class="col-md-5 contact-info d-flex flex-column justify-content-center">
                            <h3 class="fw-bold mb-4">Get in Touch</h3>
                            <p class="mb-5">Have a news tip or a question? Feel free to contact us. We are here to help
                                you 24/7.</p>

                            <div class="d-flex mb-4">
                                <i class="fas fa-map-marker-alt me-3 mt-1 fs-5"></i>
                                <span>Dhaka, Bangladesh</span>
                            </div>
                            <div class="d-flex mb-4">
                                <i class="fas fa-phone-alt me-3 mt-1 fs-5"></i>
                                <span>+880 1XXX-XXXXXX</span>
                            </div>
                            <div class="d-flex mb-4">
                                <i class="fas fa-envelope me-3 mt-1 fs-5"></i>
                                <span>info@globalnews24.com</span>
                            </div>
                        </div>

                        <div class="col-md-7 p-5 bg-white">
                            <h3 class="fw-bold mb-4 text-dark">Send a Message</h3>
                            <div id="response-msg"></div>

                            <form id="contactForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="small fw-bold">Your Name</label>
                                        <input type="text" name="name" class="form-control" placeholder="John Doe"
                                            required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small fw-bold">Your Email</label>
                                        <input type="email" name="email" class="form-control"
                                            placeholder="john@example.com" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold">Subject</label>
                                    <input type="text" name="subject" class="form-control"
                                        placeholder="Breaking News Tip" required>
                                </div>
                                <div class="mb-4">
                                    <label class="small fw-bold">Message</label>
                                    <textarea name="message" class="form-control" rows="4"
                                        placeholder="Write your message here..." required></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" id="sendBtn" class="btn btn-primary btn-lg shadow-sm">
                                        <span id="btnText">Send Message</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#contactForm').on('submit', function (e) {
                e.preventDefault();

                let btn = $('#sendBtn');
                btn.prop('disabled', true).find('#btnText').text('Sending...');

                $.ajax({
                    url: 'process-contact.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#response-msg').html('<div class="alert alert-success">' + response.message + '</div>');
                            $('#contactForm')[0].reset();
                        } else {
                            $('#response-msg').html('<div class="alert alert-danger">' + response.message + '</div>');
                        }
                        btn.prop('disabled', false).find('#btnText').text('Send Message');
                    }
                });
            });
        });
    </script>

</body>

</html>