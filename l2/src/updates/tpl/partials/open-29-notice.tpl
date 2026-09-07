<div id="overlay" class="modal-overlay" style="display: none;"></div>
<div id="modal" class="modal" style="display: none;">
    <div class="modal-header">
        <h1>Important information</h1>
    </div>
    <div class="modal-body">
        <p class="modal-p">
            You are about to update your i-doit OPEN instance to version 29 or higher. If you proceed please note that the following features will no longer be available as part of i-doit open:
        </p>
        <ul class="modal-p" style="line-height: 20px;">
            <li>JSON-RPC API</li>
            <li>Zammad interface</li>
            <li>JDisc interface</li>
            <li>Active Directory interface</li>
        </ul>
        <p class="modal-p">
            However, we won’t let you down! i-doit pro contains all features above and is available at special discounts for developers. Just drop us a line at <a href="mailto:sales@i-doit.com">sales@i-doit.com</a> and we will get back to you with the details.
        </p>
    </div>
    <div class="modal-footer">
        <span id="counter" class="modal-counter">Please wait 00:10 seconds...</span>
        <button disabled class="button disabled" id="confirm" type="button" onclick="submitToNextStep()">
            <img src="images/axialis/basic/symbol-ok.svg" alt="" /> Read and acknowledged
        </button>
    </div>
</div>
