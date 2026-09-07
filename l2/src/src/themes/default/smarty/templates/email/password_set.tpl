[{extends file="./base.tpl"}]
[{block name="content"}]
    <h2 style="text-align: center; margin-bottom: 30px">Welcome to i-doit!</h2>
    <p>Thank you for your interest in i-doit. We are happy to inform you that your new instance <a href="[{$instanceUrl}]">[{$instanceUrl}]</a> is up and running!</p>
    <p>To get started, follow the link below to set a personal password:</p>
    <br/>
    <p class="reset-link-container"><a href="[{$resetEmailData['resetLink']}]" class="reset-password-link">Set password</a></p>
    <br/>
    <p>Log into your i-doit instance with your newly set password and username:</p>
    <p><strong>[{$resetEmailData['userInfo']->userName}]</strong></p>
    <p>We hope you and your team will enjoy working with i-doit, in order to bring your IT documentation to the next level.</p>
    <p>Not sure where to start? Check out our <a href="https://www.i-doit.com/en/ressources/whitepaper/6-steps-to-it-documentation">guide to IT documentation</a> to take your first steps in documenting with i-doit.</p>
    <br/>
    <p>If you need more information or need assistance, visit our <a href="https://kb.i-doit.com/en/">Knowledge Base</a> or contact us:</p>
    <table style="border-collapse: collapse;">
        <tr>
            <td style="margin: 0; padding: 5px 0; height: 24px;">
                <img style="vertical-align: middle; margin-right: 10px;" src="https://www.i-doit.com/hubfs/cloud_tech_ops/idoit_mails/icon-envelope.png" alt="E-Mail" />
                <a style="vertical-align: middle;" href="mailto:sales@i-doit.com">sales@i-doit.com</a>
            </td>
        </tr>
        <tr>
            <td style="margin: 0; padding: 5px 0; height: 24px;">
                <img style="vertical-align: middle; margin-right: 10px;" src="https://www.i-doit.com/hubfs/cloud_tech_ops/idoit_mails/icon-telephone.png" alt="Phone" />
                <a style="vertical-align: middle;" href="tel:+4921169931185">+49 211 699 31-185</a>
            </td>
        </tr>
    </table>
[{/block}]
