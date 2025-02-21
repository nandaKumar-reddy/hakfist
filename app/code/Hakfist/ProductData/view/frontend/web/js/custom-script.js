document.addEventListener('DOMContentLoaded', function () {
    const addTeamButton = document.getElementById('add-team-button');
    let teamMemberCount = 0;
    const maxTeamMembers = 3;

    addTeamButton.addEventListener('click', function () {
        if (teamMemberCount < maxTeamMembers) {
            addTeamMember();
            teamMemberCount++;
            if (teamMemberCount >= maxTeamMembers) {
                addTeamButton.disabled = true;
            }
        }
    });

    function addTeamMember() {
        const additionalTeamMembersContainer = document.getElementById('additional-team-members');
        if (additionalTeamMembersContainer) {
            const teamMemberDiv = document.createElement('div');
            teamMemberDiv.className = 'team-member';
            teamMemberDiv.innerHTML = `
                    <div class="team-details">
                        <div class="team-details-row1">
                            <label for="team_name">Name</label>
                            <input type="text" id="team_name" name="team_name[]" required><br>
                        </div>
                        <div class="team-details-row2">
                            <label for="team_number">Mobile Number</label>
                            <input type="tel" class="team_number" name="team_number[]" required><br>
                        </div>
                        <div class="team-details-row1">
                            <label for="team_email">Email</label>
                            <div class="input-wrapper">
                                <span class="emoji">📧</span>
                                <input type="email" id="team_email" name="team_email[]" required>
                            </div>
                        </div>
                        <div class="team-details-row1">
                            <label for="team_designation">Designation</label>
                            <input type="text" id="team_designation" name="team_designation[]" required>
                        </div>
                        <div class="team-details-row1 remove-team-member">
                            &times;
                        </div>
                    </div>
                `;
            additionalTeamMembersContainer.appendChild(teamMemberDiv);

            // Initialize intl-tel-input for the new input field
            const telInput = teamMemberDiv.querySelector(".team_number");
            window.intlTelInput(telInput, {
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
            });

            teamMemberDiv.querySelector('.remove-team-member').addEventListener('click', function () {
                additionalTeamMembersContainer.removeChild(teamMemberDiv);
                teamMemberCount--;
                if (teamMemberCount < maxTeamMembers) {
                    addTeamButton.disabled = false;
                }
            });
        }
    }
});

document.addEventListener("DOMContentLoaded", function () {
    var input = document.querySelector("#team_number");
    window.intlTelInput(input, {
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
    });
});

require([
    'jquery'
], function ($, kp) {

    // jQuery(document).ready(function () {
    //     // Get the modal
    //     var popup = document.getElementById("custom-popup");
    //     var closeButton = document.getElementsByClassName("close-button")[0];

    //     // Function to open the popup
    //     function openPopup(title, message, isSuccess) {
    //         document.getElementById("popup-title").innerText = title;
    //         var messageElement = document.getElementById("popup-message");
    //         messageElement.innerText = message;
    //         messageElement.className = isSuccess ? 'success-message' : 'error-message';
    //         popup.style.display = "block";
    //     }

    //     // Function to show loading spinner
    //     function showPageLoading() {
    //         document.getElementById("loading-spinner").style.display = "block";
    //         // $('loading-spinner').show();
    //     }

    //     // Function to hide loading spinner
    //     function hidePageLoading() {
    //         document.getElementById("loading-spinner").style.display = "none";
    //     }

    //     // When the user clicks on the close button, close the popup
    //     closeButton.onclick = function () {
    //         popup.style.display = "none";
    //     }

    //     // When the user clicks anywhere outside of the popup, close it
    //     window.onclick = function (event) {
    //         if (event.target == popup) {
    //             popup.style.display = "none";
    //         }
    //     }

    //     $("#registration-form").submit(function (event) {
    //         event.preventDefault();
    //         var formData = new FormData(this);
    //         console.log("Form Data:", formData);
    //         showPageLoading();

    //         $.ajax({
    //             type: "POST",
    //             url: '/hakfist/index/process',
    //             data: formData,
    //             dataType: "JSON",
    //             processData: false, // Important!
    //             contentType: false, // Important!
    //             success: function (data) {
    //                 hidePageLoading();
    //                 if (data.status === 'success') {
    //                     console.log("Form data received:", data.formData);
    //                     console.log("Form submitted successfully!");
    //                     openPopup('Success', 'Customer account created successfully!', true);
    //                     $("#registration-form")[0].reset();
    //                 } else {
    //                     console.log("Error:", data.message);
    //                     console.log("Form submission failed!");
    //                     openPopup('Error', data.message, false);
    //                 }
    //             },
    //             error: function (xhr, status, error) {
    //                 hidePageLoading();
    //                 console.error("AJAX error:", status, error);
    //                 console.log("An error occurred while submitting the form.");
    //                 openPopup('Error', 'An error occurred while submitting the form.', false);
    //             }
    //         });
    //     });


    // });

    jQuery(document).ready(function () {
        // Get the modal
        var popup = document.getElementById("custom-popup");
        var otpPopup = document.getElementById("otp-popup");
        var closeButton = document.getElementsByClassName("close-button")[0];
        var otpCloseButton = otpPopup.getElementsByClassName("close-button")[0];

        // Function to open the popup
        function openPopup(title, message, isSuccess) {
            document.getElementById("popup-title").innerText = title;
            var messageElement = document.getElementById("popup-message");
            messageElement.innerText = message;
            messageElement.className = isSuccess ? 'success-message' : 'error-message';
            popup.style.display = "block";
        }

        // Function to open the OTP popup
        function openOtpPopup() {
            otpPopup.style.display = "block";
            startOtpTimer();
        }

        // Function to show loading spinner
        function showPageLoading() {
            document.getElementById("loading-spinner").style.display = "block";
        }

        // Function to hide loading spinner
        function hidePageLoading() {
            document.getElementById("loading-spinner").style.display = "none";
        }

        // When the user clicks on the close button, close the popup
        closeButton.onclick = function () {
            popup.style.display = "none";
        }

        otpCloseButton.onclick = function () {
            otpPopup.style.display = "none";
        }

        // When the user clicks anywhere outside of the popup, close it
        window.onclick = function (event) {
            if (event.target == popup) {
                popup.style.display = "none";
            } else if (event.target == otpPopup) {
                otpPopup.style.display = "none";
            }
        }

        $("#registration-form").submit(function (event) {
            event.preventDefault();
            var formData = new FormData(this);
            console.log("Form Data:", formData);
            showPageLoading();

            openOtpPopup(formData['company_email']);

            $.ajax({
                type: "POST",
                url: '/hakfist/index/process',
                data: formData,
                dataType: "JSON",
                processData: false, // Important!
                contentType: false, // Important!
                success: function (data) {
                    hidePageLoading();
                    if (data.status === 'success') {
                        console.log("Form data received:", data.formData);
                        console.log("Form submitted successfully!");
                        openPopup('Success', 'Customer account created successfully!', true);
                        $("#registration-form")[0].reset();
                        window.location.href = '/mpcustomerapproval-not-approved';
                    } else if (data.status === 'otp_required') {
                        openOtpPopup();
                    } else {
                        console.log("Error:", data.message);
                        console.log("Form submission failed!");
                        openPopup('Error', data.message, false);
                    }
                },
                error: function (xhr, status, error) {
                    hidePageLoading();
                    console.error("AJAX error:", status, error);
                    console.log("An error occurred while submitting the form.");
                    openPopup('Error', 'An error occurred while submitting the form.', false);
                }
            });
        });

        var resendButton = document.getElementById("resend-button");
        var validateButton = document.getElementById("validate-button");
        var resendTimer = document.getElementById("resend-timer");
        var timer;

        function startOtpTimer() {
            var timeLeft = 30;
            resendTimer.innerText = `Resend in ${timeLeft} sec`;
            resendButton.classList.add("hidden");

            timer = setInterval(function () {
                timeLeft--;
                resendTimer.innerText = `Resend in ${timeLeft} sec`;
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    resendTimer.classList.add("hidden");
                    resendButton.classList.remove("hidden");
                }
            }, 1000);
        }

        resendButton.onclick = function () {
            startOtpTimer();
            // Add your resend OTP logic here
        }

        validateButton.onclick = function () {
            var inputs = document.querySelectorAll('.otp-inputs input');
            var otp = Array.from(inputs).map(input => input.value).join('');
            // Add your OTP validation logic here using the `otp` variable
            alert('OTP: ' + otp);
        }
    });

		
			
    // $("#registration-form").submit(function (event) {
    //     event.preventDefault();
    //     var formData = new FormData(this);
    //     console.log("Form Data:", formData);
    //     showPageLoading();

    //     openOtpPopup(formData['company_email']);

    //     $.ajax({
    //         type: "POST",
    //         url: '/hakfist/index/process',
    //         data: formData,
    //         dataType: "JSON",
    //         processData: false, // Important!
    //         contentType: false, // Important!
    //         success: function (data) {
    //             hidePageLoading();
    //             if (data.status === 'success') {
    //                 console.log("Form data received:", data.formData);
    //                 console.log("Form submitted successfully!");
    //                 openPopup('Success', 'Customer account created successfully!', true);
    //                 $("#registration-form")[0].reset();
    //             } else if (data.status === 'otp_required') {
    //                 openOtpPopup();
    //             } else {
    //                 console.log("Error:", data.message);
    //                 console.log("Form submission failed!");
    //                 openPopup('Error', data.message, false);
    //             }
    //         },
    //         error: function (xhr, status, error) {
    //             hidePageLoading();
    //             console.error("AJAX error:", status, error);
    //             console.log("An error occurred while submitting the form.");
    //             openPopup('Error', 'An error occurred while submitting the form.', false);
    //         }
    //     });
    // });




});