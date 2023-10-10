<!-- CONTACT FORM TEMPLATED INCLUDED INTO CONTECT-FORM -->
<?php if (get_plugin_options('contact_plugin_active')): ?>
<section class="container">
    <div id="form_success" style="background: green;"></div>
    <div id="form_error" style="background: red;"></div>
    <div class="segment">
    <h1>Make an Enquiry</h1>
  </div>
    <form id="enquiry_form" type="post">
        <?php wp_nonce_field('wp_rest');?>

        <div>
            <label>
            <input type="text" name="name" id="name" placeholder="Name">
        </label>
         </div>
        <div>
            <label>
            <input type="email" name="email" id="email" placeholder="Email">
        </label>
         </div>
        <div>
            <label>
            <input type="tel" name="tel" id="tel" placeholder="Number">
        </label>
         </div>
        <div>
            <label>
            <textarea name="message" placeholder="Enter your message..."></textarea>
        </label>
         </div>
        <div>
         <button class="unit" type="submit">Submit</button>
        </div>
    </form>
</section>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script>
    jQuery(document).ready(function($){
        $("#enquiry_form").on('submit', function(event){
            event.preventDefault();
            var formInput = $(this);
            console.log(formInput.serialize());
            $.ajax({
                type: "POST",
                url: "<?php echo get_rest_url(null, 'v1/contact-form/submit');?>",
                data: formInput.serialize(),
                success: function(res){
                    formInput.hide();
                    // $('#form_success').html("Your message was sent").fadeIn();
                    $("#form_success").html(res).fadeIn();
                },
                error: function(){
                    $('#form_error').html("There was an error submitting your").fadeIn();
                }
            })
        });
    });

    
</script>
<?php else: ?>

This form is not Active

<?php endif; ?>