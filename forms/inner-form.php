<form class="dmt" id="<?php echo esc_attr($form_id); ?>" action="<?php echo esc_url($submissionUrl); ?>" onsubmit="submitForm(event)">


    <input type="hidden" name="form-identifier" value="sidebar-form">
<input type="hidden" name="source" value="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">


    <!-- Name Field -->
    <input type="text" id="customer-name" name="customer-name" placeholder="שם מלא" >
<span id="name-error" class="error-message"></span>

<!-- Nobile phone Name Field -->
<div style="display: none;">
  <input type="text" name="mobile-phone" id="mobile-phone" placeholder="enter your nobile phone pretty please">
  <input type="text" name="date" id="date" >
</div>


    <!-- Phone Field -->
<input type="tel" id="phone-number" name="phone-number" placeholder="מספר טלפון" >

<span id="phone-error" class="error-message"></span>



    <!-- Submit Button -->
    <input type="submit" value="שליחה">
</form>
