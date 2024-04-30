<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Bootstrap demo</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
		integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
	<?php if ($this->session->flashdata('success')) { ?>
		<div class="alert alert-success" id="successMessage">
			<?php echo $this->session->flashdata('success'); ?>
			<?php $this->session->set_flashdata("success", '') ?>
		</div>
	<?php } ?>
	<?php if ($this->session->flashdata('error')) { ?>
		<div class="alert alert-danger" id="headerError">
			<?php echo $this->session->flashdata('error'); ?>
			<?php $this->session->set_flashdata("error", '') ?>
		</div>
	<?php } ?>
	<div class="container">
		<div class="row">
			<div class="col">
				<form post='post' action="UploadCSV/uploadUser" enctype="multipart/form-data">
					<div class="input-group mb-3">
						<label for="">User Upload</label>
						<input type="file" class="form-control" id="inputGroupFile01" name='userfile'>
					</div>
					<button class='btn btn-success'>Submit</button>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<form method='post' action="UploadCSV/uploadCoupon" enctype="multipart/form-data">
					<div class="input-group mb-3">
						<label for="">Coupons Upload</label>
						<input type="file" class="form-control" id="inputGroupFile01" name='couponfile'>
					</div>
					<button type="submit" class='btn btn-success'>Submit</button>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<form method='post' action="UploadCSV/uploadGiftCard" enctype="multipart/form-data">
					<div class="input-group mb-3">
						<label for="">Gift Card Upload</label>
						<input type="file" class="form-control" id="inputGroupFile01" name='giftCardFile'>
					</div>
					<button type="submit" class='btn btn-success'>Submit</button>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<form method='post' action="UploadCSV/uploadBatchCode" enctype="multipart/form-data">
					<div class="input-group mb-3">
						<label for="">Batch Code Upload</label>
						<input type="file" class="form-control" id="inputGroupFile01" name='batchCodeFile'>
					</div>
					<button type="submit" class='btn btn-success'>Submit</button>
				</form>
			</div>
		</div>
	</div>

	<script>
		document.addEventListener("DOMContentLoaded", function () {
			setTimeout(function () {
				var successMessage = document.getElementById('successMessage');
				let div=document.getElementById('headerError');
				if (successMessage) {
					successMessage.parentNode.removeChild(successMessage);
				}
				if(div){
					div.parentElement.removeChild(div);
				}
			}, 3000); 
		});
		
	</script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
		crossorigin="anonymous"></script>
</body>

</html>