document.addEventListener('DOMContentLoaded', function() {
    var registerButton = document.getElementById('register_button');
    var mobileNumberInput = document.getElementById('id_profile_field_Phone');
    var otpSection = document.getElementById('otp_section');
    var otpInput = document.getElementById('otp_input');
    var verifyButton = document.getElementById('verify_button');
    var submitButton = document.getElementById('id_submitbutton');
    var serverGeneratedOTP;

    
    if(submitButton.disabled == false){
        submitButton.disabled = true;
    }



    if (registerButton && mobileNumberInput) {
        registerButton.addEventListener('click', function() {
            var mobileNumber = mobileNumberInput.value;

            // Validate that the input is a number and has a max length of 10
            if (!/^\d{1,10}$/.test(mobileNumber)) {
                alert('Please enter a valid mobile number with a maximum of 10 digits.');
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open('POST', M.cfg.wwwroot + '/local/register_button/send_message.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    alert('OTP Send To Your Mobile: ' + response.result);

                    serverGeneratedOTP = response.otp;
                    // otpSection.style.display = 'block';

                    if (response.result === 'Success') {
                        otpSection.style.display = 'block';
                        disableButtonWithTimeout(registerButton, 30);
                    }
                }
            };
            xhr.send('mobile_number=' + encodeURIComponent(mobileNumber));
        });
    }

    if (verifyButton && otpInput) {
        verifyButton.addEventListener('click', function() {
            var enteredOtp = otpInput.value;

            // Check if the entered OTP matches the fetched OTP
            if (parseInt(enteredOtp) === parseInt(serverGeneratedOTP)) {
                alert('OTP verification successful!');
                // mobileNumberInput.disabled = true;
                addTickIcon(mobileNumberInput);
                submitButton.disabled = false;
                otpSection.style.display = 'none';
                registerButton.style.display = 'none';
            } else {
                alert('Entered OTP does not match!');
            }
        });
    }

    function disableButtonWithTimeout(button, timeout) {
        button.disabled = true;
        var originalText = button.innerText;
        var remainingTime = timeout;

        function updateButtonText() {
            if (remainingTime > 0) {
                button.innerText = originalText + ' (' + remainingTime + 's)';
                remainingTime--;
                setTimeout(updateButtonText, 1000);
            } else {
                button.innerText = originalText;
                button.disabled = false;
            }
        }

        updateButtonText();
    }

    function addTickIcon(field) {
        var existingTickIcon = document.getElementById('tick_icon');
        if (existingTickIcon) {
            existingTickIcon.remove();
        }

        // Create a tick icon element
        var tickIcon = document.createElement('span');
        tickIcon.id = 'tick_icon';
        tickIcon.innerHTML = '✔️';
        tickIcon.style.marginLeft = '10px';
        tickIcon.style.color = 'green';

        field.parentNode.appendChild(tickIcon);
    }
});

