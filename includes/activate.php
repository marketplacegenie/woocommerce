<style>
    body {font-family: Arial, Helvetica, sans-serif;}

    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }

    /* The Close Button */
    .close {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }
</style>
<button id="myBtn">Open Modal</button>

<!-- The Modal -->
<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <div id="wf-onboarding-fresh-install" class="wf-onboarding-modal">
            <div id="wf-onboarding-fresh-install-1" class="wf-onboarding-modal-content">
                <div class="wf-onboarding-logo"><img src="/wp-content/plugins/Marketplacegenie/images/wf-horizontal.svg" alt="Marketplacegenie - Securing your WordPress Website"></div>
                <h3>You have successfully installed Marketplacegenie 7.4.1</h3>
                <h4>Please tell us where Marketplacegenie should send you security alerts for your website:</h4>
                <input type="text" id="wf-onboarding-alerts" placeholder="you@example.com" value="">
                <p id="wf-onboarding-alerts-disclaimer">We do not use this email address for any other purpose unless you opt-in to receive other mailings. You can turn off alerts in the options.</p>
                <div id="wf-onboarding-subscribe">
                    <label for="wf-onboarding-email-list">Would you also like to join our WordPress security mailing list to receive WordPress security alerts and Marketplacegenie news?</label>
                    <div id="wf-onboarding-subscribe-controls">
                        <ul id="wf-onboarding-email-list" class="wf-switch">
                            <li data-option-value="1">Yes</li>
                            <li data-option-value="0">No</li>
                        </ul>
                        <p>(Choose One)</p>
                    </div>
                </div>
                <div id="wf-onboarding-footer">
                    <ul>
                        <li>
                            <input type="checkbox" class="wf-option-checkbox wf-small" id="wf-onboarding-agree"> <label for="wf-onboarding-agree">By checking this box, I agree to the Marketplacegenie <a href="https://www.Marketplacegenie.com/terms-of-use/" target="_blank" rel="noopener noreferrer">terms</a> and <a href="https://www.Marketplacegenie.com/privacy-policy/" target="_blank" rel="noopener noreferrer">privacy policy</a></label>
                            <p class="wf-gdpr-dpa">If you qualify as a data controller under the GDPR and need a data processing agreement, <a href="https://www.Marketplacegenie.com/help/?query=gdpr-dpa" target="_blank" rel="noopener noreferrer">click here</a>.</p>
                        </li>
                        <li><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary wf-disabled" id="wf-onboarding-continue">Continue</a></li>
                    </ul>
                </div>
            </div>
            <div id="wf-onboarding-fresh-install-2" class="wf-onboarding-modal-content" style="display: none;">
                <div class="wf-onboarding-logo"><img src="/wp-content/plugins/Marketplacegenie/images/wf-horizontal.svg" alt="Marketplacegenie - Securing your WordPress Website"></div>
                <h3>Enter Premium License Key</h3>
                <p>Enter your premium license key to enable real-time protection for your website.</p>
                <div id="wf-onboarding-license"><input type="text" placeholder="Enter Premium Key"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary wf-disabled" id="wf-onboarding-license-install">Install</a></div>
                <div id="wf-onboarding-or"><span>or</span></div>
                <p>If you don't have one, you can purchase one now.</p>
                <div id="wf-onboarding-license-footer">
                    <ul>
                        <li><a href="https://www.Marketplacegenie.com/gnl1onboardingOverlayGet/Marketplacegenie-signup/#premium-order-form" class="wf-onboarding-btn wf-onboarding-btn-primary" id="wf-onboarding-get" target="_blank" rel="noopener noreferrer">Upgrade to Premium</a></li>
                        <li><a href="https://www.Marketplacegenie.com/gnl1onboardingOverlayLearn/Marketplacegenie-signup/" class="wf-onboarding-btn wf-onboarding-btn-default" id="wf-onboarding-learn" target="_blank" rel="noopener noreferrer">Learn More</a></li>
                        <li><a href="#" id="wf-onboarding-no-thanks">No Thanks</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the button that opens the modal
    var btn = document.getElementById("myBtn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    }
</script>
