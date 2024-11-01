<?php

/**
 * CF ESIG Licensed Downloads
 *
 * @package   Caldera_Forms_ESIG
 * @author    Arafat Rahman <arafatrahmank@gmail.com>
 * @license   GPL-2.0+
 * @link
 */
?>

<?php



if (!isset($element['esig']['signing_logic'])) {
    $element['esig']['signing_logic'] = 'redirect';
}
if (!isset($element['esig']['submit_type'])) {
    $element['esig']['submit_type'] = 'underline';
}
if (!isset($element['esig']['select_sad'])) {
    $element['esig']['select_sad'] = 'selected';
}
?>

<?php if (function_exists("WP_E_Sig")) { ?>
    <div class="caldera-config-group">
        <label for="signer_name">
            <?php _e('Signer Name', 'cf-form-connector'); ?><font color="red">*</font>
        </label>
        <div class="caldera-config-field">
            <input type="text" class="block-input field-config magic-tag-enabled required" name="{{_name}}[signer_name]" value="{{signer_name}}">
            <span class="description"><?php _e('Name will appear to be from this name.. ', 'esig'); ?></span>
            <span id="signer-name-validation-msg" color="red" style="display:none;color:red;"> <b>This field is required </b></span>

        </div>
    </div>


    <div class="caldera-config-group">
        <label for="signer_email">
            <?php _e('Signer Email Address', 'cf-form-connector'); ?><font color="red">*</font>
        </label>
        <div class="caldera-config-field">
            <input type="text" class="block-input field-config magic-tag-enabled required" name="{{_name}}[signer_email]" value="{{signer_email}}">
            <span class="description"><?php _e('Email will appear to be from this name.. ', 'esig'); ?></span>
            <span id="signer-email-validation-msg" color="red" style="display:none;color:red;"> <b>This field is required </b></span>
        </div>
    </div>


    <div class="caldera-config-group">
        <label><?php _e('Signing Logic', 'esig'); ?></label>
        <div class="caldera-config-field">
            <select class="field-config" name="{{_name}}[signing_logic]">

                <option value="redirect" {{#is signing_logic value="redirect"}}selected="selected" {{/is}}><?php _e('Redirect user to Contract/Agreement after Submission', 'esig'); ?></option>
                <option value="email" {{#is signing_logic value="email"}}selected="selected" {{/is}}><?php _e('Send User an Email Requesting their Signature after Submission', 'esig'); ?></option>
            </select>

            <span class="description"><?php _e('Please select your desired signing logic once this form is submitted', 'esig'); ?></span>
        </div>
    </div>

    <?php do_action('caldera_forms_processor_config', $element);



    ?>
    <div class="caldera-config-group">
        <label for="select_sad"><?php _e('Select stand alone document', 'esig'); ?><font color="red">*</font></label>
        <div class="caldera-config-field">
            <select name="{{_name}}[select_sad]" class="field-config required">
                <?php
                if (class_exists('esig_sad_document')) {

                    $sad = new esig_sad_document();
                    $sad_pages = $sad->esig_get_sad_pages();
                    echo '<option value=""> ' . __('Select an agreement page', 'esig') . ' </option>';
                    foreach ($sad_pages as $page) {
                        // $selected = '{{#is select_sad value="7"}}selected="selected"{{/is}}';
                        //echo $selected . "rupom";
                        if (get_the_title($page->page_id)) {
                            //  echo '<option value="' . $page->page_id . '" {{#is select_sad value="7"}}selected="selected"{{/is}} > ' . get_the_title($page->page_id) . ' </option>';

                            printf(
                                '<option value="esig%s" {{#is select_sad value="esig%s"}}selected="selected"{{/is}}>%s</option>',
                                esc_attr($page->page_id),
                                esc_attr($page->page_id),
                                esc_html__(get_the_title($page->page_id))
                            );
                        }
                    }
                }
                ?>
            </select><br><span id="signer-sad-validation-msg" color="red" style="display:none;color:red;"> <b>This field is required </b></span><span class="description"><?php _e('If you would like to can <a href="edit.php?post_type=esign&amp;page=esign-add-document&amp;esig_type=sad">create new document</a>', 'esig'); ?></span><br><br>

            <select class="field-config" name="{{_name}}[submit_type]">
                <option value="underline" {{#is submit_type value="underline"}}selected="selected" {{/is}}><?php _e('Underline the data That was submitted from this Caldera form', 'esig'); ?></option>
                <option value="not_under" {{#is submit_type value="not_under"}}selected="selected" {{/is}}><?php _e('Do not underline the data that was submitted from the Caldera Form', 'esig'); ?></option>
            </select>
        </div>
    </div>


    <div class="caldera-config-group">
        <label for="signing_reminder_email"><?php _e('Signing Reminder Email', 'esig'); ?></label>
        <label for="reminder_email"></label>
        <label for="first_reminder_send"></label>
        <label for="expire_reminder"></label>
        <div class="caldera-config-field">
            <input name="signing_reminder_email" name="{{_name}}[signing_reminder_email]" value="{{signing_reminder_email}}" type="hidden" />
            <input type="checkbox" id="reminder_data" onclick="prefill()" name="{{_name}}[reminder_data]" value="1" <?php if (isset($element['processor']['reminder_data'])) {
                                                                                                                        echo 'checked="checked"';
                                                                                                                    } ?>><?php _e('Enabling signing reminder email. If/When user has not sign the document', 'esig'); ?><br>
            <div id="reminder_section" <?php if (!isset($element['processor']['reminder_data'])) { ?> style="visibility:hidden" <?php } ?>>
                <?php _e('Send the first reminder to the signer ', 'esig'); ?><input type="textbox" name="{{_name}}[reminder_email]" id="reminder_email" min="1" oninput="this.value = (!isNaN(Math.abs(this.value)) && this.value>0)?Math.abs(this.value):null" value="{{reminder_email}}" style="width:40px;height:30px;"> <?php _e('days after the initial signing request. ', 'esig'); ?><br>
                <?php _e('Send the second reminder to the signer ', 'esig'); ?><input type="textbox" name="{{_name}}[first_reminder_send]" id="first_reminder_send" min="1" oninput="this.value = (!isNaN(Math.abs(this.value)) && this.value>0)?Math.abs(this.value):null" value="{{first_reminder_send}}" style="width:40px;height:30px;"> <?php _e('days after the initial signing request. ', 'esig'); ?><br>
                <?php _e('Send the last reminder to the signer ', 'esig'); ?><input type="textbox" name="{{_name}}[expire_reminder]" id="expire_reminder" min="1" oninput="this.value = (!isNaN(Math.abs(this.value)) && this.value>0)?Math.abs(this.value):null" value="{{expire_reminder}}" style="width:40px;height:30px;"><?php _e('days after the initial signing request. ', 'esig'); ?>
            </div>
        </div>

    </div>
<?php } ?>

<?php
if (!function_exists("WP_E_Sig")) {
    include_once("core-alert.php");
} ?>



<script>
    function prefill() {

        if (document.getElementById('reminder_data').checked) {
            document.getElementById("reminder_section").style.visibility = "visible";

            // setting a validation message   
            let reminderEmail = document.getElementById("reminder_email");
            let firstReminderSend = document.getElementById("first_reminder_send");
            let expireReminder = document.getElementById("expire_reminder");

            reminderEmail.classList.add("required");
            firstReminderSend.classList.add("required");
            expireReminder.classList.add("required");


        } else {
            document.getElementById("reminder_section").style.visibility = "hidden";
            let reminderEmail = document.getElementById("reminder_email");
            let firstReminderSend = document.getElementById("first_reminder_send");
            let expireReminder = document.getElementById("expire_reminder");

            reminderEmail.classList.remove("required");
            firstReminderSend.classList.remove("required");
            expireReminder.classList.remove("required");
        }

    }
</script>