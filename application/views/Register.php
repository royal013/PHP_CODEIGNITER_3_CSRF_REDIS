<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<?php
if ($this->session->flashdata('error')) { ?>
    <div class="alert alert-danger" role="alert" id='alert_div'>
        <?php echo $this->session->flashdata('error') ?>
        <?php $this->session->set_flashdata('error', '') ?>
    </div>
<?php } ?>


<body>
    <div class="container">
        <div class="row">
            <div class="col">
                <h3 class="text-center">Register Here</h3>
                <form method='post' id="form1" action=''>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">First Name:</label>
                        <input type="text" name="fname" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" required>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Last Name:</label>
                        <input type="text" name="lname" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" required>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Phone Number:</label>
                        <input type="number" name="phone" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" required>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Age:</label>
                        <input type="number" name="age" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" required>
                    </div>
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name() ?>"
                        value="<?php echo $this->security->get_csrf_hash() ?>">
                    <label for="">State</label>
                    <div class="input-group">
                        <select class="form-select" id="inputGroupSelect04"
                            aria-label="Example select with button addon" name='state' required>
                            <option value=''>Choose...</option>
                            <option value="AS">Assam</option>
                            <option value="TR">Tripura</option>
                            <option value="RJ">Rajasthan</option>
                            <option value="MP">Madhya Pradesh</option>
                            <option value="HR">Haryana</option>
                        </select>
                    </div>
                    <div class="mb-3" id="couponField">
                        <label for="exampleInputEmail1" class="form-label">Coupon</label>
                        <input type="text" name='coupon' class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp">
                    </div>
                    <div class="mb-3" id="batchCodeField">
                        <label for="exampleInputEmail1" class="form-label">BatchCode</label>
                        <input type="text" name='batchcode' class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            document.getElementById('inputGroupSelect04').addEventListener('change', function () {
                var selectedState = this.value;
                var couponField = document.getElementById('couponField');
                var batchCodeField = document.getElementById('batchCodeField');
                var form = document.getElementById('form1');
                form.action = selectedState;
                $.ajax({
                    url: 'Main/check_state_type',
                    method: 'POST',
                    data: { state: selectedState },
                    success: function (data) {
                        console.log('here', data);
                        if (data === 'coupon') {
                            batchCodeField.style.display = 'none';
                            couponField.style.display = 'block';
                        } else if (data === 'code') {
                            couponField.style.display = 'none';
                            batchCodeField.style.display = 'block';
                        } else if (data === 'coupon_giftcard') {
                            couponField.style.display = 'block';
                            batchCodeField.style.display = 'none';
                        }
                        else {
                            couponField.style.display = 'block';
                            batchCodeField.style.display = 'block';
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('Error:', errorThrown);
                    }
                });
            });
            let alert_div = document.getElementById('alert_div');
            setTimeout(() => {
                alert_div.style.display = "none";
            }, 3000);
        });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>


</html>