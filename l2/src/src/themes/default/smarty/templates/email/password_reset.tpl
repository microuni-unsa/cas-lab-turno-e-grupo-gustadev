[{extends file="./base.tpl"}]
[{block name="content"}]
    <p>Hello,</p>
    <p>
      We received a request to reset the password for your account[{if count($resetEmailData) > 1}]s[{/if}] associated with the e-mail address <b>[{$email}]</b>.
      Below, you will find
      [{if count($resetEmailData) > 1}]
        a list of password reset links for each of your accounts in different tenants.
      [{else}]
        a password reset link for your account.
      [{/if}]
    </p>
    [{if count($resetEmailData) > 1}]
    <table style="min-width: 100%">
      <thead>
        <tr>
          <th scope="col" align="left">Tenant name</th>
          <th scope="col" align="left">Username</th>
          <th scope="col" align="left">Reset link</th>
        </tr>
      </thead>
      <tbody>
        [{foreach $resetEmailData as $emailData}]
        <tr>
          <td class="row">[{$emailData['userInfo']->tenantName}]</td>
          <td class="row">[{$emailData['userInfo']->userName}]</td>
          <td class="row"><a href="[{$emailData['resetLink']}]" class="reset-password-link">Reset Password</a></td>
        </tr>
        [{/foreach}]
      </tbody>
    </table>
    [{else}]
    <br>
    <p><a href="[{$resetEmailData[0]['resetLink']}]" class="reset-password-link">Reset my password</a></p>
    [{/if}]
    <p>Please note, the link[{if count($resetEmailData) > 1}]s[{/if}] will expire in <b>[{$ttl}]</b>.</p>
    <br>
    <p>If you did not initiate the change of the password, please disregard this e-mail or contact your administrator for assistance.</p>
[{/block}]
