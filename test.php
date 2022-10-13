
<?php

$a1= array(
  0 => 
  1 => 19
);

print_r(array_values(array_filter($a1))); 
?>
<style>
.container {
  margin-top: 20px;
}
.panel-heading {
  font-size: larger;
}
.alert {
  display: none;
}
/**
 * Error color for the validation plugin
 */
.error {
  color: #e74c3c;
}
</style>
  <div class="panel panel-default">
    <div class="panel-heading">
      <a href="https://jqueryvalidation.org/" target="_blank">jQuery Validation Plugin</a> demo
    </div>
    <div class="panel-body">
      <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <strong>Successfully submitted!</strong> The form is valid.
      </div>
      <form role="form" id="myForm">
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" class="form-control" id="name" name="name">
        </div>
        <div class="form-group">
          <label for="email">Email address:</label>
          <input type="email" class="form-control" id="email" name="email">
        </div>
        <button type="submit" class="btn btn-default">Subscribe</button>
      </form>
    </div>
    <div class="panel-footer">This example is part of the article <a href="https://www.sitepoint.com/10-jquery-form-validation-plugins/" target="_blank">10 jQuery Form Validation Plugins</a> on <a href="http://sitepoint.com/" target="_blank">SitePoint</a> by <a href="https://github.com/julmot"
        target="_blank">Julian Motz</a>.</div>
  </div>
</div>
</div>

<script>
var $form = $("#myForm"),
$successMsg = $(".alert");
$.validator.addMethod("letters", function(value, element) {
  return this.optional(element) || value == value.match(/^[a-zA-Z\s]*$/);
});
$form.validate({
  rules: {
    name: {
      required: true,
      minlength: 3,
      letters: true
    },
    email: {
      required: true,
      email: true
    }
  },
  messages: {
    name: "Please specify your name (only letters and spaces are allowed)",
    email: "Please specify a valid email address"
  },
  submitHandler: function() {
    $successMsg.show();
  }
});
</script>